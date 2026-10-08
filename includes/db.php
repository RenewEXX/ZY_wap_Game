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
    foreach (['s_pts', 's_atk', 's_def', 's_hp', 'str', 'agi', 'vit', 'int', 'active_secs', 'last_seen', 'diamonds_bought', 'horse_won'] as $col) {
        try {
            db()->exec("ALTER TABLE users ADD COLUMN {$col} INTEGER NOT NULL DEFAULT 0");
        } catch (Throwable $e) {
        }
    }
    try {
        db()->exec('UPDATE users SET str=COALESCE(s_atk,0), agi=COALESCE(s_def,0), vit=COALESCE(s_hp,0), atk=atk-COALESCE(s_atk,0), def=def-COALESCE(s_def,0), maxhp=maxhp-COALESCE(s_hp,0)*5 WHERE (COALESCE(s_atk,0)+COALESCE(s_def,0)+COALESCE(s_hp,0))>0 AND (COALESCE(str,0)+COALESCE(agi,0)+COALESCE(vit,0))=0');
    } catch (Throwable $e) {
    }
    foreach (['job', 'quest', 'chapter_flags', 'diamonds', 'mp', 'maxmp'] as $col) {
        try {
            db()->exec("ALTER TABLE users ADD COLUMN {$col} " . (in_array($col, ['quest', 'diamonds', 'mp', 'maxmp'], true) ? 'INTEGER NOT NULL DEFAULT 0' : 'TEXT NOT NULL DEFAULT ""'));
        } catch (Throwable $e) {
        }
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
    db()->exec('CREATE INDEX IF NOT EXISTS idx_mats_uid ON mats (uid)');
    db()->exec('CREATE INDEX IF NOT EXISTS idx_spawns_loc ON map_spawns (loc)');
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
    db()->exec('CREATE INDEX IF NOT EXISTS idx_ground_loc ON ground_items (loc)');
    db()->exec(
        'CREATE TABLE IF NOT EXISTS mats (
            uid INTEGER NOT NULL,
            mat TEXT NOT NULL,
            num INTEGER NOT NULL DEFAULT 0,
            PRIMARY KEY (uid, mat)
        )'
    );
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
    $st = db()->prepare('SELECT * FROM users WHERE username = ? ORDER BY id');
    $st->execute([$name]);
    return $st->fetchAll();
}

function user_save(array $u): void
{
    $st = db()->prepare(
        'UPDATE users SET lv=?, exp=?, hp=?, maxhp=?, mp=?, maxmp=?, atk=?, def=?, gold=?, loc=?, weapon=?, armor=?, potion=?, job=?, quest=?, diamonds=?, s_pts=?, str=?, agi=?, vit=?, int=?, active_secs=?, last_seen=?, diamonds_bought=?, horse_won=? WHERE id=?'
    );
    $st->execute([
        $u['lv'], $u['exp'], $u['hp'], $u['maxhp'], $u['mp'] ?? 0, $u['maxmp'] ?? 0, $u['atk'], $u['def'],
        $u['gold'], $u['loc'], $u['weapon'], $u['armor'], $u['potion'], $u['job'] ?? '', $u['quest'] ?? 0, (int) ($u['diamonds'] ?? 0),
        (int) ($u['s_pts'] ?? 0), (int) ($u['str'] ?? 0), (int) ($u['agi'] ?? 0), (int) ($u['vit'] ?? 0), (int) ($u['int'] ?? 0),
        (int) ($u['active_secs'] ?? 0), (int) ($u['last_seen'] ?? 0), (int) ($u['diamonds_bought'] ?? 0), (int) ($u['horse_won'] ?? 0), $u['id'],
    ]);
}

function user_save_flags(array $u): void
{
    user_save($u);
    if (array_key_exists('chapter_flags', $u)) {
        db()->prepare('UPDATE users SET chapter_flags=? WHERE id=?')->execute([(string) $u['chapter_flags'], (int) $u['id']]);
    }
}

function user_create(string $name, string $pass, string $job = '', string $zone = 'z1', bool $hashed = false): int
{
    $j = jobs()[$job] ?? jobs()['warrior'];
    $st = db()->prepare(
        'INSERT INTO users (username, pass, lv, exp, hp, maxhp, mp, maxmp, atk, def, gold, loc, weapon, armor, potion, job, quest, zone, created_at) VALUES (?, ?, 1, 0, ?, ?, ?, ?, ?, ?, 20, "smithy", "", "", 2, ?, 0, ?, ?)'
    );
    $st->execute([$name, $hashed ? $pass : password_hash($pass, PASSWORD_DEFAULT), $j['hp'], $j['hp'], $j['mp'] ?? 30, $j['mp'] ?? 30, $j['atk'], $j['def'], $job, $zone, time()]);
    $id = (int) db()->lastInsertId();
    // 出身武器直接发新装备并穿上
    $wnames = ['warrior' => '生锈铁剑', 'mage' => '桦木法杖', 'hunter' => '猎弓', 'priest' => '白木槌'];
    $wbase = $wnames[$job] ?? '生锈铁剑';
    make_equip($id, 'weapon', $wbase, 0, 1);
    $ins = db()->prepare('UPDATE equips SET pos="wear" WHERE id=(SELECT id FROM equips WHERE uid=? AND slot=? ORDER BY id DESC LIMIT 1)');
    $ins->execute([$id, 'weapon']);
    make_equip($id, 'body', '破布甲', 0, 1);
    $ins->execute([$id, 'body']);
    return $id;
}
