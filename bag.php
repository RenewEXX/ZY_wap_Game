<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$tab = (string) ($_GET['tab'] ?? 'equip');
if (!in_array($tab, ['equip', 'mat', 'quest', 'other'], true)) {
    $tab = 'equip';
}

if ($a === 'drink') {
    if (peak_arena_of((string) ($u['loc'] ?? '')) !== '') {
        flash_set('巅峰战场禁药！全靠刀硬砍。');
    } elseif ((int) $u['potion'] <= 0) {
        flash_set('药空了。');
    } elseif ((int) $u['hp'] >= (int) $u['maxhp']) {
        flash_set('你没有伤。');
    } else {
        $u['potion'] = (int) $u['potion'] - 1;
        $heal = min(20, (int) $u['maxhp'] - (int) $u['hp']);
        $u['hp'] = (int) $u['hp'] + $heal;
        user_save($u);
        flash_set('回复' . $heal . '点生命。');
    }
    header('Location: bag.php?tab=other');
    exit;
}
if ($a === 'wear') {
    flash_set(wear_equip((int) $u['id'], (int) ($_GET['id'] ?? 0)));
    header('Location: bag.php?tab=equip');
    exit;
}
if ($a === 'off') {
    flash_set(take_off_equip((int) $u['id'], (int) ($_GET['id'] ?? 0)));
    header('Location: bag.php?tab=equip');
    exit;
}
if ($a === 'sellall') {
    $st = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="" AND quality<=2');
    $st->execute([(int) $u['id']]);
    $tot = 0;
    $cnt = 0;
    while ($eq = $st->fetch()) {
        $tot += equip_sell_price((int) $eq['quality']);
        $cnt++;
        db()->exec('DELETE FROM equips WHERE id=' . (int) $eq['id']);
    }
    _gear_uncache((int) $u['id']);
    $u['gold'] = (int) $u['gold'] + $tot;
    user_save($u);
    flash_set($cnt > 0 ? '一键卖掉' . $cnt . '件稀有及以下装备，+' . fmt_money($tot) . '。' : '没有可卖的（只卖未穿戴的稀有及以下）。');
    header('Location: bag.php?tab=equip');
    exit;
}
if ($a === 'useext') {
    flash_set(use_bag_ext((int) $u['id'], (string) ($_GET['id'] ?? '')));
    header('Location: bag.php?tab=other');
    exit;
}
if ($a === 'sell') {
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=? AND pos=""');
    $st->execute([(int) ($_GET['id'] ?? 0), (int) $u['id']]);
    $eq = $st->fetch();
    if (!$eq) {
        flash_set('穿着的不能卖，先脱下来。');
    } else {
        $p = equip_sell_price((int) $eq['quality']);
        db()->exec('DELETE FROM equips WHERE id=' . (int) $eq['id']);
        $u['gold'] = (int) $u['gold'] + $p;
        user_save($u);
        flash_set('卖了【' . equip_shortname($eq['name']) . '】，+' . fmt_money($p) . '。');
    }
    header('Location: bag.php?tab=equip');
    exit;
}
if ($a === 'drop') {
    $did = (string) ($_GET['id'] ?? '');
    $dtab = (string) ($_GET['tab'] ?? 'mat');
    if (!in_array($dtab, ['mat', 'quest', 'other'], true)) {
        $dtab = 'mat';
    }
    $st = db()->prepare('SELECT num FROM mats WHERE uid=? AND mat=?');
    $st->execute([(int) $u['id'], $did]);
    $dn = (int) ($st->fetchColumn() ?: 0);
    if ($dn > 0) {
        ground_place_mat((string) ($u['loc'] ?? 'town_sq'), $did, $dn);
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], $did]);
        flash_set('扔地上了【' . mat_name($did) . '】x' . $dn . '（1分钟后消失）。');
    } else {
        flash_set('没有这个东西。');
    }
    header('Location: bag.php?tab=' . $dtab);
    exit;
}
if ($a === 'dropequip') {
    $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=? AND pos=""');
    $st->execute([(int) ($_GET['id'] ?? 0), (int) $u['id']]);
    $eq = $st->fetch();
    if (!$eq) {
        flash_set('穿着的不能扔，先脱下来。');
    } else {
        db()->prepare('UPDATE equips SET uid=0, pos="ground" WHERE id=?')->execute([(int) $eq['id']]);
        ground_place_equip((string) ($u['loc'] ?? 'town_sq'), (int) $eq['id']);
        _gear_uncache((int) $u['id']);
        flash_set('扔地上了【' . equip_shortname((string) $eq['name']) . '】（1分钟后消失）。');
    }
    header('Location: bag.php?tab=equip');
    exit;
}
if ($a === 'dummy') {
    if ((string) ($_GET['v'] ?? '') === '1') {
        $dmats = mats_of((int) $u['id']);
        if (empty($dmats['dummy_time'])) {
            flash_set('人偶没时间，先去商城买。');
        } else {
            mat_set((int) $u['id'], 'dummy_on', 1);
            mat_set((int) $u['id'], 'dummy_last', time());
            mat_set((int) $u['id'], 'dummy_acc', 0);
            flash_set('陪练人偶启动：每30秒自动群殴6只当前地图的怪。');
        }
    } else {
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], 'dummy_on']);
        flash_set('陪练人偶停止。');
    }
    header('Location: bag.php?tab=other');
    exit;
}
if ($a === 'usebook') {
    flash_set(use_skill_book((int) $u['id'], (string) ($_GET['id'] ?? '')));
    header('Location: skills.php');
    exit;
}
if ($a === 'usecard') {
    flash_set(use_exp_card((int) $u['id']));
    header('Location: bag.php?tab=other');
    exit;
}
if ($a === 'usereset') {
    flash_set(use_reset_potion((int) $u['id']));
    header('Location: status.php?a=all');
    exit;
}
if ($a === 'usedragon') {
    flash_set(use_dragon_pill((int) $u['id']));
    header('Location: status.php?a=all');
    exit;
}
if ($a === 'synth') {
    flash_set(synth_enchant((int) $u['id'], (string) ($_GET['id'] ?? '')));
    header('Location: bag.php?tab=mat');
    exit;
}

wap_start('背包');
echo '<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
echo '容量：' . bag_count((int) $u['id']) . '/' . bag_size((int) $u['id']) . (bag_full((int) $u['id']) ? '<span style="color:#f00">（满了！新装备会烂地上）</span>' : '') . ' <a href="bag.php?a=sellall">一键卖稀有及以下</a><br>';
echo '<a href="bag.php?tab=equip">装备</a> . ';
echo '<a href="bag.php?tab=mat">材料</a> . ';
echo '<a href="bag.php?tab=quest">任务</a> . ';
echo '<a href="bag.php?tab=other">其他</a>';
echo '<div class="hr">--------</div>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
if ($tab === 'equip') {
    $eqs = equip_sort_by_slot(my_equips((int) $u['id']));
    echo '身上：<br>';
    $slots = equip_slots();
    $wornBy = [];
    $rings = [];
    foreach ($eqs as $e) {
        if ($e['pos'] !== 'wear') {
            continue;
        }
        if ($e['slot'] === 'ring') {
            $rings[] = $e;
        } else {
            $wornBy[$e['slot']] = $e;
        }
    }
    foreach ($slots as $sid => $sname) {
        if ($sid === 'ring') {
            for ($i = 0; $i < 2; $i++) {
                $r = $rings[$i] ?? null;
                echo $sname . ($i + 1) . '：' . ($r ? enhance_tag_html((int) ($r['enhance_level'] ?? 0)) . '<a href="equip.php?id=' . $r['id'] . '"><span style="color:' . equip_color($r) . '">' . h(equip_shortname($r['name'])) . '</span></a>' : '空') . '<br>';
            }
            continue;
        }
        $e = $wornBy[$sid] ?? null;
        echo $sname . '：' . ($e ? enhance_tag_html((int) ($e['enhance_level'] ?? 0)) . '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . equip_color($e) . '">' . h(equip_shortname($e['name'])) . '</span></a>' : '空') . '<br>';
    }
    echo '<div class="hr">--------</div>';
    echo '库存：<br>';
    $has = false;
    foreach ($eqs as $e) {
        if ($e['pos'] === 'wear') {
            continue;
        }
        $has = true;
        echo enhance_tag_html((int) ($e['enhance_level'] ?? 0)) . '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . equip_color($e) . '">' . h(equip_shortname($e['name'])) . '</span></a>[' . h($slots[$e['slot']] ?? '') . ']';
        if ((int) ($e['req_lv'] ?? 1) > 1 || (int) ($e['req_str'] ?? 0) > 0 || (int) ($e['req_agi'] ?? 0) > 0) {
            echo '（' . h(equip_req_text($e)) . '）';
        }
        echo ' <a href="bag.php?a=wear&id=' . $e['id'] . '">穿</a> <a href="bag.php?a=sell&id=' . $e['id'] . '">卖' . h(fmt_money(equip_sell_price((int) $e['quality']))) . '</a> <a href="bag.php?a=dropequip&id=' . $e['id'] . '">扔</a>';
        if (my_guild((int) $u['id'])) {
            echo ' <a href="guild.php?a=wput&kind=equip&eid=' . $e['id'] . '">存公会</a>';
        }
        echo '<br>';
    }
    if (!$has) {
        echo '<span class="muted">空。去刷怪掉装备吧。</span>';
    }
} elseif ($tab === 'mat') {
    $mats = mats_of((int) $u['id']);
    $qmats = quest_mats();
    $any = false;
    $junk = [];
    foreach ($mats as $mid => $num) {
        if (isset($qmats[$mid]) || isset(mall_tanks()[$mid]) || mat_hidden($mid) || is_usable_item($mid)) {
            continue;
        }
        if (!is_craft_mat($mid)) {
            $junk[$mid] = $num;
            continue;
        }
        $any = true;
        echo '·' . h(mat_name($mid)) . 'x' . $num . ' <a href="bag.php?a=drop&id=' . h($mid) . '&tab=mat">扔</a>';
        if (my_guild((int) $u['id']) && is_tradable_mat($mid)) {
            echo ' <a href="guild.php?a=wput&kind=mat&mid=' . h($mid) . '&n=' . $num . '">全存公会</a>';
        }
        $et = enchant_mat_tier($mid);
        if ($et !== '' && (enchant_tiers()[$et]['next'] ?? '') !== '' && $num >= enchant_tiers()[$et]['need']) {
            echo ' <a href="bag.php?a=synth&id=' . h($mid) . '">合成' . h(enchant_tiers()[enchant_tiers()[$et]['next']]['name']) . '</a>';
        }
        echo '<br>';
    }
    if (!$any) {
        echo '<span class="muted">空。刷怪会掉材料。</span>';
    }
    if ($junk !== []) {
        echo '<div class="hr">--------</div>';
        echo '<span class="muted">旧物杂物（已绝版，无用途，可扔）：</span><br>';
        foreach ($junk as $mid => $num) {
            echo '<span class="muted">·' . h(mat_name($mid)) . 'x' . $num . '</span> <a href="bag.php?a=drop&id=' . h($mid) . '&tab=mat">扔</a><br>';
        }
    }
} elseif ($tab === 'quest') {
    $qs = quest_state($u);
    echo '当前：' . h($qs['name']) . '<br><span class="muted">' . h($qs['todo']) . '</span><br>';
    $prog = quest_progress_text((int) $u['id'], $qs);
    if ($prog !== '') {
        echo h($prog) . '<br>';
    }
    echo '<div class="hr">--------</div>';
    $mats = mats_of((int) $u['id']);
    $qmats = quest_mats();
    $any = false;
    foreach ($qmats as $mid => $mname) {
        if (!empty($mats[$mid])) {
            $any = true;
            echo '·' . h($mname) . 'x' . $mats[$mid] . ' <a href="bag.php?a=drop&id=' . h($mid) . '&tab=quest">扔</a><br>';
        }
    }
    if (!$any) {
        echo '<span class="muted">暂无任务物品。</span>';
    }
    echo '<div class="hr">--------</div>';
    echo '技能书：<br>';
    $hasBook = false;
    foreach (skill_books() as $bid => $b) {
        if (empty($mats[$bid])) {
            continue;
        }
        $hasBook = true;
        echo '·' . h($b['name']) . 'x' . $mats[$bid] . ' <a href="bag.php?a=usebook&id=' . h($bid) . '">使用</a> <a href="bag.php?a=drop&id=' . h($bid) . '&tab=quest">扔</a><br>';
    }
    if (!$hasBook) {
        echo '<span class="muted">空。</span>';
    }
    echo '功能道具：<br>';
    $func = false;
    if (!empty($mats['exp_card100'])) {
        $func = true;
        echo '·升级卡100型x' . $mats['exp_card100'] . ' <a href="bag.php?a=usecard">使用(1小时双倍)</a><br>';
    }
    if (!empty($mats['reset_potion'])) {
        $func = true;
        echo '·属性洗点药x' . $mats['reset_potion'] . ' <a href="bag.php?a=usereset">使用</a><br>';
    }
    if (!empty($mats['dragon_pill'])) {
        $func = true;
        echo '·真龙丹x' . $mats['dragon_pill'] . '(不可交易) <a href="bag.php?a=usedragon">使用(+3自由属性点)</a><br>';
    }
    if (!empty($mats['bag_ext5'])) {
        $func = true;
        echo '·5格背包扩充x' . $mats['bag_ext5'] . ' <a href="bag.php?a=useext&id=bag_ext5">使用(最多5次)</a><br>';
    }
    if (!empty($mats['bag_ext10'])) {
        $func = true;
        echo '·10格背包扩充x' . $mats['bag_ext10'] . ' <a href="bag.php?a=useext&id=bag_ext10">使用(最多2次)</a><br>';
    }
    $cardLeft = (int) ($mats['exp_card_until'] ?? 0) - time();
    if ($cardLeft > 0) {
        $func = true;
        echo '<span class="muted">双倍经验剩' . h(dummy_fmt($cardLeft)) . '</span><br>';
    }
    if (!$func) {
        echo '<span class="muted">空。</span>';
    }
} else {
    echo '回血药：' . (int) $u['potion'] . '<br>';
    if ((int) $u['potion'] > 0) {
        echo '<a href="bag.php?a=drink">喝一瓶药</a><br>';
    }
    echo '<div class="hr">--------</div>';
    echo '药罐（点进商城可续）：<br>';
    $omats = mats_of((int) $u['id']);
    $hasTank = false;
    foreach (mall_tanks() as $tid => $t) {
        if (empty($omats[$tid])) {
            continue;
        }
        $hasTank = true;
        echo '·' . h($t['name']) . '剩' . $omats[$tid] . '点<br>';
    }
    if (!$hasTank) {
        echo '<span class="muted">空。去<a href="mall.php">商城</a>买罐。</span><br>';
    }
    echo '<div class="hr">--------</div>';
    echo '陪练人偶：';
    $dmats = mats_of((int) $u['id']);
    if (!empty($dmats['dummy_time'])) {
        echo '剩' . h(dummy_fmt((int) $dmats['dummy_time']));
    } else {
        echo '没时间';
    }
    echo !empty($dmats['dummy_on']) ? '（开着） <a href="bag.php?a=dummy&v=0">停止</a>' : ' <a href="bag.php?a=dummy&v=1">启动</a>';
    echo '<br>';
    echo '离线模块：' . (!empty($dmats['offline_on']) ? '已开通（与人偶共用时长）' : '未开通，去<a href="mall.php">商城</a>') . '<br>';
    echo '<div class="hr">--------</div>';
    echo '<a href="shop.php">去黑市</a>';
}
nav_line();
wap_end(false);
