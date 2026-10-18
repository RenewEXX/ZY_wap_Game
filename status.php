<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
sin_sync_hp((int) $u['id']);
$u = user_by_id((int) $u['id']);
$here = loc((string) $u['loc']);

wap_start('状态');
echo sin_name_html((string) $u['username'], sin_of((int) $u['id'])) . '　' . h(lv_text((int) $u['id'], (int) $u['lv']));
echo '　' . h(job_of($u)['name']) . '(' . h(job_of($u)['skill']) . ')';
echo '<br>';
echo '罪值 ' . sin_of((int) $u['id']) . (sin_of((int) $u['id']) >= 100 ? '（<span style="color:#f66">恶贯满盈，任何人都可强制PK你</span>）' : (sin_of((int) $u['id']) < 10 ? '（<span style="color:#9c9">心地纯善，生命+20%</span>）' : '')) . '<br>';
echo '<span class="hp">生命 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</span><br>';
echo '<span style="color:#6cf">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . '</span><br>';
echo '物攻 ' . player_patk($u) . '　法攻 ' . player_matk($u) . '　物防 ' . player_pdef($u) . '　法防 ' . player_mdef($u) . '<br>';
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '经验 ' . (int) $u['exp'] . '/' . exp_need((int) $u['lv']) . '<br>';
$st = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="wear"');
$st->execute([(int) $u['id']]);
$worn = equip_sort_by_slot($st->fetchAll());
if ($worn !== []) {
    echo '穿戴：<br>';
    foreach ($worn as $e) {
        echo '·[' . h(equip_slots()[$e['slot']] ?? '') . ']' . ((($e['slot'] ?? '') === 'title') ? '' : enhance_tag_html((int) ($e['enhance_level'] ?? 0))) . '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . equip_color($e) . '">' . h(equip_shortname($e['name'])) . '</span></a><br>';
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
$rsN = rs_count((int) $u['id']);
echo '<div class="hr">--------</div>【转生】<br>';
echo '转生次数：' . $rsN . '/' . rs_max() . '　等级上限：' . lv_cap() . '<br>';
if ($rsN > 0) {
    echo '当前加成：基础属性+' . ($rsN * 10) . '%、每级成长×2、经验+50%、掉落+20%、金币+30%<br>';
    echo '源罪点数：' . src_sin_point((int) $u['id']) . '<br>';
}
echo '转生条件：' . h(rs_require_text((int) $u['id'])) . '<br>';
echo '<a href="rank.php?tab=rs">转生榜</a> · <a href="rank.php?tab=rs&view=tree">源罪技能树</a><br>';
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
if ((string) ($_GET['a'] ?? '') === 'renamedo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    flash_set(use_rename_card((int) $u['id'], (string) ($_POST['newname'] ?? '')));
    header('Location: status.php');
    exit;
}
$stFlash = flash_get();
if ($stFlash !== '') {
    echo '<div class="warn">' . h($stFlash) . '</div>';
}
if ((string) ($_GET['a'] ?? '') === 'rename') {
    echo '<div class="hr">--------</div>【改名】当前：' . h($u['username']) . '（本大区唯一）<br>';
    $rc = (int) (mats_of((int) $u['id'])['rename_card'] ?? 0);
    if ($rc <= 0) {
        echo '<span class="muted">没有改名卡，去<a href="mall.php">商城·功能道具</a>买（100魔钻/张）。</span><br>';
    } else {
        echo '剩改名卡x' . $rc . '<br>';
        echo '<form method="post" action="status.php?a=renamedo">';
        echo '新名<br><input name="newname" maxlength="12" required><br><br>';
        echo '<input type="submit" value="确认改名">';
        echo '</form>';
    }
}
echo '<div class="hr">--------</div>【属性加点】<br>';
echo '潜力：存' . (int) ($u['s_pts'] ?? 0) . '点（全部1点1次）<br>';
echo '力量(物攻，法师牧师无效)：' . (int) ($u['str'] ?? 0) . ' <a href="status.php?a=alloc&k=str">+速加</a><br>';
echo '智慧(法攻)：' . (int) ($u['int'] ?? 0) . ' <a href="status.php?a=alloc&k=int">+速加</a><br>';
echo '坚毅(物防法防)：' . (int) ($u['agi'] ?? 0) . ' <a href="status.php?a=alloc&k=agi">+速加</a><br>';
echo '体质(生命)：' . (int) ($u['vit'] ?? 0) . ' <a href="status.php?a=alloc&k=vit">+速加</a><br>';
echo '灵力(魔力)：' . (int) ($u['spi'] ?? 0) . ' <a href="status.php?a=alloc&k=spi">+速加</a><br>';
echo '快速：<a href="status.php?a=allocall&k=str">全加力量</a> <a href="status.php?a=allocall&k=int">全加智慧</a> <a href="status.php?a=allocall&k=agi">全加坚毅</a> <a href="status.php?a=allocall&k=vit">全加体质</a> <a href="status.php?a=allocall&k=spi">全加灵力</a><br>';
echo '<div class="hr">--------</div>';
echo '<a href="status.php?a=all">查看全部属性</a><br>';
if ((string) ($_GET['a'] ?? '') === 'all') {
    $gs = gear_stats((int) $u['id']);
    echo '<div class="hr">--------</div>【全部属性】<br>';
    echo '攻击' . player_atk($u) . '<br>';
    echo '防御' . player_def($u) . '<br>';
    echo '物攻' . player_patk($u) . ' 法攻' . player_matk($u) . ' 物防' . player_pdef($u) . ' 法防' . player_mdef($u) . '<br>';
    echo '生命' . (int) $u['maxhp'] . '<br>';
    echo '魔力' . (int) ($u['maxmp'] ?? 0) . '<br>';
    echo '四维：力' . (int) ($u['str'] ?? 0) . ' 毅' . (int) ($u['agi'] ?? 0) . ' 体' . (int) ($u['vit'] ?? 0) . ' 智' . (int) ($u['int'] ?? 0) . '<br>';
    foreach (['atk_pct', 'matk_pct', 'precision', 'summon_power', 'summon_hp', 'matk', 'mdef', 'crit', 'critdmg', 'speed', 'element', 'lifesteal', 'penetration', 'skill_damage', 'execute', 'damage_reduction', 'resist', 'thorns', 'regen', 'cooldown', 'hit', 'exp_gain', 'dodge', 'move_speed', 'control_resist', 'slow_resist', 'all_attr', 'gold_gain', 'magic_find', 'res_light', 'res_dark', 'res_fire', 'res_wind', 'res_ice', 'res_thunder', 'allres'] as $k) {
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
echo '所在：' . h($here['name']);
nav_line();
wap_end(false);
