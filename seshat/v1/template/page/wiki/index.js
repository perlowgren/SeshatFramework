{*/*
Seshat Wiki javascript 
*/}<?php

if($wiki->hasWritePermission() && $wiki->getAction()=='edit') {
?>

(function($) {

	function getWikiURL(action,page,pid) {
		if(!pid) pid = $.wikiPID;
		if(!page) page = $.wikiPage;
		return '{*~url-wiki}'+(action && pid>=1? action+'/'+pid+'/' : '')+page;
	}

	function updatePageInfo() {
		var url = getWikiURL();
		$('#wiki-edit-page-info').value('{*~lang}: '+$.wikiLang+', ID: '+$.wikiPID+', {*~url}: <a href="'+url+'">\''+url+'\'</a>');
	}

	function showPage() {
		location.href = getWikiURL();
	}

	function previewPage() {
		$.ajax({
			url: 'wiki-preview',
			method: 'POST',
			data: $('text').value(),
			success: function(data) {
				$.wikiOpenPreview(data);
			},
			fail: function(status,message) {
				$.log(message);
				$.wikiMessageBox(message,'fail',3000);
				$.wikiClosePreview();
			}
		});
	}
/*
	function loadFile() {
		var name = $('name').value();
		$.ajax({
			url: 'wiki-load-file?name='+encodeURIComponent(name),
			method: 'GET',
			success: function(data) {
				$('text').value(data);
				$.wikiMessageBox('{*~ok}','success',3000);
			},
			fail: function(status,message) {
				$.log(message);
				$.wikiMessageBox(message,'fail',3000);
			}
		});
	}
*/
	function loadText() {
		$.ajax({
			url: 'wiki-load-text?pid='+$.wikiPID+'&action=edit',
			method: 'GET',
			success: function(data) {
				$.wikiText = data;
				$('text').value(data);
				$.wikiMessageBox('{*~ok}','success',3000);
			},
			fail: function(status,message) {
				$.log(message);
				$.wikiMessageBox(message,'fail',3000);
			}
		});
	}

	function loadPage() {
		var name = $('name').value();
		$.ajax({
			url: 'wiki-page?name='+encodeURIComponent(name)+'&lang='+$.wikiLang+'&action=edit',
			method: 'GET',
			accept: 'json',
			success: function(data) {
				var pid = $.wikiPID;
				$.wikiPID = parseInt(data.pid);
				$.wikiPage = data.page;
				$.wikiName = data.name;
				updatePageInfo();
				if($.wikiPID==0) $.wikiMessageBox('{*~text-lang-missing}','info',7000);
				else if($.wikiPID!=pid) loadText();
			},
			fail: function(status,data) {
				$.log(data.error);
				$.wikiMessageBox(data.error,'fail',3000);
			}
		});
	}

	function savePage() {
		if($.wikiFile && $.wikiFile.data) uploadFile();
		else {
			var name = $('name').value();
			$.ajax({
				url: 'wiki-save?pid='+$.wikiPID+'&name='+encodeURIComponent(name)+'&lang='+$.wikiLang,
				method: 'POST',
				accept: 'json',
				data: $('text').value(),
				success: function(data) {
					if(data.pid!==undefined) $.wikiPID = parseInt(data.pid);
					if(data.name!==undefined) {
						$('name').value(data.name);
						$('#wiki-page-name').value(data.name);
					}
					if(data.page!==undefined) $.wikiPage = data.page;
					if(data.lang!==undefined) $.wikiLang = data.lang;
					updatePageInfo();
					$.wikiMessageBox(data.message,'success',3000);
				},
				fail: function(status,data) {
					$.log(data.error);
					$.wikiMessageBox(data.error,'fail',3000);
				}
			});
		}
	}

	function uploadFile() {
		var name = $('name').value();
		$.ajax({
			url: 'wiki-upload?name='+encodeURIComponent(name),
			method: 'POST',
			accept: 'json',
			data: $.wikiFile.data,
			success: function(data) {
				if(data.message!='OK')
					$.wikiMessageBox(data.message,'fail',3000);
				else {
					$('#wiki-edit-file').hide();
					$.wikiFile.data = null;
					savePage();
				}
			},
			fail: function(status,data) {
				$.wikiMessageBox(data.error,'fail',3000);
			}
		});
	}

	function setPageLanguage(lang) {
		$('#wiki-edit-lang-top').html('{*~lang}: '+lang);
		if(lang!=$.wikiLang) {
			$.wikiLang = lang;
			loadPage();
		}
	}

	function showFileInfo() {
		var file = $.wikiFile;
		var info = '{*~file-info}: '+(file.mime? '('+file.mime+') - ' : '')+$.toBytes(file.size);
		if(file.width && file.height) info += ' - '+file.width+'x'+file.height+'px';
		$('#wiki-edit-file-info').html(info).show();
	}

	$.extend({
		wikiPID: parseInt('<?= $wiki->getPageID() ?>'),
		wikiPage: '<?= $wiki->getPageName() ?>',
		wikiLang: '<?= $wiki->getPageLanguage() ?>',
		wikiName: '',
		wikiText: '',
		wikiFile: <?= $file===false? 'null' : json_encode($file) ?>,

		wikiOpenPreview: function(content) {
			$('#wiki-preview-content').html(content);
			$('#wiki-preview').show();
			location.href = '#wiki-preview';
		},

		wikiClosePreview: function() {
			$('#wiki-preview-content').html('');
			$('#wiki-preview').hide();
			return false;
		},

		wikiMessageBox: function(message,type,tm) {
			var cl = 'wiki-message';
			if(type) cl = cl+' '+type;
			$('#wiki-message').openMessageBox('wiki-message-box',message,{class:cl},tm,'top');
		}
	});

	$(function() {
		$('text').expandingTextarea();
		updatePageInfo();
		$('#wiki-edit-show-top').click(showPage);
		$('#wiki-edit-preview-top').click(previewPage);
		$('#wiki-edit-file-top').setPopupMenu('#wiki-edit-file-menu-top');
		$('#wiki-edit-lang-top').setPopupMenu('#wiki-edit-lang-menu-top');
		$('#wiki-edit-help-top').setPopupMenu('#wiki-edit-help-menu-top');
		$('#wiki-edit-save-top').click(savePage);
		$('#wiki-edit-show-bottom').click(showPage);
		$('#wiki-edit-preview-bottom').click(previewPage);
		$('#wiki-edit-save-bottom').click(savePage);
//		$('name').change(function() { $.wikiChanged = true; });
//		$('text').change(function() { $.wikiChanged = true; });
		$('#wiki-preview-close').click($.wikiClosePreview);

		setPageLanguage($.wikiLang);
		$('#wiki-lang-en').click(function() { setPageLanguage('en'); });
		$('#wiki-lang-it').click(function() { setPageLanguage('it'); });
		$('#wiki-lang-sp').click(function() { setPageLanguage('sp'); });
		$('#wiki-lang-sv').click(function() { setPageLanguage('sv'); });
<?php

	if($link instanceof Seshat\MaatMark\Link\FileLink) {
?>

		var image = $('#wiki-edit-file-image');
		var file = $('#wiki-edit-file');

		if(file.length) {
//			file.on('dragover',fileDragOver).on('drop',fileSelect);
			file.dropFileArea(function(file) {
				$.wikiPage = 'File:'+file.name;
				$.wikiFile = file;
				$('#wiki-page-name').html($.wikiPage);
				$('name').value($.wikiPage);
				if(file.mime.indexOf('image')===0) {
					if(image.length)
						image[0].src = file.data;
				} else {
					image.hide();
					showFileInfo();
				}
			});
		}

		image.load(function() {
			image.show();
			if($.wikiFile) {
				$.wikiFile.width = image[0].naturalWidth;
				$.wikiFile.height = image[0].naturalHeight;
				showFileInfo();
			}
		});
		if($.wikiFile && $.wikiFile.url) img.src = $.wikiFile.url;
<?php
	}

?>
	});
})($seshat);

<?php
}

?>