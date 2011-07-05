<?php

/**
*     defines the ACP_flexibase class
*     @file                ACP_flexibase.php
*     @see ACP_flexibase
*/
/**
* ACP advanced settings :P
*     @class		   ACP_flexibase
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.2
*     @since        26th January 2007
*     @package		     flexibase
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// var explanation
// debug :: debug flag

// class creation
$vars = array(  );
$visible = array(  );
eval( Varloader::createclass( 'ACP_flexibase', $vars, $visible ) );
// end class creation

class ACP_flexibase extends ACP_flexibase_def
{
	/**
	* constructor
	*/
	function ACP_flexibase(  )
	{
		global $Cl_root_path, $lang_loader, $basic_gui, $security;
			
		// load the language and copy it over to the gui
		$this->lang = $lang_loader->get_lang( 'ACP_flexibase' );
		
		// sidebar stuff :P
		$URL1 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_flexibase&s=bases' );
		$URL2 = $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_flexibase&s=items' );
		$basic_gui->add2sidebar( 'left', $this->lang[ 'Sidebar_title' ], '<span class="gen"><a href="' . $URL1 . '">' . $this->lang[ 'Sidebar_manage' ] . '</a><br /><a href="' . $URL2 . '">' . $this->lang[ 'Sidebar_add' ] . '</a></span>' );
	}
	/**
	* decides what panel to show according to the URL
	*/
	function show_panel()
	{
		global $errors, $template;
		
		// get the mode
		$s = ( isset( $_GET[ 's' ] ) ) ? strval( $_GET[ 's' ] ) : '';
		
		// fire the template
		$template->assign_files( array(
			'ACP_flexibase' => 'ACP/flexibase' . tplEx
		) );
		
		// act upon it
		switch( $s )
		{
			case 'bases':
				$this->manage_panel();
				break;
			case 'bases2':
				$this->submitted();
				break;
			case 'items':
				$this->items();
				break;
			case 'items2':
				$this->items_subm();
				break;
			default:
				$errors->report_error( $this->lang[ 'Wrong_mode' ], CRITICAL_ERROR );
				break;
		}
	}
	/**
	* does the stuff needed to display the panel for managing databases
	*/
	function manage_panel()
	{
		global $db, $errors, $template, $security, $mod_loader, $lang_loader, $basic_gui, $Cl_root_path;
		
		$bases = $this->baseselect();
		$selbase = $bases[ 1 ];
		$bases = $bases[ 0 ];
		
		// get and set up list of langauges
		$langs = $lang_loader->get_langlist();
		foreach ( $langs as $i => $lang )
		{
			$name = ( isset( $this->lang[ $lang ] ) ) ? $this->lang[ $lang ] : $lang;
			$sel = ( $selbase[ 'language' ] == $lang ) ? 'selected' : '';
			$langs[ $i ] = '<option value="' . $lang . '" ' . $sel . '>' . $name . '</option>';
		}
		$langs = '<select name="language">' . implode( "\t\n", $langs ) . '</select>';
		
		// get the editor
		$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
		$mod_loader->port_vars( array( 'name' => 'description', 'quickpost' => TRUE, 'def_text' => stripslashes( $selbase[ 'description' ] ) ) );
		$mod_loader->execute_modules( 0, 'show_editor' );
		$desc = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
		
		// prepare the htmls for the fieldlist
		$tool = $basic_gui->make_tooltip( '<b style="color: red">' . $this->lang[ 'Field_delwarn' ] . '</b>', 'buttontip' );
		$field = '<input type="text" value="%s" name="title[%s]" /> <b>:</b> <select name="type[%s]"><option>' . $this->lang[ 'Field_type' ] . '</option>%s</select>';
		$delfield = '<label for="delete[%s]"><b> ' . $this->lang[ 'Field_del' ] . ': </b></label><input type="checkbox" value="1" id="delete[%s]" name="delete[%s]" ' . $tool . ' />';
		
		if ( isset( $_POST[ 'flexibase' ] ) && $_POST[ 'flexibase' ] != 'new' )
		{ // create the list of fields
			// parse it
			$fields = '';
			$index = 0;
			foreach ( explode( ':::', $selbase[ 'fieldlist' ] ) as $fd )
			{
				$fd = explode( '::', $fd );
				$fields .= sprintf( $field, $fd[ 0 ], $index, $index, $this->typesel( $i, $fd[ 1 ] ) ) . sprintf( $delfield, $index, $index, $index ) . "<br />\n";
				$index++;
			}
		}else
		{ // no fields
			$fields = '';
			$index = 0;
		}
		// add an empty one
		$fields .= sprintf( $field, $this->lang[ 'Field_name' ], $index, $index, $this->typesel( $index ) );
		$fields .= ( isset( $_POST[ 'flexibase' ] ) && $_POST[ 'flexibase' ] != 'new' ) ? ' <input type="submit" value="' . $this->lang[ 'Field_new' ] . '" name="iaddfields" /><br />' : '<br />';
		
		$template->assign_block_vars( 'manage', '', array(
			'S_LIST' => $bases,
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_flexibase&s=bases2' ),
			'S_NAME' => $selbase[ 'name' ],
			'S_LANGUAGE' => $langs,
			'S_DESCRIPTION' => $desc[ 'editor_HTML' ],
			'S_FIELDS' => $fields,
			
			'T_DELETEME' => $basic_gui->make_tooltip( '<b style="color: red">' . $this->lang[ 'Manage_delwarn' ] . '</b>', 'buttontip' ),
			
			'L_TITLE' => $this->lang[ 'Manage_title' ],
			'L_EXPLAIN' => $this->lang[ 'Manage_explain' ],
			'L_SELECT' => $this->lang[ 'Manage_select' ],
			'L_DATABASE' => $this->lang[ 'Manage_database' ],
			'L_NAME' => $this->lang[ 'Manage_name' ],
			'L_LANGUAGE' => $this->lang[ 'Manage_language' ],
			'L_DESCRIPTION' => $this->lang[ 'Manage_description' ],
			'L_FIELDS' => $this->lang[ 'Manage_fields' ],
			'L_DELETEME' => $this->lang[ 'Manage_delete' ],
		) );
		$template->assign_switch( 'manage', TRUE );
	}
	/**
	* returns the insides of a select for field types with proper selections made
	*/
	function typesel( $index, $what = '' )
	{
		if ( !isset( $this->types ) )
		{ // have to define the available types
			$types = array();
			foreach ( array( 'varchar', 'text', 'int', 'float', 'blob' ) as $type )
			{
				$types[ ] = array( '<option value="' . $type . '"' , '>' . $this->lang[ 'Type_' . $type ] . '</option>', $type );
			}
			$this->types = $types;
		}
		$ret = '';
		foreach ( $this->types as $type )
		{
			$sel = ( $type[ 2 ] == $what ) ? 'selected' : '';
			$ret .= "\t" . $type[ 0 ] . $sel . $type[ 1 ];
		}
		return $ret;
	}
	/**
	* this performs what's needed to change stuff
	*/
	function submitted()
	{
		global $errors, $db, $config_class;
		
		if ( isset( $_POST[ 'iselectdbs' ] ) )
		{ // just a select was performed
			$this->manage_panel();
			return;
		}
		if ( isset( $_POST[ 'iaddfields' ] ) )
		{
			$this->add_field();
			return;
		}
		if ( !isset( $_POST[ 'imakedbs' ] ) )
		{ // just no
			$errors->report_error( $this->lang[ 'Wrong_form' ], GENERAL_ERROR );
		}
		
		if ( $_POST[ 'flexibase' ] == 'new' )
		{ // a completely new thing
			// prepare the data
			$name = $_POST[ 'name' ];
			$lang = $_POST[ 'language' ];
			$description = str_replace( '&nbsp;', ' ', $_POST[ 'description' ] );
			
			$fieldlist1 = '';
			$fieldlist2 = '';
			for ( $i = 0; $i < count( $_POST[ 'type' ] ); $i++ )
			{
				if ( $_POST[ 'title' ][ $i ] == $this->lang[ 'Field_name' ] || $_POST[ 'type' ][ $i ] == $this->lang[ 'Field_type' ] )
				{
					$errors->report_error( 'Set the field', GENERAL_ERROR );
				}
				$fieldlist2 .= '`' . preg_replace( "#[^A-Za-z0-9]#", ' ', $_POST[ 'title' ][ $i ] ) . '` ';
				$type = $this->typeparse( $_POST[ 'type' ][ $i ] );
				$fieldlist2 .= $type . ' NOT NULL, ';
				
				$fieldlist1 .= ( empty( $fieldlist1 ) ) ? '' : ':::';
				$fieldlist1 .= preg_replace( "#[^A-Za-z0-9]#", ' ', $_POST[ 'title' ][ $i ] ) . '::' . $_POST[ 'type' ][ $i ];
			}
			
			// make the sql
			$sql = "INSERT INTO " . FLEXIBASE_TABLE . " ( name, language, description, fieldlist )VALUES( '$name', '$lang', '$description', '$fieldlist1' )";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t insert into database', CRITICAL_ERROR );
			}
			
			// fetch the id back
			$sql ="SELECT max(id) AS lastid FROM " . FLEXIBASE_TABLE;
			$db->sql_query( $sql );
			$lastid = $db->sql_fetchfield( 'lastid' );
			
			// now create the table for the data
			$sql = 'CREATE TABLE `' . FLEXIBASE_TABLE . "_$lastid` (
				`id` INT NOT NULL AUTO_INCREMENT ,
				$fieldlist2
				INDEX ( `id` ) 
				) ENGINE = MYISAM ;";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t insert into database', CRITICAL_ERROR );
			}
			
			// add it to the menu
			if ( !$config_class->add_config( 'menu' . cfgEx, "\$main[ 'flexibase' ][ 'sub' ][] = array( 'title' => '$name', 'URL' => '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=$lastid', 'lang' => '$lang' );" ) )
			{
				$errors->report_error( 'Couldn\'t add to menu', CRITICAL_ERROR );
			}
		}else
		{ // just change stuff
			// prepare the data
			$id = $_POST[ 'flexibase' ];
			$name = $_POST[ 'name' ];
			$lang = $_POST[ 'language' ];
			$description = str_replace( '&nbsp;', ' ', $_POST[ 'description' ] );
			
			if ( $_POST[ 'deleteme' ] ) 
			{ // the whole thing needs to be killed
				$sql = "DELETE FROM " . FLEXIBASE_TABLE . " WHERE id='$id'";
				if ( !$result = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Couldn\'t delete from database', CRITICAL_ERROR );
				}
				$sql = "DROP TABLE " . FLEXIBASE_TABLE . "_$id";
				if ( !$result = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Couldn\'t delete from database', CRITICAL_ERROR );
				}
				// apparently all went well
				$errors->report_error( $this->lang[ 'Finished' ], MESSAGE );
			}
			
			// get the old fieldlist
			$sql = "SELECT name, fieldlist FROM " . FLEXIBASE_TABLE . " WHERE id='$id'";
			$db->sql_query( $sql );
			$fields = $db->sql_fetchfield( 'fieldlist' );
			$fields = explode( ':::', $fields );
			$oldname = $db->sql_fetchfield( 'name' );
			
			// remove the last one
			array_pop( $_POST[ 'type' ] );
			array_pop( $_POST[ 'title' ] );
			
			$fieldlist = '';
			$change = "ALTER TABLE " . FLEXIBASE_TABLE . "_$id ";
			for ( $i = 0; $i < count( $_POST[ 'type' ] ); $i++ )
			{ // make the fieldlist and also create the sqls to change the table
				if ( !$_POST[ 'delete' ][ $i ] )
				{
					if ( $_POST[ 'title' ][ $i ] == $this->lang[ 'Field_name' ] || $_POST[ 'type' ][ $i ] == $this->lang[ 'Field_type' ] )
					{
						$errors->report_error( 'Set the field', GENERAL_ERROR );
					}
					$fieldlist .= ( empty( $fieldlist ) ) ? '' : ':::';
					$fieldlist .= preg_replace( "#[^A-Za-z0-9]#", ' ', $_POST[ 'title' ][ $i ] ) . '::' . $_POST[ 'type' ][ $i ];
				}
				
				$change .= $this->tablealter( $fields, preg_replace( "#[^A-Za-z0-9]#", ' ', $_POST[ 'title' ][ $i ], $_POST[ 'type' ][ $i ] ), $i, FALSE, $_POST[ 'delete' ][ $i ] );
			}
			$change = substr( $change, 0, -1 );
			
			// make the sql
			$sql = "UPDATE " . FLEXIBASE_TABLE . " SET name='$name', language='$lang', description='$description', fieldlist='$fieldlist' WHERE id='$id'";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t update database', CRITICAL_ERROR );
			}
			
			// commit the change
			if ( !$result = $db->sql_query( $change ) )
			{
				$errors->report_error( 'Couldn\'t update database', CRITICAL_ERROR );
			}
			
			// change the meu if needed
			if ( $name != $oldname )
			{
				$old = "\$main[ 'flexibase' ][ 'sub' ][] = array( 'title' => '$oldname', 'URL' => '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=$id', 'lang' => '$lang' );";
				$new = "\$main[ 'flexibase' ][ 'sub' ][] = array( 'title' => '$name', 'URL' => '?' . MODE_URL . '=flexibase&' . SUBMODE_URL . '=list&baseid=$id', 'lang' => '$lang' );";
				if ( !$config_class->add_config( 'menu' . cfgEx, $old, $new ) )
				{
					$errors->report_error( 'Couldn\'t change menu', CRITICAL_ERROR );
				}
			}
		}
		// apparently all went well
		$errors->report_error( $this->lang[ 'Finished' ], MESSAGE );
	}
	/**
	* this creates the right alter table sql for specific fields
	*/
	function tablealter( $fields, $name, $type, $index, $new, $delete = FALSE )
	{
		if ( !isset( $this->alterfields ) )
		{ // if first time then parse the fields, later the first argument will be ignored
			$names = array();
			$types = array();
			foreach ( $fields as $i => $field )
			{
				$field = explode( '::', $field );
				$names[] = $field[ 0 ];
				$types[] = $field[ 1 ];
			}
			$this->alterfields = array( 'names' => $names, 'types' => $types );
		}
		if ( $delete )
		{
			return "DROP `$name`,";
		}elseif ( !$new )
		{ // it exists
			if ( $type != $this->alterfields[ 'types' ][ $index ] || $name != $this->alterfields[ 'names' ][ $index ] )
			{ // somehow different
				$n = $this->alterfields[ 'names' ][ $index ];
				return "CHANGE `$n` `$name` " . $this->typeparse( $type ) . " NOT NULL,";
			}
		}else
		{ // just add it
			return "ADD `$name` " . $this->typeparse( $type ) . " NOT NULL,";
		}
	}
	/**
	* returns the sqled type
	*/
	function typeparse( $type )
	{
		switch ( $type )
		{
			case 'varchar':
				return 'VARCHAR(255)';
			case 'text':
				return 'TEXT';
			case 'int':
				return 'INT';
			case 'float':
				return 'FLOAT';
			case 'blob':
				return 'LONGBLOB';
		}
	}
	/**
	* small function to add a field :)
	*/
	function add_field()
	{
		global $errors, $db;
		
		// fetch fieldlist
		$id = $_POST[ 'flexibase' ];
		$sql = "SELECT fieldlist FROM " . FLEXIBASE_TABLE . " WHERE id='$id'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Can\'t read database', CRITICAL_ERROR );
		}
		$fieldlist = $db->sql_fetchfield( 'fieldlist' );
		
		// add the new to the list and insert
		$name = preg_replace( "#[^A-Za-z0-9]#", ' ',  $_POST[ 'title' ][ count( $_POST[ 'title' ] )-1 ] );
		$type = $_POST[ 'type' ][ count( $_POST[ 'type' ] )-1 ];
		if ( $name == $this->lang[ 'Field_name' ] || $type == $this->lang[ 'Field_type' ] )
		{
			$errors->report_error( 'Set the field', GENERAL_ERROR );
		}
		// see if already exists
		if ( strpos( $fieldlist, $name ) !== FALSE || $name == 'flexibase' || strpos( $name, ':' ) !== FALSE )
		{ // oh crud
			$errors->report_error( sprintf( $this->lang[ 'Field_noadd' ], $name ), GENERAL_ERROR );
		}else
		{ // very nice
			$fieldlist .= ':::' . $name . '::' . $type;
		}
		
		$sql ="UPDATE " . FLEXIBASE_TABLE . " SET fieldlist='$fieldlist' WHERE id='$id'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Can\'t insert into database', CRITICAL_ERROR );
		}
		
		// add it to the table
		$sql = "ALTER TABLE " . FLEXIBASE_TABLE . "_$id " . substr( $this->tablealter( array(), $name, $type, 0, TRUE ), 0, -1 );
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Can\'t insert into database', CRITICAL_ERROR );
		}
		
		// apparently all went well
		$errors->report_error( $this->lang[ 'Finished' ], MESSAGE );
	}
	/**
	* deals with adding items
	*/
	function items( $edit = FALSE, $itemid = 0 )
	{
		global $errors, $template, $db, $mod_loader, $security;
		
		$bases = $this->baseselect( FALSE );
		$selbase = $bases[ 1 ];
		$bases = $bases[ 0 ];
		
		if ( $_POST[ 'flexibase' ] )
		{
			// if edit then the info for the post is needed
			if ( $edit )
			{
				$id = $selbase[ 'id' ];
				$sql = "SELECT * FROM " . FLEXIBASE_TABLE . "_$id WHERE id='$itemid' LIMIT 1";
				if ( !$result = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Couldn\'t read from database', CRITICAL_ERROR );
				}
				$selitem = $db->sql_fetchrow( $result );
// 				print_R( $selitem );
			}else
			{ // just set this to avoid trouble
				$selitem = array();
			}
			// make the item header and choose what to show in the list and make the adding form
			if ( strpos( $selbase[ 'fieldlist' ], 'text' ) !== FALSE )
			{ // the editor will be needed
				$mods = $mod_loader->getmodule( 'editor', MOD_FETCH_NAME, NOT_ESSENTIAL );
			}
			$itemheader = '';
			$fields = array();
			$add = array( '', '' );
			foreach ( explode( ':::', $selbase[ 'fieldlist' ] ) as $field )
			{
				$field = explode( '::', $field );
				
				// make the html for this particular field's form thing
				if ( $field[ 1 ] == 'text' )
				{
					$mod_loader->port_vars( array( 'name' => $field[ 0 ], 'quickpost' => TRUE, 'def_text' => ( isset( $selitem[ $field[ 0 ] ] ) ) ? $selitem[ $field[ 0 ] ] : ''  ) );
					$mod_loader->execute_modules( 0, 'show_editor' );
					$edit = $mod_loader->get_vars( array( 'editor_HTML', 'editor_WYSIWYG' ) );
					$add[ 1 ] .= '<b>' . $field[ 0 ] . ':</b><br /><div width="370px" height="200px">' . $edit[ 'editor_HTML' ] . '</div><br />';
				}else
				{
					switch ( $field[ 1 ] )
					{
						case 'varchar':
						case 'int':
						case 'float':
							$value = ( isset( $selitem[ $field[ 0 ] ] ) ) ? $selitem[ $field[ 0 ] ] : '';
							$add[ 0 ] .= '<b>' . $field[ 0 ] . ': </b><input type="text" name="' . $field[ 0 ] . '" value="' . $value . '" /><br />';
							$itemheader .= '<td><b>' . $field[ 0 ] . '</b></td>';
							$fields[] = $field[ 0 ];
							break;
						case 'blob':
							$add[ 0 ] .= '<b>' . $field[ 0 ] . ': </b><input type="file" name="' . $field[ 0 ] . '" /><br />';
							break;
					}
				}
			}
			$itemheader = '<td><b>ID</b></td>' . $itemheader;
			
			// make the item list
			$id = $selbase[ 'id' ];
			$showfrom = ( isset( $_POST[ 'showfrom' ] ) ) ? intval( $_POST[ 'showfrom' ] ) : 0;
			$shownum = ( isset( $_POST[ 'shownum' ] ) ) ? intval( $_POST[ 'shownum' ] ) : 30;
			
			$sql = "SELECT * FROM " . FLEXIBASE_TABLE . "_$id LIMIT $showfrom, $shownum";
			if ( !$result = $db->sql_query( $sql ) )
			{
				$errors->report_error( 'Couldn\'t read from database', CRITICAL_ERROR );
			}
			$numrows = $db->sql_numrows( $result );
			if ( $numrows != 0 )
			{
				// construct the pages selector
				$pages = '';
				$showpages = 0;
				for ( $i = 0, $d = floor( $numrows / $shownum ); $i*$d <= $numrows; $i++, $showpages++ )
				{
					if ( $showfrom > $i*$d && $showfrom < $i*$d+$d )
					{
						$pages .= '<option value="' . $i*$d . '" selected>' . $i . '</option>';
					}else
					{
						$pages .= '<option value="' . $i*$d . '">' . $i . '</option>';
					}
					if ( $d < $shownum )
					{
						break;
					}
				}
				if ( $showpages > 1 )
				{
					$showpages = TRUE;
				}else
				{
					$showpages = FALSE;
				}
				// construct the values display
				while ( $row = $db->sql_fetchrow( $result ) )
				{
					$rest = '';
					foreach ( $fields as $field )
					{
						$rest .= '<td>' . $row[ $field ] . '</td>';
					}
					$template->assign_block_vars( 'itemlist', '', array(
						'ID' => $row[ 'id' ],
						'FIELDS' => $rest,
						'S_ACTION' =>$security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_flexibase&s=edititem&id=' . $row[ 'id' ] ),
						'SHOWEDIT' => 1,
					) );
					$template->assign_switch( 'itemlist', TRUE );
				}
			}else
			{
				$pages = '<option value="0">0</option>';
				$template->assign_block_vars( 'itemlist', '', array(
						'ID' => '',
						'FIELDS' =>'<td>' .  $this->lang[ 'Item_none' ] . '</td>',
						'S_ACTION' => '',
						'SHOWEDIT' => 0,
				) );
				$template->assign_switch( 'itemlist', TRUE );
				$showpages = FALSE;
			}
		}else
		{
			// no need for this
			$add = array( '', '' );
		}
		
		$template->assign_block_vars( 'items', '', array(
			'S_LIST' => $bases,
			'S_ACTION' => $security->append_sid( '?' . MODE_URL . '=ACP&' . SUBMODE_URL . '=ACP_flexibase&s=items2' ),
			
			'L_TITLE' => $this->lang[ 'Item_title' ],
			'L_EXPLAIN' => $this->lang[ 'Item_explain' ],
			'L_DATABASE' => $this->lang[ 'Manage_database' ],
			'L_SELECT' => $this->lang[ 'Manage_select' ],
			'L_CHOOSE' => $this->lang[ 'Item_choose' ],
			'L_ADD' => $this->lang[ 'Item_add' ],
			'L_INSIDE' => $this->lang[ 'Item_inside' ],
			'L_SHOW' => $this->lang[ 'Item_show' ],
			'L_SHOWY' => $this->lang[ 'Item_showy' ],
			'L_EDIT' => $this->lang[ 'Item_edit' ],
			'L_DELETE' => $this->lang[ 'Item_delete' ],
			'L_EDITITEM' => $this->lang[ 'Item_edititem' ],
			
			'ITEMHEADER' => $itemheader,
			'ADD' => array( 'LEFT' => $add[ 0 ], 'RIGHT' => $add[ 1 ] ),
			'SHOWNUM' => $shownum,
			'SHOWFROM' => $showfrom,
			'PAGES' => $pages,
			'ITEMID' => $itemid,
			
			'SHOWEMPTY' => ( !$_POST[ 'flexibase' ] ) ? 1 : 0,
			'SHOWPAGES' => ( $showpages ) ? 1 : 0,
			'SHOWEDIT' => ( empty( $selitem ) ) ? 0 : 1,
		) );
		
		$template->assign_switch( 'items', TRUE );
	}
	/**
	* returns the select for databases
	*/
	function baseselect( $addy = TRUE )
	{
		global $errors, $db;
		
		// make the select for database choosing
		$sql = "SELECT * FROM " . FLEXIBASE_TABLE;
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Can\'t read database', CRITICAL_ERROR );
		}
		if ( $addy )
		{
			$bases = "<select name=\"flexibase\">\n\t<option value=\"new\">" . $this->lang[ 'Add_new' ] . "</option> \n";
		}else
		{
			$bases = "<select name=\"flexibase\">\n\t<option value=\"\">   </option> \n";
		}
		$selbase = array();
		while ( $base = $db->sql_fetchrow( $result ) )
		{
			if ( $_POST[ 'flexibase' ] == $base[ 'id' ] )
			{
				$sel = 'selected';
				$selbase = $base;
			}else
			{
				$sel = '';
			}
			$sel = ( $_POST[ 'flexibase' ] == $base[ 'id' ] ) ? 'selected' : '';
			$bases .= "\t<option value=\"" . $base[ 'id' ] . "\" $sel>" . $base[ 'language' ] . " :: " . $base[ 'name' ] . "</option>\n";
		}
		$bases .= '</select>';
		
		return array( $bases, $selbase );
	}
	/**
	* when items are submitted
	*/
	function items_subm()
	{
		global $db, $errors;
		
		if ( isset( $_POST[ 'iselectdbs' ] ) || isset( $_POST[ 'ichangeshow' ] ) )
		{ // just display the stuff 
			$this->items();
			return;
		}elseif ( isset( $_POST[ 'iadditems' ] ) )
		{ // item add occured
			$this->additem();
			return;
		}elseif ( isset( $_POST[ 'iedititems' ] ) )
		{ // an item wants to be editted
			$id = array_keys( $_POST[ 'iedititems' ] );
			$id = $id[ 0 ];
			$this->items( TRUE, $id );
			return;
		}elseif( isset( $_POST[ 'idoedititems' ] ) )
		{ // actual editting of the file
			$this->additem( TRUE, $_POST[ 'itemid' ] );
		}else
		{ // deleting of items has to be done :)
			$id = $_POST[ 'flexibase' ];
			foreach ( $_POST[ 'deleteitem' ] as $k => $void )
			{
				$sql = "DELETE FROM " . FLEXIBASE_TABLE . "_$id WHERE id='$k' LIMIT 1";
				if ( !$result = $db->sql_query( $sql ) )
				{
					$errors->report_error( 'Couldn\'t drop from database', CRITICAL_ERROR );
				}
			}
			// all went well aye
			$errors->report_error( $this->lang[ 'Finished' ], MESSAGE );
		}
	}
	/**
	* adds an item aye
	* or edits one if such is the requirement
	*/
	function additem( $edit = FALSE, $itemid = 0 )
	{
		global $errors, $db;
		
		// first we need some info
		$id = $_POST[ 'flexibase' ];
		$sql = "SELECT fieldlist FROM " . FLEXIBASE_TABLE . " WHERE id='$id'";
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Couldn\'t read database', CRITICAL_ERROR );
		}
		$fieldlist = $db->sql_fetchfield( 'fieldlist' );
		
		// now go through the fieldlist and make the sql for insertion
		if ( !$edit )
		{
			$sql = "INSERT INTO " . FLEXIBASE_TABLE . "_$id ( %s )VALUES( %s )";
		}else
		{
			$sql = "UPDATE " . FLEXIBASE_TABLE . "_$id SET %s WHERE id='$itemid'";
		}
		$append = array( array(), array() );
		foreach ( explode( ':::', $fieldlist ) as $field )
		{
			$field = explode( '::', $field );
			
			if ( $field[ 1 ] != 'blob' )
			{
				if ( !$edit )
				{
					$append[ 0 ][] = $field[ 0 ];
					$append[ 1 ][] = "'" . str_replace( '&nbsp;', ' ', $_POST[ $field[ 0 ] ] ) . "'";
				}else
				{
					$append[ 0 ][] = $field[ 0 ] . "='" . str_replace( '&nbsp;', ' ', $_POST[ $field[ 0 ] ] ) . "'";
				}
			}else
			{ // files need some special treatment eh
				$file = $_FILES[ $field[ 0 ] ];
				if ( $file[ 'error' ] != 4 )
				{ // there was an upload
					if ( $file[ 'error' ] != 0 )
					{ // there was an error
						$errors->report_error( sprintf( $this->lang[ 'Item_filewrong' ], $field[ 0 ] ), GENERAL_ERROR );
					}
					if ( is_uploaded_file( $file[ 'tmp_name' ] ) )
					{ // cool, use it
						if ( !$contents = @file_get_contents( $file[ 'tmp_name' ] ) )
						{ // wtf, something wrong
							$errors->report_error( sprintf( $this->lang[ 'Item_filewrong' ], $field[ 0 ] ), GENERAL_ERROR );
						}
						if ( !$edit )
						{
							$append[ 0 ][] = $field[ 0 ];
							$append[ 1 ][] = "'" . addslashes( $contents ) . "'";
						}else
						{
							$append[ 0 ][] = $field[ 0 ] . "='" . addslashes( $contents ) . "'";
						}
					}else
					{ // also an error
						$errors->report_error( sprintf( $this->lang[ 'Item_filewrong' ], $field[ 0 ] ), GENERAL_ERROR );
					}
				}
			}
		}
		if ( !$edit )
		{
			$sql = sprintf( $sql, implode( ', ', $append[ 0 ] ), implode( ', ', $append[ 1 ] ) );
		}else
		{
			$sql = sprintf( $sql, implode( ', ', $append[ 0 ] ) );
		}
		// execute the sql
		if ( !$result = $db->sql_query( $sql ) )
		{
			$errors->report_error( 'Failed inserting into database', CRITICAL_ERROR );
		}
		
		// all went well apparently
		$errors->report_error( $this->lang[ 'Finished' ], MESSAGE );
	}
	
	//
	// End of ACP_flexibase class
	//
}

?>