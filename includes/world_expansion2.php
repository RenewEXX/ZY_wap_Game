<?php
declare(strict_types=1);

// ============================================================
// 世界扩张 第二弹：7 个不同主题 / 不同等级区间的大型区域（每区 12 张小地图）
// 通过统一的 spec 生成 locations / monsters / lv / element / boss_of_map /
// bosses / drops / skills / regions / 入口连线，再被 world_expansion.php 聚合。
// 槽位规则（与上一批保持一致）：
//   - 8 只小怪依次覆盖 主手/副手/上身/头部/下身/腰部/手套/鞋子
//   - 3 个 BOSS 依次覆盖 背部/戒指/项链，且各掉一件专属传说
//   - 小怪品质由 roll_drop 封顶史诗，只有 BOSS 出传说
// ============================================================

function we2_specs(): array
{
    return [
        // ================= 1. 熔金工坊（火/雷·机巧） 200~255 =================
        'forge' => [
            'name' => '熔金工坊',
            'hub' => 'capital_gate',
            'mobs' => [
                ['forge_slag', '熔渣傀儡', 200, 'fire', '熔渣战锤'],
                ['forge_piston', '活塞卫兵', 210, 'fire', '活塞护盾'],
                ['forge_spark', '电火花精', 215, 'thunder', '导能铁甲'],
                ['forge_bellows', '风箱兽', 220, 'fire', '风箱头盔'],
                ['forge_anvil', '铁砧魔像', 225, 'fire', '铁砧护腿'],
                ['forge_cog', '齿轮精', 230, 'thunder', '齿轮束带'],
                ['forge_smith', '堕炉匠', 235, 'fire', '炉火护手'],
                ['forge_furnace', '熔炉行尸', 240, 'fire', '灰烬战靴'],
            ],
            'bosses' => [
                ['forge_overseer', '熔金监工', 245, 'fire', '监工披风', '熔金监工·赤铁之翼', 'damage_reduction', 12.0],
                ['forge_king', '熔炉之王·卡尔恩', 250, 'fire', '王座护符', '熔炉之王·炽心戒', 'critdmg', 4.0],
                ['forge_titan', '巨炉泰坦', 255, 'thunder', '泰坦颈环', '巨炉泰坦·雷鸣颈环', 'skill_damage', 7.0],
            ],
            'maps' => [
                ['forge_gate', '熔金工坊·正门', '铁水沿沟渠流成河，蒸汽从锈管里喷出。守卫的关节里塞满齿轮。', ['forge_slag']],
                ['forge_floor', '铸造车间', '传送带永不停歇，把烧红的胚料送进深渊般的炉口。', ['forge_piston']],
                ['forge_spark_hall', '电光回廊', '头顶电弧噼啪，火花精在电线上筑巢。', ['forge_spark']],
                ['forge_pit', '熔铁深井', '井底是流动的铁水。监工悬在铁链上，俯视每一个下来的人。', ['forge_overseer']],
                ['forge_bellows', '风箱巨室', '巨大的风箱一呼一吸，整座工坊跟着喘息。', ['forge_bellows']],
                ['forge_anvil_yard', '铁砧广场', '成排的铁砧自己砸自己，砸得满地火星。', ['forge_anvil']],
                ['forge_gear_garden', '齿轮庭', '齿轮咬合成林，齿轮精在齿缝里穿行。', ['forge_cog']],
                ['forge_throne', '炉心王座', '熔炉之王坐在赤红的王座上，铁水是他的披风。', ['forge_king']],
                ['forge_quench', '淬火池', '池水瞬间汽化，堕炉匠在蒸汽里锻造“人”。', ['forge_smith']],
                ['forge_ash', '灰烬长道', '满地炉渣，熔炉行尸从灰里一节节爬起来。', ['forge_furnace']],
                ['forge_heart', '工坊核心', '所有管道都汇向这里，炽热得看不清路。', ['forge_slag', 'forge_piston']],
                ['forge_colossus', '巨炉之膛', '巨炉泰坦盘踞在最大的熔炉里，它的胸腔就是炉膛。', ['forge_titan']],
            ],
        ],
        // ================= 2. 青藤古林（风/自然） 290~345 =================
        'verd' => [
            'name' => '青藤古林',
            'hub' => 'theater',
            'mobs' => [
                ['verd_sprout', '藤蔓幼体', 290, 'wind', '藤鞭'],
                ['verd_guardian', '树皮守卫', 300, 'wind', '木皮盾'],
                ['verd_wisp', '萤光林灵', 305, 'light', '林光法袍'],
                ['verd_thorn', '荆棘猎兽', 310, 'wind', '荆冠'],
                ['verd_treant', '古藤树人', 315, 'wind', '树根胫甲'],
                ['verd_serpent', '藤蟒', 320, 'dark', '藤蟒束腰'],
                ['verd_druid', '堕林德鲁伊', 325, 'wind', '德鲁伊手环'],
                ['verd_beetle', '巨角甲虫', 330, 'wind', '甲虫硬靴'],
            ],
            'bosses' => [
                ['verd_matriarch', '古藤母树', 335, 'wind', '母树披叶', '古藤母树·万叶之翼', 'damage_reduction', 13.0],
                ['verd_stag', '银角鹿王', 340, 'light', '鹿王指环', '银角鹿王·月华戒', 'all_attr', 30.0],
                ['verd_hollow', '空心巨树', 345, 'dark', '空心颈链', '空心巨树·渊苔链', 'lifesteal', 2.0],
            ],
            'maps' => [
                ['verd_gate', '青藤古林·林门', '藤蔓拱成门，门后树影比人高。幼体在藤上缓慢蠕动。', ['verd_sprout']],
                ['verd_bark', '树皮岗哨', '树皮守卫贴在树干上，动起来像一整面墙在走。', ['verd_guardian']],
                ['verd_glow', '萤光林径', '腐木上全是萤光，林灵在光点间打盹。', ['verd_wisp']],
                ['verd_thornnest', '荆刺巢', '荆棘猎兽在刺丛里伏击，古藤母树的根须缠着整片巢。', ['verd_matriarch']],
                ['verd_roots', '盘根地穴', '树根像血管一样搏动，荆棘猎兽贴着根须游走。', ['verd_thorn']],
                ['verd_coil', '藤蟒泽', '藤蟒盘在枯枝上，不动时就是一根藤。', ['verd_treant']],
                ['verd_grove', '堕落德鲁伊林', '德鲁伊披着苔衣，念着让人脚底生根的咒。', ['verd_serpent']],
                ['verd_antler', '圣鹿空地', '银角鹿王立于月下，角上结着微光。', ['verd_stag']],
                ['verd_beetle', '甲虫丘', '巨角甲虫拱起土丘，壳硬得能弹开箭。', ['verd_druid']],
                ['verd_canopy', '林冠层', '脚下的路是交错的枝，掉下去就是古藤树人的臂弯。', ['verd_beetle']],
                ['verd_thicket', '密藤丛', '藤蔓织成一片密不透风的墙，幼体和林灵在暗处窥视。', ['verd_sprout', 'verd_wisp']],
                ['verd_crown', '古藤冠顶', '最高的树顶托着一顶藤冠，空心巨木的真身在此盘踞。', ['verd_hollow']],
            ],
        ],
        // ================= 3. 霜魄墓园（冰/暗） 380~440 =================
        'crypt' => [
            'name' => '霜魄墓园',
            'hub' => 'frostburg_gate',
            'mobs' => [
                ['crypt_wraith', '霜嚎怨灵', 380, 'ice', '霜嚎镰'],
                ['crypt_guard', '墓园守卫', 390, 'ice', '墓碑盾'],
                ['crypt_ghoul', '冻尸', 400, 'dark', '冻尸裹衣'],
                ['crypt_banshee', '霜啼女妖', 405, 'ice', '霜啼冠'],
                ['crypt_reaper', '掠魂者', 410, 'dark', '掠魂腿甲'],
                ['crypt_hound', '寒墓猎犬', 415, 'ice', '寒墓束带'],
                ['crypt_necro', '招魂师', 420, 'dark', '招魂指套'],
                ['crypt_lich', '巫妖侍从', 425, 'dark', '巫妖步履'],
            ],
            'bosses' => [
                ['crypt_lord', '霜墓领主', 430, 'ice', '领主棺披', '霜墓领主·永眠之翼', 'damage_reduction', 15.0],
                ['crypt_queen', '墓园女主人', 435, 'dark', '女主人戒指', '墓园女主人·亡语戒', 'skill_damage', 7.0],
                ['crypt_lichking', '巫妖王·丧钟', 440, 'ice', '丧钟项链', '巫妖王·丧钟之链', 'critdmg', 4.0],
            ],
            'maps' => [
                ['crypt_gate', '霜魄墓园·铁门', '铁门结满霜花，门内碑林望不到头。怨灵在碑缝里低嚎。', ['crypt_wraith']],
                ['crypt_path', '冻土墓道', '每一步都踩碎薄冰，守卫从墓碑后转出。', ['crypt_guard']],
                ['crypt_hall', '停尸长厅', '长厅两侧停满石棺，最近的一口盖子是掀开的。', ['crypt_ghoul']],
                ['crypt_lord_room', '停棺室', '霜墓领主躺在主棺里，棺盖被它自己顶开。', ['crypt_lord']],
                ['crypt_wail', '霜啼回廊', '女妖的哭声在回廊里绕，绕三圈就贴到你背后。', ['crypt_banshee']],
                ['crypt_reap', '掠魂阶', '台阶上刻满名字，踩上去会被抽走一口气。', ['crypt_reaper']],
                ['crypt_kennel', '寒墓犬舍', '猎犬的骨头冻成了冰雕，冰雕还在呼吸。', ['crypt_hound']],
                ['crypt_queen_tomb', '女主人的寝陵', '墓园女主人端坐在梳妆台前，镜里没有脸。', ['crypt_queen']],
                ['crypt_script', '招魂密室', '满墙招魂符文，招魂师正在把你写进死亡名单。', ['crypt_necro']],
                ['crypt_lich_room', '巫妖起居', '巫妖侍从整理着命匣，匣子里是别人的心跳。', ['crypt_lich']],
                ['crypt_ossuary', '髓骨厅', '骨头堆到房梁，冻尸从骨山里翻找自己的腿。', ['crypt_ghoul', 'crypt_wraith']],
                ['crypt_bell', '丧钟之巅', '巫妖王坐在钟楼上，钟一响，全墓园的坟都想开。', ['crypt_lichking']],
            ],
        ],
        // ================= 4. 星陨荒原（光/雷·星） 470~525 =================
        'star' => [
            'name' => '星陨荒原',
            'hub' => 'greenheart_gate',
            'mobs' => [
                ['star_shardling', '星屑精', 470, 'light', '星屑刃'],
                ['star_golem', '陨石魔像', 480, 'thunder', '陨铁盾'],
                ['star_wisp', '星辉游魂', 485, 'light', '星辉袍'],
                ['star_hound', '陨犬', 490, 'thunder', '陨铁头盔'],
                ['star_caster', '观星者', 495, 'light', '星象腿甲'],
                ['star_worm', '星环蠕虫', 500, 'thunder', '星环束带'],
                ['star_knight', '星陨骑士', 505, 'light', '陨星护手'],
                ['star_beast', '星蚀兽', 510, 'dark', '星蚀战靴'],
            ],
            'bosses' => [
                ['star_devourer', '吞噬星兽', 515, 'dark', '星兽吞披', '吞噬星兽·噬星之翼', 'skill_damage', 8.0],
                ['star_oracle', '星象先知', 520, 'light', '先知指环', '星象先知·预言之戒', 'all_attr', 35.0],
                ['star_titan', '星陨泰坦', 525, 'thunder', '泰坦星链', '星陨泰坦·雷霆星链', 'damage_reduction', 16.0],
            ],
            'maps' => [
                ['star_gate', '星陨荒原·坠星口', '巨大陨坑还在冒青烟，星屑精是坠落的星子磨出来的。', ['star_shardling']],
                ['star_crater', '陨坑', '坑壁嵌满烧黑的陨铁，陨石魔像一步一个坑。', ['star_golem']],
                ['star_glow', '星辉道', '路面浮着淡星河，游魂在光带上打转。', ['star_wisp']],
                ['star_maw', '陨心', '吞噬星兽盘在陨星心脏上，啃着还没凉透的星核。', ['star_devourer']],
                ['star_kennel', '陨犬穴', '陨犬不睡，它们守着坠落的碎片等你来拿。', ['star_hound']],
                ['star_obs', '观星台', '残破的观星台仍指向某颗不存在的星，观星者背对着你。', ['star_caster']],
                ['star_ring', '星环沼', '环形水泽映出另一片星空，蠕虫在倒影里游。', ['star_worm']],
                ['star_oracle_hall', '先知圣所', '圣所穹顶刻满预言，星象先知早已写好你的结局。', ['star_oracle']],
                ['star_knight_hall', '陨星骑士厅', '骑士们披着陨铁甲，面甲里是炽白的星火。', ['star_knight']],
                ['star_beastland', '星蚀兽原', '荒原被星蚀啃出黑洞，兽在洞边缘狩光。', ['star_beast']],
                ['star_field', '陨铁原', '满地陨铁碎块，魔像在拼自己，蠕虫在啃魔像。', ['star_golem', 'star_worm']],
                ['star_titan_seat', '泰坦星座', '星陨泰坦坐在坠落的星座上，一整座星座压着它的肩。', ['star_titan']],
            ],
        ],
        // ================= 5. 渊海熔潮（火/暗·熔海） 560~615 =================
        'magma' => [
            'name' => '渊海熔潮',
            'hub' => 'ember_foothill',
            'mobs' => [
                ['magma_imp', '熔焰小鬼', 560, 'fire', '熔焰短斧'],
                ['magma_hound', '熔岩猎犬', 570, 'fire', '熔岩臂盾'],
                ['mtide_golem', '熔潮魔像', 575, 'fire', '熔潮岩甲'],
                ['magma_worm', '岩浆蠕虫', 580, 'fire', '岩浆头盔'],
                ['magma_shade', '熔影', 585, 'dark', '熔影腿甲'],
                ['magma_priest', '拜火祭司', 590, 'fire', '拜火束带'],
                ['magma_drake', '熔岩亚龙', 595, 'fire', '熔岩鳞手'],
                ['magma_horror', '熔海恐魔', 600, 'dark', '熔海之靴'],
            ],
            'bosses' => [
                ['magma_behemoth', '熔海巨兽', 605, 'fire', '熔海兽披', '熔海巨兽·熔壳之背', 'damage_reduction', 15.0],
                ['magma_lord', '熔潮君主', 610, 'fire', '君主指环', '熔潮君主·涌心戒', 'skill_damage', 8.0],
                ['magma_leviathan', '熔渊利维坦', 615, 'dark', '熔渊颈链', '熔渊利维坦·深渊之链', 'lifesteal', 2.5],
            ],
            'maps' => [
                ['magma_shore', '渊海熔潮·熔岸', '黑石海岸，浪是滚烫的火。熔焰小鬼在浪里打滚。', ['magma_imp']],
                ['magma_field', '熔岩野', '龟裂的地面下是流动的火，猎犬踩着裂缝追你。', ['magma_hound']],
                ['magma_shell', '石胄道', '熔潮魔像一层层裹着冷却的岩壳，像穿着铠甲的山。', ['mtide_golem']],
                ['magma_behemoth_nest', '熔兽巢', '熔海巨兽卧在火湖中央，背上是一整片冷却的怪石。', ['magma_behemoth']],
                ['magma_burrow', '岩浆洞', '洞里滴着岩浆，蠕虫从头顶的钟乳上一节节落下来。', ['magma_worm']],
                ['magma_shadow', '熔影径', '火光挡不住的地方，熔影贴着地面滑行。', ['magma_shade']],
                ['magma_fane', '拜火祭坛', '祭司把活人献进熔口，说火要吃新鲜的名字。', ['magma_priest']],
                ['magma_lord_hall', '熔潮君王殿', '熔潮君主坐在凝固的火瀑下，呼吸一次地就烫一分。', ['magma_lord']],
                ['magma_drake_ledge', '亚龙崖', '熔岩亚龙盘在高温崖壁，鳞片滴着亮红的火。', ['magma_drake']],
                ['magma_abyss', '熔海深窟', '越往下火越冷，冷到极致的火成了黑，恐魔住在黑里。', ['magma_horror']],
                ['magma_tide', '涌潮口', '熔潮在这里翻涌成墙，魔像和熔影随浪冲上岸。', ['mtide_golem', 'magma_shade']],
                ['magma_leviathan_den', '熔渊利维坦之巢', '海底的鲸骨熔成了黑曜石，利维坦在骨缝间呼吸火。', ['magma_leviathan']],
            ],
        ],
        // ================= 6. 时空回廊（奥术/雷·时空） 660~725 =================
        'chrono' => [
            'name' => '时空回廊',
            'hub' => 'god_gate',
            'mobs' => [
                ['chrono_echo', '时序残响', 660, 'thunder', '时序刃'],
                ['chrono_sentinel', '回廊守卫', 675, 'thunder', '时光盾'],
                ['chrono_wisp', '奥术游灵', 680, 'light', '奥术法袍'],
                ['chrono_hound', '裂隙猎犬', 685, 'thunder', '裂隙头盔'],
                ['chrono_weaver', '时空织者', 695, 'thunder', '织时腿甲'],
                ['chrono_eater', '蚀时者', 700, 'dark', '蚀时束带'],
                ['chrono_knight', '永恒骑士', 705, 'light', '永恒护手'],
                ['chrono_shade', '昨日之影', 710, 'dark', '昨日行靴'],
            ],
            'bosses' => [
                ['chrono_warden', '时之守望者', 715, 'thunder', '守望披风', '时之守望者·永劫之翼', 'skill_damage', 9.0],
                ['chrono_tyrant', '时空暴君', 720, 'thunder', '暴君时戒', '时空暴君·万劫之戒', 'critdmg', 5.0],
                ['chrono_paradox', '悖论之核', 725, 'light', '悖论颈环', '悖论之核·永恒之链', 'all_attr', 40.0],
            ],
            'maps' => [
                ['chrono_gate', '时空回廊·裂隙门', '门框里流的不是景，是时间。残响在门口一遍遍走同一步。', ['chrono_echo']],
                ['chrono_watch', '回廊守望', '守卫把每一秒都数过，数错一次就把你拨回原点。', ['chrono_sentinel']],
                ['chrono_arcane', '奥术游廊', '光带缠成楼梯，游灵在符文里穿针引线。', ['chrono_wisp']],
                ['chrono_warden_core', '守望者之枢', '时之守望者坐在停滞的钟心，指针冻在你到来的那一刻。', ['chrono_warden']],
                ['chrono_rift', '裂隙猎场', '裂隙把东西撕成两半，猎犬只吃被撕开的那半。', ['chrono_hound']],
                ['chrono_loom', '织时纺厅', '织者把命运抽成线，纺成你从没见过的一生。', ['chrono_weaver']],
                ['chrono_bite', '蚀时岔道', '蚀时者啃掉了这条路的五分钟，你只能从头再来。', ['chrono_eater']],
                ['chrono_tyrant_throne', '暴君时座', '时空暴君坐着所有被它吞掉的“现在”。', ['chrono_tyrant']],
                ['chrono_knight_hall', '永恒骑士廊', '骑士永远在冲锋，只是冲锋的方向一次都没变过。', ['chrono_knight']],
                ['chrono_yester', '昨日回廊', '昨日的影贴着墙走，你一回头，它就成了今天。', ['chrono_shade']],
                ['chrono_loop', '无尽循环', '这条路你走过无数遍，残响和守卫也走了无数遍。', ['chrono_echo', 'chrono_sentinel']],
                ['chrono_paradox_core', '悖论之核', '核里同时有你的生与死，悖论睁眼时两者都成真。', ['chrono_paradox']],
            ],
        ],
        // ================= 7. 天外神墟（光/暗·圣） 900~980 =================
        'divine' => [
            'name' => '天外神墟',
            'hub' => 'gf_gate',
            'mobs' => [
                ['divine_seraph', '伪天使', 900, 'light', '圣裁长枪'],
                ['divine_acolyte', '神墟侍从', 915, 'light', '圣辉盾'],
                ['divine_wraith', '神罚怨灵', 925, 'light', '圣罚袍'],
                ['divine_golem', '圣像魔像', 935, 'thunder', '圣像头盔'],
                ['divine_hound', '神犬', 945, 'light', '神犬腿甲'],
                ['divine_priest', '堕落司祭', 955, 'dark', '堕落束带'],
                ['divine_knight', '圣殿骑士', 960, 'light', '圣殿护手'],
                ['divine_shade', '神墟暗影', 965, 'dark', '神墟步履'],
            ],
            'bosses' => [
                ['divine_archon', '神墟执政官', 970, 'light', '执政披风', '神墟执政官·圣翼', 'skill_damage', 10.0],
                ['divine_judge', '天外审判者', 975, 'light', '审判指环', '天外审判者·裁决之戒', 'critdmg', 6.0],
                ['divine_ruin', '神墟终焉', 980, 'dark', '终焉颈链', '神墟终焉·归墟之链', 'all_attr', 50.0],
            ],
            'maps' => [
                ['divine_gate', '天外神墟·南天门', '云海上悬着半座神殿，天使的羽毛落在石阶上还发着光。', ['divine_seraph']],
                ['divine_court', '神墟前庭', '前庭跪满石像，侍从在石像之间无声穿行。', ['divine_acolyte']],
                ['divine_wail', '神罚回廊', '怨灵是被神罚钉住的信徒，至今还在喊冤。', ['divine_wraith']],
                ['divine_archon_hall', '执政官之殿', '执政官端坐云端，翅膀遮住了半片天。', ['divine_archon']],
                ['divine_idol', '圣像广场', '广场中央的圣像缺了脸，魔像替它守着。', ['divine_golem']],
                ['divine_kennel', '神犬苑', '神犬守着神殿骨门，你不进，它不动；你进，它先动。', ['divine_hound']],
                ['divine_apse', '堕落司祭堂', '司祭背弃了神，神却还没背弃他——所以他还在这里。', ['divine_priest']],
                ['divine_judge_seat', '天外审判庭', '审判者的天平一端是你，一端是一整座下界。', ['divine_judge']],
                ['divine_knighthall', '圣殿骑士廊', '骑士的甲胄亮得刺眼，甲里却早已没有活人。', ['divine_knight']],
                ['divine_shadow', '神墟暗影道', '神光照不到的地方，暗影替神收债。', ['divine_shade']],
                ['divine_ruinfield', '神墟废墟', '神殿塌了半边，侍从和骑士守着一地神骸。', ['divine_acolyte', 'divine_knight']],
                ['divine_end', '神墟终焉·归墟', '神殿尽头是一个正在吞自己的空洞，终焉悬在洞里。', ['divine_ruin']],
            ],
        ],
    ];
}

// 由 spec 生成所有派生结构（记忆化）
function we2_build(): array
{
    static $b = null;
    if ($b !== null) {
        return $b;
    }
    $mobSlots = ['weapon', 'offhand', 'body', 'head', 'legs', 'belt', 'gloves', 'shoes'];
    $bossSlots = ['back', 'ring', 'necklace'];
    $loc = []; $mon = []; $lv = []; $el = []; $bossMap = []; $bosses = [];
    $drops = []; $skills = []; $regions = []; $links = []; $baseIds = [];

    foreach (we2_specs() as $rkey => $R) {
        $maps = $R['maps'];
        // 每个区域最前面加一个「根据地/基地」：安全区，带常用NPC，是进图前的落脚点与撤退后路
        $baseId = $rkey . '_camp';
        array_unshift($maps, [$baseId, $R['name'] . '·根据地', '你的根据地：篝火、铁砧、补给一应俱全，是深入「' . $R['name'] . '」前最后的落脚点，也是撤下来的后路。', []]);
        $baseIds[] = $baseId;
        $n = count($maps);
        $ids = [];
        foreach ($maps as $M) {
            $ids[] = $M[0];
        }
        foreach ($maps as $i => $M) {
            [$id, $nm, $desc, $mobs] = $M;
            $ex = [];
            if ($i > 0) {
                $ex[$ids[$i - 1]] = $maps[$i - 1][1];
            }
            if ($i < $n - 1) {
                $ex[$ids[$i + 1]] = $maps[$i + 1][1];
            }
            $loc[$id] = ['name' => $nm, 'desc' => $desc, 'exits' => $ex, 'monsters' => $mobs];
        }
        $links[$ids[0]] = $R['hub'];
        $regions[$R['name']] = $ids;

        foreach ($R['mobs'] as $idx => $mob) {
            [$id, $nm, $mlv, $mel, $base] = $mob;
            $slot = $mobSlots[$idx] ?? 'body';
            $mon[$id] = ['name' => $nm, 'hp' => (int) round($mlv * 175), 'atk' => (int) round($mlv * 2.6), 'exp' => (int) ($mlv * $mlv * 2), 'gold' => $mlv * 60];
            $lv[$id] = $mlv;
            $el[$id] = $mel;
            $drops[$id] = ['slot' => $slot, 'base' => $base, 'rate' => 0.10, 'q' => [0, 20, 50, 30, 0]];
            $en = element_name($mel);
            $skills[$id] = ['atk' => $en . '攻击', 'skills' => [['name' => $en . '冲击', 'rate' => 22, 'mult' => 1.6, 'el' => $mel]]];
        }
        foreach ($R['bosses'] as $idx => $bs) {
            [$id, $nm, $blv, $bel, $base, $legBase, $legKey, $legVal] = $bs;
            $slot = $bossSlots[$idx] ?? 'ring';
            $mon[$id] = ['name' => $nm, 'hp' => (int) round($blv * 175 * 2.8), 'atk' => (int) round($blv * 2.6 * 1.6), 'exp' => (int) ($blv * $blv * 3), 'gold' => $blv * 120];
            $lv[$id] = $blv;
            $el[$id] = $bel;
            $bosses[] = $id;
            $drops[$id] = ['slot' => $slot, 'base' => $base, 'rate' => 0.40, 'q' => [0, 0, 10, 40, 50], 'legendary' => ['base' => $legBase, 'extra' => ['k' => $legKey, 'v' => $legVal]]];
            $en = element_name($bel);
            $skills[$id] = ['atk' => $en . '轰击', 'skills' => [
                ['name' => $en . '爆发', 'rate' => 30, 'mult' => 2.6, 'el' => $bel],
                ['name' => '重压碾击', 'rate' => 16, 'mult' => 1.8, 'dot' => [3, 3]],
                ['name' => '狂暴突进', 'rate' => 14, 'mult' => 1.7, 'rush' => true],
            ]];
        }
    }
    foreach ($loc as $id => $L) {
        $ms = $L['monsters'];
        if (count($ms) === 1 && in_array($ms[0], $bosses, true)) {
            $bossMap[$id] = $ms[0];
        }
    }
    return $b = [
        'loc' => $loc, 'mon' => $mon, 'lv' => $lv, 'el' => $el, 'bossMap' => $bossMap,
        'bosses' => $bosses, 'drops' => $drops, 'skills' => $skills, 'regions' => $regions, 'links' => $links,
        'bases' => $baseIds,
    ];
}

function we2_locations(): array { return we2_build()['loc']; }
function we2_base_ids(): array { return we2_build()['bases']; }
function we2_monsters(): array { return we2_build()['mon']; }
function we2_lv(): array { return we2_build()['lv']; }
function we2_element(): array { return we2_build()['el']; }
function we2_boss_map(): array { return we2_build()['bossMap']; }
function we2_bosses(): array { return we2_build()['bosses']; }
function we2_drops(): array { return we2_build()['drops']; }
function we2_skills(): array { return we2_build()['skills']; }
function we2_regions(): array { return we2_build()['regions']; }
function we2_links(): array { return we2_build()['links']; }
