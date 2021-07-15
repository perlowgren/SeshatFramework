<?php

use Seshat\MaatMark;

$tm = time();
if(isset($_COOKIE['news'])) $_COOKIE['news'] = "{$tm}";
setcookie('news',"{$tm}",$tm+315360000,'/');

?>
{* include theme/{*~THEME}/page-header.php }
<article>
<?php

if(Ra::hasPermission(AUTH_ADMIN)) {
	$nid = 0;
	$lang = Ra::getLanguage();
	$subject = '';
	$text = '';
	$html = '';

	if(isset($_GET['edit-news-id'])) {
		$nid = $_POST['edit-news-id'];
		list($lang,$subject,$text) = Ra::row("SELECT lang,subject,text FROM news WHERE nid=?",array($nid));
	}
?>
	<form id="news-form" action="news" method="post">
		<input type="hidden" id="news-id" name="nid" value="<?= $nid ?>" />
		<input type="hidden" id="news-lang" name="lang" value="<?= $lang ?>" />

		<label>{*~subject}</label>
		<input type="text" name="subject" value="<?= htmlspecialchars($subject) ?>" placeholder="{*~placeholder-subject}" />

		<label>{*~message}</label>
		<textarea id="news-text" name="text"><?= htmlspecialchars($text) ?></textarea>

		<nav><!--
			--><a id="preview" class="left">{*~preview}</a><!--
			--><input type="submit" id="publish" class="right" value="{*~publish}" /><!--
		--></nav>
	</form>
<?php

	if(isset($_POST['nid'])) $nid = $_POST['nid'];
	if(isset($_POST['subject']) && isset($_POST['text'])) {
		$lang = $_POST['lang'];
		$subject = $_POST['subject'];
		$text = $_POST['text'];
		$maat = new MaatMark();
		$html = $maat->parse($text);
		if($nid) Ra::exec('UPDATE news SET subject=?,html=?,text=?,changed=? WHERE nid=?',
		              array($subject,$html,$text,$tm,$nid));
		else Ra::exec("INSERT INTO news (nid,uid,lang,subject,html,text,created,changed) VALUES (NULL,?,?,?,?,?,?,?)",
		              array(Ra::getUserID(),$lang,$subject,$html,$text,$tm,$tm));
	}
?>

	<hr />
<?php
}

$where = '1';
$result = Ra::query("SELECT u.user,n.created,n.subject,n.html FROM news AS n LEFT JOIN user AS u ON n.uid=u.uid WHERE {$where} ORDER BY n.created DESC");

for($i=0; $row=$result->fetchArray(SQLITE3_ASSOC); ++$i) {
	$user = $row['user'];
	$published = date('Y-m-d H:i',intval($row['created']));
	$subject = $row['subject'];
	$news = $row['html'];
?>
<dl>
	<dt><a href="/page/Member:<?= $user ?>"><?= $user ?></a>: <?= $subject ?> - <?= $published ?></dt>
	<dd><?= $news ?></dd>
</dl>
<?php
}

?>
</article>

{* include theme/{*~THEME}/page-footer.php }
