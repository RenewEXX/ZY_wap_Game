<?php
declare(strict_types=1);

// ============================================================
// 运行时内容覆盖层（给 dev/ 后台用）
// ------------------------------------------------------------
// 游戏数据（怪物/技能/掉落）原本写死在 PHP 里。这里新增一张 dev_monster_override 表，
// 后台把改动以 JSON 存进去，再用“包装函数”把改动叠加到核心访问函数上。
// ★ 没有任何覆盖记录时，行为与原版 100% 一致（纯透传），不会影响现有玩法与测试。
// ============================================================

function dev_ov_table(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        db()->exec('CREATE TABLE IF NOT EXISTS dev_monster_override (mid TEXT PRIMARY KEY, data TEXT NOT NULL, updated_at INTEGER)');
    } catch (Throwable $e) {
    }
}

function dev_ov_bust(): void
{
    unset($GLOBALS['__dev_ov']);
}

// 全部覆盖：mid => [name?, hp?, atk?, exp?, gold?, lv?, el?, skills?, material?, drops?]
function dev_ov_all(): array
{
    if (isset($GLOBALS['__dev_ov'])) {
        return $GLOBALS['__dev_ov'];
    }
    dev_ov_table();
    $arr = [];
    try {
        foreach (db()->query('SELECT mid, data FROM dev_monster_override') as $r) {
            $j = json_decode((string) $r['data'], true);
            if (is_array($j)) {
                $arr[(string) $r['mid']] = $j;
            }
        }
    } catch (Throwable $e) {
    }
    return $GLOBALS['__dev_ov'] = $arr;
}

function dev_ov_get(string $mid): array
{
    $all = dev_ov_all();
    return isset($all[$mid]) && is_array($all[$mid]) ? $all[$mid] : [];
}

function dev_ov_set(string $mid, array $data): void
{
    dev_ov_table();
    try {
        db()->prepare('INSERT OR REPLACE INTO dev_monster_override (mid, data, updated_at) VALUES (?,?,?)')
            ->execute([$mid, json_encode($data, JSON_UNESCAPED_UNICODE), time()]);
    } catch (Throwable $e) {
    }
    dev_ov_bust();
}

function dev_ov_delete(string $mid): void
{
    dev_ov_table();
    try {
        db()->prepare('DELETE FROM dev_monster_override WHERE mid=?')->execute([$mid]);
    } catch (Throwable $e) {
    }
    dev_ov_bust();
}

// ---------- 传奇故事覆盖（name => 故事文本）----------
function dev_lore_table(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        db()->exec('CREATE TABLE IF NOT EXISTS dev_lore (name TEXT PRIMARY KEY, text TEXT NOT NULL, updated_at INTEGER)');
    } catch (Throwable $e) {
    }
}

function dev_lore_bust(): void
{
    unset($GLOBALS['__dev_lore']);
}

function dev_lore_all(): array
{
    if (isset($GLOBALS['__dev_lore'])) {
        return $GLOBALS['__dev_lore'];
    }
    dev_lore_table();
    $arr = [];
    try {
        foreach (db()->query('SELECT name, text FROM dev_lore') as $r) {
            $arr[(string) $r['name']] = (string) $r['text'];
        }
    } catch (Throwable $e) {
    }
    return $GLOBALS['__dev_lore'] = $arr;
}

function dev_lore_set(string $name, string $text): void
{
    dev_lore_table();
    try {
        db()->prepare('INSERT OR REPLACE INTO dev_lore (name, text, updated_at) VALUES (?,?,?)')->execute([$name, $text, time()]);
    } catch (Throwable $e) {
    }
    dev_lore_bust();
}

function dev_lore_delete(string $name): void
{
    dev_lore_table();
    try {
        db()->prepare('DELETE FROM dev_lore WHERE name=?')->execute([$name]);
    } catch (Throwable $e) {
    }
    dev_lore_bust();
}

// 传奇故事：有覆盖用覆盖，否则走原版
function legend_lore(string $shortName): string
{
    $ov = dev_lore_all();
    if (isset($ov[$shortName]) && $ov[$shortName] !== '') {
        return $ov[$shortName];
    }
    return legend_lore_base($shortName);
}

// ---------------- 包装函数（覆盖生效点）----------------

function monsters(): array
{
    $m = monsters_base();
    $ov = dev_ov_all();
    if ($ov === []) {
        return $m;
    }
    foreach ($ov as $mid => $o) {
        if (!isset($m[$mid]) || !is_array($o)) {
            continue;
        }
        foreach (['name', 'hp', 'atk', 'exp', 'gold'] as $k) {
            if (array_key_exists($k, $o)) {
                $m[$mid][$k] = $o[$k];
            }
        }
    }
    return $m;
}

function monster_lv(string $mid): int
{
    $o = dev_ov_get($mid);
    if (isset($o['lv'])) {
        return (int) $o['lv'];
    }
    return monster_lv_base($mid);
}

function monster_element(string $mid): string
{
    $o = dev_ov_get($mid);
    if (isset($o['el']) && (string) $o['el'] !== '') {
        return (string) $o['el'];
    }
    return monster_element_base($mid);
}

function monster_skills(): array
{
    $s = monster_skills_base();
    $ov = dev_ov_all();
    if ($ov === []) {
        return $s;
    }
    foreach ($ov as $mid => $o) {
        if (isset($o['skills']) && is_array($o['skills'])) {
            $s[$mid] = $o['skills'];
        }
    }
    return $s;
}

function monster_material(string $mid): array
{
    $o = dev_ov_get($mid);
    if (array_key_exists('material', $o)) {
        return is_array($o['material']) ? $o['material'] : [];
    }
    return monster_material_base($mid);
}

function monster_drops(string $mid): array
{
    $o = dev_ov_get($mid);
    if (array_key_exists('drops', $o)) {
        return is_array($o['drops']) ? $o['drops'] : [];
    }
    return monster_drops_base($mid);
}
