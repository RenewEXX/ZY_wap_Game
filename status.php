<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$here = loc((string) $u['loc']);

wap_start('状态');
echo h($u['username']) . '　' . (int) $u['lv'] . '级';
echo '　' . h(job_of($u)['name']) . '(' . h(job_of($u)['skill']) . ')';
echo '<br>';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span><br>';
echo '<span style="color:#6cf">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . '</span><br>';
echo '攻击 ' . player_atk($u) . '　防御 ' . player_def($u) . '<br>';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '经验 ' . (int) $u['exp'] . '/' . exp_need((int) $u['lv']) . '<br>';
$st = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="wear"');
$st->execute([(int) $u['id']]);
$worn = $st->fetchAll();
if ($worn !== []) {
    echo '穿戴：<br>';
    $qcolor = ['#999', '#fff', '#6cf', '#c6f', '#fc3'];
    foreach ($worn as $e) {
        echo '·[' . h(equip_slots()[$e['slot']] ?? '') . ']' . enhance_tag_html((int) ($e['enhance_level'] ?? 0)) . '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . $qcolor[(int) $e['quality']] . '">' . h(equip_shortname($e['name'])) . '</span></a><br>';
    }
    $gs = gear_stats((int) $u['id']);
    echo '<span class="muted">共加成：攻+' . (int) $gs['atk'] . ' 防+' . (int) $gs['def'] . ' 命+' . (int) $gs['hp'];
    foreach (['crit' => '暴击', 'critdmg' => '爆伤', 'dodge' => '闪避', 'lifesteal' => '吸血'] as $k => $n) {
        if ($gs[$k] > 0) {
            echo ' ' . $n . $gs[$k] . '%';
        }
    }
    echo '</span><br>';
}
if ((string) ($_GET['a'] ?? '') === 'alloc') {
    flash_set(alloc_stat((int) $u['id'], (string) ($_GET['k'] ?? '')));
    header('Location: status.php?a=all');
    exit;
}
if ((string) ($_GET['a'] ?? '') === 'allocall') {
    flash_set(alloc_all_stat((int) $u['id'], (string) ($_GET['k'] ?? '')));
    header('Location: status.php?a=all');
    exit;
}
if ((string) ($_GET['a'] ?? '') === 'reset') {
    flash_set(use_reset_potion((int) $u['id']));
    header('Location: status.php?a=all');
    exit;
}
$stFlash = flash_get();
if ($stFlash !== '') {
    echo '<div class="warn">' . h($stFlash) . '</div>';
}
echo '<div class="hr">--------</div>【属性加点】<br>';
echo '潜力：存' . (int) ($u['s_pts'] ?? 0) . '点（力量/坚毅3点1次，体质/智慧1点1次）<br>';
echo '力量(影响攻击)：' . (int) ($u['str'] ?? 0) . ' <a href="status.php?a=alloc&k=str">+速加</a><br>';
echo '坚毅(影响防御)：' . (int) ($u['agi'] ?? 0) . ' <a href="status.php?a=alloc&k=agi">+速加</a><br>';
echo '体质(影响生命)：' . (int) ($u['vit'] ?? 0) . ' <a href="status.php?a=alloc&k=vit">+速加</a><br>';
echo '智慧(影响魔力)：' . (int) ($u['int'] ?? 0) . ' <a href="status.php?a=alloc&k=int">+速加</a><br>';
echo '快速：<a href="status.php?a=allocall&k=str">全加力量</a> <a href="status.php?a=allocall&k=agi">全加坚毅</a> <a href="status.php?a=allocall&k=vit">全加体质</a> <a href="status.php?a=allocall&k=int">全加智慧</a><br>';
echo '<span class="muted">注意：强力装备有属性点要求，不要只加一种。可用<a href="bag.php?tab=other">洗点药</a>重修。</span><br>';
echo '<div class="hr">--------</div>';
echo '<a href="status.php?a=all">查看全部属性</a><br>';
if ((string) ($_GET['a'] ?? '') === 'all') {
    $gs = gear_stats((int) $u['id']);
    echo '<div class="hr">--------</div>【全部属性】<br>';
    echo '攻击' . player_atk($u) . '<br>';
    echo '防御' . player_def($u) . '<br>';
    echo '生命' . (int) $u['maxhp'] . '<br>';
    echo '魔力' . (int) ($u['maxmp'] ?? 0) . '<br>';
    echo '四维：力' . (int) ($u['str'] ?? 0) . ' 毅' . (int) ($u['agi'] ?? 0) . ' 体' . (int) ($u['vit'] ?? 0) . ' 智' . (int) ($u['int'] ?? 0) . '<br>';
    foreach (['atk_pct', 'crit', 'critdmg', 'speed', 'element', 'lifesteal', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'thorns', 'regen', 'cooldown', 'hit', 'exp_gain', 'dodge', 'move_speed', 'control_resist', 'slow_resist', 'all_attr', 'gold_gain', 'magic_find', 'res_light', 'res_dark', 'res_fire', 'res_wind', 'res_ice', 'res_thunder', 'allres'] as $k) {
        if ((float) ($gs[$k] ?? 0) == 0.0) {
            continue;
        }
        echo h(affix_fmt($k, (float) $gs[$k])) . '<br>';
    }
    echo '属性攻击(附魔，技能对应属性生效)：';
    $els = [];
    foreach (['light', 'dark', 'fire', 'wind', 'ice', 'thunder'] as $eel) {
        $els[] = element_name($eel) . enchant_el_dmg((int) $u['id'], $eel);
    }
    echo h(implode(' ', $els)) . '<br>';
    echo '<div class="hr">--------</div>';
}
echo '回血药：' . (int) $u['potion'] . '<br>';
echo '所在：' . h($here['name']);
nav_line();
wap_end(false);
