<?php

$name = Ra::getUserData('user');
$email = Ra::getUserData('email');

?>
{* include theme/{*~THEME}/page-header.php }
<article>
	<form id="contact-form" action="{*~url-base}contact" method="post">
		<fieldset>
			<label>{*~name}</label>
			<input type="text" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="{*~placeholder-name}" />

			<label>{*~email}</label>
			<input type="text" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="{*~placeholder-email}" />

			<label for="cc"><input type="checkbox" id="cc" name="cc" />{*~cc}</label>
		</fieldset>

		<label>{*~message}</label>
		<textarea name="message"></textarea>
		<p>{*~message-format}</p>

		<div id="contact-message"></div>

		<nav><!--
			--><a id="send-message" class="right">{*~send-message}</a><!--
		--></nav>
	</form>
</article>
{* include theme/{*~THEME}/page-footer.php }
