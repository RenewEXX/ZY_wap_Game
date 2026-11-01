<?php
define('DH_TEST_MODE', true);
require 'includes/bootstrap.php';
$ok=0;$bad=0;
function ck($l,$c,$d=''){global $ok,$bad; if($c)$ok++; else{$bad++; echo "FAIL $l $d\n";}}
$uid=(int)user_create('fest'.mt_rand(100000,999999),'x','warrior','z1',true,'tmp');
$u=user_by_id($uid);
// normal day (today 11-01) -> no festival
$GLOBALS['__festival_md']='11-01';
ck('非节日无festival', festival_today()===null);
$before=(int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
festival_mail_tick($u);
$after=(int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
ck('非节日不发电件', $after===$before, "before=$before after=$after");
// halloween day
$GLOBALS['__festival_md']='10-31';
$f=festival_today();
ck('万圣节festival识别', is_array($f)&&$f['id']==='halloween');
festival_mail_tick($u);
$after2=(int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
ck('节日发了1封', $after2===$before+1, "before=$before after2=$after2");
$row=db()->query('SELECT title,body,type FROM mails WHERE uid='.$uid.' ORDER BY id DESC LIMIT 1')->fetch();
ck('邮件标题含万圣节', str_contains((string)$row['title'],'万圣节'), (string)$row['title']);
ck('正文含参与方式', str_contains((string)$row['body'],'活动任务'), '');
ck('邮件类型event', (string)$row['type']==='event');
ck('年度标记已设', (int)(cflags($uid)['fest_mail_halloween']??0)===(int)date('Y'));
// dedup same day
festival_mail_tick($u);
$after3=(int)db()->query('SELECT COUNT(*) FROM mails WHERE uid='.$uid)->fetchColumn();
ck('同日不重复发', $after3===$after2, "after2=$after2 after3=$after3");
echo "OK=$ok FAIL=$bad\n";
db()->prepare('DELETE FROM users WHERE id=?')->execute([$uid]);
db()->prepare('DELETE FROM mails WHERE uid=?')->execute([$uid]);
db()->prepare('DELETE FROM mats WHERE uid=?')->execute([$uid]);
