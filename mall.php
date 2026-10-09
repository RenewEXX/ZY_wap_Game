<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$m = (string) ($_GET['m'] ?? '');
$goods = mall_goods();

if ($a === 'buy') {
    $n = max(1, min(99, (int) ($_GET['n'] ?? $_POST['n'] ?? 1)));
    $mats0 = mats_of((int) $u['id']);
    if ($m === 'bag_ext5' && (int) ($mats0['bag_ext5'] ?? 0) + (int) ($mats0['bag_ext5_used'] ?? 0) + $n > 5) {
        flash_set('5格扩充终身最多5个（已用+持有），买多了用不了。');
        header('Location: mall.php' . ($m !== '' ? '?m=' . urlencode($m) : ''));
        exit;
    }
    if ($m === 'bag_ext10' && (int) ($mats0['bag_ext10'] ?? 0) + (int) ($mats0['bag_ext10_used'] ?? 0) + $n > 2) {
        flash_set('10格扩充终身最多2个（已用+持有），买多了用不了。');
        header('Location: mall.php' . ($m !== '' ? '?m=' . urlencode($m) : ''));
        exit;
    }
    if (!isset($goods[$m])) {
        flash_set('没有这件商品。');
    } elseif ((int) ($u['diamonds'] ?? 0) < $goods[$m]['price'] * $n) {
        flash_set('魔钻不够。买' . $n . '个要' . fmt_diamond($goods[$m]['price'] * $n) . '。');
    } else {
        $u['diamonds'] = (int) $u['diamonds'] - $goods[$m]['price'] * $n;
        user_save($u);
        spend_diamonds((int) $u['id'], $goods[$m]['price'] * $n);
        if ($m === 'offline_mod') {
            add_mat((int) $u['id'], 'dummy_time', 3600 * $n);
            mat_set((int) $u['id'], 'offline_on', 1);
            mat_set((int) $u['id'], 'dummy_on', 1);
            mat_set((int) $u['id'], 'dummy_last', time());
            mat_set((int) $u['id'], 'dummy_acc', 0);
            flash_set('离线模块开通并续费' . $n . '小时（与人偶共用时长），人偶已启动。');
        } else {
            add_mat((int) $u['id'], $m, $goods[$m]['unit'] * $n);
            flash_set($m === 'dummy_time' ? '陪练人偶续费' . $n . '小时，去背包启动。' : '买下' . $n . '个' . $goods[$m]['name'] . '，已进背包材料栏。');
        }
    }
    header('Location: mall.php' . ($m !== '' ? '?m=' . urlencode($m) : ''));
    exit;
}
if ($a === 'goldbuy' && $m === 'pet_food') {
    $n = max(1, min(99, (int) ($_GET['n'] ?? 1)));
    if ((int) $u['gold'] < 10000 * $n) {
        flash_set('金币不够，' . $n . '个粮食要' . fmt_money(10000 * $n) . '。');
    } else {
        $u['gold'] = (int) $u['gold'] - 10000 * $n;
        user_save($u);
        add_mat((int) $u['id'], 'pet_food', $n);
        flash_set('买下' . $n . '个宠物粮食，去宠物页喂。');
    }
    header('Location: mall.php');
    exit;
}
if ($a === 'redeem' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
    $codes = redeem_codes();
    if (!isset($codes[$code])) {
        flash_set('兑换码无效。');
    } else {
        $mark = 'code_' . $code;
        $mats = mats_of((int) $u['id']);
        $repeat = in_array($code, redeem_repeatable(), true);
        if (!empty($mats[$mark]) && !$repeat) {
            flash_set('这个兑换码已经用过了。');
        } else {
            if (!$repeat) {
                add_mat((int) $u['id'], $mark, 1);
            }
            $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + $codes[$code];
            $u['diamonds_bought'] = (int) ($u['diamonds_bought'] ?? 0) + $codes[$code];
            user_save($u);
            flash_set('兑换成功，+' . fmt_diamond($codes[$code]) . '。');
        }
    }
    header('Location: mall.php');
    exit;
}

wap_start('商城');
echo '<span style="color:#c6f">' . h(fmt_diamond((int) ($u['diamonds'] ?? 0))) . '</span><br>';
echo '<span class="muted">魔钻只能通过充值和特殊活动获取，刷怪不掉。</span>';
echo '<div class="hr">--------</div>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}

if ($m !== '' && isset($goods[$m])) {
    $g = $goods[$m];
    $isTank = isset(mall_tanks()[$m]);
    echo '<b><span style="color:#fc3">【' . h($g['name']) . '】</span></b><br>';
    echo '单价：<b>' . h(fmt_diamond($g['price'])) . '</b>/个<br>';
    if ($isTank) {
        echo '含量：每次买存' . $g['unit'] . '点' . (mall_tanks()[$m]['kind'] === 'hp' ? '生命' : '魔力') . '，血低于15%、蓝低于30%自动喝<br>';
    }
    if ($m === 'dummy_time') {
        echo '效果：启动后每30秒自动群殴6只当前地图的怪，最低买1小时<br>';
    }
    if ($m === 'offline_mod') {
        echo '效果：离线后仍模拟打怪，上线按离线时间结算经验掉落，与人偶共用时长，最低买1小时<br>';
    }
    echo '余额：' . h(fmt_diamond((int) ($u['diamonds'] ?? 0))) . '<br>';
    echo '<div class="hr">--------</div>';
    echo '快捷：<a href="mall.php?a=buy&m=' . h($m) . '&n=1">买1个</a> ';
    echo '<a href="mall.php?a=buy&m=' . h($m) . '&n=5">买5个(' . h(fmt_diamond($g['price'] * 5)) . ')</a> ';
    echo '<a href="mall.php?a=buy&m=' . h($m) . '&n=10">买10个(' . h(fmt_diamond($g['price'] * 10)) . ')</a><br>';
    echo '<form method="get" action="mall.php">';
    echo '<input type="hidden" name="a" value="buy">';
    echo '<input type="hidden" name="m" value="' . h($m) . '">';
    echo '数量 <input name="n" size="3" value="1"> <input type="submit" value="批量买">';
    echo '</form>';
    echo '<a href="mall.php">回商城</a>';
} else {
    $tab = (string) ($_GET['tab'] ?? 'hot');
    if (!in_array($tab, ['hot', 'stone', 'tank', 'dummy', 'pet', 'func'], true)) {
        $tab = 'hot';
    }
    echo '<a href="mall.php?tab=hot">热卖</a> <a href="mall.php?tab=stone">强化附魔</a> <a href="mall.php?tab=tank">药罐</a> <a href="mall.php?tab=dummy">人偶</a> <a href="mall.php?tab=pet">宠物</a> <a href="mall.php?tab=func">功能</a><br>';
    echo '<div class="hr">--------</div>';
    if ($tab === 'hot') {
        echo '【热卖推荐】<br>';
        echo '·<b><span style="color:#c6f">【陪练人偶】</span></b> <b>' . h(fmt_diamond(50)) . '</b>/小时 ';
        echo '<a href="mall.php?a=buy&m=dummy_time&n=1">买1小时</a><br>';
        echo '·<b><span style="color:#fc3">【升级卡100型】</span></b> <b>' . h(fmt_diamond(100)) . '</b>/张(1小时双倍) ';
        echo '<a href="mall.php?a=buy&m=exp_card100&n=1">买1</a><br>';
        echo '·<b><span style="color:#6cf">【未知宠物蛋】</span></b> <b>' . h(fmt_diamond(10)) . '</b>/个 ';
        echo '<a href="mall.php?a=buy&m=egg_unknown&n=1">买1</a><br>';
        echo '·<b><span style="color:#fc3">【下级强化石】</span></b> <b>' . h(fmt_diamond(mall_stones()['enhance_t1'])) . '</b>/个 ';
        echo '<a href="mall.php?a=buy&m=enhance_t1&n=1">买1</a><br>';
    }
    if ($tab === 'stone') {
        echo '强化石（魔钻专供）：<br>';
        foreach (mall_stones() as $mid => $price) {
            echo '·<a href="mall.php?m=' . h($mid) . '"><b><span style="color:#fc3">【' . h(enhance_material_name($mid)) . '】</span></b></a> ';
            echo '<b>' . h(fmt_diamond($price)) . '</b>/个 ';
            echo '<a href="mall.php?a=buy&m=' . h($mid) . '&n=1">买1</a> ';
            echo '<a href="mall.php?a=buy&m=' . h($mid) . '&n=10">买10</a><br>';
        }
        echo '属性石（2魔钻/颗，10石合晶石，5晶石合珠，5珠合神石，装备详情页附魔）：<br>';
        foreach (['light', 'dark', 'fire', 'wind', 'ice', 'thunder'] as $eel) {
            echo '·<b>【' . h(element_name($eel)) . '属性石】</b> ';
            echo '<b>' . h(fmt_diamond(20)) . '</b>/颗 ';
            echo '<a href="mall.php?a=buy&m=el_' . $eel . '_stone&n=1">买1</a> ';
            echo '<a href="mall.php?a=buy&m=el_' . $eel . '_stone&n=10">买10</a><br>';
        }
    }
    if ($tab === 'tank') {
        echo '药罐（血低于15%、蓝低于30%自动喝）：<br>';
        foreach (mall_tanks() as $tid => $t) {
            echo '·<a href="mall.php?m=' . h($tid) . '"><b><span style="color:#6cf">【' . h($t['name']) . '】</span></b></a> ';
            echo '存' . $t['cap'] . '点' . ($t['kind'] === 'hp' ? '生命' : '魔力') . ' ';
            echo '<b>' . h(fmt_diamond($t['price'])) . '</b>/个 ';
            echo '<a href="mall.php?a=buy&m=' . h($tid) . '&n=1">买1</a> ';
            echo '<a href="mall.php?a=buy&m=' . h($tid) . '&n=5">买5</a><br>';
        }
    }
    if ($tab === 'dummy') {
        echo '陪练人偶（5魔钻/小时）：<br>';
        echo '·<a href="mall.php?m=dummy_time"><b><span style="color:#c6f">【陪练人偶】</span></b></a> ';
        echo '<b>' . h(fmt_diamond(50)) . '</b>/小时 ';
        echo '<a href="mall.php?a=buy&m=dummy_time&n=1">买1小时</a> ';
        echo '<a href="mall.php?a=buy&m=dummy_time&n=5">买5小时</a><br>';
        echo '·<a href="mall.php?m=offline_mod"><b><span style="color:#c6f">【人偶离线升级模块】</span></b></a> ';
        echo '<b>' . h(fmt_diamond(50)) . '</b>/小时 ';
        echo '<a href="mall.php?a=buy&m=offline_mod&n=1">买1小时</a> ';
        echo '<a href="mall.php?a=buy&m=offline_mod&n=5">买5小时</a><br>';
    }
    if ($tab === 'pet') {
        echo '金币区：·<b>【宠物粮食】</b> <b>1金</b>/个(清宠物疲劳，去宠物页喂) ';
        echo '<a href="mall.php?a=goldbuy&m=pet_food&n=1">买1</a> ';
        echo '<a href="mall.php?a=goldbuy&m=pet_food&n=10">买10</a><br>';
        echo '宠物蛋（1魔钻/个，砸出普通~史诗）：<br>';
        echo '·<a href="mall.php?m=egg_unknown"><b><span style="color:#6cf">【未知宠物蛋】</span></b></a> ';
        echo '<b>' . h(fmt_diamond(10)) . '</b>/个 ';
        echo '<a href="mall.php?a=buy&m=egg_unknown&n=1">买1</a> ';
        echo '<a href="mall.php?a=buy&m=egg_unknown&n=10">买10</a><br>';
    }
    if ($tab === 'func') {
        echo '功能道具：<br>';
        echo '·<b>【5格背包扩充】</b> <b>' . h(fmt_diamond(200)) . '</b>/个(每角色最多用5次) ';
        echo '<a href="mall.php?a=buy&m=bag_ext5&n=1">买1</a><br>';
        echo '·<b>【10格背包扩充】</b> <b>' . h(fmt_diamond(500)) . '</b>/个(每角色最多用2次) ';
        echo '<a href="mall.php?a=buy&m=bag_ext10&n=1">买1</a><br>';
        echo '·<b><span style="color:#fc3">【升级卡100型】</span></b> ';
        echo '<b>' . h(fmt_diamond(100)) . '</b>/张(1~100级用，1小时双倍经验) ';
        echo '<a href="mall.php?a=buy&m=exp_card100&n=1">买1</a> ';
        echo '<a href="mall.php?a=buy&m=exp_card100&n=5">买5</a><br>';
        echo '·<b><span style="color:#c6f">【属性洗点药】</span></b> ';
        echo '<b>' . h(fmt_diamond(300)) . '</b>/瓶(重新分配属性点，第三章结局也送) ';
        echo '<a href="mall.php?a=buy&m=reset_potion&n=1">买1</a><br>';
    }
    echo '<div class="hr">--------</div>';
    echo '充值/兑换：<br>';
    echo '<form method="post" action="mall.php?a=redeem">';
    echo '<input name="code" maxlength="16" placeholder="兑换码">';
    echo '<input type="submit" value="兑换">';
    echo '</form>';
}
nav_line();
wap_end(false);
