<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0777, true);
    }
    $path = DATA_DIR . DIRECTORY_SEPARATOR . 'zuiyuan.sqlite';
    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA synchronous = NORMAL');
    $pdo->exec('PRAGMA temp_store = MEMORY');
    $pdo->exec('PRAGMA cache_size = -8000');
    return $pdo;
}

// ================= 自动备份 =================
// 触发方式：请求落地时（db_init 末尾）检查距离上次备份是否超时。
// PHP 没有常驻进程，靠"懒触发"实现定期备份；间隔到点后的第一个请求就会备一份。

function db_backup_interval(): int
{
    return defined('DB_BACKUP_INTERVAL') ? (int) DB_BACKUP_INTERVAL : 6 * 3600;
}

function db_backup_keep(): int
{
    return defined('DB_BACKUP_KEEP') ? (int) DB_BACKUP_KEEP : 12;
}

function db_backup_dir(): string
{
    return DATA_DIR . DIRECTORY_SEPARATOR . 'backups';
}

// 按文件名升序（文件名含时间戳，字典序即时间序）
function db_backup_files(): array
{
    $files = glob(db_backup_dir() . DIRECTORY_SEPARATOR . 'zuiyuan-*.sqlite');
    if (!is_array($files)) {
        return [];
    }
    sort($files);
    return $files;
}

function db_backup_last_at(): int
{
    try {
        return (int) (db()->query("SELECT num FROM sys_kv WHERE k='last_backup_at'")->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return 0;
    }
}

function db_backup_due(): bool
{
    $last = db_backup_last_at();
    return $last <= 0 || (time() - $last) >= db_backup_interval();
}

// 立即做一份备份，成功返回文件路径，失败返回 null
function db_backup_now(): ?string
{
    $dir = db_backup_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
        return null;
    }
    // 文件名 = zuiyuan-<Ymd-His>-<序号6位>：
    // 1) 同秒内靠序号保证唯一；2) 字典序 == 时间序（超量清理靠它排序淘汰最旧）
    $base = 'zuiyuan-' . date('Ymd-His');
    $file = '';
    for ($n = 0; $n < 10000; $n++) {
        $file = $dir . DIRECTORY_SEPARATOR . $base . '-' . sprintf('%06d', $n) . '.sqlite';
        if (!file_exists($file)) {
            break;
        }
        $file = '';
    }
    if ($file === '') {
        return null;
    }
    // VACUUM INTO 生成事务一致的快照（含 WAL 里未 checkpoint 的内容），比直接 copy 安全得多
    try {
        db()->exec('VACUUM INTO ' . db()->quote($file));
    } catch (Throwable $e) {
        @unlink($file);
        return null;
    }
    if (!is_file($file)) {
        return null;
    }
    try {
        db()->prepare("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('last_backup_at', ?)")->execute([time()]);
    } catch (Throwable $e) {
    }
    // 只保留最近 N 份，超出的删掉
    $files = db_backup_files();
    $extra = count($files) - db_backup_keep();
    if ($extra > 0) {
        foreach (array_slice($files, 0, $extra) as $old) {
            @unlink($old);
        }
    }
    return $file;
}

function db_auto_backup(): void
{
    try {
        if (!db_backup_due()) {
            return;
        }
        $dir = db_backup_dir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $lock = @fopen($dir . DIRECTORY_SEPARATOR . '.backup.lock', 'c');
        if ($lock === false) {
            return;
        }
        try {
            // 并发请求只放一个进来做备份
            if (!flock($lock, LOCK_EX | LOCK_NB)) {
                return;
            }
            // 拿到锁再复查一次，避免多个请求重复备份
            if (db_backup_due()) {
                db_backup_now();
            }
        } finally {
            @flock($lock, LOCK_UN);
            @fclose($lock);
        }
    } catch (Throwable $e) {
        // 备份失败绝不能影响正常请求
    }
}

function db_init(): void
{
    db()->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            pass TEXT NOT NULL,
            lv INTEGER NOT NULL DEFAULT 1,
            exp INTEGER NOT NULL DEFAULT 0,
            hp INTEGER NOT NULL DEFAULT 40,
            maxhp INTEGER NOT NULL DEFAULT 40,
            mp INTEGER NOT NULL DEFAULT 0,
            maxmp INTEGER NOT NULL DEFAULT 0,
            atk INTEGER NOT NULL DEFAULT 6,
            def INTEGER NOT NULL DEFAULT 1,
            gold INTEGER NOT NULL DEFAULT 20,
            s_pts INTEGER NOT NULL DEFAULT 0,
            s_atk INTEGER NOT NULL DEFAULT 0,
            s_def INTEGER NOT NULL DEFAULT 0,
            s_hp INTEGER NOT NULL DEFAULT 0,
            str INTEGER NOT NULL DEFAULT 0,
            agi INTEGER NOT NULL DEFAULT 0,
            vit INTEGER NOT NULL DEFAULT 0,
            int INTEGER NOT NULL DEFAULT 0,
            loc TEXT NOT NULL DEFAULT "smithy",
            weapon TEXT NOT NULL DEFAULT "",
            armor TEXT NOT NULL DEFAULT "",
            potion INTEGER NOT NULL DEFAULT 1,
            job TEXT NOT NULL DEFAULT "",
            quest INTEGER NOT NULL DEFAULT 0,
            diamonds INTEGER NOT NULL DEFAULT 0,
            active_secs INTEGER NOT NULL DEFAULT 0,
            last_seen INTEGER NOT NULL DEFAULT 0,
            diamonds_bought INTEGER NOT NULL DEFAULT 0,
            horse_won INTEGER NOT NULL DEFAULT 0,
            zone TEXT NOT NULL DEFAULT "z1",
            chapter_flags TEXT NOT NULL DEFAULT "",
            created_at INTEGER NOT NULL,
            UNIQUE(username, zone)
        )'
    );
    foreach (['s_pts', 's_atk', 's_def', 's_hp', 'str', 'agi', 'vit', 'int', 'spi', 'grow_def', 'grow_matk', 'active_secs', 'last_seen', 'diamonds_bought', 'diamonds_spent', 'horse_won'] as $col) {
        try {
            db()->exec("ALTER TABLE users ADD COLUMN {$col} INTEGER NOT NULL DEFAULT 0");
        } catch (Throwable $e) {
        }
    }
    try {
        db()->exec('UPDATE users SET str=COALESCE(s_atk,0), agi=COALESCE(s_def,0), vit=COALESCE(s_hp,0), atk=atk-COALESCE(s_atk,0), def=def-COALESCE(s_def,0), maxhp=maxhp-COALESCE(s_hp,0)*5 WHERE (COALESCE(s_atk,0)+COALESCE(s_def,0)+COALESCE(s_hp,0))>0 AND (COALESCE(str,0)+COALESCE(agi,0)+COALESCE(vit,0))=0');
    } catch (Throwable $e) {
    }
    foreach (['job', 'quest', 'chapter_flags', 'diamonds', 'mp', 'maxmp', 'account'] as $col) {
        try {
            db()->exec("ALTER TABLE users ADD COLUMN {$col} " . (in_array($col, ['quest', 'diamonds', 'mp', 'maxmp'], true) ? 'INTEGER NOT NULL DEFAULT 0' : 'TEXT NOT NULL DEFAULT ""'));
        } catch (Throwable $e) {
        }
    }
    try {
        db()->exec("UPDATE users SET account=username WHERE account=''");
    } catch (Throwable $e) {
    }
    // 召唤师成长曲线下调（血量每级+6，全职业最低）后，把已有召唤师的存量血量
    // 一次性对齐到新曲线：减去 (等级-1) × 4，并按体质点数保留该有的加成。
    // 用 mat_set 打一次性标记，避免每次启动重复扣。
    try {
        $rows = db()->query("SELECT id, lv, maxhp, hp FROM users WHERE job='summoner'")->fetchAll();
        foreach ($rows as $r) {
            if ((int) (mats_of((int) $r['id'])['rs_summ_hp'] ?? 0) === 1) {
                continue;
            }
            $cut = max(0, ((int) $r['lv'] - 1) * 4);
            $newMax = max(1, (int) $r['maxhp'] - $cut);
            db()->prepare('UPDATE users SET maxhp=?, hp=? WHERE id=?')->execute([$newMax, min((int) $r['hp'], $newMax), (int) $r['id']]);
            mat_set((int) $r['id'], 'rs_summ_hp', 1);
        }
    } catch (Throwable $e) {
    }
    foreach (['zone'] as $tcol) {
        foreach (['chat_msgs', 'auctions', 'guilds', 'parties', 'horse_races'] as $tbl) {
            try {
                db()->exec("ALTER TABLE {$tbl} ADD COLUMN {$tcol} TEXT NOT NULL DEFAULT 'z1'");
            } catch (Throwable $e) {
            }
        }
    }
    // peak_result主键加zone（分大区各自夺冠）
    try {
        $cols = db()->query('PRAGMA table_info(peak_result)')->fetchAll();
        $hasZone = false;
        foreach ($cols as $c) {
            if (($c['name'] ?? '') === 'zone') {
                $hasZone = true;
            }
        }
        if (!$hasZone) {
            db()->exec('CREATE TABLE IF NOT EXISTS peak_result_new (day TEXT NOT NULL, arena TEXT NOT NULL, zone TEXT NOT NULL DEFAULT "z1", winner_uid INTEGER NOT NULL DEFAULT 0, winner_name TEXT NOT NULL DEFAULT "", created_at INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (day, arena, zone))');
            foreach (db()->query('SELECT day, arena, winner_uid, winner_name, created_at FROM peak_result')->fetchAll() as $r) {
                $z = db()->query('SELECT zone FROM users WHERE id=' . (int) ($r['winner_uid'] ?? 0))->fetchColumn();
                db()->prepare('INSERT OR IGNORE INTO peak_result_new (day, arena, zone, winner_uid, winner_name, created_at) VALUES (?, ?, ?, ?, ?, ?)')->execute([(string) ($r['day'] ?? ''), (string) ($r['arena'] ?? ''), $z ?: 'z1', (int) ($r['winner_uid'] ?? 0), (string) ($r['winner_name'] ?? ''), (int) ($r['created_at'] ?? 0)]);
            }
            db()->exec('DROP TABLE peak_result');
            db()->exec('ALTER TABLE peak_result_new RENAME TO peak_result');
        }
    } catch (Throwable $e) {
    }
    try {
        db()->exec("UPDATE auctions SET zone=COALESCE((SELECT zone FROM users WHERE users.id=auctions.seller_uid), 'z1') WHERE seller_uid>0");
    } catch (Throwable $e) {
    }
    try {
        db()->exec('ALTER TABLE users ADD COLUMN zone TEXT NOT NULL DEFAULT "z1"');
    } catch (Throwable $e) {
    }
    db()->exec(
        'CREATE TABLE IF NOT EXISTS equips (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid INTEGER NOT NULL,
            slot TEXT NOT NULL,
            name TEXT NOT NULL,
            quality INTEGER NOT NULL DEFAULT 0,
            affixes TEXT NOT NULL,
            pos TEXT NOT NULL DEFAULT "",
            enchant_el TEXT NOT NULL DEFAULT "",
            enchant_val INTEGER NOT NULL DEFAULT 0
        )'
    );
    foreach (['item_level' => 'INTEGER NOT NULL DEFAULT 1', 'enhance_level' => 'INTEGER NOT NULL DEFAULT 0', 'enhance_fail' => 'INTEGER NOT NULL DEFAULT 0', 'broken' => 'INTEGER NOT NULL DEFAULT 0', 'enchant_el' => 'TEXT NOT NULL DEFAULT ""', 'enchant_val' => 'INTEGER NOT NULL DEFAULT 0', 'req_lv' => 'INTEGER NOT NULL DEFAULT 1', 'req_str' => 'INTEGER NOT NULL DEFAULT 0', 'req_agi' => 'INTEGER NOT NULL DEFAULT 0'] as $col => $definition) {
        try {
            db()->exec("ALTER TABLE equips ADD COLUMN {$col} {$definition}");
        } catch (Throwable $e) {
        }
    }
    // 碎裂概念已删除：老碎裂装恢复正常
    try {
        db()->exec('UPDATE equips SET broken=0 WHERE broken!=0');
    } catch (Throwable $e) {
    }
    db()->exec('CREATE INDEX IF NOT EXISTS idx_equips_uid ON equips (uid)');
    // mats/map_spawns 的表体在后面才建，索引要容错（新库首次初始化时表还不存在）
    try {
        db()->exec('CREATE INDEX IF NOT EXISTS idx_mats_uid ON mats (uid)');
    } catch (Throwable $e) {
    }
    try {
        db()->exec('CREATE INDEX IF NOT EXISTS idx_spawns_loc ON map_spawns (loc)');
    } catch (Throwable $e) {
    }
    if (function_exists('backfill_rank_stats')) {
        backfill_rank_stats();
    }
    db()->exec(
        'CREATE TABLE IF NOT EXISTS mails (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid INTEGER NOT NULL,
            sender TEXT NOT NULL DEFAULT "",
            type TEXT NOT NULL DEFAULT "system",
            title TEXT NOT NULL DEFAULT "",
            body TEXT NOT NULL DEFAULT "",
            attachments TEXT NOT NULL DEFAULT "[]",
            is_read INTEGER NOT NULL DEFAULT 0,
            claimed INTEGER NOT NULL DEFAULT 0,
            pinned INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL DEFAULT 0,
            expires_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_mails_uid ON mails (uid)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS auctions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            seller_uid INTEGER NOT NULL,
            kind TEXT NOT NULL DEFAULT "mat",
            mat_id TEXT NOT NULL DEFAULT "",
            qty INTEGER NOT NULL DEFAULT 1,
            item_name TEXT NOT NULL DEFAULT "",
            item_slot TEXT NOT NULL DEFAULT "",
            item_quality INTEGER NOT NULL DEFAULT 0,
            item_level INTEGER NOT NULL DEFAULT 1,
            item_affixes TEXT NOT NULL DEFAULT "[]",
            enhance_level INTEGER NOT NULL DEFAULT 0,
            currency TEXT NOT NULL DEFAULT "gold",
            start_price INTEGER NOT NULL DEFAULT 0,
            buyout INTEGER NOT NULL DEFAULT 0,
            cur_price INTEGER NOT NULL DEFAULT 0,
            cur_bidder INTEGER NOT NULL DEFAULT 0,
            ends_at INTEGER NOT NULL DEFAULT 0,
            extensions INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT "open",
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_auctions_status ON auctions (status, ends_at)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS bids (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            auction_id INTEGER NOT NULL,
            bidder INTEGER NOT NULL,
            price INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS auction_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid INTEGER NOT NULL,
            text TEXT NOT NULL DEFAULT "",
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS skills (
            uid INTEGER NOT NULL,
            skill TEXT NOT NULL,
            level INTEGER NOT NULL DEFAULT 1,
            prof INTEGER NOT NULL DEFAULT 0,
            tier INTEGER NOT NULL DEFAULT 1,
            active INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (uid, skill)
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS pets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid INTEGER NOT NULL,
            species TEXT NOT NULL,
            quality TEXT NOT NULL DEFAULT "common",
            level INTEGER NOT NULL DEFAULT 1,
            exp INTEGER NOT NULL DEFAULT 0,
            hp INTEGER NOT NULL DEFAULT 1,
            status TEXT NOT NULL DEFAULT "normal",
            active INTEGER NOT NULL DEFAULT 0,
            fatigue INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    try {
        db()->exec('ALTER TABLE pets ADD COLUMN fatigue INTEGER NOT NULL DEFAULT 0');
    } catch (Throwable $e) {
    }
    db()->exec('CREATE INDEX IF NOT EXISTS idx_pets_uid ON pets (uid)');
    $usql = (string) db()->query("SELECT sql FROM sqlite_master WHERE name='users'")->fetchColumn();
    if (str_contains($usql, 'username TEXT NOT NULL UNIQUE')) {
        db()->exec('ALTER TABLE users RENAME TO users_bak');
        db()->exec(
            'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL,
                pass TEXT NOT NULL,
                lv INTEGER NOT NULL DEFAULT 1,
                exp INTEGER NOT NULL DEFAULT 0,
                hp INTEGER NOT NULL DEFAULT 40,
                maxhp INTEGER NOT NULL DEFAULT 40,
                mp INTEGER NOT NULL DEFAULT 0,
                maxmp INTEGER NOT NULL DEFAULT 0,
                atk INTEGER NOT NULL DEFAULT 6,
                def INTEGER NOT NULL DEFAULT 1,
                gold INTEGER NOT NULL DEFAULT 20,
                s_pts INTEGER NOT NULL DEFAULT 0,
                s_atk INTEGER NOT NULL DEFAULT 0,
                s_def INTEGER NOT NULL DEFAULT 0,
                s_hp INTEGER NOT NULL DEFAULT 0,
                str INTEGER NOT NULL DEFAULT 0,
                agi INTEGER NOT NULL DEFAULT 0,
                vit INTEGER NOT NULL DEFAULT 0,
                int INTEGER NOT NULL DEFAULT 0,
                loc TEXT NOT NULL DEFAULT "smithy",
                weapon TEXT NOT NULL DEFAULT "",
                armor TEXT NOT NULL DEFAULT "",
                potion INTEGER NOT NULL DEFAULT 1,
                job TEXT NOT NULL DEFAULT "",
                quest INTEGER NOT NULL DEFAULT 0,
                diamonds INTEGER NOT NULL DEFAULT 0,
                active_secs INTEGER NOT NULL DEFAULT 0,
                last_seen INTEGER NOT NULL DEFAULT 0,
                diamonds_bought INTEGER NOT NULL DEFAULT 0,
                horse_won INTEGER NOT NULL DEFAULT 0,
                zone TEXT NOT NULL DEFAULT "z1",
                chapter_flags TEXT NOT NULL DEFAULT "",
                created_at INTEGER NOT NULL,
                UNIQUE(username, zone)
            )'
        );
        db()->exec('INSERT INTO users (id, username, pass, lv, exp, hp, maxhp, mp, maxmp, atk, def, gold, loc, weapon, armor, potion, job, quest, diamonds, zone, chapter_flags, created_at) SELECT id, username, pass, lv, exp, hp, maxhp, mp, maxmp, atk, def, gold, loc, weapon, armor, potion, job, quest, diamonds, zone, chapter_flags, created_at FROM users_bak');
        db()->exec('DROP TABLE users_bak');
    }
    db()->exec(
        'CREATE TABLE IF NOT EXISTS guilds (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            leader_uid INTEGER NOT NULL DEFAULT 0,
            level INTEGER NOT NULL DEFAULT 1,
            exp INTEGER NOT NULL DEFAULT 0,
            notice TEXT NOT NULL DEFAULT "",
            gold INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS guild_members (
            uid INTEGER PRIMARY KEY,
            gid INTEGER NOT NULL DEFAULT 0,
            role TEXT NOT NULL DEFAULT "member",
            contrib INTEGER NOT NULL DEFAULT 0,
            joined_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_gm_gid ON guild_members (gid)');
    try {
        db()->exec('ALTER TABLE guilds ADD COLUMN gold INTEGER NOT NULL DEFAULT 0');
        // 贡献单位从铜改成金，老数据一次性折算
        db()->exec('UPDATE guild_members SET contrib = CAST(contrib / 10000 AS INTEGER)');
    } catch (Throwable $e) {
    }
    db()->exec(
        'CREATE TABLE IF NOT EXISTS guild_warehouse (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gid INTEGER NOT NULL DEFAULT 0,
            kind TEXT NOT NULL DEFAULT "mat",
            mat_id TEXT NOT NULL DEFAULT "",
            qty INTEGER NOT NULL DEFAULT 0,
            equip_id INTEGER NOT NULL DEFAULT 0,
            donor_uid INTEGER NOT NULL DEFAULT 0,
            donor_name TEXT NOT NULL DEFAULT "",
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_gwh_gid ON guild_warehouse (gid)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS guild_warehouse_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gid INTEGER NOT NULL DEFAULT 0,
            uid INTEGER NOT NULL DEFAULT 0,
            username TEXT NOT NULL DEFAULT "",
            action TEXT NOT NULL DEFAULT "",
            detail TEXT NOT NULL DEFAULT "",
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_gwhlog_gid ON guild_warehouse_log (gid, id)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS guild_invites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            gid INTEGER NOT NULL DEFAULT 0,
            inviter_uid INTEGER NOT NULL DEFAULT 0,
            target_uid INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT "open",
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_ginv_target ON guild_invites (target_uid, status)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS peak_join (
            uid INTEGER NOT NULL,
            day TEXT NOT NULL DEFAULT "",
            arena TEXT NOT NULL DEFAULT "",
            alive INTEGER NOT NULL DEFAULT 1,
            PRIMARY KEY (uid, day)
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS peak_result (
            day TEXT NOT NULL,
            arena TEXT NOT NULL,
            winner_uid INTEGER NOT NULL DEFAULT 0,
            winner_name TEXT NOT NULL DEFAULT "",
            created_at INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (day, arena)
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS wars (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            a_gid INTEGER NOT NULL,
            b_gid INTEGER NOT NULL,
            a_score INTEGER NOT NULL DEFAULT 0,
            b_score INTEGER NOT NULL DEFAULT 0,
            ends_at INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT "open",
            winner INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS chat_msgs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid INTEGER NOT NULL DEFAULT 0,
            username TEXT NOT NULL DEFAULT "",
            channel TEXT NOT NULL DEFAULT "world",
            target INTEGER NOT NULL DEFAULT 0,
            text TEXT NOT NULL DEFAULT "",
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_chat_ch ON chat_msgs (channel, target, id)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS parties (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            leader_uid INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS party_members (
            uid INTEGER PRIMARY KEY,
            pid INTEGER NOT NULL DEFAULT 0,
            joined_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS war_points (
            uid INTEGER NOT NULL,
            week TEXT NOT NULL DEFAULT "",
            gid INTEGER NOT NULL DEFAULT 0,
            points INTEGER NOT NULL DEFAULT 0,
            settled INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (uid, week)
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS map_spawns (
            loc TEXT NOT NULL,
            mid TEXT NOT NULL,
            elite INTEGER NOT NULL DEFAULT 0,
            num INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (loc, mid, elite)
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS map_respawn (
            loc TEXT PRIMARY KEY,
            at INTEGER NOT NULL DEFAULT 0
        )'
    );
    // map_respawn 从按地图计时迁移到按(地图,怪)计时
    try {
        $cols = db()->query("PRAGMA table_info(map_respawn)")->fetchAll();
        $hasMid = false;
        foreach ($cols as $c) {
            if (($c['name'] ?? '') === 'mid') {
                $hasMid = true;
            }
        }
        if (!$hasMid) {
            db()->exec('CREATE TABLE IF NOT EXISTS map_respawn_new (loc TEXT NOT NULL, mid TEXT NOT NULL DEFAULT "", at INTEGER NOT NULL DEFAULT 0, PRIMARY KEY (loc, mid))');
            $old = db()->query('SELECT loc, at FROM map_respawn')->fetchAll();
            $ins = db()->prepare('INSERT OR IGNORE INTO map_respawn_new (loc, mid, at) VALUES (?, "", ?)');
            foreach ($old as $r) {
                $ins->execute([(string) ($r['loc'] ?? ''), (int) ($r['at'] ?? 0)]);
            }
            db()->exec('DROP TABLE map_respawn');
            db()->exec('ALTER TABLE map_respawn_new RENAME TO map_respawn');
        }
    } catch (Throwable $e) {
    }
    db()->exec('CREATE INDEX IF NOT EXISTS idx_respawn_loc_mid ON map_respawn (loc, mid)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS horse_races (
            id INTEGER PRIMARY KEY,
            starts_at INTEGER NOT NULL DEFAULT 0,
            ends_at INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT "open",
            base_pool INTEGER NOT NULL DEFAULT 500,
            result TEXT NOT NULL DEFAULT ""
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS horse_bets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            race_id INTEGER NOT NULL DEFAULT 0,
            uid INTEGER NOT NULL DEFAULT 0,
            horse INTEGER NOT NULL DEFAULT 0,
            amount INTEGER NOT NULL DEFAULT 0,
            UNIQUE (race_id, uid)
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_hb_race ON horse_bets (race_id)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS ground_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            loc TEXT NOT NULL DEFAULT "",
            kind TEXT NOT NULL DEFAULT "mat",
            ref TEXT NOT NULL DEFAULT "",
            num INTEGER NOT NULL DEFAULT 1,
            at INTEGER NOT NULL DEFAULT 0
        )'
    );
    // 竞技场：arena_rank=排名表（uid=0是假人），arena_log=防守记录，arena_shop_log=商店限购
    db()->exec(
        'CREATE TABLE IF NOT EXISTS arena_rank (
            uid INTEGER NOT NULL DEFAULT 0,
            zone TEXT NOT NULL DEFAULT "z1",
            rank INTEGER NOT NULL DEFAULT 0,
            name TEXT NOT NULL DEFAULT "",
            job TEXT NOT NULL DEFAULT "",
            lv INTEGER NOT NULL DEFAULT 1,
            power INTEGER NOT NULL DEFAULT 0,
            is_dummy INTEGER NOT NULL DEFAULT 0,
            season TEXT NOT NULL DEFAULT "",
            PRIMARY KEY (uid, zone)
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_arena_rank ON arena_rank (zone, rank)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS arena_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            zone TEXT NOT NULL DEFAULT "z1",
            defender_uid INTEGER NOT NULL DEFAULT 0,
            attacker_name TEXT NOT NULL DEFAULT "",
            win INTEGER NOT NULL DEFAULT 0,
            old_rank INTEGER NOT NULL DEFAULT 0,
            new_rank INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL DEFAULT 0
        )'
    );
    db()->exec('CREATE INDEX IF NOT EXISTS idx_arena_log ON arena_log (defender_uid)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS arena_shop_log (
            uid INTEGER NOT NULL DEFAULT 0,
            item TEXT NOT NULL DEFAULT "",
            period TEXT NOT NULL DEFAULT "",
            num INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (uid, item, period)
        )'
    );
    db()->exec('CREATE TABLE IF NOT EXISTS sys_kv (k TEXT PRIMARY KEY, num INTEGER NOT NULL DEFAULT 0)');
    db()->exec('CREATE INDEX IF NOT EXISTS idx_ground_loc ON ground_items (loc)');
    // 一次性迁移：回血药删除，老玩家剩余数量按1:1换成雾纹绷带
    try {
        $done = (int) (db()->query("SELECT num FROM sys_kv WHERE k='mig_potion_rm'")->fetchColumn() ?: 0);        if ($done === 0) {
            foreach (db()->query('SELECT id, potion FROM users WHERE potion>0')->fetchAll() as $pr) {
                $pid = (int) $pr['id'];
                $pn = (int) $pr['potion'];
                db()->prepare('INSERT OR IGNORE INTO mats (uid, mat, num) VALUES (?, ?, 0)')->execute([$pid, 'hp_pct10']);
                db()->prepare('UPDATE mats SET num=num+? WHERE uid=? AND mat=?')->execute([$pn, $pid, 'hp_pct10']);
                db()->prepare('UPDATE users SET potion=0 WHERE id=?')->execute([$pid]);
            }
            db()->prepare("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('mig_potion_rm', 1)")->execute();
        }
    } catch (Throwable $e) {
    }
    // 一次性迁移：火龙多伊尔幼体去重。
    // 旧表里 fire_dragon（火龙多伊尔幼体）和 fire_drake（炎龙雏）是同一只火龙，
    // 世界BOSS蛋实际孵出的是 fire_drake。统一到 fire_drake，避免图鉴出现两条火龙。
    try {
        $done = (int) (db()->query("SELECT num FROM sys_kv WHERE k='mig_pet_fire'")->fetchColumn() ?: 0);
        if ($done === 0) {
            db()->exec("UPDATE pets SET species='fire_drake' WHERE species='fire_dragon'");
            db()->prepare("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('mig_pet_fire', 1)")->execute();
        }
    } catch (Throwable $e) {
    }
    db()->exec(
        'CREATE TABLE IF NOT EXISTS mats (
            uid INTEGER NOT NULL,
            mat TEXT NOT NULL,
            num INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (uid, mat)
        )'
    );
    db()->exec(
        'CREATE TABLE IF NOT EXISTS wb_dmg (
            uid INTEGER NOT NULL,
            name TEXT NOT NULL DEFAULT "",
            day INTEGER NOT NULL DEFAULT 0,
            dmg INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (uid, day)
        )'
    );
    // 六龙王：每条龙一份伤害排行，wb_dmg 增加 mid 列并把主键改成 (uid, day, mid)
    try {
        $cols = db()->query("PRAGMA table_info(wb_dmg)")->fetchAll();
        $hasMid = false;
        foreach ($cols as $c) {
            if ((string) ($c['name'] ?? '') === 'mid') {
                $hasMid = true;
            }
        }
        if (!$hasMid) {
            db()->exec('CREATE TABLE IF NOT EXISTS wb_dmg2 (uid INTEGER NOT NULL, name TEXT NOT NULL DEFAULT "", day INTEGER NOT NULL DEFAULT 0, dmg INTEGER NOT NULL DEFAULT 0, mid TEXT NOT NULL DEFAULT "doyle", PRIMARY KEY (uid, day, mid))');
            db()->exec('INSERT OR REPLACE INTO wb_dmg2 (uid, name, day, dmg, mid) SELECT uid, name, day, dmg, "doyle" FROM wb_dmg');
            db()->exec('DROP TABLE wb_dmg');
            db()->exec('ALTER TABLE wb_dmg2 RENAME TO wb_dmg');
        }
        db()->exec('CREATE INDEX IF NOT EXISTS idx_wb_dmg_rank ON wb_dmg (day, mid, dmg)');
    } catch (Throwable $e) {
    }
    // 一次性迁移：清掉历史导入事故遗留的垃圾装备（部位为 NULL/'equip'/数字名等非法值），
    // 它们会在背包里显示成"没有部位、名字不对、没有属性"的奇怪装备。
    try {
        $done = (int) (db()->query("SELECT num FROM sys_kv WHERE k='mig_equip_junk'")->fetchColumn() ?: 0);
        if ($done === 0) {
            $validSlots = array_keys(equip_slots());
            $ph = implode(',', array_fill(0, count($validSlots), '?'));
            $st = db()->prepare("DELETE FROM equips WHERE slot IS NULL OR slot NOT IN ($ph)");
            $st->execute($validSlots);
            db()->prepare("INSERT OR REPLACE INTO sys_kv (k, num) VALUES ('mig_equip_junk', 1)")->execute();
        }
    } catch (Throwable $e) {
    }
    // 定期自动备份（到点才做，失败不影响请求）
    db_auto_backup();
}

function user_by_id(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
}

function user_by_name(string $name): ?array
{
    $st = db()->prepare('SELECT * FROM users WHERE username = ?');
    $st->execute([$name]);
    $row = $st->fetch();
    return $row ?: null;
}

function account_rows(string $name): array
{
    // 账号与角色名分离后，按account列归属账号
    try {
        $st = db()->prepare('SELECT * FROM users WHERE account = ? ORDER BY id');
        $st->execute([$name]);
        $rows = $st->fetchAll();
        if ($rows !== []) {
            return $rows;
        }
    } catch (Throwable $e) {
    }
    $st = db()->prepare('SELECT * FROM users WHERE username = ? ORDER BY id');
    $st->execute([$name]);
    return $st->fetchAll();
}

function user_save(array $u): void
{
    $st = db()->prepare(
        'UPDATE users SET lv=?, exp=?, hp=?, maxhp=?, mp=?, maxmp=?, atk=?, def=?, gold=?, loc=?, weapon=?, armor=?, potion=?, job=?, quest=?, diamonds=?, s_pts=?, str=?, agi=?, vit=?, int=?, spi=?, grow_def=?, grow_matk=?, active_secs=?, last_seen=?, diamonds_bought=?, diamonds_spent=?, horse_won=? WHERE id=?'
    );
    $st->execute([
        $u['lv'], $u['exp'], $u['hp'], $u['maxhp'], $u['mp'] ?? 0, $u['maxmp'] ?? 0, $u['atk'], $u['def'],
        $u['gold'], $u['loc'], $u['weapon'], $u['armor'], $u['potion'], $u['job'] ?? '', $u['quest'] ?? 0, (int) ($u['diamonds'] ?? 0),
        (int) ($u['s_pts'] ?? 0), (int) ($u['str'] ?? 0), (int) ($u['agi'] ?? 0), (int) ($u['vit'] ?? 0), (int) ($u['int'] ?? 0), (int) ($u['spi'] ?? 0),
        (int) ($u['grow_def'] ?? 0), (int) ($u['grow_matk'] ?? 0),
        (int) ($u['active_secs'] ?? 0), (int) ($u['last_seen'] ?? 0), (int) ($u['diamonds_bought'] ?? 0), (int) ($u['diamonds_spent'] ?? 0), (int) ($u['horse_won'] ?? 0), $u['id'],
    ]);
}

function user_save_flags(array $u): void
{
    user_save($u);
    if (array_key_exists('chapter_flags', $u)) {
        db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([(string) $u['chapter_flags'], (int) $u['id']]);
    }
}

function user_create(string $name, string $pass, string $job = '', string $zone = 'z1', bool $hashed = false, string $account = ''): int
{
    $j = jobs()[$job] ?? jobs()['warrior'];
    $st = db()->prepare(
        'INSERT INTO users (username, pass, lv, exp, hp, maxhp, mp, maxmp, atk, def, gold, loc, weapon, armor, potion, job, quest, zone, account, created_at) VALUES (?, ?, 1, 0, ?, ?, ?, ?, ?, ?, 20, "smithy", "", "", 0, ?, 0, ?, ?, ?)'
    );
    // 新建1级：物攻法攻基础全0（武器给起步伤害，防具给起步防御）
    $st->execute([$name, $hashed ? $pass : password_hash($pass, PASSWORD_DEFAULT), $j['hp'], $j['hp'], $j['mp'] ?? 30, $j['mp'] ?? 30, (int) $j['atk'], (int) $j['def'], $job, $zone, $account !== '' ? $account : $name, time()]);
    $id = (int) db()->lastInsertId();
    // 出身加点：战士力量5，法师智慧8，猎手力量3坚毅2，牧师智慧5体质2（法师从创建就不长物理）
    // 出身点已含在职业底攻(jobs())里，属性面板保持0，特色直接体现在攻/防/血/魔
    // 出身武器直接发新装备并穿上（职业限定：斧/杖/弓/锤）
    $wnames = ['warrior' => '生锈战斧', 'mage' => '桦木法杖', 'hunter' => '猎弓', 'priest' => '白木槌', 'summoner' => '残魂灯'];
    $wbase = $wnames[$job] ?? '生锈铁剑';
    make_equip($id, 'weapon', $wbase, 0, 1);
    $ins = db()->prepare('UPDATE equips SET pos="wear" WHERE id=(SELECT id FROM equips WHERE uid=? AND slot=? ORDER BY id DESC LIMIT 1)');
    $ins->execute([$id, 'weapon']);
    make_equip($id, 'body', '破布甲', 0, 1);
    $ins->execute([$id, 'body']);
    return $id;
}
