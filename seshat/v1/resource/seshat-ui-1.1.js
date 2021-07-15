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
 * @file Extension for handling UI-functionality. This script is part
 * of the Seshat framework.
 * 
 * @author Per Löwgren
 * @file resource/seshat-ui-1.1.js  
 * @version 1.1
 * @date Modified: 2016-01-17
 * @date Created: 2014-08-03
 * @copyright Per Löwgren 2016
 */


(function($) {

	function _center(e,f) {
//$.log('_center()');
		var s = e.style,d = s.display;
		if(d=='none' || d===undefined || d===null) s.visibility = 'hidden';
		s.position = f!==true? 'absolute' : 'fixed';
		s.display = 'block';
		s.left = ((window.innerWidth-e.offsetWidth)/2)+'px';
		s.top = ((window.innerHeight-e.offsetHeight)/2)+'px';
//$.log('_center(w.innerWidth: '+window.innerWidth+', w.innerHeight: '+window.innerHeight+', e.offsetWidth: '+e.offsetWidth+', e.offsetHeight: '+e.offsetHeight+', left: '+s.left+', top: '+s.top+')');
		if(s.visibility=='hidden') s.visibility = 'visible';
	}

	function _draggable(e,b,c) {
//$.log('_draggable()');
		var s = b.style,cur = e.style.cursor,omm = document.onmousemove,omu = document.onmouseup;
		if(s.display!='block' || (s.position!='absolute' && s.position!='fixed')) {
//$.log('_draggable(not possible)');
			return;
		}
		if(!(c instanceof Element)) c = document.body;
		e.onmousedown = function(evt) {
			evt = evt || window.event;
			var diffX = evt.clientX-parseInt(s.left),diffY = evt.clientY-parseInt(s.top),x,y,w = b.offsetWidth,h = b.offsetHeight;
$.log('_draggable(mousedown - x: '+evt.clientX+', y: '+evt.clientY+', w: '+w+', h: '+h+')');
			e.style.cursor = 'move';
			document.onmousemove = function(evt) {
				evt = evt || window.event;
				x = evt.clientX-diffX;
$.log('_draggable(onmousemove - left: '+c.offsetLeft+', top: '+c.offsetTop+', right: '+(c.offsetLeft+c.scrollWidth)+', bottom: '+(c.offsetTop+c.scrollHeight)+')');
				if(x<c.offsetLeft) x = c.offsetLeft;
				else if(x+w>c.offsetLeft+c.scrollWidth) x = c.offsetLeft+c.scrollWidth-w;
				y = evt.clientY-diffY;
				if(y<c.offsetTop) y = c.offsetTop;
				else if(y+h>c.offsetTop+c.scrollHeight) y = c.offsetTop+c.scrollHeight-h;
//$.log('_draggable(mousemove - x: '+evt.clientX+', y: '+evt.clientY+')');
				s.left = x+'px';
				s.top = y+'px';
				return false;
			};
			document.onmouseup = function(evt) {
				document.onmousemove = omm;
				document.onmouseup = omu;
				e.style.cursor = cur;
			};
		};
	}

	function _expandingTextarea(d,s,t) {
		t.__$__setValue__ = function() { s.textContent = t.value; };
		if(t.addEventListener)
			t.addEventListener('input',(f=function() { s.textContent = t.value; }),false);
		else if(t.attachEvent)
			t.attachEvent('onpropertychange',(f=function() { s.innerText = t.value; }));
		if(f) {
			t.expand = f;
			t.expand();
			d.className += ' active';
		}
	}

	function _createFields(fields,parent,on) {
		if(parent===undefined) parent = document.body;
		if(on===undefined) on = {};
		for(var i=0,s=on.submit; i<fields.length; ++i) {
			var f = fields[i],t = f.type,e;
$.log('field: '+t+', submit: '+(!!on.submit? 'true' : 'false')+', close: '+(!!on.close? 'true' : 'false'));
			if(t=='form') {
				e = $.create('form',{id:f.id,method:f.method,action:f.action,class:f.class || 'dialog-form'},parent);
				e.onsubmit = function(event){event.preventDefault();return false;};
				on.submit = f.submit;
			} else if(t=='text' || t=='password') {
				e = $.create('input',{type:t,id:f.id,name:f.name,class:f.class,value:f.value},parent);
			} else if(t=='button' || t=='ok' || t=='cancel') {
				e = $.create('input',{type:t=='ok'? 'submit' : 'button',text:f.text,id:f.id,class:f.class || 'dialog-button'},parent);
				if(f.click) $(e).click(f.click);
				else if(t=='ok' && on.submit) $(e).click(on.submit);
				else if(t=='cancel' && !!on.close) $(e).click(on.close);
			} else e = $.create(t,{id:f.id,class:f.class,text:f.text,attr:f.attr},parent);
			if(e) {
				if((t=='text' || t=='password') && !!on.close)
					$(e).captureKeys({'27':on.close});
				if(f.focus===true) on.focus = e;
				if(f.fields) _createFields(f.fields,e,on);
				on.submit = s;
			}
		}
	}

	$.implement({
		/**
		 * @summary Absolutely position selected element(s) in the centre of the window
		 * @memberof $selection.prototype
		 * @param {boolean} f Fixed, if true position is set as 'fixed', else 'absolute'
		 * @return {$selection}
		 */
		center: function(f) {
			if(this.length)
				for(var i=0; i<this.length; ++i)
					_center(this[i],f);
			return this;
		},

		/**
		 * @summary Make selected element(s) draggable
		 * 
		 * @description If 'b' is set, only the first Element in selection is draggable by 'b'. If 'b' is
		 * omitted the entire selection is draggable.
		 * 
		 * @param {Element} b Element within selection that will be draggable area, e.g. title bar in a window
		 * @memberof $selection.prototype
		 * @return {$selection}
		 */
		draggable: function(b) {
			if(this.length)
				for(var i=0; i<this.length; ++i,b=undefined)
					_draggable(this[i],b || this[i]);
			return this;
		},

		/**
		 * @summary Make a textarea expanding to the size of the content
		 * @memberof $selection.prototype
		 * @return {$selection}
		 */
		expandingTextarea: function() {
			if(this.length)
				for(var i=0; i<this.length; ++i)
					if(this[i].tagName=='TEXTAREA') {
						var xta = this[i];
						var div1 = $.create('div',{class:'expanding-textarea'});
						var div2 = $.create('div',null,div1);
						var pre = $.create('pre',null,div2);
						var span = $.create('span',null,pre);
						var br = $.create('br',null,pre);
						$(xta).before(div1);
						$(xta).remove();
						$(div2).append(xta);
						_expandingTextarea(div1,span,xta);
					}
			return this;
		},

		/**
		 * @summary Capture specified keys, and if pressed call associated function(s)
		 * @memberof $selection.prototype
		 * @return {boolean}
		 */
		captureKeys: function(keys) {
			this.keyup(function(event) {
				if(!event) event = window.event;
				var k = event.keyCode+'';
				$.log('Seshat[$.captureKeys]: keyCode='+k);
				if(!!keys[k]) {
					(keys[k])(k);
					return false;
				}
			});
			return this;
		},

		openMessageBox: function(id,content,attr,tm,focus) {
			if(!id) id = 'seshat-message-box';
			for(var i=1,n=id; document.getElementById(id); ++i) id = n+'-'+i;
			if(!attr) attr = {};
			attr.id = id;
			var div = $.create('div',attr,this);
			var box = $(div);
			box.html(content).show();
			if(tm) window.setTimeout(function() { box.remove(); },tm);
			if(focus=='box') location.href = '#'+id;
			else if(focus=='top') window.scrollTo(0,0);
			else if(focus && focus.charAt(0)=='#') location.href = '#'+focus;
			return id;
		},

		setPopupMenu: function(menu_id) {
			var body = $('<body>');
			var menu = $(menu_id);
			menu.hide();
			this.click(function(event) {
				if(!menu.isVisible()) {
					menu.show();
					body.click();
					event.stopPropagation();
					body.click(function() {
						menu.hide();
						body.click(null);
					});
				}
			});
		},

		dropFileArea: function(drop) {
			this.on('dragover',function(event) {
				event.stopPropagation();
				event.preventDefault();
				event.dataTransfer.dropEffect = 'copy'; // Explicitly show this is a copy.
			});
			this.on('drop',function(event) {
				event.stopPropagation();
				event.preventDefault();
				var files = event.dataTransfer.files;
				if(files.length) {
					var file = files[0];
					var sz = file.size;
					var reader = new FileReader();
					reader.onload = (function(theFile) {
						return function(event) {
							$.log('Seshat[$.dropFileArea]: "'+file.name+'"');
							if(drop) drop({
								mime: file.type,
								name: file.name,
								size: sz,
								data: event.target.result
							});
						};
					})(file);
					reader.readAsDataURL(file);
				}
			});
		}
	});

	$.extend({
		dialog: function(dlg,parent) {
			var box = $.create('div',{id:dlg.id,class:dlg.class || 'dialog-box'}),title = false;
			var on = { close:function() { $(box).remove(); } };
			if(dlg.head) {
				var h = dlg.head;
				if(h.title) title = $.create('p',{class:h.class || 'dialog-title',text:h.title},box);
				if(h.closeButton) $($.create('a',{text:h.closeButton.text || 'X',class:h.closeButton.class || 'dialog-close-button'},box)).click(on.close);
			}
			if(dlg.body) {
				var b = dlg.body;
				_createFields(b,box,on);
			}
			$(document.body).append(box);
			if(dlg.center===true) $(box).center();
			if(dlg.draggable===true) $(title || box).draggable(box);
			$.log('Seshat[$.dialog]: focus: '+on.focus);
			if(on.focus instanceof Element) $(on.focus).focus();
			return box;
		},

		cookiesMessage: function(id,text,ok) {
			var cookies = $.cookie('cookies');
			if(cookies=='ok') return;
			else {
				var box = $.create('div',{id:id});
				var div = $.create('div',null,box);
				$($.create('a',{text:ok},div)).click(function() {
					$(box).remove();
					$.cookie('cookies','ok',315360000/*approx 10 years*/,'/');
				});
				var p = $.create('p',null,box);
				$($.create('span',null,p)).html(text);
				$('<body>').prepend(box);
			}
		}
	});

	$(function() {
		$('<button>').attr('type','button');
	});

})($seshat);


