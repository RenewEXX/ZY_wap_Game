<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$mid = (string) ($_GET['m'] ?? '');
$allm = monsters();

if ((int) $u['hp'] <= 0) {
    $pen = death_penalty($u);
    if (in_array((string) ($u['loc'] ?? ''), dsw_maps(), true)) {
        $u['loc'] = 'town_sq';
        $pen .= '副本中倒下，直接传回白石镇广场。';
    }
    user_save($u);
    unset($_SESSION['battle']);
    flash_set('你被拖回营地。' . $pen);
    header('Location: home.php');
    exit;
}

if ($a === 'start' && isset($allm[$mid])) {
    $here = loc((string) $u['loc']);
    if (!in_array($mid, $here['monsters'], true)) {
        flash_set('这里没有那种东西。');
        header('Location: home.php');
        exit;
    }
    $n = (int) ($_GET['n'] ?? 1);
    if ($n !== 6) {
        $n = 1;
    }
    $m = $allm[$mid];
    $_SESSION['battle'] = [
        'id' => $mid,
        'name' => $m['name'],
        'hp' => $m['hp'],
        'maxhp' => $m['hp'],
        'atk' => $m['atk'],
        'exp' => $m['exp'],
        'gold' => $m['gold'],
        'log' => $n > 1 ? '你被' . $n . '只' . $m['name'] . '围住了！它们会一起打你。' : '你撞上了' . $m['name'] . '。',
        'num' => $n,
        'left' => $n,
        'wexp' => 0,
        'wgold' => 0,
        'drops' => [],
        'last' => time(),
    ];
    header('Location: fight.php');
    exit;
}

$b = $_SESSION['battle'] ?? null;
if (!is_array($b)) {
    header('Location: home.php');
    exit;
}

$log = (string) $b['log'];

$isAjax = isset($_GET['ajax']) || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');

if ($a === 'hit' || $a === 'skill' || $a === 'tick') {
    $b['last'] = time();
    $mode = $a === 'hit' ? 'hit' : 'skill';
    $r = battle_round($u, $b, $mode);
    if ($r['status'] === 'fight') {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            $bb = $_SESSION['battle'];
            echo json_encode([
                'done' => false,
                'log' => $r['log'],
                'uhp' => (int) $u['hp'], 'umax' => (int) $u['maxhp'],
                'ump' => (int) ($u['mp'] ?? 0), 'umaxmp' => (int) ($u['maxmp'] ?? 0),
                'bhp' => (int) $bb['hp'], 'bmax' => (int) $bb['maxhp'],
                'bname' => (string) $bb['name'],
                'left' => (int) ($bb['left'] ?? 1), 'num' => (int) ($bb['num'] ?? 1),
            ]);
            exit;
        }
        header('Location: fight.php');
        exit;
    }
    flash_set($r['flash']);
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['done' => true, 'log' => $r['log'], 'redirect' => 'home.php', 'flash' => $r['flash']]);
        exit;
    }
    header('Location: home.php');
    exit;
}
// 单轮战斗逻辑见 includes/game.php 的 battle_round()，切页后台推进见 background_battle()

if ($a === 'run') {
    unset($_SESSION['battle']);
    flash_set('你撤了。没人笑话你，因为笑话你的多半已经死了。');
    header('Location: home.php');
    exit;
}

if ($a === 'drink' && (int) $u['potion'] > 0) {
    $u['potion'] = (int) $u['potion'] - 1;
    $heal = min(20, (int) $u['maxhp'] - (int) $u['hp']);
    $u['hp'] = (int) $u['hp'] + $heal;
    user_save($u);
    $b['log'] = '你喝下一瓶药，回复' . $heal . '。';
    $b['last'] = time();
    $_SESSION['battle'] = $b;
    header('Location: fight.php');
    exit;
}

wap_start('交手');
$history = $b['history'] ?? [$log];
$num = (int) ($b['num'] ?? 1);
$left = (int) ($b['left'] ?? 1);
echo '<div>敌人：' . h((string) $b['name']) . ($num > 1 ? '（共' . $num . '只，剩' . $left . '）' : '') . '</div>';
echo '<div class="hp" id="bhp">敌生命 ' . (int) $b['hp'] . '/' . (int) $b['maxhp'] . '</div>';
if ($num > 1) {
    echo '<div class="warn" id="bleft">剩余 ' . $left . '/' . $num . '只，存活怪每回合一起打你！</div>';
}
$fsk = active_skill((int) ($u['id'] ?? 0), job_id_of($u));
echo '<div class="hp" id="uhp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
echo '<div style="color:#6cf" id="ump">魔力 ' . (int) ($u['mp'] ?? 0) . '/' . (int) ($u['maxmp'] ?? 0) . ($fsk ? '·' . h($fsk['name']) . $fsk['mp'] . '蓝' : '·未学技能') . ' <a href="skills.php">换技能</a></div>';
echo '<div class="muted">自动战斗中：每1秒默认放[' . h(job_of($u)['skill']) . ']，战报每3秒刷新一块。切到别的画面战斗也会在后台继续。</div>';
echo '<div class="hr">--------</div>';
echo '<div id="blog">';
foreach (array_slice($history, -6) as $h) {
    echo h($h) . '<br>';
}
echo '</div>';
echo '<div class="hr">--------</div>';
echo '<button id="pauseBtn" type="button">暂停</button> ';
if ((int) $u['potion'] > 0) {
    echo '<a href="fight.php?a=drink">喝药 (' . (int) $u['potion'] . ')</a> ';
}
echo '<a href="fight.php?a=run">撤</a>';
nav_line();
echo '<script>
let paused=false, buf=[];
document.getElementById("pauseBtn").onclick=()=>{paused=!paused;document.getElementById("pauseBtn").innerText=paused?"继续":"暂停";};
// 每1秒打一轮（默认技能）
setInterval(async ()=>{
  if(paused||document.hidden) return;
  try{
    const r=await fetch("fight.php?a=tick&ajax=1",{headers:{"X-Requested-With":"fetch"},credentials:"same-origin"});
    const d=await r.json();
    if(d.done){ location.href=d.redirect||"home.php"; return; }
    buf.push(d.log);
    document.getElementById("uhp").innerText="你 "+d.uhp+"/"+d.umax;
    if(d.ump!==undefined){ const me=document.getElementById("ump"); if(me) me.innerText="魔力 "+d.ump+"/"+d.umaxmp; }
    document.getElementById("bhp").innerText="敌生命 "+d.bhp+"/"+d.bmax;
    if(d.num>1){ const bl=document.getElementById("bleft"); if(bl) bl.innerText="剩余 "+d.left+"/"+d.num+"只，存活怪每回合一起打你！"; }
  }catch(e){}
},1000);
// 每3秒把这3秒的战报刷到页面
setInterval(()=>{
  if(!buf.length) return;
  const box=document.getElementById("blog");
  box.innerHTML=(box.innerHTML+"<br>---3秒---<br>"+buf.map(s=>s.replace(/</g,"&lt;")).join("<br>")).split("<br>").slice(-20).join("<br>");
  buf=[];
  window.scrollTo(0,document.body.scrollHeight);
},3000);
</script>';
echo '<noscript><meta http-equiv="refresh" content="3;url=fight.php?a=skill"></noscript>';
wap_end(false);
