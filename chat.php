<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$ch = (string) ($_GET['ch'] ?? 'world');
if (!in_array($ch, ['world', 'guild', 'party'], true)) {
    $ch = 'world';
}
$target = 0;
if ($ch === 'guild') {
    $g = my_guild($uid);
    if ($g) {
        $target = (int) $g['id'];
    }
} elseif ($ch === 'party') {
    $p = my_party($uid);
    if ($p) {
        $target = (int) $p['id'];
    }
}

if ((string) ($_GET['a'] ?? '') === 'poll') {
    header('Content-Type: application/json; charset=utf-8');
    $rows = chat_fetch($ch, $target, (int) ($_GET['last'] ?? 0), (string) ($u['zone'] ?? 'z1'));
    // 附带头像/名字链接/正文 HTML，前端轮询时直接插入（名字可点进资料）
    foreach ($rows as &$m) {
        $mid = (int) ($m['uid'] ?? 0);
        $m['avatar'] = '<span class="avatar-zoom">' . avatar_by_uid_html($mid, 32) . '</span>';
        $nm = sin_name_html((string) $m['username'], sin_of($mid));
        $m['namehtml'] = $mid > 0 ? '<a href="look.php?from=chat&id=' . $mid . '">' . $nm . '</a>' : $nm;
        $m['texthtml'] = h((string) $m['text']);
    }
    unset($m);
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $err = chat_post($uid, $ch, $target, (string) ($_POST['text'] ?? ''));
    if ($err !== '') {
        flash_set($err);
    }
    header('Location: chat.php?ch=' . $ch);
    exit;
}

wap_start('聊天');
echo '<a href="chat.php?ch=world">世界</a> <a href="chat.php?ch=guild">公会</a> <a href="chat.php?ch=party">队伍</a><br>';
echo '<div class="hr">--------</div>';
$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}

$zone = (string) ($u['zone'] ?? 'z1');
$per = 10;
$total = chat_count($ch, $target, $zone);
$pages = max(1, (int) ceil($total / $per));
$page = max(1, min($pages, (int) ($_GET['p'] ?? 1)));
$msgs = chat_page($ch, $target, $zone, $page, $per);

$pageBar = function (int $page, int $pages, int $total, string $ch): string {
    $html = '<div class="muted">共' . $total . '条 · 第' . $page . '/' . $pages . '页';
    if ($page > 1) {
        $html .= ' · <a href="chat.php?ch=' . h($ch) . '&p=1">最新</a> <a href="chat.php?ch=' . h($ch) . '&p=' . ($page - 1) . '">上一页</a>';
    }
    if ($page < $pages) {
        $html .= ' · <a href="chat.php?ch=' . h($ch) . '&p=' . ($page + 1) . '">下一页</a> <a href="chat.php?ch=' . h($ch) . '&p=' . $pages . '">最旧</a>';
    }
    return $html . '</div>';
};
echo $pageBar($page, $pages, $total, $ch);
echo '<div id="msgs">';
foreach ($msgs as $m) {
    $muid = (int) ($m['uid'] ?? 0);
    $nm = sin_name_html((string) $m['username'], sin_of($muid));
    $nm = $muid > 0 ? '<a href="look.php?from=chat&id=' . $muid . '">' . $nm . '</a>' : $nm;
    echo '<div><span class="avatar-zoom">' . avatar_by_uid_html($muid, 32) . '</span> ' . $nm . '　' . h($m['text']) . '</div>';
}
echo '</div>';
echo $pageBar($page, $pages, $total, $ch);

echo '<form method="post" action="chat.php?ch=' . h($ch) . '">';
echo '<input name="text" maxlength="60" size="18" placeholder="说点什么(60字)">';
echo '<input type="submit" value="发送">';
echo '</form>';

if ($page === 1) {
    $last = 0;
    foreach ($msgs as $m) {
        $last = max($last, (int) $m['id']);
    }
    echo '<div class="muted">5秒自动刷新。世界频道仅本大区可见。</div>';
    echo '<script>
var lastId=' . $last . ';
setInterval(async()=>{try{
const r=await fetch("chat.php?ch=' . $ch . '&a=poll&last="+lastId,{credentials:"same-origin"});
const d=await r.json();
if(!Array.isArray(d)||!d.length)return;
const box=document.getElementById("msgs");
for(const m of d){lastId=Math.max(lastId,m.id);
const dv=document.createElement("div");dv.innerHTML=(m.avatar||"")+" "+(m.namehtml||m.username||"")+"　"+(m.texthtml||"");box.insertBefore(dv,box.firstChild);}
}catch(e){}},5000);
</script>';
} else {
    echo '<div class="muted">历史消息（自动刷新已暂停）。<a href="chat.php?ch=' . h($ch) . '&p=1">回到最新</a> · 世界频道仅本大区可见。</div>';
}
nav_line();
wap_end(false);
