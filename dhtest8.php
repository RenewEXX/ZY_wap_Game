<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok = 0; $bad = 0;
function ck8(string $l, $got, $want) { global $ok,$bad; if ($got === $want) { $ok++; } else { $bad++; echo "FAIL $l: got=",var_export($got,true)," want=",var_export($want,true),"\n"; } }

// —— 词条注册 ——
chk_affix: {
    $cat = affix_catalog();
    ck8('summon_power 在catalog', isset($cat['summon_power']), true);
    ck8('summon_hp 在catalog', isset($cat['summon_hp']), true);
    ck8('gear_stats 含 summon_power', array_key_exists('summon_power', gear_stats(1)), true);
    ck8('gear_stats 含 summon_hp', array_key_exists('summon_hp', gear_stats(1)), true);
    ck8('精准仍存在', isset($cat['precision']), true);
}

// —— 主词条规则 ——
chk_primary: {
    // 魂契器主手 → summon_power；契匣副手 → summon_hp
    $u = user_create('tsum8', 'x', 'summoner', 'z1', true, 'tmp');
    $w = make_equip($u, 'weapon', '深渊引魂灯', 3, 480); // relic 基底
    $box = make_equip($u, 'offhand', '招魂匣', 2, 200);
    $rows = db()->prepare('SELECT slot,name,affixes FROM equips WHERE uid=?');
    $rows->execute([$u]);
    $wmain = ''; $boxmain = '';
    foreach ($rows->fetchAll() as $r) {
        $aff = json_decode((string) $r['affixes'], true) ?: [];
        foreach ($aff as $a) {
            if (!empty($a['main'])) {
                if ($r['slot'] === 'weapon') $wmain = (string) $a['id'];
                if ($r['slot'] === 'offhand') $boxmain = (string) $a['id'];
            }
        }
    }
    echo "武器主词条=$wmain 契匣主词条=$boxmain\n";
    ck8('魂契器主词条=summon_power', $wmain, 'summon_power');
    ck8('契匣主词条=summon_hp', $boxmain, 'summon_hp');
    // 法杖主词条仍 matk（不受影响）
    $mw = make_equip($u, 'weapon', '烬焰·灰烬法杖', 3, 665);
    $st = db()->prepare('SELECT affixes FROM equips WHERE uid=? AND name LIKE ?');
    $st->execute([$u, '%灰烬法杖%']);
    $sm = '';
    foreach (json_decode((string) $st->fetchColumn(), true) ?: [] as $a) { if (!empty($a['main'])) $sm = $a['id']; }
    ck8('法杖主词条仍=matk', $sm, 'matk');
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $u);
    db()->exec('DELETE FROM users WHERE id=' . (int) $u);
    echo "cleaned $u\n";
}

// —— summon_hp 应用：生命随词条提升 ——
chk_spawnhp: {
    $id = user_create('tsum8b', 'x', 'summoner', 'z1', true, 'tmp');
    $u = user_by_id($id);
    $u['maxhp'] = 1000;
    // 无加成
    $s0 = summon_spawn('imp', 100, 1000, $u);
    // 插一件 +50% 召唤兽生命护符
    db()->prepare('INSERT INTO equips (uid,slot,name,quality,affixes,pos,item_level) VALUES (?,"necklace","t_hp",3,?,"wear",300)')
        ->execute([$id, json_encode([['id' => 'summon_hp', 'tier' => 3, 'v' => 50.0]])]);
    _gear_uncache($id);
    $u2 = user_by_id($id);
    $s1 = summon_spawn('imp', 100, 1000, $u2);
    echo "无词条hp={$s0['hp']}  +50%后hp={$s1['hp']}\n";
    ck8('summon_hp 提升生命', $s1['hp'] > $s0['hp'], true);
    ck8('summon_hp 约翻半倍', $s1['hp'] > (int) ($s0['hp'] * 1.4) && $s1['hp'] <= (int) ($s0['hp'] * 1.55), true);
    db()->exec('DELETE FROM equips WHERE uid=' . (int) $id);
    db()->exec('DELETE FROM users WHERE id=' . (int) $id);
}

// —— 传说词条合法性（k 必须在 catalog 里，否则无法显示） ——
chk_legend: {
    $found = [];
    foreach (['abaddon', 'abyss_eye', 'abyss_spore', 'valentin'] as $mid) {
        $d = monster_drops($mid); // 返回该怪的单条掉落
        if ($d === [] || empty($d['legendary']['extra']['k'])) {
            $bad++; echo "FAIL $mid 无召唤传说\n";
            continue;
        }
        $k = (string) $d['legendary']['extra']['k'];
        $found["$mid:$k"] = (float) $d['legendary']['extra']['v'];
        ck8("传说 $mid 词条 $k 已注册", isset(affix_catalog()[$k]), true);
        ck8("传说 $mid 有 base 名", !empty($d['legendary']['base']), true);
    }
    echo "召唤传说掉落：" . json_encode($found, JSON_UNESCAPED_UNICODE) . "\n";
    ck8('至少2条召唤传说掉落', count($found) >= 2, true);
}

// —— 喝药转治疗召唤物/宠物 ——
chk_drink: {
    $id = user_create('tsum8c', 'x', 'summoner', 'z1', true, 'tmp');
    $u = user_by_id($id);
    $_SESSION['uid'] = $id;
    add_mat($id, 'hp_pct50', 5);
    $u = user_by_id($id);
    $u['maxhp'] = 1000; $u['hp'] = 1000; // 满血
    user_save($u);
    // 造一只残血召唤兽
    $u = user_by_id($id);
    $_SESSION['battle'] = ['id' => 'rat', 'name' => '鼠', 'hp' => 5, 'maxhp' => 5, 'atk' => 1, 'num' => 1, 'left' => 1, 'history' => [], 'last' => time()];
    summon_save([['kind' => 'imp', 'name' => '深渊小鬼', 'hp' => 10, 'maxhp' => 100, 'atk' => 5, 'turns' => 3, 'bomb' => false]]);
    $msg = drink_pct_potion($u, 'hp_pct50');
    echo "满血喝药: $msg\n";
    $after = summon_list();
    ck8('召唤物被治疗', $after[0]['hp'] > 10, true);
    ck8('药被消耗', (int) (mats_of($id)['hp_pct50'] ?? 0), 4);
    ck8('提示喂给同伴', str_contains($msg, '同伴') || str_contains($msg, '小鬼'), true);

    // 自己没满血 → 优先自己，召唤物不动
    $u = user_by_id($id);
    $u['hp'] = 100; user_save($u);
    summon_save([['kind' => 'imp', 'name' => '深渊小鬼', 'hp' => 10, 'maxhp' => 100, 'atk' => 5, 'turns' => 3, 'bomb' => false]]);
    $u = user_by_id($id);
    $msg2 = drink_pct_potion($u, 'hp_pct50');
    echo "残血喝药: $msg2\n";
    $u3 = user_by_id($id);
    ck8('优先治疗自己', (int) $u3['hp'] > 100, true);
    ck8('自己没满时召唤物不动', summon_list()[0]['hp'], 10);

    // 满血且召唤物满血 → 不浪费
    unset($_SESSION['battle']);
    $u = user_by_id($id);
    $u['hp'] = $u['maxhp']; user_save($u);
    $before = (int) (mats_of($id)['hp_pct50'] ?? 0);
    $u = user_by_id($id);
    $msg3 = drink_pct_potion($u, 'hp_pct50');
    echo "都满时: $msg3\n";
    ck8('都满不扣药', (int) (mats_of($id)['hp_pct50'] ?? 0), $before);
    ck8('都满提示别浪费', str_contains($msg3, '别浪费'), true);

    db()->exec('DELETE FROM equips WHERE uid=' . (int) $id);
    db()->exec('DELETE FROM mats WHERE uid=' . (int) $id);
    db()->exec('DELETE FROM users WHERE id=' . (int) $id);
    $_SESSION = [];
}

echo "OK=$ok FAIL=$bad\n";