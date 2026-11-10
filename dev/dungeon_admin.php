<?php
declare(strict_types=1);
// ============================================================
// 副本内容后台（内部使用）
// ------------------------------------------------------------
// 打开：http://<服务器>/dev/dungeon_admin.php
// 功能：
//   · 列出所有副本（按“名字·房间”前缀分组），每个副本的每个房间及怪物、BOSS。
//   · 点任意怪物 → 查看完整详情（等级/属性/元素/技能/材料掉落/装备掉落含传说）。
//   · 在线编辑怪物：名字/生命/攻击/经验/金币/等级/元素，以及用 JSON 编辑 技能、材料掉落、装备掉落。
//   · 改动以“运行时覆盖”存入 dev_monster_override 表，立即生效；可一键“清除覆盖”还原。
//   · 没有覆盖记录时游戏行为与原版完全一致。
// ============================================================
require __DIR__ . '/../includes/bootstrap.php';

function dv_h($s): string { return h((string) $s); }
function dv_json($v): string { return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); }

// ---------- 保存 / 清除 ----------
$flash = '';
$flashErr = '';
// 保存传奇故事（纯文本覆盖）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_lore') {
    $lname = (string) ($_POST['lname'] ?? '');
    $ltext = (string) ($_POST['ltext'] ?? '');
    if ($lname !== '') {
        if (trim($ltext) === '') {
            dev_lore_delete($lname);
        } else {
            dev_lore_set($lname, $ltext);
        }
    }
    header('Location: dungeon_admin.php?mid=' . urlencode((string) ($_POST['mid'] ?? '')) . '&loresaved=1');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $mid = (string) ($_POST['mid'] ?? '');
    $base = monsters_base()[$mid] ?? null;
    if ($base === null) {
        $flashErr = '未知怪物 id：' . $mid;
    } elseif ($_POST['action'] === 'reset') {
        dev_ov_delete($mid);
        header('Location: dungeon_admin.php?mid=' . urlencode($mid) . '&reset=1');
        exit;
    } elseif ($_POST['action'] === 'save') {
        $data = [];
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name !== '' && $name !== (string) ($base['name'] ?? '')) {
            $data['name'] = $name;
        }
        foreach (['hp', 'atk', 'exp', 'gold'] as $f) {
            $v = $_POST[$f] ?? '';
            if ($v !== '' && is_numeric($v) && (int) $v !== (int) ($base[$f] ?? 0)) {
                $data[$f] = (int) $v;
            }
        }
        $lv = $_POST['lv'] ?? '';
        if ($lv !== '' && is_numeric($lv) && (int) $lv !== monster_lv_base($mid)) {
            $data['lv'] = (int) $lv;
        }
        $el = (string) ($_POST['el'] ?? '');
        if ($el !== '' && $el !== monster_element_base($mid)) {
            $data['el'] = $el;
        }
        // JSON 字段：留空=不覆盖；填 [] 或 null = 覆盖为空
        $jsonFields = [
            'skills'   => ['base' => monster_skills_base()[$mid] ?? null, 'label' => '技能'],
            'material' => ['base' => monster_material_base($mid), 'label' => '材料掉落'],
            'drops'    => ['base' => monster_drops_base($mid), 'label' => '装备掉落'],
        ];
        foreach ($jsonFields as $key => $meta) {
            $raw = trim((string) ($_POST[$key . '_json'] ?? ''));
            if ($raw === '') {
                continue; // 不覆盖
            }
            $val = json_decode($raw, true);
            if ($val === null && strtolower($raw) !== 'null') {
                $flashErr = $meta['label'] . ' 的 JSON 格式错误，未保存任何改动。';
                break;
            }
            if ($val != $meta['base']) {
                $data[$key] = $val;
            }
        }
        if ($flashErr === '') {
            if ($data === []) {
                dev_ov_delete($mid);
            } else {
                dev_ov_set($mid, $data);
            }
            header('Location: dungeon_admin.php?mid=' . urlencode($mid) . '&saved=1');
            exit;
        }
    }
}

// ---------- 数据准备 ----------
$monsters = monsters();        // 已叠加覆盖
$monBase = monsters_base();
$elList = function_exists('dragon_elements') ? dragon_elements() : ['fire', 'wind', 'ice', 'thunder', 'dark', 'light'];

// 副本分组：dungeon_maps() 返回的是一串“房间地图id”
$mapIds = dungeon_maps();
$allLoc = locations();
$groups = [];   // prefix => ['rooms'=>[...], 'bosses'=>[...], 'mons'=>set]
$monAppear = []; // mid => [mapName,...]
foreach ($mapIds as $mId) {
    $loc = $allLoc[$mId] ?? [];
    $nm = (string) ($loc['name'] ?? $mId);
    $prefix = str_contains($nm, '·') ? explode('·', $nm)[0] : $nm;
    $boss = function_exists('boss_of_map') ? (string) boss_of_map($mId) : '';
    $mons = (array) ($loc['monsters'] ?? []);
    $groups[$prefix]['rooms'][] = ['id' => $mId, 'name' => $nm, 'mons' => $mons, 'boss' => $boss];
    foreach ($mons as $mm) {
        $groups[$prefix]['mons'][$mm] = true;
        $monAppear[$mm][] = $nm;
    }
    if ($boss !== '') {
        $groups[$prefix]['bosses'][$boss] = true;
        $groups[$prefix]['mons'][$boss] = true;
        $monAppear[$boss][] = $nm . '（BOSS）';
    }
}

$selMid = (string) ($_GET['mid'] ?? '');
$showSaved = isset($_GET['saved']);
$showReset = isset($_GET['reset']);
$showLore = isset($_GET['loresaved']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>副本内容后台</title>
<style>
    * { box-sizing: border-box; }
    body { margin: 0; padding: 12px; background: #0f1115; color: #c9ccd1; font-family: ui-monospace, Consolas, "Microsoft YaHei", monospace; font-size: 13px; line-height: 1.5; }
    a { color: #6db3ff; text-decoration: none; } a:hover { text-decoration: underline; }
    h1 { font-size: 16px; color: #e8b84a; margin: 0 0 8px; }
    h2 { font-size: 14px; color: #7fb0ff; margin: 16px 0 6px; }
    .flash { padding: 8px 10px; border-radius: 6px; margin-bottom: 10px; }
    .flash.ok { background: #12351f; border: 1px solid #2f7d4a; color: #8ef0aa; }
    .flash.err { background: #3a1414; border: 1px solid #8a2b2b; color: #ff9a9a; }
    .grp { border: 1px solid #232a36; border-radius: 8px; margin: 8px 0; padding: 8px 10px; background: #12161d; }
    .grp .t { color: #e8b84a; font-weight: bold; }
    .room { padding: 3px 0 3px 10px; }
    .mon-link { color: #9cd0ff; }
    .boss { color: #ff8f6b; }
    .muted { color: #6b7280; }
    .ovmark { color: #ffd166; font-size: 11px; margin-left: 4px; }
    .box { background: #12161d; border: 1px solid #232a36; border-radius: 8px; padding: 10px; margin: 8px 0; }
    .kv { margin: 2px 0; } .kv b { color: #e8b84a; display: inline-block; min-width: 64px; }
    pre { background: #0c0f14; border: 1px solid #232a36; border-radius: 6px; padding: 8px; overflow: auto; margin: 4px 0; max-height: 260px; }
    input[type=text], input[type=number], select, textarea { background: #0c0f14; border: 1px solid #2a3140; border-radius: 6px; color: #c9ccd1; padding: 5px 7px; font-family: inherit; font-size: 13px; }
    textarea { width: 100%; min-height: 90px; }
    label.f { display: inline-block; margin: 4px 12px 4px 0; }
    label.f span { color: #9aa3ad; display: block; font-size: 11px; }
    button { background: #2a6df0; color: #fff; border: 0; padding: 7px 16px; border-radius: 6px; cursor: pointer; font-size: 13px; }
    button:hover { background: #3f80ff; }
    button.danger { background: #a02020; } button.danger:hover { background: #c62828; }
    .tabs a { margin-right: 12px; }
    .idx { max-height: 60vh; overflow: auto; columns: 2; column-gap: 16px; }
    .idx a { display: block; }
    .hint { color: #7f858e; font-size: 12px; }
</style>
</head>
<body>
<h1>副本内容后台</h1>
<div class="tabs">
    <a href="dungeon_admin.php">▸ 副本总览</a>
    <a href="#monidx">▸ 怪物索引</a>
    <span class="hint">改动写入运行时覆盖表 dev_monster_override，立即生效；“清除覆盖”可还原默认。</span>
</div>

<?php if ($showSaved): ?><div class="flash ok">✔ 已保存并生效。</div><?php endif; ?>
<?php if ($showReset): ?><div class="flash ok">✔ 已清除该怪物的覆盖，恢复默认。</div><?php endif; ?>
<?php if ($showLore): ?><div class="flash ok">✔ 传奇故事已保存。</div><?php endif; ?>
<?php if ($flashErr !== ''): ?><div class="flash err">✘ <?= dv_h($flashErr) ?></div><?php endif; ?>

<?php if ($selMid !== '' && isset($monBase[$selMid])):
    $ov = dev_ov_get($selMid);
    $eff = $monsters[$selMid] ?? $monBase[$selMid];
    $isBoss = function_exists('is_boss_mid') && is_boss_mid($selMid);
    $skBase = monster_skills_base()[$selMid] ?? null;
    $skEff = monster_skills()[$selMid] ?? null;
    $matBase = monster_material_base($selMid);
    $matEff = monster_material($selMid);
    $drBase = monster_drops_base($selMid);
    $drEff = monster_drops($selMid);
?>
    <h2>怪物详情 / 编辑：<?= dv_h($eff['name'] ?? $selMid) ?> <span class="muted">(<?= dv_h($selMid) ?>)</span></h2>
    <div class="box">
        <div class="kv"><b>等级</b><?= (int) monster_lv($selMid) ?> <span class="muted">(原始 <?= (int) monster_lv_base($selMid) ?>)</span>
            · <b>元素</b><?= dv_h(element_name(monster_element($selMid)) ?: '无') ?> <span class="muted">(原始 <?= dv_h(element_name(monster_element_base($selMid)) ?: '无') ?>)</span>
            · <b>类型</b><?= $isBoss ? '<span class="boss">BOSS</span>' : '小怪' ?></div>
        <div class="kv"><b>生命</b><?= (int) ($eff['hp'] ?? 0) ?> <span class="muted">(原始 <?= (int) ($monBase[$selMid]['hp'] ?? 0) ?>)</span>
            · <b>攻击</b><?= (int) ($eff['atk'] ?? 0) ?> <span class="muted">(原始 <?= (int) ($monBase[$selMid]['atk'] ?? 0) ?>)</span>
            · <b>经验</b><?= (int) ($eff['exp'] ?? 0) ?> · <b>金币</b><?= (int) ($eff['gold'] ?? 0) ?></div>
        <div class="kv"><b>出现于</b><?= dv_h(implode('、', array_unique($monAppear[$selMid] ?? []))) ?: '<span class="muted">（未在副本房间中登记）</span>' ?></div>
        <?php if ($ov !== []): ?><div class="kv"><span class="ovmark">● 该怪物已有覆盖，下列“现行”为覆盖后的值。</span></div><?php endif; ?>
    </div>

    <form method="post" class="box">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="mid" value="<?= dv_h($selMid) ?>">
        <div>
            <label class="f"><span>名字</span><input type="text" name="name" value="<?= dv_h($eff['name'] ?? '') ?>"></label>
            <label class="f"><span>等级</span><input type="number" name="lv" value="<?= (int) monster_lv($selMid) ?>"></label>
            <label class="f"><span>元素</span>
                <select name="el">
                    <option value="">（不覆盖）</option>
                    <?php foreach ($elList as $el): ?>
                        <option value="<?= dv_h($el) ?>" <?= monster_element($selMid) === $el ? 'selected' : '' ?>><?= dv_h(element_name($el)) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div>
            <label class="f"><span>生命 hp</span><input type="number" name="hp" value="<?= (int) ($eff['hp'] ?? 0) ?>"></label>
            <label class="f"><span>攻击 atk</span><input type="number" name="atk" value="<?= (int) ($eff['atk'] ?? 0) ?>"></label>
            <label class="f"><span>经验 exp</span><input type="number" name="exp" value="<?= (int) ($eff['exp'] ?? 0) ?>"></label>
            <label class="f"><span>金币 gold</span><input type="number" name="gold" value="<?= (int) ($eff['gold'] ?? 0) ?>"></label>
        </div>

        <div style="margin-top:8px">
            <label class="f" style="display:block"><span>技能（现行 JSON；改这里即可改怪物技能。留空=不改，填 [] 清空）</span></label>
            <textarea name="skills_json" placeholder="留空=不覆盖"><?= $skEff !== null ? dv_h(dv_json($skEff)) : '' ?></textarea>
            <details><summary class="hint">原始默认技能</summary><pre><?= dv_h($skBase !== null ? dv_json($skBase) : 'null') ?></pre></details>
        </div>

        <div style="margin-top:8px">
            <label class="f" style="display:block"><span>材料掉落（现行 JSON，形如 ["soul_fire","龙魂·火"]；留空=不改，[] 清空）</span></label>
            <textarea name="material_json" placeholder="留空=不覆盖"><?= dv_h(dv_json($matEff)) ?></textarea>
            <details><summary class="hint">原始默认材料</summary><pre><?= dv_h(dv_json($matBase)) ?></pre></details>
        </div>

        <div style="margin-top:8px">
            <label class="f" style="display:block"><span>装备掉落（现行 JSON；可改 slot/base/rate/品质权重q/legendary。留空=不改，[] 清空）</span></label>
            <textarea name="drops_json" placeholder="留空=不覆盖"><?= dv_h(dv_json($drEff)) ?></textarea>
            <details><summary class="hint">原始默认掉落</summary><pre><?= dv_h(dv_json($drBase)) ?></pre></details>
        </div>

        <div style="margin-top:10px">
            <button type="submit">保存并生效</button>
        </div>
    </form>

    <form method="post" style="margin:8px 0">
        <input type="hidden" name="action" value="reset">
        <input type="hidden" name="mid" value="<?= dv_h($selMid) ?>">
        <button type="submit" class="danger" onclick="return confirm('清除该怪物的所有覆盖，恢复默认？')">清除覆盖（恢复默认）</button>
    </form>

    <h2>装备掉落详情（解析）</h2>
    <?php
    $dr = is_array($drEff) ? $drEff : [];
    $hasDrop = isset($dr['slot']);
    $slotsMap = function_exists('equip_slots') ? equip_slots() : [];
    $slotRaw = $hasDrop ? (string) $dr['slot'] : '';
    $slotVal = $hasDrop ? ($slotsMap[$slotRaw] ?? null) : null;
    $slotLabel = is_array($slotVal) ? ($slotVal['name'] ?? $slotRaw) : (is_string($slotVal) ? $slotVal : $slotRaw);
    $dBase = (string) ($dr['base'] ?? '');
    $catalog = affix_catalog();
    $fmtN = function ($n): string {
        $s = rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    };
    $tierNames = ['T5', 'T4', 'T3', 'T2', 'T1'];
    $rangeHtml = function (string $id) use ($catalog, $tierNames, $fmtN): string {
        $t = $catalog[$id]['data']['tiers'] ?? [];
        if (!is_array($t) || $t === []) {
            if (function_exists('boss_legendary_effects')) {
                $be = boss_legendary_effects();
                if (isset($be[$id])) {
                    return 'BOSS专属特效（无档位）';
                }
            }
            return '—';
        }
        $parts = [];
        foreach ($t as $i => $r) {
            $parts[] = ($tierNames[$i] ?? ('T' . (5 - $i))) . ' ' . $fmtN($r[0] ?? 0) . '~' . $fmtN($r[1] ?? 0);
        }
        return implode('<br>', $parts);
    };
    $affName = function (string $id) use ($catalog): string {
        if (isset($catalog[$id]['name'])) {
            return (string) $catalog[$id]['name'];
        }
        // 词缀目录里没有的（如 BOSS 专属 lg_* 传说特效）：用其效果说明当名字
        if (function_exists('boss_legendary_effects')) {
            $be = boss_legendary_effects();
            if (isset($be[$id]['intro']) && $be[$id]['intro'] !== '') {
                return (string) $be[$id]['intro'];
            }
        }
        return $id;
    };
    $affGroup = function (string $id) use ($catalog): string {
        return (string) ($catalog[$id]['data']['group'] ?? '');
    };
    ?>
    <?php if ($hasDrop): ?>
        <?php
        $offKind = (string) ($dr['off'] ?? '');
        $primary = function_exists('equip_primary') ? equip_primary($slotRaw, $offKind, $dBase) : '';
        if ($slotRaw === 'weapon' && function_exists('weapon_sub') && in_array(weapon_sub($dBase), ['staff', 'relic'], true)) {
            $primary = 'matk';
        }
        if ($slotRaw === 'weapon' && function_exists('weapon_sub') && weapon_sub($dBase) === 'relic') {
            $primary = 'summon_power';
        }
        if ($slotRaw === 'offhand' && function_exists('summoner_offhands') && isset(summoner_offhands()[$dBase])) {
            $primary = 'summon_hp';
        }
        $lgK0 = (string) ($dr['legendary']['extra']['k'] ?? '');
        $pool = function_exists('equip_sub_pool') ? equip_sub_pool($slotRaw, $dBase, $primary, $lgK0, $offKind) : [];
        $dmgType = function_exists('equip_damage_type') ? equip_damage_type($slotRaw, $dBase, $offKind) : 'any';
        $twohand = ($slotRaw === 'weapon' && function_exists('weapon_is_twohand') && weapon_is_twohand($dBase));
        ?>
        <div class="box">
            <div class="kv"><b>部位</b><?= dv_h($slotLabel) ?> <span class="muted">(slot=<?= dv_h($slotRaw) ?>)</span>
                · <b>伤害路数</b><?= $dmgType === 'magic' ? '法系' : ($dmgType === 'phys' ? '物理' : '通用') ?><?= $twohand ? ' · <b>双手武器</b>（主词缀×2.2）' : '' ?></div>
            <div class="kv"><b>基础名</b><?= dv_h($dBase) ?> · <b>掉率</b><?= dv_h((string) ($dr['rate'] ?? '默认')) ?>
                · <b>品质权重</b><?= dv_h(json_encode($dr['q'] ?? [])) ?> <span class="muted">[白,绿,蓝,紫,橙]</span></div>
            <div class="kv"><b>主词条</b><?= dv_h($affName($primary)) ?> <span class="muted">(id=<?= dv_h($primary) ?>)</span></div>
        </div>

        <div class="box">
            <div class="kv"><b>品质阶梯</b>（装备名/词缀数/数值倍率/档位权重）</div>
            <table style="width:100%;border-collapse:collapse;font-size:12px">
                <tr>
                    <th style="text-align:left;border-bottom:1px solid #333;padding:2px">品质</th>
                    <th style="text-align:left;border-bottom:1px solid #333;padding:2px">显示名</th>
                    <th style="text-align:left;border-bottom:1px solid #333;padding:2px">词缀数</th>
                    <th style="text-align:left;border-bottom:1px solid #333;padding:2px">倍率</th>
                    <th style="text-align:left;border-bottom:1px solid #333;padding:2px">权重(T5→T1)</th>
                </tr>
                <?php foreach ([0, 1, 2, 3, 4] as $q):
                    $qcfg = function_exists('equip_quality_config') ? (equip_quality_config()[$q] ?? []) : [];
                    $qname = function_exists('equip_fullname') ? equip_fullname($dBase, $q) : $dBase;
                ?>
                <tr>
                    <td style="padding:2px"><?= $q ?></td>
                    <td style="padding:2px"><?= dv_h($qname) ?></td>
                    <td style="padding:2px"><?= function_exists('equip_affix_count') ? (int) equip_affix_count($q) : '?' ?></td>
                    <td style="padding:2px">×<?= dv_h($fmtN($qcfg['multiplier'] ?? 1)) ?></td>
                    <td style="padding:2px"><?= dv_h(implode('/', $qcfg['tiers'] ?? [])) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="box">
            <div class="kv"><b>副词缀池</b>（非主词条可随机出的词缀，共 <?= count($pool) ?> 个）</div>
            <?php if ($pool === []): ?>
                <div class="muted">（空）</div>
            <?php else: ?>
                <table style="width:100%;border-collapse:collapse;font-size:12px">
                    <tr>
                        <th style="text-align:left;border-bottom:1px solid #333;padding:2px">词缀</th>
                        <th style="text-align:left;border-bottom:1px solid #333;padding:2px">id</th>
                        <th style="text-align:left;border-bottom:1px solid #333;padding:2px">组</th>
                        <th style="text-align:left;border-bottom:1px solid #333;padding:2px">档位范围 T5→T1</th>
                    </tr>
                    <?php foreach ($pool as $aid): ?>
                        <tr>
                            <td style="padding:2px"><?= dv_h($affName($aid)) ?></td>
                            <td style="padding:2px" class="muted"><?= dv_h($aid) ?></td>
                            <td style="padding:2px" class="muted"><?= dv_h($affGroup($aid)) ?></td>
                            <td style="padding:2px"><?= $rangeHtml($aid) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>

        <?php if (isset($dr['legendary']['base'])):
            $lgb = (string) $dr['legendary']['base'];
            $lk = (string) ($dr['legendary']['extra']['k'] ?? '');
            $lv = $dr['legendary']['extra']['v'] ?? '';
            $story = legend_lore($lgb);
            $overrideKey = '';
            if (function_exists('boss_legendary_key_by_base')) {
                $overrideKey = (string) boss_legendary_key_by_base($dBase);
            }
        ?>
            <div class="box">
                <div class="kv"><b>传奇件</b><?= dv_h($lgb) ?>　<span class="muted">显示名：<?= dv_h(function_exists('equip_fullname') ? equip_fullname($lgb, 4) : $lgb) ?></span></div>
                <div class="kv"><b>传奇词缀</b><?= dv_h($affName($lk)) ?> <span class="muted">(id=<?= dv_h($lk) ?>)</span> · 数值 <?= dv_h((string) $lv) ?></div>
                <div class="kv muted">档位范围 T5→T1：<?= $rangeHtml($lk) ?></div>
                <?php if ($overrideKey !== ''): ?>
                    <div class="kv warn">BOSS 专属：以橙(传说)品质掉落时，会强制覆盖为 <?= dv_h($affName($overrideKey)) ?>（id=<?= dv_h($overrideKey) ?>，值=1）</div>
                <?php endif; ?>
                <form method="post" style="margin-top:6px">
                    <input type="hidden" name="action" value="save_lore">
                    <input type="hidden" name="mid" value="<?= dv_h($selMid) ?>">
                    <input type="hidden" name="lname" value="<?= dv_h($lgb) ?>">
                    <label class="f" style="display:block"><span>传奇故事（可编辑；留空=恢复默认故事）</span></label>
                    <textarea name="ltext" style="min-height:110px"><?= dv_h($story) ?></textarea>
                    <div style="margin-top:6px"><button type="submit">保存传奇故事</button></div>
                </form>
            </div>
        <?php endif; ?>

        <?php
        $setId = ($dBase !== '' && function_exists('equip_set_id')) ? (string) equip_set_id($dBase) : '';
        if ($setId !== '' && function_exists('equip_set_pieces')):
            $pieces = equip_set_pieces($setId);
            $cnt = is_array($pieces) ? count($pieces) : 0;
            $bonus = function_exists('equip_set_bonus_text') ? (string) equip_set_bonus_text($setId, $cnt) : '';
        ?>
            <div class="box">
                <div class="kv"><b>套装</b><?= dv_h($setId) ?>（共 <?= $cnt ?> 件）<?= (is_array($pieces) && in_array($dBase, $pieces, true)) ? ' · 本掉落属于该套装' : '' ?></div>
                <?php if ($bonus !== ''): ?><pre><?= dv_h($bonus) ?></pre><?php endif; ?>
                <?php if (is_array($pieces) && $pieces !== []): ?>
                    <details><summary class="hint">套装件清单（<?= $cnt ?> 件）</summary><pre><?= dv_h(implode("\n", $pieces)) ?></pre></details>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($dr['class_weapons']) && is_array($dr['class_weapons'])): ?>
            <div class="box"><div class="kv"><b>职业武器</b><?= dv_h(json_encode($dr['class_weapons'], JSON_UNESCAPED_UNICODE)) ?></div></div>
        <?php endif; ?>
    <?php else: ?>
        <div class="box muted">该怪物没有装备掉落（drops 为空或未配置）。</div>
    <?php endif; ?>

    <h2>词缀 id 参考（用于 legendary.extra.k）</h2>
    <div class="box">
        <?php $affNames = []; foreach ($catalog as $aid => $av) { $affNames[] = $aid . '(' . (string) ($av['name'] ?? '') . ')'; } ?>
        <pre><?= dv_h(implode('、', $affNames)) ?></pre>
    </div>

    <h2>掉落说明</h2>
    <div class="box hint">
        · <b>rate</b>=掉率(0~1)；<b>q</b>=品质权重数组 [白,绿,蓝,紫,橙]，会据此随机品质；<br>
        · <b>legendary</b> 可含 <code>{"base":"传说装备名","extra":{"k":"词条键","v":数值}}</code>，掉落时额外给传说件；<br>
        · <b>material</b> 形如 <code>["material_key","材料显示名"]</code>，击杀必掉 1 个；填 <code>[]</code> 取消材料掉落；<br>
        · 可用下面的“传说/装备参考”查已有 base 与传说名，避免拼写错误。
    </div>

    <h2>传说 / 装备掉落参考（全部怪物里出现过的）</h2>
    <?php
    $baseNames = []; $legendNames = [];
    foreach (array_keys($monBase) as $mm) {
        $d = monster_drops($mm);
        if (is_array($d) && $d !== []) {
            if (isset($d['base'])) { $baseNames[(string) $d['base']] = true; }
            if (isset($d['legendary']['base'])) { $legendNames[(string) $d['legendary']['base']] = true; }
            if (isset($d['class_weapons']) && is_array($d['class_weapons'])) { foreach ($d['class_weapons'] as $bn) { $baseNames[(string) $bn] = true; } }
        }
    }
    ?>
    <div class="box">
        <div class="kv"><b>装备 base</b> <span class="hint">（<?= count($baseNames) ?> 个）</span></div>
        <pre><?= dv_h(implode('、', array_keys($baseNames))) ?></pre>
        <div class="kv"><b>传说 base</b> <span class="hint">（<?= count($legendNames) ?> 个）</span></div>
        <pre><?= dv_h(implode('、', array_keys($legendNames))) ?></pre>
    </div>
    <p><a href="dungeon_admin.php">← 返回副本总览</a></p>
<?php elseif ($selMid !== ''): ?>
    <div class="flash err">✘ 没有这个怪物：<?= dv_h($selMid) ?></div>
<?php endif; ?>

<?php if ($selMid === ''): ?>
    <h2>副本总览（共 <?= count($groups) ?> 个副本区域 / <?= count($mapIds) ?> 个房间）</h2>
    <?php foreach ($groups as $prefix => $g): ?>
        <div class="grp">
            <div class="t"><?= dv_h($prefix) ?>
                <span class="muted">（<?= count($g['rooms'] ?? []) ?> 房间 · <?= count($g['mons'] ?? []) ?> 种怪）</span>
            </div>
            <?php foreach (($g['rooms'] ?? []) as $room): ?>
                <div class="room">
                    <span class="muted"><?= dv_h($room['name']) ?>：</span>
                    <?php
                    $links = [];
                    foreach ($room['mons'] as $mm) {
                        $mnm = $monsters[$mm]['name'] ?? $mm;
                        $links[] = '<a class="mon-link" href="dungeon_admin.php?mid=' . dv_h($mm) . '">' . dv_h($mnm) . '</a>';
                    }
                    if ($room['boss'] !== '') {
                        $bm = $room['boss'];
                        $bmn = $monsters[$bm]['name'] ?? $bm;
                        $links[] = '<a class="boss" href="dungeon_admin.php?mid=' . dv_h($bm) . '">★' . dv_h($bmn) . '</a>';
                    }
                    echo $links === [] ? '<span class="muted">（无怪）</span>' : implode('、', $links);
                    ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <h2 id="monidx">怪物索引（全部 <?= count($monsters) ?> 种）</h2>
    <input class="search" id="monSearch" placeholder="搜索名字或 id…" style="width:100%;padding:6px 8px;margin-bottom:8px;background:#0c0f14;border:1px solid #2a3140;border-radius:6px;color:#c9ccd1">
    <div class="idx" id="monIdx">
        <?php foreach ($monsters as $mm => $mv): ?>
            <a href="dungeon_admin.php?mid=<?= dv_h($mm) ?>" data-k="<?= dv_h(($mv['name'] ?? $mm) . ' ' . $mm) ?>">
                <?= dv_h($mv['name'] ?? $mm) ?> <span class="muted">(<?= dv_h($mm) ?> · Lv<?= (int) monster_lv($mm) ?>)</span><?= dev_ov_get($mm) !== [] ? '<span class="ovmark">●</span>' : '' ?>
            </a>
        <?php endforeach; ?>
    </div>
    <script>
    (function(){
        var s=document.getElementById('monSearch'), box=document.getElementById('monIdx');
        if(!s) return;
        s.addEventListener('input',function(){
            var kw=s.value.trim().toLowerCase();
            box.querySelectorAll('a').forEach(function(a){
                var t=(a.getAttribute('data-k')||'').toLowerCase();
                a.style.display=(kw===''||t.indexOf(kw)>=0)?'':'none';
            });
        });
    })();
    </script>
<?php endif; ?>
</body>
</html>
