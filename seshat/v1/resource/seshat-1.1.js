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
 * @file There are apparent similarities with jQuery, though it
 * doesn't contain the advanced select functions of jQuery. It isn't
 * designed to fill all the functionality of jQuery, but instead handle
 * specific needs for application development. This script is part
 * of the Seshat framework.
 * 
 * @author Per Löwgren
 * @file resource/seshat-1.1.js  
 * @version 1.1
 * @date Modified: 2016-01-17
 * @date Created: 2014-08-03
 * @copyright Per Löwgren 2016
 */

(function(global) {

	/* Function starting with underscore, e.g. "_extend" are internal and
	 * can only be called by $. */

	/* Merge items in Objects e2 with e1, and recurse into sub-Objects/Arrays */
	function _extend(e1,e2) {
		for(var k in e2) {
			var v1 = e1[k],v2 = e2[k];
			if(v1===undefined) e1[k] = v2;
			else if((v1 instanceof Object) && (v2 instanceof Object)) _extend(v1,v2);
			else if((v1 instanceof Array) && (v2 instanceof Array)) e1[k] = v1.concat(v2);
			else e1[k] = v2;
		}
	}

	/* Prepend HTML-elements in v to e, where v may be an instance of $selection, Element,
	 * Array or NodeList */
	function _prepend(e,v) {
		if((v instanceof Element) || (v instanceof Text) || (v instanceof DocumentFragment)) {
			if(e.childNodes.length==0) e.appendChild(v);
			else e.insertBefore(v,e.firstChild);
		} else if((v instanceof Array) || (v instanceof NodeList) || (v instanceof $selection))
			for(var i=v.length-1; i>=0; --i) _prepend(e,v[i]);
	}

	/* Append HTML-elements in v to e, where v may be an instance of $selection, Element,
	 * Array or NodeList */
	function _append(e,v) {
		if((v instanceof Element) || (v instanceof Text) || (v instanceof DocumentFragment)) e.appendChild(v);
		else if((v instanceof Array) || (v instanceof NodeList) || (v instanceof $selection))
			for(var i=0; i<v.length; ++i) _append(e,v[i]);
	}

	/* Prepend HTML-elements in v to e, where v may be an instance of $selection, Element,
	 * Array or NodeList */
	function _before(e,v) {
		if((v instanceof Element) || (v instanceof Text) || (v instanceof DocumentFragment)) {
			if(e.parentNode) e.parentNode.insertBefore(v,e);
		} else if((v instanceof Array) || (v instanceof NodeList) || (v instanceof $selection))
			for(var i=v.length-1; i>=0; --i) _before(e,v[i]);
	}

	/* Append HTML-elements in v to e, where v may be an instance of $selection, Element,
	 * Array or NodeList */
	function _after(e,v) {
		if((v instanceof Element) || (v instanceof Text) || (v instanceof DocumentFragment)) {
			if(e.parentNode) {
				if(e.nextSibling) e.parentNode.insertBefore(v,e.nextSibling);
				else e.parentNode.appendChild(v);
			}
		} else if((v instanceof Array) || (v instanceof NodeList) || (v instanceof $selection))
			for(var i=0; i<v.length; ++i) _after(e,v[i]);
	}

	/* Remove HTML-elements in e, where e may be an instance of $selection, Element, Array
	 * or NodeList */
	function _remove(e) {
		if(e instanceof Element) e.parentNode.removeChild(e);
		else if((e instanceof Array) || (e instanceof NodeList) || (e instanceof $selection))
			for(var i=e.length-1; i>=0; --i)
					if((e[i] instanceof Element) && e[i].parentNode)
						e[i].parentNode.removeChild(e[i]);
	}

	/* Remove HTML-elements in e, where e may be an instance of Element, Array
	 * or NodeList */
	function _removeChildren(e) {
		if(e instanceof Element)
			while(e.firstChild) e.removeChild(e.firstChild);
		else if((e instanceof Array) || (e instanceof NodeList) || (e instanceof $selection))
			for(var i=e.length-1,n; i>=0; --i)
					if(e[i] instanceof Element) _removeChildren(e[i]);
	}

	/* Get the value of HTML-element e, depending on type of element */
	function _getValue(e) {
		if(e.tagName=='INPUT') {
			if(e.type=='checkbox' || e.type=='radio') return e.checked;
			return e.value;
		}
		else if(e.tagName=='SELECT') return e.options[e.selectedIndex].value;
		else if(e.tagName=='TEXTAREA') return e.value;
		return e.innerHTML;
	}

	/* Set the value v to HTML-element e, depending on type of element and value */
	function _setValue(e,v) {
		if(e.tagName=='INPUT') {
			if(e.type=='text' || e.type=='password') e.value = v;
			else if(e.type=='checkbox' || e.type=='radio') e.checked = (v==='true' || v===1 || v===true);
			else e.value = v;
		} else if(e.tagName=='SELECT') {
			e.selectedIndex = 0;
			for(var i=0; i<e.options.length; ++i)
				if(e.options[i].value==v) {
					e.selectedIndex = i;
					break;
				}
		} else if(e.tagName=='TEXTAREA') e.value = v;
		else {
			if(v=='') _removeChildren(e);
			else e.innerHTML = v;
		}
		if((f=e.__$__setValue__)!==undefined && (typeof f === 'function')) f();
//$.log('Seshat: value('+e+','+v+')');
	}

	/* Set attribute(s) of HTML-element e, where k may be an Object with key-value
	 * pairs, or k is key and v is value */
	function _setAttribute(e,k,v) {
		if(v===undefined) {
			if(k instanceof Object)
				for(i in k) _setAttribute(e,i,k[i]);
		} else {
			if(k==='style' || k==='css')
				for(var i in v) e.style[i] = v[i];
			else if(k==='class') e.className = v;
			else if(k==='text') e.appendChild(document.createTextNode(v));
			else if(k==='attr') _setAttribute(e,v);
			else if(v===false) e.removeAttribute(k);
			else e.setAttribute(k,v);
		}
	}

	/* Set the CSS-style of HTML-element e, where k may be an Object with key-value
	 * pairs, or k is key and v is value, or of k='class' set className to v */
	function _setStyle(e,k,v) {
		if(k instanceof Object) {
			for(var i in k) e.style[i] = k[i];
		} else if(typeof k === 'string' || k instanceof String) {
			if(k=='class') e.className = v;
			else e.style[k] = v;
		}
	}

	/* General function for on-events for HTML-element e, where n is name of event,
	 * e.g. "onclick", and if f=undefined function is triggeres, otherwise set to f */
	function _on(e,n,f) {
//$.log('click: '+e.id+' '+f);
		if(f===undefined) {
			if((f=e[n])!==undefined) { $.log('_on('+n+')');e[n](); }
			else if((f=e['on'+n])!==undefined) f();
		} else if(f===null || (typeof f === 'function')) e['on'+n] = f;
	}

	/* General function för testing the is-method of $selection. */
	function _is(e,k,v) {
		if(e instanceof Element) return (v===e[k]);
		else for(var i=0; i<e.length; ++i) if(v===e[i][k]) return true;
		return false;
	}

	function _select(id,e) {
		var c = id.charAt(0),i;
		if(e===undefined) e = document;
		if(c==='#')
			return e.getElementById(id.substr(1));
		else if(c==='.')
			return e.getElementsByClassName(id.substr(1));
		else if(c==='<' && (i=id.indexOf('>'))>0)
			return e.getElementsByTagName(id.substr(1,i-1));
		else if(c==='?')
			return e.querySelectorAll(id.substr(1));
		else
			return e.getElementsByName(id);
	};

	function _transferElementsToArray(a,e) {
		if(!e) return;
		if(e instanceof Element) a.push(e);
		else {
			for(var i=0,n; i<e.length; ++i) {
				n = e[i];
				if(n instanceof Element) a.push(n);
				else if(typeof n == 'string' || n instanceof String)
					_transferElementsToArray(a,_select(n));
			}
		}
	}

	/**
	 * @summary Constructor for selections of elements used by $
	 * 
	 * @description Extends the built in Array class, and contains an array of Elements.
	 * 
	 * To this class is added $selection-specific methods
	 * 
	 * @class $selection
	 * @param {(Element|Array|NodeList)} e 
	 */
	function $selection(e) {
		_transferElementsToArray(this,e);
	}

	$selection.prototype = [];

	/**
	 * @summary The library Namespace
	 * 
	 * @description The $ framework is not instantiated, but instead
	 * makes use of the selector function and static methods.
	 * 
	 * Extending the $ js-framework is done similarly to jQuery and other
	 * libraries, with the extend-method:
	 * ```
	 * $.extend({
	 *    extendedMember: 'abc',
	 *    extendedMethod: function() { ... }
	 * });
	 * ```
	 * 
	 * $ has its own element, $selection, which contains an
	 * instance of either a DOM Element or Array (or is set to null). A
	 * $selection can be used to call $selection methods.
	 * 
	 * Selecting DOM Elements can be done by name, id, class or tag, and a
	 * $selection is created for accessing the Element object. The last
	 * selected element is stored and accessed by an empty call:
	 * ```
	 * $('name'); // Select element(s) by name
	 * $('#id'); // Select element by id
	 * $('.class'); // Select element(s) by class
	 * $('<tag>'); // Select element(s) by tag
	 * $(); // Last selected element
	 * $(elem); // Create a $selection that wraps the $selection or Element object (elem)
	 * ```
	 * 
	 * The $-elements have their own set of methods, and can also extend
	 * additional members and methods with the implement-method:
	 * ```
	 * $.implement({
	 *    implementedMember: 'abc',
	 *    implementedMethod: function() { ... }
	 * });
	 * ```
	 * 
	 * When using the extend or implement methods, any existing members are
	 * replaced indiscriminately, and so naming is of importance.
	 * 
	 * Another functionality of $ is to add start-functions, which are called
	 * by window.onload. This can be done like so:
	 * ```
	 * $(function() {
	 *    $.extendedMethod();
	 *    $('#id-of-element').implementedMethod();
	 * });
	 * ```
	 * 
	 * An alias for '$' is '$seshat', and both can be used in exactly the same way
	 * and as the same object. It is good practice to enclose extensions inside
	 * an anonymous function:
	 * ```
	 * (function($) {
	 *    $.extend({ ... });
	 * })($seshat);
	 * ```
	 * 
	 * $ is not designed to replace the DOM-model entirely, the way jQuery is,
	 * and therefore ordinary DOM Element objects are always returned by all
	 * $-methods that returns element object (such as create), that is methods
	 * extended to the $-object with the extend method, except the selector-method
	 * called by $ itself `$('#id')`.
	 * 
	 * @namespace $
	 */

	/**
	 * @summary $ selector function
	 * 
	 * @description Selects one or more Elements that are stored in a new
	 * $selection which is returned. The selector id may be in the following
	 * formats:
	 *  - string:
	 *    - 'name': Name of the Element(s)
	 *    - '#id': ID of the Element
	 *    - '.class': Class name of the Element(s)
	 *    - '&lt;tag&gt;': Tag name of the Element(s)
	 *  - Element: Generated a $selection that wraps the Element
	 *  - Function: Adds function to the functions run by window.onload
	 *  - undefined: By calling $(), the last $selection selected is returned
	 * 
	 * @function $
	 * @variation 2
	 * @param {(string|$selection|Element|function())=} id
	 * @param {($|$selection|Element)=} c
	 * @return {$selection}
	 * @global
	 * @see $selection
	 */
	var $ = function(id,c) {
		if(id===undefined) return this.__selected__;
		if(id instanceof $selection) return (this.__selected__ = id);
		if(typeof id === 'function') $.start(id);
		else {
			var e = null;
			if((id instanceof Element) || (id instanceof Array) ||
				(id instanceof NodeList) || (id instanceof $selection)) e = id;
			else if(typeof id == 'string' || id instanceof String) {
				e = _select(id,c);
/*				var sel,n = id.charAt(0);
				if(c===undefined) c = document;
				else if(c instanceof $) c = c.__selected__;
				if(n==='#') e = document.getElementById(id.substr(1));
				else if(n==='.') e = c.getElementsByClassName(id.substr(1));
				else if(n==='<' && (n=id.indexOf('>'))>0) e = c.getElementsByTagName(id.substr(1,n-1));
				else if(n==='?') e = c.querySelectorAll(id.substr(1));
				else e = document.getElementsByName(id);*/
//$.log('$("'+id+'")');
			}
			return (this.__selected__ = new $selection(e));
		}
	};

	/**
	 * @summary Extends $ with members and methods
	 * 
	 * @description Any number of arguments can be given. If more than one
	 * argument is given, the first argument will be extended with the following
	 * arguments' members.
	 * 
	 * If an index already contains a value, it's replaced, unless it's an Array
	 * or Object, in which case it's searched recursively for sub-indexes. Thus
	 * no indexes are lost, and all are safely merged into one object.
	 * 
	 * @method extend
	 * @memberof $
	 * @param {...Object}
	 * @return {($|Object)}
	 */
	$.extend = function() {
		var e1 = this,e2,i;
		if(arguments.length>0) {
			if(arguments.length>1) e1 = arguments[0];
			if(e1 instanceof Object)
				for(i=0; i<arguments.length; ++i) {
					e2 = arguments[i];
					if(e1!==e2 && (e2 instanceof Object)) _extend(e1,e2);
				}
		}
		return e1;
	};

	$.extend({
		/**
		 * @summary Hidden data that should not be used outside of this script
		 * @memberof $
		 * @type {Object}
		 * @private
		 * @ignore
		 */
		__private__: {
			debug: true,
			alert: true,
			start: [],
			lang: 'en'
		},

		/**
		 * @summary Setup private data used internally by $ functions, or get private data value 
		 * @memberof $
		 * @param {string|Object} v If a string, returns private value of v; if an Object, set private values to v
		 * @return {($|*)}
		 */
		setup: function(v) {
			var _p = $.__private__;
			if(typeof v == 'string' || v instanceof String) return _p[v];
			else if(v instanceof Object)
				for(var k in v) {
					if((_p[k] instanceof Object) && (v[k] instanceof Object)) _extend(_p[k],v[k]);
					else _p[k] = v[k];
				}
			return this;
		},

		/**
		 * @summary Adds function(s) to call by window.onload
		 * @memberof $
		 * @param {(Array|function())} f A function or array of functions
		 * @return {$}
		 */
		start: function(f) {
			var _p = $.__private__;
			if(_p.start===false) f();
			else if(f instanceof Array) _p.start = _p.start.concat(f);
			else _p.start.push(f);
			return this;
		},

		/**
		 * @summary Add methods to the $selection returned by the $-selector
		 * 
		 * @description Any number of arguments may be given - but if there
		 * are more than one argument, the first argument's Object is extended
		 * with the following arguments' Objects' members.
		 * 
		 * This method is similar to the extend-function, except it extends
		 * to the Object's prototype-member, instead of the Object itself.
		 * 
		 * @memberof $
		 * @return {$}
		 * @see $.extend
		 */
		implement: function() {
			var e1 = $selection,e2,e3,i;
			if(arguments.length>0) {
				if(arguments.length>1) e1 = arguments[0];
				if(e1 instanceof Object) {
					e2 = e1.prototype;
					for(i=0; i<arguments.length; ++i) {
						e3 = arguments[i];
						if(e1!==e3 && e2!==e3 && (e3 instanceof Object)) _extend(e2,e3);
					}
				}
			}
			return this;
		},

		/**
		 * @summary Call a function for each of the given values
		 * @memberof $
		 * @param {($|Element|Array|Object)} e Values to iterate
		 * @param {function((number|string),string)} f Function to call, where first argument is index, and second is value
		 * @return {$}
		 */
		each: function(e,f) {
			if(typeof f !== 'function') return;
			if(e instanceof Element) f.call(e,0,e);
			else if(e instanceof Object)
				for(var k in e) f.call(k,k,e[k]);
			else
				for(var i=0; i<e.length; ++i) f.call(e[i],i,e[i]);
			return this;
		},

		parseHTML: function(html) {
			var e = document.createElement('template');
			if('content' in e) {
$.log('template');
				e.innerHTML = html;
$.log('template: '+e.innerHTML);
				return e.content;
			}
			var d = document.createDocumentFragment();
			e = document.createElement('body');
			e.innerHTML = html;
			for(var i=0; i<e.childNodes.length; ++i)
				d.appendChild(el.childNodes[i]);
			return d;
		},
/*
		parseHTML: function(html) {
			if(typeof html == 'string' || html instanceof String) {
				var div = document.createElement('div');
				div.innerHTML = html;
				return div.childNodes;
			} else return null;
		},*/

		/**
		 * @summary Create a HTML Element object
		 * 
		 * @description The attributes can have a "style" or "css" value, which
		 * is added to the Element's style.
		 * 
		 * If "text" is given as an attribute, it's appended as a text-node.
		 * 
		 * @memberof $
		 * @param {string} tag Tag type of Element
		 * @param {Object} attr Attributes of the Element
		 * @param {($|Element)=} parent Parent Element to add to as a child
		 * @return {Element}
		 */
		create: function(tag,attr,parent) {
			var e = document.createElement(tag);
			if(attr) _setAttribute(e,attr);
			if(parent) {
				if(parent instanceof $selection) parent.append(e);
				else parent.appendChild(e);
			}
			return e;
		},

		/**
		 * @summary Include a javascript file
		 * @memberof $
		 * @param {string} id The ID to be associadet with the script
		 * @param {string} url URL for the scripte
		 * @return {$}
		 */
		include: function(id,url) {
			if(!document.getElementById(id)) {
				var fjs = document.getElementsByTagName('script')[0];
				var js = document.createElement('script');
				js.id = id;
				js.src = url;
				fjs.parentNode.insertBefore(js,fjs);
			}
			return this;
		},

		/**
		 * @summary Make an AJAX call
		 * 
		 * @description Argument may contain the following fields (only
		 * the url-field is required):
		 *  - url: URL (required)
		 *  - cors: If set to true attempts to make a Cross-Origin Resource Sharing (CORS) request
		 *  - method: HTTP-method (GET, POST)
		 *  - headers: List of HTTP-headers in the format { 'Content-Type': 'application/json; charset=UTF-8' }
		 *  - mime: Mime-type for sent data
		 *  - accept: format of data that is expected
		 *  - data: Data to be sent
		 *  - success: Callback function called on success, with param 'data'
		 *  - fail: Callback function called on fail, with params 'status', 'response'
		 * 
		 * @memberof $
		 * @param {Object} o Object containing parameters for making the ajax-call (see method description)
		 * @return {$}
		 */
		ajax: function(o) {
			if(!XMLHttpRequest) throw new Error('Seshat[$.ajax]: AJAX not working, upgrade browser to a more recent version.');
			else if(!o.url) throw new Error('Seshat[$.ajax]: URL-property missing.');
			else {
//$.log('Seshat: ajax(url: '+o.url+')');
				var ajax_url = $.setup('ajax-url');
				if(ajax_url) o.url = ajax_url+o.url;

				var h = o.headers || {};
				var load = function(status,response) {
$.log('Seshat[$.ajax]: "'+o.url+'", '+status);
$.log(response);
					if(o.accept=='json') response = JSON.parse(response+'');
					if(status==200 || status==201) {
						if(o.success) o.success(response);
					} else {
						if(o.fail) o.fail(status,response);
					}
				};

//$.log('Seshat[$.ajax]: Data instanceof Array: '+(o.data instanceof Array? 'true' : 'false'));
//$.log('Seshat[$.ajax]: Data instanceof Object: '+(o.data instanceof Object? 'true' : 'false'));

				// If o.data is an Array or Object, it is sent as a JSON-string:
				if(o.data && ((o.data instanceof Array) || (o.data instanceof Object)))
					o.data = JSON.stringify(o.data);

//$.log('Seshat[$.ajax]: Data: '+o.data);

				var xhr = new XMLHttpRequest();
				if(!o.cors || ('withCredentials' in xhr)) {
					// Firefox 3.5 and Safari 4
					xhr.onreadystatechange = function () {
//$.log('Seshat[$.ajax]: readyState: '+xhr.readyState+', status: '+xhr.status);
						if(xhr.readyState===4) {
							load(xhr.status,xhr.responseText);
//							if(xhr.status==200 || xhr.status==201) load(xhr.responseText);
//							else if(o.fail) o.fail(xhr.status,xhr.responseText);
						}
					};
					xhr.open(o.method || 'GET',o.url,true);
					if(o.mime && xhr.overrideMimeType) xhr.overrideMimeType(o.mime);
					if(!h["X-Requested-With"]) h['X-Requested-With'] = 'XMLHttpRequest';
					for(var n in h) xhr.setRequestHeader(n,h[n]);
					xhr.send(o.data || null);
				} else if(XDomainRequest) { // IE8 support for CORS:
					var xdr = new XDomainRequest();
					if(xdr) {
						if(o.fail) {
							xdr.onerror = function() { load(500,xdr.responseText); };
							xdr.ontimeout = function() {
								var msg = '408 Request Timeout';
								if(o.accept=='json') msg = '{"code":500,"error":"'+msg+'"}';
								load(408,msg);
							};
//							xdr.onerror = function() { o.fail(0,xdr.responseText); };
//							xdr.ontimeout = function() { o.fail(500,'Seshat[$.ajax]: Timeout.'); };
						}
						xdr.timeout = 10000;
						xdr.onload = function() { load(200,xdr.responseText); }
						xdr.open(o.method || 'GET',o.url);
						xdr.send(o.data || null);
					} else throw new Error('Seshat[$.ajax]: Could not create XDomainRequest object.');
				} else throw new Error('Seshat[$.ajax]: Browser not supporting Cross-Origin Resource Sharing (CORS), or server side headers missing.');
			}
			return this;
		},

		/**
		 * @summary Write a message to the console log (not all browsers)
		 * @memberof $
		 * @param {string} msg Message to be written
		 * @return {$}
		 */
		log: function(msg) {
			if($.__private__.debug && global.console && global.console.log)
				global.console.log(msg);
			return this;
		},

		/**
		 * @memberof $
		 * @return {$}
		 */
		alert: function(id,msg,timer) {
			var _p = $.__private__;
			if(_p.alert!==false) {
				var a = $(/*(_p.page? _p.page.id+'_' : '')+*/'alert');
				if(a) {
					$.clearAlert();
					a.html(msg).show();
				} else alert(msg);
				var i = $(id);
				if(i.length) {
					var e = i[0];
					var p = e.parentNode;
					_p.alert = [ e,e.onfocus,e.onblur,p,p.className ];
					p.className = 'field-alert';
//$.log('alert(tag: '+p.tagName+', class: '+p.className+')');
					e.onfocus = e.onblur = $.clearAlert;
					if(timer!==undefined && (typeof timer === 'number')) global.setTimeout($.clearAlert,timer);
				}
			}
			return this;
		},

		/**
		 * @memberof $
		 * @return {$}
		 */
		showAlert: function(alert) {
			if(alert===true || alert===false) $.__private__.alert = alert;
			return this;
		},

		/**
		 * @memberof $
		 * @return {$}
		 */
		clearAlert: function() {
//$.log('clearAlert()');
			var _p = $.__private__;
			if(_p.alert!==false) {
				var a = $(/*(_p.page? _p.page.id+'_' : '')+*/'alert');
				if(a) a.hide();
				if(_p.alert instanceof Array) {
					var e = _p.alert[0];
					e.onfocus = _p.alert[1];
					e.onblur = _p.alert[2];
					var p = _p.alert[3];
					p.className = _p.alert[4];
					_p.alert = true;
				}
			}
			return this;
		},

		/**
		 * @memberof $
		 * @return {boolean}
		 */
		confirm: function(caption,message) {
			if(!message) message = caption;
			return confirm(message);
		},

		/**
		 * @summary Encode an object with given format
		 * 
		 * @description The following formats can be encoded:
		 *  - 'escape': Escape
		 *  - 'string': See 'escape'
		 *  - 'json': Encode as a JSON-string
		 *  - 'url': Encode URL
		 *  - 'uri': See 'url'
		 * 
		 * @memberof $
		 * @param {string} format format to be used
		 * @param {(string|Array|Object)} obj Object to be encoded
		 * @return {string}
		 */
		encode: function(format,obj) {
			if(!obj) return '';
			if(format!==undefined && (typeof format == 'string' || format instanceof String)) {
				format = format.toLowerCase();
				if(format=='json') return JSON.stringify(obj);
				else if(typeof obj == 'string' || obj instanceof String) {
					if(format=='escape' || format=='string') return obj.replace(/[\\"']/g,'\\$&');
					else if(format=='url' || format=='uri') return encodeURI(obj);
				}
			}
			return obj.toString();
		},

		/**
		 * @summary Decode a string with given format
		 * 
		 * @description The following formats can be decoded:
		 *  - 'unescape': Unescape
		 *  - 'string': See 'unescape'
		 *  - 'json': Parse string as JSON
		 *  - 'url': Decode URL
		 *  - 'uri': See 'url'
		 * 
		 * @memberof $
		 * @param {string} format format to be used
		 * @param {string} str String to be decoded
		 * @return {string}
		 */
		decode: function(format,str) {
			if(!str) return '';
			if(format!==undefined && (typeof format == 'string' || format instanceof String)) {
				format = format.toLowerCase();
				if(typeof str == 'string' || str instanceof String) {
					if(format=='unescape' || format=='string') return str.replace(/\\([\\"'])/g,'$&');
					else if(format=='json') return JSON.parse(str);
					else if(format=='url' || format=='uri') return decodeURI(str);
				}
			}
			return str;
		},

		/**
		 * @summary Randomly generate a password string
		 * @memberof $
		 * @param {number} len Length of string in number of chars
		 * @param {string=} chars Chars to be accepted in the generaetd string, if undefined [a-zA-Z0-9] is used
		 * @return {string}
		 */
		generatePassword: function(len,chars) {
			var i,result;
			if(chars===undefined) chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			for(i=0,result=''; i<len; ++i)
				result += chars.charAt(Math.floor(Math.random()*chars.length));
			return result;
		},

		/**
		 * @summary Generate a GUID v4
		 * @memberof $
		 * @param {boolean=} uc If true makes GUID upper case
		 * @retun {string} GUID in the format "xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx"
		 */
		generateGUID: function(uc) {
			var i,n = 0,r = '';
			var x = uc===true? "0123456789ABCDEF" : "0123456789abcdef";
			for(i=0; i<32;++i,n>>=4) {
				if((i%4)==0) n = Math.random()*0xffffffff|0;
				if(i==8 || i==12 || i==16 || i==20) r += '-';
				r += x[i==12? 4 : (i==16? (n&0x3|0x8) : (n&0xf))];
			}
			return r;
		},

		/**
		 * @summary Convert a number to a string with appropriate bytes, kB, or MB suffix
		 * @description Rounds number to two decimal digits.
		 * @memberof $
		 * @param {number} n Integer of number of bytes
		 * @return {string}
		 */
		toBytes: function(n) { return n<1024? n+' bytes' : (n<1024*1024? (n/1024).toFixed(2)+' kB' : (n/(1024*1024)).toFixed(2)+' MB'); },

		/**
		 * @summary Send form data using AJAX to URL
		 * @memberof $
		 * @param {string} url URL to send form data
		 * @param {(string|Object)} form Form data, should be a string containing name of HTML-form, or a key-value indexed object (a key named 'form' will name a HTML-form)
		 * @param {string=} method GET or POST
		 * @return {(string|boolean)} Response string, or false on fail
		 */
		sendFormData: function(url,form,method) {
			var ret = false,fd;
			if(!FormData) throw new Error('Seshat[$.sendFormData]: FormData object not supported by browser, please upgrade.');
			else {
				if(typeof form == 'string' || form instanceof String) form = {form:form};
				if(form.form) {
					fd = new FormData(document.forms.namedItem(form.form));
					delete form['form'];
				} else fd = new FormData();
				if(!fd) throw new Error('Seshat[$.sendFormData]: Could not create FormData object.');
				for(var key in form)
					if(form.hasOwnProperty(key)) fd.append(key,form[key]);
				$.ajax({
					url: url,
					cors: true,
					method: method || 'POST',
					data: fd,
					success: function(data) { ret = data; },
					fail: function(status,message) {
						throw new Error('Seshat[$.sendFormData]: Could not send form data to server.');
					}
				});
			}
			/*var form = $.create('form',{method:method || 'post',action:url});
			for(var key in params)
				if(params.hasOwnProperty(key))
					$.create('input',{type:'hidden',name:key,value:params[key]},form);
			document.body.appendChild(form);
			form.submit();*/
			return ret;
		},

		/**
		 * @summary Set or get browser cookie
		 * @memberof $
		 * @param {string} name Name of cookie
		 * @param {(string|Object)=} value If set, cookie value is set; else value of cookie is returned
		 * @param {number=} expires Number of seconds
		 * @param {string=} path Path for cookie
		 * @return {(string|boolean)} Response string, or false on fail
		 */
		cookie: function(name,value,expires,path) {
			if(name===undefined || name.length==0) throw new Error('Seshat[$.cookie]: Name-param missing.');
			else {
				if(value===undefined) {
					if(document.cookie.length==0) return '';
					var offset = document.cookie.indexOf(name+'=');
					if(offset==-1) return '';
					offset += name.length+1;
					var end = document.cookie.indexOf(";",offset);
					if(end==-1) end = document.cookie.length;
					return unescape(document.cookie.substring(offset,end));
				}
				if(expires===undefined) expires = '';
				else {
					var d = new Date();
					if(typeof expires === 'string' || expires instanceof String) expires = parseInt(expires);
					d.setTime(d.getTime()+(expires*1000));
					expires = '; expires='+d.toGMTString();
				}
				if(path===undefined) path = '';
				else path = '; path='+path;
				var cookie = name+'='+escape(value)+expires+path;
//$.log('Seshat[$.cookie]: Set cookie "'+cookie+'"');
				document.cookie = cookie;
			}
			return this;
		},

		/**
		 * @summary Send page to printer
		 * @memberof $
		 * @return {$}
		 */
		print: function() {
			global.print();
			return this;
		}
	});

	/**
	 * @summary Create or trigger event 'blur'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method blur
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'focus'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method focus
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'focusin'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method focusin
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'focusout'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method focusout
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'load'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method load
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'resize'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method resize
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'scroll'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method scroll
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'unload'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method unload
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'click'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method click
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'dblclick'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method dblclick
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'mousedown'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method mousedown
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'mouseup'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method mouseup
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'mousemove'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method blur
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'mouseover'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method mouseover
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'mouseout'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method mouseout
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'mouseenter'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method mouseenter
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'mouseleave'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method mouseleave
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'change'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method change
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'select'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method select
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'keydown'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method keydown
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'keypress'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method keypress
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'keyup'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method keyup
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'error'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method error
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	/**
	 * @summary Create or trigger event 'contextmenu'
	 * @description When triggered, *this* is set to the DOM element.
	 * @method contextmenu
	 * @memberof $selection.prototype
	 * @param {function(Event)=} f If set creates event with f as function to be called when triggered; else triggers event
	 * @return {$selection}
	 */

	$.each(['blur','focus','focusin','focusout','load','resize','scroll','unload','click','dblclick',
		'mousedown','mouseup','mousemove','mouseover','mouseout','mouseenter','mouseleave',
		'change','select','keydown','keypress','keyup','error','contextmenu'],function(i,n) {
		$selection.prototype[n] = function(f) {
			for(var i=0; i<this.length; ++i)
				_on(this[i],n,f);
			return this;
		}
	});


	/**
	 * @summary Set or get id-property of Element
	 * @method id
	 * @memberof $selection.prototype
	 * @param {string=} s Sets value unless undefined
	 * @return {($selection|string)} If s is set, return this; else returns value
	 */

	/**
	 * @summary Set or get name-property of Element
	 * @method name
	 * @memberof $selection.prototype
	 * @param {string=} s Sets value unless undefined
	 * @return {($selection|string)} If s is set, return this; else returns value
	 */

	/**
	 * @summary Set or get title-property of Element
	 * @method title
	 * @memberof $selection.prototype
	 * @param {string=} s Sets value unless undefined
	 * @return {($selection|string)} If s is set, return this; else returns value
	 */

	/**
	 * @summary Set or get checked-property of Element
	 * @method checked
	 * @memberof $selection.prototype
	 * @param {string=} s Sets value unless undefined
	 * @return {($selection|boolean)} If s is set, return this; else returns value
	 */

	$.each({'id': 'id','name': 'name','title': 'title','checked': 'checked'},function(key,value) {
		$selection.prototype[key] = function(s) {
//$.log('click: '+e.id+' '+f);
			if(s===undefined) {
				if(!this.length) return undefined;
				var e = this[0];
				return e[value];
			}
			for(var i=0,e; i<this.length; ++i) {
				e = this[i];
				e[value] = s;
			}
			return this;
		}
	});

	/**
	 * @summary Get tagName-property of Element
	 * @method tag
	 * @memberof $selection.prototype
	 * @return {string}
	 */

	/**
	 * @summary Get type-property of Element
	 * @method type
	 * @memberof $selection.prototype
	 * @return {string}
	 */

	$.each({'tag': 'tagName','type': 'type'},function(key,value) {
		$selection.prototype[key] = function() {
//$.log('click: '+e.id+' '+f);
			if(!this.length) return undefined;
			var e = this[0];
			return e[value];
		}
	});

	/**
	 * @summary Prepend HTML Element(s) as children
	 * @method prepend
	 * @memberof $selection.prototype
	 * @param {($selection|Element|Array)} e
	 * @return {$selection}
	 */

	/**
	 * @summary Append HTML Element(s) as children
	 * @method append
	 * @memberof $selection.prototype
	 * @param {($selection|Element|Array)} e
	 * @return {$selection}
	 */

	/**
	 * @summary Insert HTML Element(s) before selected Element(s)
	 * @method before
	 * @memberof $selection.prototype
	 * @param {($selection|Element|Array)} e
	 * @return {$selection}
	 */

	/**
	 * @summary Insert HTML Element(s) after selected Element(s)
	 * @method after
	 * @memberof $selection.prototype
	 * @param {($selection|Element|Array)} e
	 * @return {$selection}
	 */

	$.each({'prepend':_prepend,'append':_append,'before':_before,'after':_after},function(n,f) {
		$selection.prototype[n] = function(e) {
			if(this.length && e!==undefined) {
				if(typeof e == 'string' || e instanceof String) e = $.parseHTML(e);
				for(var i=0; i<this.length; ++i)
					f(this[i],i==0? e : e.cloneNode(true));
			}
			return this;
		}
	});

	$.implement({
		/**
		 * @summary Get the first Element contained in the $selection
		 * @memberof $selection.prototype
		 * @return {Element}
		 */
		first: function() { return this.length? this[0] : undefined; },

		/**
		 * @summary Get the last Element contained in the $selection
		 * @memberof $selection.prototype
		 * @return {Element}
		 */
		last: function() { return this.length? this[this.length-1] : undefined; },

		/**
		 * @summary Get an Array of the child-nodes of the selected Element(s)
		 * @memberof $selection.prototype
		 * @return {Array}
		 */
		children: function() {
			var arr = [];
			for(var i=0; i<this.length; ++i)
				_transferElementsToArray(arr,this[i].childNodes);
			return arr;
		},

		/**
		 * @summary Remove selected Element(s) from the document
		 * @memberof $selection.prototype
		 * @return {Element}
		 */
		remove: function() {
			_remove(this);
			return this;
		},

		/**
		 * @summary Add Event-listener for the event 'e' and function 'f'
		 * @memberof $selection.prototype
		 * @param {string} e Name of event
		 * @param {function()} f Function to be called
		 * @param {boolean} c Use capture; if true called at capturing phase, else at bubbling phase (default)
		 * @return {$selection}
		 */
		on: function(e,f,c) {
			if(this.length && typeof f === 'function')
				for(var i=0; i<this.length; ++i)
					this[i].addEventListener(e,f,!!c);
			return this;
		},

		/**
		 * @summary Call a function for each of the selected Element(s)
		 * 
		 * @description The function is called with the DOM element set as *this*.
		 * 
		 * @memberof $selection.prototype
		 * @param {function()} f Function to be called
		 * @param {Array=} a If given, function is called with a as arguments
		 * @return {$selection}
		 */
		each: function(f,a) {
			if(this.length && typeof f === 'function')
				for(var i=0; i<this.length; ++i) {
					if(a===undefined) f.call(this[i]);
					else f.apply(this[i],a);
				}
			return this;
		},

		/**
		 * @summary Test for an expression, for every Element, and return true if one or more matches
		 * @memberof $selection.prototype
		 * @param {(string|function(number,Element)|Element|Array|NodeList)} id
		 * @return {boolean}
		 */
		is: function(id) {
			if(this.length) {
				if(typeof id == 'string' || id instanceof String) {
					var n = id.charAt(0);
					if(n==='#') return _is(this,'id',id.substr(1));
					else if(n==='.') return _is(this,'className',id.substr(1));
					else if(n==='<' && (n=id.indexOf('>'))>0) return _is(this,'tagName',id.substr(1,n-1));
					else if(n!=='?') return _is(this,'name',id);
				} else if(typeof id === 'function') {
					for(var i=0; i<e.length; ++i)
						if(id.call(this[i],i,this[i])) return true;
				} else {
					if(id instanceof Element) {
						for(var i=0; i<this.length; ++i)
							if(id===this[i]) return true;
					} else if((id instanceof Array) || (id instanceof NodeList) || (id instanceof $selection)) {
						for(var n=0,m; n<id.length; ++n)
							if((m=id[n]) instanceof Element)
								for(var i=0; i<this.length; ++i)
									if(m===this[i]) return true;
					}
				}
			}
			return false;
		},

		/**
		 * @summary Return true if Element.style.display!=='none'
		 * @memberof $selection.prototype
		 * @return {boolean}
		 */
		isVisible: function() {
			for(var i=0; i<this.length; ++i)
				if(this[0].style.display!=='none') return true;
			return false;
		},

		/**
		 * @summary Store or retrieve user data associated with the selected Element(s)
		 * 
		 * @description The data can be any value. If the function is called with a value
		 * it is stored, otherwise the method returns the value that is stored, if any,
		 * for the given name.
		 * 
		 * @memberof $selection.prototype
		 * @param {string} k Name of data
		 * @param {*=} v Value to store
		 * @return {*} If v is not set, the value stored for index k; else *this*
		 */
		data: function(k,v) {
			if(this.length && k!==undefined) {
				k = 'data-'+k;
				if(v===undefined) {
					if(this.length==1) {
						var e = this[0];
						return !e[k]? undefined : e[k];
					} else {
						var e,d = [];
						for(var i=0; i<this.length; ++i) {
							e = this[i];
							d[i] = e[k];
						}
						return d;
					}
				}
				for(var i=0,e; i<this.length; ++i) {
					e = this[i];
					e[k] = v;
				}
			}
			return this;
		},

		/**
		 * @summary Set or get value of selected Element(s)
		 * 
		 * @description If a value is given, it's set to the Element(s), otherwise the value(s)
		 * of the selected Element(s) is returned, either as a string, or an Array if there are many.
		 * 
		 * @memberof $selection.prototype
		 * @param {string=} v Value to store
		 * @return {(string|Array|$selection)} If v is not set, the value stored for index k; else *this*
		 */
		value: function(v) {
			if(this.length) {
				if(v===undefined) {
					if(this.length==1) return _getValue(this[0]);
					v = [];
					for(var i=0; i<this.length; ++i) v[i] = _getValue(this[i]);
					return v;
				}
				for(var i=0; i<this.length; ++i) _setValue(this[i],v);
			}
			return this;
		},

		/**
		 * @summary Set or get HTML contents of selected Element(s)
		 * 
		 * @description If a HTML value is given, it's set to the Element(s) contens, otherwise
		 * the contents of the Element(s) is returned, either as a string, or an Array if there
		 * are many.
		 * 
		 * @memberof $selection.prototype
		 * @param {string=} h HTML content
		 * @return {(string|Array|$selection)} If h is not set, the HTML content of Element(s); else *this*
		 */
		html: function(h) {
			if(this.length) {
				if(h===undefined) {
					if(this.length==1) return this[0].innerHTML;
					v = [];
					for(var i=0; i<this.length; ++i) v[i] = this[i].innerHTML;
					return v;
				}
				if(h=='') _removeChildren(this);
				else for(var i=0; i<this.length; ++i) this[i].innerHTML = h;
			}
			return this;
		},

		/**
		 * @summary Set or get text contents of selected Element(s)
		 * 
		 * @description If a text value is given, it's set to the Element(s) contens, otherwise
		 * the contents of the Element(s) is returned, either as a string, or an Array if there
		 * are many.
		 * 
		 * @memberof $selection.prototype
		 * @param {string=} t Text content
		 * @return {(string|Array|$selection)} If t is not set, the text content of Element(s); else *this*
		 */
		text: function(t) {
			if(this.length) {
				if(t===undefined) {
					if(this.length==1) return e.textContent;
					v = [];
					for(var i=0; i<this.length; ++i) v[i] = this[i].textContent;
					return v;
				}
				for(var i=0; i<this.length; ++i) this[i].textContent = t;
			}
			return this;
		},

		/**
		 * @summary Set or get attribute(s) of selected Element(s)
		 * 
		 * @description If a value is given, or if key is an Object of a key=>value indexed list of
		 * attributes, it's set to the attribute of the Element(s), otherwise the attributes of
		 * the selected Element(s) is returned, either as a string, or an Array if there
		 * are many.
		 * 
		 * To set a class instead of style, use the key 'class', which will set the className
		 * for the Element; use 'style' to set a style; and 'attr' an internal Object of additional
		 * attributes.
		 * 
		 * @memberof $selection.prototype
		 * @param {(string|Object)} k Attribute name, or Object containing a set of attributes and values
		 * @param {string=} v If set, the value to set to the attribute
		 * @return {(string|Array|$selection)} If v is not set, the value(s) for attribute k; else *this*
		 */
		attr: function(k,v) {
			if(this.length) {
				if(v===undefined && (typeof k == 'string' || k instanceof String)) {
					if(this.length==1) return this[0].getAttribute(k);
					v = [];
					for(var i=0; i<this.length; ++i) v[i] = this[i].getAttribute(k);
					return v;
				}
				for(var i=0; i<this.length; ++i) _setAttribute(this[i],k,v);
			}
			return this;
		},

		/**
		 * @summary Set or get style of selected Element(s)
		 * 
		 * @description If a value is given, it's set to the style of the Element(s), otherwise the
		 * style(s) of the selected Element(s) is returned, either as a string, or an Array if there
		 * are many.
		 * 
		 * To set a class instead of style, use the key 'class', which will set the className
		 * for the Element.
		 * 
		 * @memberof $selection.prototype
		 * @param {(string|Object)} k Style name, or Object containing a set of styles and values
		 * @param {string=} v If set, the value to set to the style
		 * @return {(string|Array|$selection)} If v is not set, the value(s) for style k; else *this*
		 */
		style: function(k,v) {
			if(this.length) {
				if(v===undefined && (typeof k == 'string' || k instanceof String)) {
					if(this.length==1) return k=='class'? e.className : this[0].style[k];
					v = [];
					for(var i=0; i<this.length; ++i) v[i] = k=='class'? this[i].className : this[i].style[k];
					return v;
				}
				for(var i=0; i<this.length; ++i) _setStyle(this[i],k,v);
			}
			return this;
		},

		/**
		 * @summary Show selected Element(s), (or hide)
		 * @memberof $selection.prototype
		 * @param {boolean=} d If set, either show if true or hide if false
		 * @return {$selection}
		 */
		show: function(d) {
			if(this.length) {
				if(d===undefined) d = 'block';
				else d = d===true? 'block' : (d===false? 'none' : ((typeof d == 'string' || d instanceof String)? d : 'block'));
				for(var i=0; i<this.length; ++i) this[i].style.display = d;
			}
			return this;
		},

		/**
		 * @summary Hide selected Element(s)
		 * @memberof $selection.prototype
		 * @return {$selection}
		 */
		hide: function() {
			for(var i=0; i<this.length; ++i) this[i].style.display = 'none';
			return this;
		},

		/**
		 * @summary Toggle visibility of selected Element(s)
		 * @memberof $selection.prototype
		 * @param {boolean|string=} d If given: if true shows, if false hides, otherwise set style.display to given string
		 * @return {($selection|boolean)} If one Element is toggled: true for visible, else false; in all other cases the selection
		 */
		toggle: function(d) {
			if(d===true || d===false) return this.show(d);
			if(this.length) {
				if(d===undefined) d = 'block';
				else d = (typeof d == 'string' || d instanceof String)? d : 'block';
				if(this.length==1) {
					var e = this[0];
$.log('e.style.display: '+e.style.display+', d: '+d);
					e.style.display = e.style.display=='none'? d : 'none';
					return e.style.display!='none';
				} else {
					for(var i=0; i<this.length; ++i)
						this[i].style.display = this[i].style.display=='none'? d : 'none';
				}
			}
			return this;
		},

		/**
		 * @summary Submit form, if selected element is a FORM-element, or set onsubmit function
		 * @description If selection is an Array, first FOMR-element is used
		 * @memberof $selection.prototype
		 * @param {function()=} f
		 * @return {$selection}
		 */
		submit: function(f) {
			if(this.length)
				for(var i=0; i<this.length; ++i) {
					var e = this[i];
					if(e.tagName==='FORM') {
						if(f===undefined) e.submit();
						else if(typeof f === 'function') e.onsubmit = f;
					}
				}
			return this;
		},

		/**
		 * @summary Set cursor for selection
		 * @memberof $selection.prototype
		 * @param {string} c
		 * @return {$selection}
		 */
		cursor: function(c) {
			if(this.length)
				for(var i=0; i<this.length; ++i)
					this[i].style.cursor = c;
			return this;
		},

		/**
		 * @summary Encode value of selected Element
		 * @description If more than one Element is selected, first is used.
		 * @memberof $selection.prototype
		 * @param {string} format format to be used
		 * @return {string}
		 * @see $.encode
		 */
		encode: function(f) {
			if(!this.length) return '';
			if(this.length==1) return $.encode(f,_getValue(this[0]));
			var v = [];
			for(var i=0; i<this.length; ++i)
				v[i] = $.encode(f,_getValue(this[i]));
			return v;
		},

		/**
		 * @summary Decode value of selected Element
		 * @description If more than one Element is selected, first is used.
		 * @memberof $selection.prototype
		 * @param {string} f Format to be used
		 * @return {string}
		 * @see $.decode
		 */
		decode: function(f) {
			if(!this.length) return '';
			if(this.length==1) return $.decode(f,_getValue(this[0]));
			var v = [];
			for(var i=0; i<this.length; ++i)
				v[i] = $.decode(f,_getValue(this[i]));
			return v;
		}
	});

	if(global.$) {
		throw new Error('Seshat: $ has already been defined');
	} else {
		var _p = $.__private__;
		if(global.onload) $.start(window.onload);
		global.$ = $;
		global.$seshat = $;
		global.onload = function() {
			for(var s=_p.start,i=0; i<s.length; ++i) (s[i])();
			_p.start = false;
		}
	}

})(typeof window === 'undefined' ? this : window);

