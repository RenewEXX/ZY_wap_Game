<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$uid = (int) $u['id'];
$a = (string) ($_GET['a'] ?? '');

if ($a === 'hatch') {
    $egg = (string) ($_GET['egg'] ?? '');
    $eggs = pet_eggs();
    $mats = mats_of($uid);
    if (!isset($eggs[$egg]) || $eggs[$egg]['species'] === '') {
        flash_set('不能孵这个。');
    } elseif (empty($mats[$egg])) {
        flash_set('你没有这个蛋。');
    } else {
        add_mat($uid, $egg, -1);
        mat_set($uid, 'hatch_' . $eggs[$egg]['species'], time() + 600);
        flash_set('开始孵化【' . $eggs[$egg]['name'] . '】，10分钟后来领。');
    }
    header('Location: pet.php');
    exit;
}
if ($a === 'claim') {
    $sp = (string) ($_GET['sp'] ?? '');
    $mats = mats_of($uid);
    if (!isset(pet_species()[$sp])) {
        flash_set('没有这种宠物。');
    } elseif (($mats['hatch_' . $sp] ?? 0) > time()) {
        flash_set('还没孵好。');
    } elseif (empty($mats['hatch_' . $sp])) {
        flash_set('没有在孵的蛋。');
    } else {
        db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([$uid, 'hatch_' . $sp]);
        $cat = pet_species()[$sp];
        $stats = pet_stats(['species' => $sp, 'level' => 1]);
        $st = db()->prepare('INSERT INTO pets (uid, species, quality, level, exp, hp, status, active, created_at) VALUES (?, ?, ?, 1, 0, ?, "normal", 0, ?)');
        $st->execute([$uid, $sp, $cat['quality'], $stats['maxhp'], time()]);
        $eggName = $sp === 'shadow_cat' ? '雷恩蛋' : '蛋';
        flash_set('【' . $eggName . '】孵化成功，获得了宠物【' . $cat['name'] . '】！');
    }
    header('Location: pet.php');
    exit;
}
if ($a === 'open') {
    $mats = mats_of($uid);
    if (empty($mats['egg_unknown'])) {
        flash_set('你没有未知宠物蛋，去商城买。');
    } else {
        add_mat($uid, 'egg_unknown', -1);
        $pool = shop_egg_pool();
        $tot = array_sum($pool);
        $r = mt_rand(1, $tot);
        $acc = 0;
        $got = 'egg_slime';
        foreach ($pool as $eid => $w) {
            $acc += $w;
            if ($r <= $acc) {
                $got = $eid;
                break;
            }
        }
        add_mat($uid, $got, 1);
        flash_set('【砸蛋】你砸开了未知宠物蛋，获得了【' . pet_eggs()[$got]['name'] . '】！去孵化吧。');
    }
    header('Location: pet.php');
    exit;
}
if ($a === 'deploy') {
    flash_set(pet_set_active($uid, (int) ($_GET['id'] ?? 0)));
    header('Location: pet.php');
    exit;
}
if ($a === 'rest') {
    db()->prepare('UPDATE pets SET active=0 WHERE id=? AND uid=?')->execute([(int) ($_GET['id'] ?? 0), $uid]);
    flash_set('宠物休息去了。');
    header('Location: pet.php');
    exit;
}
if ($a === 'heal') {
    $st = db()->prepare('SELECT * FROM pets WHERE id=? AND uid=?');
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $p = $st->fetch();
    if (!$p) {
        flash_set('没有这只宠物。');
    } elseif ($p['status'] !== 'weak') {
        flash_set('它没事，不用治。');
    } else {
        $cost = pet_heal_cost($p);
        if ((int) $u['gold'] < $cost) {
            flash_set('治疗要' . fmt_money($cost) . '，钱不够。');
        } else {
            $u['gold'] = (int) $u['gold'] - $cost;
            user_save($u);
            $stats = pet_stats($p);
            db()->prepare('UPDATE pets SET status="normal", hp=? WHERE id=?')->execute([$stats['maxhp'], (int) $p['id']]);
            flash_set('治疗好了，满血复活！');
        }
    }
    header('Location: pet.php?a=view&id=' . (int) ($_GET['id'] ?? 0));
    exit;
}
if ($a === 'revive') {
    $st = db()->prepare('SELECT * FROM pets WHERE id=? AND uid=?');
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $p = $st->fetch();
    $mats = mats_of($uid);
    if (!$p) {
        flash_set('没有这只宠物。');
    } elseif ($p['status'] !== 'weak') {
        flash_set('它没事，不用治。');
    } elseif (empty($mats['pet_revive_potion'])) {
        flash_set('没有宠物复活药。');
    } else {
        add_mat($uid, 'pet_revive_potion', -1);
        $stats = pet_stats($p);
        db()->prepare('UPDATE pets SET status="normal", hp=? WHERE id=?')->execute([$stats['maxhp'], (int) $p['id']]);
        flash_set('复活药灌下去，满血复活！');
    }
    header('Location: pet.php?a=view&id=' . (int) ($_GET['id'] ?? 0));
    exit;
}
if ($a === 'release') {
    $st = db()->prepare('SELECT * FROM pets WHERE id=? AND uid=?');
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $p = $st->fetch();
    if (!$p) {
        flash_set('没有这只宠物。');
    } else {
        db()->prepare('DELETE FROM pets WHERE id=?')->execute([(int) $p['id']]);
        flash_set('放生了【' . pet_species()[$p['species']]['name'] . '】，江湖再见。');
    }
    header('Location: pet.php');
    exit;
}
if ($a === 'feed') {
    $st = db()->prepare('SELECT * FROM pets WHERE id=? AND uid=?');
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $p = $st->fetch();
    if (!$p) {
        flash_set('没有这只宠物。');
    } elseif ($p['status'] !== 'normal') {
        flash_set('虚弱中吃不下，先治疗。');
    } elseif (empty(mats_of($uid)['pet_food'])) {
        flash_set('没有宠物粮食，去商城买（1金/个）。');
    } else {
        add_mat($uid, 'pet_food', -1);
        $full = pet_stats($p);
        db()->prepare('UPDATE pets SET fatigue=0, hp=? WHERE id=?')->execute([(int) $full['hp'], (int) $p['id']]);
        flash_set('吃饱了！疲劳清零，生命回满，继续干活。');
    }
    header('Location: pet.php?a=view&id=' . (int) ($_GET['id'] ?? 0));
    exit;
}

wap_start('宠物');
if ($a === 'dex') {
    echo '【宠物图鉴】<br>';
    foreach (pet_species() as $sp) {
        echo '·' . h($sp['name']) . '（' . h(pet_quality_name($sp['quality'])) . '）：' . h($sp['source']) . '<br>';
        echo '<span class="muted">主动' . h($sp['active']) . '　被动' . h($sp['passive']) . '</span><br>';
    }
    echo '<a href="pet.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}
if ($a === 'view') {
    $st = db()->prepare('SELECT * FROM pets WHERE id=? AND uid=?');
    $st->execute([(int) ($_GET['id'] ?? 0), $uid]);
    $p = $st->fetch();
    if (!$p) {
        flash_set('没有这只宠物。');
        header('Location: pet.php');
        exit;
    }
    $flash = flash_get();
    if ($flash !== '') {
        echo '<div class="warn">' . h($flash) . '</div>';
    }
    $sp = pet_species()[$p['species']];
    $stats = pet_stats($p);
    echo '【宠物详情】<br>';
    echo '<b>' . h($sp['name']) . '</b> ' . (int) $p['level'] . '级<br>';
    echo '品质：' . h(pet_quality_name($sp['quality'])) . '　状态：' . ($p['status'] === 'weak' ? '<span style="color:#f00">虚弱</span>' : '正常') . ((int) $p['active'] === 1 ? '·出战中' : '') . '<br>';
    echo '疲劳：' . (int) ($p['fatigue'] ?? 0) . '/100' . ((int) ($p['fatigue'] ?? 0) >= 100 ? '<span style="color:#f00">（累趴了，喂粮食）</span>' : '') . '<br>';
    echo '经验：' . (int) $p['exp'] . '/' . ((int) $p['level'] * 100) . '<br>';
    echo '攻击' . $stats['atk'] . ' 防御' . $stats['def'] . ' 生命' . min((int) $p['hp'], $stats['maxhp']) . '/' . $stats['maxhp'] . ' 速度' . $stats['spd'] . ' 暴击' . $stats['crit'] . '% 爆伤' . $stats['cd'] . '%<br>';
    echo '主动：' . h($sp['active']) . '　被动：' . h($sp['passive']) . '<br>';
    echo '<div class="hr">--------</div>';
    if ((int) $p['active'] === 1) {
        echo '<a href="pet.php?a=rest&id=' . $p['id'] . '">休息</a> ';
    } else {
        echo '<a href="pet.php?a=deploy&id=' . $p['id'] . '">出战</a> ';
    }
    echo '<a href="pet.php?a=feed&id=' . $p['id'] . '">喂粮食(清疲劳)</a> ';
    echo '<a href="pet.php?a=release&id=' . $p['id'] . '">放生</a> ';
    if ($p['status'] === 'weak') {
        echo '<a href="pet.php?a=heal&id=' . $p['id'] . '">治疗(' . h(fmt_money(pet_heal_cost($p))) . ')</a> ';
        echo '<a href="pet.php?a=revive&id=' . $p['id'] . '">用复活药</a>';
    }
    echo '<br><a href="pet.php">返回</a>';
    nav_line();
    wap_end(false);
    exit;
}

$flash = flash_get();
if ($flash !== '') {
    echo '<div class="warn">' . h($flash) . '</div>';
}
$ap = active_pet($uid);
echo '【我的宠物】' . ($ap ? '出战：' . h(pet_species()[$ap['species']]['name']) . (int) $ap['level'] . '级' : '没出战') . '<br>';
echo '<div class="hr">--------</div>';
$pets = my_pets($uid);
if ($pets === []) {
    echo '<span class="muted">空窝。去刷雷恩碰雷恩蛋，或去商城砸蛋。</span><br>';
}
foreach ($pets as $p) {
    $sp = pet_species()[$p['species']];
    echo '·<a href="pet.php?a=view&id=' . $p['id'] . '">' . h($sp['name']) . '</a>' . (int) $p['level'] . '级' . h(pet_quality_name($sp['quality']));
    echo ($p['status'] === 'weak' ? '(虚弱)' : '') . ((int) $p['active'] === 1 ? '(出战中)' : '') . '<br>';
}
echo '<div class="hr">--------</div>';
echo '【孵化宠物蛋】<br>';
$mats = mats_of($uid);
$hasEgg = false;
foreach (pet_eggs() as $eid => $e) {
    if ($e['species'] === '' || empty($mats[$eid])) {
        continue;
    }
    $hasEgg = true;
    echo '·' . h($e['name']) . 'x' . $mats[$eid] . ' <a href="pet.php?a=hatch&egg=' . h($eid) . '">孵化(10分钟)</a> <a href="bag.php?a=drop&id=' . h($eid) . '&tab=other">扔掉</a><br>';
}
foreach ($mats as $mid => $num) {
    if (!str_starts_with($mid, 'hatch_')) {
        continue;
    }
    $hasEgg = true;
    $sp = substr($mid, 6);
    $left = (int) $num - time();
    echo '·' . h(pet_species()[$sp]['name'] ?? $sp) . '蛋孵化中';
    echo $left > 0 ? '，剩' . h(dummy_fmt($left)) : '，<a href="pet.php?a=claim&sp=' . h($sp) . '">领取</a>';
    echo '<br>';
}
if (!empty($mats['egg_unknown'])) {
    $hasEgg = true;
    echo '·未知宠物蛋x' . $mats['egg_unknown'] . ' <a href="pet.php?a=open">砸蛋</a> <a href="bag.php?a=drop&id=egg_unknown&tab=other">扔掉</a><br>';
}
if (!$hasEgg) {
    echo '<span class="muted">没蛋。</span><br>';
}
echo '<div class="hr">--------</div>';
echo '<a href="pet.php?a=dex">宠物图鉴</a>';
nav_line();
wap_end(false);
