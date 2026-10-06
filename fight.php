<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$a = (string) ($_GET['a'] ?? '');
$mid = (string) ($_GET['m'] ?? '');
$allm = monsters();

if ((int) $u['hp'] <= 0) {
    $u['hp'] = 1;
    $u['loc'] = 'camp';
    $u['gold'] = max(0, (int) $u['gold'] - 50);
    user_save($u);
    unset($_SESSION['battle']);
    flash_set('你被拖回营地。丢了50铜。');
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
    $pd = dmg_to_monster($u);
    $isSkill = ($a !== 'hit'); // tick 默认放技能
    if ($isSkill) {
        $job = job_id_of($u);
        if ($job === 'warrior') {
            // 战士：稳定150%物理
            $pd = (int) ($pd * 1.5);
        } elseif ($job === 'mage') {
            // 法师：无视防御，固定高伤
            $pd = player_atk($u) + random_int(4, 7);
        } elseif ($job === 'hunter') {
            // 猎手：两箭，约160%
            $pd = (int) ($pd * 0.8) + (int) ($pd * 0.8);
        } elseif ($job === 'priest') {
            // 牧师：回25血+小额神圣伤害
            $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + 25);
            $pd = (int) ($pd * 0.7) + 2;
        }
        if (($b['id'] ?? '') === 'banshee') {
            $pd += 4; // 技能破女妖减伤
        }
    }
    // 女妖物理减半
    if (($b['id'] ?? '') === 'banshee' && !$isSkill) {
        $pd = max(1, (int) ($pd / 2));
    }
    $b['hp'] = (int) $b['hp'] - $pd;
    $log = ($isSkill ? '你放出' . job_of($u)['skill'] . '，造成' : '你劈出') . $pd . '点。';
    $gs = gear_stats((int) ($u['id'] ?? 0));
    if ($gs['lifesteal'] > 0) {
        $hl = max(1, (int) ($pd * $gs['lifesteal'] / 100));
        $u['hp'] = min((int) $u['maxhp'], (int) $u['hp'] + $hl);
        $log .= '(吸血+' . $hl . ')';
    }
    if ((int) $b['hp'] <= 0) {
        $killed = (int) ($b['num'] ?? 1) - (int) ($b['left'] ?? 1) + 1;
        if ((int) ($b['left'] ?? 1) > 1) {
            // 还有剩：累计奖励，下一只顶上，本回合剩下的照样反扑
            $b['wexp'] = (int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? ''));
            $b['wgold'] = (int) ($b['wgold'] ?? 0) + (int) $b['gold'] + random_int(0, 2);
            $b['left'] = (int) $b['left'] - 1;
            $b['hp'] = (int) $b['maxhp'];
            $dp = roll_drop((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($dp !== '') {
                $b['drops'][] = $dp;
                $log .= '掉落【' . $dp . '】！';
            }
            $mi = roll_material((int) $u['id'], (string) ($b['id'] ?? ''));
            $ei = roll_enhance_material((int) $u['id'], (string) ($b['id'] ?? ''));
            if ($mi !== '') {
                $b['drops'][] = $mi;
            }
            if ($ei !== '') {
                $b['drops'][] = $ei;
            }
            $log .= '第' . $killed . '只倒了！下一只扑上来(剩' . (int) $b['left'] . ')。';
        } else {
        $num = (int) ($b['num'] ?? 1);
        $dp = roll_drop((int) $u['id'], (string) ($b['id'] ?? ''));
        $mi = roll_material((int) $u['id'], (string) ($b['id'] ?? ''));
        $ei = roll_enhance_material((int) $u['id'], (string) ($b['id'] ?? ''));
        if ($mi !== '') {
            $b['drops'][] = $mi;
        }
        if ($ei !== '') {
            $b['drops'][] = $ei;
        }
        if ($dp !== '') {
            $b['drops'][] = $dp;
        }
        $g = (int) ($b['wgold'] ?? 0) + (int) $b['gold'] + random_int(0, 2);
        $u['gold'] = (int) $u['gold'] + $g;
        $msg = gain_exp($u, (int) ($b['wexp'] ?? 0) + exp_gain_for($u, (string) ($b['id'] ?? '')));
        // 新手引导推进：哥布林→1，老井→2，食人魔→3毕业
        $qmap = ['goblin' => 1, 'banshee' => 2, 'ogre' => 3];
        $bid = (string) ($b['id'] ?? '');
        if (isset($qmap[$bid]) && (int) ($u['quest'] ?? 0) < $qmap[$bid]) {
            $u['quest'] = $qmap[$bid];
            if ($bid === 'goblin') {
                $msg .= '。玛莎给你燕麦肉汤，伤全好了';
                $u['hp'] = (int) $u['maxhp'];
                $u['potion'] = (int) $u['potion'] + 1;
            }
            if ($bid === 'banshee') {
                $msg .= '。女妖散去：“别往下听……”莉娜加入';
            }
            if ($bid === 'ogre') {
                $msg .= '。咕噜让路，送你大骨棒+黑币！桥通了，去白石镇吧';
                $bb = make_equip((int) $u['id'], 'weapon', '大骨棒', 1);
                $msg .= '。【' . $bb . '】已放进背包，去穿上吧';
                add_mat((int) $u['id'], 'blackcoin', 1);
                $msg .= '。获得任务物品【不断下坠的黑币】';
            }
        }
        user_save($u);
        unset($_SESSION['battle']);
        $txt = ($num > 1 ? '共杀' . $num . '只' : '') . $b['name'] . '散了。' . $msg . '，钱+' . fmt_money($g);
        if (!empty($b['drops'])) {
            $txt .= '。掉落' . h('【' . implode('】【', $b['drops']) . '】') . '(背包查看)';
        }
        // 第一章：计数+自动推进（杀够数才进下一环）
        $bid = (string) ($b['id'] ?? '');
        for ($ki = 0; $ki < $num; $ki++) {
            $kc = add_kill((int) $u['id'], $bid);
        }
        $cur = (int) ($u['quest'] ?? 0);
        if ($cur >= 10 && $cur <= 15) {
            $qs = ch1_quests()[$cur];
            if (check_ch1_done((int) $u['id'], $qs)) {
                $u['quest'] = $cur + 1;
                if ($cur === 10) {
                    $u['quest'] = 11;
                    $txt .= '。艾琳给你铜牌！';
                } elseif ($cur === 15) {
                    $txt .= '。莫尔倒下，第一章完！他手里的黑币和你的一模一样。';
                } else {
                    $txt .= '。本环完成，进下一环！';
                }
                // 食人魔毕业跳第一章
                user_save($u);
            } else {
                $txt .= '。【' . $qs['name'] . '】' . quest_progress_text((int) $u['id'], $qs);
            }
        }
        // 食人魔毕业进第一章
        if ($bid === 'ogre' && (int) ($u['quest'] ?? 0) === 3) {
            $u['quest'] = 10;
            user_save($u);
            $txt .= '。去白石镇广场找公会吧！';
        }
        flash_set($txt);
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['done' => true, 'log' => $log . $txt, 'redirect' => 'home.php', 'flash' => $txt]);
            exit;
        }
        header('Location: home.php');
        exit;
        }
    }
    $alive = max(1, (int) ($b['left'] ?? 1));
    $md = 0;
    for ($i = 0; $i < $alive; $i++) {
        $md += dmg_to_player($u, $b);
    }
    $u['hp'] = (int) $u['hp'] - $md;
    $log .= $alive > 1 ? $b['name'] . '合击x' . $alive . '共' . $md . '点。' : $b['name'] . '回击' . $md . '点。';
    user_save($u);
    if ((int) $u['hp'] <= 0) {
        $u['hp'] = 1;
        $u['loc'] = 'camp';
        $u['gold'] = max(0, (int) $u['gold'] - 50);
        user_save($u);
        unset($_SESSION['battle']);
        flash_set('你倒了。被拖回营地，钱-50铜。');
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['done' => true, 'log' => $log, 'redirect' => 'home.php']);
            exit;
        }
        header('Location: home.php');
        exit;
    }
    $b['log'] = $log;
    if (!isset($b['history']) || !is_array($b['history'])) {
        $b['history'] = [];
    }
    $b['history'][] = $log;
    $b['history'] = array_slice($b['history'], -20);
    $_SESSION['battle'] = $b;
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'done' => false,
            'log' => $log,
            'uhp' => (int) $u['hp'], 'umax' => (int) $u['maxhp'],
            'bhp' => (int) $b['hp'], 'bmax' => (int) $b['maxhp'],
            'bname' => (string) $b['name'],
            'left' => (int) ($b['left'] ?? 1), 'num' => (int) ($b['num'] ?? 1),
        ]);
        exit;
    }
    header('Location: fight.php');
    exit;
}

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
echo '<div class="hp" id="uhp">你 ' . (int) $u['hp'] . '/' . (int) $u['maxhp'] . '</div>';
echo '<div class="muted">自动战斗中：每1秒默认放[' . h(job_of($u)['skill']) . ']，战报每3秒刷新一块。</div>';
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
