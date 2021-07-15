<?php

use Seshat\WadjetWiki;

$wiki = new WadjetWiki();
$wiki->handleAction();
$link = $wiki->getLinkHandler();

$file = false;
if($link instanceof Seshat\MaatMark\Link\FileLink) {
	$st = @stat($link->file_path);
	if($st!==false) {
		$file = array(
			'url'=>$link->file_url,
			'type'=>$link->file_type,
			'mime'=>$link->mime,
			'size'=>$st['size']
		);
	}
}

?>
{* set title|{*~title}: <?= $wiki->getPageName() ?> }
{* include theme/{*~THEME}/page-header.php }
<?php

switch($wiki->getAction()) {
	case 'page':
?>
<article class="wiki">
<?php
		if($wiki->hasWritePermission()) {
?>	<a id="wiki-edit-button" class="button" href="<?= $wiki->getURL('edit') ?>">{*~edit-button}</a>
<?php
		}
?>

<!-- Wiki page: -->
<?= $wiki->getHTML() ?>
<!-- End of Wiki page -->

</article>
<?php
		break;

	case 'edit':
?>
<article>
	<h3 id="wiki-page-name"><?= htmlentities($wiki->getRealName()) ?></h3>

	<form id="wiki-edit-form">
		<nav><!--
			--><a id="wiki-edit-show-top" class="left">{*~show-page}</a><!--
			--><a id="wiki-edit-preview-top" class="left">{*~preview}</a><!--
			--><div class="left"><!--
				--><a id="wiki-edit-file-top">{*~file}</a><!--
				--><ul id="wiki-edit-file-menu-top"><!--
					--><li><a href="/wiki/edit/0/Page:New" target="_blank">{*~new-page}</a></li><!--
					--><li><hr /></li><!--
					--><li><a id="wiki-edit-page-settings">{*~page-settings}</a></li><!--
					--><li><hr /></li><!--
					--><li><a href="/wiki/edit/0/File:New" target="_blank">{*~upload-file}</a></li><!--
					--><li><a id="wiki-edit-select-file">{*~select-file}</a></li><!--
				--></ul><!--
			--></div><!--
			--><div class="left"><!--
				--><a id="wiki-edit-lang-top">{*~lang}</a><!--
				--><ul id="wiki-edit-lang-menu-top"><!--
					--><li><a id="wiki-lang-en">{*~en}</a></li><!--
					--><li><a id="wiki-lang-it">{*~it}</a></li><!--
					--><li><a id="wiki-lang-sp">{*~sp}</a></li><!--
					--><li><a id="wiki-lang-sv">{*~sv}</a></li><!--
				--></ul><!--
			--></div><!--
			--><div class="left"><!--
				--><a id="wiki-edit-help-top">{*~help}</a><!--
				--><ul id="wiki-edit-help-menu-top"><!--
					--><li><a href="/page/Help:Markup" target="_blank">Basic Markup Syntax</a></li><!--
					--><li><a href="/page/Help:Tables" target="_blank">Tables</a></li><!--
					--><li><a href="/page/Help:Lists" target="_blank">Lists</a></li><!--
					--><li><a href="/page/Help:Links" target="_blank">Links</a></li><!--
					--><li><a href="/page/Help:Tags" target="_blank">Tags</a></li><!--
					--><li><a href="/page/Help:Templates" target="_blank">Templates</a></li><!--
					--><li><a href="/page/Help:Symbols and Alphabets" target="_blank">Symbols and Alphabets</a></li><!--
				--></ul><!--
			--></div><!--
			--><a id="wiki-edit-save-top" class="right">{*~save}</a><!--
			--><!--li class="right"><a id="wiki-edit-load-file-top" class="button">{*~load-file}</a></li--><!--
		--></nav>

		<div id="wiki-message"></div>

		<!--div id="wiki-message" class="wiki-message">
			<div id="wiki-message-body">
				<div id="wiki-message-content"></div>
			</div>
		</div-->

		<div id="wiki-edit-name">
			<input type="text" name="name" value="<?= htmlentities($wiki->getRealName()) ?>" />
		</div>
<?php

		if($link instanceof Seshat\MaatMark\Link\FileLink) {
			if($file===false) {
?>

		<div id="wiki-edit-file">{*~drop-file-here}</div>

<?php
			}
?>

		<img id="wiki-edit-file-image" style="display:none;" src="" />
		<div id="wiki-edit-file-info" style="display:none;"></div>

<?php
		}

?>

		<textarea id="wiki-edit-text" name="text"><?= $wiki->getText() ?></textarea>

		<div id="wiki-edit-page-info"></div>

		<nav><!--
			--><a id="wiki-edit-show-bottom" class="left">{*~show-page}</a><!--
			--><a id="wiki-edit-preview-bottom" class="left">{*~preview}</a><!--
			--><a id="wiki-edit-save-bottom" class="right">{*~save}</a><!--
			--><!--a id="wiki-edit-load-file-bottom" class="right">{*~load-file}</a--><!--
		--></nav>
	</form>

	<section id="wiki-preview" class="wiki">
		<div id="wiki-preview-body">
			<a id="wiki-preview-close" class="button">{*~preview-close}</a>
			<div id="wiki-preview-content"></div>
		</div>
	</section>
</article>
<?php
		break;
}

?>
{* include theme/{*~THEME}/page-footer.php }
