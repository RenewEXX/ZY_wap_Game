<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$who = (string) ($_GET['who'] ?? '');
$choice = (string) ($_GET['choice'] ?? '');
$cur = (string) $u['loc'];
$people = [
    'gallu' => ['name' => '神使·伽楼', 'loc' => 'god_gate', 'text' => '我守这扇门七百年，从没见过有人真的走完神界。神座会告诉你真相，堕天阶会告诉你代价。'],
    'godmerchant' => ['name' => '神界商人·白羽', 'loc' => 'god_hall', 'text' => '神界也收金币，收得更狠。战力不够就别往天柱去，那里的执政官看谁都像叛徒。'],
    'yzkeeper' => ['name' => '渊门引路人·缄默', 'loc' => 'god_hall', 'text' => '源罪渊每月只开一次。里面没有小怪，只有一个东西——你自己犯过的所有罪，捏成了一个人形。700级以上。它不问你准备好了没有。'],
    'rspriest' => ['name' => '转生祭司·源罪', 'loc' => 'god_hall', 'text' => '凡人的巅峰是999级。再往上，只有把自己献祭给源罪——神魂满级、斩世界BOSS，你来找我。'],
    'fallenguide' => ['name' => '堕天引路·堕羽', 'loc' => 'god_throne', 'text' => '想见神帝？先把神座打碎。下面是纯粹的坠落——45分钟，活着回来，或者留在星星之间。'],
    'mirrorelder' => ['name' => '镜厅长老·镜胜雪', 'loc' => 'god_mirror', 'text' => '每面镜子都是一条判词，满厅都是。想照镜子？先把剑磨快，镜子认得血，不认得人。'],
    'celia' => ['name' => '堕天传令·赛丽亚', 'loc' => 'gf_stair', 'text' => '我替神帝跑腿，也替坠落的人引路。你要是杀上来，记得先杀传令官，再杀我，最后杀他。规矩不能乱。'],    'gykeeper' => ['name' => '罪渊守门人·渡罪', 'loc' => 'square', 'text' => '这口渊每月只开一次，罪值越高的人进去越安全。里面的怪物只有1滴血——它们叫善意、爱意、友谊，诸如此类。杀了它们，你的罪就涨了。别问我为什么。'],
    'dane' => ['name' => '丹恩·铜须', 'loc' => 'smith', 'text' => '新来的？卡尔说你要下矿。空手去就是送死。我这辈子打过三千把武器，强化找我，炉子没灭过。下级强化石矿里到处是，中级要打精英，上级只在深处。'],
    'martha' => ['name' => '杂货商·玛莎', 'loc' => 'supply', 'text' => '火把、干粮、回城卷轴？下矿可不能没有火把。还有，捡到的破烂别扔，石头比金子值钱。'],
    'aileen' => ['name' => '药剂师·艾琳', 'loc' => 'market', 'text' => '我哥哥艾登·灰叶在调查队里。他没回来。拿着解毒剂。如果找到他的铭牌，请带回来。'],
    'carl' => ['name' => '守卫队长·卡尔', 'loc' => 'wall', 'text' => '先让我看看你的斤两。去旧水道杀十只腐化老鼠。活着回来，我教你战斗技巧；死了，我帮你收尸。'],
    'jack' => ['name' => '酒馆老板·老杰克', 'loc' => 'tavern', 'text' => '第一杯免费。听我一句，别信镇长。矿坑出事那天，他给调查队发了双倍补给，像是早知道他们回不来。'],
    'lily' => ['name' => '神秘少女·莉莉', 'loc' => 'gate', 'text' => '第三层如果有人叫你的名字，不要回答。不管那声音像谁。那东西只会模仿它吃过的人。'],
    'augustus' => ['name' => '镇长·奥古斯都', 'loc' => 'mansion', 'text' => '三年前矿坑挖穿了一堵墙。墙后不是岩石，是会流动的黑色东西。王国调查队十二人进去，只回来一个疯掉的托马斯。找到他们的记录，确认矿坑到底发生了什么。'],
    'thomas' => ['name' => '牧师·托马斯', 'loc' => 'church', 'text' => '眼睛……到处都是眼睛……雷恩叫我们进去，他说里面很温暖。别去第三层！那东西在养我们，就像我们养牲口一样！'],
    'alice' => ['name' => '修女·爱丽丝', 'loc' => 'town_sq', 'text' => '愿光照着你，冒险者。镇西的黑暗沼泽最近异动不断，10到30级、带上入场券（刷深渊回响·雷恩有1%掉）我就送你进去。记住：里面只有30分钟，死了或者杀了沼泽之王都会被传回来。'],
    'sentry' => ['name' => '守卫·布雷', 'loc' => 'silver_gate', 'text' => '雷恩……深渊回响那个雷恩？你杀的？进去吧，格温会长在公会大厅等你。天黑后别在街上走，尤其别靠近下水道——因为你的影子会自己动。'],
    'gwen' => ['name' => '会长·格温', 'loc' => 'sguild', 'text' => '三个人失踪，尸体找到了，影子不见了。有人故意让裂隙扩大。去下水道清理，杀够数再回来。'],
    'reed' => ['name' => '守卫·雷德', 'loc' => 'dsewer1', 'text' => '我是雷德……第三小队的……只有我跑出来了……杀了我……快……它要出来了……'],
    'lily2' => ['name' => '少女·莉莉', 'loc' => 'dsewer2', 'text' => '那本笔记是我写的。我是那个声音。我在白石镇等了你三个月。把笔记交给格温，还是烧了，装作不知道？你选。'],
    'apostle_echo' => ['name' => '使徒残响', 'loc' => 'bmine2', 'text' => '主人等你很久了。她不是你的敌人，也不是朋友。她只是一只眼睛。你的影子比来时长了三寸，你逃不掉的。'],
    'golem_echo' => ['name' => '守护者残响', 'loc' => 'sforest', 'text' => '我是守护者，守着莉莉的秘密。你知道，但你不信。你心里还有一丝希望，对吧？我可以让你看到真相，但你要付出代价。'],
    'lord' => ['name' => '城主·瓦伦丁', 'loc' => 'manor', 'text' => '你来了，我等你很久了。你的影子已经长到可以用了。加入我，我给你力量、财富、一切。'],
    'darklily' => ['name' => '暗影莉莉', 'loc' => 'baltar', 'text' => '我是她不敢面对的那一部分。她不敢面对自己是一只眼睛，不敢面对喜欢你。杀了我，她变人类；理解我，她变眼睛。你决定。'],
    'vera' => ['name' => '守渊人·薇拉', 'loc' => 'silver_sq', 'text' => '白银城地下还有一口更深的渊，叫影蚀深渊。50到120级、带上入场券（杀深渊之眼有1%掉）我就送你进去。记住：里面只有30分钟，死了或者杀了阿巴顿都会被传回这里。'],
    'gmaster' => ['name' => '公会管理员·霍尔', 'loc' => 'sguild', 'text' => '想建公会？10级、1金。想打架？会长可以宣战，24小时刷怪比战功。'],
    'greg' => ['name' => '公会接待·格雷', 'loc' => 'guild', 'text' => '白石镇公会接待处。建公会、加公会、看排行、宣战，都在这办。'],
    'horse_t' => ['name' => '赛马人·老霍', 'loc' => 'town_sq', 'text' => '赌马了啊！10匹马，2小时一场，1到10魔钻，冠军分奖池六成！'],
    'horse_s' => ['name' => '赛马人·阿金', 'loc' => 'silver_sq', 'text' => '白银城分场，奖池全服通用。押马要趁早，开赛不候。'],
    'horse_g' => ['name' => '赛马人·豆芽', 'loc' => 'square', 'text' => '灰雾村也有马！小注怡情，大注发家。'],
    'rank_t' => ['name' => '榜单老人', 'loc' => 'town_sq', 'text' => '战力、活跃、消费、宠物、赛马，五榜每刻更新。扬名立万，就在此处。'],
    'rank_s' => ['name' => '榜单老人', 'loc' => 'silver_sq', 'text' => '白银城也看榜。数据全服通用。'],
    'rank_g' => ['name' => '榜单老人', 'loc' => 'square', 'text' => '灰雾村小地方，榜可是全服的。'],
    'waldon' => ['name' => '线人·瓦尔顿', 'loc' => 'avenue', 'text' => '真正的王都二十年前就没了，这里只是深渊搭的戏台。拿着格温的信，就去中央大道杀傀儡守卫，杀够80只再回来。'],
    'guard_captain' => ['name' => '守卫队长', 'loc' => 'noble', 'text' => '银丝已经缠到我脖子了……杀了我，或者切断银丝，或者……走开，别看。'],
    'stringer' => ['name' => '牵线者', 'loc' => 'theater', 'text' => '我不是深渊，我是国王。二十年前，国王为了永生献出灵魂，灵魂变成了我。你是谁？你来杀我，还是来理解我？'],
    'spider' => ['name' => '断线人·阿蛛', 'loc' => 'theater', 'text' => '剧场下面还有个母巢，是牵线者死后留下的卵。150级以上、带着母巢入场券（杀牵线者1%掉），我送你进去。45分钟，出来或者变成茧。'],
    'grocer' => ['name' => '菜商·豆豆', 'loc' => 'farm', 'text' => '农场币换好东西！粮食、洗点药、升级卡，每周限量，先到先得。'],
    'rank_c' => ['name' => '榜单老人', 'loc' => 'avenue', 'text' => '王都也看榜。数据全服通用。'],
    'med_g' => ['name' => '雾语药剂师·菘蓝', 'loc' => 'square', 'text' => '雾气浸透绷带，药效反而更好。按最大生命百分比回，战斗中手动喝。记住：药救得活人，救不活死人。'],
    'eventer' => ['name' => '活动专员·阿战', 'loc' => 'square', 'text' => '巅峰之战，每晚22:30~23:00！三个战场按等级分流，活到最后的人拿真龙丹（+3自由属性点）！死了、跑了、传走了，都别想再进来！'],
    'arena_m' => ['name' => '竞技场大师·雷横', 'loc' => 'square', 'text' => '想证明自己吗？竞技场欢迎你。这里不拼手速，只拼实力。你的队伍、宠物、装备，全部自动战斗。赢了排名上升，输了排名下降。每天结算一次，排名越高，奖励越好。'],
    'med_t' => ['name' => '草药学徒·薄荷', 'loc' => 'market', 'text' => '我是艾琳的学徒，老师忙着找她哥哥，药摊我看着。绷带10%到圣水50%，按血量回，血越多越划算。'],
    'med_s' => ['name' => '银月药剂师·霜叶', 'loc' => 'silver_sq', 'text' => '白银城的影子会动，血可不能空。备瓶月影圣水吧，关键时刻能救命。'],
    'rb_waldon' => ['name' => '总会长·瓦尔顿', 'loc' => 'avenue', 'text' => '你的力量到瓶颈了。再往上，要选自己的路。四条路，四种命运。去体验它们，再回来选。'],
    'rb_iron' => ['name' => '铁拳', 'loc' => 'arena_gate', 'text' => '战士的路，是承受。不是杀死敌人，是活下来。和我的学徒打一场，撑十回合不死，就算过。不许用技能，只能砍。'],
    'rb_mori' => ['name' => '莫里亚蒂院长', 'loc' => 'library', 'text' => '法师的路，是理解。力量来自知识，不是蛮力。答对我三道题。答错一次1银，交了再答。'],
    'rb_hawk' => ['name' => '老猎人·霍克', 'loc' => 'capital_gate', 'text' => '猎手的路，是耐心。追那头白鹿，别惊动它。树冠快但险，溪谷稳但慢，草丛要等。你选。'],
    'rb_samuel' => ['name' => '大主教·塞缪尔', 'loc' => 'cathedral', 'text' => '牧师的路，是取舍。你救不了所有人。十份药，十个病人，怎么分，你定。救满六人，算过。'],
    'rb_veil' => ['name' => '深渊引渡人·帷幕', 'loc' => 'slum', 'text' => '召唤师的路，是代价。深渊不收信仰，只收血肉。献祭你当前两成生命，我就替你引渡第一只小鬼。'],
    'rockwarden' => ['name' => '岩狱守门人·磐石', 'loc' => 'frostburg_sq', 'text' => '霜堡广场风大。磐石抱着块石头：岩狱开了，700~900级，泰坦在里面等。30万门票，45分钟。'],
    'voidwalker' => ['name' => '虚空行者', 'loc' => 'volcano_heart', 'text' => '进去就别回头，回头的那面你已经死了。裂隙底下有群攻秘籍，暴君那有大的。'],
    'med_c' => ['name' => '王都药剂师·藏红', 'loc' => 'avenue', 'text' => '傀儡不流血，你流。银丝勒颈之前，先把血回满。'],
    'priest_c' => ['name' => '传教神父·奥古', 'loc' => 'silver_sq', 'text' => '孩子，每周在这世上好好活够2小时（有效游玩），圣光就记你一功，赏你10魔钻。每周一刷新，可领一次。'],
    'northwind' => ['name' => '守夜人·北风', 'loc' => 'capital_gate', 'text' => '北门外就是冰原。凛冬之王醒了，霜狼下山了。你要是活得不耐烦，我送你一程——去风雪小径杀120只霜狼，算投名状。'],
    'dora' => ['name' => '猎户·朵拉', 'loc' => 'ice_village', 'text' => '湖上有怨灵，场上有猛犸，隘口有魔像收买路钱。认路再走，肉干管饱，命自己管。'],
    'oldfish' => ['name' => '潜水人·老咸鱼', 'loc' => 'ice_village', 'text' => '冰渊底下有大家伙，利维坦。我这绳子45分钟，入场券拿来，人送到。'],
    'qingya' => ['name' => '森语者·青芽', 'loc' => 'fo_gate', 'text' => '林子认得路，人不认。雾森往南是火山，火山里住着一位老邻居——别吵它，吵醒了，柱子会倒。'],
    'oldash' => ['name' => '探火人·老灰', 'loc' => 'ember_foothill', 'text' => '熔心底下有化身，见过的人都说心跳会跟它走。我这绳子也是45分钟，入场券拿来。'],
    'watcher' => ['name' => '熔火守望者', 'loc' => 'volcano_heart', 'text' => '嘘——柱子后面就是南炎柱，烬焰领主·火龙多伊尔在睡觉。你只是来看看，对吧？看一眼，留下名字，赶紧走。想磨它？每天它醒一次，全服一起上。'],
    'soulmaster' => ['name' => '神魂师·墨老', 'loc' => 'silver_sq', 'text' => '神魂碎片十个一合，天地人三级看命。神魂无等级限制，戴上就加真伤。我这能升级，I级起，X级顶，越往上越吃碎片。'],
    'bounty_boss' => ['name' => '榜爷', 'loc' => 'bounty_hall', 'text' => '每天三单，杀够数领钱领药。单子天天有，命只有一条。'],    'bounty_elder' => ['name' => '树皮榜', 'loc' => 'elder_tree', 'text' => '悬赏单贴在树皮上，风一吹哗哗响。杀够数，找榜爷领——树皮只管贴，不管发。'],
    'bounty_frost' => ['name' => '榜爷学徒', 'loc' => 'frostburg_sq', 'text' => '师父在悬赏大厅，我在广场摆摊。单子一样，钱一样，杀够数就行。'],
];
if (!isset($people[$who]) || $people[$who]['loc'] !== $cur) {
    flash_set('这个人不在这里。');
    header('Location: home.php');
    exit;
}

if ($who === 'gykeeper' && $choice === 'enter') {
    if ((int) ($u['lv'] ?? 1) < 1) {
        flash_set('你的等级不够。');
    } else {
        $curMonth = date('Y-m');
        $mats = mats_of((int) $u['id']);
        if ((string) ($mats['gy_month'] ?? '') === $curMonth) {
            flash_set('渡罪摇头：这个月你已经进去过了。下个月再来。');
        } else {
            mat_set((int) $u['id'], 'gy_month', $curMonth);
            mat_set((int) $u['id'], 'gy_enter', time());
            $u['loc'] = 'gy_gate';
            user_save($u);
            flash_set('渡罪推开罪渊之门，30分钟倒计时开始。杀光它们——或者被它们看着。');
        }
    }
    header('Location: home.php');
    exit;
}
if ($who === 'gallu' && $choice === 'start') {
    if ((int) ($u['lv'] ?? 1) < 700 || (int) ($u['quest'] ?? 0) < 81) {
        flash_set('伽楼看了你一眼：还不够。把你的名字磨到700级再来。');
    } else {
        $u['quest'] = 100;
        user_save($u);
        quest_god_baseline((int) $u['id'], 100);
        flash_set('伽楼侧身让路：天门开了。去杀六翼天使——它们会让路，或者不。');
    }
    header('Location: home.php');
    exit;
}
if ($who === 'fallenguide' && $choice === 'enter') {
    $free = !empty(cflags((int) $u['id'])['gf_free']);
    $mats = mats_of((int) $u['id']);
    if (!$free && empty($mats['gf_ticket'])) {
        flash_set('堕羽：没有【堕天阶入场券】进不去。杀神座化身拿一张，或者把主线走到神座碎（112环后免票）。');
    } else {
        if (!$free) {
            add_mat((int) $u['id'], 'gf_ticket', -1);
        }
        mat_set((int) $u['id'], 'gf_enter', time());
        $u['loc'] = 'gf_gate';
        user_save($u);
        flash_set($free ? '堕羽低头：归路已赦免。45分钟，往下走。' : '堕羽吹灭了灯：45分钟，往下走就是坠落。');
    }
    header('Location: home.php');
    exit;
}
if ($who === 'godmerchant' && $choice === 'heal') {
    if ((int) $u['gold'] < 50000) {
        flash_set('白羽摇头：50000铜，一个子儿不能少。');
    } else {
        $u['gold'] = (int) $u['gold'] - 50000;
        $u['hp'] = (int) $u['maxhp'];
        $u['mp'] = (int) ($u['maxmp'] ?? 0);
        user_save($u);
        flash_set('白羽弹了个响指，你的伤全好了。');
    }
    header('Location: npc.php?who=godmerchant');
    exit;
}
if ($who === 'rspriest' && $choice === 'rite') {
    if (!rs_can_rebirth((int) $u['id'])) {
        flash_set('源罪祭司摇头：源罪未满——' . rs_require_text((int) $u['id']));
    } else {
        flash_set('源罪祭司的声音像从井底传来：「源罪已满。现在，你只需要做一个选择。转生之后，你会失去等级，但会得到超越等级的力量。你确定吗？」<a href="npc.php?who=rspriest&choice=confirm">【确认转生】</a> / <a href="home.php">【再等等】</a>');
    }
    header('Location: npc.php?who=rspriest');
    exit;
}
if ($who === 'rspriest' && $choice === 'confirm') {
    $out = rs_perform((int) $u['id']);
    flash_set(h($out));
    header('Location: home.php');
    exit;
}
if ($who === 'yzkeeper' && $choice === 'enter') {
    if ((int) ($u['lv'] ?? 1) < 700) {
        flash_set('缄默：你太干净了。源罪渊只接待罪人，700级再来。');
    } else {
        $ym = date('Y-m');
        $ymats = mats_of((int) $u['id']);
        if ((string) ($ymats['yz_month'] ?? '') === $ym) {
            flash_set('缄默：这个月你已经见过它了。它记得你的脸。下个月再来。');
        } else {
            mat_set((int) $u['id'], 'yz_month', $ym);
            mat_set((int) $u['id'], 'yz_enter', time());
            $u['loc'] = 'yz_gate';
            user_save($u);
            flash_set('缄默侧身让路。门开了。30分钟。');
        }
    }
    header('Location: home.php');
    exit;
}
if ($choice === 'gift' && isset(favor_npcs()[$who])) {
    flash_set(favor_gift((int) $u['id'], $who, (string) ($_GET['mid'] ?? ''), 1));
    header('Location: npc.php?who=' . $who);
    exit;
}
if ($choice === 'ambush' && isset(ambush_npcs()[$who])) {
    if ((int) $u['hp'] <= 0) {
        flash_set('你先回满血再来。');
        header('Location: home.php');
        exit;
    }
    // 多轮战斗：开战即+10罪（无论胜负），去战斗页一刀一刀砍
    $cfg = ambush_npcs()[$who];
    $u2 = user_by_id((int) $u['id']);
    sin_gain_array($u2, 10);
    user_save($u2);
    $_SESSION['ambush'] = ['npc' => $who, 'hp' => (int) $cfg['hp'], 'maxhp' => (int) $cfg['hp']];
    flash_set('你摸向' . $cfg['name'] . '……罪值+10，先下手为强！');
    header('Location: fight.php?a=ambushfight');
    exit;
}
if ($who === 'augustus' && $choice === 'accept' && (int) $u['quest'] === 10) {
    $u['quest'] = 11;
    $u['chapter_flags'] = json_encode(['accepted' => true], JSON_UNESCAPED_UNICODE);
    user_save_flags($u);
    flash_set('奥古斯都交给你矿坑封锁门的钥匙。任务更新：腐化的前兆。');
    header('Location: home.php');
    exit;
}
if ($who === 'carl' && (int) $u['quest'] === 11) {
    echo ''; // 保持对话页面，不自动跳过考验
}
if ($who === 'lily' && (int) $u['quest'] === 13) {
    $u['chapter_flags'] = json_encode(['accepted' => true, 'lily_warned' => true], JSON_UNESCAPED_UNICODE);
    user_save_flags($u);
}
if ($who === 'alice' && $choice === 'open') {
    $lv = (int) ($u['lv'] ?? 1);
    $mats = mats_of((int) $u['id']);
    if ($lv < 10 || $lv > 30) {
        flash_set('爱丽丝摇头：黑暗沼泽只要10到30级的冒险者，你现在' . $lv . '级。');
    } elseif (empty($mats['dsw_ticket'])) {
        flash_set('爱丽丝：没有【黑暗沼泽副本入场券】进不去，去刷深渊回响·雷恩（1%掉）。');
    } else {
        add_mat((int) $u['id'], 'dsw_ticket', -1);
        foreach (['dsw_item1', 'dsw_item2', 'dsw_item3', 'dsw_item4'] as $it) {
            db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], $it]);
        }
        mat_set((int) $u['id'], 'dsw_stage', 1);
        mat_set((int) $u['id'], 'dsw_enter', time());
        $u['loc'] = 'dsw_gate';
        user_save($u);
        flash_set('爱丽丝为你祝福，送你进黑暗沼泽。30分钟倒计时开始！');
    }
    header('Location: home.php');
    exit;
}
function ch2_choice(array &$u, string $doneFlag, string $resultFlag, string $result, int $silver, string $msg): void
{
    cflag_set((int) $u['id'], $doneFlag, 1);
    cflag_set((int) $u['id'], $resultFlag, $result);
    if ($silver > 0) {
        $u['gold'] = (int) $u['gold'] + $silver;
    }
    user_save($u);
    flash_set($msg);
}

if ($who === 'reed' && in_array($choice, ['kill', 'save', 'leave'], true) && (int) $u['quest'] === 21) {
    if (empty(cflags((int) $u['id'])['reed_done'])) {
        if ($choice === 'save') {
            $u['hp'] = max(1, (int) ((int) $u['hp'] / 2));
            ch2_choice($u, 'reed_done', 'reed_result', 'save', 1000, '你按住雷德的影子，生命力从掌心流走（生命减半）。影子退回去了。格温后来对你说：你救了一个不该救的人。但也许，这才是对的。（+10银）');
        } elseif ($choice === 'kill') {
            ch2_choice($u, 'reed_done', 'reed_result', 'kill', 0, '你结束了雷德的痛苦。他的影子尖啸着散去。下水道里安静了。');
        } else {
            ch2_choice($u, 'reed_done', 'reed_result', 'leave', 0, '你转身离开。身后传来一声很长的叹息，然后什么都没了。');
        }
    }
    quest_stuck_fix($u);
    $u = user_by_id((int) $u['id']);
    $qs2 = ch2_quests()[21] + ['id' => 21];
    if (check_ch2_done((int) $u['id'], $qs2)) {
        $u['quest'] = 22;
        quest2_baseline((int) $u['id'], 22, true);
        user_save($u);
        flash_set(flash_get() . '【任务完成】进下一环：深处笔记·莉莉！');
    } else {
        flash_set(flash_get() . '【' . $qs2['name'] . '】' . quest_progress2_text((int) $u['id'], $qs2) . '（先杀够数再来）');
    }
    header('Location: npc.php?who=reed');
    exit;
}
function chx_advance(array $u, int $q, string $doneFlag): void
{
    $u = user_by_id((int) $u['id']);
    $all = $q >= 30 ? ch3_quests() : ch2_quests();
    $fn = $q >= 30 ? 'check_ch3_done' : 'check_ch2_done';
    $qs = ($all[$q] ?? null) + ['id' => $q];
    if (($all[$q] ?? null) && $fn((int) $u['id'], $qs)) {
        $u['quest'] = $q + 1;
        if ($q >= 30) {
            quest3_baseline((int) $u['id'], $q + 1);
        } else {
            quest2_baseline((int) $u['id'], $q + 1);
        }
        user_save($u);
        flash_set(flash_get() . '【任务完成】进下一环！');
    }
}

if ($who === 'lily2' && in_array($choice, ['give', 'burn', 'keep'], true) && (int) $u['quest'] === 22 && empty(cflags((int) $u['id'])['note_done'])) {
    if ($choice === 'give') {
        ch2_choice($u, 'note_done', 'note_result', 'give', 1000, '格温看完笔记沉默很久：我知道了。她开始调查城主。（+10银）');
    } elseif ($choice === 'burn') {
        ch2_choice($u, 'note_done', 'note_result', 'burn', 0, '火苗吞掉纸页。莉莉看着火光，什么都没说。城主会更加警惕。');
        cflag_set((int) $u['id'], 'lord_alert', 1);
    } else {
        ch2_choice($u, 'note_done', 'note_result', 'keep', 0, '你把笔记揣进怀里。莉莉笑了笑：也好，知道太多的人，影子都长得快。城主会更加警惕。');
        cflag_set((int) $u['id'], 'lord_alert', 1);
    }
    chx_advance($u, 22, 'note_done');
    header('Location: npc.php?who=lily2');
    exit;
}
if ($who === 'apostle_echo' && in_array($choice, ['ask', 'killfirst', 'free'], true) && (int) $u['quest'] === 23 && empty(cflags((int) $u['id'])['apostle_done'])) {
    if ($choice === 'ask') {
        ch2_choice($u, 'apostle_done', 'apostle_result', 'ask', 1000, '使徒说：莉莉是裂隙分化出的人性碎片，是眼睛，也是心。她在学做人。她帮过你，也害过你。她只是一面镜子。（+10银）');
    } elseif ($choice === 'killfirst') {
        ch2_choice($u, 'apostle_done', 'apostle_result', 'killfirst', 0, '你没有多问。法杖断裂的声音在矿道里回荡了很久。');
        cflag_set((int) $u['id'], 'lily_cold', 1);
    } else {
        ch2_choice($u, 'apostle_done', 'apostle_result', 'free', 0, '你放他去带话。他走远时说：她会听到的。她一直听着。');
        cflag_set((int) $u['id'], 'lily_cold', 1);
    }
    chx_advance($u, 23, 'apostle_done');
    header('Location: npc.php?who=apostle_echo');
    exit;
}
if ($who === 'golem_echo' && in_array($choice, ['see', 'refuse', 'what'], true) && (int) $u['quest'] === 24 && empty(cflags((int) $u['id'])['golem_done'])) {
    if ($choice === 'see') {
        ch2_choice($u, 'golem_done', 'golem_result', 'see', 1000, '傀儡的手按在你额头上。你看见枯井边的她，看见祭坛里莫尔甘身后的她，眼神是空的。（+10银）');
    } elseif ($choice === 'refuse') {
        ch2_choice($u, 'golem_done', 'golem_result', 'refuse', 0, '你拒绝直视。傀儡说：你已经知道了，你只是不想承认。');
        cflag_set((int) $u['id'], 'lily_cold', 1);
    } else {
        ch2_choice($u, 'golem_done', 'golem_result', 'what', 0, '傀儡没有回答，只是把真相按进你脑子里。你踉跄一步，耳朵里全是水声。');
        cflag_set((int) $u['id'], 'lily_cold', 1);
    }
    chx_advance($u, 24, 'golem_done');
    header('Location: npc.php?who=golem_echo');
    exit;
}
if ($who === 'lord' && in_array($choice, ['fake', 'fight', 'asklily'], true) && (int) $u['quest'] === 25 && empty(cflags((int) $u['id'])['lord_done'])) {
    if ($choice === 'asklily') {
        ch2_choice($u, 'lord_done', 'lord_result', 'ask', 1000, '城主笑：莉莉什么都不知道。她以为在学习人类，其实她只是一把钥匙，一把打开王都地下大门的钥匙。（+10银）');
    } elseif ($choice === 'fake') {
        ch2_choice($u, 'lord_done', 'lord_result', 'fake', 0, '你假意低头，袖中刀已出鞘。城主却先一步退进影子里：王都地下，万眼之夜。');
        cflag_set((int) $u['id'], 'lily_doom', 1);
    } else {
        ch2_choice($u, 'lord_done', 'lord_result', 'fight', 0, '你直接开战。他比想象的强，但终究逃了，只留一封信：王都地下，万眼之夜。');
        cflag_set((int) $u['id'], 'lily_doom', 1);
    }
    chx_advance($u, 25, 'lord_done');
    header('Location: npc.php?who=lord');
    exit;
}
if ($who === 'darklily' && in_array($choice, ['slay', 'talk', 'where'], true) && (int) $u['quest'] === 26 && empty(cflags((int) $u['id'])['lily_done'])) {
    if ($choice === 'where') {
        ch2_choice($u, 'lily_done', 'lily_result', 'where', 0, '她说：杀了我，她变人类；理解我，她变眼睛。你决定吧。你选择了前者。她笑着倒下，真正的莉莉哭着出现：我终于……可以哭了。（魔力上限+20）');
        $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 20;
        $u['mp'] = (int) ($u['mp'] ?? 0) + 20;
        user_save($u);
    } elseif ($choice === 'slay') {
        ch2_choice($u, 'lily_done', 'lily_result', 'slay', 0, '你直接动手。她没有反抗。真正的莉莉没有出现，祭坛里只剩风声。');
        cflag_set((int) $u['id'], 'lily_gone', 1);
    } else {
        ch2_choice($u, 'lily_done', 'lily_result', 'talk', 0, '你试图理解她。她听着听着，影子翅膀合拢，把你们一起裹进黑暗。等你醒来，祭坛空了。');
        add_mat((int) $u['id'], 'qingzui_dan', 1);
        cflag_set((int) $u['id'], 'lily_gone', 1);
    }
    chx_advance($u, 26, 'lily_done');
    header('Location: npc.php?who=darklily');
    exit;
}
if ($who === 'gwen' && $choice === 'start' && (int) $u['quest'] === 16) {
    if ((int) ($u['lv'] ?? 1) < 30) {
        flash_set('格温：你还不到30级，先去白石镇附近历练，白银城太危险。');
    } else {
        $u['quest'] = 20;
        user_save($u);
        quest2_baseline((int) $u['id'], 20);
        flash_set('格温：三个人失踪，影子不见了。第二章·影蚀之潮开启！去下水道清理影蚀鼠×10。');
    }
    header('Location: npc.php?who=gwen');
    exit;
}
if ($who === 'rb_waldon' && $choice === 'start' && (int) $u['quest'] === 38 && (int) ($u['lv'] ?? 1) >= 299) {
    $u['quest'] = 40;
    user_save($u);
    cflag_set((int) $u['id'], 'rb1_start', 1);
    flash_set('瓦尔顿：1觉·觉醒之路开启！去见四位导师，完成四重试炼再回来。');
    header('Location: npc.php?who=rb_waldon');
if ($who === 'rb_waldon' && $choice === 'start2' && (int) $u['quest'] === 76 && (int) ($u['lv'] ?? 1) >= 599) {
    $u['quest'] = 77;
    user_save($u);
    cflag_set((int) $u['id'], 'rb2_start', 1);
    flash_set('瓦尔顿：2觉·群攻之路开启！去火山之心完成虚空试炼再回来。');
    header('Location: npc.php?who=rb_waldon');
    exit;
}
if ($who === 'rb_waldon' && $choice === 'crown' && (int) $u['quest'] === 80 && !empty(cflags((int) $u['id'])['rb2_job'])) {
    if (empty(cflags((int) $u['id'])['rb2_done'])) {
        cflag_set((int) $u['id'], 'rb2_done', 1);
        $u['gold'] = (int) ($u['gold'] ?? 0) + 1000000;
        user_save($u);
        add_mat((int) $u['id'], 'reset_potion', 1);
        add_mat((int) $u['id'], 'soul_shard', 20);
        add_mat((int) $u['id'], 'rb2_proof', 1);
        $job = (string) (cflags((int) $u['id'])['rb2_job'] ?? '');
        send_mail((int) $u['id'], '总会长', 'rebirth', '2觉', rebirth2_name($job) . '加冕！群攻之路完成，得100万+洗点药+神魂碎片×20。去虚空裂隙/灰烬深渊刷你的群攻秘籍吧！', []);
        flash_set('加冕完成！2觉证明+100万+洗点药+碎片×20已发放，去刷群攻秘籍吧！');
        $u = user_by_id((int) $u['id']);
        $u['quest'] = 81;
        user_save($u);
    }
    header('Location: npc.php?who=rb_waldon');
    exit;
}
if ($who === 'rb_waldon' && (int) $u['quest'] === 79 && in_array($choice, ['berserker', 'aegis', 'wintermage', 'wildfire', 'ranger', 'assassin', 'shadowbishop', 'holybishop', 'necro', 'plague'], true) && empty(cflags((int) $u['id'])['rb2_job'])) {
    $base = ['berserker' => 'warrior', 'aegis' => 'warrior', 'wintermage' => 'mage', 'wildfire' => 'mage', 'ranger' => 'hunter', 'assassin' => 'hunter', 'shadowbishop' => 'priest', 'holybishop' => 'priest', 'necro' => 'summoner', 'plague' => 'summoner'][$choice];
    if ($base !== job_id_of($u)) {
        flash_set('这不是你的路。');
    } else {
        cflag_set((int) $u['id'], 'rb2_job', $choice);
        $sk = rebirth2_skill($choice);
        if ($sk !== '') {
            add_mat((int) $u['id'], 'book_' . $sk, 1);
        }
        flash_set('你选择了【' . rebirth2_name($choice) . '】！获得群攻秘籍，找瓦尔顿加冕！');
        $u['quest'] = 80;
        user_save($u);
    }
    header('Location: npc.php?who=rb_waldon');
    exit;
}
if ($who === 'voidwalker' && $choice === 'open') {
    $lv = (int) ($u['lv'] ?? 1);
    if ($lv < 600) {
        flash_set('虚空行者摇头：裂隙只要600级以上的冒险者，你现在' . $lv . '级。');
    } elseif ((int) ($u['gold'] ?? 0) < 300000) {
        flash_set('虚空行者：进裂隙要30万买命钱。');
    } else {
        $u['gold'] = (int) $u['gold'] - 300000;
        mat_set((int) $u['id'], 'vx_enter', time());
        $u['loc'] = 'vx_gate';
        user_save($u);
        flash_set('虚空行者把你推进裂隙！45分钟倒计时开始！');
    }
if ($who === 'spider' && $choice === 'open2') {    $lv = (int) ($u['lv'] ?? 1);    if ($lv < 300 || $lv > 460) {        flash_set('阿蛛摇头：傀儡回廊只要300到460级的冒险者，你现在' . $lv . '级。');    } elseif ((int) ($u['gold'] ?? 0) < 50000) {        flash_set('阿蛛：进回廊要5万门票（修补提线的钱）。');    } else {        $u['gold'] = (int) $u['gold'] - 50000;        mat_set((int) $u['id'], 'pz_stage', 1);        mat_set((int) $u['id'], 'pz_enter', time());        $u['loc'] = 'pz_gate';        user_save($u);        flash_set('阿蛛剪断你影子上的丝，送你进傀儡回廊！45分钟倒计时开始！');    }    header('Location: home.php');    exit;}if ($who === 'qingya' && $choice === 'open') {    $lv = (int) ($u['lv'] ?? 1);    if ($lv < 500 || $lv > 620) {        flash_set('青芽摇头：雾森祭坛只要500到620级的冒险者，你现在' . $lv . '级。');    } elseif ((int) ($u['gold'] ?? 0) < 200000) {        flash_set('青芽：进祭坛要20万供奉（雾要喝甜的）。');    } else {        $u['gold'] = (int) $u['gold'] - 200000;        mat_set((int) $u['id'], 'sen_stage', 1);        mat_set((int) $u['id'], 'sen_enter', time());        $u['loc'] = 'sen_gate';        user_save($u);        flash_set('青芽吹了声口哨，雾让开一条路：雾森祭坛，45分钟！');    }    header('Location: home.php');    exit;}if ($who === 'northwind' && $choice === 'start' && in_array((int) $u['quest'], [38, 46], true)) {
    $u['quest'] = 50;
    user_save($u);
    quest3_baseline((int) $u['id'], 50);
    flash_set('北风：第四章·凛冬冰原开启！去风雪小径杀冻原霜狼×120。挂机也能刷，等级到了再往下走。');
    header('Location: npc.php?who=northwind');
    exit;
}
if ($who === 'rockwarden' && $choice === 'open') {
    $lv = (int) ($u['lv'] ?? 1);
    if ($lv < 700 || $lv > 900) {
        flash_set('磐石摇头：岩狱只要700~900级的冒险者，你现在' . $lv . '级。');
    } elseif ((int) ($u['gold'] ?? 0) < 300000) {
        flash_set('磐石：进岩狱要30万门票（修门）。');
    } else {
        $u['gold'] = (int) $u['gold'] - 300000;
        mat_set((int) $u['id'], 'ry_stage', 1);
        mat_set((int) $u['id'], 'ry_enter', time());
        $u['loc'] = 'ry_gate';
        user_save($u);
        flash_set('磐石挪开巨石：岩狱·泰坦，45分钟！土龙岳柱在最深处。');
    }
    header('Location: home.php');
    exit;
}

if ($who === 'oldfish' && $choice === 'open') {
    $lv = (int) ($u['lv'] ?? 1);
    $mats = mats_of((int) $u['id']);
    if ($lv < 350) {
        flash_set('老咸鱼摇头：冰渊只要350级以上的冒险者，你现在' . $lv . '级。');
    } elseif (empty($mats['fr_ticket'])) {
        flash_set('老咸鱼：没有【冰渊副本入场券】进不去，打完碎冰洞的任务找我之前先有券（55环奖励）。');
    } else {
        add_mat((int) $u['id'], 'fr_ticket', -1);
        foreach (['fr_item1', 'fr_item2'] as $it) {
            db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], $it]);
        }
        mat_set((int) $u['id'], 'fr_stage', 1);
        mat_set((int) $u['id'], 'fr_enter', time());
        $u['loc'] = 'fr_gate';
        user_save($u);
        flash_set('老咸鱼把你顺进冰渊！45分钟倒计时开始！');
    }
    header('Location: home.php');
    exit;
}
if ($who === 'oldash' && $choice === 'open') {
    $lv = (int) ($u['lv'] ?? 1);
    $mats = mats_of((int) $u['id']);
    if ($lv < 580) {
        flash_set('老灰摇头：熔心只要580级以上的冒险者，你现在' . $lv . '级。');
    } elseif (empty($mats['em_ticket'])) {
        flash_set('老灰：没有【熔心副本入场券】进不去，火山麓69环奖励。');
    } else {
        add_mat((int) $u['id'], 'em_ticket', -1);
        foreach (['em_item1', 'em_item2'] as $it) {
            db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], $it]);
        }
        mat_set((int) $u['id'], 'em_stage', 1);
        mat_set((int) $u['id'], 'em_enter', time());
        $u['loc'] = 'em_gate';
        user_save($u);
        flash_set('老灰把你送进熔心！45分钟倒计时开始！');
    }
    header('Location: home.php');
    exit;
}
if ($who === 'watcher' && $choice === 'meet' && (int) $u['quest'] === 75) {
    if (!empty(cflags((int) $u['id'])['doyle_met'])) {
        flash_set('守望者：名字留过了，别出声，走。');
    } else {
        cflag_set((int) $u['id'], 'doyle_met', 1);
        user_save($u);
        flash_set('柱子后面睁开一只眼睛，又闭上了。多伊尔翻了个身，岩浆涨了三尺。你活下来了——任务能交了，它没醒。');
    }
    header('Location: npc.php?who=watcher');
    exit;
}
if ($who === 'soulmaster' && $choice === 'craft') {
    flash_set(soul_craft((int) $u['id']));
    header('Location: npc.php?who=soulmaster');
    exit;
}
if ($who === 'soulmaster' && $choice === 'up') {
    flash_set(soul_upgrade((int) $u['id'], (int) ($_GET['eid'] ?? 0)));
    header('Location: npc.php?who=soulmaster');
    exit;
}
if (in_array($who, ['bounty_boss', 'bounty_elder', 'bounty_frost'], true) && ($choice === 'take' || $choice === 'claim')) {    flash_set(bounty_claim((int) $u['id'], (int) ($_GET['i'] ?? 0)));    header('Location: npc.php?who=' . $who);    exit;}    $lv = (int) ($u['lv'] ?? 1);
if ($who === 'oldash' && $choice === 'open2') {
    $lv = (int) ($u['lv'] ?? 1);
    if ($lv < 580) {
        flash_set('老灰摇头：灰烬深渊只要580级以上的冒险者，你现在' . $lv . '级。');
    } elseif ((int) ($u['gold'] ?? 0) < 300000) {
        flash_set('老灰：进深渊要30万买命钱，灰里刨食不容易。');
    } else {
        $u['gold'] = (int) $u['gold'] - 300000;
        mat_set((int) $u['id'], 'as_enter', time());
        $u['loc'] = 'as_gate';
        user_save($u);
        flash_set('老灰把你推进灰烬深渊！45分钟倒计时开始！');
    }
    header('Location: home.php');
    exit;
}
    header('Location: home.php');
    exit;
}
}
// 1觉四试炼：铁拳承伤/莫里亚蒂答题/霍克追鹿/塞缪尔舍药
if ($who === 'rb_veil' && $choice === 'try' && in_array((int) $u['quest'], [40, 41], true)) {
    $f = cflags((int) $u['id']);
    if (!empty($f['rb1_sum'])) { flash_set('帷幕：契约已经签了，别再割自己了。'); }
    else {
        $cost = max(1, (int) floor((int) ($u['hp'] ?? 1) * 0.2));
        if ((int) ($u['hp'] ?? 1) - $cost < 1) { flash_set('帷幕：你这点血不够献的，回满再来。'); }
        else {
            $u['hp'] = (int) ($u['hp'] ?? 1) - $cost;
            cflag_set((int) $u['id'], 'rb1_sum', 1);
            $u['gold'] = (int)($u['gold'] ?? 0) + 5000;
            user_save($u);
            flash_set('你割开手掌，血没落地就被黑暗喝掉了。一只深渊小鬼从影子里抬起头，第一次叫你主人。深渊试炼通过！奖励5银。');
        }
    }
    header('Location: npc.php?who=rb_veil');
    exit;
}
if ($who === 'rb_iron' && $choice === 'try' && in_array((int) $u['quest'], [40, 41], true)) {
    $f = cflags((int) $u['id']);
    if (!empty($f['rb1_war'])) { flash_set('铁拳：这关你过了，别再来找打。'); }
    elseif (!empty($f['rb1_war_try'])) {
        cflag_set((int) $u['id'], 'rb1_war', 1);
        $u['gold'] = (int)($u['gold'] ?? 0) + 5000; user_save($u);
        flash_set('铁拳：十回合，站着！战士试炼通过！奖励5银。');
    } else {
        cflag_set((int) $u['id'], 'rb1_war_try', 1); user_save($u);
        flash_set('铁拳的学徒上场了！他只会砍你，你只能普通攻击（去打一架再回来，技能不许用）。');
    }
    header('Location: npc.php?who=rb_iron'); exit;
}
if ($who === 'rb_mori' && $choice === 'quiz' && in_array((int) $u['quest'], [40, 41], true)) {
    $f = cflags((int) $u['id']);
    $a1 = (string)($_GET['a1'] ?? ''); $a2 = (string)($_GET['a2'] ?? ''); $a3 = (string)($_GET['a3'] ?? '');
    if (!empty($f['rb1_mag'])) { flash_set('莫里亚蒂：你已经懂了，去别处看看吧。'); }
    elseif ($a1 !== '' && $a2 !== '' && $a3 !== '') {
        if ($a1 === 'light' && $a2 === 'holy' && $a3 === 'arcane') { cflag_set((int) $u['id'], 'rb1_mag', 1); $u['gold'] = (int)($u['gold'] ?? 0) + 5000; user_save($u); flash_set('莫里亚蒂：全对！法师试炼通过！奖励5银。'); }
        elseif ((int)($u['gold'] ?? 0) < 1000) { flash_set('答错了，重答一次1银，你钱不够。'); }
        else { $u['gold'] = (int)$u['gold'] - 1000; user_save($u); flash_set('答错了，扣1银，再想想（光/圣光/奥术）。'); }
    }
    header('Location: npc.php?who=rb_mori'); exit;
}
if ($who === 'rb_hawk' && in_array($choice, ['tree', 'vale', 'grass'], true) && in_array((int) $u['quest'], [40, 41], true)) {
    $f = cflags((int) $u['id']);
    if (!empty($f['rb1_hun'])) { flash_set('霍克：鹿已经认得你了，去忙别的吧。'); }
    elseif ($choice === 'tree' && mt_rand(1, 100) > 50) { flash_set('树冠太吵，白鹿跑了！再试一次（换条路更稳）。'); }
    elseif ($choice === 'vale') { cflag_set((int) $u['id'], 'rb1_hun_wait', time() + 600); flash_set('你沿溪谷跟了上去……十分钟后再来找我（先去干别的）。'); }
    elseif ($choice === 'grass') { cflag_set((int) $u['id'], 'rb1_hun_wait', time() + 300); flash_set('你趴进草丛……五分钟后再来找我。'); }
    else { cflag_set((int) $u['id'], 'rb1_hun', 1); $u['gold'] = (int)($u['gold'] ?? 0) + 5000; user_save($u); flash_set('树冠突袭成功！猎手试炼通过！奖励5银。'); }
    header('Location: npc.php?who=rb_hawk'); exit;
if ($who === 'rb_samuel' && $choice === 'give' && in_array((int) $u['quest'], [40, 41], true)) {
    $f = cflags((int) $u['id']);
    $pick = array_map('intval', explode(',', (string)($_GET['p'] ?? '')));
    if (!empty($f['rb1_pri']) || (job_id_of($u) === 'summoner' && $choice === 'summoner' && empty($f['rb1_sum']))) { flash_set('塞缪尔：你已经做过取舍了。'); }
    elseif ($pick === []) { flash_set('先选人再交给我。'); }
    else {
        $need = [1,1,1,2,2,2,2,3,3,5]; $cost = 0; $saved = 0;
        foreach ($pick as $pi) { if ($pi >= 1 && $pi <= 10) { $cost += $need[$pi - 1]; $saved++; } }
        $pick = array_values(array_unique($pick)); $cost = 0; $saved = 0;
        foreach ($pick as $pi) { if ($pi >= 1 && $pi <= 10) { $cost += $need[$pi - 1]; $saved++; } }
        if ($cost > 10) { flash_set($cost . '份药不够分，只有十份。'); }
        elseif ($saved >= 6) { cflag_set((int) $u['id'], 'rb1_pri', 1); $u['gold'] = (int)($u['gold'] ?? 0) + 5000; user_save($u); $m = '塞缪尔：救下' . $saved . '人！牧师试炼通过！奖励5银。'; flash_set($m); }
        else { $m2 = '只救下' . $saved . '人，不到六人。再想想（先救药少的人）。'; flash_set($m2); }
    }
    header('Location: npc.php?who=rb_samuel'); exit;
}
if ($who === 'rb_waldon' && in_array($choice, ['warrior','mage','hunter','priest','summoner'], true) && (int) $u['quest'] === 45) {
    $f = cflags((int) $u['id']);
    if (empty($f['rb1_war']) || empty($f['rb1_mag']) || empty($f['rb1_hun']) || empty($f['rb1_pri']) || (job_id_of($u) === 'summoner' && $choice === 'summoner' && empty($f['rb1_sum']))) { flash_set('瓦尔顿：四重试炼还没完，别急。'); }
    elseif (!empty($f['rb1_job'])) { flash_set('路已经选了，走下去吧。'); }
    else {
        cflag_set((int) $u['id'], 'rb1_job', $choice);
        $u['quest'] = 46; $u['gold'] = (int)($u['gold'] ?? 0) + 20000; user_save($u);
        $sk = ['warrior' => 'w3', 'mage' => 'm3', 'hunter' => 'h3', 'priest' => 'p3', 'summoner' => 's3'][$choice] ?? '';
        if ($sk !== "") { $bk = "book_" . $sk; add_mat((int) $u["id"], $bk, 1); }
        $rn = rebirth1_names()[$choice] ?? $choice; $pd2 = rebirth1_passive_desc($choice);
        send_mail((int) $u['id'], '总会长', 'rebirth', '1觉', $rn . $pd2, []);
        $rn2 = rebirth1_names()[$choice] ?? $choice;
        flash_set('1觉完成' . $rn2);
    }
    header('Location: npc.php?who=rb_waldon'); exit;
}
}
if ($who === 'rb_hawk' && $choice === 'back' && in_array((int) $u['quest'], [40, 41], true)) {
    $f = cflags((int) $u['id']);
    if (!empty($f['rb1_hun'])) { flash_set('霍克：鹿已经认得你了。'); }
    elseif (!empty($f['rb1_hun_wait']) && (int)$f['rb1_hun_wait'] > time()) { $left = gmdate('i:s', (int)$f['rb1_hun_wait'] - time()); flash_set('还不到时候，白鹿还没回来（剩' . $left . '）。'); }
    elseif (!empty($f['rb1_hun_wait'])) { cflag_set((int) $u['id'], 'rb1_hun', 1); $u['gold'] = (int)($u['gold'] ?? 0) + 5000; user_save($u); flash_set('你跟着白鹿到了林间空地！猎手试炼通过！奖励5银。'); }
    else { flash_set('先选一条路再回来。'); }
    header('Location: npc.php?who=rb_hawk'); exit;
if ($who === 'rb_samuel' && $choice === 'give' && in_array((int) $u['quest'], [40, 41], true)) {
    $f = cflags((int) $u['id']);
    $pick = array_map('intval', explode(',', (string)($_GET['p'] ?? '')));
    if (!empty($f['rb1_pri']) || (job_id_of($u) === 'summoner' && $choice === 'summoner' && empty($f['rb1_sum']))) { flash_set('塞缪尔：你已经做过取舍了。'); }
    elseif ($pick === []) { flash_set('先选人再交给我。'); }
    else {
        $need = [1,1,1,2,2,2,2,3,3,5]; $cost = 0; $saved = 0;
        foreach ($pick as $pi) { if ($pi >= 1 && $pi <= 10) { $cost += $need[$pi - 1]; $saved++; } }
        $pick = array_values(array_unique($pick)); $cost = 0; $saved = 0;
        foreach ($pick as $pi) { if ($pi >= 1 && $pi <= 10) { $cost += $need[$pi - 1]; $saved++; } }
        if ($cost > 10) { flash_set($cost . '份药不够分，只有十份。'); }
        elseif ($saved >= 6) { cflag_set((int) $u['id'], 'rb1_pri', 1); $u['gold'] = (int)($u['gold'] ?? 0) + 5000; user_save($u); $m = '塞缪尔：救下' . $saved . '人！牧师试炼通过！奖励5银。'; flash_set($m); }
        else { $m2 = '只救下' . $saved . '人，不到六人。再想想（先救药少的人）。'; flash_set($m2); }
    }
    header('Location: npc.php?who=rb_samuel'); exit;
}
if ($who === 'rb_waldon' && in_array($choice, ['warrior','mage','hunter','priest','summoner'], true) && (int) $u['quest'] === 45) {
    $f = cflags((int) $u['id']);
    if (empty($f['rb1_war']) || empty($f['rb1_mag']) || empty($f['rb1_hun']) || empty($f['rb1_pri']) || (job_id_of($u) === 'summoner' && $choice === 'summoner' && empty($f['rb1_sum']))) { flash_set('瓦尔顿：四重试炼还没完，别急。'); }
    elseif (!empty($f['rb1_job'])) { flash_set('路已经选了，走下去吧。'); }
    else {
        cflag_set((int) $u['id'], 'rb1_job', $choice);
        $u['quest'] = 46; $u['gold'] = (int)($u['gold'] ?? 0) + 20000; user_save($u);
        $sk = ['warrior' => 'w3', 'mage' => 'm3', 'hunter' => 'h3', 'priest' => 'p3', 'summoner' => 's3'][$choice] ?? '';
        if ($sk !== "") { $bk = "book_" . $sk; add_mat((int) $u["id"], $bk, 1); }
        $rn = rebirth1_names()[$choice] ?? $choice; $pd2 = rebirth1_passive_desc($choice);
        $body = "rb1:" . $rn . "|" . $pd2;
        send_mail((int) $u["id"], "w2", "rebirth", "t2", $body, []);
        $rn2 = rebirth1_names()[$choice] ?? $choice;
        flash_set("rb1done:" . $rn2);
    }
    header('Location: npc.php?who=rb_waldon'); exit;
}
}
if ($who === 'waldon' && $choice === 'start' && (int) $u['quest'] === 28) {
    $u['quest'] = 30;
    user_save($u);
    quest3_baseline((int) $u['id'], 30);
    flash_set('瓦尔顿：银丝已经吊满全城。第三章·傀儡之夜开启！去中央大道杀傀儡守卫×80。');
    header('Location: npc.php?who=waldon');
    exit;
}
if ($who === 'guard_captain' && in_array($choice, ['kill', 'cut', 'leave'], true) && (int) $u['quest'] === 31 && empty(cflags((int) $u['id'])['gc_done'])) {
    if ($choice === 'cut') {
        ch2_choice($u, 'gc_done', 'gc_result', 'cut', 100, '你切断他身上的银丝。他活下来了，后续会在农场出现报答你。（+1银）');
    } elseif ($choice === 'kill') {
        ch2_choice($u, 'gc_done', 'gc_result', 'kill', 0, '你结束了他的痛苦。银丝缩回天上，舞会照常进行。');
    } else {
        ch2_choice($u, 'gc_done', 'gc_result', 'leave', 0, '你转身离开。身后舞曲不停，像什么都没发生。');
    }
    header('Location: npc.php?who=guard_captain');
    exit;
}
if ($who === 'stringer' && in_array($choice, ['ask', 'kill', 'purify'], true) && (int) $u['quest'] === 37 && empty(cflags((int) $u['id'])['st_done'])) {
    if ($choice === 'ask') {
        ch2_choice($u, 'st_done', 'st_result', 'ask', 0, '你问：你是谁？牵线者沉默很久：我是国王。你的灵魂被认可了。（魔力上限+50）');
        $u['maxmp'] = (int) ($u['maxmp'] ?? 0) + 50;
        $u['mp'] = (int) ($u['mp'] ?? 0) + 50;
        user_save($u);
    } elseif ($choice === 'kill') {
        ch2_choice($u, 'st_done', 'st_result', 'kill', 0, '你直接动手。银丝崩断的声音像满城的琴弦一起断掉。');
    } else {
        ch2_choice($u, 'st_done', 'st_result', 'purify', 0, '你试图净化它。银丝白了一瞬，又黑了回去。它说：谢谢，但别白费力气。');
    }
    header('Location: npc.php?who=stringer');
    exit;
}
if ($who === 'spider' && $choice === 'open') { // 银丝母巢 open 已下线（券走牵线者掉落兑换），阿蛛只留傀儡回廊 open2
if ($who === 'priest_c' && $choice === 'diamond') {
    $mats = mats_of((int) $u['id']);
    $wid = date('oW');
    $week = (int) ($mats['active_week'] ?? 0);
    if ((string) ($mats['priest_wid'] ?? '') === $wid) { flash_set('本周领过了，下周一再来。'); }
    elseif ($week < 7200) { flash_set('本周有效游玩还差' . gmdate('H:i:s', 7200 - $week) . '，够2小时再来。'); }
    else { mat_set((int) $u['id'], 'priest_wid', (int) $wid); $u['diamonds'] = (int) ($u['diamonds'] ?? 0) + 10; user_save($u); flash_set('圣光见证！领到10魔钻，下周再来。'); }
    header('Location: npc.php?who=priest_c'); exit;
}
    $lv = (int) ($u['lv'] ?? 1);
    $mats = mats_of((int) $u['id']);
    if ($lv < 150 || $lv > 340) {
        flash_set('阿蛛摇头：银丝母巢只要150到340级的冒险者，你现在' . $lv . '级。');
    } elseif (empty($mats['mx_ticket'])) {
        flash_set('阿蛛：没有【银丝母巢入场券】进不去，去杀牵线者（1%掉）。');
    } else {
        add_mat((int) $u['id'], 'mx_ticket', -1);
        foreach (['mx_item1', 'mx_item2', 'mx_item3', 'mx_item4', 'mx_item5', 'mx_item6'] as $it) {
            db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], $it]);
        }
        mat_set((int) $u['id'], 'mx_stage', 1);
        mat_set((int) $u['id'], 'mx_enter', time());
        $u['loc'] = 'mx_gate';
        user_save($u);
        flash_set('阿蛛剪断你影子上的丝，送你进银丝母巢。45分钟倒计时开始！');
    }
    header('Location: home.php');
    exit;
}
if (in_array($who, ['med_g', 'med_t', 'med_s', 'med_c'], true) && str_starts_with($choice, 'buy')) {
    flash_set(buy_pct_potion((int) $u['id'], substr($choice, 3)));
    header('Location: npc.php?who=' . $who);
    exit;
}
if ($who === 'grocer' && str_starts_with($choice, 'ex')) {
    if ((int) $u['quest'] < 38) {
        flash_set('豆豆：农场还没开放，通关第三章再来。');
    } else {
        flash_set(farm_exchange((int) $u['id'], substr($choice, 2)));
    }
    header('Location: npc.php?who=grocer');
    exit;
}
if ($who === 'vera' && $choice === 'open') {
    $lv = (int) ($u['lv'] ?? 1);
    $mats = mats_of((int) $u['id']);
    if ($lv < 50 || $lv > 120) {
        flash_set('薇拉摇头：影蚀深渊只要50到120级的冒险者，你现在' . $lv . '级。');
    } elseif (empty($mats['abx_ticket'])) {
        flash_set('薇拉：没有【影蚀深渊入场券】进不去，去杀深渊之眼（1%掉）。');
    } else {
        add_mat((int) $u['id'], 'abx_ticket', -1);
        foreach (['abx_item1', 'abx_item2', 'abx_item3', 'abx_item4'] as $it) {
            db()->prepare('DELETE FROM mats WHERE uid=? AND mat=?')->execute([(int) $u['id'], $it]);
        }
        mat_set((int) $u['id'], 'abx_stage', 1);
        mat_set((int) $u['id'], 'abx_enter', time());
        $u['loc'] = 'abx_gate';
        user_save($u);
        flash_set('薇拉为你祝福，送你进影蚀深渊。30分钟倒计时开始！');
    }
    header('Location: home.php');
    exit;
}
if ($who === 'eventer' && str_starts_with($choice, 'peak')) {
    flash_set(peak_enter((int) $u['id'], $choice));
    header('Location: home.php');
    exit;
}
if ($who === 'augustus' && (int) $u['quest'] === 15 && in_array($choice, ['expose', 'question', 'hide'], true)) {
    cflag_set((int) $u['id'], 'ending', $choice);
    $u['quest'] = 16;
    if (in_array($choice, ['expose', 'question'], true)) {
        add_mat((int) $u['id'], 'qingzui_dan', 1);
    }
    user_save($u);
    $ending = ['expose' => '你把信交给卡尔。镇长被捕，矿坑封印。莉莉在井边说：你做了正确的选择。', 'question' => '你私下质问奥古斯都。这个疲惫的男人跪了下来，白石镇的未来悬在你的选择上。', 'hide' => '你烧掉了信。白石镇继续运转，但井底的黑雾比昨夜更浓。莉莉对你失望了。'][$choice];
    flash_set($ending . ' 离开时，井底传来你自己的声音：“谢谢你，帮我打开了门。”');
    header('Location: home.php');
    exit;
}

wap_start($people[$who]['name']);
$npcFlash = flash_get();
if ($npcFlash !== '') {
    echo '<div class="warn">' . h($npcFlash) . '</div><div class="hr">--------</div>';
}
echo '<div class="muted">' . h($people[$who]['text']) . '</div><div class="hr">--------</div>';
if ($who === 'augustus' && (int) $u['quest'] === 10) {
    echo '<a href="npc.php?who=augustus&choice=accept">接受矿坑调查委托</a><br>';
}
if ($who === 'alice') {
    echo '<a href="npc.php?who=alice&choice=open">开启副本【黑暗沼泽】（10~30级，消耗入场券×1）</a><br>';
}
if ($who === 'vera') {
    echo '<a href="npc.php?who=vera&choice=open">开启副本【影蚀深渊】（50~120级，消耗入场券×1）</a><br>';
}
if ($who === 'gmaster' || $who === 'greg') {
    echo '<a href="guild.php">进入公会大厅（创建/加入/排行/宣战）</a><br>';
}
if ($who === 'gwen' && (int) $u['quest'] === 16 && (int) ($u['lv'] ?? 1) >= 30) {
    echo '<a href="npc.php?who=gwen&choice=start">接受委托，开启第二章·影蚀之潮</a><br>';
}
if ($who === 'waldon' && (int) $u['quest'] === 28) {
    echo '<a href="npc.php?who=waldon&choice=start">递上推荐信，开启第三章·傀儡之夜</a><br>';
}
if ($who === 'rb_waldon' && (int) $u['quest'] === 38 && (int) ($u['lv'] ?? 1) >= 299) {
    echo '<a href="npc.php?who=rb_waldon&choice=start">聆听四条路，开启1觉·觉醒之路</a><br>';
if ($who === 'rb_waldon' && (int) $u['quest'] === 76 && (int) ($u['lv'] ?? 1) >= 599) {
    echo '<a href="npc.php?who=rb_waldon&choice=start2">聆听八条路，开启2觉·群攻之路</a><br>';
}
}
if ($who === 'rb_waldon' && (int) $u['quest'] === 45) {
    $rf = cflags((int) $u['id']);
    if (!empty($rf['rb1_war']) && !empty($rf['rb1_mag']) && !empty($rf['rb1_hun']) && !empty($rf['rb1_pri']) && empty($rf['rb1_job'])) {
        foreach (rebirth1_names() as $jid => $jname) {
            echo '<a href="npc.php?who=rb_waldon&choice=' . $jid . '">选择【' . h($jname) . '】：' . h(rebirth1_passive_desc($jid)) . '</a><br>';
        }
    }
}
if ($who === 'rb_waldon' && (int) $u['quest'] === 79 && empty(cflags((int) $u['id'])['rb2_job'])) {
    $base = job_id_of($u);
    foreach (rebirth2_names()[$base] ?? [] as $rid => $rname) {
        echo '<a href="npc.php?who=rb_waldon&choice=' . $rid . '">选择【' . h($rname) . '】：' . h(rebirth2_passive_desc($rid)) . '</a><br>';
    }
}
if ($who === 'rb_waldon' && (int) $u['quest'] === 80 && !empty(cflags((int) $u['id'])['rb2_job']) && empty(cflags((int) $u['id'])['rb2_done'])) {
    echo '<a href="npc.php?who=rb_waldon&choice=crown">加冕！</a><br>';
}
if ($who === 'voidwalker') {
    echo '<a href="npc.php?who=voidwalker&choice=open">进虚空裂隙（600级+，30万买命钱，45分钟）</a><br>';
if ($who === 'rockwarden') {
    echo '<a href="npc.php?who=rockwarden&choice=open">开启副本【岩狱·泰坦】（700~900级，门票30万，45分钟，掉土龙蛋+岩狱套）</a><br>';
}
}
if ($who === 'northwind' && in_array((int) $u['quest'], [38, 46], true)) {
    echo '<a href="npc.php?who=northwind&choice=start">接下投名状，开启第四章·凛冬冰原</a><br>';
}
if ($who === 'oldfish') {
    echo '<a href="npc.php?who=oldfish&choice=open">开启副本【冰渊】（350级+，消耗入场券×1，45分钟）</a><br>';
}
if ($who === 'oldash') {
    echo '<a href="npc.php?who=oldash&choice=open">开启副本【熔心】（580级+，消耗入场券×1，45分钟）</a><br>';
    echo '<a href="npc.php?who=oldash&choice=open2">开启副本【灰烬深渊】（580级+，30万买命钱，45分钟）</a><br>';
}
if ($who === 'qingya') {
    echo '雾森往南是火山，火山里住着一位老邻居。你的主线：<a href="quest.php">查看任务</a><br>';
    echo '<a href="npc.php?who=qingya&choice=open">开启副本【雾森祭坛】（500~620级，供奉20万，45分钟）</a><br>';
}
if ($who === 'watcher') {
    if ((int) $u['quest'] === 75 && empty(cflags((int) $u['id'])['doyle_met'])) {
        echo '<a href="npc.php?who=watcher&choice=meet">屏住呼吸，留下名字（见巨龙，不战斗）</a><br>';
    }
    echo '<a href="wb.php">世界BOSS·烬焰领主（每天刷新，全服磨血）</a><br>';
}
if ($who === 'soulmaster') {
    echo '当前真伤加成：' . soul_truedmg((int) $u['id']) . '<br>';
    echo '<a href="npc.php?who=soulmaster&choice=craft">合成神魂（10碎片，随机天地人）</a><br>';
    $st = db()->prepare('SELECT id, name FROM equips WHERE uid=? AND slot="soul" ORDER BY id DESC');
    $st->execute([(int) $u['id']]);
    foreach ($st->fetchAll() as $so) {
        $lv = soul_lv((int) $u['id'], (int) $so['id']);
        echo '·' . h(equip_shortname((string) $so['name'])) . '（' . $lv . '级） <a href="npc.php?who=soulmaster&choice=up&eid=' . (int) $so['id'] . '">升级</a><br>';
    }
}
if ($who === 'spider') {
    echo '<a href="npc.php?who=spider&choice=open2">开启副本【傀儡回廊】（300~460级，门票5万，45分钟）</a><br>';
}
if (in_array($who, ['bounty_boss', 'bounty_elder', 'bounty_frost'], true)) {
    echo '今日悬赏：<br>';
    foreach (bounty_today() as $i => $b) {
        echo '·' . h(monsters()[$b['mid']]['name']) . '×' . $b['need'] . '（赏' . h(fmt_money($b['gold'])) . '+' . h(mat_name($b['pot'])) . '×2）';
        echo ' <a href="npc.php?who=' . $who . '&choice=take&i=' . $i . '">接</a> <a href="npc.php?who=' . $who . '&choice=claim&i=' . $i . '">交</a><br>';
    }
}
if ($who === 'rb_iron' && in_array((int) $u['quest'], [40, 41], true) && empty(cflags((int) $u['id'])['rb1_war'])) {
    echo '<a href="npc.php?who=rb_iron&choice=try">接受战士试炼（只能普攻，撑十回合）</a><br>';
}
if ($who === 'rb_mori' && in_array((int) $u['quest'], [40, 41], true) && empty(cflags((int) $u['id'])['rb1_mag'])) {
    echo '三道题（答错1银）：<br>';
    echo '一、什么克制深渊？二、什么净化影蚀？三、什么冻结时间？<br>';
    echo '<a href="npc.php?who=rb_mori&choice=quiz&a1=light&a2=holy&a3=arcane">交卷：光·圣光·奥术</a><br>';
    echo '<a href="npc.php?who=rb_mori&choice=quiz&a1=fire&a2=fire&a3=ice">交卷：火·火·冰（试试答错）</a><br>';
}
if ($who === 'rb_hawk' && in_array((int) $u['quest'], [40, 41], true) && empty(cflags((int) $u['id'])['rb1_hun'])) {
    echo '<a href="npc.php?who=rb_hawk&choice=tree">走树冠（快，50%惊鹿）</a><br>';
    echo '<a href="npc.php?who=rb_hawk&choice=vale">走溪谷（稳，10分钟）</a><br>';
    echo '<a href="npc.php?who=rb_hawk&choice=grass">走草丛（稳，5分钟）</a><br>';
    echo '<a href="npc.php?who=rb_hawk&choice=back">回去找霍克交差</a><br>';
}
if ($who === 'rb_veil' && in_array((int) $u['quest'], [40, 41], true)) {
    if (!empty(cflags((int) $u['id'])['rb1_sum'])) {
        echo '<span class="muted">契约已签。</span><br>';
    } else {
        echo '<a href="npc.php?who=rb_veil&choice=try">深渊试炼：献祭两成生命</a><br>';
    }
}
if ($who === 'rb_samuel' && in_array((int) $u['quest'], [40, 41], true) && empty(cflags((int) $u['id'])['rb1_pri'])) {
    echo '十份药，十个病人（需求1/1/1/2/2/2/2/3/3/5），救满6人即过：<br>';
    echo '<a href="npc.php?who=rb_samuel&choice=give&p=1,2,3,4,5,6">救1~6号（3份+6份=9份，救6人）</a><br>';
    echo '<a href="npc.php?who=rb_samuel&choice=give&p=10">只救10号（5份，救1人，会失败）</a><br>';
}
if ($who === 'guard_captain' && (int) $u['quest'] === 31 && empty(cflags((int) $u['id'])['gc_done'])) {
    echo '抉择：<a href="npc.php?who=guard_captain&choice=kill">杀了他</a> <a href="npc.php?who=guard_captain&choice=cut">切断银丝(+1银)</a> <a href="npc.php?who=guard_captain&choice=leave">离开</a><br>';
}
if ($who === 'stringer' && (int) $u['quest'] === 37 && empty(cflags((int) $u['id'])['st_done'])) {
    echo '抉择：<a href="npc.php?who=stringer&choice=kill">杀死牵线者</a> <a href="npc.php?who=stringer&choice=purify">试图净化</a> <a href="npc.php?who=stringer&choice=ask">问：你是谁？(+50魔力上限)</a><br>';
}
if ($who === 'spider') {
    // 银丝母巢入口已迁走，阿蛛只开傀儡回廊（见下）。

}
if ($who === 'priest_c') {
    $pm = mats_of((int) $u['id']);
    $pw = (int) ($pm['active_week'] ?? 0);
    echo '本周有效游玩：' . gmdate('H:i:s', min($pw, 86399)) . '/02:00:00<br>';
    echo '<a href="npc.php?who=priest_c&choice=diamond">兑换10魔钻（每周一次）</a><br>';
}
if ($who === 'gallu') {
    if ((int) ($u['quest'] ?? 0) >= 81 && (int) ($u['lv'] ?? 1) >= 700) {
        echo '<a href="npc.php?who=gallu&choice=start">开启第八章·神界（需要700级）</a><br>';
    } else {
        echo '<span class="muted">（700级、2觉完成后可从这里进入神界主线）</span><br>';
    }
}
if ($who === 'fallenguide') {
    echo '<a href="npc.php?who=fallenguide&choice=enter">进入堕天阶（消耗堕天阶入场券，45分钟）</a><br>';
}
if ($who === 'yzkeeper') {
    $ym = date('Y-m');
    $used = (string) (mats_of((int) $u['id'])['yz_month'] ?? '') === $ym;
    if ((int) ($u['lv'] ?? 1) < 700) {
        echo '<span class="muted">需要700级。</span><br>';
    } elseif ($used) {
        echo '<span class="muted">本月已挑战过源罪渊，下个月再来。</span><br>';
    } else {
        echo '<a href="npc.php?who=yzkeeper&choice=enter">进入源罪渊（700级+，每月一次，30分钟）</a><br>';
    }
    echo '<span class="muted">单BOSS；击杀后70%概率获得【寂灭】/【噬渊】套装或源罪渊传说。</span><br>';
}
if ($who === 'rspriest') {
    echo '转生条件：999级 + 当日世界BOSS + 任一神魂X级。<br>';
    echo '当前：' . h(rs_require_text((int) $u['id'])) . '<br>';
    echo '<a href="npc.php?who=rspriest&choice=rite">举行转生仪式</a><br>';
}
if ($who === 'godmerchant') {
    echo '<a href="npc.php?who=godmerchant&choice=heal">圣愿（花50000铜回满血魔）</a><br>';
}
if ($who === 'gykeeper') {
    $curMonth = date('Y-m');
    $used = (string) (mats_of((int) $u['id'])['gy_month'] ?? '') === $curMonth;
    if ($used) {
        echo '<span class="muted">本月已挑战过错渊，下个月再来。</span><br>';
    } else {
        echo '<a href="npc.php?who=gykeeper&choice=enter">进入罪渊（本月限一次，30分钟）</a><br>';
    }
}
if ($who === 'arena_m') {
    if ((int) ($u['lv'] ?? 1) >= 80 && empty(cflags((int) ($u['id'] ?? 0))['arena_guide_done']) && (int) ($u['quest'] ?? 0) === 28) {
        cflag_set((int) $u['id'], 'arena_guide_done', 1);
        $u['gold'] = (int) ($u['gold'] ?? 0) + 2000;
        user_save($u);
        flash_set('雷横拍了拍你的肩：好，80级了，有胆！竞技场欢迎你。任务完成，奖励20银！');
        header('Location: npc.php?who=arena_m');
        exit;
    }
    echo '<a href="arena.php">进入竞技场</a><br>';
    echo '<a href="arena.php?a=rank">查看排名</a> <a href="arena.php?a=prize">查看奖励</a><br>';
    echo '<a href="arena.php?a=def">查看防守记录</a><br>';
}
if ($who === 'eventer') {
    if (!peak_open()) {
        echo '<span class="muted">今晚22:30~23:00开打，现在先备好药。</span><br>';
    } else {
        $myArena = peak_arena_for_level((int) ($u['lv'] ?? 1));
        if ($myArena === '') {
            echo '<span class="muted">你' . (int) ($u['lv'] ?? 1) . '级，三个战场（100~300/301~600/601~999）都收不了你。</span><br>';
        } else {
            $cfg = peak_arenas()[$myArena];
            echo '你的战场：【' . h(loc($myArena)['name']) . '】(' . $cfg['min'] . '~' . $cfg['max'] . '级，胜者真龙丹x' . $cfg['pills'] . ')<br>';
            echo '<a href="npc.php?who=eventer&choice=' . $myArena . '">进场！（伤势回满，出来就没资格了）</a><br>';
        }
    }
}
if ($who === 'grocer' && (int) $u['quest'] >= 38) {
    $mats = mats_of((int) $u['id']);
    echo '农场币：' . (int) ($mats['farm_coin'] ?? 0) . '<br>';
    echo '换：<a href="npc.php?who=grocer&choice=expetfood">宠物粮食(10币/周20)</a> <a href="npc.php?who=grocer&choice=exexpcard">升级卡100型(100币/周2)</a> <a href="npc.php?who=grocer&choice=exreset">洗点药(200币/周1)</a><br>';
}
if (in_array($who, ['horse_t', 'horse_s', 'horse_g'], true)) {
    echo '<a href="horse.php">去赌马（2小时一场）</a><br>';
}
if (in_array($who, ['rank_t', 'rank_s', 'rank_g', 'rank_c'], true)) {
    echo '<a href="rank.php">看排行榜（战力/活跃/消费/宠物/赛马）</a><br>';
}
if (in_array($who, ['med_g', 'med_t', 'med_s', 'med_c'], true)) {
    echo '卖药（按最大生命百分比回，战斗中手动喝，不自动）：<br>';
    $medm = mats_of((int) $u['id']);
    foreach (pct_potions() as $pmid => $pt) {
        $pr = $pt['unit'] === 'gold' ? fmt_money($pt['price']) : fmt_money($pt['price']);
        echo '·【' . h($pt['name']) . '】回' . $pt['pct'] . '%' . ((($pt['stat'] ?? 'hp') === 'mp') ? '蓝' : '血') . ' ' . h($pr) . '(有' . (int) ($medm[$pmid] ?? 0) . ') <a href="npc.php?who=' . $who . '&choice=buy' . $pmid . '">买</a><br>';
    }
}
if ($who === 'reed' && (int) $u['quest'] === 21 && empty(cflags((int) $u['id'])['reed_done'])) {
    echo '抉择：<a href="npc.php?who=reed&choice=kill">杀了他</a> <a href="npc.php?who=reed&choice=save">按住影子救他(血减半)</a> <a href="npc.php?who=reed&choice=leave">离开</a><br>';
}
if ($who === 'lily2' && (int) $u['quest'] === 22 && empty(cflags((int) $u['id'])['note_done'])) {
    echo '抉择：<a href="npc.php?who=lily2&choice=give">交给格温</a> <a href="npc.php?who=lily2&choice=burn">烧了</a> <a href="npc.php?who=lily2&choice=keep">自留</a><br>';
}
if ($who === 'apostle_echo' && (int) $u['quest'] === 23 && empty(cflags((int) $u['id'])['apostle_done'])) {
    echo '抉择：<a href="npc.php?who=apostle_echo&choice=killfirst">直接杀</a> <a href="npc.php?who=apostle_echo&choice=free">放走带话</a> <a href="npc.php?who=apostle_echo&choice=ask">追问莉莉的事</a><br>';
}
if ($who === 'golem_echo' && (int) $u['quest'] === 24 && empty(cflags((int) $u['id'])['golem_done'])) {
    echo '抉择：<a href="npc.php?who=golem_echo&choice=see">接受，看真相</a> <a href="npc.php?who=golem_echo&choice=refuse">拒绝杀掉</a> <a href="npc.php?who=golem_echo&choice=what">问真相是什么</a><br>';
}
if ($who === 'lord' && (int) $u['quest'] === 25 && empty(cflags((int) $u['id'])['lord_done'])) {
    echo '抉择：<a href="npc.php?who=lord&choice=fake">假意偷袭</a> <a href="npc.php?who=lord&choice=fight">直接开战</a> <a href="npc.php?who=lord&choice=asklily">问莉莉知道吗</a><br>';
}
if ($who === 'darklily' && (int) $u['quest'] === 26 && empty(cflags((int) $u['id'])['lily_done'])) {
    echo '抉择：<a href="npc.php?who=darklily&choice=slay">杀</a> <a href="npc.php?who=darklily&choice=talk">对话理解</a> <a href="npc.php?who=darklily&choice=where">问真莉莉在哪</a><br>';
}
if ($who === 'augustus' && (int) $u['quest'] === 15) {
    echo '你手里的信写着：“按您的吩咐，第七批已送达。”<br>';
    echo '<a href="npc.php?who=augustus&choice=expose">把信交给卡尔，揭发镇长</a><br>';
    echo '<a href="npc.php?who=augustus&choice=question">私下质问奥古斯都</a><br>';
    echo '<a href="npc.php?who=augustus&choice=hide">烧掉信，隐瞒真相</a><br>';
}
// 好感度：8主要NPC可赠送
if (isset(favor_npcs()[$who])) {
    echo '好感：' . favor_of((int) $u['id'], $who) . (favor_of((int) $u['id'], $who) >= 100 ? '（每周一寄清罪丹）' : '（满100每周一寄清罪丹）') . '<br>';
    $gm = mats_of((int) $u['id']);
    $opts = [];
    foreach (['enhance_t1', 'enhance_t2', 'enhance_t3', 'hp_pct10', 'hp_pct20', 'tent'] as $mid) {
        if ((int) ($gm[$mid] ?? 0) > 0) {
            $opts[] = '<a href="npc.php?who=' . $who . '&choice=gift&mid=' . $mid . '">赠' . mat_name($mid) . 'x1</a>';
        }
    }
    if ($opts !== []) {
        echo '赠送：' . implode(' ', $opts) . '<br>';
    }
}
// 偷袭：8可偷袭NPC
if (isset(ambush_npcs()[$who])) {
    $cfg = ambush_npcs()[$who];
    echo '<a href="npc.php?who=' . $who . '&choice=ambush">偷袭' . h($cfg['name']) . '（' . $cfg['lv'] . '级，胜有' . (int) round($cfg['legend']['rate'] * 100) . '%掉传说' . h($cfg['legend']['base']) . '）</a><br>';
}
echo '<a href="home.php">回行动</a>';
nav_line();
wap_end(false);
