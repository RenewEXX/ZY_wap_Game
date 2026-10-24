<?php
declare(strict_types=1);

// ============================================================
// 大区域专属副本（世界扩张区域：天空岛 / 沉没圣所 / 七主题大区）
// ------------------------------------------------------------
// 世界扩张把新区域做成了"野外大地图"，但没有真正的"副本"。本模块给每个
// 新大区补一个专属副本：走地图入口进出、进副本自动计时(45min)、超时弹回
// 该区根据地，副本深处 BOSS 掉「专属传说」，并凑一套「副本套装」（含部件
// 词条 + 集齐加成，全部随角色等级缩放，符合区域等级区间）。
//
// 接入点（均为小改，参数化）：
//   - region_dungeon_locations() 并入 locations()
//   - region_dungeon_maps()      并入 dungeon_maps()
//   - region_dungeon_monsters() 并入 monsters()
//   - lv/element/skills/drops/boss_map 并入 world_expansion.php 的聚合函数
//   - region_dungeon_gear_apply() 在 gear_stats() 末尾调用（套装集齐加成）
//
// 品质约定：副本小怪掉部件（品质封顶史诗），BOSS 掉专属传说；套装件命名
//   统一用 "<区字>·<部件>"，靠前缀识别套装（与既有套装机制一致）。
// ============================================================

function region_dungeon_specs(): array
{
    // 每个大区一个副本。maps 顺序即深入顺序；最后一张是 BOSS 房。
    // set: 3 部位（body / gloves / legs 各一件），配 <区字>· 前缀。
    // legend: BOSS 专属传说（单一部件）。
    return [
        // ============ 天空岛·浮空列岛 120~195 ============
        'sky' => [
            'name' => '苍穹回廊',
            'region' => '天空岛·浮空列岛',
            'gate' => 'sky_gale_shrine',     // 从风蚀神殿深处起航
            'min_lv' => 120,
            'maps' => [
                ['rd_sky_door', '苍穹回廊·浮门', '回廊浮在云上，每块砖都在缓缓错位。', ['rd_sky_drifter']],
                ['rd_sky_gallery', '浮空画廊', '半空悬着的画框里，画的全是来过的冒险者。', ['rd_sky_drifter', 'rd_sky_gust']],
                ['rd_sky_choir', '风咏回廊', '无人的唱诗班还在吟诵，吟得云都往下沉。', ['rd_sky_gust']],
                ['rd_sky_vault', '穹顶之核', '整个回廊的心脏悬在这里，一团会呼吸的风盘在它上面。', ['rd_sky_warden']],
            ],
            'mobs' => [
                ['rd_sky_drifter', '浮空幻影', 155, 'wind', 'drift'],
                ['rd_sky_gust', '狂风气旋', 170, 'wind', 'gust'],
            ],
            'boss' => ['rd_sky_warden', '裂风之喉·呜咽', 195, 'thunder', 'warden'],
            'legend' => ['slot' => 'weapon', 'base' => '裂帛', 'k' => 'skill_damage', 'v' => 6.0],
            'set' => [
                'prefix' => '苍穹',
                'pieces' => [
                    ['weapon', '苍穹·断云刃'],
                    ['offhand', '苍穹·听风幡'],
                    ['body', '苍穹·逐鹞服'],
                    ['head', '苍穹·御风箍'],
                    ['legs', '苍穹·掠天裤'],
                    ['gloves', '苍穹·引线手甲'],
                    ['shoes', '苍穹·凌虚靴'],
                ],
            ],
        ],

        // ============ 沉没圣所·海渊 90~160 ============
        'sunk' => [
            'name' => '溺海神龛',
            'region' => '沉没圣所·海渊',
            'gate' => 'sunk_queen',
            'min_lv' => 90,
            'maps' => [
                ['rd_sunk_step', '溺海神龛·水阶', '台阶没在水下，走一步，泡一盏灯。', ['rd_sunk_lurker']],
                ['rd_sunk_hall', '沉没祷厅', '祷词还在水里回荡，尸体跪成一圈。', ['rd_sunk_lurker', 'rd_sunk_coral']],
                ['rd_sunk_deep', '海渊深处', '水压大得能听见自己骨头发响。', ['rd_sunk_coral']],
                ['rd_sunk_shrine', '神龛之心', '一座沉在海底的祭龛，龛里坐着一具还在念经的湿尸。', ['rd_sunk_hierophant']],
            ],
            'mobs' => [
                ['rd_sunk_lurker', '溺影潜伏者', 120, 'dark', 'lurk'],
                ['rd_sunk_coral', '魔化珊瑚卫', 145, 'ice', 'coral'],
            ],
            'boss' => ['rd_sunk_hierophant', '咸海诵经者·溺声', 160, 'dark', 'hiero'],
            'legend' => ['slot' => 'necklace', 'base' => '溺声', 'k' => 'damage_reduction', 'v' => 5.0],
            'set' => [
                'prefix' => '溺海',
                'pieces' => [
                    ['weapon', '溺海·骨潮鱼叉'],
                    ['offhand', '溺海·蚌生盾'],
                    ['body', '溺海·泡尸衣'],
                    ['head', '溺海·海藻幞'],
                    ['legs', '溺海·沉锚裤'],
                    ['gloves', '溺海·缠鳍手套'],
                    ['shoes', '溺海·踩水屐'],
                ],
            ],
        ],

        // ============ 熔金工坊 200~255 ============
        'forge' => [
            'name' => '永燃锻炉',
            'region' => '熔金工坊',
            'gate' => 'forge_throne',
            'min_lv' => 200,
            'maps' => [
                ['rd_forge_furnace', '永燃锻炉·赤焰膛', '炉火七百年没熄过，也烧不化墙上的影子。', ['rd_forge_ember']],
                ['rd_forge_chain', '铁链长廊', '悬着的铁链自己晃，链尾系着烧红的铁球。', ['rd_forge_ember', 'rd_forge_guard']],
                ['rd_forge_slagpit', '熔渣深坑', '坑底的渣里裹着没炼完的人。', ['rd_forge_guard']],
                ['rd_forge_forgeheart', '锻炉之心', '永不熄灭的核心，一口被炉火养了七百年的活炉盘在正中。', ['rd_forge_lord']],
            ],
            'mobs' => [
                ['rd_forge_ember', '赤焰余烬', 235, 'fire', 'ember'],
                ['rd_forge_guard', '锻炉守卫', 250, 'thunder', 'guard'],
            ],
            'boss' => ['rd_forge_lord', '吞炉者·不熄', 255, 'fire', 'lord'],
            'legend' => ['slot' => 'body', 'base' => '不熄', 'k' => 'damage_reduction', 'v' => 6.0],
            'set' => [
                'prefix' => '永燃',
                'pieces' => [
                    ['weapon', '永燃·敲骨锤'],
                    ['offhand', '永燃·淬火砧盾'],
                    ['body', '永燃·炉皮围裙'],
                    ['head', '永燃·防火面罩'],
                    ['legs', '永燃·踏渣靴'],
                    ['gloves', '永燃·铁匠护掌'],
                    ['shoes', '永燃·风箱履'],
                ],
            ],
        ],

        // ============ 青藤古林 290~345 ============
        'verd' => [
            'name' => '万藤古祠',
            'region' => '青藤古林',
            'gate' => 'verd_crown',
            'min_lv' => 290,
            'maps' => [
                ['rd_verd_path', '万藤古祠·藤径', '藤蔓自己让开一条路，又在身后合上。', ['rd_verd_creeper']],
                ['rd_verd_altar', '苔痕祭坛', '祭坛上长满苔，苔下压着供奉的白骨。', ['rd_verd_creeper', 'rd_verd_bloom']],
                ['rd_verd_deep', '古木之心', '一棵活了万年的树，树心是空的。', ['rd_verd_bloom']],
                ['rd_verd_sanctum', '万藤圣所', '藤母盘在无边的绿意里，做梦都在生长。', ['rd_verd_mother']],
            ],
            'mobs' => [
                ['rd_verd_creeper', '缠魂藤', 325, 'wind', 'creeper'],
                ['rd_verd_bloom', '食人花妖', 340, 'dark', 'bloom'],
            ],
            'boss' => ['rd_verd_mother', '缠木胎·绿眠', 345, 'wind', 'mother'],
            'legend' => ['slot' => 'gloves', 'base' => '缠心', 'k' => 'lifesteal', 'v' => 4.0],
            'set' => [
                'prefix' => '万藤',
                'pieces' => [
                    ['weapon', '万藤·绞根杖'],
                    ['offhand', '万藤·苞叶盾'],
                    ['body', '万藤·树皮背心'],
                    ['head', '万藤·年轮冠'],
                    ['legs', '万藤·藤编裤'],
                    ['gloves', '万藤·攀援手套'],
                    ['shoes', '万藤·苔履'],
                ],
            ],
        ],

        // ============ 霜魄墓园 380~440 ============
        'crypt' => [
            'name' => '霜魄陵寝',
            'region' => '霜魄墓园',
            'gate' => 'crypt_bell',
            'min_lv' => 380,
            'maps' => [
                ['rd_crypt_step', '霜魄陵寝·冰阶', '台阶结着霜，每下一步，蜡烛就多一盏。', ['rd_crypt_wraith']],
                ['rd_crypt_hall', '亡者纪念堂', '碑上刻着还没死的名字。', ['rd_crypt_wraith', 'rd_crypt_reaper']],
                ['rd_crypt_ossuary', '骨库', '骨头堆到顶，堆里偶尔动一下。', ['rd_crypt_reaper']],
                ['rd_crypt_tomb', '霜魄主墓', '主墓的冰棺裂了缝，缝里有只眼睛，一直睁着。', ['rd_crypt_king']],
            ],
            'mobs' => [
                ['rd_crypt_wraith', '霜魄怨灵', 410, 'ice', 'wraith'],
                ['rd_crypt_reaper', '陵寝收魂者', 430, 'dark', 'reaper'],
            ],
            'boss' => ['rd_crypt_king', '棺中未眠者·僵卧', 440, 'ice', 'king'],
            'legend' => ['slot' => 'ring', 'base' => '未亡', 'k' => 'critdmg', 'v' => 6.0],
            'set' => [
                'prefix' => '霜魄',
                'pieces' => [
                    ['weapon', '霜魄·冻棺刀'],
                    ['offhand', '霜魄·碑石盾'],
                    ['body', '霜魄·裹尸霜衣'],
                    ['head', '霜魄·守夜面具'],
                    ['legs', '霜魄·缄默裤'],
                    ['gloves', '霜魄·白事手套'],
                    ['shoes', '霜魄·涉霜履'],
                ],
            ],
        ],

        // ============ 星陨荒原 470~525 ============
        'star' => [
            'name' => '陨星坑',
            'region' => '星陨荒原',
            'gate' => 'star_titan_seat',
            'min_lv' => 470,
            'maps' => [
                ['rd_star_rim', '陨星坑·环坑', '坑壁一圈圈同心，像被谁敲过无数下。', ['rd_star_shard']],
                ['rd_star_crater', '焦灼坑心', '坑心的石头还烫，踩上去滋滋响。', ['rd_star_shard', 'rd_star_flame']],
                ['rd_star_tunnel', '陨铁隧道', '隧道壁是凝固的星铁，敲一下回三下音。', ['rd_star_flame']],
                ['rd_star_core', '星核', '星核跳到一半停住了，一个甲虫似的大家伙把它按在爪下。', ['rd_star_lord']],
            ],
            'mobs' => [
                ['rd_star_shard', '星屑精怪', 500, 'thunder', 'shard'],
                ['rd_star_flame', '陨火元素', 515, 'fire', 'flame'],
            ],
            'boss' => ['rd_star_lord', '抱星巨甲·坠光', 525, 'thunder', 'lord'],
            'legend' => ['slot' => 'head', 'base' => '坠光', 'k' => 'crit', 'v' => 6.0],
            'set' => [
                'prefix' => '星陨',
                'pieces' => [
                    ['weapon', '星陨·陨心巨刃'],
                    ['offhand', '星陨·灼坑盾'],
                    ['body', '星陨·熔壳甲'],
                    ['head', '星陨·窥星目镜'],
                    ['legs', '星陨·陨行裤'],
                    ['gloves', '星陨·拾星手甲'],
                    ['shoes', '星陨·残轨靴'],
                ],
            ],
        ],

        // ============ 渊海熔潮 560~615 ============
        'magma' => [
            'name' => '熔潮深渊',
            'region' => '渊海熔潮',
            'gate' => 'magma_lord_hall',
            'min_lv' => 560,
            'maps' => [
                ['rd_magma_surface', '熔潮深渊·浮岸', '熔海在脚下翻浪，浪是凝固的橙色。', ['rd_magma_crawler']],
                ['rd_magma_tide', '赤潮', '一道熔浪压过来，退下去时少了两个人。', ['rd_magma_crawler', 'rd_magma_tide']],
                ['rd_magma_depths', '熔底暗渊', '熔海底下还有更黑的水，水里游着不该游的东西。', ['rd_magma_tide']],
                ['rd_magma_throne', '熔潮之座', '熔潮之座是一整条活着的熔鲸，脊背拱出浪尖。', ['rd_magma_overlord']],
            ],
            'mobs' => [
                ['rd_magma_crawler', '熔潮蟹将', 590, 'fire', 'crawler'],
                ['rd_magma_tide', '赤焰潮灵', 605, 'fire', 'tide'],
            ],
            'boss' => ['rd_magma_overlord', '熔海吞潮鲸·焰喉', 615, 'fire', 'crawler'],
            'legend' => ['slot' => 'weapon', 'base' => '潮脊', 'k' => 'truedmg', 'v' => 120.0],
            'set' => [
                'prefix' => '熔潮',
                'pieces' => [
                    ['weapon', '熔潮·裂浪齿'],
                    ['offhand', '熔潮·鳍骨盾'],
                    ['body', '熔潮·鲸皮甲'],
                    ['head', '熔潮·喷潮颅'],
                    ['legs', '熔潮·踏浪裤'],
                    ['gloves', '熔潮·灼鳃手套'],
                    ['shoes', '熔潮·浮板履'],
                ],
            ],
        ],

        // ============ 时空回廊 660~725 ============
        'chrono' => [
            'name' => '凝固时刻',
            'region' => '时空回廊',
            'gate' => 'chrono_paradox_core',
            'min_lv' => 660,
            'maps' => [
                ['rd_chrono_gate', '凝固时刻·停摆之门', '门里的钟停在你抬手的一刻，走进去就是走回去。', ['rd_chrono_echo']],
                ['rd_chrono_corridor', '倒流长廊', '走廊逼你倒着走，走一步年轻一岁。', ['rd_chrono_echo', 'rd_chrono_loop']],
                ['rd_chrono_void', '时之断崖', '崖下没有底，掉下去的都不曾发生。', ['rd_chrono_loop']],
                ['rd_chrono_core', '凝固之刻', '所有时间在这个点冻住，一个披着钟表的影子在擦它的指针。', ['rd_chrono_master']],
            ],
            'mobs' => [
                ['rd_chrono_echo', '时序残响', 695, 'thunder', 'echo'],
                ['rd_chrono_loop', '循环游灵', 710, 'light', 'loop'],
            ],
            'boss' => ['rd_chrono_master', '擦钟者·偷秒', 725, 'thunder', 'master'],
            'legend' => ['slot' => 'gloves', 'base' => '偷秒', 'k' => 'skill_damage', 'v' => 9.0],
            'set' => [
                'prefix' => '凝固',
                'pieces' => [
                    ['weapon', '凝固·卡壳刃'],
                    ['offhand', '凝固·停摆盾'],
                    ['body', '凝固·钟摆甲'],
                    ['head', '凝固·齿轮冕'],
                    ['legs', '凝固·回拨裤'],
                    ['gloves', '凝固·发条手甲'],
                    ['shoes', '凝固·迟滞靴'],
                ],
            ],
        ],

        // ============ 天外神墟 900~980 ============
        'divine' => [
            'name' => '墟心圣殿',
            'region' => '天外神墟',
            'gate' => 'divine_end',
            'min_lv' => 900,
            'maps' => [
                ['rd_divine_step', '墟心圣殿·神阶', '阶梯每一级都刻着一个神名，踩上去，名字就灭了。', ['rd_divine_seraph']],
                ['rd_divine_nave', '圣殿中殿', '穹顶破了个洞，光直直落在一具没有脸的圣像上。', ['rd_divine_seraph', 'rd_divine_wraith']],
                ['rd_divine_vault', '神骸之库', '库里的神骸自己发光，照得人影子都变白。', ['rd_divine_wraith']],
                ['rd_divine_core', '墟心', '圣殿的心是一个正在苏醒的空洞，空洞里伸出一只手，把神名一个个摘下来。', ['rd_divine_god']],
            ],
            'mobs' => [
                ['rd_divine_seraph', '堕落炽天使', 940, 'light', 'seraph'],
                ['rd_divine_wraith', '神罚怨灵', 960, 'dark', 'wraith'],
            ],
            'boss' => ['rd_divine_god', '摘神者·空座', 980, 'light', 'god'],
            'legend' => ['slot' => 'necklace', 'base' => '空座', 'k' => 'all_attr', 'v' => 55.0],
            'set' => [
                'prefix' => '墟心',
                'pieces' => [
                    ['weapon', '墟心·摘名刃'],
                    ['offhand', '墟心·无面盾'],
                    ['body', '墟心·空壳袍'],
                    ['head', '墟心·摘星冠'],
                    ['legs', '墟心·坠神裤'],
                    ['gloves', '墟心·神骸手甲'],
                    ['shoes', '墟心·踏墟靴'],
                ],
            ],
        ],
    ];
}

// 由 spec 生成全部派生结构（记忆化）
function region_dungeon_build(): array
{
    static $b = null;
    if ($b !== null) {
        return $b;
    }
    $loc = []; $mon = []; $lv = []; $el = []; $bossMap = []; $bosses = [];
    $drops = []; $skills = []; $maps = []; $gateOf = []; $minLv = []; $setByPrefix = []; $legendByMid = [];

    foreach (region_dungeon_specs() as $rk => $R) {
        $mgid = 'rd_' . $rk . '_gate';
        $maps[$rk] = [$mgid];

        // 每个副本的"入口层"：接一扇从野外 / 前一层进入的门
        $gateDesc = '副本「' . $R['name'] . '」入口。踏进去开始计时，45分钟内要么通关，要么被弹回根据地。';
        $loc[$mgid] = [
            'name' => $R['name'] . '·入口',
            'desc' => $gateDesc,
            'exits' => [],
            'monsters' => [],
        ];
        $gateOf[$mgid] = $mgid;
        $minLv[$mgid] = (int) $R['min_lv'];

        // 串联内部地图：入口 → 各层 → BOSS 房，双向连通
        $chain = [$mgid];
        foreach ($R['maps'] as $i => $M) {
            [$id, $nm, $desc, $mobs] = $M;
            $loc[$id] = ['name' => $nm, 'desc' => $desc, 'exits' => [], 'monsters' => $mobs];
            $maps[$rk][] = $id;
            $gateOf[$id] = $mgid;
            $minLv[$id] = (int) $R['min_lv'];
            $chain[] = $id;
        }
        // 双向连线
        for ($i = 0; $i < count($chain) - 1; $i++) {
            $a = $chain[$i]; $c = $chain[$i + 1];
            $loc[$a]['exits'][$c] = (string) ($loc[$c]['name'] ?? $c);
            $loc[$c]['exits'][$a] = (string) ($loc[$a]['name'] ?? $a);
        }

        // 小怪（沿用 we2 线性缩放口径：hp=lv*175、atk=lv*2.6，identity 体系下 base 即最终值）
        foreach ($R['mobs'] as $M) {
            [$mid, $nm, $mlv, $mel, $atkName] = $M;
            $hp = (int) round($mlv * 180);
            $atk = (int) round($mlv * 2.7);
            $exp = (int) ($mlv * $mlv * 2);
            $gold = $mlv * 62;
            $mon[$mid] = ['name' => $nm, 'hp' => $hp, 'atk' => $atk, 'exp' => $exp, 'gold' => $gold];
            $lv[$mid] = $mlv;
            $el[$mid] = $mel;
            $drops[$mid] = region_dungeon_mob_drop($rk, $M, $R);
            $skills[$mid] = ['atk' => $atkName, 'skills' => [['name' => $nm . '·扑击', 'rate' => 22, 'mult' => 1.5, 'el' => $mel]]];
        }

        // BOSS（比小怪更厚更疼：hp=lv*175*2.8、atk=lv*2.6*1.6，与 we2 BOSS 一致）
        [$bmid, $bnm, $blv, $bel, $batk] = $R['boss'];
        $bhp = (int) round($blv * 180 * 2.8);
        $batkVal = (int) round($blv * 2.7 * 1.6);
        $bexp = (int) ($blv * $blv * 3);
        $bgold = $blv * 125;
        $mon[$bmid] = ['name' => $bnm, 'hp' => $bhp, 'atk' => $batkVal, 'exp' => $bexp, 'gold' => $bgold];
        $lv[$bmid] = $blv;
        $el[$bmid] = $bel;
        $bossMap[$R['maps'][count($R['maps']) - 1][0]] = $bmid; // 最后一张图 = BOSS 房
        $bosses[] = $bmid;

        // BOSS 掉专属传说；并把套装件挂到 BOSS（随机一件，掉率更高，凑齐 7 件靠 BOSS 更稳）
        $L = $R['legend'];
        $pieces = (array) ($R['set']['pieces'] ?? []);
        $sIdx = count($pieces) > 0 ? (crc32($bmid) % count($pieces)) : 0;
        $setSlot = count($pieces) > 0 ? $pieces[$sIdx][0] : 'body';
        $setBase = count($pieces) > 0 ? $pieces[$sIdx][1] : '套装残片';
        $drops[$bmid] = [
            'slot' => (string) $L['slot'],
            'base' => (string) $L['base'],
            'rate' => 0.42,
            'q' => [0, 0, 10, 40, 50],
            'legendary' => ['base' => (string) $L['base'], 'extra' => ['k' => (string) $L['k'], 'v' => (float) $L['v']]],
            // BOSS 额外"保底风格"地掉一件套装件（与传说同样按部位走，quest 掉落里单独判定）
            'set_piece' => ['slot' => $setSlot, 'base' => $setBase],
        ];
        $legendByMid[$bmid] = (string) $L['base'];
        $skills[$bmid] = ['atk' => $batk, 'skills' => [
            ['name' => $bnm . '·重压', 'rate' => 28, 'mult' => 2.5, 'el' => $bel],
            ['name' => $bnm . '·震击', 'rate' => 15, 'mult' => 1.8, 'dot' => [3, 3]],
            ['name' => $bnm . '·壁障', 'rate' => 12, 'mult' => 0.5, 'guard' => 3],
        ]];

        // 套装前缀 → 部件
        $setByPrefix[$R['set']['prefix']] = [
            'prefix' => (string) $R['set']['prefix'],
            'pieces' => array_map(fn($p) => $p[1], $R['set']['pieces']),
            'region' => (string) $R['region'],
        ];
    }

    return [
        'loc' => $loc, 'mon' => $mon, 'lv' => $lv, 'el' => $el,
        'bossMap' => $bossMap, 'bosses' => $bosses, 'drops' => $drops,
        'skills' => $skills, 'maps' => $maps, 'gateOf' => $gateOf,
        'minLv' => $minLv, 'setByPrefix' => $setByPrefix, 'legendByMid' => $legendByMid,
    ];
}

// 小怪掉配套装件：每次击杀从该套 7 件里随机挑一件（磨齐整套）。
// 不同小怪可掉重叠部件，靠多刷凑齐 7 件。
function region_dungeon_mob_drop(string $rk, array $M, array $R): array
{
    $mid = (string) $M[0];
    $pieces = (array) ($R['set']['pieces'] ?? []);
    if ($pieces === []) {
        return ['slot' => 'body', 'base' => '残片', 'rate' => 0.05, 'q' => [0, 20, 50, 30, 0]];
    }
    // 用 mid 做种子决定"主要掉哪个部位"，让不同小怪有偏好、又不至于死板
    $seed = crc32($mid);
    $idx = $seed % count($pieces);
    [$slot, $base] = $pieces[$idx];
    return ['slot' => $slot, 'base' => $base, 'rate' => 0.05, 'q' => [0, 20, 50, 30, 0]];
}

// ---- 对外访问器 ----
function region_dungeon_locations(): array { return region_dungeon_build()['loc']; }
function region_dungeon_monsters(): array { return region_dungeon_build()['mon']; }
function region_dungeon_lv(): array { return region_dungeon_build()['lv']; }
function region_dungeon_element(): array { return region_dungeon_build()['el']; }
function region_dungeon_boss_map(): array { return region_dungeon_build()['bossMap']; }
function region_dungeon_bosses(): array { return region_dungeon_build()['bosses']; }
function region_dungeon_drops(): array { return region_dungeon_build()['drops']; }
function region_dungeon_skills(): array { return region_dungeon_build()['skills']; }
function region_dungeon_maps(): array { $m = []; foreach (region_dungeon_build()['maps'] as $list) { foreach ($list as $id) { $m[] = $id; } } return $m; }
function region_dungeon_set_prefixes(): array { return array_keys(region_dungeon_build()['setByPrefix']); }
function region_dungeon_set_info(string $prefix): array { return region_dungeon_build()['setByPrefix'][$prefix] ?? []; }

// 这个地点属于哪个副本入口（用于 dungeon_tick 统一计时/弹回）；非副本返回空串
function region_dungeon_of(string $loc): string
{
    $g = region_dungeon_build()['gateOf'];
    return (string) ($g[$loc] ?? '');
}

// 副本入口对应的"根据地/eject 点"：从该副本对应的野外大区决定
function region_dungeon_eject(string $loc): string
{
    $gate = region_dungeon_of($loc);
    if ($gate === '') {
        return 'town_sq';
    }
    // 把副本映射回它的区域，再找该区域的 camp（根据地）
    foreach (region_dungeon_specs() as $rk => $R) {
        if ('rd_' . $rk . '_gate' === $gate) {
            $camp = $rk . '_camp';
            // 天空岛/沉没没有 we2 camp：用最近的野外点
            if ($rk === 'sky') { return 'sky_ferry'; }
            if ($rk === 'sunk') { return 'sunk_shore'; }
            return $camp;
        }
    }
    return 'town_sq';
}

// 副本最低进入等级（玩家等级低于此则进不去）
function region_dungeon_min_lv(string $loc): int
{
    return (int) (region_dungeon_build()['minLv'][$loc] ?? 0);
}

// 套装集齐加成：在 gear_stats() 末尾调用。按角色等级缩放，卡在区域等级区间使用。
// 7 件套，最低 3 件起效，3/5/7 三档递进。
function region_dungeon_gear_apply(int $uid, array &$s): void
{
    $info = region_dungeon_build()['setByPrefix'];
    if ($info === []) {
        return;
    }
    $st = db()->prepare('SELECT name FROM equips WHERE uid=? AND pos="wear"');
    $st->execute([$uid]);
    $worn = [];
    while ($r = $st->fetch()) {
        $worn[] = equip_shortname((string) $r['name']);
    }
    if ($worn === []) {
        return;
    }
    $ulv = max(1, (int) (db()->query('SELECT lv FROM users WHERE id=' . $uid)->fetchColumn() ?: 1));
    foreach ($info as $prefix => $set) {
        $n = 0;
        foreach ($set['pieces'] as $piece) {
            if (in_array($piece, $worn, true)) { $n++; }
        }
        // 最低 3 件才有效果
        if ($n < 3) {
            continue;
        }
        // 3件起基础档：攻击/法攻/生命 随等级
        $s['atk']  = (int) ($s['atk'] ?? 0) + (int) round($ulv * 1.2) * $n;
        $s['matk'] = (int) ($s['matk'] ?? 0) + (int) round($ulv * 1.2) * $n;
        $s['hp']   = (int) ($s['hp'] ?? 0) + (int) round($ulv * 6) * $n;
        $s['skill_damage'] = (float) ($s['skill_damage'] ?? 0) + 6.0;
        $s['truedmg'] = (float) ($s['truedmg'] ?? 0) + $ulv * 4;
        // 5件档
        if ($n >= 5) {
            $s['damage_reduction'] = (float) ($s['damage_reduction'] ?? 0) + 6.0;
            $s['lifesteal'] = (float) ($s['lifesteal'] ?? 0) + 2.0;
            $s['truedmg'] = (float) ($s['truedmg'] ?? 0) + $ulv * 8;
            $s['all_attr'] = (float) ($s['all_attr'] ?? 0) + 3.0;
        }
        // 7件满档（整套）
        if ($n >= 7) {
            $s['atk_pct'] = (float) ($s['atk_pct'] ?? 0) + 8.0;
            $s['matk_pct'] = (float) ($s['matk_pct'] ?? 0) + 8.0;
            $s['damage_reduction'] = (float) ($s['damage_reduction'] ?? 0) + 8.0;
            $s['skill_damage'] = (float) ($s['skill_damage'] ?? 0) + 10.0;
            $s['truedmg'] = (float) ($s['truedmg'] ?? 0) + $ulv * 12;
            $s['all_attr'] = (float) ($s['all_attr'] ?? 0) + 5.0;
        }
    }
}

// 套装说明文案（给 equip.php 展示）
function region_dungeon_set_bonus_text(string $prefix): string
{
    $set = region_dungeon_set_info($prefix);
    if ($set === []) {
        return '';
    }
    $region = $set['region'] ?? '';
    return '【' . $prefix . '套装】' . $region . '副本套装，集齐件数决定加成（最低3件生效）——'
        . '每件：攻击/法攻+等级×1.2、生命+等级×6；'
        . '3件：全伤害+6%、真伤+等级×4；'
        . '5件：再+减伤6%、吸血2%、真伤+等级×8、全属性+3；'
        . '7件：再+物攻/法攻+8%、减伤+8%、全伤害+10%、真伤+等级×12、全属性+5！';
}

// 该前缀是否是"副本套装"
function region_dungeon_is_set_prefix(string $prefix): bool
{
    return isset(region_dungeon_build()['setByPrefix'][$prefix]);
}

// 某个 BOSS mid 是否掉落副本套装件；返回 ['slot'=>,base=>] 或 null
function region_dungeon_boss_set_piece(string $mid): ?array
{
    $d = region_dungeon_build()['drops'][$mid] ?? null;
    if (is_array($d) && isset($d['set_piece'])) {
        return $d['set_piece'];
    }
    return null;
}

// 若某件装备短名属于某个副本套装，返回该套装前缀，否则空串
function region_dungeon_prefix_of(string $shortName): string
{
    foreach (array_keys(region_dungeon_build()['setByPrefix']) as $prefix) {
        if (str_starts_with($shortName, $prefix . '·')) {
            return $prefix;
        }
    }
    return '';
}
