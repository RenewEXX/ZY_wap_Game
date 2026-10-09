<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');
settle_auctions();

function auction_back(string $to = 'auction.php'): void
{
    header('Location: ' . $to);
    exit;
}

if ($a === 'bid' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = db()->prepare("SELECT * FROM auctions WHERE id=? AND status='open'");
    $st->execute([(int) ($_GET['id'] ?? 0)]);
    $auc = $st->fetch();
    if (!$auc) {
        flash_set('拍卖已结束。');
        auction_back();
    }
    if ((int) $auc['seller_uid'] === $uid) {
        flash_set('不能拍自己的东西。');
        auction_back('auction.php?a=view&id=' . $auc['id']);
    }
    if ((int) $auc['ends_at'] <= time()) {
        settle_auction((int) $auc['id']);
        flash_set('拍卖刚结束，已结算。');
        auction_back();
    }
    $cur = (int) $auc['cur_price'] > 0 ? (int) $auc['cur_price'] : (int) $auc['start_price'];
    $min = $cur + auction_min_inc($cur);
    if ($auc['currency'] === 'diamond') {
        $price = max(0, (int) ($_POST['diamond'] ?? 0));
    } else {
        $price = max(0, (int) ($_POST['g'] ?? 0)) * 10000 + max(0, (int) ($_POST['s'] ?? 0)) * 100 + max(0, (int) ($_POST['c'] ?? 0));
    }
    $bal = $auc['currency'] === 'diamond' ? (int) ($u['diamonds'] ?? 0) : (int) $u['gold'];
    if ($price < $min) {
        flash_set('最低出价' . auction_money_text($min, $auc['currency']) . '。');
        auction_back('auction.php?a=bid&id=' . $auc['id']);
    } elseif ($price > $bal) {
        flash_set('钱不够，出价会被冻结。');
        auction_back('auction.php?a=bid&id=' . $auc['id']);
    } else {
        if ($auc['currency'] === 'diamond') {
            $u['diamonds'] = (int) $u['diamonds'] - $price;
        } else {
            $u['gold'] = (int) $u['gold'] - $price;
        }
        user_save($u);
        if ((int) $auc['cur_bidder'] > 0) {
            $mt = $auc['currency'] === 'diamond' ? 'diamond' : 'gold';
            send_mail((int) $auc['cur_bidder'], '拍卖行', 'auction', '你对【' . auction_item_name($auc) . '】的出价被超过', '冻结的' . auction_money_text((int) $auc['cur_price'], $auc['currency']) . '已退回，请查收附件。', [['t' => $mt, 'n' => (int) $auc['cur_price']]]);
        }
        $ends = (int) $auc['ends_at'];
        $ext = (int) $auc['extensions'];
        if ($ends - time() < 300 && $ext < 3) {
            $ends += 300;
            $ext++;
        }
        db()->prepare('UPDATE auctions SET cur_price=?, cur_bidder=?, ends_at=?, extensions=? WHERE id=?')->execute([$price, $uid, $ends, $ext, (int) $auc['id']]);
        $stb = db()->prepare('INSERT INTO bids (auction_id, bidder, price, created_at) VALUES (?, ?, ?, ?)');
        $stb->execute([(int) $auc['id'], $uid, $price, time()]);
        flash_set('出价成功，冻结' . auction_money_text($price, $auc['currency']) . '。');
        auction_back('auction.php?a=view&id=' . $auc['id']);
    }
}
if ($a === 'buyout') {
    $st = db()->prepare("SELECT * FROM auctions WHERE id=? AND status='open'");
    $st->execute([(int) ($_GET['id'] ?? 0)]);
    $auc = $st->fetch();
    if (!$auc || (int) $auc['buyout'] <= 0) {
        flash_set('没有一口价。');
        auction_back();
    }
    if ((int) $auc['seller_uid'] === $uid) {
        flash_set('不能买自己的东西。');
        auction_back('auction.php?a=view&id=' . $auc['id']);
    }
    $price = (int) $auc['buyout'];
    $bal = $auc['currency'] === 'diamond' ? (int) ($u['diamonds'] ?? 0) : (int) $u['gold'];
    if ($price > $bal) {
        flash_set('钱不够。');
        auction_back('auction.php?a=view&id=' . $auc['id']);
    }
    if ((string) ($_GET['yes'] ?? '') !== '1') {
        wap_start('确认购买');
        echo '<div class="warn">你确定吗？</div>';
        echo '一口价【' . h(auction_item_name($auc)) . '】' . h(auction_money_text($price, $auc['currency'])) . '，买下不可退！<br>';
        echo '<div class="hr">--------</div>';
        echo '<a href="auction.php?a=buyout&id=' . $auc['id'] . '&yes=1">确定购买</a>　<a href="auction.php?a=view&id=' . $auc['id'] . '">取消</a><br>';
        nav_line();
        wap_end(false);
        exit;
    }
    if ($auc['currency'] === 'diamond') {
        $u['diamonds'] = (int) $u['diamonds'] - $price;
    } else {
        $u['gold'] = (int) $u['gold'] - $price;
    }
    user_save($u);
    if ((int) $auc['cur_bidder'] > 0 && (int) $auc['cur_bidder'] !== $uid) {
        $mt = $auc['currency'] === 'diamond' ? 'diamond' : 'gold';
        send_mail((int) $auc['cur_bidder'], '拍卖行', 'auction', '你对【' . auction_item_name($auc) . '】的出价被一口价终结', '冻结的' . auction_money_text((int) $auc['cur_price'], $auc['currency']) . '已退回。', [['t' => $mt, 'n' => (int) $auc['cur_price']]]);
    }
    db()->prepare('UPDATE auctions SET cur_price=?, cur_bidder=? WHERE id=?')->execute([$price, $uid, (int) $auc['id']]);
    settle_auction((int) $auc['id']);
    flash_set('一口价拿下！装备/物品已发到邮箱，去领取。');
    auction_back('mail.php');
}
if ($a === 'selldo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $open = (int) db()->query('SELECT COUNT(*) FROM auctions WHERE seller_uid=' . $uid . " AND status='open'")->fetchColumn();
    if ($open >= 5) {
        flash_set('上架额度满了（5件）。');
        auction_back('auction.php?a=mine');
    }
    $currency = (string) ($_POST['currency'] ?? 'gold') === 'diamond' ? 'diamond' : 'gold';
    if ($currency === 'diamond') {
        $start = max(1, (int) ($_POST['diamond_s'] ?? 0));
        $buy = max(0, (int) ($_POST['diamond_b'] ?? 0));
    } else {
        $start = max(1, (int) ($_POST['g_s'] ?? 0)) * 10000 + max(0, (int) ($_POST['s_s'] ?? 0)) * 100 + max(0, (int) ($_POST['c_s'] ?? 0));
        $buy = max(0, (int) ($_POST['g_b'] ?? 0)) * 10000 + max(0, (int) ($_POST['s_b'] ?? 0)) * 100 + max(0, (int) ($_POST['c_b'] ?? 0));
    }
    $dur = (string) ($_POST['dur'] ?? '1') === '24' ? 86400 : 3600;
    if ($start <= 0) {
        flash_set('起拍价必须大于0。');
        auction_back('auction.php?a=sell');
    }
    if ($buy > 0 && $buy <= $start) {
        flash_set('一口价必须高于起拍价。');
        auction_back('auction.php?a=sell');
    }
    $eid = (int) ($_POST['eid'] ?? 0);
    $mm = (string) ($_POST['mm'] ?? '');
    $mq = max(1, (int) ($_POST['mq'] ?? 1));
    if ($eid > 0) {
        $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
        $st->execute([$eid, $uid]);
        $e = $st->fetch();
        if (!$e || !auction_listable_equip($e)) {
            flash_set('这件装备不能上架（仅沼泽套装3件可交易）。');
            auction_back('auction.php?a=sell');
        }
        db()->exec('DELETE FROM equips WHERE id=' . (int) $e['id']);
        _gear_uncache($uid);
        $st2 = db()->prepare('INSERT INTO auctions (seller_uid, kind, item_name, item_slot, item_quality, item_level, item_affixes, enhance_level, currency, start_price, buyout, cur_price, cur_bidder, ends_at, status, created_at) VALUES (?, "equip", ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, "open", ?)');
        $st2->execute([$uid, $e['name'], $e['slot'], (int) $e['quality'], (int) ($e['item_level'] ?? 1), (string) $e['affixes'], (int) ($e['enhance_level'] ?? 0), $currency, $start, $buy, time() + $dur, time()]);
        flash_set('上架成功！到期' . date('m-d H:i', time() + $dur) . '，成交扣' . ($dur >= 86400 ? '7%' : '5%') . '手续费。');
    } elseif ($mm === 'dsw_ticket' || (isset(pet_eggs()[$mm]) && pet_eggs()[$mm]['species'] !== '')) {
        $mats = mats_of($uid);
        if (($mats[$mm] ?? 0) < $mq) {
            flash_set('数量不够。');
            auction_back('auction.php?a=sell');
        }
        add_mat($uid, $mm, -$mq);
        $st2 = db()->prepare('INSERT INTO auctions (seller_uid, kind, mat_id, qty, currency, start_price, buyout, cur_price, cur_bidder, ends_at, status, created_at) VALUES (?, "mat", ?, ?, ?, ?, ?, 0, 0, ?, "open", ?)');
        $st2->execute([$uid, $mm, $mq, $currency, $start, $buy, time() + $dur, time()]);
        flash_set('上架成功！到期' . date('m-d H:i', time() + $dur) . '，成交扣' . ($dur >= 86400 ? '7%' : '5%') . '手续费。');
    } else {
        flash_set('只能上架入场券、宠物蛋和沼泽套装。');
    }
    auction_back('auction.php?a=mine');
}
if ($a === 'cancel') {
    $st = db()->prepare("SELECT * FROM auctions WHERE id=? AND seller_uid=? AND status='open'");
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $auc = $st->fetch();
    if (!$auc) {
        flash_set('没有这件上架。');
    } elseif ((int) $auc['cur_bidder'] > 0) {
        flash_set('已有人出价，不能下架。');
    } else {
        auction_give_item($uid, $auc);
        db()->prepare("UPDATE auctions SET status='cancelled' WHERE id=?")->execute([(int) $auc['id']]);
        flash_set('已下架，物品回背包了。');
    }
    auction_back('auction.php?a=mine');
}

wap_start('拍卖行');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}

if ($a === 'view') {
    $st = db()->prepare('SELECT * FROM auctions WHERE id=?');
    $st->execute([(int) ($_GET['id'] ?? 0)]);
    $auc = $st->fetch();
    if (!$auc) {
        echo '没这个拍卖。<br><a href="auction.php">返回大厅</a>';
        nav_line();
        wap_end(false);
        exit;
    }
    echo '【物品详情】<br>';
    if ($auc['kind'] === 'equip') {
        echo '<b style="color:' . equip_color_by((string) $auc['item_name'], (int) $auc['item_quality']) . '">' . h(auction_item_name($auc)) . '</b><br>';
    } else {
        echo '<b>' . h(auction_item_name($auc)) . '</b><br>';
    }
    if ($auc['kind'] === 'equip') {
        echo '类型：' . h(equip_slots()[$auc['item_slot']] ?? '') . ' | 强化：' . enhance_tag_html((int) $auc['enhance_level']) . '<br>';
        $aff = json_decode((string) $auc['item_affixes'], true);
        if (is_array($aff)) {
            echo equip_affix_html($aff, (int) $auc['enhance_level']);
        }
    } else {
        echo '类型：材料<br>';
    }
    echo '<div class="hr">--------</div>';
    $cur = (int) $auc['cur_price'] > 0 ? (int) $auc['cur_price'] : (int) $auc['start_price'];
    echo '卖家：匿名<br>';
    echo '当前价：' . h(auction_money_text($cur, $auc['currency'])) . '<br>';
    echo '加价幅度：最低' . h(auction_money_text(auction_min_inc($cur), $auc['currency'])) . '<br>';
    $left = (int) $auc['ends_at'] - time();
    echo '剩余时间：' . ($left > 0 ? h(dummy_fmt($left)) : '已结束') . '<br>';
    if ((int) $auc['buyout'] > 0) {
        echo '一口价：' . h(auction_money_text((int) $auc['buyout'], $auc['currency'])) . '<br>';
    }
    if ($auc['status'] === 'open' && $left > 0 && (int) $auc['seller_uid'] !== $uid) {
        echo '<a href="auction.php?a=bid&id=' . $auc['id'] . '">出价</a> ';
        if ((int) $auc['buyout'] > 0) {
            echo '<a href="auction.php?a=buyout&id=' . $auc['id'] . '">一口价购买</a>';
        }
        echo '<br>';
    }
    echo '<a href="auction.php?a=browse">返回列表</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'bid') {
    $st = db()->prepare("SELECT * FROM auctions WHERE id=? AND status='open'");
    $st->execute([(int) ($_GET['id'] ?? 0)]);
    $auc = $st->fetch();
    if (!$auc) {
        echo '拍卖已结束。<br><a href="auction.php">返回大厅</a>';
        nav_line();
        wap_end(false);
        exit;
    }
    $cur = (int) $auc['cur_price'] > 0 ? (int) $auc['cur_price'] : (int) $auc['start_price'];
    $min = $cur + auction_min_inc($cur);
    echo '【出价】<br>物品：' . h(auction_item_name($auc)) . '<br>';
    echo '当前价：' . h(auction_money_text($cur, $auc['currency'])) . '<br>';
    echo '最低出价：' . h(auction_money_text($min, $auc['currency'])) . '<br>';
    echo '<span class="muted">出价后货币冻结，被超价或结束退回（走邮件）。</span><br>';
    echo '<form method="post" action="auction.php?a=bid&id=' . $auc['id'] . '">';
    if ($auc['currency'] === 'diamond') {
        echo '出价 <input name="diamond" size="6" value="' . ceil($min / 10) . '"> 魔钻（精确到0.1）<br>';
    } else {
        echo '出价 <input name="g" size="4" value="0">金 <input name="s" size="3" value="0">银 <input name="c" size="3" value="0">铜<br>';
    }
    echo '<input type="submit" value="确认出价">';
    echo '</form>';
    echo '<a href="auction.php?a=view&id=' . $auc['id'] . '">返回详情</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'sell') {
    echo '【选择上架物品】只收：入场券、沼泽套装3件。<br>';
    $eqs = my_equips($uid);
    $i = 0;
    foreach ($eqs as $e) {
        if (!auction_listable_equip($e)) {
            continue;
        }
        $i++;
        echo '[' . $i . '] <a href="auction.php?a=sellset&eid=' . $e['id'] . '">' . h(equip_shortname($e['name'])) . '+' . (int) ($e['enhance_level'] ?? 0) . '</a><br>';
    }
    $mats = mats_of($uid);
    if (!empty($mats['dsw_ticket'])) {
        $i++;
        echo '[' . $i . '] <a href="auction.php?a=sellset&mm=dsw_ticket">黑暗沼泽副本入场券x' . $mats['dsw_ticket'] . '</a><br>';
    }
    foreach (pet_eggs() as $eid => $e) {
        if ($e['species'] === '' || empty($mats[$eid])) {
            continue;
        }
        $i++;
        echo '[' . $i . '] <a href="auction.php?a=sellset&mm=' . h($eid) . '">' . h($e['name']) . 'x' . $mats[$eid] . '</a><br>';
    }
    if ($i === 0) {
        echo '<span class="muted">没有可上架的东西。</span><br>';
    }
    echo '<a href="auction.php">返回大厅</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'sellset') {
    $eid = (int) ($_GET['eid'] ?? 0);
    $mm = (string) ($_GET['mm'] ?? '');
    $label = '';
    if ($eid > 0) {
        $st = db()->prepare('SELECT * FROM equips WHERE id=? AND uid=?');
        $st->execute([$eid, $uid]);
        $e = $st->fetch();
        if (!$e || !auction_listable_equip($e)) {
            echo '不能上架。<br><a href="auction.php?a=sell">重选</a>';
            nav_line();
            wap_end(false);
            exit;
        }
        $label = equip_shortname($e['name']) . '+' . (int) ($e['enhance_level'] ?? 0);
    } elseif ($mm === 'dsw_ticket' || (isset(pet_eggs()[$mm]) && pet_eggs()[$mm]['species'] !== '')) {
        $label = $mm === 'dsw_ticket' ? '黑暗沼泽副本入场券' : pet_eggs()[$mm]['name'];
    } else {
        echo '不能上架。<br><a href="auction.php?a=sell">重选</a>';
        nav_line();
        wap_end(false);
        exit;
    }
    echo '【上架设置】<br>物品：' . h($label) . '<br>';
    echo '<form method="post" action="auction.php?a=selldo">';
    echo '<input type="hidden" name="eid" value="' . $eid . '">';
    echo '<input type="hidden" name="mm" value="' . h($mm) . '">';
    if ($mm === 'dsw_ticket' || isset(pet_eggs()[$mm])) {
        echo '数量 <input name="mq" size="3" value="1"><br>';
    }
    echo '货币 <input type="radio" name="currency" value="gold" checked>金币 <input type="radio" name="currency" value="diamond">魔钻<br>';
    echo '起拍价 <input name="g_s" size="4" value="0">金 <input name="s_s" size="3" value="0">银 <input name="c_s" size="3" value="0">铜<br>';
    echo '魔钻起拍 <input name="diamond_s" size="5" value="0">魔钻<br>';
    echo '一口价(可选) <input name="g_b" size="4" value="0">金 <input name="s_b" size="3" value="0">银 <input name="c_b" size="3" value="0">铜<br>';
    echo '魔钻一口 <input name="diamond_b" size="5" value="0">魔钻<br>';
    echo '时长 <input type="radio" name="dur" value="1" checked>1小时 <input type="radio" name="dur" value="24">24小时<br>';
    echo '<span class="muted">成交扣手续费：1小时5%，24小时7%。</span><br>';
    echo '<input type="submit" value="确认上架">';
    echo '</form>';
    echo '<a href="auction.php?a=sell">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'mine') {
    echo '【我的上架】<br>进行中：<br>';
    $st = db()->prepare("SELECT * FROM auctions WHERE seller_uid=? AND status='open' ORDER BY ends_at");
    $st->execute([$uid]);
    $rows = $st->fetchAll();
    if ($rows === []) {
        echo '<span class="muted">无。</span><br>';
    }
    foreach ($rows as $r) {
        $cur = (int) $r['cur_price'] > 0 ? (int) $r['cur_price'] : (int) $r['start_price'];
        $left = (int) $r['ends_at'] - time();
        echo '·' . h(auction_item_name($r)) . ' ';
        echo ((int) $r['cur_bidder'] > 0 ? '当前价' . h(auction_money_text($cur, $r['currency'])) : '无人出价') . ' | 剩' . h(dummy_fmt(max(0, $left)));
        echo ' <a href="auction.php?a=cancel&id=' . $r['id'] . '">下架</a><br>';
    }
    echo '已结束（货款走邮件）：<br>';
    $st = db()->prepare("SELECT * FROM auctions WHERE seller_uid=? AND status IN ('sold','expired','cancelled') ORDER BY id DESC LIMIT 10");
    $st->execute([$uid]);
    foreach ($st->fetchAll() as $r) {
        echo '·' . h(auction_item_name($r)) . ' [' . $r['status'] . '] <a href="mail.php">查看邮件</a><br>';
    }
    echo '<a href="auction.php">返回大厅</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'bids') {
    echo '【我的竞拍】<br>';
    $st = db()->prepare('SELECT DISTINCT auction_id FROM bids WHERE bidder=? ORDER BY auction_id DESC LIMIT 20');
    $st->execute([$uid]);
    $ids = $st->fetchAll();
    if ($ids === []) {
        echo '<span class="muted">还没出过价。</span><br>';
    }
    foreach ($ids as $row) {
        $sa = db()->prepare('SELECT * FROM auctions WHERE id=?');
        $sa->execute([(int) $row['auction_id']]);
        $r = $sa->fetch();
        if (!$r) {
            continue;
        }
        $sb = db()->prepare('SELECT MAX(price) FROM bids WHERE auction_id=? AND bidder=?');
        $sb->execute([(int) $r['id'], $uid]);
        $mine = (int) $sb->fetchColumn();
        $lead = (int) $r['cur_bidder'] === $uid && $r['status'] === 'open';
        echo '·' . h(auction_item_name($r)) . ' 我的出价' . h(auction_money_text($mine, $r['currency']));
        echo $r['status'] === 'open' ? ($lead ? '(领先)' : '(被超价)') : '[' . $r['status'] . ']';
        echo ' <a href="auction.php?a=view&id=' . $r['id'] . '">查看</a><br>';
    }
    echo '<a href="auction.php">返回大厅</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'logs') {
    echo '【交易记录】<br>';
    $st = db()->prepare('SELECT * FROM auction_logs WHERE uid=? ORDER BY id DESC LIMIT 30');
    $st->execute([$uid]);
    $rows = $st->fetchAll();
    if ($rows === []) {
        echo '<span class="muted">无记录。</span><br>';
    }
    foreach ($rows as $r) {
        echo date('m-d H:i', (int) $r['created_at']) . ' ' . h($r['text']) . '<br>';
    }
    echo '<a href="auction.php">返回大厅</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'browse') {
    $cat = (string) ($_GET['cat'] ?? 'all');
    $fq = (int) ($_GET['q'] ?? -1);
    $fset = (string) ($_GET['set'] ?? 'all');
    $fminlv = max(0, (int) ($_GET['minlv'] ?? 0));
    $fkw = trim((string) ($_GET['kw'] ?? ''));
    $fcur = (string) ($_GET['cur'] ?? 'all');
    if (!in_array($fcur, ['all', 'gold', 'diamond'], true)) {
        $fcur = 'all';
    }
    $sort = (string) ($_GET['sort'] ?? 'end');
    $p = max(1, (int) ($_GET['p'] ?? 1));
    $per = 5;
    $bl = function (array $over = []) use ($cat, $fq, $fset, $fminlv, $fkw, $fcur, $sort, $p) {
        $q = array_merge(['a' => 'browse', 'cat' => $cat, 'q' => $fq, 'set' => $fset, 'minlv' => $fminlv, 'kw' => $fkw, 'cur' => $fcur, 'sort' => $sort, 'p' => $p], $over);
        return 'auction.php?' . http_build_query($q);
    };
    echo '【浏览拍卖品】<br>';
    echo '分类：';
    foreach (['all' => '全部', 'equip' => '装备', 'mat' => '材料', 'use' => '消耗品'] as $k => $n) {
        echo ($k === $cat ? '<b>' . $n . '</b>' : '<a href="' . h($bl(['cat' => $k, 'p' => 1])) . '">' . $n . '</a>') . ' ';
    }
    echo '<br>品级：';
    foreach ([-1 => '全部', 4 => '传说', 3 => '史诗', 2 => '稀有'] as $k => $n) {
        echo ($k === $fq ? '<b>' . $n . '</b>' : '<a href="' . h($bl(['q' => $k, 'p' => 1])) . '">' . $n . '</a>') . ' ';
    }
    echo '<br>套装：';
    foreach (['all' => '全部', 'swamp' => '沼泽', 'abyss' => '深渊'] as $k => $n) {
        echo ($k === $fset ? '<b>' . $n . '</b>' : '<a href="' . h($bl(['set' => $k, 'p' => 1])) . '">' . $n . '</a>') . ' ';
    }
    echo '<br>货币：';
    foreach (['all' => '全部', 'gold' => '金币区', 'diamond' => '魔钻区'] as $k => $n) {
        echo ($k === $fcur ? '<b>' . $n . '</b>' : '<a href="' . h($bl(['cur' => $k, 'p' => 1])) . '">' . $n . '</a>') . ' ';
    }
    echo '<br>排序：';
    foreach (['end' => '将结束', 'price_asc' => '价格↑', 'price_desc' => '价格↓', 'quality' => '品级', 'level' => '等级'] as $k => $n) {
        echo ($k === $sort ? '<b>' . $n . '</b>' : '<a href="' . h($bl(['sort' => $k, 'p' => 1])) . '">' . $n . '</a>') . ' ';
    }
    echo '<br>';
    echo '<form method="get" action="auction.php">';
    echo '<input type="hidden" name="a" value="browse">';
    echo '<input type="hidden" name="cat" value="' . h($cat) . '">';
    echo '<input type="hidden" name="q" value="' . $fq . '">';
    echo '<input type="hidden" name="set" value="' . h($fset) . '">';
    echo '<input type="hidden" name="cur" value="' . h($fcur) . '">';
    echo '<input type="hidden" name="sort" value="' . h($sort) . '">';
    echo '等级≥<input name="minlv" size="3" value="' . $fminlv . '"> 名称<input name="kw" size="8" value="' . h($fkw) . '"><input type="submit" value="搜">';
    echo '</form>';
    echo '<div class="hr">--------</div>';
    $where = "status='open' AND ends_at>" . time();
    if ($cat === 'equip') {
        $where .= " AND kind='equip'";
    } elseif ($cat === 'mat') {
        $where .= " AND kind='mat'";
    } elseif ($cat === 'use') {
        $where .= ' AND 1=0';
    }
    if ($fq >= 0) {
        $where .= " AND kind='equip' AND item_quality=" . $fq;
    }
    if ($fset === 'swamp') {
        $where .= " AND kind='equip' AND item_name LIKE " . db()->quote('%沼泽%');
    } elseif ($fset === 'abyss') {
        $where .= " AND kind='equip' AND item_name LIKE " . db()->quote('%深渊%');
    }
    if ($fminlv > 0) {
        $where .= ' AND item_level>=' . $fminlv;
    }
    if ($fkw !== '') {
        $where .= ' AND item_name LIKE ' . db()->quote('%' . $fkw . '%');
    }
    if ($fcur === 'gold' || $fcur === 'diamond') {
        $where .= ' AND currency=' . db()->quote($fcur);
    }
    $effPrice = '(CASE WHEN cur_price>0 THEN cur_price ELSE start_price END)';
    $order = match ($sort) {
        'price_asc' => $effPrice . ' ASC, ends_at ASC',
        'price_desc' => $effPrice . ' DESC, ends_at ASC',
        'quality' => 'item_quality DESC, item_level DESC, ends_at ASC',
        'level' => 'item_level DESC, item_quality DESC, ends_at ASC',
        default => 'ends_at ASC',
    };
    $total = (int) db()->query('SELECT COUNT(*) FROM auctions WHERE ' . $where)->fetchColumn();
    $pages = max(1, (int) ceil($total / $per));
    $p = min($p, $pages);
    $st = db()->prepare('SELECT * FROM auctions WHERE ' . $where . ' ORDER BY ' . $order . ' LIMIT ' . $per . ' OFFSET ' . (($p - 1) * $per));
    $st->execute();
    $rows = $st->fetchAll();
    if ($rows === []) {
        echo '<span class="muted">没东西，去上架第一件吧。</span><br>';
    }
    foreach ($rows as $r) {
        $cur = (int) $r['cur_price'] > 0 ? (int) $r['cur_price'] : (int) $r['start_price'];
        $left = (int) $r['ends_at'] - time();
        if ($r['kind'] === 'equip') {
            echo '·<span style="color:' . equip_color_by((string) $r['item_name'], (int) $r['item_quality']) . '">' . h(auction_item_name($r)) . '</span>';
            echo '(' . h(equip_qualities()[(int) $r['item_quality']] ?? '') . (int) $r['item_level'] . '级)';
        } else {
            echo '·' . h(auction_item_name($r));
        }
        echo '<br>';
        echo '起拍' . h(auction_money_text((int) $r['start_price'], $r['currency']));
        if ((int) $r['buyout'] > 0) {
            echo ' 一口' . h(auction_money_text((int) $r['buyout'], $r['currency']));
        }
        echo ' | 剩' . h(dummy_fmt(max(0, $left))) . ' <a href="auction.php?a=view&id=' . $r['id'] . '">查看</a><br>';
    }
    echo '[' . ($p > 1 ? '<a href="' . h($bl(['p' => $p - 1])) . '">上一页</a>' : '上一页') . '] ';
    echo '[' . ($p < $pages ? '<a href="' . h($bl(['p' => $p + 1])) . '">下一页</a>' : '下一页') . '] ';
    echo '<a href="auction.php">返回大厅</a>';
    nav_line();
    wap_end(false);
    exit;
}

echo '【拍卖行】<br>';
echo '资产：<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span> 魔钻：<span style="color:#c6f">' . h(fmt_diamond((int) ($u['diamonds'] ?? 0))) . '</span><br>';
$open = (int) db()->query('SELECT COUNT(*) FROM auctions WHERE seller_uid=' . $uid . " AND status='open'")->fetchColumn();
echo '上架额度：' . $open . '/5件<br>';
$amail = (int) db()->query("SELECT COUNT(*) FROM mails WHERE uid=" . $uid . " AND type='auction' AND is_read=0")->fetchColumn();
if ($amail > 0) {
    echo '<div class="warn">你有' . $amail . '封拍卖行邮件待领取。<a href="mail.php">查看邮件</a></div>';
}
echo '<div class="hr">--------</div>';
echo '<a href="auction.php?a=browse">浏览拍卖品</a><br>';
echo '<a href="auction.php?a=sell">我要上架</a><br>';
echo '<a href="auction.php?a=mine">我的上架</a><br>';
echo '<a href="auction.php?a=bids">我的竞拍</a><br>';
echo '<a href="auction.php?a=logs">交易记录</a><br>';
echo '<a href="home.php">返回广场</a>';
nav_line();
wap_end(false);
