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
 * @file This script does one thing only, and that is generating
 * QR-codes. It was originally based on the jQuery script
 * (https://larsjung.de/jquery-qrcode/), and acknowledgements go to
 * Lars Jung. However, much is now rewritten and extended with
 * additional functionality.
 * 
 * Included in this script is also "QR Code Generator for JavaScript"
 * (Copyright (c) 2009 Kazuhiko Arase, URL: http://www.d-project.com/).
 * 
 * This script is part of the Seshat framework.
 * 
 * @author Per Löwgren
 * @file resource/seshat-qrcode-1.1.js  
 * @version 1.1
 * @date Modified: 2015-04-18
 * @date Created: 2014-08-03
 */

(function($) {

	// Wrapper for the original QR code generator.
	function QRCode(text,level,version,quiet) {
		// `qrcode` is the single public function that will be defined by the `QR Code Generator`
		// at the end of the file.
		var qr = qrcode(version,level);
		qr.addData(text);
		qr.make();

		quiet = quiet || 0;

		var qrModuleCount = qr.getModuleCount(),
			quietModuleCount = qr.getModuleCount() + 2*quiet,
			isDark = function(row,col) {
				row -= quiet;
				col -= quiet;
				if(row<0 || row>=qrModuleCount || col<0 || col>=qrModuleCount) return false;
				return qr.isDark(row,col);
			},
			addBlank = function(l,t,r,b) {
				var prevIsDark = this.isDark,moduleSize = 1/quietModuleCount;
				this.isDark = function(row,col) {
					var ml = col * moduleSize,
						mt = row * moduleSize,
						mr = ml + moduleSize,
						mb = mt + moduleSize;
					return prevIsDark(row,col) && (l>mr || ml>r || t>mb || mt>b);
				};
			};

		this.text = text;
		this.level = level;
		this.version = version;
		this.size = qrModuleCount;
		this.moduleCount = quietModuleCount;
		this.isDark = isDark;
		this.addBlank = addBlank;
	};

	// Check if canvas is available in the browser (as Modernizr does)
	var canvasAvailable = (function () {
		var elem = document.createElement('canvas');
		return !!(elem.getContext && elem.getContext('2d'));
	}());

	var arcToAvailable = Object.prototype.toString.call(window.opera) !== '[object Opera]';

	// Returns a minimal QR code for the given text starting with version `minVersion`.
	// Returns `null` if `text` is too long to be encoded in `maxVersion`.
	function createQRCode(text,level,version,quiet) {
		version = version<1? 1 : (version>10? 10 : version);
		for(version; version<=10; ++version) {
			try { return new QRCode(text,level,version,quiet); } catch(err) {}
		}
	};

	function drawBackground(qr,context,settings) {
		if(!settings.background) return;
		if($(settings.background).is('<img>')) {
			context.drawImage(settings.background,0,0,settings.size,settings.size);
		} else {
			context.fillStyle = settings.background;
			context.fillRect(settings.left,settings.top,settings.size,settings.size);
		}
	};

	function drawLabel(qr,context,settings) {
		if(!settings.label) return;
		var l = settings.label,sz = settings.size,ps = sz/qr.size,sx = l.position.x,sy = l.position.y,sw,sh;
		if(l.text) {
			var ctx = $.create('canvas').getContext('2d');
			ctx.font = l.font.style;
			var w = ctx.measureText(l.text).width;
			sh = l.font.size/sz,sw = w/sz;
$.log('sx: '+sx+', sy: '+sy+', w: '+w+', sw: '+sw+', sh: '+sh+', sz: '+sz+', font: "'+ctx.font+'"');
		} else if(l.image) {
			var w = l.image.naturalWidth || 1,h = l.image.naturalHeight || 1;
			sh = l.size,sw = sh*w/h;
		}

		var sl = (1-sw)*sx,st = (1-sh)*sy,sr = sl+sw,sb = st+sh;
		var p = l.padding,pl = p.left*ps/sz,pt = p.top*ps/sz,pr = p.right*ps/sz,pb = p.bottom*ps/sz;
		sx = sl;
		sy = st;
		if(l.mode==='strip') sl = 0,st -= pt,sr = sz,sb += pb,sw = sz,sh += pt+pb;
		else if(l.mode==='box') sl -= pl,st -= pt,sr += pr,sb += pb,sw += pl+pr,sh += pt+pb;
		qr.addBlank(sl,st,sr,sb);

		if(l.text) {
			context.fillStyle = l.font.color;
			context.font = l.font.style;
			context.fillText(l.text,sx*sz,sy*sz+0.75*l.font.size);
		} else if(l.image) {
			context.drawImage(l.image,sx*sz,sy*sz,sw*sz,sh*sz);
		}
		if(l.border) {
			var w = l.border.width/2;
			context.lineWidth = l.border.width;
			context.strokeStyle = l.border.color;
			context.strokeRect(sl*sz,st*sz,sw*sz,sh*sz);
		}
	};

	function drawModuleDefault(qr,context,settings,left,top,width,row,col) {
		if(qr.isDark(row,col)) context.rect(left,top,width,width);
	};

	function drawModuleRoundedDark(ctx,l,t,r,b,rad,nw,ne,se,sw) {
		if(nw) ctx.moveTo(l+rad,t);
		else ctx.moveTo(l,t);
		if(ne) {
			ctx.lineTo(r-rad,t);
			ctx.arcTo(r,t,r,b,rad);
		} else ctx.lineTo(r,t);
		if(se) {
			ctx.lineTo(r,b-rad);
			ctx.arcTo(r,b,l,b,rad);
		} else ctx.lineTo(r,b);
		if(sw) {
			ctx.lineTo(l+rad,b);
			ctx.arcTo(l,b,l,t,rad);
		} else ctx.lineTo(l,b);
		if(nw) {
			ctx.lineTo(l,t+rad);
			ctx.arcTo(l,t,r,t,rad);
		} else ctx.lineTo(l,t);
	};

	function drawModuleRoundendLight(ctx,l,t,r,b,rad,nw,ne,se,sw) {
		if(nw) {
			ctx.moveTo(l+rad,t);
			ctx.lineTo(l,t);
			ctx.lineTo(l,t+rad);
			ctx.arcTo(l,t,l+rad,t,rad);
		}
		if(ne) {
			ctx.moveTo(r-rad,t);
			ctx.lineTo(r,t);
			ctx.lineTo(r,t+rad);
			ctx.arcTo(r,t,r-rad,t,rad);
		}
		if(se) {
			ctx.moveTo(r-rad,b);
			ctx.lineTo(r,b);
			ctx.lineTo(r,b-rad);
			ctx.arcTo(r,b,r-rad,b,rad);
		}
		if(sw) {
			ctx.moveTo(l+rad,b);
			ctx.lineTo(l,b);
			ctx.lineTo(l,b-rad);
			ctx.arcTo(l,b,l+rad,b,rad);
		}
	};

	function drawModuleRounded(qr,context,settings,l,t,w,row,col) {
		var isDark = qr.isDark,
			r = l+w,b = t+w,
			radius = settings.radius*w,
			rowT = row-1,rowB = row+1,colL = col-1,colR = col+1,
			center = isDark(row,col),
			nw = isDark(rowT,colL),north = isDark(rowT,col),ne = isDark(rowT,colR),east = isDark(row,colR),
			se = isDark(rowB,colR),south = isDark(rowB,col),sw = isDark(rowB,colL),west = isDark(row,colL);
		if(center) drawModuleRoundedDark(context,l,t,r,b,radius,!north && !west,!north && !east,!south && !east,!south && !west);
		else drawModuleRoundendLight(context,l,t,r,b,radius,north && west && nw,north && east && ne,south && east && se,south && west && sw);
	};

	function drawModules(qr,context,settings) {
		var moduleCount = qr.moduleCount,
			moduleSize = settings.size/moduleCount,
			fn = drawModuleDefault,row,col,l,t,w;
		if(arcToAvailable && settings.radius>0 && settings.radius<=0.5) fn = drawModuleRounded;
		context.beginPath();
		for(row=0; row<moduleCount; ++row)
			for(col=0; col<moduleCount; ++col) {
				l = settings.left+col*moduleSize;
				t = settings.top+row*moduleSize;
				w = moduleSize;
				fn(qr,context,settings,l,t,w,row,col);
			}
		if($(settings.color).is('<img>')) {
			context.strokeStyle = 'rgba(0,0,0,0.5)';
			context.lineWidth = 2;
			context.stroke();
			var prev = context.globalCompositeOperation;
			context.globalCompositeOperation = "destination-out";
			context.fill();
			context.globalCompositeOperation = prev;
			context.clip();
			context.drawImage(settings.color,0,0,settings.size,settings.size);
			context.restore();
		} else {
			context.fillStyle = settings.color;
			context.fill();
		}
	};

	// Creates an anchor for the QR-element, when clicking on it.
	function createAnchor(elem,settings) {
		var a = $($.create('a',{href: settings.href}));
		a.append(elem);
		return a;
	};

	// Draws QR code to the given `canvas` and returns it.
	function drawOnCanvas(canvas,qr,settings) {
		if(!qr) return null;
		var c = $(canvas).data('qrcode',qr),context = c.first().getContext('2d');
		drawBackground(qr,context,settings);
		drawLabel(qr,context,settings);
		drawModules(qr,context,settings);
		if(settings.href) return createAnchor(c,settings);
		else return c;
	};

	// Returns a `canvas` element representing the QR code for the given settings.
	function createCanvas(qr,settings) {
		var c = $($.create('canvas',{width: settings.size,height: settings.size}));
		return drawOnCanvas(c,qr,settings);
	};

	// Returns an `image` element representing the QR code for the given settings.
	function createImage(settings) {
		return $($.create('img',{src: createCanvas(settings).first().toDataURL('image/png')}));
	};

	// Returns a `div` element representing the QR code for the given settings.
	function createDiv(settings) {
		if(!qr) return null;
		// some shortcuts to improve compression
		var sz = settings.size,mn = qr.moduleCount,ms = Math.floor(settings.size/mn),
			offset = Math.floor(0.5*(settings.size-ms*mn)),y,x,
			containerCSS = { position: 'relative',left: 0,top: 0,padding: 0,margin: 0,width: sz+'px',height: sz+'px' },
			darkCSS = { position: 'absolute',padding: 0,margin: 0,left: 0,top: 0,width: ms+'px',height: ms+'px',backgroundColor: settings.color },
			attr = { style: darkCSS },div;
		if(settings.background) containerCSS.backgroundColor = settings.background;
		div = $($.create('div',{ style: containerCSS })).data('qrcode',qr);
		for(y=0; y<mn; ++y)
			for(x=0; x<mn; ++x)
				if(qr.isDark(y,x)) {
					darkCSS.left = (offset+x*ms)+'px';
					darkCSS.top = (offset+y*ms)+'px';
					$.create('div',attr,div);
				}
		return div;
	};

	function createHTML(qr,settings) {
		var elem;
		if(canvasAvailable && settings.render==='canvas') elem = createCanvas(qr,settings);
		else if(canvasAvailable && settings.render==='image') elem = createImage(qr,settings);
		else elem = createDiv(qr,settings);
		if(settings.href) return createAnchor(elem,settings);
		else return elem;
	};

	$.implement({
		/**
		 * @summary Create a QR-code
		 * 
		 * @description Generate a QR-code by calling qrcode on a $-element:
		 *     $('#id').qrcode({text:'qrtext'});
		 * 
		 * Parameters that can be used when calling qrcode:
		 * + render [string]: render method: canvas, image or div (default canvas)
		 * + version [integer]: version range somewhere in 1 .. 10 (default 1)
		 * + level [string]: error correction level: L, M, Q or H (default L)
		 * + left [integer]: offset in pixel if drawn onto existing canvas (default 0)
		 * + top [integer]: offset in pixel if drawn onto existing canvas (default 0)
		 * + size [integer]: size in pixels, if 1 .. 10 is size of dots, otherwise size of entire code (default 1)
		 * + color [string|element]: code color or image element (default #000)
		 * + background [string|element]: background color or image element, omit for transparent background
		 * + text [string]: content (default 'empty')
		 * + radius [float]: corner radius relative to module width: 0.0 .. 0.5 (default 0.0)
		 * + quiet [integer]: quiet zone in modules (default 0)
		 * + label [array]: insert a label inside of qr-code (see below)
		 * + href [string]: make so that clicking on the code links to the href-URL
		 * 
		 * Label parameters:
		 * + mode [string]: modes: normal, strip, box (default normal)
		 * + position [array: x,y]: position of label relative to size of qr 0.0 .. 1.0 (default x: 0.5,y: 0.5)
		 * + padding [array: x,y,left,top,right,bottom]: padding of label in dots (default x: 0,y: 0)
		 * + border [array: width,color]: draw a border around the label
		 * + text [string]: text for label
		 * + font [array: name,size,weight,color]: font style for text label (default name: 'Sans',size: 12,weight: 'bold',color: '#000')
		 * + image [element]: image to draw as label
		 * + size [integer]: image size
		 * 
		 * @memberof $selection.prototype
		 * @param {Object} options Options for how to render the QR-code (see above)
		 * @return {$selection}
		 */
		qrcode: function(options) {
			var defaults = { render: 'canvas',version: 1,level: 'L',left: 0,top: 0,size: 1,color: '#000',text: 'empty',radius: 0,quiet: 0 };
			var settings = $.extend({},defaults,options);
			var qr = createQRCode(settings.text,settings.level,settings.version,settings.quiet);
//$.log("Settings size: "+settings.size+", QR size: "+qr.size);
			if(settings.size<qr.size*4+17)
				settings.size = settings.size<=10? qr.size*settings.size : qr.size;
			if(settings.label) {
				var l = settings.label,p,b,f;
				if(l.position===undefined) l.position = {};
				p = l.position;
				if(p.x===undefined) p.x = 0.5;
				if(p.y===undefined) p.y = 0.5;
				if(!l.padding) l.padding = {};
				p = l.padding;
				if(!p.x) p.x = 0;
				if(!p.y) p.y = 0;
				if(p.left===undefined) p.left = p.x;
				if(p.top===undefined) p.top = p.y;
				if(p.right===undefined) p.right = p.x;
				if(p.bottom===undefined) p.bottom = p.y;
				if(l.border) {
					b = l.border;
					if(b.width===undefined) b.width = 1;
					if(b.color===undefined) b.color = '#000';
				}
				if(l.text) {
					if(l.font===undefined) l.font = {};
					f = l.font;
					if(f.name===undefined) f.name = 'Sans';
					if(f.size===undefined) f.size = 12;
					if(f.weight===undefined) f.weight = 'bold';
					if(f.color===undefined) f.color = '#000';
					l.font.style = l.font.weight+' '+l.font.size+'px '+l.font.name;
				}
			}
//$.log("Settings size: "+settings.size+", QR size: "+qr.size);
			return this.each(function () {
				if(this.tagName==='canvas') drawOnCanvas(this,qr,settings);
				else $(this).append(createHTML(qr,settings));
			});
		}
	});


	//---------------------------------------------------------------------
	//
	// QR Code Generator for JavaScript
	//
	// Copyright (c) 2009 Kazuhiko Arase
	//
	// URL: http://www.d-project.com/
	//
	// Licensed under the MIT license:
	//	http://www.opensource.org/licenses/mit-license.php
	//
	// The word 'QR Code' is registered trademark of
	// DENSO WAVE INCORPORATED
	//	http://www.denso-wave.com/qrcode/faqpatent-e.html
	//
	//---------------------------------------------------------------------

	var qrcode = function() {

		//---------------------------------------------------------------------
		// qrcode
		//---------------------------------------------------------------------

		/**
		 * qrcode
		 * @param typeNumber 1 to 10
		 * @param errorCorrectLevel 'L','M','Q','H'
		 */
		var qrcode = function(typeNumber, errorCorrectLevel) {

			var PAD0 = 0xEC;
			var PAD1 = 0x11;

			var _typeNumber = typeNumber;
			var _errorCorrectLevel = QRErrorCorrectLevel[errorCorrectLevel];
			var _modules = null;
			var _moduleCount = 0;
			var _dataCache = null;
			var _dataList = new Array();

			var _this = {};

			var makeImpl = function(test, maskPattern) {

				_moduleCount = _typeNumber * 4 + 17;
				_modules = function(moduleCount) {
					var modules = new Array(moduleCount);
					for (var row = 0; row < moduleCount; row += 1) {
						modules[row] = new Array(moduleCount);
						for (var col = 0; col < moduleCount; col += 1) {
							modules[row][col] = null;
						}
					}
					return modules;
				}(_moduleCount);

				setupPositionProbePattern(0, 0);
				setupPositionProbePattern(_moduleCount - 7, 0);
				setupPositionProbePattern(0, _moduleCount - 7);
				setupPositionAdjustPattern();
				setupTimingPattern();
				setupTypeInfo(test, maskPattern);

				if (_typeNumber >= 7) {
					setupTypeNumber(test);
				}

				if (_dataCache == null) {
					_dataCache = createData(_typeNumber, _errorCorrectLevel, _dataList);
				}

				mapData(_dataCache, maskPattern);
			};

			var setupPositionProbePattern = function(row, col) {

				for (var r = -1; r <= 7; r += 1) {

					if (row + r <= -1 || _moduleCount <= row + r) continue;

					for (var c = -1; c <= 7; c += 1) {

						if (col + c <= -1 || _moduleCount <= col + c) continue;

						if ( (0 <= r && r <= 6 && (c == 0 || c == 6) )
								|| (0 <= c && c <= 6 && (r == 0 || r == 6) )
								|| (2 <= r && r <= 4 && 2 <= c && c <= 4) ) {
							_modules[row + r][col + c] = true;
						} else {
							_modules[row + r][col + c] = false;
						}
					}
				}
			};

			var getBestMaskPattern = function() {

				var minLostPoint = 0;
				var pattern = 0;

				for (var i = 0; i < 8; i += 1) {

					makeImpl(true, i);

					var lostPoint = QRUtil.getLostPoint(_this);

					if (i == 0 || minLostPoint > lostPoint) {
						minLostPoint = lostPoint;
						pattern = i;
					}
				}

				return pattern;
			};

			var setupTimingPattern = function() {

				for (var r = 8; r < _moduleCount - 8; r += 1) {
					if (_modules[r][6] != null) {
						continue;
					}
					_modules[r][6] = (r % 2 == 0);
				}

				for (var c = 8; c < _moduleCount - 8; c += 1) {
					if (_modules[6][c] != null) {
						continue;
					}
					_modules[6][c] = (c % 2 == 0);
				}
			};

			var setupPositionAdjustPattern = function() {

				var pos = QRUtil.getPatternPosition(_typeNumber);

				for (var i = 0; i < pos.length; i += 1) {

					for (var j = 0; j < pos.length; j += 1) {

						var row = pos[i];
						var col = pos[j];

						if (_modules[row][col] != null) {
							continue;
						}

						for (var r = -2; r <= 2; r += 1) {

							for (var c = -2; c <= 2; c += 1) {

								if (r == -2 || r == 2 || c == -2 || c == 2
										|| (r == 0 && c == 0) ) {
									_modules[row + r][col + c] = true;
								} else {
									_modules[row + r][col + c] = false;
								}
							}
						}
					}
				}
			};

			var setupTypeNumber = function(test) {

				var bits = QRUtil.getBCHTypeNumber(_typeNumber);

				for (var i = 0; i < 18; i += 1) {
					var mod = (!test && ( (bits >> i) & 1) == 1);
					_modules[Math.floor(i / 3)][i % 3 + _moduleCount - 8 - 3] = mod;
				}

				for (var i = 0; i < 18; i += 1) {
					var mod = (!test && ( (bits >> i) & 1) == 1);
					_modules[i % 3 + _moduleCount - 8 - 3][Math.floor(i / 3)] = mod;
				}
			};

			var setupTypeInfo = function(test, maskPattern) {

				var data = (_errorCorrectLevel << 3) | maskPattern;
				var bits = QRUtil.getBCHTypeInfo(data);

				// vertical
				for (var i = 0; i < 15; i += 1) {

					var mod = (!test && ( (bits >> i) & 1) == 1);

					if (i < 6) {
						_modules[i][8] = mod;
					} else if (i < 8) {
						_modules[i + 1][8] = mod;
					} else {
						_modules[_moduleCount - 15 + i][8] = mod;
					}
				}

				// horizontal
				for (var i = 0; i < 15; i += 1) {

					var mod = (!test && ( (bits >> i) & 1) == 1);

					if (i < 8) {
						_modules[8][_moduleCount - i - 1] = mod;
					} else if (i < 9) {
						_modules[8][15 - i - 1 + 1] = mod;
					} else {
						_modules[8][15 - i - 1] = mod;
					}
				}

				// fixed module
				_modules[_moduleCount - 8][8] = (!test);
			};

			var mapData = function(data, maskPattern) {

				var inc = -1;
				var row = _moduleCount - 1;
				var bitIndex = 7;
				var byteIndex = 0;
				var maskFunc = QRUtil.getMaskFunction(maskPattern);

				for (var col = _moduleCount - 1; col > 0; col -= 2) {

					if (col == 6) col -= 1;

					while (true) {

						for (var c = 0; c < 2; c += 1) {

							if (_modules[row][col - c] == null) {

								var dark = false;

								if (byteIndex < data.length) {
									dark = ( ( (data[byteIndex] >>> bitIndex) & 1) == 1);
								}

								var mask = maskFunc(row, col - c);

								if (mask) {
									dark = !dark;
								}

								_modules[row][col - c] = dark;
								bitIndex -= 1;

								if (bitIndex == -1) {
									byteIndex += 1;
									bitIndex = 7;
								}
							}
						}

						row += inc;

						if (row < 0 || _moduleCount <= row) {
							row -= inc;
							inc = -inc;
							break;
						}
					}
				}
			};

			var createBytes = function(buffer, rsBlocks) {

				var offset = 0;

				var maxDcCount = 0;
				var maxEcCount = 0;

				var dcdata = new Array(rsBlocks.length);
				var ecdata = new Array(rsBlocks.length);

				for (var r = 0; r < rsBlocks.length; r += 1) {

					var dcCount = rsBlocks[r].dataCount;
					var ecCount = rsBlocks[r].totalCount - dcCount;

					maxDcCount = Math.max(maxDcCount, dcCount);
					maxEcCount = Math.max(maxEcCount, ecCount);

					dcdata[r] = new Array(dcCount);

					for (var i = 0; i < dcdata[r].length; i += 1) {
						dcdata[r][i] = 0xff & buffer.getBuffer()[i + offset];
					}
					offset += dcCount;

					var rsPoly = QRUtil.getErrorCorrectPolynomial(ecCount);
					var rawPoly = qrPolynomial(dcdata[r], rsPoly.getLength() - 1);

					var modPoly = rawPoly.mod(rsPoly);
					ecdata[r] = new Array(rsPoly.getLength() - 1);
					for (var i = 0; i < ecdata[r].length; i += 1) {
						var modIndex = i + modPoly.getLength() - ecdata[r].length;
						ecdata[r][i] = (modIndex >= 0)? modPoly.getAt(modIndex) : 0;
					}
				}

				var totalCodeCount = 0;
				for (var i = 0; i < rsBlocks.length; i += 1) {
					totalCodeCount += rsBlocks[i].totalCount;
				}

				var data = new Array(totalCodeCount);
				var index = 0;

				for (var i = 0; i < maxDcCount; i += 1) {
					for (var r = 0; r < rsBlocks.length; r += 1) {
						if (i < dcdata[r].length) {
							data[index] = dcdata[r][i];
							index += 1;
						}
					}
				}

				for (var i = 0; i < maxEcCount; i += 1) {
					for (var r = 0; r < rsBlocks.length; r += 1) {
						if (i < ecdata[r].length) {
							data[index] = ecdata[r][i];
							index += 1;
						}
					}
				}

				return data;
			};

			var createData = function(typeNumber, errorCorrectLevel, dataList) {

				var rsBlocks = QRRSBlock.getRSBlocks(typeNumber, errorCorrectLevel);

				var buffer = qrBitBuffer();

				for (var i = 0; i < dataList.length; i += 1) {
					var data = dataList[i];
					buffer.put(data.getMode(), 4);
					buffer.put(data.getLength(), QRUtil.getLengthInBits(data.getMode(), typeNumber) );
					data.write(buffer);
				}

				// calc num max data.
				var totalDataCount = 0;
				for (var i = 0; i < rsBlocks.length; i += 1) {
					totalDataCount += rsBlocks[i].dataCount;
				}

				if (buffer.getLengthInBits() > totalDataCount * 8) {
					throw new Error('code length overflow. ('
						+ buffer.getLengthInBits()
						+ '>'
						+ totalDataCount * 8
						+ ')');
				}

				// end code
				if (buffer.getLengthInBits() + 4 <= totalDataCount * 8) {
					buffer.put(0, 4);
				}

				// padding
				while (buffer.getLengthInBits() % 8 != 0) {
					buffer.putBit(false);
				}

				// padding
				while (true) {

					if (buffer.getLengthInBits() >= totalDataCount * 8) {
						break;
					}
					buffer.put(PAD0, 8);

					if (buffer.getLengthInBits() >= totalDataCount * 8) {
						break;
					}
					buffer.put(PAD1, 8);
				}

				return createBytes(buffer, rsBlocks);
			};

			_this.addData = function(data) {
				var newData = qr8BitByte(data);
				_dataList.push(newData);
				_dataCache = null;
			};

			_this.isDark = function(row, col) {
				if (row < 0 || _moduleCount <= row || col < 0 || _moduleCount <= col) {
					throw new Error(row + ',' + col);
				}
				return _modules[row][col];
			};

			_this.getModuleCount = function() {
				return _moduleCount;
			};

			_this.make = function() {
				makeImpl(false, getBestMaskPattern() );
			};

			return _this;
		};

		//---------------------------------------------------------------------
		// qrcode.stringToBytes
		//---------------------------------------------------------------------

		qrcode.stringToBytes = function(s) {
			var bytes = new Array();
			for (var i = 0; i < s.length; i += 1) {
				var c = s.charCodeAt(i);
				bytes.push(c & 0xff);
			}
			return bytes;
		};

		//---------------------------------------------------------------------
		// QRMode
		//---------------------------------------------------------------------

		var QRMode = {
			MODE_NUMBER :		1 << 0,
			MODE_ALPHA_NUM : 	1 << 1,
			MODE_8BIT_BYTE : 	1 << 2,
			MODE_KANJI :		1 << 3
		};

		//---------------------------------------------------------------------
		// QRErrorCorrectLevel
		//---------------------------------------------------------------------

		var QRErrorCorrectLevel = {
			L : 1,
			M : 0,
			Q : 3,
			H : 2
		};

		//---------------------------------------------------------------------
		// QRMaskPattern
		//---------------------------------------------------------------------

		var QRMaskPattern = {
			PATTERN000 : 0,
			PATTERN001 : 1,
			PATTERN010 : 2,
			PATTERN011 : 3,
			PATTERN100 : 4,
			PATTERN101 : 5,
			PATTERN110 : 6,
			PATTERN111 : 7
		};

		//---------------------------------------------------------------------
		// QRUtil
		//---------------------------------------------------------------------

		var QRUtil = function() {

			var PATTERN_POSITION_TABLE = [
				[],
				[6, 18],
				[6, 22],
				[6, 26],
				[6, 30],
				[6, 34],
				[6, 22, 38],
				[6, 24, 42],
				[6, 26, 46],
				[6, 28, 50],
				[6, 30, 54],
				[6, 32, 58],
				[6, 34, 62],
				[6, 26, 46, 66],
				[6, 26, 48, 70],
				[6, 26, 50, 74],
				[6, 30, 54, 78],
				[6, 30, 56, 82],
				[6, 30, 58, 86],
				[6, 34, 62, 90],
				[6, 28, 50, 72, 94],
				[6, 26, 50, 74, 98],
				[6, 30, 54, 78, 102],
				[6, 28, 54, 80, 106],
				[6, 32, 58, 84, 110],
				[6, 30, 58, 86, 114],
				[6, 34, 62, 90, 118],
				[6, 26, 50, 74, 98, 122],
				[6, 30, 54, 78, 102, 126],
				[6, 26, 52, 78, 104, 130],
				[6, 30, 56, 82, 108, 134],
				[6, 34, 60, 86, 112, 138],
				[6, 30, 58, 86, 114, 142],
				[6, 34, 62, 90, 118, 146],
				[6, 30, 54, 78, 102, 126, 150],
				[6, 24, 50, 76, 102, 128, 154],
				[6, 28, 54, 80, 106, 132, 158],
				[6, 32, 58, 84, 110, 136, 162],
				[6, 26, 54, 82, 110, 138, 166],
				[6, 30, 58, 86, 114, 142, 170]
			];
			var G15 = (1 << 10) | (1 << 8) | (1 << 5) | (1 << 4) | (1 << 2) | (1 << 1) | (1 << 0);
			var G18 = (1 << 12) | (1 << 11) | (1 << 10) | (1 << 9) | (1 << 8) | (1 << 5) | (1 << 2) | (1 << 0);
			var G15_MASK = (1 << 14) | (1 << 12) | (1 << 10) | (1 << 4) | (1 << 1);

			var _this = {};

			var getBCHDigit = function(data) {
				var digit = 0;
				while (data != 0) {
					digit += 1;
					data >>>= 1;
				}
				return digit;
			};

			_this.getBCHTypeInfo = function(data) {
				var d = data << 10;
				while (getBCHDigit(d) - getBCHDigit(G15) >= 0) {
					d ^= (G15 << (getBCHDigit(d) - getBCHDigit(G15) ) );
				}
				return ( (data << 10) | d) ^ G15_MASK;
			};

			_this.getBCHTypeNumber = function(data) {
				var d = data << 12;
				while (getBCHDigit(d) - getBCHDigit(G18) >= 0) {
					d ^= (G18 << (getBCHDigit(d) - getBCHDigit(G18) ) );
				}
				return (data << 12) | d;
			};

			_this.getPatternPosition = function(typeNumber) {
				return PATTERN_POSITION_TABLE[typeNumber - 1];
			};

			_this.getMaskFunction = function(maskPattern) {

				switch (maskPattern) {

				case QRMaskPattern.PATTERN000 :
					return function(i, j) { return (i + j) % 2 == 0; };
				case QRMaskPattern.PATTERN001 :
					return function(i, j) { return i % 2 == 0; };
				case QRMaskPattern.PATTERN010 :
					return function(i, j) { return j % 3 == 0; };
				case QRMaskPattern.PATTERN011 :
					return function(i, j) { return (i + j) % 3 == 0; };
				case QRMaskPattern.PATTERN100 :
					return function(i, j) { return (Math.floor(i / 2) + Math.floor(j / 3) ) % 2 == 0; };
				case QRMaskPattern.PATTERN101 :
					return function(i, j) { return (i * j) % 2 + (i * j) % 3 == 0; };
				case QRMaskPattern.PATTERN110 :
					return function(i, j) { return ( (i * j) % 2 + (i * j) % 3) % 2 == 0; };
				case QRMaskPattern.PATTERN111 :
					return function(i, j) { return ( (i * j) % 3 + (i + j) % 2) % 2 == 0; };

				default :
					throw new Error('bad maskPattern:' + maskPattern);
				}
			};

			_this.getErrorCorrectPolynomial = function(errorCorrectLength) {
				var a = qrPolynomial([1], 0);
				for (var i = 0; i < errorCorrectLength; i += 1) {
					a = a.multiply(qrPolynomial([1, QRMath.gexp(i)], 0) );
				}
				return a;
			};

			_this.getLengthInBits = function(mode, type) {

				if (1 <= type && type < 10) {

					// 1 - 9

					switch(mode) {
					case QRMode.MODE_NUMBER 	: return 10;
					case QRMode.MODE_ALPHA_NUM 	: return 9;
					case QRMode.MODE_8BIT_BYTE	: return 8;
					case QRMode.MODE_KANJI		: return 8;
					default :
						throw new Error('mode:' + mode);
					}

				} else if (type < 27) {

					// 10 - 26

					switch(mode) {
					case QRMode.MODE_NUMBER 	: return 12;
					case QRMode.MODE_ALPHA_NUM 	: return 11;
					case QRMode.MODE_8BIT_BYTE	: return 16;
					case QRMode.MODE_KANJI		: return 10;
					default :
						throw new Error('mode:' + mode);
					}

				} else if (type < 41) {

					// 27 - 40

					switch(mode) {
					case QRMode.MODE_NUMBER 	: return 14;
					case QRMode.MODE_ALPHA_NUM	: return 13;
					case QRMode.MODE_8BIT_BYTE	: return 16;
					case QRMode.MODE_KANJI		: return 12;
					default :
						throw new Error('mode:' + mode);
					}

				} else {
					throw new Error('type:' + type);
				}
			};

			_this.getLostPoint = function(qrcode) {

				var moduleCount = qrcode.getModuleCount();

				var lostPoint = 0;

				// LEVEL1

				for (var row = 0; row < moduleCount; row += 1) {
					for (var col = 0; col < moduleCount; col += 1) {

						var sameCount = 0;
						var dark = qrcode.isDark(row, col);

						for (var r = -1; r <= 1; r += 1) {

							if (row + r < 0 || moduleCount <= row + r) {
								continue;
							}

							for (var c = -1; c <= 1; c += 1) {

								if (col + c < 0 || moduleCount <= col + c) {
									continue;
								}

								if (r == 0 && c == 0) {
									continue;
								}

								if (dark == qrcode.isDark(row + r, col + c) ) {
									sameCount += 1;
								}
							}
						}

						if (sameCount > 5) {
							lostPoint += (3 + sameCount - 5);
						}
					}
				};

				// LEVEL2

				for (var row = 0; row < moduleCount - 1; row += 1) {
					for (var col = 0; col < moduleCount - 1; col += 1) {
						var count = 0;
						if (qrcode.isDark(row, col) ) count += 1;
						if (qrcode.isDark(row + 1, col) ) count += 1;
						if (qrcode.isDark(row, col + 1) ) count += 1;
						if (qrcode.isDark(row + 1, col + 1) ) count += 1;
						if (count == 0 || count == 4) {
							lostPoint += 3;
						}
					}
				}

				// LEVEL3

				for (var row = 0; row < moduleCount; row += 1) {
					for (var col = 0; col < moduleCount - 6; col += 1) {
						if (qrcode.isDark(row, col)
								&& !qrcode.isDark(row, col + 1)
								&&  qrcode.isDark(row, col + 2)
								&&  qrcode.isDark(row, col + 3)
								&&  qrcode.isDark(row, col + 4)
								&& !qrcode.isDark(row, col + 5)
								&&  qrcode.isDark(row, col + 6) ) {
							lostPoint += 40;
						}
					}
				}

				for (var col = 0; col < moduleCount; col += 1) {
					for (var row = 0; row < moduleCount - 6; row += 1) {
						if (qrcode.isDark(row, col)
								&& !qrcode.isDark(row + 1, col)
								&&  qrcode.isDark(row + 2, col)
								&&  qrcode.isDark(row + 3, col)
								&&  qrcode.isDark(row + 4, col)
								&& !qrcode.isDark(row + 5, col)
								&&  qrcode.isDark(row + 6, col) ) {
							lostPoint += 40;
						}
					}
				}

				// LEVEL4

				var darkCount = 0;

				for (var col = 0; col < moduleCount; col += 1) {
					for (var row = 0; row < moduleCount; row += 1) {
						if (qrcode.isDark(row, col) ) {
							darkCount += 1;
						}
					}
				}

				var ratio = Math.abs(100 * darkCount / moduleCount / moduleCount - 50) / 5;
				lostPoint += ratio * 10;

				return lostPoint;
			};

			return _this;
		}();

		//---------------------------------------------------------------------
		// QRMath
		//---------------------------------------------------------------------

		var QRMath = function() {

			var EXP_TABLE = new Array(256);
			var LOG_TABLE = new Array(256);

			// initialize tables
			for (var i = 0; i < 8; i += 1) {
				EXP_TABLE[i] = 1 << i;
			}
			for (var i = 8; i < 256; i += 1) {
				EXP_TABLE[i] = EXP_TABLE[i - 4]
					^ EXP_TABLE[i - 5]
					^ EXP_TABLE[i - 6]
					^ EXP_TABLE[i - 8];
			}
			for (var i = 0; i < 255; i += 1) {
				LOG_TABLE[EXP_TABLE[i] ] = i;
			}

			var _this = {};

			_this.glog = function(n) {

				if (n < 1) {
					throw new Error('glog(' + n + ')');
				}

				return LOG_TABLE[n];
			};

			_this.gexp = function(n) {

				while (n < 0) {
					n += 255;
				}

				while (n >= 256) {
					n -= 255;
				}

				return EXP_TABLE[n];
			};

			return _this;
		}();

		//---------------------------------------------------------------------
		// qrPolynomial
		//---------------------------------------------------------------------

		function qrPolynomial(num, shift) {

			if (typeof num.length == 'undefined') {
				throw new Error(num.length + '/' + shift);
			}

			var _num = function() {
				var offset = 0;
				while (offset < num.length && num[offset] == 0) {
					offset += 1;
				}
				var _num = new Array(num.length - offset + shift);
				for (var i = 0; i < num.length - offset; i += 1) {
					_num[i] = num[i + offset];
				}
				return _num;
			}();

			var _this = {};

			_this.getAt = function(index) {
				return _num[index];
			};

			_this.getLength = function() {
				return _num.length;
			};

			_this.multiply = function(e) {

				var num = new Array(_this.getLength() + e.getLength() - 1);

				for (var i = 0; i < _this.getLength(); i += 1) {
					for (var j = 0; j < e.getLength(); j += 1) {
						num[i + j] ^= QRMath.gexp(QRMath.glog(_this.getAt(i) ) + QRMath.glog(e.getAt(j) ) );
					}
				}

				return qrPolynomial(num, 0);
			};

			_this.mod = function(e) {

				if (_this.getLength() - e.getLength() < 0) {
					return _this;
				}

				var ratio = QRMath.glog(_this.getAt(0) ) - QRMath.glog(e.getAt(0) );

				var num = new Array(_this.getLength() );
				for (var i = 0; i < _this.getLength(); i += 1) {
					num[i] = _this.getAt(i);
				}

				for (var i = 0; i < e.getLength(); i += 1) {
					num[i] ^= QRMath.gexp(QRMath.glog(e.getAt(i) ) + ratio);
				}

				// recursive call
				return qrPolynomial(num, 0).mod(e);
			};

			return _this;
		};

		//---------------------------------------------------------------------
		// QRRSBlock
		//---------------------------------------------------------------------

		var QRRSBlock = function() {

			var RS_BLOCK_TABLE = [

				// L
				// M
				// Q
				// H

				// 1
				[1, 26, 19],
				[1, 26, 16],
				[1, 26, 13],
				[1, 26, 9],

				// 2
				[1, 44, 34],
				[1, 44, 28],
				[1, 44, 22],
				[1, 44, 16],

				// 3
				[1, 70, 55],
				[1, 70, 44],
				[2, 35, 17],
				[2, 35, 13],

				// 4
				[1, 100, 80],
				[2, 50, 32],
				[2, 50, 24],
				[4, 25, 9],

				// 5
				[1, 134, 108],
				[2, 67, 43],
				[2, 33, 15, 2, 34, 16],
				[2, 33, 11, 2, 34, 12],

				// 6
				[2, 86, 68],
				[4, 43, 27],
				[4, 43, 19],
				[4, 43, 15],

				// 7
				[2, 98, 78],
				[4, 49, 31],
				[2, 32, 14, 4, 33, 15],
				[4, 39, 13, 1, 40, 14],

				// 8
				[2, 121, 97],
				[2, 60, 38, 2, 61, 39],
				[4, 40, 18, 2, 41, 19],
				[4, 40, 14, 2, 41, 15],

				// 9
				[2, 146, 116],
				[3, 58, 36, 2, 59, 37],
				[4, 36, 16, 4, 37, 17],
				[4, 36, 12, 4, 37, 13],

				// 10
				[2, 86, 68, 2, 87, 69],
				[4, 69, 43, 1, 70, 44],
				[6, 43, 19, 2, 44, 20],
				[6, 43, 15, 2, 44, 16]
			];

			var qrRSBlock = function(totalCount, dataCount) {
				var _this = {};
				_this.totalCount = totalCount;
				_this.dataCount = dataCount;
				return _this;
			};

			var _this = {};

			var getRsBlockTable = function(typeNumber, errorCorrectLevel) {
				switch(errorCorrectLevel) {
				case QRErrorCorrectLevel.L :
					return RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 0];
				case QRErrorCorrectLevel.M :
					return RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 1];
				case QRErrorCorrectLevel.Q :
					return RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 2];
				case QRErrorCorrectLevel.H :
					return RS_BLOCK_TABLE[(typeNumber - 1) * 4 + 3];
				default :
					return undefined;
				}
			};

			_this.getRSBlocks = function(typeNumber, errorCorrectLevel) {

				var rsBlock = getRsBlockTable(typeNumber, errorCorrectLevel);

				if (typeof rsBlock == 'undefined') {
					throw new Error('bad rs block @ typeNumber:' + typeNumber +
							'/errorCorrectLevel:' + errorCorrectLevel);
				}

				var length = rsBlock.length / 3;

				var list = new Array();

				for (var i = 0; i < length; i += 1) {

					var count = rsBlock[i * 3 + 0];
					var totalCount = rsBlock[i * 3 + 1];
					var dataCount = rsBlock[i * 3 + 2];

					for (var j = 0; j < count; j += 1) {
						list.push(qrRSBlock(totalCount, dataCount) );
					}
				}

				return list;
			};

			return _this;
		}();

		//---------------------------------------------------------------------
		// qrBitBuffer
		//---------------------------------------------------------------------

		var qrBitBuffer = function() {

			var _buffer = new Array();
			var _length = 0;

			var _this = {};

			_this.getBuffer = function() {
				return _buffer;
			};

			_this.getAt = function(index) {
				var bufIndex = Math.floor(index / 8);
				return ( (_buffer[bufIndex] >>> (7 - index % 8) ) & 1) == 1;
			};

			_this.put = function(num, length) {
				for (var i = 0; i < length; i += 1) {
					_this.putBit( ( (num >>> (length - i - 1) ) & 1) == 1);
				}
			};

			_this.getLengthInBits = function() {
				return _length;
			};

			_this.putBit = function(bit) {

				var bufIndex = Math.floor(_length / 8);
				if (_buffer.length <= bufIndex) {
					_buffer.push(0);
				}

				if (bit) {
					_buffer[bufIndex] |= (0x80 >>> (_length % 8) );
				}

				_length += 1;
			};

			return _this;
		};

		//---------------------------------------------------------------------
		// qr8BitByte
		//---------------------------------------------------------------------

		var qr8BitByte = function(data) {

			var _mode = QRMode.MODE_8BIT_BYTE;
			var _data = data;
			var _bytes = qrcode.stringToBytes(data);

			var _this = {};

			_this.getMode = function() {
				return _mode;
			};

			_this.getLength = function(buffer) {
				return _bytes.length;
			};

			_this.write = function(buffer) {
				for (var i = 0; i < _bytes.length; i += 1) {
					buffer.put(_bytes[i], 8);
				}
			};

			return _this;
		};

		//=====================================================================
		// GIF Support etc.
		//

		//---------------------------------------------------------------------
		// byteArrayOutputStream
		//---------------------------------------------------------------------

		var byteArrayOutputStream = function() {

			var _bytes = new Array();

			var _this = {};

			_this.writeByte = function(b) {
				_bytes.push(b & 0xff);
			};

			_this.writeShort = function(i) {
				_this.writeByte(i);
				_this.writeByte(i >>> 8);
			};

			_this.writeBytes = function(b, off, len) {
				off = off || 0;
				len = len || b.length;
				for (var i = 0; i < len; i += 1) {
					_this.writeByte(b[i + off]);
				}
			};

			_this.writeString = function(s) {
				for (var i = 0; i < s.length; i += 1) {
					_this.writeByte(s.charCodeAt(i) );
				}
			};

			_this.toByteArray = function() {
				return _bytes;
			};

			_this.toString = function() {
				var s = '';
				s += '[';
				for (var i = 0; i < _bytes.length; i += 1) {
					if (i > 0) {
						s += ',';
					}
					s += _bytes[i];
				}
				s += ']';
				return s;
			};

			return _this;
		};

		//---------------------------------------------------------------------
		// base64EncodeOutputStream
		//---------------------------------------------------------------------

		var base64EncodeOutputStream = function() {

			var _buffer = 0;
			var _buflen = 0;
			var _length = 0;
			var _base64 = '';

			var _this = {};

			var writeEncoded = function(b) {
				_base64 += String.fromCharCode(encode(b & 0x3f) );
			};

			var encode = function(n) {
				if (n < 0) {
					// error.
				} else if (n < 26) {
					return 0x41 + n;
				} else if (n < 52) {
					return 0x61 + (n - 26);
				} else if (n < 62) {
					return 0x30 + (n - 52);
				} else if (n == 62) {
					return 0x2b;
				} else if (n == 63) {
					return 0x2f;
				}
				throw new Error('n:' + n);
			};

			_this.writeByte = function(n) {

				_buffer = (_buffer << 8) | (n & 0xff);
				_buflen += 8;
				_length += 1;

				while (_buflen >= 6) {
					writeEncoded(_buffer >>> (_buflen - 6) );
					_buflen -= 6;
				}
			};

			_this.flush = function() {

				if (_buflen > 0) {
					writeEncoded(_buffer << (6 - _buflen) );
					_buffer = 0;
					_buflen = 0;
				}

				if (_length % 3 != 0) {
					// padding
					var padlen = 3 - _length % 3;
					for (var i = 0; i < padlen; i += 1) {
						_base64 += '=';
					}
				}
			};

			_this.toString = function() {
				return _base64;
			};

			return _this;
		};

		//---------------------------------------------------------------------
		// base64DecodeInputStream
		//---------------------------------------------------------------------

		var base64DecodeInputStream = function(str) {

			var _str = str;
			var _pos = 0;
			var _buffer = 0;
			var _buflen = 0;

			var _this = {};

			_this.read = function() {

				while (_buflen < 8) {

					if (_pos >= _str.length) {
						if (_buflen == 0) {
							return -1;
						}
						throw new Error('unexpected end of file./' + _buflen);
					}

					var c = _str.charAt(_pos);
					_pos += 1;

					if (c == '=') {
						_buflen = 0;
						return -1;
					} else if (c.match(/^\s$/) ) {
						// ignore if whitespace.
						continue;
					}

					_buffer = (_buffer << 6) | decode(c.charCodeAt(0) );
					_buflen += 6;
				}

				var n = (_buffer >>> (_buflen - 8) ) & 0xff;
				_buflen -= 8;
				return n;
			};

			var decode = function(c) {
				if (0x41 <= c && c <= 0x5a) {
					return c - 0x41;
				} else if (0x61 <= c && c <= 0x7a) {
					return c - 0x61 + 26;
				} else if (0x30 <= c && c <= 0x39) {
					return c - 0x30 + 52;
				} else if (c == 0x2b) {
					return 62;
				} else if (c == 0x2f) {
					return 63;
				} else {
					throw new Error('c:' + c);
				}
			};

			return _this;
		};

		//---------------------------------------------------------------------
		// gifImage (B/W)
		//---------------------------------------------------------------------

		var gifImage = function(width, height) {

			var _width = width;
			var _height = height;
			var _data = new Array(width * height);

			var _this = {};

			_this.setPixel = function(x, y, pixel) {
				_data[y * _width + x] = pixel;
			};

			_this.write = function(out) {

				//---------------------------------
				// GIF Signature

				out.writeString('GIF87a');

				//---------------------------------
				// Screen Descriptor

				out.writeShort(_width);
				out.writeShort(_height);

				out.writeByte(0x80); // 2bit
				out.writeByte(0);
				out.writeByte(0);

				//---------------------------------
				// Global Color Map

				// black
				out.writeByte(0x00);
				out.writeByte(0x00);
				out.writeByte(0x00);

				// white
				out.writeByte(0xff);
				out.writeByte(0xff);
				out.writeByte(0xff);

				//---------------------------------
				// Image Descriptor

				out.writeString(',');
				out.writeShort(0);
				out.writeShort(0);
				out.writeShort(_width);
				out.writeShort(_height);
				out.writeByte(0);

				//---------------------------------
				// Local Color Map

				//---------------------------------
				// Raster Data

				var lzwMinCodeSize = 2;
				var raster = getLZWRaster(lzwMinCodeSize);

				out.writeByte(lzwMinCodeSize);

				var offset = 0;

				while (raster.length - offset > 255) {
					out.writeByte(255);
					out.writeBytes(raster, offset, 255);
					offset += 255;
				}

				out.writeByte(raster.length - offset);
				out.writeBytes(raster, offset, raster.length - offset);
				out.writeByte(0x00);

				//---------------------------------
				// GIF Terminator
				out.writeString(';');
			};

			var bitOutputStream = function(out) {

				var _out = out;
				var _bitLength = 0;
				var _bitBuffer = 0;

				var _this = {};

				_this.write = function(data, length) {

					if ( (data >>> length) != 0) {
						throw new Error('length over');
					}

					while (_bitLength + length >= 8) {
						_out.writeByte(0xff & ( (data << _bitLength) | _bitBuffer) );
						length -= (8 - _bitLength);
						data >>>= (8 - _bitLength);
						_bitBuffer = 0;
						_bitLength = 0;
					}

					_bitBuffer = (data << _bitLength) | _bitBuffer;
					_bitLength = _bitLength + length;
				};

				_this.flush = function() {
					if (_bitLength > 0) {
						_out.writeByte(_bitBuffer);
					}
				};

				return _this;
			};

			var getLZWRaster = function(lzwMinCodeSize) {

				var clearCode = 1 << lzwMinCodeSize;
				var endCode = (1 << lzwMinCodeSize) + 1;
				var bitLength = lzwMinCodeSize + 1;

				// Setup LZWTable
				var table = lzwTable();

				for (var i = 0; i < clearCode; i += 1) {
					table.add(String.fromCharCode(i) );
				}
				table.add(String.fromCharCode(clearCode) );
				table.add(String.fromCharCode(endCode) );

				var byteOut = byteArrayOutputStream();
				var bitOut = bitOutputStream(byteOut);

				// clear code
				bitOut.write(clearCode, bitLength);

				var dataIndex = 0;

				var s = String.fromCharCode(_data[dataIndex]);
				dataIndex += 1;

				while (dataIndex < _data.length) {

					var c = String.fromCharCode(_data[dataIndex]);
					dataIndex += 1;

					if (table.contains(s + c) ) {

						s = s + c;

					} else {

						bitOut.write(table.indexOf(s), bitLength);

						if (table.size() < 0xfff) {

							if (table.size() == (1 << bitLength) ) {
								bitLength += 1;
							}

							table.add(s + c);
						}

						s = c;
					}
				}

				bitOut.write(table.indexOf(s), bitLength);

				// end code
				bitOut.write(endCode, bitLength);

				bitOut.flush();

				return byteOut.toByteArray();
			};

			var lzwTable = function() {

				var _map = {};
				var _size = 0;

				var _this = {};

				_this.add = function(key) {
					if (_this.contains(key) ) {
						throw new Error('dup key:' + key);
					}
					_map[key] = _size;
					_size += 1;
				};

				_this.size = function() {
					return _size;
				};

				_this.indexOf = function(key) {
					return _map[key];
				};

				_this.contains = function(key) {
					return typeof _map[key] != 'undefined';
				};

				return _this;
			};

			return _this;
		};

		//---------------------------------------------------------------------
		// returns qrcode function.

		return qrcode;
	}();

})($seshat);

