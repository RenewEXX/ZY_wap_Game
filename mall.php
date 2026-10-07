<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$m = (string) ($_GET['m'] ?? '');
$goods = mall_goods();

if ($a === 'buy') {
    $n = max(1, min(99, (int) ($_GET['n'] ?? $_POST['n'] ?? 1)));
    if (!isset($goods[$m])) {
        flash_set('没有这件商品。');
    } elseif ((int) ($u['diamonds'] ?? 0) < $goods[$m]['price'] * $n) {
        flash_set('魔钻不够。买' . $n . '个要' . fmt_diamond($goods[$m]['price'] * $n) . '。');
    } else {
        $u['diamonds'] = (int) $u['diamonds'] - $goods[$m]['price'] * $n;
        user_save($u);
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
        echo '含量：每次买存' . $g['unit'] . '点' . (mall_tanks()[$m]['kind'] === 'hp' ? '生命' : '魔力') . '，血蓝低于30%自动喝<br>';
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
    echo '强化石（魔钻专供）：<br>';
    foreach (mall_stones() as $mid => $price) {
        echo '·<a href="mall.php?m=' . h($mid) . '"><b><span style="color:#fc3">【' . h(enhance_material_name($mid)) . '】</span></b></a> ';
        echo '<b>' . h(fmt_diamond($price)) . '</b>/个 ';
        echo '<a href="mall.php?a=buy&m=' . h($mid) . '&n=1">买1</a> ';
        echo '<a href="mall.php?a=buy&m=' . h($mid) . '&n=10">买10</a><br>';
    }
    echo '<div class="hr">--------</div>';
    echo '药罐（血蓝低于30%自动喝）：<br>';
    foreach (mall_tanks() as $tid => $t) {
        echo '·<a href="mall.php?m=' . h($tid) . '"><b><span style="color:#6cf">【' . h($t['name']) . '】</span></b></a> ';
        echo '存' . $t['cap'] . '点' . ($t['kind'] === 'hp' ? '生命' : '魔力') . ' ';
        echo '<b>' . h(fmt_diamond($t['price'])) . '</b>/个 ';
        echo '<a href="mall.php?a=buy&m=' . h($tid) . '&n=1">买1</a> ';
        echo '<a href="mall.php?a=buy&m=' . h($tid) . '&n=5">买5</a><br>';
    }
    echo '<div class="hr">--------</div>';
    echo '陪练人偶（5魔钻/小时）：<br>';
    echo '·<a href="mall.php?m=dummy_time"><b><span style="color:#c6f">【陪练人偶】</span></b></a> ';
    echo '<b>' . h(fmt_diamond(50)) . '</b>/小时 ';
    echo '<a href="mall.php?a=buy&m=dummy_time&n=1">买1小时</a> ';
    echo '<a href="mall.php?a=buy&m=dummy_time&n=5">买5小时</a><br>';
    echo '·<a href="mall.php?m=offline_mod"><b><span style="color:#c6f">【人偶离线升级模块】</span></b></a> ';
    echo '<b>' . h(fmt_diamond(50)) . '</b>/小时 ';
    echo '<a href="mall.php?a=buy&m=offline_mod&n=1">买1小时</a> ';
    echo '<a href="mall.php?a=buy&m=offline_mod&n=5">买5小时</a><br>';
    echo '宠物蛋（1魔钻/个，砸出普通~史诗）：<br>';
    echo '·<a href="mall.php?m=egg_unknown"><b><span style="color:#6cf">【未知宠物蛋】</span></b></a> ';
    echo '<b>' . h(fmt_diamond(10)) . '</b>/个 ';
    echo '<a href="mall.php?a=buy&m=egg_unknown&n=1">买1</a> ';
    echo '<a href="mall.php?a=buy&m=egg_unknown&n=10">买10</a><br>';
    echo '<div class="hr">--------</div>';
    echo '充值/兑换：<br>';
    echo '<form method="post" action="mall.php?a=redeem">';
    echo '<input name="code" maxlength="16" placeholder="兑换码">';
    echo '<input type="submit" value="兑换">';
    echo '</form>';
}
nav_line();
wap_end(false);
