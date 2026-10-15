<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$tab = (string) ($_GET['tab'] ?? 'equip');
if (!in_array($tab, ['equip', 'mat', 'book', 'func', 'potion', 'quest'], true)) {
    $tab = 'equip';
}
if ($a === 'drink') {
    flash_set(drink_pct_potion($u, 'hp_pct10'));
    header('Location: bag.php?tab=potion');
    exit;
}
if ($a === 'drinkpct') {
    flash_set(drink_pct_potion($u, (string) ($_GET['m'] ?? '')));
    header('Location: bag.php?tab=potion');
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
    header('Location: bag.php?tab=func');
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
    if (!in_array($dtab, ['mat', 'book', 'func', 'potion', 'quest'], true)) {
        $dtab = 'mat';
    }
    $st = db()->prepare('SELECT num FROM mats WHERE uid=? AND mat=?');
    $st->execute([(int) $u['id'], $did]);
    $dn = (int) ($st->fetchColumn() ?: 0);
    if ($dn > 0) {
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], $did]);
        if ((isset(pet_eggs()[$did]) && pet_eggs()[$did]['species'] !== '') || isset(skill_books()[$did]) || $did === 'dsw_ticket') {
            ground_place_mat((string) ($u['loc'] ?? 'town_sq'), $did, $dn);
            flash_set('扔地上了。【' . mat_name($did) . '】x' . $dn . '（1分钟后消失）。');
        } else {
            flash_set('【' . mat_name($did) . '】不能拍卖，扔掉后直接销毁了。');
        }
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
        if (auction_listable_equip($eq)) {
            db()->prepare('UPDATE equips SET uid=0, pos="ground" WHERE id=?')->execute([(int) $eq['id']]);
            ground_place_equip((string) ($u['loc'] ?? 'town_sq'), (int) $eq['id']);
            flash_set('扔地上了。【' . equip_shortname((string) $eq['name']) . '】（1分钟后消失）。');
        } else {
            db()->exec('DELETE FROM equips WHERE id=' . (int) $eq['id']);
            flash_set('【' . equip_shortname((string) $eq['name']) . '】不能拍卖，扔掉后直接销毁了。');
        }
        _gear_uncache((int) $u['id']);
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
            flash_set('陪练人偶启动：每15秒自动打N只(背包功能里选1/3/6)当前地图的怪。');
        }
    } else {
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], 'dummy_on']);
        flash_set('陪练人偶停止。');
    }
    header('Location: bag.php?tab=func');
    exit;
}
if ($a === 'dummynum') {
    $dn = (int) ($_GET['n'] ?? 6);
    if (!in_array($dn, [1, 3, 6], true)) { $dn = 6; }
    mat_set((int) $u['id'], 'dummy_num', $dn);
    flash_set('挂机改为每次打' . $dn . '只怪（人偶+离线都生效，BOSS仍单只）。');
    header('Location: bag.php?tab=func');
    exit;
}
if ($a === 'autosell') {
    $v = (string) ($_GET['v'] ?? '');
    if ($v === '1') { mat_set((int) $u['id'], 'autosell_on', 1); flash_set('自动售卖开了。'); }
    elseif ($v === '0') { db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], 'autosell_on']); flash_set('自动售卖关了。'); }
    else { $qq = (int) ($_GET['q'] ?? 2); if (!in_array($qq, [0, 1, 2], true)) { $qq = 2; } mat_set((int) $u['id'], 'autosell_q', $qq); flash_set('自动售卖只卖' . ['低劣', '普通', '优秀'][$qq] . '及以下。'); }
    header('Location: bag.php?tab=func');
    exit;
}
if ($a === 'usebook') {
    flash_set(use_skill_book((int) $u['id'], (string) ($_GET['id'] ?? '')));
    header('Location: skills.php');
    exit;
}
if ($a === 'usecardx') {

    $mult = (int) ($_GET['m'] ?? 10);

    if (!in_array($mult, [10, 1000], true)) { $mult = 10; }

    flash_set(use_exp_cardx((int) $u['id'], $mult));

    header('Location: bag.php?tab=func');

    exit;

}

if ($a === 'usecard') {
    flash_set(use_exp_card((int) $u['id']));
    header('Location: bag.php?tab=func');
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
if ($a === 'useqingzui') {
    flash_set(use_qingzui_dan((int) $u['id']));
    header('Location: bag.php?tab=func');
    exit;
}
if ($a === 'usebox') {
    flash_set(open_dragon_box((int) $u['id']));
    header('Location: bag.php?tab=func');
    exit;
}
if ($a === 'usetent') {
    flash_set(use_tent((int) $u['id']));
    header('Location: bag.php?tab=func');
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
echo '<a href="bag.php?tab=book">秘籍</a> . ';
echo '<a href="bag.php?tab=func">功能</a> . ';
echo '<a href="bag.php?tab=potion">药水</a> . ';
echo '<a href="bag.php?tab=quest">任务</a>';
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
                echo $sname . ($i + 1) . '：' . ($r ? (($r['slot'] ?? '') === 'title' ? '' : enhance_tag_html((int) ($r['enhance_level'] ?? 0))) . '<a href="equip.php?id=' . $r['id'] . '"><span style="color:' . equip_color($r) . '">' . h(equip_shortname($r['name'])) . '</span></a>' : '空') . '<br>';
            }
            continue;
        }
        $e = $wornBy[$sid] ?? null;
        echo $sname . '：' . ($e ? (($e['slot'] ?? '') === 'title' ? '' : enhance_tag_html((int) ($e['enhance_level'] ?? 0))) . '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . equip_color($e) . '">' . h(equip_shortname($e['name'])) . '</span></a>' : '空') . '<br>';
    }
    echo '<div class="hr">--------</div>';
    echo '库存：<br>';
    $has = false;
    foreach ($eqs as $e) {
        if ($e['pos'] === 'wear') {
            continue;
        }
        $has = true;
        echo ((($e['slot'] ?? '') === 'title') ? '' : enhance_tag_html((int) ($e['enhance_level'] ?? 0))) . '<a href="equip.php?id=' . $e['id'] . '"><span style="color:' . equip_color($e) . '">' . h(equip_shortname($e['name'])) . '</span></a>[' . h($slots[$e['slot']] ?? '') . ']';
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
        // 系统标记/功能物：不算杂物（帐篷/人偶/离线/自动复活/称号位等）
        if (in_array($mid, ['tent', 'dummy_num', 'dummy_on', 'dummy_time', 'dummy_acc', 'dummy_last', 'dummy_total', 'offline_on', 'offline_time', 'pet_autorev', 'title_wear', 'seen_last', 'exp_card_until', 'bag_ext5_used', 'bag_ext10_used', 'autosell_on', 'autosell_time', 'autosell_q', 'autosell_earned'], true) || str_starts_with($mid, 'autosell_') || str_starts_with($mid, 'hatch_')) {
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
} elseif ($tab === 'book') {
    $mats = mats_of((int) $u['id']);
    echo '技能书：<br>';
    $hasBook = false;
    foreach (skill_books() as $bid => $b) {
        if (empty($mats[$bid])) {
            continue;
        }
        $hasBook = true;
        echo '·' . h($b['name']) . 'x' . $mats[$bid] . ' <a href="bag.php?a=usebook&id=' . h($bid) . '">使用</a> <a href="bag.php?a=drop&id=' . h($bid) . '&tab=book">扔</a><br>';
    }
    if (!$hasBook) {
        echo '<span class="muted">空。</span>';
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
} elseif ($tab === 'func') {
    $mats = mats_of((int) $u['id']);
    echo '功能道具：<br>';
    $func = false;
    if (!empty($mats['exp_card100'])) {
        $func = true;
        echo '·升级卡100型x' . $mats['exp_card100'] . ' <a href="bag.php?a=usecard">使用(1小时双倍)</a><br>';
    }
    if (!empty($mats['exp_card10x'])) {
        $func = true;
        echo '·10倍经验卡·天x' . $mats['exp_card10x'] . ' <a href="bag.php?a=usecardx&m=10">使用(1天10倍)</a><br>';
    }
    if (!empty($mats['exp_card1000x'])) {
        $func = true;
        echo '·1000倍经验卡·天x' . $mats['exp_card1000x'] . ' <a href="bag.php?a=usecardx&m=1000">使用(1天1000倍)</a><br>';
    }
    if (!empty($mats['reset_potion'])) {
        $func = true;
        echo '·属性洗点药x' . $mats['reset_potion'] . ' <a href="bag.php?a=usereset">使用</a><br>';
    }
    if (!empty($mats['rename_card'])) {
        $func = true;
        echo '·改名卡x' . $mats['rename_card'] . ' <a href="status.php?a=rename">去状态页改名</a><br>';
    }
    if (!empty($mats['dragon_pill'])) {
        $func = true;
        echo '·真龙丹x' . $mats['dragon_pill'] . '(不可交易) <a href="bag.php?a=usedragon">使用(+3自由属性点)</a><br>';
        if (!empty($mats['qingzui_dan'])) { echo '·清罪丹x' . $mats['qingzui_dan'] . '(随机-5~20罪值) <a href="bag.php?a=useqingzui">使用</a><br>'; }
    }
    if (!empty($mats['dragon_box'])) {
        $func = true;
        echo '·龙鳞宝箱x' . $mats['dragon_box'] . '(强化石+宠物蛋+陪练+抽称号) <a href="bag.php?a=usebox">开启</a><br>';
    }
    if (!empty($mats['tent'])) {
        $func = true;
        echo '·随身帐篷(野外扎营回满，不消耗) <a href="bag.php?a=usetent">扎营</a><br>';
    }
    if (!empty($mats['bag_ext5'])) {
        $func = true;
        echo '·5格背包扩充x' . $mats['bag_ext5'] . ' <a href="bag.php?a=useext&id=bag_ext5">使用(最多5次)</a><br>';
    }
    if (!empty($mats['bag_ext10'])) {
        $func = true;
        echo '·10格背包扩充x' . $mats['bag_ext10'] . ' <a href="bag.php?a=useext&id=bag_ext10">使用(最多2次)</a><br>';
    }
    echo '<div class="hr">--------</div>';
    echo '陪练人偶：';
    $dm = mats_of((int) $u['id']);
    if (!empty($dm['dummy_time'])) {
        echo '剩' . h(dummy_fmt((int) $dm['dummy_time']));
    } else {
        echo '没时间';
    }
    echo !empty($dm['dummy_on']) ? '（开着） <a href="bag.php?a=dummy&v=0">停止</a>' : ' <a href="bag.php?a=dummy&v=1">启动</a>';
    echo '<br>';
    echo '离线模块：' . (!empty($dm['offline_on']) ? '已开通，剩' . h(dummy_fmt((int) ($dm['offline_time'] ?? 0))) . '（独立计时）' : '未开通，去<a href="mall.php">商城</a>') . '<br>';
    $dn = (int) ($dm['dummy_num'] ?? 6);
    echo '每次打怪：' . ($dn === 1 ? '<b>1只(稳)</b>' : '<a href="bag.php?a=dummynum&n=1">1只(稳)</a>') . ' ' . ($dn === 3 ? '<b>3只</b>' : '<a href="bag.php?a=dummynum&n=3">3只</a>') . ' ' . ($dn === 6 ? '<b>6只(快)</b>' : '<a href="bag.php?a=dummynum&n=6">6只(快)</a>') . '<br>';
    $am = mats_of((int) $u['id']);
    echo '自动售卖：' . (!empty($am['autosell_on']) ? '开(剩' . h(dummy_fmt((int) ($am['autosell_time'] ?? 0))) . ') <a href="bag.php?a=autosell&v=0">关</a>' : (empty($am['autosell_time']) ? '没时间，去<a href="mall.php">商城人偶栏</a>买' : '<a href="bag.php?a=autosell&v=1">开</a>'));
    $aq = (int) ($am['autosell_q'] ?? 2);
    echo ' 卖：' . ($aq === 0 ? '<b>低劣</b>' : '<a href="bag.php?a=autosell&q=0">低劣</a>') . ' ' . ($aq === 1 ? '<b>普通及以下</b>' : '<a href="bag.php?a=autosell&q=1">普通及以下</a>') . ' ' . ($aq === 2 ? '<b>优秀及以下</b>' : '<a href="bag.php?a=autosell&q=2">优秀及以下</a>') . '<br>';
    $cardLeft = (int) ($mats['exp_card_until'] ?? 0) - time();
    if ($cardLeft > 0) {
        $func = true;
        echo '<span class="muted">双倍经验剩' . h(dummy_fmt($cardLeft)) . '</span><br>';
    }
    if (!$func) {
        echo '<span class="muted">空。</span>';
    }
} elseif ($tab === 'potion') {
    echo '<div class="hr">--------</div>';
    echo '百分比药（战斗中可手动喝）：<br>';
    $pmats = mats_of((int) $u['id']);
    $hasPct = false;
    foreach (pct_potions() as $pmid => $pt) {
        if (empty($pmats[$pmid])) {
            continue;
        }
        $hasPct = true;
        echo '·' . h($pt['name']) . '（回' . $pt['pct'] . '%）x' . $pmats[$pmid] . ' <a href="bag.php?a=drinkpct&m=' . h($pmid) . '">喝一瓶</a><br>';
    }
    if (!$hasPct) {
        echo '<span class="muted">空。去药剂师那买。</span><br>';
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
    echo '<a href="shop.php">去黑市</a>';
}
nav_line();
wap_end(false);
