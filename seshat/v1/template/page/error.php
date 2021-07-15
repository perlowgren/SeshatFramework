<?php

/* $code and $error should be set in $GLOBALS or in function calling script */

$error_messages = array(
{* grep /(\d+)-message/|\t{*~1}=>'{*~ {*~0}|string }',\n }
);

$title = $error;
$message = isset($error_messages[$code])? $error_messages[$code] : $error;

?>
{* set title|{*~title} - <?= $title ?> }
{* include theme/{*~THEME}/page-header.php }
<article>
	<h1><?= $title ?></h1>

	<p><?= $message ?></p>
<!--
DOCUMENT_ROOT: <?= $_SERVER['DOCUMENT_ROOT'] ?>

REDIRECT_URL: <?= $_SERVER['REDIRECT_URL'] ?>
-->
</article>
{* include theme/{*~THEME}/page-footer.php }
