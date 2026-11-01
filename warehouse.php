<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

if ($a === 'storemat') {
    flash_set(warehouse_store_mat($uid, (string) ($_GET['mid'] ?? '')));
    header('Location: warehouse.php');
    exit;
}
if ($a === 'takemat') {
    flash_set(warehouse_take_mat($uid, (int) ($_GET['wid'] ?? 0)));
    header('Location: warehouse.php');
    exit;
}
if ($a === 'storeequip') {
    flash_set(warehouse_store_equip($uid, (int) ($_GET['eid'] ?? 0)));
    header('Location: warehouse.php');
    exit;
}
if ($a === 'takeequip') {
    flash_set(warehouse_take_equip($uid, (int) ($_GET['wid'] ?? 0)));
    header('Location: warehouse.php');
    exit;
}
if ($a === 'storegold' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    flash_set(warehouse_store_gold($uid, (int) ($_POST['amount'] ?? 0)));
    header('Location: warehouse.php');
    exit;
}
if ($a === 'takegold' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    flash_set(warehouse_take_gold($uid, (int) ($_POST['amount'] ?? 0)));
    header('Location: warehouse.php');
    exit;
}
if ($a === 'useext') {
    flash_set(warehouse_use_ext($uid));
    header('Location: warehouse.php');
    exit;
}

$u = user_by_id($uid);
$used = warehouse_slots_used($uid);
$cap = warehouse_capacity($uid);
$wgold = warehouse_gold_of($uid);
$extHeld = (int) (mats_of($uid)['wh_ext5'] ?? 0);

wap_start('个人仓库');
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
echo '格子：<b>' . $used . '</b> / ' . $cap . '　（基础20格，扩容书+5/本）<br>';
echo '仓库存金：<span class="gold">' . h(fmt_money($wgold)) . '</span>　身上金币：<span class="gold">' . h(fmt_money((int) $u['gold'])) . '</span><br>';
if ($extHeld > 0) {
    echo '<div class="warn">你持有【仓库扩容+5】x' . $extHeld . ' <a href="warehouse.php?a=useext">立即使用（+5格）</a></div>';
}
echo '<div class="hr">--------</div>';

// ===== 存钱 / 取钱 =====
echo '<b>金币存取</b><br>';
echo '<form method="post" action="warehouse.php?a=storegold" style="display:inline">存 <input name="amount" size="9" value="' . (int) $u['gold'] . '"> <input type="submit" value="存入"></form> ';
echo '<form method="post" action="warehouse.php?a=takegold" style="display:inline">取 <input name="amount" size="9" value="' . (int) $wgold . '"> <input type="submit" value="取出"></form><br>';
echo '<span class="muted">金币不占仓库格子。</span>';
echo '<div class="hr">--------</div>';

// ===== 仓库内物品（取出） =====
echo '<b>仓库物品</b><br>';
$rows = db()->prepare('SELECT * FROM warehouse WHERE uid=? ORDER BY kind DESC, id ASC');
$rows->execute([$uid]);
$whRows = $rows->fetchAll();
$hasAny = false;
foreach ($whRows as $r) {
    $hasAny = true;
    if (($r['kind'] ?? '') === 'equip') {
        $eid = (int) $r['equip_id'];
        $est = db()->prepare('SELECT * FROM equips WHERE id=?');
        $est->execute([$eid]);
        $e = $est->fetch();
        if (!$e) {
            echo '·<span class="muted">（失效装备，点取出清理）</span> <a href="warehouse.php?a=takeequip&wid=' . (int) $r['id'] . '">取出</a><br>';
            continue;
        }
        $slots = equip_slots();
        echo '·' . ((($e['slot'] ?? '') === 'title') ? '' : enhance_tag_html((int) ($e['enhance_level'] ?? 0)))
            . '<span style="color:' . equip_color($e) . '">' . h(equip_shortname((string) $e['name'])) . '</span>[' . h($slots[$e['slot']] ?? '') . '] '
            . '<a href="warehouse.php?a=takeequip&wid=' . (int) $r['id'] . '">取出</a><br>';
    } else {
        $mid = (string) $r['mat_id'];
        echo '·' . h(mat_name($mid)) . 'x' . (int) $r['qty'] . ' <a href="warehouse.php?a=takemat&wid=' . (int) $r['id'] . '">取出</a><br>';
    }
}
if (!$hasAny) {
    echo '<span class="muted">仓库空空如也。</span><br>';
}
echo '<div class="hr">--------</div>';

// ===== 背包物品（存入） =====
$full = $used >= $cap;
if ($full) {
    echo '<div class="warn">仓库已满（' . $cap . '格），先取出东西或使用扩容书。</div>';
}
echo '<b>从背包存入</b><br>';
$anyMat = false;
foreach (mats_of($uid) as $mid => $num) {
    if ((int) $num <= 0 || !warehouse_mat_allowed((string) $mid)) {
        continue;
    }
    $anyMat = true;
    echo '·' . h(mat_name((string) $mid)) . 'x' . (int) $num . ' <a href="warehouse.php?a=storemat&mid=' . h((string) $mid) . '">存入</a><br>';
}
$eqs = db()->prepare('SELECT * FROM equips WHERE uid=? AND pos="" ORDER BY id DESC');
$eqs->execute([$uid]);
$slots = equip_slots();
$anyEq = false;
foreach ($eqs->fetchAll() as $e) {
    $anyEq = true;
    echo '·' . ((($e['slot'] ?? '') === 'title') ? '' : enhance_tag_html((int) ($e['enhance_level'] ?? 0)))
        . '<span style="color:' . equip_color($e) . '">' . h(equip_shortname((string) $e['name'])) . '</span>[' . h($slots[$e['slot']] ?? '') . '] '
        . '<a href="warehouse.php?a=storeequip&eid=' . (int) $e['id'] . '">存入</a><br>';
}
if (!$anyMat && !$anyEq) {
    echo '<span class="muted">背包里没有可存的东西。</span><br>';
}
echo '<div class="hr">--------</div>';
nav_line();
wap_end();
