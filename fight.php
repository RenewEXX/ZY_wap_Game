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
    if (in_array((string) ($u['loc'] ?? ''), abx_maps(), true)) {
        $u['loc'] = 'silver_sq';
        $pen .= '深渊中倒下，直接传回白银广场。';
    }
    if (in_array((string) ($u['loc'] ?? ''), mx_maps(), true)) {
        $u['loc'] = 'avenue';
        $pen .= '母巢中倒下，直接传回中央大道。';
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
    $elite = (int) ($_GET['elite'] ?? 0) === 1 ? 1 : 0;
    if ($mid === boss_of_map((string) $u['loc'])) {
        $n = 1;
    }
    $m = $allm[$mid];
    if ($mid === 'echo_rayne' && (int) ($u['quest'] ?? 0) > 14) {
        $m = ['name' => '深渊回响·雷恩', 'hp' => 2000, 'atk' => 85, 'exp' => 2200, 'gold' => 1000];
    }
    $m = scale_monster($m, $mid, $elite);
    if ($elite === 1 && $mid === boss_of_map((string) $u['loc'])) {
        $elite = 0;
    }
    $got = spawn_take((string) $u['loc'], $mid, $elite, $n);
    if ($got <= 0) {
        $wait = spawn_respawn_in((string) $u['loc']);
        flash_set($elite === 1 ? '这只精英已经被宰了，蹲刷新吧。' : '这里的怪被杀光了，' . ($wait > 0 ? '剩' . gmdate('i:s', $wait) . '刷新。' : '马上刷新。'));
        header('Location: home.php');
        exit;
    }
    $n = min($n, $got);
    if ($elite === 1) {
        $m['name'] .= '（精英）';
        $m['exp'] = (int) $m['exp'] * 3;
        $m['gold'] = (int) $m['gold'] * 2;
    }
    $old = $_SESSION['battle'] ?? null;
    if (is_array($old) && ($old['id'] ?? '') === $mid && (int) ($old['left'] ?? 0) > 0 && (int) ($old['elite'] ?? 0) === $elite) {
        $old['num'] = (int) ($old['num'] ?? 0) + $n;
        $old['left'] = (int) ($old['left'] ?? 0) + $n;
        $old['log'] = '又有' . $n . '只' . $m['name'] . '加入围殴！(场上剩' . $old['left'] . '只)';
        $_SESSION['battle'] = $old;
        header('Location: fight.php');
        exit;
    }
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
        'elite' => $elite,
        'live' => true,
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
                'pet' => (function () use ($u) { $ap = active_pet((int) ($u['id'] ?? 0)); if (!$ap) { return ''; } $st = pet_stats($ap); return pet_species()[$ap['species']]['name'] . ' ' . min((int) $ap['hp'], $st['maxhp']) . '/' . $st['maxhp']; })(),
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
    unset($_SESSION['pvp']);
    flash_set('你撤了。没人笑话你，因为笑话你的多半已经死了。');
    header('Location: home.php');
    exit;
}

function pvp_check(array $u, int $tuid): array
{
    if (!war_window_open()) {
        return [null, '战场没开（周六日20点）。'];
    }
    if ((string) ($u['loc'] ?? '') !== 'warfield') {
        return [null, '你不在战场。'];
    }
    $t = user_by_id($tuid);
    if (!$t) {
        return [null, '那个人不见了。'];
    }
    if ((string) ($t['loc'] ?? '') !== 'warfield' || (int) $t['hp'] <= 0) {
        return [null, '对方不在战场或已倒下。'];
    }
    $g1 = my_guild((int) ($u['id'] ?? 0));
    $g2 = my_guild($tuid);
    if (!$g1 || !$g2 || (int) $g1['id'] === (int) $g2['id']) {
        return [null, '只能打不同公会的人。'];
    }
    return [$t, ''];
}

if ($a === 'pvp') {
    [$t, $err] = pvp_check($u, (int) ($_GET['uid'] ?? 0));
    if ($err !== '') {
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    $_SESSION['pvp'] = ['tuid' => (int) $t['id']];
    header('Location: fight.php?a=pvpfight');
    exit;
}
if ($a === 'pvp_hit') {
    $pvp = $_SESSION['pvp'] ?? null;
    [$t, $err] = pvp_check($u, (int) ($pvp['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['pvp']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    $pd = max(1, player_atk($u) + random_int(0, 3) - player_def($t));
    $t['hp'] = (int) $t['hp'] - $pd;
    if ((int) $t['hp'] <= 0) {
        $t['hp'] = 1;
        $t['loc'] = 'silver_sq';
        user_save($t);
        unset($_SESSION['pvp']);
        war_add_point((int) $u['id']);
        user_save($u);
        flash_set('你一刀结果了【' . $t['username'] . '】！战功+1（本周' . war_my_points((int) $u['id']) . '分）。他被抬回白银广场。');
        header('Location: home.php');
        exit;
    }
    user_save($t);
    $md = max(1, player_atk($t) + random_int(0, 2) - player_def($u));
    $u['hp'] = (int) $u['hp'] - $md;
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        unset($_SESSION['pvp']);
        $pen = death_penalty($u);
        user_save($u);
        flash_set('你被【' . $t['username'] . '】反杀了。' . $pen);
        header('Location: home.php');
        exit;
    }
    flash_set('你砍' . $pd . '点，对方回敬' . $md . '点。');
    header('Location: fight.php?a=pvpfight');
    exit;
}
if ($a === 'pvpfight') {
    $pvp = $_SESSION['pvp'] ?? null;
    [$t, $err] = pvp_check($u, (int) ($pvp['tuid'] ?? 0));
    if ($err !== '') {
        unset($_SESSION['pvp']);
        flash_set($err);
        header('Location: home.php');
        exit;
    }
    wap_start('公会战');
    echo '敌【' . h($t['username']) . '】' . (int) $t['lv'] . '级<br>';
    echo '<div class="hp">敌生命 ' . (int) $t['hp'] . '/' . (int) $t['maxhp'] . '</div>';
    echo '<div class="hp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
    echo '<div class="hr">--------</div>';
    echo '<a href="fight.php?a=pvp_hit">砍他一刀</a> <a href="fight.php?a=run">撤</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'answer') {
    $bb = $_SESSION['battle'] ?? null;
    if (is_array($bb) && ($bb['id'] ?? '') === 'abyss_eye') {
        unset($_SESSION['battle']);
        $pen = death_penalty($u);
        user_save($u);
        flash_set('你回答了。你的影子站起来，穿上了你。' . $pen);
    }
    header('Location: home.php');
    exit;
}
if ($a === 'silence') {
    $bb = $_SESSION['battle'] ?? null;
    if (is_array($bb) && ($bb['id'] ?? '') === 'abyss_eye') {
        $bb['eye_weak'] = true;
        $bb['log'] = '你保持沉默。深渊之眼动摇了，它打你-10%。';
        $bb['last'] = time();
        $_SESSION['battle'] = $bb;
    }
    header('Location: fight.php');
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
$fpet = active_pet((int) ($u['id'] ?? 0));
if ($fpet) {
    $fpst = pet_stats($fpet);
    echo '<div style="color:#9c9" id="ppet">' . h(pet_species()[$fpet['species']]['name']) . ' ' . min((int) $fpet['hp'], $fpst['maxhp']) . '/' . $fpst['maxhp'] . '</div>';
} else {
    echo '<div style="color:#9c9" id="ppet"></div>';
}
if (($b['id'] ?? '') === 'abyss_eye') {
    echo '<div class="warn">深渊之眼呼唤你的名字：“回答我……” <a href="fight.php?a=answer">回答</a> <a href="fight.php?a=silence">沉默</a></div>';
}
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
    if(d.pet!==undefined){ const pe=document.getElementById("ppet"); if(pe) pe.innerText=d.pet; }
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
