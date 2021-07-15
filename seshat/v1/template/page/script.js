
(function($) {

	$.setup({
		'lang': '{*~LANGUAGE}',
		'ajax-url': '{*~url-ajax}',
		'fb-app-id': '{*~fb-app-id}'
	});

	$.extend({
/*
		statLabels: [
			['mail','{*~stat-mail|string}','{*~stat-mails|string}'],
			['log','{*~stat-log-post|string}','{*~stat-log-posts|string}'],
			['forum','{*~stat-forum-reply|string}','{*~stat-forum-replies|string}']
		],
		statNumbers: ['{*~stat-numbers|','|join}'],
*/
		showLogin: function() {
			$.include('seshat-login-js','/seshat-login-1.1.js');
			$.dialog({
				id:'login-box',
				center: true,
				draggable: true,
				head: {
					title:'{*~login-title}',
					closeButton: { class:'button dialog-close-button' }
				},
				body: [
					{
						type:'form',
						id:'login-form',
						submit:$.loginUser,
						fields: [
							{ type:'div',id:'login-message' },
							{ type:'label',text:'{*~login-user}' },
							{ type:'text',name:'user',id:'login-user',focus:true },
							{ type:'label',text:'{*~login-password}' },
							{ type:'password',name:'password',id:'login-pass' },
							{ type:'div',id:'fb-login-button',class:'fb-connect fb-wait',fields: [
								{ type: 'i' },{ type:'span',text:'{*~fb-login-button}' }
							] },
							{ type:'ok',id:'login-ok',class:'button dialog-button',text:'{*~login-ok-button}' }
						]
					}
				]
			});
		},

		onFacebookLoaded: function() {
			$('#fb-login-button').style('class','fb-connect').facebookLoginButton();
		},

		updateQR: function(id,text,label,href) {
			var e = $(id);
			e.html('');
$.log('qrcode['+e.id()+']: '+text);
			if(e.qrcode)
				e.qrcode(label? {
					level: 'H',
					version: 8,
					size: 5,
					background: '#fff',
					text: text,
					label: {
						mode: 'box',
						padding: { x: 5,top: 2,bottom: 1 },
						text: label,
						font: { name: 'Serif',size: 24,color: '#f47321' },
						border: { width: 2,color: '#f47321' }
					},
					href: href
				} : {
					level: 'H',
					version: 8,
					size: 5,
					text: text,
					background: '#fff',
					href: href
				});
		},

		loginUser: function() {
			var user = $('#login-user').value(),pass = $('#login-pass').value();
			$.updateSession(JSON.stringify({action:'login',user:user,pass:pass}),function(data) {
$.log('loginUser('+data+')');
				if(data=='OK') window.location.reload(true);
				else $('#login-message').openMessageBox('login-message',data,{class:'fail'},5000);
			});
		},
		logoutUser: function() {
			$.updateSession('{"action":"logout"}',$.reloadOnOK);
		},
		setLanguage: function(lang) {
			$.updateSession('{"lang":"'+lang+'"}',$.reloadOnOK);
		},

		reloadOnOK: function(data) {
			if(data=='OK') window.location.reload(true);
		},

		updateSession: function(json,cb) {
			$.ajax({
				url: 'session',
				method: 'POST',
				data: json,
				success: function(data) {
					$.log(data);
					if(cb) cb(data);
				},
				fail: function(status,message) {
					$.log(message);
				}
			});
		}
	});

	$(function() {
		$.cookiesMessage('cookies-message','{*~cookies-message|inline|markup|string}','{*~cookies-message-ok|string}');
	});

})($seshat);

