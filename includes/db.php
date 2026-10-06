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
    return $pdo;
}

function db_init(): void
{
    db()->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            pass TEXT NOT NULL,
            lv INTEGER NOT NULL DEFAULT 1,
            exp INTEGER NOT NULL DEFAULT 0,
            hp INTEGER NOT NULL DEFAULT 40,
            maxhp INTEGER NOT NULL DEFAULT 40,
            atk INTEGER NOT NULL DEFAULT 6,
            def INTEGER NOT NULL DEFAULT 1,
            gold INTEGER NOT NULL DEFAULT 20,
            loc TEXT NOT NULL DEFAULT "smithy",
            weapon TEXT NOT NULL DEFAULT "",
            armor TEXT NOT NULL DEFAULT "",
            potion INTEGER NOT NULL DEFAULT 1,
            job TEXT NOT NULL DEFAULT "",
            quest INTEGER NOT NULL DEFAULT 0,
            created_at INTEGER NOT NULL
        )'
    );
    foreach (['job', 'quest'] as $col) {
        try {
            db()->exec("ALTER TABLE users ADD COLUMN {$col} " . ($col === 'quest' ? 'INTEGER NOT NULL DEFAULT 0' : 'TEXT NOT NULL DEFAULT ""'));
        } catch (Throwable $e) {
        }
    }
    db()->exec(
        'CREATE TABLE IF NOT EXISTS equips (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            uid INTEGER NOT NULL,
            slot TEXT NOT NULL,
            name TEXT NOT NULL,
            quality INTEGER NOT NULL DEFAULT 0,
            affixes TEXT NOT NULL,
            pos TEXT NOT NULL DEFAULT ""
        )'
    );
    foreach (['item_level' => 'INTEGER NOT NULL DEFAULT 1', 'enhance_level' => 'INTEGER NOT NULL DEFAULT 0', 'enhance_fail' => 'INTEGER NOT NULL DEFAULT 0', 'broken' => 'INTEGER NOT NULL DEFAULT 0'] as $col => $definition) {
        try {
            db()->exec("ALTER TABLE equips ADD COLUMN {$col} {$definition}");
        } catch (Throwable $e) {
        }
    }
    db()->exec('CREATE INDEX IF NOT EXISTS idx_equips_uid ON equips (uid)');
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

function user_save(array $u): void
{
    $st = db()->prepare(
        'UPDATE users SET lv=?, exp=?, hp=?, maxhp=?, atk=?, def=?, gold=?, loc=?, weapon=?, armor=?, potion=?, job=?, quest=? WHERE id=?'
    );
    $st->execute([
        $u['lv'], $u['exp'], $u['hp'], $u['maxhp'], $u['atk'], $u['def'],
        $u['gold'], $u['loc'], $u['weapon'], $u['armor'], $u['potion'], $u['job'] ?? '', $u['quest'] ?? 0, $u['id'],
    ]);
}

function user_create(string $name, string $pass, string $job = ''): int
{
    $j = jobs()[$job] ?? jobs()['warrior'];
    $st = db()->prepare(
        'INSERT INTO users (username, pass, lv, exp, hp, maxhp, atk, def, gold, loc, weapon, armor, potion, job, quest, created_at) VALUES (?, ?, 1, 0, ?, ?, ?, ?, 20, "smithy", "", "", 2, ?, 0, ?)'
    );
    $st->execute([$name, password_hash($pass, PASSWORD_DEFAULT), $j['hp'], $j['hp'], $j['atk'], $j['def'], $job, time()]);
    $id = (int) db()->lastInsertId();
    // 出身武器直接发新装备并穿上
    $wnames = ['warrior' => '生锈铁剑', 'mage' => '桦木法杖', 'hunter' => '猎弓', 'priest' => '白木槌'];
    $wbase = $wnames[$job] ?? '生锈铁剑';
    make_equip($id, 'weapon', $wbase, 0, 1);
    $ins = db()->prepare('UPDATE equips SET pos="wear" WHERE uid=? AND slot=? ORDER BY id DESC LIMIT 1');
    $ins->execute([$id, 'weapon']);
    make_equip($id, 'body', '破布甲', 0, 1);
    $ins->execute([$id, 'body']);
    return $id;
}
