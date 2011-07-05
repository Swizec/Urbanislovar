var selected_file = "";
var files = new Array();
var current_dir;

function return_tree( get )
{
	br = document.getElementById( "browser_window" );
	br.innerHTML = "";
	files = new Array();
		
	for ( var i in get )
	{
		if ( i == 0 )
		{
			current_dir = get[ i ];
		}else
		{
			br.innerHTML += get[ i ][ 2 ];
			files[ get[ i ][ 0 ] ] = get[ i ][ 1 ];
		}
	}
}

function change_selected( id )
{
	if ( selected_file != "" )
	{
		document.getElementById( selected_file ).className = "browser_entry";
	}
	selected_file = id;
	document.getElementById( selected_file ).className = "browser_entry_sel";
}

function go_edit()
{
	x_shw_edit( files[ selected_file ], selected_file, show_edit );
}

function show_edit( get )
{
	document.getElementById( "edit_window" ).innerHTML = get;
}

function do_edit()
{
	x_do_edit( files[ selected_file ], document.getElementById( "edit_text" ).value, done_edit );
}

function done_edit( get )
{
	document.getElementById( "edit_window" ).innerHTML = get;
}

function go_delete()
{
	x_shw_delete( files[ selected_file ], selected_file, show_delete );
}

function show_delete( get )
{
	document.getElementById( "edit_window" ).innerHTML = get;
}

function do_delete()
{
	x_do_delete( files[ selected_file ], done_delete );
}

function done_delete( get )
{
	document.getElementById( "edit_window" ).innerHTML = get;
}

function go_download()
{
	x_shw_download( files[ selected_file ], selected_file, show_download );
}

function show_download( get )
{
	document.getElementById( "edit_window" ).innerHTML = get;
}

function go_view()
{
	x_shw_view( files[ selected_file ], selected_file, show_view );
}

function show_view( get )
{
	document.getElementById( "edit_window" ).innerHTML = get;
}

function go_upload( )
{
	x_shw_upload( current_dir, show_upload );
}

function show_upload( get )
{
	document.getElementById( 'edit_window' ).innerHTML = get;
}

// from http://www.javascripter.net/faq/creating.htm
// but edited a bit
function deleteLayer(id) {
 if (document.layers && document.layers[id]) {
  document.layers[id].visibility='hide'
  delete document.layers[id]
 }
 if (document.all && document.all[id]) {
  document.all[id].innerHTML=''
  document.all[id].outerHTML=''
 }
}

function makeLayer(id,L,T,W,H,class,visible,zIndex,content) {
 if (document.layers) {
  if (document.layers[id]) {
   deleteLayer( id );
   return
  }
  var LR=document.layers[id]=new Layer(W)
  LR.name= id
  LR.left= L
  LR.top = T
  LR.clip.height=H
  LR.visibility=(null==visible || 1==visible ? 'show' : 'hide')
  LR.className = class
  LR.document.text = content
  if(null!=zIndex)  LR.zIndex=zIndex
 }
 else if (document.all) {
  if (document.all[id]) {
   deleteLayer( id );
   return
  }
  var LR= '\n<DIV id='+id+' class='+class+' style="position:absolute'
  +'; overflow: auto'
  +'; left:'+L
  +'; top:'+T
  +'; width:'+W
  +'; height:'+H
  +'; clip:rect(0,'+W+','+H+',0)'
  +'; visibility:'+(null==visible || 1==visible ? 'visible':'hidden')
  +(null==zIndex  ? '' : '; z-index:'+zIndex)
  +'">'+ content + '</DIV>'
  document.body.insertAdjacentHTML("BeforeEnd",LR)
 }
}

function findPosX(obj)
{
var curleft = 0;
if (obj.offsetParent)
{
while (obj.offsetParent)
{
curleft += obj.offsetLeft
obj = obj.offsetParent;
}
}
else if (obj.x)
curleft += obj.x;
return curleft;
}

function findPosY(obj)
{
var curtop = 0;
if (obj.offsetParent)
{
while (obj.offsetParent)
{
curtop += obj.offsetTop
obj = obj.offsetParent;
}
}
else if (obj.y)
curtop += obj.y;
return curtop;
}

function go_rename()
{
	file = files[ selected_file ].split( '/' );
	name = file[ file.length-1 ];
	f = document.getElementById( selected_file );
	
// 	alert(  );
	fle = files[ selected_file ];
// 	val = d;
	
	makeLayer( "renaming", findPosX( f ), findPosY( f ), 300, 22, 'browser_edit', true, 1, '<input type="text" value="'+name+'" id="renaming_val"><input type="submit" onclick="do_rename();">' );
// 	makeLayer( "editing", 100, 100, 15, 15, "", true, 5, "<b>name</b>" );
}

function do_rename()
{
	x_do_rename( files[ selected_file ], document.getElementById( "renaming_val" ).value, return_rename );
}

function return_rename( get )
{
	document.getElementById( "edit_window" ).innerHTML = get;
	x_gettree( current_dir, return_tree );
	deleteLayer( "renaming" );
}