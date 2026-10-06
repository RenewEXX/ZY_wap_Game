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
            loc TEXT NOT NULL DEFAULT "gate",
            weapon TEXT NOT NULL DEFAULT "",
            armor TEXT NOT NULL DEFAULT "",
            potion INTEGER NOT NULL DEFAULT 1,
            created_at INTEGER NOT NULL
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
        'UPDATE users SET lv=?, exp=?, hp=?, maxhp=?, atk=?, def=?, gold=?, loc=?, weapon=?, armor=?, potion=? WHERE id=?'
    );
    $st->execute([
        $u['lv'], $u['exp'], $u['hp'], $u['maxhp'], $u['atk'], $u['def'],
        $u['gold'], $u['loc'], $u['weapon'], $u['armor'], $u['potion'], $u['id'],
    ]);
}

function user_create(string $name, string $pass): int
{
    $st = db()->prepare(
        'INSERT INTO users (username, pass, created_at) VALUES (?, ?, ?)'
    );
    $st->execute([$name, password_hash($pass, PASSWORD_DEFAULT), time()]);
    return (int) db()->lastInsertId();
}
