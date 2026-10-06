<?php
declare(strict_types=1);

function locations(): array
{
    return [
        'gate' => [
            'name' => '罪渊入口',
            'desc' => '黑雾贴地爬行。石碑上只剩半个「渊」字。远处有火光，像黑市，又像诱饵。',
            'exits' => ['tunnel' => '腐骨甬道', 'shop' => '黑市', 'camp' => '残火营地'],
            'monsters' => ['rat'],
        ],
        'tunnel' => [
            'name' => '腐骨甬道',
            'desc' => '墙里嵌着无名骨。每走一步都有细碎的咀嚼声跟着你。',
            'exits' => ['gate' => '罪渊入口', 'shoal' => '黑血浅滩'],
            'monsters' => ['rat', 'wraith'],
        ],
        'shoal' => [
            'name' => '黑血浅滩',
            'desc' => '浅水反光如凝血。有东西在水面下慢慢转圈。',
            'exits' => ['tunnel' => '腐骨甬道', 'altar' => '深渊祭坛'],
            'monsters' => ['wraith', 'leech'],
        ],
        'altar' => [
            'name' => '深渊祭坛',
            'desc' => '九圈锈铁围着空座。座上没有神像，只有一个朝下的掌印。',
            'exits' => ['shoal' => '黑血浅滩', 'prison' => '遗忘监牢'],
            'monsters' => ['leech', 'guard'],
        ],
        'prison' => [
            'name' => '遗忘监牢',
            'desc' => '铁门全开着。真正的锁不在门上，在你还想不想回头。',
            'exits' => ['altar' => '深渊祭坛'],
            'monsters' => ['guard', 'jailer'],
        ],
        'shop' => [
            'name' => '黑市',
            'desc' => '掌柜没有脸。货架上的东西会自己报出价钱。',
            'exits' => ['gate' => '罪渊入口'],
            'monsters' => [],
        ],
        'camp' => [
            'name' => '残火营地',
            'desc' => '一堆快灭的火。睡在这里能把伤养回来，但罪渊不会因此变浅。',
            'exits' => ['gate' => '罪渊入口'],
            'monsters' => [],
        ],
    ];
}

function loc(string $id): array
{
    $all = locations();
    return $all[$id] ?? $all['gate'];
}

function monsters(): array
{
    return [
        'rat' => ['name' => '腐骨鼠', 'hp' => 14, 'atk' => 4, 'exp' => 6, 'gold' => 4],
        'wraith' => ['name' => '贴墙怨魂', 'hp' => 22, 'atk' => 7, 'exp' => 12, 'gold' => 8],
        'leech' => ['name' => '黑血蛭', 'hp' => 32, 'atk' => 10, 'exp' => 20, 'gold' => 14],
        'guard' => ['name' => '祭坛守卫', 'hp' => 48, 'atk' => 14, 'exp' => 32, 'gold' => 22],
        'jailer' => ['name' => '罪渊狱卒', 'hp' => 80, 'atk' => 18, 'exp' => 80, 'gold' => 50],
    ];
}

function items(): array
{
    return [
        'rusty' => ['name' => '锈刃', 'slot' => 'weapon', 'atk' => 3, 'def' => 0, 'gold' => 28],
        'spike' => ['name' => '铁刺', 'slot' => 'weapon', 'atk' => 6, 'def' => 0, 'gold' => 70],
        'brand' => ['name' => '罪纹短剑', 'slot' => 'weapon', 'atk' => 12, 'def' => 0, 'gold' => 180],
        'cloth' => ['name' => '破布甲', 'slot' => 'armor', 'atk' => 0, 'def' => 2, 'gold' => 24],
        'bone' => ['name' => '骨甲', 'slot' => 'armor', 'atk' => 0, 'def' => 5, 'gold' => 90],
        'potion' => ['name' => '回血药', 'slot' => 'potion', 'atk' => 0, 'def' => 0, 'gold' => 12],
    ];
}

function item_name(string $id): string
{
    if ($id === '') {
        return '无';
    }
    return items()[$id]['name'] ?? $id;
}

function player_atk(array $u): int
{
    $bonus = 0;
    if ($u['weapon'] !== '' && isset(items()[$u['weapon']])) {
        $bonus += (int) items()[$u['weapon']]['atk'];
    }
    return (int) $u['atk'] + $bonus;
}

function player_def(array $u): int
{
    $bonus = 0;
    if ($u['armor'] !== '' && isset(items()[$u['armor']])) {
        $bonus += (int) items()[$u['armor']]['def'];
    }
    return (int) $u['def'] + $bonus;
}

function exp_need(int $lv): int
{
    return $lv * 18;
}

function gain_exp(array &$u, int $exp): string
{
    $u['exp'] = (int) $u['exp'] + $exp;
    $msg = '经验+' . $exp;
    while ((int) $u['exp'] >= exp_need((int) $u['lv'])) {
        $u['exp'] -= exp_need((int) $u['lv']);
        $u['lv'] = (int) $u['lv'] + 1;
        $u['maxhp'] = (int) $u['maxhp'] + 8;
        $u['atk'] = (int) $u['atk'] + 2;
        $u['def'] = (int) $u['def'] + 1;
        $u['hp'] = (int) $u['maxhp'];
        $msg .= '！升级至' . $u['lv'] . '级，伤势尽复';
    }
    return $msg;
}

function dmg_to_monster(array $u): int
{
    return max(1, player_atk($u) + random_int(0, 3));
}

function dmg_to_player(array $u, array $m): int
{
    return max(1, (int) $m['atk'] + random_int(0, 2) - player_def($u));
}
