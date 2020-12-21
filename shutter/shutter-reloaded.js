/* -*- indent-tabs-mode: t; tab-width: 4; js-indent-level: 4 -*-
Shutter Reloaded for NextGEN Gallery
http://www.laptoptips.ca/javascripts/shutter-reloaded/
Version: 1.3.3
Copyright (C) 2007-2008  Andrew Ozz (Modification by Alex Rabe)
Released under the GPL, http://www.gnu.org/copyleft/gpl.html

Acknowledgement: some ideas are from: Shutter by Andrew Sutherland - http://code.jalenack.com, WordPress - http://wordpress.org, Lightbox by Lokesh Dhakar - http://www.huddletogether.com, the icons are from Crystal Project Icons, Everaldo Coelho, http://www.everaldo.com

*/

shutterOnload = function(){shutterReloaded.init('sh');}

if (typeof shutterOnload == 'function') {
	if ('undefined' != typeof jQuery) jQuery(document).ready(function(){shutterOnload();});
	else if( typeof window.onload != 'function' ) window.onload = shutterOnload;
	else {oldonld = window.onload;window.onload = function(){if(oldonld){oldonld();};shutterOnload();}};
}

shutterReloaded = {

	I : function (a) {
		return document.getElementById(a);
	},

	settings : function() {
		var s = shutterSettings;

		this.imageCount = s.imageCount || 0;
		this.msgLoading = s.msgLoading || 'L O A D I N G';
		this.msgClose = s.msgClose || 'Click to Close';
	},

	init : function (a) {
		var L, T, ext, i, m, setid, inset, shfile, shMenuPre, k, img;
		shutterLinks = {}, shutterSets = {};
		if ( 'object' != typeof shutterSettings ) shutterSettings = {};

		// If the screen orientation is defined we are in a modern mobile OS
		this.mobileOS = typeof orientation != 'undefined' ? true : false;

		for ( let i = 0; i < document.links.length; i++ ) {
			L = document.links[i];
			ext = ( L.href.indexOf('?') == -1 ) ? L.href.slice(-4).toLowerCase() : L.href.substring( 0, L.href.indexOf('?') ).slice(-4).toLowerCase();
			if ( ext != '.jpg' && ext != '.png' && ext != '.gif' && ext != 'jpeg' ) continue;
			if ( a == 'sh' && L.className.toLowerCase().indexOf('shutter') == -1 ) continue;
			if ( a == 'lb' && L.rel.toLowerCase().indexOf('lightbox') == -1 ) continue;

			if ( L.className.toLowerCase().indexOf('shutterset') != -1 )
			setid = L.className.replace(/\s/g, '_');
			else if ( L.rel.toLowerCase().indexOf('lightbox[') != -1 )
			setid = L.rel.replace(/\s/g, '_');
			else setid = 0, inset = -1;

			if( setid ) {
				if ( ! shutterSets[setid] ) shutterSets[setid] = [];
					inset = shutterSets[setid].push(i);
			}

			shfile = L.href.slice(L.href.lastIndexOf('/')+1);
			T = ( L.title && L.title != shfile ) ? L.title : '';

			shutterLinks[i] = {link:L.href,num:inset,set:setid,title:T}
			L.onclick = function () { shutterReloaded.make(i); return false; }
		}

		this.settings();
	},

	make : function(ln,fs) {
		var prev, next, prevlink = '', nextlink = '', previmg, nextimg, D, S, W, fsarg = -1, imgNum, NavBar;

		if ( ! this.Top ) {
			if ( typeof window.pageYOffset != 'undefined' ) this.Top = window.pageYOffset;
			else this.Top = (document.documentElement.scrollTop > 0) ? document.documentElement.scrollTop : document.body.scrollTop;
		}

		if ( typeof this.pgHeight == 'undefined' )
			this.pgHeight = Math.max(document.documentElement.scrollHeight,document.body.scrollHeight);

		if ( fs ) this.FS = ( fs > 0 ) ? 1 : 0;
		else this.FS = shutterSettings.FS || 0;

		if ( this.resizing ) this.resizing = null;

		// resize event if window or orientation changed (i.e. iOS)
		if (this.mobileOS)
			window.onorientationchange = function () { shutterReloaded.resize(ln); }
		else
			window.onresize = function () { shutterReloaded.resize(ln); }

		document.documentElement.style.overflowX = 'hidden';
		if ( ! this.VP ) {
			this._viewPort();
			this.VP = true;
		}

		if ( ! (S = this.I('shShutter')) ) {
			S = document.createElement('div');
			S.setAttribute('id','shShutter');
			document.getElementsByTagName('body')[0].appendChild(S);
			this.hideTags();
		}

		if ( ! (D = this.I('shDisplay')) ) {
			D = document.createElement('div');
			D.setAttribute('id','shDisplay');
			D.style.top = this.Top + 'px';
			document.getElementsByTagName('body')[0].appendChild(D);
		}

		S.style.height = this.pgHeight + 'px';

		var dv = this.textBtns ? ' | ' : '';
		if ( shutterLinks[ln].num > 1 ) {
			prev = shutterSets[shutterLinks[ln].set][shutterLinks[ln].num - 2];
			prevlink = '<a href="#" id="prevpic" onclick="shutterReloaded.make('+prev+');return false">&lt;&lt;</a>'+dv;
			previmg = new Image();
			previmg.src = shutterLinks[prev].link;
		} else {
			prevlink = '';
		}

		if ( shutterLinks[ln].num != -1 && shutterLinks[ln].num < (shutterSets[shutterLinks[ln].set].length) ) {
			next = shutterSets[shutterLinks[ln].set][shutterLinks[ln].num];
			nextlink = '<a href="#" id="nextpic" onclick="shutterReloaded.make('+next+');return false">&gt;&gt;</a>'+dv;
			nextimg = new Image();
			nextimg.src = shutterLinks[next].link;
		} else {
			nextlink = '';
		}

		imgNum = ( (shutterLinks[ln].num > 0) && this.imageCount ) ? '<div id="shCount">&nbsp;(&nbsp;' + shutterLinks[ln].num + '&nbsp;/&nbsp;' + shutterSets[shutterLinks[ln].set].length + '&nbsp;)&nbsp;</div>' : '';

		NavBar = '<div id="shTitle"><div id="shPrev">' + prevlink + '</div><div id="shNext">' + nextlink + '</div><div id="shName">' + shutterLinks[ln].title + '</div>' + imgNum + '</div>';

		D.innerHTML = '<div id="shWrap"><img src="'+shutterLinks[ln].link+'" id="shTopImg" title="' + this.msgClose + '" onload="shutterReloaded.showImg();" onclick="shutterReloaded.hideShutter();" />' + NavBar +'</div>';

		document.onkeydown = function(event){shutterReloaded.handleArrowKeys(event);};
		//Google Chrome 4.0.249.78 bug for onload attribute
		document.getElementById('shTopImg').src = shutterLinks[ln].link;

		window.setTimeout(function(){shutterReloaded.loading();},1000);
	},

	loading : function() {
		var S, WB, W;
		if ( (W = this.I('shWrap')) && W.style.visibility == 'visible' ) return;
		if ( ! (S = this.I('shShutter')) ) return;
		if ( this.I('shWaitBar') ) return;
		WB = document.createElement('div');
		WB.setAttribute('id','shWaitBar');
		WB.style.top = this.Top + 'px';
		WB.style.marginTop =(this.pgHeight/2) + 'px'
		WB.innerHTML = this.msgLoading;
		S.appendChild(WB);
	},

	hideShutter : function() {
		var D, S;
		if ( D = this.I('shDisplay') ) D.parentNode.removeChild(D);
		if ( S = this.I('shShutter') ) S.parentNode.removeChild(S);
		this.hideTags(true);
		window.scrollTo(0,this.Top);
		window.onresize = this.FS = this.Top = this.VP = null;
		document.documentElement.style.overflowX = '';
		document.onkeydown = null;
	},

	resize : function(ln) {
		if ( this.resizing ) return;
		if ( ! this.I('shShutter') ) return;
		var W = this.I('shWrap');
		if ( W ) W.style.visibility = 'hidden';

		window.setTimeout(function () { shutterReloaded.resizing = null }, 500);
		window.setTimeout(function () {
			shutterReloaded.VP = null;
			shutterReloaded.make(ln);
		}, 100);
		this.resizing = true;
	},

	_viewPort : function() {
		var wiH = window.innerHeight ? window.innerHeight : 0;
		var dbH = document.body.clientHeight ? document.body.clientHeight : 0;
		var deH = document.documentElement ? document.documentElement.clientHeight : 0;

		if( wiH > 0 ) {
			this.wHeight = ( (wiH - dbH) > 1 && (wiH - dbH) < 30 ) ? dbH : wiH;
			this.wHeight = ( (this.wHeight - deH) > 1 && (this.wHeight - deH) < 30 ) ? deH : this.wHeight;
		} else this.wHeight = ( deH > 0 ) ? deH : dbH;

		var deW = document.documentElement ? document.documentElement.clientWidth : 0;
		var dbW = window.innerWidth ? window.innerWidth : document.body.clientWidth;
		this.wWidth = ( deW > 1 ) ? deW : dbW;
	},

	showImg : function() {
		var S = this.I('shShutter'), D = this.I('shDisplay'), TI = this.I('shTopImg'), T = this.I('shTitle'), NB = this.I('shNavBar'), W, WB, wHeight, wWidth, shHeight, maxHeight, itop, mtop, resized = 0;

		if ( ! S ) return;
		if ( (W = this.I('shWrap')) && W.style.visibility == 'visible' ) return;
		if ( WB = this.I('shWaitBar') ) WB.parentNode.removeChild(WB);

		S.style.width = D.style.width = '';
		T.style.width = (TI.width - 4) + 'px';

		shHeight = this.wHeight - 50;

		if ( this.FS ) {
			if ( TI.width > (this.wWidth - 10) )
			S.style.width = D.style.width = TI.width + 10 + 'px';
			document.documentElement.style.overflowX = '';
		} else {
			window.scrollTo(0, this.Top);
			if ( TI.height > shHeight ) {
				TI.width = TI.width * (shHeight / TI.height);
				TI.height = shHeight;
				resized = 1;
			}
			if ( TI.width > (this.wWidth - 16) ) {
				TI.height = TI.height * ((this.wWidth - 16) / TI.width);
				TI.width = this.wWidth - 16;
				resized = 1;
			}
			T.style.width = (TI.width - 4) + 'px';
		}

		maxHeight = this.Top + TI.height + 10;
		if ( maxHeight > this.pgHeight ) S.style.height = maxHeight + 'px';
		window.scrollTo(0,this.Top);

		itop = (shHeight - TI.height) * 0.45;
		mtop = (itop > 3) ? Math.floor(itop) : 3;
		D.style.top = this.Top + mtop + 'px';
		W.style.visibility = 'visible';
	},

	hideTags : function(arg) {
		var sel = document.getElementsByTagName('select');
		var obj = document.getElementsByTagName('object');
		var emb = document.getElementsByTagName('embed');
		var ifr = document.getElementsByTagName('iframe');

		var vis = ( arg ) ? 'visible' : 'hidden';

		for (i = 0; i < sel.length; i++) sel[i].style.visibility = vis;
		for (i = 0; i < obj.length; i++) obj[i].style.visibility = vis;
		for (i = 0; i < emb.length; i++) emb[i].style.visibility = vis;
		for (i = 0; i < ifr.length; i++) ifr[i].style.visibility = vis;
	},

	handleArrowKeys : function(e) {
		var code = 0;
		if (!e) var e = window.event
			if (e.keyCode) code = e.keyCode;
			else if (e.which) code = e.which;

		var nextlink = document.getElementById('prevpic');
		var prevlink = document.getElementById('nextpic');
		var closelink = document.getElementById('shTopImg');

		switch (code) {
		case 39:
			if (prevlink) prevlink.onclick();
			break;
		case 37:
			if (nextlink) nextlink.onclick();
			break;
		case 27:
			if (closelink) closelink.onclick();
			break;
		 }
	}
}
