<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$u = require_login();
$who = (string) ($_GET['who'] ?? '');
$choice = (string) ($_GET['choice'] ?? '');
$cur = (string) $u['loc'];
$people = [
    'barton' => ['name' => '老铁匠·巴顿', 'loc' => 'smith', 'text' => '新来的？卡尔说你要下矿。空手去就是送死。我这辈子打过三千把武器，这把生锈短剑是我学徒时打的，强化一下还能用。下级强化石矿里到处是，中级要打精英，上级只在深处。'],
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
    'rank_t' => ['name' => '榜单老人', 'loc' => 'town_sq', 'text' => '战力、活跃、充值、宠物、赛马，五榜每刻更新。扬名立万，就在此处。'],
    'rank_s' => ['name' => '榜单老人', 'loc' => 'silver_sq', 'text' => '白银城也看榜。数据全服通用。'],
    'rank_g' => ['name' => '榜单老人', 'loc' => 'square', 'text' => '灰雾村小地方，榜可是全服的。'],
    'waldon' => ['name' => '线人·瓦尔顿', 'loc' => 'avenue', 'text' => '真正的王都二十年前就没了，这里只是深渊搭的戏台。拿着格温的信，就去中央大道杀傀儡守卫，杀够80只再回来。'],
    'guard_captain' => ['name' => '守卫队长', 'loc' => 'noble', 'text' => '银丝已经缠到我脖子了……杀了我，或者切断银丝，或者……走开，别看。'],
    'stringer' => ['name' => '牵线者', 'loc' => 'theater', 'text' => '我不是深渊，我是国王。二十年前，国王为了永生献出灵魂，灵魂变成了我。你是谁？你来杀我，还是来理解我？'],
    'spider' => ['name' => '断线人·阿蛛', 'loc' => 'theater', 'text' => '剧场下面还有个母巢，是牵线者死后留下的卵。150级以上、带着母巢入场券（杀牵线者1%掉），我送你进去。45分钟，出来或者变成茧。'],
    'grocer' => ['name' => '菜商·豆豆', 'loc' => 'farm', 'text' => '农场币换好东西！粮食、洗点药、升级卡，每周限量，先到先得。'],
    'rank_c' => ['name' => '榜单老人', 'loc' => 'avenue', 'text' => '王都也看榜。数据全服通用。'],
    'med_g' => ['name' => '卖药郎中', 'loc' => 'square', 'text' => '跌打损伤找我！绷带草药金疮药圣水，铜币金币都收。记住：药只能战斗中手动喝，死了可别怪药。'],
    'med_t' => ['name' => '卖药郎中', 'loc' => 'market', 'text' => '白石镇分号，药价全服统一。绷带10%到圣水50%，按血量回，血越多越划算。'],
    'med_s' => ['name' => '卖药郎中', 'loc' => 'silver_sq', 'text' => '白银城分号。影子会动，血可不能空，备点圣水吧。'],
    'med_c' => ['name' => '卖药郎中', 'loc' => 'avenue', 'text' => '王都分号。傀儡不流血，你流，备药吧。'],
];
if (!isset($people[$who]) || $people[$who]['loc'] !== $cur) {
    flash_set('这个人不在这里。');
    header('Location: home.php');
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
if ($who === 'spider' && $choice === 'open') {
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
if ($who === 'augustus' && (int) $u['quest'] === 15 && in_array($choice, ['expose', 'question', 'hide'], true)) {
    cflag_set((int) $u['id'], 'ending', $choice);
    $u['quest'] = 16;
    user_save($u);
    $ending = ['expose' => '你把信交给卡尔。镇长被捕，矿坑封印。莉莉在井边说：你做了正确的选择。', 'question' => '你私下质问奥古斯都。这个疲惫的男人跪了下来，白石镇的未来悬在你的选择上。', 'hide' => '你烧掉了信。白石镇继续运转，但井底的黑雾比昨夜更浓。莉莉对你失望了。'][$choice];
    flash_set($ending . ' 离开时，井底传来你自己的声音：“谢谢你，帮我打开了门。”');
    header('Location: home.php');
    exit;
}

wap_start($people[$who]['name']);
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
if ($who === 'guard_captain' && (int) $u['quest'] === 31 && empty(cflags((int) $u['id'])['gc_done'])) {
    echo '抉择：<a href="npc.php?who=guard_captain&choice=kill">杀了他</a> <a href="npc.php?who=guard_captain&choice=cut">切断银丝(+1银)</a> <a href="npc.php?who=guard_captain&choice=leave">离开</a><br>';
}
if ($who === 'stringer' && (int) $u['quest'] === 37 && empty(cflags((int) $u['id'])['st_done'])) {
    echo '抉择：<a href="npc.php?who=stringer&choice=kill">杀死牵线者</a> <a href="npc.php?who=stringer&choice=purify">试图净化</a> <a href="npc.php?who=stringer&choice=ask">问：你是谁？(+50魔力上限)</a><br>';
}
if ($who === 'spider') {
    echo '<a href="npc.php?who=spider&choice=open">开启副本【银丝母巢】（150~340级，消耗入场券×1）</a><br>';
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
    echo '<a href="rank.php">看排行榜（战力/活跃/充值/宠物/赛马）</a><br>';
}
if (in_array($who, ['med_g', 'med_t', 'med_s', 'med_c'], true)) {
    echo '卖药（按最大生命百分比回，战斗中手动喝，不自动）：<br>';
    $medm = mats_of((int) $u['id']);
    foreach (pct_potions() as $pmid => $pt) {
        $pr = $pt['unit'] === 'gold' ? fmt_money($pt['price']) : fmt_money($pt['price']);
        echo '·【' . h($pt['name']) . '】回' . $pt['pct'] . '% ' . h($pr) . '(有' . (int) ($medm[$pmid] ?? 0) . ') <a href="npc.php?who=' . $who . '&choice=buy' . $pmid . '">买</a><br>';
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
echo '<a href="home.php">回行动</a>';
nav_line();
wap_end(false);
