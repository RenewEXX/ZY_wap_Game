<?php
declare(strict_types=1);
// ============================================================
// 立绘替换工具（内部使用）
// ------------------------------------------------------------
// 打开：浏览器访问 http://<服务器>/img/replacer.php
// 左栏=怪物(含六龙王)，中栏=角色/职业头像，右栏=宠物头像。点选 → 选图(弹 Windows 文件选择框) → 上传替换。
// 压缩：在“你的浏览器”里用 Canvas 处理后再上传（PNG、保留透明、小图不放大）：
//       · 怪物：长边≤256、保持原始宽高比（首页有大量缩略图，太大拖慢加载）。
//       · 角色头像 / 宠物头像：居中裁成方形，存两张——列表小图(≤128) + 点开大图(≤640)，大图按需加载。
//       服务端无需 GD，只负责校验与落盘。
// 落盘：怪物 → img/monsters/<怪物id>.png ；角色 → img/avatars/<头像id把:换成_>.png ；宠物 → img/pets/<宠物id>.png（自动删旧图）。
// 安全：只允许替换已登记 id，文件名由 id 派生，仅接受图片，限制大小。
// ============================================================
require __DIR__ . '/../includes/bootstrap.php';

$MON_DIR = __DIR__ . '/monsters';
$AV_DIR  = __DIR__ . '/avatars';
$EXT_ALL = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

$mons = monsters();
$avs  = avatar_catalog();
$pets = pet_species();
$dragonIds = array_keys(dragons());
$PET_DIR = __DIR__ . '/pets';

// —— 区域 / 副本 / BOSS 标记数据 ——
// 副本 = dungeon_maps() 里登记的地图 key；区域 = map_regions() 的归属；BOSS = boss_of_map() + 六龙王世界BOSS。
$dungeonSet = [];
foreach (dungeon_maps() as $__dk) {
    if (is_string($__dk)) {
        $dungeonSet[$__dk] = true;
    }
}
$locRegion = [];
foreach (map_regions() as $__region => $__lks) {
    foreach ((array) $__lks as $__lk) {
        $locRegion[$__lk] = (string) $__region;
    }
}
// 小图 → 大区域标签（按“区域块函数”归类），避免每张小图单独成组
$chunkLabels = [
    'ch1_locations' => '荒野',
    'ch2_locations' => '白银城·影蚀之潮',
    'ch3_locations' => '王都·傀儡之夜',
    'farm_locations' => '王都农场',
    'dragon_locations' => '六圣柱',
    'wild3_locations' => '世界BOSS巢穴',
    'frostburg_locations' => '霜炉堡',
    'greenheart_locations' => '青叶城',
    'wild45_locations' => '荒野·冰原',
    'gr_locations' => '咕噜荒原',
    'ticket_hunt_locations' => '猎场',
];
$chunkFns = ['ch1_locations', 'ch2_locations', 'ch3_locations', 'dragon_locations', 'farm_locations', 'ice_locations', 'fo_locations', 'wild3_locations', 'frostburg_locations', 'greenheart_locations', 'wild45_locations', 'as_locations', 'vx_locations', 'gr_locations', 'ry_locations', 'god_locations', 'gf_locations', 'world_expansion_locations', 'region_dungeon_locations', 'abx_locations', 'mx_locations', 'sky_locations', 'sunken_locations', 'we2_locations', 'dsw_locations', 'pz_locations', 'sen_locations', 'fr_locations', 'em_locations', 'ticket_hunt_locations'];
$locChunk = [];
foreach ($chunkFns as $__fn) {
    if (!function_exists($__fn)) {
        continue;
    }
    $__ks = $__fn();
    if (!is_array($__ks)) {
        continue;
    }
    $__cl = $chunkLabels[$__fn] ?? '周边区域';
    foreach ($__ks as $__ck => $__cv) {
        if (!isset($locChunk[$__ck])) {
            $locChunk[$__ck] = $__cl;
        }
    }
}
// 副本地图 key → 大副本名（按 *_maps() 分组函数归类；区域副本按名字“·”前前缀）
$dungeonBigFns = [
    'dsw_maps' => '黑暗沼泽',
    'abx_maps' => '影蚀深渊',
    'mx_maps' => '银丝母巢',
    'fr_maps' => '冰渊',
    'em_maps' => '熔心',
    'pz_maps' => '傀儡回廊',
    'sen_maps' => '雾森祭坛',
    'as_maps' => '灰烬深渊',
    'ry_maps' => '岩狱',
    'vx_maps' => '虚空裂隙',
    'gy_maps' => '罪渊',
    'gf_maps' => '堕天阶',
    'yz_maps' => '源罪渊',
];
$locDungeonBig = [];
foreach ($dungeonBigFns as $__fn => $__lab) {
    if (!function_exists($__fn)) {
        continue;
    }
    $__ks = $__fn();
    if (!is_array($__ks)) {
        continue;
    }
    foreach ($__ks as $__ck) {
        if (is_string($__ck)) {
            $locDungeonBig[$__ck] = $__lab;
        }
    }
}
if (function_exists('region_dungeon_maps')) {
    $__rd = region_dungeon_maps();
    if (is_array($__rd)) {
        foreach ($__rd as $__ck) {
            if (!is_string($__ck) || isset($locDungeonBig[$__ck])) {
                continue;
            }
            $__nm = (string) (loc($__ck)['name'] ?? $__ck);
            $__bp = explode('·', $__nm, 2)[0];
            $locDungeonBig[$__ck] = ($__bp !== '') ? $__bp : $__nm;
        }
    }
}
$allLoc = function_exists('locations') ? locations() : [];
$monRegions = [];
$monDungeons = [];
$monOrphans = [];
foreach ($allLoc as $__lk => $__lv) {
    $__mids = $__lv['monsters'] ?? [];
    if (!is_array($__mids)) {
        continue;
    }
    if (isset($dungeonSet[$__lk])) {
        $__bucket = 'dgn';
        $__label = $locDungeonBig[$__lk] ?? (string) ($__lv['name'] ?? $__lk);
    } elseif (isset($locRegion[$__lk])) {
        $__bucket = 'reg';
        $__label = $locRegion[$__lk];
    } else {
        $__bucket = 'orph';
        $__label = $locChunk[$__lk] ?? '周边区域';
    }
    foreach ($__mids as $__mid) {
        if (!is_string($__mid)) {
            continue;
        }
        if ($__bucket === 'dgn') {
            $monDungeons[$__mid][$__label] = true;
        } elseif ($__bucket === 'reg') {
            $monRegions[$__mid][$__label] = true;
        } else {
            $monOrphans[$__mid][$__label] = true;
        }
    }
}
$bossSet = [];
foreach ($allLoc as $__lk => $__lv) {
    $__bm = boss_of_map((string) $__lk);
    if ($__bm !== '') {
        $bossSet[$__bm] = true;
    }
}
$dragonSet = [];
foreach (dragons() as $__dm => $__dd) {
    $bossSet[$__dm] = true;
    $dragonSet[$__dm] = true;
}
// 六圣柱区域首领等“龙王体系”怪物：不一定写进某张地图的 monsters，但归属六圣柱
$dragonMonSet = [];
if (function_exists('dragon_monsters')) {
    foreach (dragon_monsters() as $__dm => $__dv) {
        $dragonMonSet[$__dm] = true;
    }
}
$monsterLevel = static fn(string $mid): int => max(1, (int) monster_lv($mid));
$monBadges = function (string $mid) use ($monRegions, $monOrphans, $monDungeons, $bossSet, $dragonSet, $dragonMonSet, $monsterLevel): string {
    $cat = '';
    if (isset($bossSet[$mid])) {
        $cat .= '<span class="badge boss">BOSS</span>';
    }
    if (isset($dragonSet[$mid])) {
        $cat .= '<span class="badge dragon">世界BOSS</span>';
    }
    $regs = array_merge(array_keys($monRegions[$mid] ?? []), array_keys($monOrphans[$mid] ?? []));
    foreach (array_slice($regs, 0, 2) as $r) {
        $cat .= '<span class="badge reg">' . h((string) $r) . '</span>';
    }
    if (count($regs) > 2) {
        $cat .= '<span class="badge reg">+' . (count($regs) - 2) . '</span>';
    }
    $dgns = array_keys($monDungeons[$mid] ?? []);
    foreach (array_slice($dgns, 0, 2) as $d) {
        $cat .= '<span class="badge dgn">副本·' . h((string) $d) . '</span>';
    }
    if (count($dgns) > 2) {
        $cat .= '<span class="badge dgn">+' . (count($dgns) - 2) . '</span>';
    }
    if ($regs === [] && $dgns === [] && isset($dragonMonSet[$mid])) {
        $cat .= '<span class="badge reg">六圣柱</span>';
    }
    if ($cat === '') {
        $cat = '<span class="badge none">未使用（无地图）</span>';
    }
    return '<span class="badge lv">Lv.' . $monsterLevel($mid) . '</span>' . $cat;
};

// 本工具位于 /img/replacer.php，查找函数返回的是“相对站点根”的 img/... 路径，
// 在 /img/ 目录下会被解析成 /img/img/... → 破图。这里统一补成 ../img/... 修正。
function rp_asset(string $u): string
{
    return ($u !== '' && str_starts_with($u, 'img/')) ? '../' . $u : $u;
}

// 统一响应：AJAX 返回 JSON；否则 PRG 重定向回页面
function rp_respond(bool $ajax, string $type, string $id, string $err, string $thumb = ''): void
{
    if ($ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $err === '', 'err' => $err, 'thumb' => $thumb, 't' => time()], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $q = 'type=' . urlencode($type) . '&id=' . urlencode($id);
    $q .= ($err === '') ? '&ok=1' : '&err=' . urlencode($err);
    header('Location: replacer.php?' . $q);
    exit;
}

// ---------------- 处理上传 ----------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['img'])) {
    $isAjax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch') || (($_POST['ajax'] ?? '') === '1');
    $type = (string) ($_POST['type'] ?? '');
    $id   = (string) ($_POST['id'] ?? '');
    $f    = $_FILES['img'];

    if ($type === 'monster') {
        if (preg_replace('/[^A-Za-z0-9_]/', '', $id) === '' || (!isset($mons[$id]) && !in_array($id, $dragonIds, true))) {
            rp_respond($isAjax, $type, $id, '未知的怪物 id');
        }
        $dir  = $MON_DIR;
        $base = preg_replace('/[^A-Za-z0-9_]/', '', $id);
    } elseif ($type === 'avatar') {
        if (!isset($avs[$id])) {
            rp_respond($isAjax, $type, $id, '未知的头像 id');
        }
        $dir  = $AV_DIR;
        $base = preg_replace('/[^A-Za-z0-9_]/', '_', $id);
    } elseif ($type === 'pet') {
        if (!isset($pets[$id])) {
            rp_respond($isAjax, $type, $id, '未知的宠物 id');
        }
        $dir  = $PET_DIR;
        $base = preg_replace('/[^A-Za-z0-9_]/', '_', $id);
    } else {
        rp_respond($isAjax, '', '', '类型不正确');
    }

    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        rp_respond($isAjax, $type, $id, '上传失败（错误码 ' . (int) ($f['error'] ?? -1) . '），可能文件过大。');
    }
    if (($f['size'] ?? 0) > 8 * 1024 * 1024) {
        rp_respond($isAjax, $type, $id, '图片太大（上限 8MB）');
    }
    $info = @getimagesize($f['tmp_name']);
    if ($info === false) {
        rp_respond($isAjax, $type, $id, '这不是有效的图片文件');
    }
    $map = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    $ext = $map[$info[2]] ?? '';
    if ($ext === '') {
        rp_respond($isAjax, $type, $id, '只支持 jpg / png / webp / gif');
    }
    if (!is_dir($dir) && !@mkdir($dir, 0777, true)) {
        rp_respond($isAjax, $type, $id, '目录无法创建：' . basename($dir));
    }
    // 删同名旧图（各种扩展），避免高优先级扩展名挡住新图；顺带清掉旧的大图
    foreach ($EXT_ALL as $old) {
        foreach (['', '.big.', '.hd.'] as $tierMark) {
            $p = $dir . '/' . $base . $tierMark . $old;
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }
    if (!@move_uploaded_file($f['tmp_name'], $dir . '/' . $base . '.' . $ext)) {
        rp_respond($isAjax, $type, $id, '写入失败（检查 img 目录写权限）');
    }
    // 头像/怪物：保存可选大图 base.big.<ext>(头像灯箱) / base.hd.<ext>(头像状态页、怪物战斗高清)
    foreach (['big', 'hd'] as $tier) {
        if ($tier === 'big' && !in_array($type, ['avatar', 'pet'], true)) {
            continue; // 只有头像/宠物用 .big 灯箱图
        }
        if (!isset($_FILES[$tier]) || !is_array($_FILES[$tier])) {
            continue;
        }
        $bf = $_FILES[$tier];
        if (($bf['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($bf['size'] ?? 0) > 12 * 1024 * 1024) {
            continue;
        }
        $binfo = @getimagesize($bf['tmp_name']);
        if ($binfo === false) {
            continue;
        }
        $bext = $map[$binfo[2]] ?? '';
        if ($bext !== '') {
            @move_uploaded_file($bf['tmp_name'], $dir . '/' . $base . '.' . $tier . '.' . $bext);
        }
    }
    // 返回新缩略图地址（带时间戳防缓存；补 ../ 修正 /img/ 目录下的相对路径）
    $thumb = ($type === 'monster') ? monster_image_url($id) : avatar_image_url($id);
    rp_respond($isAjax, $type, $id, '', rp_asset($thumb));
}

$selType = (string) ($_GET['type'] ?? '');
$selId   = (string) ($_GET['id'] ?? '');
$okFlag  = isset($_GET['ok']);
$errMsg  = (string) ($_GET['err'] ?? '');

function rp_row(string $type, string $id, string $name, string $thumbUrl, string $fallbackHtml = '', string $badges = ''): void
{
    $thumbUrl = rp_asset($thumbUrl);
    $inner = $thumbUrl !== ''
        ? '<img src="' . h($thumbUrl) . '" alt="" style="height:40px;width:40px;object-fit:cover;border-radius:6px;border:1px solid #333;background:#000;display:block">'
        : ($fallbackHtml !== ''
            ? '<span style="height:40px;width:40px;display:inline-flex;align-items:center;justify-content:center;border:1px dashed #444;border-radius:6px">' . $fallbackHtml . '</span>'
            : '<span style="height:40px;width:40px;display:inline-block;border:1px dashed #444;border-radius:6px"></span>');
    echo '<div class="row" data-type="' . h($type) . '" data-id="' . h($id) . '" data-label="' . h($name) . '">'
        . '<span class="thumb" data-base="' . h($type === 'monster' ? 'img/monsters/' : ($type === 'pet' ? 'img/pets/' : 'img/avatars/')) . '">' . $inner . '</span>'
        . '<div class="meta"><div class="nm">' . h($name) . '</div><div class="id">' . h($id) . '</div>'
        . ($badges !== '' ? '<div class="badges">' . $badges . '</div>' : '')
        . '</div>'
        . '</div>';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>立绘替换工具</title>
<style>
    * { box-sizing: border-box; }
    body { margin: 0; padding: 12px; background: #0f1115; color: #c9ccd1; font-family: ui-monospace, Consolas, "Microsoft YaHei", monospace; font-size: 14px; }
    h1 { font-size: 16px; margin: 0 0 4px; color: #e8b84a; }
    .tip { color: #7f858e; font-size: 12px; margin-bottom: 10px; line-height: 1.7; }
    .flash { padding: 8px 10px; border-radius: 6px; margin-bottom: 10px; font-size: 13px; }
    .flash.ok { background: #12351f; border: 1px solid #2f7d4a; color: #8ef0aa; }
    .flash.err { background: #3a1414; border: 1px solid #8a2b2b; color: #ff9a9a; }
    .panel { position: sticky; top: 8px; z-index: 5; background: #161a22; border: 1px solid #2a3140; border-radius: 8px; padding: 10px; margin-bottom: 12px; }
    .panel .sel { margin-bottom: 8px; }
    .panel .sel b { color: #e8b84a; }
    .panel form { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .panel .st { color: #7fb0ff; font-size: 12px; }
    input[type=file] { color: #c9ccd1; }
    button { background: #2a6df0; color: #fff; border: 0; padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; }
    button:hover { background: #3f80ff; }
    button:disabled { background: #2a3140; color: #6b7280; cursor: not-allowed; }
    .cols { display: flex; gap: 12px; align-items: flex-start; flex-wrap: wrap; }
    .col { flex: 1 1 340px; min-width: 0; background: #12161d; border: 1px solid #232a36; border-radius: 8px; padding: 8px; }
    .col h2 { font-size: 14px; margin: 2px 0 8px; color: #7fb0ff; }
    .search { width: 100%; padding: 6px 8px; margin-bottom: 8px; background: #0c0f14; border: 1px solid #2a3140; border-radius: 6px; color: #c9ccd1; }
    .list { max-height: 72vh; overflow: auto; }
    .group { color: #8a93a0; font-size: 12px; margin: 8px 4px 4px; border-bottom: 1px dashed #2a3140; padding-bottom: 3px; }
    .row { display: flex; gap: 8px; align-items: center; padding: 4px 6px; border-radius: 6px; cursor: pointer; border: 1px solid transparent; }
    .row:hover { background: #1a2029; }
    .row.active { background: #1f2a3d; border-color: #2a6df0; }
    .thumb { flex: 0 0 auto; line-height: 0; display: inline-block; }
    .row .meta { min-width: 0; }
    .row .nm { color: #d9dde3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .row .id { color: #6b7280; font-size: 11px; }
    .badges { display:flex; gap:3px; flex-wrap:wrap; margin-top:3px; }
    .badge { display:inline-block; padding:1px 4px; border-radius:4px; font-size:10px; line-height:1.4; white-space:nowrap; }
    .badge.boss { color:#ffb3b3; background:#4a2025; border:1px solid #873943; }
    .badge.dragon { color:#ffd779; background:#493b1d; border:1px solid #8a702f; }
    .badge.reg { color:#a9d2ff; background:#1d344d; border:1px solid #365c80; }
    .badge.dgn { color:#d7b4ff; background:#35224a; border:1px solid #624084; }
    .badge.none { color:#8a93a0; background:#20242b; border:1px solid #343a44; }
    .badge.lv { color:#b8e7a3; background:#203a27; border:1px solid #376847; }
    .legend { color:#8a93a0; font-size:11px; margin:-3px 0 6px; line-height:1.6; }
    .tools { display:flex; gap:6px; margin-bottom:6px; }
    button.mini { background:#232a36; color:#c9ccd1; border:1px solid #2f3846; padding:3px 8px; font-size:11px; border-radius:5px; cursor:pointer; }
    button.mini:hover { background:#2b3442; }
    .list details.grp { margin:5px 0; border:1px solid #232a36; border-radius:6px; overflow:hidden; }
    .list details.grp > summary { cursor:pointer; padding:5px 8px; background:#161b23; color:#aeb6c2; font-size:12px; list-style:none; user-select:none; }
    .list details.grp > summary::-webkit-details-marker { display:none; }
    .list details.grp > summary::before { content:"▸ "; color:#6b7280; }
    .list details.grp[open] > summary::before { content:"▾ "; }
    .list details.grp .cnt { color:#6b7280; }
    .list details.grp > .row { padding-left:10px; }
    @media (max-width: 720px) { .cols { flex-direction: column; } }
</style>
</head>
<body>
<h1>立绘替换工具</h1>
<div class="tip">
    左栏：怪物（含六圣柱龙王）→ <code>img/monsters/</code>；右栏：角色/职业头像 → <code>img/avatars/</code>。<br>
    操作：点选条目 → 「选择图片」（弹出 Windows 文件管理器）→ 「上传替换」。<br>
    <b style="color:#8ef0aa">上传前会在浏览器里自动压缩成 PNG</b>：怪物→长边≤256 保持比例；角色头像/宠物头像→居中裁方并同时存两张：列表小图(≤128) + 点开大图(≤640)。不拉伸、保留透明、小图不放大，保存后刷新游戏页面即生效。<br>
    宠物立绘落盘为 <code>img/pets/<宠物id>.png</code>，仅在【宠物详情】画面显示（与人物头像同尺寸 64px）。
</div>

<?php if ($okFlag): ?>
    <div class="flash ok">✔ 已替换成功！刷新游戏页面即可看到新立绘。</div>
<?php elseif ($errMsg !== ''): ?>
    <div class="flash err">✘ <?= h($errMsg) ?></div>
<?php endif; ?>

<div class="panel">
    <div class="sel">当前选择：<b id="selLabel">（还没选，先点下面任意一个条目）</b></div>
    <form method="post" enctype="multipart/form-data" id="upForm">
        <input type="hidden" name="type" id="selType" value="<?= h($selType) ?>">
        <input type="hidden" name="id" id="selId" value="<?= h($selId) ?>">
        <input type="file" name="img" id="fileInput" accept="image/*" required>
        <button type="submit" id="upBtn" disabled>上传替换</button>
        <span class="st" id="status"></span>
    </form>
</div>

<div class="cols">
    <div class="col">
        <h2>怪物（<?= count($mons) ?>）＋ 六龙王（<?= count($dragonIds) ?>）</h2>
        <div class="legend"><span class="badge boss">BOSS</span> 首领 · <span class="badge dragon">世界BOSS</span> 六龙王 · <span class="badge reg">区域</span> 野外区域 · <span class="badge dgn">副本</span> 副本地图</div>
        <div class="tools">
            <button type="button" class="mini" data-grp="open">展开全部</button>
            <button type="button" class="mini" data-grp="close">折叠全部</button>
        </div>
        <input class="search" placeholder="搜索名字、id、区域或副本…" data-target="monList">
        <div class="list" id="monList">
            <?php
            // 按“大区域 / 大副本”折叠分组（不再按每张小图分组）
            $bigGroupOf = function (string $mid) use ($monRegions, $monOrphans, $monDungeons, $dragonMonSet): string {
                $regs = array_keys($monRegions[$mid] ?? []);
                if ($regs !== []) {
                    return (string) $regs[0];
                }
                $orps = array_keys($monOrphans[$mid] ?? []);
                if ($orps !== []) {
                    return (string) $orps[0];
                }
                $dgns = array_keys($monDungeons[$mid] ?? []);
                if ($dgns !== []) {
                    return '副本·' . (string) $dgns[0];
                }
                if (isset($dragonMonSet[$mid])) {
                    return '六圣柱';
                }
                return '未使用（无地图）';
            };
            $byGroup = [];
            foreach ($mons as $mid => $mv) {
                if (isset($dragonSet[(string) $mid])) {
                    continue; // 六龙王单独一组，避免重复
                }
                $byGroup[$bigGroupOf((string) $mid)][] = (string) $mid;
            }
            $groupLevel = function (array $mids) use ($monsterLevel): int {
                $levels = array_map($monsterLevel, $mids);
                return $levels === [] ? PHP_INT_MAX : min($levels);
            };
            foreach ($byGroup as $__g => $__mids) {
                usort($__mids, function ($a, $b) use ($monsterLevel, $mons): int {
                    $la = $monsterLevel($a);
                    $lb = $monsterLevel($b);
                    return $la === $lb ? strcmp((string) ($mons[$a]['name'] ?? $a), (string) ($mons[$b]['name'] ?? $b)) : ($la <=> $lb);
                });
                $byGroup[$__g] = $__mids;
            }
            uksort($byGroup, function ($a, $b) use ($byGroup, $groupLevel): int {
                $ua = ($a === '未使用（无地图）') ? 1 : 0;
                $ub = ($b === '未使用（无地图）') ? 1 : 0;
                if ($ua !== $ub) {
                    return $ua <=> $ub;
                }
                $la = $groupLevel($byGroup[$a]);
                $lb = $groupLevel($byGroup[$b]);
                return $la === $lb ? strcmp((string) $a, (string) $b) : ($la <=> $lb);
            });
            $grpOpen = function (string $label, array $mids) use ($mons, $monBadges, $monsterLevel): void {
                if ($mids === []) {
                    return;
                }
                $levels = array_map($monsterLevel, $mids);
                $minLv = min($levels);
                $maxLv = max($levels);
                $lvText = $minLv === $maxLv ? 'Lv.' . $minLv : 'Lv.' . $minLv . '–' . $maxLv;
                echo '<details class="grp"><summary>' . h($label) . ' <span class="cnt">(' . count($mids) . ' · ' . h($lvText) . ')</span></summary>';
                foreach ($mids as $mid) {
                    $mv = $mons[$mid] ?? [];
                    $nm = (string) ($mv['name'] ?? $mid);
                    rp_row('monster', $mid, $nm, monster_image_url($mid, $nm), '', $monBadges($mid));
                }
                echo '</details>';
            };
            foreach ($byGroup as $g => $mids) {
                $label = (str_starts_with((string) $g, '副本·') || $g === '未使用（无地图）') ? (string) $g : ('区域·' . $g);
                $grpOpen($label, (array) $mids);
            }
            echo '<details class="grp"><summary>六圣柱·六龙王（世界BOSS） <span class="cnt">(' . count($dragonIds) . ')</span></summary>';
            $__dragons = dragons();
            $__dmIds = array_keys($__dragons);
            usort($__dmIds, function ($a, $b) use ($monsterLevel): int {
                $la = $monsterLevel((string) $a);
                $lb = $monsterLevel((string) $b);
                return $la === $lb ? strcmp((string) $a, (string) $b) : ($la <=> $lb);
            });
            foreach ($__dmIds as $dmId) {
                $dd = $__dragons[$dmId] ?? [];
                $nm = (string) ($dd['name'] ?? $dmId);
                rp_row('monster', (string) $dmId, $nm, monster_image_url((string) $dmId, $nm), '', $monBadges((string) $dmId));
            }
            echo '</details>';
            ?>
        </div>
    </div>
    <div class="col">
        <h2>角色 / 职业头像（<?= count($avs) ?>）</h2>
        <input class="search" placeholder="搜索名字或 id…" data-target="avList">
        <div class="list" id="avList">
            <?php
            foreach ($avs as $aid => $av) {
                $nm = (string) ($av['name'] ?? $aid);
                rp_row('avatar', (string) $aid, $nm, avatar_image_url((string) $aid), avatar_render((string) $aid, 30));
            }
            ?>
        </div>
    </div>
    <div class="col">
        <h2>宠物头像（<?= count($pets) ?>）</h2>
        <div class="legend"><span class="badge common">普通</span> <span class="badge good">优秀</span> <span class="badge rare">稀有</span> <span class="badge epic">史诗</span> <span class="badge legend">传说</span></div>
        <input class="search" placeholder="搜索名字或 id…" data-target="petList">
        <div class="list" id="petList">
            <?php
            $petQualityColors = ['common' => '#aeb6c2', 'good' => '#bfffcf', 'rare' => '#cfe8ff', 'epic' => '#e6c9ff', 'legend' => '#ffe6a0'];
            uksort($pets, function ($a, $b) use ($pets): int {
                $qa = array_search($pets[$a]['quality'] ?? 'common', ['common', 'good', 'rare', 'epic', 'legend'], true) ?? 0;
                $qb = array_search($pets[$b]['quality'] ?? 'common', ['common', 'good', 'rare', 'epic', 'legend'], true) ?? 0;
                return $qb <=> $qa ?: strcmp((string) ($pets[$a]['name'] ?? $a), (string) ($pets[$b]['name'] ?? $b));
            });
            foreach ($pets as $pid => $pc) {
                $nm = (string) ($pc['name'] ?? $pid);
                $q  = (string) ($pc['quality'] ?? 'common');
                $col = $petQualityColors[$q] ?? $petQualityColors['common'];
                $badge = '<span class="badge" style="background:#161b23;border:1px solid ' . h($col) . ';color:' . h($col) . '">' . h(pet_quality_name($q)) . '</span>';
                rp_row('pet', (string) $pid, $nm, pet_image_url((string) $pid, $nm), '', $badge);
            }
            ?>
        </div>
    </div>
</div>

<script>
(function () {
    // 怪物：单图，长边≤256、保持原始比例（首页大量缩略图，太大影响加载）。
    // 角色头像：存两张——列表用小图(≤128) + 点开看的大图(≤640)，大图按需才加载，兼顾清晰与速度。
    var MAX_SIDE_MONSTER = 256;
    var MONSTER_HD_SIDE = 1080;  // 实时战斗立绘用高清（列表仍用小图）
    var AVATAR_SMALL_SIDE = 128;
    var AVATAR_BIG_SIDE = 640;
    var AVATAR_HD_SIDE = 1080;   // 仅状态页使用的高清大图

    var selType = document.getElementById('selType');
    var selId = document.getElementById('selId');
    var selLabel = document.getElementById('selLabel');
    var upBtn = document.getElementById('upBtn');
    var fileInput = document.getElementById('fileInput');
    var statusEl = document.getElementById('status');
    var activeRow = null;
    var pendingFile = null;

    function activate(row) {
        document.querySelectorAll('.row.active').forEach(function (r) { r.classList.remove('active'); });
        row.classList.add('active');
        activeRow = row;
        selType.value = row.getAttribute('data-type');
        selId.value = row.getAttribute('data-id');
        selLabel.textContent = row.getAttribute('data-label') + '（' + row.getAttribute('data-id') + '）';
        upBtn.disabled = !(pendingFile && activeRow);
    }
    document.querySelectorAll('.row').forEach(function (row) {
        row.addEventListener('click', function () { activate(row); });
    });

    var preType = selType.value, preId = selId.value;
    if (preType && preId) {
        var found = document.querySelector('.row[data-type="' + preType + '"][data-id="' + preId.replace(/"/g, '\\"') + '"]');
        if (found) { activate(found); found.scrollIntoView({block: 'center'}); }
    }

    // 读文件 → 缩放（头像居中裁方）→ PNG Blob
    function compress(file, maxSide, square) {
        return new Promise(function (resolve, reject) {
            var fr = new FileReader();
            fr.onerror = function () { reject(new Error('读取文件失败')); };
            fr.onload = function () {
                var img = new Image();
                img.onerror = function () { reject(new Error('图片解码失败')); };
                img.onload = function () {
                    var w = img.naturalWidth || img.width, h = img.naturalHeight || img.height;
                    var sx = 0, sy = 0, sw = w, sh = h;
                    if (square) {
                        // 居中裁成正方形，避免头像显示时被拉伸/偏移
                        var side = Math.min(w, h);
                        sx = (w - side) / 2; sy = (h - side) / 2; sw = side; sh = side;
                    }
                    var s = Math.min(1, maxSide / Math.max(sw, sh));
                    var nw = Math.max(1, Math.round(sw * s)), nh = Math.max(1, Math.round(sh * s));
                    var cv = document.createElement('canvas');
                    cv.width = nw; cv.height = nh;
                    var ctx = cv.getContext('2d');
                    // PNG 保留透明通道：不铺底色，直接绘制
                    ctx.drawImage(img, sx, sy, sw, sh, 0, 0, nw, nh);
                    var done = function (blob) {
                        if (!blob) { reject(new Error('压缩失败')); return; }
                        resolve({ blob: blob, ow: w, oh: h, nw: nw, nh: nh });
                    };
                    if (cv.toBlob) { cv.toBlob(done, 'image/png'); }
                    else { reject(new Error('浏览器不支持压缩')); }
                };
                img.src = fr.result;
            };
            fr.readAsDataURL(file);
        });
    }

    fileInput.addEventListener('change', function () {
        pendingFile = null;
        var file = fileInput.files && fileInput.files[0];
        if (!file) { statusEl.textContent = ''; upBtn.disabled = true; return; }
        pendingFile = file;
        statusEl.textContent = '已选择：' + file.name + '（选好条目后点“上传替换”，会在浏览器里自动压缩）';
        upBtn.disabled = !activeRow;
    });

    document.getElementById('upForm').addEventListener('submit', function (ev) {
        if (!pendingFile || !activeRow) { return; } // 没选文件/条目 → 走原生提交兜底
        ev.preventDefault();
        upBtn.disabled = true;
        var type = selType.value;
        var square = (type === 'avatar' || type === 'pet');
        var maxSide = square ? AVATAR_SMALL_SIDE : MAX_SIDE_MONSTER;
        statusEl.textContent = '正在压缩…';
        compress(pendingFile, maxSide, square).then(function (r) {
            statusEl.textContent = '原图 ' + r.ow + '×' + r.oh + ' → ' + r.nw + '×' + r.nh + ' PNG（' + Math.round(r.blob.size / 1024) + 'KB），上传中…';
            var fd = new FormData();
            fd.append('type', type);
            fd.append('id', selId.value);
            fd.append('img', r.blob, 'art.png');
            if (!square) {
                // 怪物：生成 ≤1080 高清立绘（实时战斗用），列表仍用小图
                return compress(pendingFile, MONSTER_HD_SIDE, false).then(function (hd) {
                    if (hd && hd.blob) { fd.append('hd', hd.blob, 'art.hd.png'); }
                    return fetch(location.pathname, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' });
                });
            }
            // 头像：生成 ≤640 灯箱大图 + ≤1080 状态页高清图一起上传
            return compress(pendingFile, AVATAR_BIG_SIDE, true).then(function (big) {
                if (big && big.blob) { fd.append('big', big.blob, 'art.big.png'); }
                return compress(pendingFile, AVATAR_HD_SIDE, true);
            }).then(function (hd) {
                if (hd && hd.blob) { fd.append('hd', hd.blob, 'art.hd.png'); }
                return fetch(location.pathname, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' });
            });
        }).then(function (resp) { return resp.json(); })
          .then(function (d) {
            if (!d.ok) { statusEl.textContent = '失败：' + (d.err || '未知错误'); upBtn.disabled = false; return; }
            // 原地刷新该行缩略图
            if (activeRow && d.thumb) {
                var span = activeRow.querySelector('.thumb');
                if (span) span.innerHTML = '<img src="' + d.thumb + '?t=' + d.t + '" alt="" style="height:40px;width:40px;object-fit:cover;border-radius:6px;border:1px solid #333;background:#000;display:block">';
            }
            statusEl.textContent = '✔ 已替换';
            pendingFile = null;
            fileInput.value = '';
            upBtn.disabled = true;
        }).catch(function (e) { statusEl.textContent = '上传/压缩出错：' + (e && e.message ? e.message : ''); upBtn.disabled = false; });
    });

    // 搜索过滤（行 + 折叠分组自动展开/隐藏）
    document.querySelectorAll('.search').forEach(function (inp) {
        inp.addEventListener('input', function () {
            var kw = inp.value.trim().toLowerCase();
            var list = document.getElementById(inp.getAttribute('data-target'));
            if (!list) { return; }
            list.querySelectorAll('.row').forEach(function (row) {
                var b = row.querySelector('.badges');
                var t = (row.getAttribute('data-label') + ' ' + row.getAttribute('data-id') + ' ' + (b ? b.textContent : '')).toLowerCase();
                row.style.display = (kw === '' || t.indexOf(kw) >= 0) ? '' : 'none';
            });
            list.querySelectorAll('details.grp').forEach(function (det) {
                if (kw === '') { det.style.display = ''; return; }
                var any = false;
                det.querySelectorAll('.row').forEach(function (r) { if (r.style.display !== 'none') { any = true; } });
                det.style.display = any ? '' : 'none';
                if (any) { det.open = true; }
            });
            list.querySelectorAll('.group').forEach(function (g) { g.style.display = kw === '' ? '' : 'none'; });
        });
    });
    // 展开 / 折叠全部
    document.querySelectorAll('button.mini[data-grp]').forEach(function (b) {
        b.addEventListener('click', function () {
            var open = b.getAttribute('data-grp') === 'open';
            document.querySelectorAll('#monList details.grp').forEach(function (d) { d.open = open; });
        });
    });
})();
</script>
</body>
</html>
