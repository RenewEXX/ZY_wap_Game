<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
// 地图/副本“可正常进入”审计：任何地图都必须有入口（走入 / 副本按钮 / 已知系统入口）
$ok=0;$bad=0;
function ck51($l,$c,$d=''){ global $ok,$bad; if($c){$ok++;}else{$bad++;echo "FAIL $l $d\n";} }

$L=locations();

// ---- 1) 9个副本：入口点存在 + 门↔野外点双向 + 从野外点能走到门 ----
$B=region_dungeon_build(); $roots=['town_sq','square','ice_village','ember_foothill','theater','fo_gate','volcano_heart','avenue','silver_sq','greenheart_sq','god_hall','sky_ferry','sunk_shore'];
foreach(region_dungeon_specs() as $rk=>$R){
  $gate='rd_'.$rk.'_gate'; $from=(string)$R['gate'];
  ck51("[$rk] 野外入口点存在", isset($L[$from]), $from);
  ck51("[$rk] 副本门存在", isset($L[$gate]));
  ck51("[$rk] 野外点→门", isset($L[$from]['exits'][$gate]));
  ck51("[$rk] 门→野外点", isset($L[$gate]['exits'][$from]));
  ck51("[$rk] 门内首图可达", isset($L[$gate]['exits'][region_dungeon_build()['maps'][$rk][1] ?? '']));
}

// ---- 2) 全局：从城镇根 BFS ----
$reach=[]; $q=[];
foreach($roots as $r){ if(isset($L[$r])&&!isset($reach[$r])){ $reach[$r]=true; $q[]=$r; } }
while($q){ $x=array_shift($q); foreach(($L[$x]['exits']??[]) as $y=>$_){ if(isset($L[$y])&&!isset($reach[$y])){ $reach[$y]=true; $q[]=$y; } } }

// 副本内部图靠按钮/NPC 进，排除；剩下“既走不到又不在副本里”的=潜在死图
$inst=array_flip(dungeon_maps());
$orphans=[];
foreach($L as $id=>$v){ if(!isset($reach[$id]) && !isset($inst[$id])) $orphans[]=$id; }
$expect=['peak1','peak2','peak3','farm','wb_arena','wb_wind','wb_ice','wb_thunder','wb_dark','wb_light'];
sort($orphans); sort($expect);
ck51('不可达(非副本)仅剩已知系统入口图', $orphans===$expect, '孤儿='.implode(',', $orphans));

// 这些“系统入口图”必须各有真实入口：peak=peak_enter、farm=任务?go=、wb=wb.php
ck51('peak 走 peak_enter 进', function_exists('peak_enter'));
$src='';
foreach(glob('*.php') as $f){ if(!str_starts_with($f,'dhtest')&&!str_starts_with($f,'zz_')) $src.=file_get_contents($f)."\n"; }
$src.=file_get_contents('includes/game.php')."\n".file_get_contents('includes/dragons.php');
ck51('farm 有任务/传送 ?go= 入口', str_contains($src,"'loc' => 'farm'"));
ck51('龙柱由 wb.php 送入', str_contains($src, '$u[\'loc\'] = (string) $d[\'lair\']'));

// ---- 3) 之前“无入口”的地图，现在必须可达 ----
$wasDead=['forest3','forest4','swamp3','swamp4','hill2','hill3','hill4','arena_gate','frostborn_camp','ash_chapel2'];
foreach($wasDead as $id){
  $nm=$L[$id]['name']??'?';
  ck51("旧死图已可进入: $id ($nm)", isset($reach[$id]));
}

// ---- 4) 铁拳试炼 / 霜裔营地 / 灰烬教堂：能从其父图走通 ----
ck51('中央大道→竞技场试炼门', isset($L['avenue']['exits']['arena_gate']));
ck51('霜语墓园→霜裔营地', isset($L['frost_graveyard']['exits']['frostborn_camp']));
ck51('落叶城废墟→灰烬小教堂', isset($L['fallen_city']['exits']['ash_chapel2']));

echo "\nOK=$ok FAIL=$bad\n";
