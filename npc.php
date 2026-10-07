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
];
if (!isset($people[$who]) || $people[$who]['loc'] !== $cur) {
    flash_set('这个人不在这里。');
    header('Location: home.php');
    exit;
}

if ($who === 'augustus' && $choice === 'accept' && (int) $u['quest'] === 10) {
    $u['quest'] = 11;
    $u['chapter_flags'] = json_encode(['accepted' => true], JSON_UNESCAPED_UNICODE);
    user_save($u);
    flash_set('奥古斯都交给你矿坑封锁门的钥匙。任务更新：腐化的前兆。');
    header('Location: home.php');
    exit;
}
if ($who === 'carl' && (int) $u['quest'] === 11) {
    echo ''; // 保持对话页面，不自动跳过考验
}
if ($who === 'lily' && (int) $u['quest'] === 13) {
    $u['chapter_flags'] = json_encode(['accepted' => true, 'lily_warned' => true], JSON_UNESCAPED_UNICODE);
    user_save($u);
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
if ($who === 'augustus' && (int) $u['quest'] === 15 && in_array($choice, ['expose', 'question', 'hide'], true)) {
    $flags = ['ending' => $choice];
    $u['chapter_flags'] = json_encode($flags, JSON_UNESCAPED_UNICODE);
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
if ($who === 'augustus' && (int) $u['quest'] === 15) {
    echo '你手里的信写着：“按您的吩咐，第七批已送达。”<br>';
    echo '<a href="npc.php?who=augustus&choice=expose">把信交给卡尔，揭发镇长</a><br>';
    echo '<a href="npc.php?who=augustus&choice=question">私下质问奥古斯都</a><br>';
    echo '<a href="npc.php?who=augustus&choice=hide">烧掉信，隐瞒真相</a><br>';
}
echo '<a href="home.php">回行动</a>';
nav_line();
wap_end(false);
