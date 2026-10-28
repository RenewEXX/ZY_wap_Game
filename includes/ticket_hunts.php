<?php
declare(strict_types=1);

// ============================================================
// 旧副本门票猎场：给"傀儡回廊/雾森祭坛/灰烬深渊/虚空裂隙/岩狱"这类
// 旧副本（原金币开门）提供专属门票掉落 BOSS——每个猎场是一张小地图，
// 地图上只有一只 BOSS，斩了它 0.5% 掉该副本的入场券（由 roll_region_ticket 结算）。
// 傀儡回廊继续用傀儡剧场的 牵线者 做掉落源（已有对应 BOSS），其余 4 个
// 区域没有对应的 BOSS，故额外延展出这 4 张猎场。
// ============================================================

function ticket_hunt_specs(): array
{
    return [
        'sen_hunt' => [
            'loc' => 'sen_hunt',
            'name' => '雾隐猎场',
            'desc' => '雾森深处的禁猎林，雾眼先知盘踞其中。斩了它，雾森祭坛的入场券就掉手里。',
            'from' => 'fo_gate',
            'boss' => 'mist_seer',
            'ticket' => 'sen_ticket',
        ],
        'as_hunt' => [
            'loc' => 'as_hunt',
            'name' => '余烬祭坛',
            'desc' => '火山灰里新掘的祭坛，余烬暴君把灰烬深渊的入场券攥在手里。',
            'from' => 'ember_foothill',
            'boss' => 'ember_lord',
            'ticket' => 'as_ticket',
        ],
        'vx_hunt' => [
            'loc' => 'vx_hunt',
            'name' => '裂隙前庭',
            'desc' => '虚空裂隙的前庭，虚空先驱把入场券当配饰。',
            'from' => 'volcano_heart',
            'boss' => 'void_herald',
            'ticket' => 'vx_ticket',
        ],
        'ry_hunt' => [
            'loc' => 'ry_hunt',
            'name' => '岩窟前哨',
            'desc' => '岩狱前的岩窟，岩狱先锋看守着岩狱的入场券。',
            'from' => 'frostburg_sq',
            'boss' => 'stone_warden',
            'ticket' => 'tt_ticket',
        ],
    ];
}

function ticket_hunt_locations(): array
{
    $out = [];
    foreach (ticket_hunt_specs() as $s) {
        $out[$s['loc']] = [
            'name' => $s['name'],
            'desc' => $s['desc'],
            'exits' => [],
            'monsters' => [$s['boss']],
        ];
    }
    return $out;
}

function ticket_hunt_monsters(): array
{
    return [
        'mist_seer' => ['name' => '雾眼先知', 'hp' => 272160, 'atk' => 2333, 'exp' => 874800, 'gold' => 64800],
        'ember_lord' => ['name' => '余烬暴君', 'hp' => 322560, 'atk' => 2765, 'exp' => 1228800, 'gold' => 76800],
        'void_herald' => ['name' => '虚空先驱', 'hp' => 332640, 'atk' => 2851, 'exp' => 1299600, 'gold' => 79200],
        'stone_warden' => ['name' => '岩狱先锋', 'hp' => 403200, 'atk' => 3456, 'exp' => 1920000, 'gold' => 96000],
    ];
}

function ticket_hunt_lv(): array
{
    return [
        'mist_seer' => 540,
        'ember_lord' => 640,
        'void_herald' => 660,
        'stone_warden' => 800,
    ];
}

function ticket_hunt_element(): array
{
    return [
        'mist_seer' => 'wind',
        'ember_lord' => 'fire',
        'void_herald' => 'dark',
        'stone_warden' => 'thunder',
    ];
}

function ticket_hunt_skills(): array
{
    return [
        'mist_seer' => ['atk' => '雾眼凝视', 'skills' => [['name' => '雾隐摄魂', 'rate' => 30, 'mult' => 2.2, 'psilence' => 1]]],
        'ember_lord' => ['atk' => '余烬重击', 'skills' => [['name' => '烬浪倾泻', 'rate' => 30, 'mult' => 2.2, 'el' => 'fire', 'dot' => [3, 3]]]],
        'void_herald' => ['atk' => '虚空撕裂', 'skills' => [['name' => '先驱裂隙', 'rate' => 30, 'mult' => 2.2, 'rush' => true]]],
        'stone_warden' => ['atk' => '岩窟重拳', 'skills' => [['name' => '岩狱崩塌', 'rate' => 30, 'mult' => 2.4, 'pstun' => 1]]],
    ];
}

// BOSS mid -> 门票材料（供 roll_region_ticket 使用）
function ticket_hunt_ticket_map(): array
{
    $out = [];
    foreach (ticket_hunt_specs() as $s) {
        $out[$s['boss']] = $s['ticket'];
    }
    return $out;
}