function getStyleObject(objectId) {
	// checkW3C DOM, then MSIE 4, then NN 4.
	if(document.getElementById && document.getElementById(objectId)) {
		return document.getElementById(objectId).style;
	}
	else if (document.all && document.all(objectId)) {  
		return document.all(objectId).style;
	} 
	else if (document.layers && document.layers[objectId]) { 
		return document.layers[objectId];
	} else {
		return false;
	}
}

function switchDiv(div_id)
{
	var obj = getStyleObject(div_id);
	if ( obj )
	{		
		if ( obj.display != 'block' )
		{
			obj.display = 'block';
		}else
		{
			obj.display = 'none';
		}
	}
}

function pausecomp(millis) 
{
	var date = new Date();
	var curDate = null;

	do { curDate = new Date(); } 
	while(curDate-date < millis);
}

var BrowserDetect = {
	init: function () {
		this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
		this.version = this.searchVersion(navigator.userAgent)
			|| this.searchVersion(navigator.appVersion)
			|| "an unknown version";
		this.OS = this.searchString(this.dataOS) || "an unknown OS";
	},
	searchString: function (data) {
		for (var i=0;i<data.length;i++)	{
			var dataString = data[i].string;
			var dataProp = data[i].prop;
			this.versionSearchString = data[i].versionSearch || data[i].identity;
			if (dataString) {
				if (dataString.indexOf(data[i].subString) != -1)
					return data[i].identity;
			}
			else if (dataProp)
				return data[i].identity;
		}
	},
	searchVersion: function (dataString) {
		var index = dataString.indexOf(this.versionSearchString);
		if (index == -1) return;
		return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
	},
	dataBrowser: [
		{ 	string: navigator.userAgent,
			subString: "OmniWeb",
			versionSearch: "OmniWeb/",
			identity: "OmniWeb"
		},
		{
			string: navigator.vendor,
			subString: "Apple",
			identity: "Safari"
		},
		{
			prop: window.opera,
			identity: "Opera"
		},
		{
			string: navigator.vendor,
			subString: "iCab",
			identity: "iCab"
		},
		{
			string: navigator.vendor,
			subString: "KDE",
			identity: "Konqueror"
		},
		{
			string: navigator.userAgent,
			subString: "Firefox",
			identity: "Firefox"
		},
		{
			string: navigator.vendor,
			subString: "Camino",
			identity: "Camino"
		},
		{		// for newer Netscapes (6+)
			string: navigator.userAgent,
			subString: "Netscape",
			identity: "Netscape"
		},
		{
			string: navigator.userAgent,
			subString: "MSIE",
			identity: "Explorer",
			versionSearch: "MSIE"
		},
		{
			string: navigator.userAgent,
			subString: "Gecko",
			identity: "Mozilla",
			versionSearch: "rv"
		},
		{ 		// for older Netscapes (4-)
			string: navigator.userAgent,
			subString: "Mozilla",
			identity: "Netscape",
			versionSearch: "Mozilla"
		}
	],
	dataOS : [
		{
			string: navigator.platform,
			subString: "Win",
			identity: "Windows"
		},
		{
			string: navigator.platform,
			subString: "Mac",
			identity: "Mac"
		},
		{
			string: navigator.platform,
			subString: "Linux",
			identity: "Linux"
		}
	]

};
BrowserDetect.init();

function browserWarn( text )
{
	document.write( '<div id="browserWarn">'+text+'</div>' );
}

document.onscroll = function ()
{
	if ( screenHeight+document.body.scrollTop >= 900 )
	{
		document.body.scrollTop = 900-screenHeight;
	}
	if ( screenWidth+document.body.scrollLeft >= 1208 )
	{
		document.body.scrollLeft = 1208-screenWidth;
	}
}

function calculateScreen ()
{
	try
	{
		screenWidth = window.innerWidth;
		screenHeight = window.innerHeight;
	}catch ( err )
	{
		screenWidth = document.body.offsetWidth;
		screenHeight = document.body.offsetHeight;
	}
	
	try
	{
		dd.elements[ 'blackDim' ].resizeTo( screenWidth, screenHeight );
	}catch ( err )
	{
	}
}

function screenResize()
{
	d = screenWidth;
	mod = ( screenWidth < 1208 ) ? 1208-screenWidth : 0;
	aye = ( screenWidth > 1208 ) ? true : false;
	
	calculateScreen();
	
	d = d-screenWidth;
	d += mod;
	
	if ( screenWidth < 1208 )
	{
		d -= (1208-screenWidth);
	}
	
	if ( aye || screenWidth > 1208 )
	{
		//alert( d );
		if ( directories != undefined )
		{
			for ( j = 0; j < directories.length; j++ )
			{
				for ( i = 0; i < dd.elements[ 'dir_'+j ].children.length; i++ )
				{
					dd.elements[ 'dir_'+j ].children[ i ].moveBy( d/2, 0 );	
				}
			}
		}
		
		dd.elements[ 'workArea2' ].moveBy( -d/2, 0 );
	}
	
	document.body.scrollTop = 0;
	
	if ( screenHeight < 840 || screenWidth < 1208 )
	{
		try
		{
			document.body.style.overflow = 'auto';
		}catch( err )
		{
		}
	}else
	{
		try
		{
			document.body.style.overflow = 'hidden';
		}catch( err )
		{
		}
	}
}

calculateScreen();

var heldKey = 0;

function keyDown( e )
{
	if ( window.event )
	{
		heldKey = window.event.keyCode;
	}else if ( e )
	{
		heldKey = e.which;
	}
}

function keyUp( e )
{
	heldKey = 0;
}


document.onkeydown = keyDown;
document.onkeyup = keyUp;
if ( document.layers != undefined )
{
	document.captureEvents( Event.KEYDOWN );
	document.captureEvents( Event.KEYUP );
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function flickerOff( object, target )
{
	for ( j = 0; j < 2; j++ )
	{
		for ( i = 0; i < opacities.length; i++ )
		{
			try
			{
				object.setOpacity( opacities[ i ] );
			}catch( err )
			{
				if ( typeof object.style.opacity == 'string' )
				{
					object.style.opacity = opacities[ i ];
				}else
				{
					object.filters.alpha.opacity = opacities[ i ]*100;
				}
			}
			pausecomp( 5 );
		}
		for ( i = opacities.length-1; i >= 0; i-- )
		{
			try
			{
				object.setOpacity( opacities[ i ] );
			}catch( err )
			{
				if ( typeof object.style.opacity == 'string' )
				{
					object.style.opacity = opacities[ i ];
				}else
				{
					object.filters.alpha.opacity = opacities[ i ]*100;
				}
			}
			pausecomp( 5 );
		}
	}
	
	if ( typeof( target ) != 'undefined' )
	{
		try
		{
			object.setOpacity( target );
		}catch( err )
		{
			if ( typeof object.style.opacity == 'string' )
			{
				object.style.opacity = target;
			}else
			{
				object.filters.alpha.opacity = target*100;
			}
		}
	}
}

function flickerOn( object, target )
{
	for ( j = 0; j < 2; j++ )
	{
		for ( i = 0; i < opacities.length; i++ )
		{
			try
			{
				object.setOpacity( opacities[ i ] );
			}catch( err )
			{
				if ( typeof object.style.opacity == 'string' )
				{
					object.style.opacity = opacities[ i ];
				}else
				{
					object.filters.alpha.opacity = opacities[ i ]*100;
				}
			}
			pausecomp( 11 );
		}
		for ( i = opacities.length-1; i >= 0; i-- )
		{
			try
			{
				object.setOpacity( opacities[ i ] );
			}catch( err )
			{
				if ( typeof object.style.opacity == 'string' )
				{
					object.style.opacity = opacities[ i ];
				}else
				{
					object.filters.alpha.opacity = opacities[ i ]*100;
				}
			}
			pausecomp( 11 );
		}
	}
	for ( i = 0; i < opacities.length; i++ )
	{
		try
		{
			object.setOpacity( opacities[ i ] );
		}catch( err )
		{
			if ( typeof object.style.opacity == 'string' )
			{
				object.style.opacity = opacities[ i ];
			}else
			{
				object.filters.alpha.opacity = opacities[ i ]*100;
			}
		}
		pausecomp( 11 );
	}
	
	if ( typeof( target ) != 'undefined' )
	{
		try
		{
			object.setOpacity( target );
		}catch( err )
		{
			if ( typeof object.style.opacity == 'string' )
			{
				object.style.opacity = target;
			}else
			{
				object.filters.alpha.opacity = target*100;
			}
		}
	}
}


var opacities = new Array( 0.15, 0.20, 0.25, 0.30, 0.35, 0.40, 0.45, 0.50, 0.55, 0.60, 0.65, 0.70, 0.75, 0.80, 0.85, 0.90, 0.95, 1.0 );