<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$uid=(int)user_create('limiteq'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
$u=user_by_id($uid);
// give a limited title (expires 10 days)
$exp = time() + 10*86400;
give_title($uid, 'title_halloween', 1, $exp);
// verify equip created with expires_at
$eq = db()->query('SELECT * FROM equips WHERE uid='.$uid.' AND slot="title"')->fetch();
ck('限时称号已创建', is_array($eq));
ck('expires_at 已设置', (int)($eq['expires_at']??0) === $exp);
ck('有效期文本', equip_expire_text((int)$eq['expires_at']) !== '永久');
// wear it
db()->prepare('UPDATE equips SET pos="wear" WHERE id=?')->execute([(int)$eq['id']]);
_gear_uncache($uid);
// gear_stats applies bonus (critdmg +3, hp +800)
$gs = gear_stats($uid);
ck('限时称号属性生效(critdmg)', abs((float)$gs['critdmg'] - 3.0) < 0.01, 'critdmg='.(float)$gs['critdmg']);
// non-limited equip: expires_at 0 -> 永久
give_title($uid, 'title_national', 1, 0);
$eq2 = db()->query('SELECT * FROM equips WHERE uid='.$uid.' AND name="称号·金龙献瑞"')->fetch();
ck('永久称号expires_at=0', (int)($eq2['expires_at']??-1) === 0);
ck('永久称号文本=永久', equip_expire_text((int)$eq2['expires_at']) === '永久');
// expire tick: force halloween title expired
db()->prepare('UPDATE equips SET expires_at=? WHERE id=?')->execute([time()-1, (int)$eq['id']]);
$beforeMail = (int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
equip_expire_tick($u);
$afterMail = (int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
ck('过期装备已删除', db()->query('SELECT COUNT(*) FROM equips WHERE id='.(int)$eq['id'])->fetchColumn() == 0);
ck('过期邮件已发', $afterMail === $beforeMail + 1);
$mail = db()->query('SELECT title,body FROM mails WHERE uid='.$uid.' ORDER BY id DESC LIMIT 1')->fetch();
ck('邮件标题含过期', str_contains((string)$mail['title'], '过期'));
ck('邮件正文含装备名', str_contains((string)$mail['body'], '万圣惊魂'));
// gear_stats no longer applies expired bonus
_gear_uncache($uid);
$gs2 = gear_stats($uid);
ck('过期后属性移除', abs((float)$gs2['critdmg']) < 0.01, 'critdmg='.(float)$gs2['critdmg']);
// non-expired tick does nothing new
$beforeMail2 = (int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
equip_expire_tick($u);
$afterMail2 = (int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
ck('无过期时不再发邮件', $afterMail2 === $beforeMail2);
echo "OK=$ok FAIL=$bad\n";
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM equips WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
