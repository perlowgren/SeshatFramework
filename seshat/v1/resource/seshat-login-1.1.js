/*
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 */

/**
 * @file Extension for handling logging in by various social networks (at
 * the time of writing only Facebook is implemented).
 * 
 * This script is part of the Seshat framework.
 * 
 * @author Per Löwgren
 * @file resource/seshat-login-1.1.js  
 * @version 1.1
 * @date Modified: 2016-01-17
 * @date Created: 2014-07-14
 * @copyright Per Löwgren 2016
 */

(function($) {

	function _FB_login() {
		$.ajax({
			url: 'login-fb',
			accept: 'json',
			success: function(data) {
$.log('Seshat: Logged in to UOMS ['+data.code+','+data.message+'].');
				if(data.message=='OK') window.location.reload(true);
			},
			fail: function(status,data) {
$.log('Seshat: Could not login (status: '+status+', error: '+data.error+').');
			}
		});
	}

	function _FB_connect() {
		$.login({
			media: 'facebook',
			action: 'status',
			done: function(response,ok) {
				if(!ok) {
					$.login({
						media: 'facebook',
						action: 'login',
						done: function(response,ok) {
							if(!ok) console.log('Seshat: You failed logging in.');
							else _FB_login();
						}
					});
				} else _FB_login();
			}
		});
	}

	$.extend({
		login: function(opts) {
			var fb_app_id = $.setup('fb-app-id');
			if(opts.media=='facebook' && !!fb_app_id) {
				if(!FB) {
					$.log('Seshat[$.login]: Facebook SDK not yet loaded.');
				} else if(opts.action=='login') {
					FB.login(function(response) {
						if(opts.done)
							opts.done(response,!!response.authResponse);
					},{scope:'email'});
				} else if(opts.action=='logout') {
					FB.logout(function(response) {
						if(opts.done)
							opts.done(response,true);
					});
				} else if(opts.action=='status') {
					FB.getLoginStatus(function(response) {
						if(opts.done)
							opts.done(response,response.status==='connected');
					});
				} else if(opts.action=='user-data') {
					FB.api('/me',function(response) {
						if(opts.done)
							opts.done(response,true);
					});
				}
			}
		},
	});

	$.implement({
		facebookLoginButton: function() {
			this.click(_FB_connect);
		}
	});

	$(function() {
		var fb_app_id = $.setup('fb-app-id');
		if(!!fb_app_id) {
$.log('FB App ID: '+fb_app_id);
			if(!document.getElementById('fb-root')) {
				var div = document.createElement('div');
				div.id = 'fb-root';
$.log('div: '+div.id);
				document.body.insertBefore(div,document.body.firstChild);
			}
			window.fbAsyncInit = function() {
				FB.init({
					appId: fb_app_id,
					cookie: true,
					xfbml: true,
					version: 'v2.2'
				});
				FB.getLoginStatus(function(response) {
					if(response.status==='connected') {
						$.log('Seshat: Logged in with Facebook.');
						if($.onlogin) $.onlogin('facebook');
					} else if(response.status==='not_authorized') {
						$.log('Seshat: Not logged in with Facebook.');
					} else {
						$.log('Seshat: Not logged in to Facebook.');
					}
					if($.onFacebookLoaded) $.onFacebookLoaded();
				});
			};
			if(!document.getElementById('facebook-jssdk'))
				$.include('facebook-jssdk','https://connect.facebook.net/en_US/sdk.js');
		}
	});

})($seshat);


