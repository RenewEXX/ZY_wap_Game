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
    echo json_encode(chat_fetch($ch, $target, (int) ($_GET['last'] ?? 0)), JSON_UNESCAPED_UNICODE);
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
echo '<div id="msgs">';
foreach (chat_fetch($ch, $target, 0) as $m) {
    echo '<div>【' . h($m['username']) . '】' . h($m['text']) . '</div>';
}
echo '</div>';
echo '<form method="post" action="chat.php?ch=' . h($ch) . '">';
echo '<input name="text" maxlength="60" size="18" placeholder="说点什么(60字)">';
echo '<input type="submit" value="发送">';
echo '</form>';
echo '<div class="muted">5秒自动刷新。世界频道所有人可见。</div>';
$last = 0;
foreach (chat_fetch($ch, $target, 0) as $m) {
    $last = max($last, (int) $m['id']);
}
echo '<script>
var lastId=' . $last . ';
setInterval(async()=>{try{
const r=await fetch("chat.php?ch=' . $ch . '&a=poll&last="+lastId,{credentials:"same-origin"});
const d=await r.json();
if(!Array.isArray(d)||!d.length)return;
const box=document.getElementById("msgs");
for(const m of d){lastId=Math.max(lastId,m.id);
const dv=document.createElement("div");dv.textContent="【"+m.username+"】"+m.text;box.appendChild(dv);}
}catch(e){}},5000);
</script>';
nav_line();
wap_end(false);
