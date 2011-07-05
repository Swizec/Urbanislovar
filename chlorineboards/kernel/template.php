<?php

/**
*     defines the template class
*     @file                template.php
*     @see template
*/
/**
* Template engine for the Chlorine Boards
*     @class		   Template
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.13.33
*     @since        18th May 2005
*     @package		     ClB_base
*     @subpackage	     ClB_kernel
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// vars explanation
// template_files :: list of template files
// template_vars :: all the variables
// template_misc :: list of flags and such
// cache :: cache module
// block_hash :: references of all blocks
// forbidden_code :: class with forbidden code
// functions :: functions code
// debug :: debug mode flag
// folder :: the template folder to be used

// class creation
$vars = array( 'template_files', 'template_vars', 'template_misc', 'cache', 'block_hash', 'forbidden_code', 'functions', 'debug', 'folder', 'compiler', 'phpGlobals' );
$visible = array( 'private', 'private', 'private', 'private', 'private', 'private', 'private', 'private', 'public', 'private', 'private' );
eval( Varloader::createclass( 'template', $vars, $visible ) );
// end class creation

// the compiler needs this for some silliness with callbacks
function _TemplatePhpCallback( $matches )
{
	global $template;
	return $template->_phpCallback( $matches );
}

function _phpGlobalCallback( $matches )
{
	global $template;
	return $template->_phpGlobalCallback( $matches );
}

class Template extends template_def
{
	/**
	*  used for creating a new template object
	* @usage $template = new template( $cache, $folder );
	* @param class $cache global for the cache class
	* @param string $folder what is the base folder of the template
	* @param bool $debug debugging on or off
	*/
	function template( $cache, $folder, $debug = FALSE )
	{
		// get global vars
		global $Cl_root_path, $board_config;
		
		// get whats needed
		include( $Cl_root_path . 'kernel/forbidden_code' . phpEx );
		
		$this->template_files = array( );
		$this->template_vars = array( );
		$this->template_misc[ 'switches' ] = array( );
		$this->template_code = array( );
		$this->block_hash = array();
		$this->functions = array();
		$this->debug = $debug;
		$this->folder = $folder;
		$this->phpGlobals = array();
		
		// add modules
		$this->cache = $cache;
		$this->forbidden_code = new forbidden_code();
		
		// flag that determines behaviour
		$this->compiler = $board_config[ 'template_compiler' ];
	}
	/**
	*  used to clear everything that has been set thusfar
	* or if the block is set to clear the specific block
	*/
	function clear( $block = '' )
	{
		if ( empty( $block ) )
		{
			$this->template_files = array( );
			$this->template_vars = array( );
			$this->template_misc[ 'switches' ] = array( );
			$this->template_code = array( );
			$this->block_hash = array();
			$this->functions = array();
		}else
		{
			unset( $this->template_vars[ $block . '.' ] );
			unset( $this->template_misc[ 'switches'  ][ $block . '.' ] );
			unset( $this->block_hash[ $block ] );
		}
	}
	/**
	*  used for changing the template folder
	* @usage $template->change_folder( $Cl_root_path . $newtemp )
	* @param string $folder the new folder path
	*/
	function change_folder( $folder )
	{
		global $errors;
		
		$errors->debug_info( $this->debug, 'Template', 'change_folder', 'changing template folder from' . $this->folder . ' to ' . $folder );
		
		$this->folder = $folder . '/';
	}

	// used for retrieving the correct directory path
	// $dir = $template->get_folder()
// 	function get_folder()
// 	{
// 		global $Cl_root_path4template, $Cl_root_path;
// 		
// // 		echo $Cl_root_path4template, $this->folder ) . '||';
// 		
// 		return str_replace( $Cl_root_path, $Cl_root_path4template, $this->folder );
// // 		return $Cl_root_path4template . $this->folder;
// 	}
	/**
	*  used to find out if the handle is used
	* @usage $template->is_assignedfile( 'forums' )
	* @param string $handle the file handle to check
	* @return bool 
	*/
	function is_assignedfile( $handle )
	{
		global $errors;
		
		$errors->debug_info( $this->debug, 'Template', 'is_assignedfile', 'finding out if handle ' . $handle . ' is a used' );
		
		return isset( $this->template_files[ $handle ] );
	}
	/**
	*  used to find out if the handle is executed
	* @usage $template->is_executedfile( 'forums' )
	* @param string $handle the hangle of the file to check
	* @return bool
	*/
	function is_executedfile( $handle )
	{
		global $errors;
		
		$errors->debug_info( $this->debug, 'Template', 'is_executedfile', 'finding out if handle ' . $handle . ' has been executed' );
		
		return isset( $this->template_files[ $handle ][ 'executed' ] );
	}
	/**
	*  used for assigning template files
	* @usage $template->assign_files( array( 'handle1' => 'file1.tpl', 'handle2' => 'file2.tpl' ) );
	* @param mixed $files associative array of handles and files
	*/
	function assign_files( $files )
	{
		global $errors;
			
		// loop through the array and add
		while ( list ( $handle, $file ) = each ( $files ) )
		{
			// add path to the filename
			$file = $this->folder . $file;
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'assign_files', 'linking ' . $file . ' with ' . $handle );
			$this->template_files[ $handle ][ 'file' ] = $file;
			$this->template_files[ $handle ][ 'executed' ] = FALSE;
			$this->template_files[ $handle ][ 'compiled' ] = FALSE;
		}
	}
	/**
	* used for retrieving information about blocks
	* @usage #template->blockinfo( $block )
	* @param string $block the block name to check
	* @return mixed ( isset, count )
	*/
	function blockinfo( $block )
	{
		return array( isset( $this->template_vars[ "$block." ] ), count( $this->template_vars[ "$block." ] ) );
	}
	/**
	* internal use only
	*  used to read a file
	* @access private 
	* @usage $this->getfile( 'handle' );
	* @param string $handle the file handle to read
	* @return string the fetched code
	*/
	function _getfile( $handle, $nocompiler = FALSE )
	{
		global $errors, $userdata;
		
		$cache = $this->cache; // saves typing
		
		$file = $this->template_files[ $handle ][ 'file' ];
		
		// get the code
		if ( is_object( $cache ) )
		{
			if ( $this->compiler && !$nocompiler )
			{ // we do things a bit differently with the compiler
				$filename =  str_replace( array( $this->folder, '/' ), array( '', '.' ), $this->template_files[ $handle ][ 'file' ] );
				if ( $time = $cache->pull( "template.$filename:{$userdata[ 'user_skin' ]}.time", ESSENTIAL ) )
				{ // first good sign
					if ( filemtime( $file ) < $time )
					{ // yay, we already have this compiled
						// debug info
						$errors->debug_info( $this->debug, 'Template', '_getfile', 'reading ' . $handle . ' already compiled' );
						$this->template_files[ $handle ][ 'compiled' ] = TRUE;
						return "COMPILED";
					}
				}
				// debug info
				$errors->debug_info( $this->debug, 'Template', '_getfile', 'reading ' . $handle . ' from cache' );
				// if possible use cache
				$code = $cache->get_file( $file );
			}else
			{
				// debug info
				$errors->debug_info( $this->debug, 'Template', '_getfile', 'reading ' . $handle . ' from cache' );
				// if possible use cache
				$code = $cache->get_file( $file );
			}
		}else
		{
			// not possible :(
			if ( is_readable( $file ) )
			{
				$errors->debug_info( $this->debug, 'Template', '_getfile', 'reading ' . $handle . ' from disk' );
				// debug info
				$code = file_get_contents( $file );
			}else
			{
				// error if file couldn't be loaded
				$errors->report_error( 'The file  ' . $this->template_files[ $handle ] . ' could not be found', CRITICAL_ERROR, 'Template', '_getfile', __LINE__, ERROR_RAW );
			}
		}
		
		return $code;
	}
	/**
	*  used to output the compiled file
	* @usage $template->output( 'handle' );
	* @usage $template->output( array( 'handle1', 'handle2' ) );
	* @param string $handles single string or string array of file handles
	*/
	function output( $handles )
	{
		global $errors;
		
		$cache = $this->cache; // saves typing
		
		// make array if not already
		if ( !is_array( $handles ) )
		{
			$handles = array( $handles );
		}
		
		// do the stuff with each file
		while ( list ( $key, $handle ) = each ( $handles ) )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'output', 'fetching ' . $handle );
			// get code
			$code = $this->_getfile( $handle );
			
			if ( !isset( $code ) ) // have to check :)
			{
				// error if file couldn't be loaded
				$errors->report_error( 'The file ' . $this->template_files[ $handle ] . ' could not be found', CRITICAL_ERROR, 'Template', '_getfile', __LINE__, ERROR_RAW );
			}
			
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'output', 'compiling ' . $handle );
			$compiled = $this->compile( $code, $handle );
			
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'output', 'outputing ' . $handle );
			// output it
			echo $compiled;
			// mark it executed
			$this->template_files[ $handle ][ 'executed' ] = TRUE;
		}
	}
	/**
	*  used to fetch the compiled code of a file or to compile a string
	* @usage $compiled = $template->justcompile( 'handle' );
	* @param string $handle the file handle of the file to parse
	* @return string the compiled code
	*/
	function justcompile( $handle, $iscode = FALSE )
	{
		global $errors;
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', 'output', 'fetching ' . $handle );
		// get code
		if ( $iscode )
		{
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'justcompile', 'interpreting ' . $handle );
			return $this->_interpreter( $handle );
		}else
		{
			$code = $this->_getfile( $handle );
		}
		
		if ( !isset( $code ) ) // have to check :)
		{
			// error if file couldn't be loaded
			$errors->report_error( 'The file ' . $this->template_files[ $handle ] . ' could not be found', CRITICAL_ERROR, 'Template', '_getfile', __LINE__, ERROR_RAW );
		}
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', 'justcompile', 'compiling ' . $handle );
		$compiled = $this->compile( $code, $handle );
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', 'justcompile', 'returning ' . $handle );
		// mark it executed
		$this->template_files[ $handle ][ 'executed' ] = TRUE;
		
		// return what we got
		return $compiled;
	}
	/**
	* used to fetch values of variables
	* @usage $var = $template->value( 'VAR' )
	* @param string $var variable name
	*/
	function value( $var )
	{
		$var = preg_split( '#[.:]#', str_replace( '.', '..', $var ) );
		
		$val = $this->template_vars;
		for ( $i = 0; $i < count( $var ); $i++ )
		{
			if ( empty( $var[ $i+1 ] ) )
			{
				$key = strtolower( $var[ $i ] ) . '.';
				$i++;
			}else
			{
				$key = strtoupper( $var[ $i ] );
			}
			
			$val = $val[ $key ];
		}
		
		return ( isset( $val[ '_self_' ] ) ) ? $val[ '_self_' ] : '';
	}
	/**
	*  used to pass variables to template
	* @usage $template->assign_vars( array( 'var1' => 'val1', 'var2' => 'val2' ) );
	* @param mixed $vars associative array of names and values
	*/
	function assign_vars( $vars )
	{
		global $errors;
		
		// go through the vars and add them
		$cd = $this->_recursive_var_add( '', $vars, '$this->template_vars' );
	
		// execute the code produced
		eval( $cd );
	}
	/**
	*  recursively goes through switches and decides what to do
	* @access private
	*/
	function _recursive_show_find( $par, $rec = FALSE )
	{
		// separate this from parent
		array_pop( $par );
		
		if ( count( $par ) == 1 )
		{
			$par = implode( '.', $par );
			// only check last one
			$cnt = '$cnt = count( $this->template_misc[ \'switches\' ]' . $this->block_hash[ $par ] . ') - 1;';
			eval( $cnt );
			if ( $rec )
			{ // only return the count
				return $cnt;
			}else
			{
				// check
				$show = '$show = $this->template_misc[ \'switches\' ]' . $this->block_hash[ $par ] . '[ ' . $cnt . ' ][ \'_self_\' ];';
				eval( $show );
				return array( $show, $cnt );
			}
		}else
		{
			$cnt = $this->_recursive_show_find( $par, TRUE );
			$par = implode( '.', $par );
			// check
			$show = '$show = $this->template_misc[ \'switches\' ]' . $this->block_hash[ $par ][ $cnt ] . '[ ' . $cnt . ' ][ \'_self_\' ];';
			eval( $show );
			
			return array( $show, $cnt );
		}
	}
	/**
	*  used to make a switch visible or hidden
	* uses a recursive function to check the parent's viewability
	* @see Template::_recursive_show_find()
	* @usage $template->assign_switch( 'block', TRUE );
	* @param string $block array or single string of block names
	* @param bool $view the wanted viewability
	*/
	function assign_switch( $block, $view = TRUE )
	{
		global $errors;
		
		// make the block lowercase, for pretty
		$block = strtolower( $block );
		
		// $block MUST not be 'main' or _self_
		if ( $block == 'main' || $block == '_self_' ) return;
		
		$block = explode( '.', $block );
		$b = '$this->template_misc[ \'switches\' ]';
		$i = 0;
		$numblocks = count( $block )-1;
		while( list( $index, $Bname ) = each( $block ) )
		{
			$arr = eval( 'return ' . $b . "[ '$Bname.' ];" );
			if ( !isset( $arr ) )
			{ // if the entry doesn't exist anymore make it
				eval( $b . "[ '$Bname.' ] = Array();" );
			}
			
			if ( $i == $numblocks )
			{
				eval( $b . "[ '$Bname.' ][][ '_self_' ] = $view;" );
			}
			$b .= "[ '$Bname.' ][ count( " . $b . "[ '$Bname.' ] )-1 ]";
			$i++;
		}
	}
	/**
	*  used for adding levels to variables already set
	* @usage $template->assign_var_levels( 'block', 'VAR1', '', array( 'var2' => 'val2', 'var3' => 'val3' ) );
	* @param string $block array or single string of parent blocks
	* @param string $var the variable to add to
	* @param string $add associative aray or single string of variables to add
	* @param string $itera what iteration of the loop to add to
	*/
	function assign_var_levels( $block, $var, $add, $itera = 'last' )
	{
		global $errors;
		
		// var must not be _self_
		if ( $var == '_self_' )
		{
			return;
		}
		
		$errors->debug_info( $this->debug, 'Template', 'assign_var_levels', 'assigning variables to ' . $block );
		
		// set the var and simply call the correct var setting directive :)
		$var = array( $var => $add );
		if ( !empty( $block ) )
		{
			$this->assign_block_vars( $block, $itera, $var );
		}else
		{
			$this->assign_vars( $var );
		}
	}
	/**
	*  internally used for compiling code for var assign
	* @access private
	*/
	function _recursive_var_add( $cd, $vars, $arry )
	{
		global $errors;
		
		$i = 0;
		
		while ( list( $var, $val ) = each ( $vars ) )
		{
			if ( is_array( $val ) )
			{
				$cd = $this->_recursive_var_add( $cd, $val, $arry . '[ \'' . $var . '\' ]' );
				$i++;
			}else
			{
				$var = strtoupper( $var );
				// have been getting some nasty erros coz of this :)
// 				$val = preg_replace( "#^\'#", "\'", $val );
// 				$var = preg_replace( "#^\'#", "\'", $var );
				$val = str_replace ( array( "'", "\\\'" ), "\'", $val );
				$var = str_replace (array( "'", "\\\'" ), "\'", $var );
				// debug info
				$errors->debug_info( $this->debug, 'Template', 'recursive_var_add', 'linking ' . $var . ' with ' . $val );
				$cd .= $arry . '[ \'' . $var . '\' ][ \'_self_\' ] = ';
				$cd .= ( is_string( $val ) ) ? '\'' . $val . '\';' : $val . ';';
			}
		}
// 		echo $cd .'<br/><br/>';
		
		return $cd;
	}
	/**
	*  used to pass objected vars to template
	* @usage $template->assign_block_vars( 'block', '', array( 'var1' => 'val1', 'var2' => 'val2' ) );
	* uses a recursive function to achieve this
	* @see Template::_recursive_var_add()
	* @param string $block what block to add to/create
	* @param string $itera what iteration of the loop to add to (can be string 'now', 'last' or integer for specific, empty defaults to now)
	*/
	function assign_block_vars( $block, $itera, $vars )
	{
		global $errors;
		
		// make the block lowercase, for pretty
		$block = strtolower( $block );
		
		// $block MUST not be 'main', _self_ or this
		if ( $block == 'main' || $block == '_self_' )
		{
			$errors->debug_info( $this->debug, 'Template', 'assign_block_vars', $block . ' is a reserved block name' );
			return;
		}
		
		// determine what iteration to add the vars to
		$itera = strtolower( $itera );
		if ( empty( $itera ) || $itera == 'new' )
		{
			$itera = '';
			$ahead = TRUE;
		}elseif( $itera == 'last' )
		{
			$itera = '';
			$ahead = FALSE;
		}else
		{
			$itera = intval( $itera );
			$ahead = FALSE;
		}
	
		if ( strpos( $block, '.' ) !== FALSE ) // is this a nested thing
		{
			// explode nested block.
			$blocks = explode( '.', $block );
			$cur = array_pop( $blocks ); // this will be added
			$scope = implode( '.', $blocks ); // this is where it needs to be added
			// fetch scope from hash
			$scope = ( strpos( $scope, '.' ) !== FALSE ) ? $this->block_hash[ $scope ][ intval( $itera ) ] : $this->block_hash[ $scope ];
			// this will ensure we insert to the last occurence
			$c = '$cnt = count( $this->template_vars' . $scope .' );';
			eval( $c );
			$cnt -= substr_count( $block, '.' );
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'assign_block_vars', 'assigning variables to ' . $block );
			
			// code for insertion
			if ( empty( $itera ) )
			{ // get the last iteration if needed
				$set = '( isset( $this->template_vars' . $scope. '[ $cnt ][ $cur . \'.\' ] ) )';
				$cd = '$keys = ' . $set . ' ? array_keys( $this->template_vars' . $scope. '[ $cnt ][ $cur . \'.\' ] ) : \'\';';
				eval( $cd );
				$itera = ( !empty( $keys ) ) ? $keys[ count( $keys )-1 ] : -1 ;
				if ( $ahead )
				{ // if we're trying to go ahead
					$itera++;
				}
			}
			$arry = '$this->template_vars' . $scope. '[ $cnt ][ $cur . \'.\' ][ ' . $itera . ' ]';
			
			$cd = $this->_recursive_var_add( '', $vars, $arry );
	
			// execute the code produced
			eval( $cd );
			
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'assign_block_vars', 'adding ' . $block . ' to hash' );
			// add to block hash
			$this->block_hash[ $block ][ $cnt ] = $scope. '[ ' . $cnt . ' ][ \'' . $cur . '.\' ]';
		}
		else
		{
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'assign_block_vars', 'assigning variables to ' . $block );
			// add the vars to the given block
			if ( empty( $itera ) )
			{ // get the last iteration if needed
				$keys = ( isset( $this->template_vars[ $block . '.' ] ) ) ? array_keys( $this->template_vars[ $block . '.' ] ) : '';
				$itera = ( !empty( $keys ) ) ? $keys[ count( $keys )-1 ] : -1 ;
				if ( $ahead )
				{ // if we're trying to go ahead
					$itera++;
				}
			}
			$arry = '$this->template_vars[ $block . \'.\' ][ ' . $itera . ' ]';
	
			$cd = $this->_recursive_var_add( '', $vars, $arry );
	
			// execute the code
			eval( $cd );
			
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'assign_block_vars', 'adding ' . $block . ' to hash' );// add to block hash
			$this->block_hash[ $block ] = "[ '$block.' ]";
		}
	}
	/**
	* used to retrieve the view flag of a switch
	* @access private
	* @usage $flag = $this->_getviewflag( $name, $chunk )
	* @param string $switch name of the switch
	* @param mixed $chunk the chunk to parse
	* @return bool visibility
	*/
	function _getviewflag( $switch, $chunk )
	{
		global $errors;
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_getviewflag', 'fetching viewability for ' . $switch );
		
		// if the switch is not set then it definately is not viewable
		if ( !isset( $this->block_hash[ $switch ] ) )
		{
			return FALSE;
		}
		
		$c = $chunk[ $switch ][ 'var' ]; // loop count
		
		// define the scope for the block
		if ( strpos( $switch, '.' ) === FALSE )
		{ // not nested
			$scope = $this->block_hash[ $switch ];
		}else
		{ // nested
			// the count here has to be of the parent actually
			$s = explode( '.', $switch );
			array_pop( $s );
			$cnt = $chunk[ implode( '.', $s ) ][ 'var' ];
			
			$scope = $this->block_hash[ $switch ][ $cnt ];
		}
		
		// make code for flag retrieval
		$block = '$this->template_misc[ \'switches\' ]' . $scope . '[ ' . $c . ' ][ \'_self_\' ]';
 		$code = '$flag = ( isset( ' . $block . ' ) ) ? ' . $block . ' : FALSE;';
		// execute
		eval( $code );
		
		return $flag;
	}
	/**
	* used to replace var calls with values
	* @access private
	* @usage $line = $this->_getvars( $line, $chunk );
	* @param string $line the line to parse
	* @param integer $chunk number of the chunk
	* @param integer $count iteration number
	* @return string the parsed line
	*/
	function _getvars( $line, $chunk = 0, $count = 0 )
	{
		global $errors;
		
		// find all variable references
		preg_match_all( '#\{([a-z0-9\-_.:]*?)\}#is', $line, $matches );
		// go through the matches
		while ( list ( $v, $match ) = each ( $matches[ 1 ] ) )
		{
			// debug info
			if ( strpos( $match, '.' ) !== FALSE )
			{ // dot
				// separate blocks from the variable itself
				$blocks = explode( '.', $match );
				$var = array_pop( $blocks );
				$block = implode( '.', $blocks );
				// loop count
				if ( is_array( $chunk ) ) 
				{ // normal operation
					$c = $chunk[ $block ][ 'var' ];
				}else
				{ // manualy set operation
					$c = $count;
				}
				
				if ( $block != 'this' )
				{
					// define the scope for the block
					if ( strpos( $block, '.' ) === FALSE )
					{ // not nested
						$scope = $this->block_hash[ $block ];
					}else
					{ // nested
						// the count here has to be of the parent actually
						$s = explode( '.', $block );
						array_pop( $s );
						$cnt = $chunk[ implode( '.', $s ) ][ 'var' ];
			
						$scope = $this->block_hash[ $block ][ $cnt ];
					}
					
					// check if the variable is any of the default ones
					if ( in_array( $var, array( 'var', 'count', 'return', 'name' ) ) )
					{
						// return data of that block
						$val = $chunk[ $block ][ $var ];
					}else
					{
						$code = '$val = $this->template_vars' . $scope . '[ ' . $c . ' ]';
						// make the code to retrieve value
						if ( strpos( $var, ':' ) !== FALSE )
						{
							$var = explode( ':', $var );
							while ( list ( $k, $vr ) = each ( $var ) )
							{
								$code .= '[ \'' . $vr . '\' ]';
							}
							$code .= '[ \'_self_\' ];';
						}else
						{
							$code .= '[ \'' . $var . '\' ][ \'_self_\' ];';
						}
						eval( $code );
					}
				}else
				{
					// return data of current block
					$var = strtolower( $var );
					$d = end( $chunk );
					$val = $d[ $var ];
				}
				// insert into the line
				if ( isset( $var ) )
				{
					$line = str_replace( $matches[ 0 ][ $v ], $val, $line );
				}else
				{
					$line = str_replace( $matches[ 0 ][ $v ], '', $line );
				}
			}else
			{ // no dot
				// deal with function argumenst
				if ( strpos( $match, 'func:' ) !== FALSE )
				{
					$var = explode( ':', $match );
					$val = $this->functions[ $var[ 1 ] ][ 'args' ][ $var[ 2 ] ];
				}else
				{
					$code = '$val = $this->template_vars';
					// make the code to retrieve value
					if ( strpos( $match, ':' ) !== FALSE )
					{ // aye
						$var = explode( ':', $match );
						while ( list ( $k, $vr ) = each ( $var ) )
						{
							$code .= '[ \'' . $vr . '\' ]';
						}
						$code .= '[ \'_self_\' ];';
					}else
					{ // nay
						$code .= '[ \'' . $match . '\' ][ \'_self_\' ];';
					}
					// execute
					eval( $code );
					// replace with value in the array
				}
				if ( isset( $val ) )
				{
					$line = str_replace( $matches[ 0 ][ $v ], $val, $line );
				}else
				{
					$line = str_replace( $matches[ 0 ][ $v ], '', $line );
				}
			}
			// debug info
			$errors->debug_info( $this->debug, 'Template', '_getvars', 'fetched value of ' . $match . '=' . $val );
		}
		
		return $line;
	}
	/**
	* used to get the number of loops a switch has
	* @access private
	* @usage $num = $this->_countswitch( 'name', $chunk );
	* @param string $name
	* @param mixed $chunk
	* @return integer the count
	*/
	function _countswitch( $name, $chunk )
	{
		global $errors;
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_countswitch', 'counting ' . $name );
		
		// if block isn't set then count is 0
		if ( !isset( $this->block_hash[ $name ] ) )
		{
			return FALSE;
		}
		
		// define the scope for the block
		if ( strpos( $name, '.' ) === FALSE )
		{ // not nested
			$scope = $this->block_hash[ $name ];
		}else
		{ // nested
			// the count here has to be of the parent actually
			$s = explode( '.', $name );
			array_pop( $s );
			$cnt = $chunk[ implode( '.', $s ) ][ 'var' ];
			
			$scope = $this->block_hash[ $name ][ $cnt ];
		}
		
		// generate the code
		$code = '$cnt = count( $this->template_misc[ \'switches\' ]' . $scope . ' );';
		// execute it
		eval( $code );
		$errors->debug_info( $this->debug, 'Template', '_countswitch', 'Counted ' . $cnt );
		
		return $cnt;
	}
	/**
	* used to parse a line of php to work
	* @access private
	* @usage $this->_parsephp( $line )
	* @param string $line the line to parse
	* @return string the parsed line
	*/
	function _parsephp( $line )
	{
		global $errors, $plugins;
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_parsephp', 'parsing php line: <b>' . htmlspecialchars( $line ) . '</b>' );
		// we have to make sure this won't mess up our variables
		$line = str_replace( '$', '$embedded_', $line );
		$specials = array( 'GLOBALS(?![a-zA-Z0-9])', '_SESSION(?![a-zA-Z0-9])', '_COOKIE(?![a-zA-Z0-9])', '_GET(?![a-zA-Z0-9])', '_POST(?![a-zA-Z0-9])', '_FILES(?![a-zA-Z0-9])', '_SERVER(?![a-zA-Z0-9])', '_ENV(?![a-zA-Z0-9])', 'template(?![a-zA-Z0-9])', 'security(?![a-zA-Z0-9])', 'Sajax(?![a-zA-Z0-9])', 'errors(?![a-zA-Z0-9])', 'board_config(?![a-zA-Z0-9])', 'encryption(?![a-zA-Z0-9])', 'lang_loader(?![a-zA-Z0-9])', 'basic_lang(?![a-zA-Z0-9])', 'mod_loader(?![a-zA-Z0-9])', 'TemplateOut(?![a-zA-Z0-9])', 'Cl_root_path(?![a-zA-Z0-9])', 'Cl_root_path4template' );
		$specials = array_merge( $specials, $plugins->list );
		// now we have to give access to core classes and plugins
		//"\n$1$$2"
		
		$line = preg_replace_callback( '#(.*?)\$embedded_(' . implode( '|', $specials ) . ')#', '_phpGlobalCallback', $line );
		// needed globals
		//$line = 'global $' . implode( ', $', $this->phpGlobals ) . ";\n" . $line;;
		$line = 'global $' . implode( ', $', $this->phpGlobals ) . ";\n$line";
		// we have to remove anything possibly dangerous
		for ( $count = 1; $count > 0; )
		{ // loop because some of the regexes contain whole lines
			$line = preg_replace( $this->forbidden_code->php, '', $line, -1, $count ); // remove stuff
		}
		
		$line = str_replace( '\"', '"', $line );
		
		return $line;
	}
	/**
	* used to parse a line of pseudocode
	* @access private
	* @usage $this->_parsecode( $line )
	* @param string $line the line to parse
	* @param mixed $chunk the current chunk
	* @return string parsed line
	*/
	function _parsecode( $line, $chunk = '', $getvars = TRUE )
	{
		global $errors;
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_parsecode', 'parsing pseudocode line: <b>' . htmlspecialchars( $line ) . '</b>' );
		
		// var calls replace with vars
		if ( $getvars )
		{
			$line = $this->_getvars( $line, $chunk );
		}
		
		// turn code into php code
		$searches = array( 
				'#==#',
				'#([^!+-.=*/%&<>])=#',
				'#(.*?)!and!(.*?)#', 
				'#(.*?)!or!(.*?)#', 
				'#not\((.*?)\)#',
				'#(.*?)not(.*?)#',
				'#(.*?)!div!(.*?)#',
				'#(.*?)!mod!(.*?)#',
				'#int\((.*?)\)#',
				'#float\((.*?)\)#',
				'#str\((.*?)\)#',
				);
		$replaces = array( 
				'=',
				'$1==',
				'($1)&&($2)',
				'($1)||($2)',
				'!($1)',
				'($1)!=($2)',
				'($1)/($2)',
				'($1)%($2)',
				'intval("$1")',
				'floatval("$1")',
				'strval("$1")',
				);
		$line = preg_replace( $searches, $replaces, $line );
		
// 		echo "$line<br>";
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_parsecode', 'got line: <b>' . htmlspecialchars( $line ) . '</b>' );
		
		return $line;
	}
	/**
	* used to include a file into the scope
	* @access private
	* @usage $this->_includefile( $file, $code, $point, 'php' )
	* @param string $file filename
	* @param array $code the scope
	* @param integer $point where to put it
	* @param integer $codelength current length of code
	* @param string $special if there is anything special about the way of including the source and what
	*/
	function _includefile( $file, &$code, $point, &$codelength, $special = FALSE )
	{
		global $errors;
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_includefile', 'fetching' . $file );
		// get the code for inclusion
		$insert = $this->_getfile( $file, TRUE );
		$insert = explode( "\n", $insert ); // separate into lines
		
		// codelength has to be updated
		$old = $codelength;
		$codelength += count( $insert );
		$errors->debug_info( $this->debug, 'Template', '_includefile', 'Updating codelength from ' . $old . ' to ' . $codelength );
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_includefile', 'sticking fetched into the code scope' );
		// insert the lines into the code
		$rest = array_slice( $code, $point ); // code bellow the line
		
		if ( !$special )
		{
			array_shift( $rest ); // remove this line
		}else
		{ 
			switch ( $special )
			{
				case 'php':
					// first line of insert delimits start of php
					$insert = array_merge( array( '<!-- PHP -->' ), $insert );
					// first line of rest delimits end of php
					$rest[ 0 ] = '<!-- PHPEND -->'; 
					break;
			}
		}
		$rest = array_merge( $insert, $rest ); // exploded line and the rest
		$code = array_slice( $code, 0, $point ); // get first part of code
		$code = array_merge( $code, $rest ); // put together
	}
	/**
	* inserts code into the scope
	* @access private
	* @usage $this->_insertcode( $code, $point, array( 'line1', 'line2' ), $codelength );continue; (this is a must)
	* @param array $code the current scope
	* @param integer $point where to
	* @param array $insert scope to insert
	* @param integer $codelength current length of code
	*/
	function _insertcode( &$code, &$point, $insert, &$codelength )
	{
		global $errors;
		
		// codelength has to be updated
		$old = $codelength;
		$codelength += count( $insert );
		$errors->debug_info( $this->debug, 'Template', '_insertcode', 'Updating codelength from ' . $old . ' to ' . $codelength );
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', '_insertcode', 'inserting code into the scope: <b>' . htmlspecialchars( implode( ';;', $insert ) )  . '</b>' );
		// insert
		$rest = array_slice( $code, $point ); // code bellow the line
		array_shift( $rest ); // remove this line
		$rest = array_merge( $insert, $rest ); // exploded line and the rest
		$code = array_slice( $code, 0, $point ); // get first part of code
		$code = array_merge( $code, $rest ); // put together
		
		// re-execute this line
		$point--;
	}
	/**
	* generalizes line breaks (everything to \n)
	* @param string $str
	* @return string the parsed string
	*/
	function gennuline( $str )
	{
		return str_replace( "\r", "\n", str_replace( "\r\n", "\n", $str ) );
	}
	/**
	* used to compile template code
	* @usage $compiled = $template->compile( $code );
	* @param string $code the code to compile
	* @return string compiled code
	*/
	function compile( $code, $handle = '' )
	{
		if ( !$this->compiler )
		{
			return $this->_interpreter( $code );
		}else
		{	
			if ( $code != 'COMPILED' )
			{
				$this->_compiler( $code, $handle );
			}
// 			$this->_vardump( );
			return $this->_run( $handle );
		}
	}
	/**
	* returns the code put through an interpreter
	* @acces private
	* @param string $code the code to compile
	* @return string compiled code
	*/
	function _interpreter( $code )
	{
		global $errors;
		
		$compiled = array( );
		
		// debug info
		$errors->debug_info( $this->debug, 'Template', 'compile', '<b>compiler started</b>' );
		
		// remove any php
		$tags = array( '#\<\?php .*?\?\>#is', '#\<\script language="php".*?\>.*?\<\/script\>#is', '#\<\?.*?\?\>#s', '#\<%.*?%\>#s' );
		$code = preg_replace( $tags, '', $code );
	
		// separate code into lines
		$code = explode( "\n", $code );
		
		// go through lines and do the stuff
		$point = 0; // line pointer
		$chunk[ 'main' ][ 'view' ] = TRUE; // code chunk we're in
		$codelength = count( $code ); // number of code lines
		$is_special_code = FALSE; // tells if currently any special code is being used(php, JS..)
		$special_code = array();// the special code goes here
		$function = FALSE; // not adding a function are we now
		$funct_code = array(); // you know why
		$funct_name = ''; // no name yet
		$justprint = FALSE; // not "justprinting"
		while ( $point <= $codelength )
		{
			// get the line
			$line = $code[ $point ];
			// debug info
			$errors->debug_info( $this->debug, 'Template', 'compile', 'got line <b>' . htmlspecialchars( $line ) . '</b>' );
				
			//
			// compile the line
			//
			
			// function creation
			if ( $function && $line != '<!-- ENDFUNCTION -->' )
			{
				// debug info
				$errors->debug_info( $this->debug, 'Template', 'compile', 'add line to function ' . $funct_name );
				$funct_code[] = $line;
				// no need to go on
				$point++;
				continue;
			}
			// end function creation
			
			// determine if this is curently viewable
			$info = end( $chunk );
			if ( $info[ 'view' ] )
			{
				// debug info
				$errors->debug_info( $this->debug, 'Template', 'compile', 'line viewable' );
			}else
			{
				// debug info
				$errors->debug_info( $this->debug, 'Template', 'compile', 'line unviewable' );
			}
			
			// special code support
			if ( $is_special_code != FALSE && $line != '<!-- PHPEND -->' && $info[ 'view' ] )
			{
				// debug info
				$errors->debug_info( $this->debug, 'Template', 'compile', 'adding line to ' . $is_special_code . ' code scope' );
				// determine code and what to do
				switch ( $is_special_code )
				{
					case 'php':
						$parsed_line = $this->_parsephp( $line );
						break;
				}
				// add to scope
				$special_code[] = $parsed_line;
				// jump through this
				$point++;
				continue;
			}
			// end special code support
			
			// print this line only if not switch statement
			if ( $justprint && strpos( $line, '<!-- END JUSTPRINT -->' ) === FALSE )
			{ // just output the thing
				// debug info
				$errors->debug_info( $this->debug, 'Template', 'compile', 'add line to compiled scope' );
				// add line to compiled code
				$compiled[] = $line;
			}elseif ( strpos( $line, '<!--' ) === FALSE )
			{
				// variables
				if ( $info[ 'view' ] && !$function )
				{
					$line = $this->_getvars( $line, $chunk );
					// debug info
					$errors->debug_info( $this->debug, 'Template', 'compile', 'add line to compiled scope' );
					// add line to compiled code
					$compiled[] = $line;
				}
			}else
			{
				// switches/loops
				// get the number of switch statements
				preg_match_all( '#<!-- (.*?) -->#', $line, $matches );
				$switches = count( $matches[ 1 ] );
				// if more than one split the line
				if ( $switches > 1 )
				{
					// debug info
					$errors->debug_info( $this->debug, 'Template', 'compile', 'more than one switch statement, breaking line up' );
					// split
					$lines = preg_split( '#<!-- (.*?) -->#', $code[ $point ] );
					// put the switch calls back in
					$whereto = 0;
					$i = 0;
					while ( $i < count( $matches[ 1 ] ) )
					{
						if ( empty( $lines[ $whereto ] ) )
						{
							$switch = $matches[ 1 ][ $i ];
							$lines[ $whereto ] = "<!-- $switch -->";
							$i++;
						}
						$whereto++;
					}
					
					// insert the lines into the code
					$this->_insertcode( $code, $point, $lines, $codelength );
					continue;
				}
					
				// get the switch statement
				preg_match( '#<!-- (.*?) -->#', $line, $switch );
				
				// separate switch statement from it's name
				$switch = explode( " ", $this->_getvars( $switch[ 1 ], $chunk ) );
				
				// debug info
				$errors->debug_info( $this->debug, 'Template', 'compile', 'executing switch specific stuff' );
				// do according to the switch
				$statement = $switch[ 0 ];
				$name = $switch[ 1 ];
				$viewable = $info[ 'view' ]; // when older code clashes with new stuff, this is what you need
				switch ( $statement )
				{
					case 'ASSIGN':
						// debug info
						$errors->debug_info( $this->debug, 'Template', 'compile', 'executing assign variable command' );
						// get values
						$var = $name;
						$val = $switch[ 2 ];
						// do we need to fetch a var for the value
						if ( strpos( $val, '{' ) !== FALSE )
						{
							$val = $this->_getvars( $val, $chunk );
						}
						// decide on the block to use
						if ( strpos( $var, '.' ) === FALSE )
						{
							$this->assign_vars( array( $var => $val ) );
						}else
						{
							// get the block
							$blocks = explode( '.', $var );
							$var = array_pop( $blocks );
							$block = implode( '.', $blocks );
							// execute according to the block
							if ( $block == 'this' )
							{
								// check if 'this' is main
								if ( count( $chunk ) == 1 )
								{ // is main
									$this->assign_vars( array( $var => $val ) );
								}else
								{ // not main
									// get block
									$d = end( $chunk );
									$block = $d[ 'name' ];
									$i = $d[ 'var' ];
									$this->assign_block_vars( $block, $i, array( $var => $val ) );
								}
							}else
							{
								$this->assign_block_vars( $block, 'last', array( $var => $val ) );
							}
						}
						break;
					case 'FUNCTION':
						// debug info
						$errors->debug_info( $this->debug, 'Template', 'compile', 'beginning a function' );
						// deal with the arguments
						$args = explode( ',', $switch[ 2 ] );
						while ( list( $k, $v ) = each( $args ) )
						{
							$this->functions[ $name ][ 'args' ][ $k ] = '';
						}
						// state that now we'll be adding a function
						$function = TRUE;
						$funct_code = array();
						$funct_name = $name;
						break;
					case 'ENDFUNCTION':
						// debug info
						$errors->debug_info( $this->debug, 'Template', 'compile', 'ending a function' );
						// generate the function
						$funct_code = implode( "\n", $funct_code );
						$this->functions[ $funct_name ][ '_code_' ] = $funct_code;
						// state we're no longer in a function
						$function = FALSE;
						$funct_code = array();
						$funct_name = '';
						break;
					case 'EXECUTE':
						// don't bother if not visible
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'executing a function' );
							// deal with the arguments
							$args = explode( ',', $switch[ 2 ] );
							// enough arguments?
							if ( count( $args ) != count( $this->functions[ $name ][ 'args' ] ) )
							{
								continue;
							}
							// set values
							while ( list( $k, $v ) = each( $args ) )
							{
								$this->functions[ $name ][ 'args' ][ $k ] = $v;
							}
							// get code
							$funct = $this->functions[ $name ][ '_code_' ];
							$funct = explode( "\n", $funct );
							// insert the function into the code
							$this->_insertcode( $code, $point, $funct, $codelength );
							continue;
						}
						break;
					case 'PHP':
						// don't bother if this is currently hidden
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'beginning php code' );
							// denote start of php code
							$is_special_code = 'php';
							$special_code = array();
						}
						break;
					case 'PHPEND':
						// don't bother if this is currently hidden
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'ending php code' );
							// execute gathered php code
							$special_code = implode( "\n", $special_code );
							eval( $special_code );
							// denote end of php code
							$is_special_code = FALSE;
							$special_code = array();
						}
						break;
					case 'ELSE':
						// still needs checking?
						if ( $chunk[ $name ][ 'check' ] )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', '"elsing"' );
							// set the name of chunk for easier use later on
							$chunk[ $name ][ 'name' ] = $name;
							// set the var pointer
							$chunk[ $name ][ 'var' ] = 0;
							// loop count to none as it's in no way a loop
							$chunk[ $name ][ 'count' ] = 1;
							
							$chunk[ $name ][ 'view' ] = TRUE;  // viewable
							$chunk[ $name ][ 'check' ] = FALSE; // don't check anymore
						}else
						{
							$chunk[ $name ][ 'view' ] = FALSE;
						}
						break;
					case 'ELSEIF':
						// still needs checking?
						if ( $chunk[ $name ][ 'check' ] )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', '"elseifing"' );
							// set the name of chunk for easier use later on
							$chunk[ $name ][ 'name' ] = $name;
							// set the var pointer
							$chunk[ $name ][ 'var' ] = 0;
							// loop count to none as it's in no way a loop
							$chunk[ $name ][ 'count' ] = 1;
							
							// get code to parse
							preg_match( '#\(.*?\)#', $ln, $pseudo );
							$pseudo = $pseudo[ 0 ];
							$pseudo = $this->_parsecode( $pseudo, $chunk );
							$show = FALSE;
							$pseudo = 'if (' . $pseudo . ') $show = TRUE;';
							// execute code
							eval( $pseudo );
							if ( $show ) // check
							{
								// debug info
								$errors->debug_info( $this->debug, 'Template', 'compile', 'making viewable' );
								$chunk[ $name ][ 'view' ] = TRUE;  // viewable
								$chunk[ $name ][ 'check' ] = FALSE; // don't check anymore
							}else
							{
								// debug info
								$errors->debug_info( $this->debug, 'Template', 'compile', 'making nonviewable' );
								$chunk[ $name ][ 'view' ] = FALSE; // nonviewable
								$chunk[ $name ][ 'check' ] = TRUE; // do check more
							}
						}else
						{
							$chunk[ $name ][ 'view' ] = FALSE;
						}
						break;
					case 'IF':
						// don't bother if this is currently hidden
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', '"ifing"' );
							// set the name of chunk for easier use later on
							$chunk[ $name ][ 'name' ] = $name;
							// set the var pointer
							$chunk[ $name ][ 'var' ] = 0;
							// loop count to none as it's in no way a loop
							$chunk[ $name ][ 'count' ] = 1;
							
							// get code to parse
							$ln = implode( " ", $switch );
							preg_match( '#\(.*?\)#', $ln, $pseudo );
							$pseudo = $pseudo[ 0 ];
							$pseudo = $this->_parsecode( $pseudo, $chunk );
							$show = FALSE;
							$pseudo = 'if (' . $pseudo . ') $show = TRUE;';
							// execute code
							eval( $pseudo );
							if ( $show ) // check
							{
								// debug info
								$errors->debug_info( $this->debug, 'Template', 'compile', 'making viewable' );
								$chunk[ $name ][ 'view' ] = TRUE;  // viewable
								$chunk[ $name ][ 'check' ] = FALSE; // don't check anymore
							}else
							{
								// debug info
								$errors->debug_info( $this->debug, 'Template', 'compile', 'making nonviewable' );
								$chunk[ $name ][ 'view' ] = FALSE; // nonviewable
								$chunk[ $name ][ 'check' ] = TRUE; // do check more
							}
						}
						break;
					case 'INCLUDE':
						// don't bother if this is currently hidden
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'including ' . $name );
							$this->_includefile( $name, $code, $point, $codelength ); // include
							$point--; // this line needs to get recompiled
							continue; // just jump through
						}
						break;
					case 'INCLUDEPHP':
						// don't bother if this is currently hidden
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'including php ' . $name );
							$this->_includefile( $name, $code, $point, $codelength . 'php' ); // include
							$point--; // this line needs to get recompiled
							continue; // just jump through
						}
						break;
					case 'EVEN':
						// don't bother if this is currently hidden
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'is even? ' . $switch[ 2 ] );
							// set the name of chunk for easier use later on
							$chunk[ $name ][ 'name' ] = $name;
							// set the var pointer
							$chunk[ $name ][ 'var' ] = 0;
							// loop count to none as it's in no way a loop
							$chunk[ $name ][ 'count' ] = 1;
							
							$var = $switch[ 2 ]; // get the var to check
							$var = $this->_getvars( $var, $chunk ); // change to value
							if ( $var % 2 == 0 || $var == 0 ) // check
							{
								// debug info
								$errors->debug_info( $this->debug, 'Template', 'compile', 'it is' );
								$chunk[ $name ][ 'view' ] = TRUE;  // viewable
							}else
							{
								// debug info
								$errors->debug_info( $this->debug, 'Template', 'compile', 'it isn\'t' );
								$chunk[ $name ][ 'view' ] = FALSE; // nonviewable
							}
						}
						break;
					case 'ODD':
						// don't bother if this is currently hidden
						if ( $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'is odd? ' . $switch[ 2 ] );
							// set the name of chunk for easier use later on
							$chunk[ $name ][ 'name' ] = $name;
							// set the var pointer
							$chunk[ $name ][ 'var' ] = 0;
							// loop count to none as it's in no way a loop
							$chunk[ $name ][ 'count' ] = 1;
						
							$var = $switch[ 2 ]; // get the var to check
							$var = $this->_getvars( $var, $chunk ); // change to value
							if ( $var % 2 != 0 ) // check
							{
								// debug info
								$errors->debug_info( $this->debug, 'Template', 'compile', 'it is' );
								$chunk[ $name ][ 'view' ] = TRUE;  // viewable
							}else
							{
								// debug info
									$errors->debug_info( $this->debug, 'Template', 'compile', 'it isn\'t' );
								$chunk[ $name ][ 'view' ] = FALSE; // nonviewable
							}
						}
						break;
					case 'NOT':
						// debug info
						$errors->debug_info( $this->debug, 'Template', 'compile', 'negating switch ' . $name );
						if ( !isset( $chunk[ $name ] ) ) // only set the first time
						{
							// set return point
							$chunk[ $name ][ 'return' ] = $point;
							// set the name of chunk for easier use later on
							$chunk[ $name ][ 'name' ] = $name;
							// set the var pointer
							$chunk[ $name ][ 'var' ] = 0;
							// loop count
							$chunk[ $name ][ 'count' ] = $this->_countswitch( $name, $chunk );
						}
						// parent view flag
						$info = $chunk;
						array_pop( $info );
						$info = end( $info );
						// set the view flag
						if ( !$this->_getviewflag( $name, $chunk ) && $info[ 'view' ] )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'viewable' );
								$chunk[ $name ][ 'view' ] = TRUE;  // viewable
						}else
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'nonviewable' );
							$chunk[ $name ][ 'view' ] = FALSE; // nonviewable
						}
						break;
					case 'BEGIN':
						// check for justprint
						if ( $name == 'JUSTPRINT' )
						{
							$justprint = TRUE;
							$point++;
							continue;
						}
						if ( !isset( $chunk[ $name ] ) ) // only set the first time
						{
							// set return point
							$chunk[ $name ][ 'return' ] = $point;
							// set the name of chunk for easier use later on
							$chunk[ $name ][ 'name' ] = $name;
							// set the var pointer
							$chunk[ $name ][ 'var' ] = 0;
							// loop count
							$chunk[ $name ][ 'count' ] = $this->_countswitch( $name, $chunk );
						}
						
						// set the view flag
						if ( $this->_getviewflag( $name, $chunk ) && $viewable )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'block ' . $name . ' viewable' );
							$chunk[ $name ][ 'view' ] = TRUE;  // viewable
						}else
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'block ' . $name . ' nonviewable' );
							$chunk[ $name ][ 'view' ] = FALSE; // nonviewable
						}
						break;
					case 'END':
						// check for justprint
						if ( $name == 'JUSTPRINT' )
						{
							$justprint = FALSE;
							$point++;
							continue;
						}
						// loop if needed
						if ( $chunk[ $name ][ 'count' ] > 1 )
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'go for another loop of ' . $name );
							$chunk[ $name ][ 'count' ]--; // loop count decrease
							$chunk[ $name ][ 'var' ]++; // var pointer increase
							$point = $chunk[ $name ][ 'return' ] - 1; // line pointer...
						}else
						{
							// debug info
							$errors->debug_info( $this->debug, 'Template', 'compile', 'end block ' . $name );
						// remove chunk data
						unset( $chunk[ $name ] );
						}
						break;
				}
				// end switch stuff
			} // end is switch if
			
			// increase line pointer
			$point++;
		}
		// put the compiled code together
		$compiled = implode( "\n", $compiled );
		
		return $compiled;
	}
	/**
	* @access private
	* @param string $handle the handle to run
	* @return string output
	*/
	function _run( $handle )
	{
		global $basic_gui, $errors, $board_config, $userdata;
		
		$filename =  str_replace( array( $this->folder, '/' ), array( '', '.' ), $this->template_files[ $handle ][ 'file' ] );
		
		include_once( $Cl_root_path . 'cache/template.' . $filename . '.' . $userdata[ 'user_skin' ] . phpEx );
		$func = "template_$handle";
		if ( !function_exists( $func ) )
		{ // sometimes with ajax there are problems
			include( $Cl_root_path . 'cache/template.' . $filename . '.' . $userdata[ 'user_skin' ] . phpEx );
		}
		return $func( $this->template_vars, $this->template_misc );
	}
	/**
	* returns an array path to the value
	* @access private
	* @param string name of the template variable
	* @return string
	*/
	function _varScope( $varName )
	{
		// construct the array scope
		$dots = preg_split( "#[^.:]+#", $varName );
		$variable = '';
		foreach ( preg_split( "#\.|:#", $varName ) as $i => $key )
		{
			$variable .= ( $dots[ $i+1 ] == '.' ) ? "[ '$key.' ][ \$$key ]" : "[ '$key' ]";
		}
		
		return $variable;
	}
	/**
	* used as a callback for the php sanitazing preg_replace_callback
	* @access private
	*/
	function _phpCallback( $matches )
	{
		return  $this->HTMLdelimiter . $this->_parsephp( $matches[ 1 ] ) . $this->HTMLinitiator;
	}
	/**
	* used to store everything that needs globalising for php
	* @access private
	*/
	function _phpGlobalCallback( $matches )
	{
		$autoglobals = array( '_SERVER', 'GLOBALS', '_GET', '_POST', '_COOKIE', '_FILES', '_ENV', '_SESSION' );
		if ( !isset( $this->phpGlobals[ $matches[ 2 ] ] ) && array_search( $matches[ 2 ], $autoglobals ) === FALSE && $matches[ 2 ] != 'TemplateOut' )
		{
			$this->phpGlobals[ $matches[ 2 ] ] = $matches[ 2 ];
		}
		return $matches[ 1 ] . '$' . $matches[ 2 ];
	}
	/**
	* replaces this. vars with what's needed
	* @access private
	*/
	function _thisvars( $code )
	{
		$return  = array();
		for ( $i = 0; $i < count( $code[ 0 ] ); $i++ )
		{ // go through the nests and replace stuff
			$return[ $i ] = $code[ 0 ][ $i ];
			preg_match_all( "#<!--\s*BEGIN\s+(.*?)\s*-->(.*?)<!--\s*END\s+\\1\s*-->#sm", substr( $code[ 0 ][ $i ], strpos( $code[ 0 ][ $i ], '-->' ) ), $vars );
			if ( count( $vars[ 0 ] > 0 ) )
			{ // a new nest found within the nest, recurse
				$arr = $this->_thisvars( $vars );
				$return[ $i ] = str_replace( $arr[ 0 ], $arr[ 1 ], $return[ $i ] );
			}
			$name2 = substr( strrchr( $code[ 1 ][ $i ], "." ), 1 );
			$name2 = ( empty( $name2 ) ) ? $code[ 1 ][ $i ] : $name2;
			$variable = $this->_varScope( $code[ 1 ][ $i ] );
			$variable = str_replace( array( "' ]", "..' ]" ), ".' ]", $variable );
			$return[ $i ] = preg_replace( array(
						'#{this.COUNT}([+\-*/]*[0-9]+)*#',
						'#{this.INDEX}([+\-*/]*[0-9]+)*#',
						'#{this.VAR}([+\-*/]*[0-9]+)*#',
						'#{this.NAME}#'
					), array(
						'" . count( $variables' . $variable . ' ) . "',
						'" . ($' . $name2 . '$1) . "',
						'" . ($' . $name2 . '$1) . "',
						$code[ 1 ][ $i ],
					), $return[ $i ] );
		}
		return array( $code[ 0 ], $return );
	}
	/**
	* returns escaped php suitable to be put in preg thingies
	* @access private
	* @param string $code code to escape
	* @return string escaped code
	*/
	function _escapeForPreg( $code )
	{
		$needles = array( '$', '[', ']', '{', '}' );
		
		$replaces = array( '\$', '\[', '\]', '\{', '\}' );
	
		return str_replace( $needles, $replaces, $code );
	}
	/**
	* returns the code put through a compiler
	* @acces private
	* @param string $code the code to compile
	* @return string compiled code
	*/
	function _compiler( $code, $handle, $functionBody = FALSE )
	{
		global $Cl_root_path, $errors, $cache, $board_config, $userdata;
		
		// no need for compiling if it's already done
		if ( $this->template_files[ $handle ][ 'compiled' ] )
		{
			return TRUE;
		}
		
		$code = str_replace ( array( '"', '\"' ), '\"', $code );
		if ( $functionBody )
		{ // fixes a small issue with argument use
			$code = preg_replace( '#\\\" \. (\$arg_.*?) \. \\\"#', '" . $1 . "', $code );
		}
		
		// let's make template code quite flexible
		$openCode = array( '<! ', '{% ', '{& ', '{{ ', '{c ', '{$ ' );
		$closeCode = array( ' ->', ' %}', ' &}', ' }}', ' c}', ' $}' );
		$code = str_replace( $openCode, '<!-- ', $code );
		$code = str_replace( $closeCode, ' -->', $code );
		
		$initiator = '$TemplateOut .= "';  // this will be used to initiate start of html
		$delimiter = "\";\n"; // end of html and continued php
		// make these a tad more useful
		$this->HTMLinitiator = &$initiator;
		$this->HTMLdelimiter = &$delimiter;
		
		// some cache type things
		$replacedVars = array();
		
		// first construct the initial comment, has to be done
		if ( !$functionBody )
		{
			$compiled = "<?php\n// template compiled with Chlorine Boards Template Engine on " . date( 'd.m.Y', EXECUTION_TIME ) . ' at ' . date(  'H:i:s', EXECUTION_TIME ) . "\n// the template itself might be under a license different to GPL so please consult its documentation if applicable\n\n// some basic definitions\n";
			$compiled .= "define( 'phpEx', '" . phpEx . "' );\n\n";
		}else
		{
			$compiled = "// function compiled with Chlorine Boards Template Engine on " . date( 'd.m.Y', EXECUTION_TIME ) . ' at ' . date(  'H:i:s', EXECUTION_TIME ) . "\n";
		}
		
		// first some code cleanup to avoid hiccups
		$code = preg_replace( "#\s+\<\!--#", '<!--', $code );
		
		// plaintext is sometimes required to stay uncompiled
		preg_match_all( '#<!--\s*PLAIN\s*-->(.*?)<!--\s*END\s*PLAIN\s*-->#sm', $code, $plainText );
		
		for ( $i = 0; $i < count( $plainText[ 0 ] ); $i++ )
		{
			$code = str_replace( $plainText[ 0 ][ $i ], '<!-- PLAIN ' . $i . ' --!>', $code );
		}
		
		// this is needed in a few things like functions and inclusions
		preg_match_all( '#<!--\s*BEGIN\s+(.*?)\s*-->#', $code, $Counters );
		for ( $i = 0; $i < count( $Counters[ 1 ] ); $i++ )
		{
			$Counters[ 1 ][ $i ] = '$' . $Counters[ 1 ][ $i ];
		}
		$Counters = $Counters[ 1 ];
		$Counters = ( count( $Counters ) > 0 ) ? ', ' . implode( ', ', $Counters ) : '';
		
		//
		// functions
		//
		preg_match_all( "#<!--\s*FUNCTION\s+(.*?)\s+(.*?)\s*-->(.*?)<!--\s*ENDFUNCTION\s+\\1\s*-->#sm", $code, $functions );
		if ( !$functionBody )
		{ // clears residue if this isn't inside a recursion
			$this->functions = Array();
		}
		for ( $i = 0; $i < count( $functions[ 0 ] ); $i++ )
		{
			// arguments
			$args = explode( ',', str_replace( ' ', '', $functions[ 2 ][ $i ] ) );
			// take care of argument use
			$arr = array( 'what' => array(), 'with' => array() );
			for ( $j = 0; $j < count( $args ); $j++ )
			{
				$arr[ 'what' ][] = '#\{' . $args[ $j ] . '\}#i';
				$arr[ 'with' ][] = '" . $arg_' . $args[ $j ] . ' . "';
			}
			$body = preg_replace( $arr[ 'what' ], $arr[ 'with' ], $functions[ 3 ][ $i ] );
			// so the stuff can be used in conditionals and such
			for ( $count = 1; $count > 0; )
			{ // so it can be used more than once in a line
				$body = preg_replace( '#(<!--.*?)" \. (.*?) \. "(.*?-->)#', '$1 $2 $3', $body, -1, $count );
			}
			// put arguments in header formation
			for ( $j = 0; $j < count( $args ); $j++ )
			{
				$args[ $j ] = "\$arg_${args[ $j ]}";
			}
			$args = ( count( $args ) > 0 ) ? ', ' . implode( ', ', $args ) : '';
			// we put the body of the function through the compiler so we get everything working properly :P
			$body = $this->_compiler( $body, $handle . '_function_' . $functions[ 1 ][ $i ], TRUE );
			// php code of the function
			$this->functions[] = 'function function_' . $functions[ 1 ][ $i ] .' ( $variables, $miscellaneous ' . $Counters . $args . " ) \n{\n$body\n}\n\n";
		}
		// remove functions from code
		$code = str_replace( $functions[ 0 ], '', $code );
		
		//
		// included php
		//
		$code = preg_replace(
				array( 
					'#\<\?(.*?)\?\>#s',
					'#\<\?php( .*?)\?\>#is', 
					'#\<\script\s+language="php".*?\>(.*?)\<\/script\>#is', 
					'#\<%(.*?)%\>#s',
					'#<!--\s*PHP\s*-->(.*?)<!--\s*PHPEND\s*-->#s' 
				),
				'<?php $1 ?>',
				$code
			);
		$code = str_replace( '<?php  php', '<?php', $code );
		$code = preg_replace_callback( '#\<\?php(.*?) \?\>#s', '_TemplatePhpCallback', $code );
		
		//
		// variables
		//

		$neededFilters = array();
		$neededFiltersHash = array();

		// specials
		preg_match_all( "#<!--\s*BEGIN\s+(.*?)\s*-->.*?<!--\s*END\s+\\1\s*-->#sm", $code, $vars );
		if ( count( $vars[ 0 ] > 0 ) )
		{
			$arr = $this->_thisvars( $vars );
			$code = str_replace( $arr[ 0 ], $arr[ 1 ], $code );
			// so the stuff can be used in conditionals and such
			for ( $count = 1; $count > 0; )
			{ // so it can be used more than once in a line
				$code = preg_replace( '#(<!--.*?)" \. (.*?) \. "(.*?-->)#', '$1 $2 $3', $code, -1, $count );
			}
		}
		
		// normals
		preg_match_all( "#([:|])*\{([a-zA-Z.:0-9_\-]+?)(\|[a-zA-Z0-9:|]+)*\}([+\-*/]*[0-9]+)*#m", $code, $vars, PREG_PATTERN_ORDER );
// 		print_R( $vars );
		if ( is_array( $vars[ 2 ] ) )
		{
			while ( list( $index, $varName ) = each( $vars[ 2 ] ) )
			{
				if ( isset( $replacedVars[ $varName ] ) && empty( $vars[ 3 ][ $index ] ) && empty( $vars[ 4 ][ $index ] ) )
				{ // this one already got replaced
					continue;
				}
				$variable = $this->_varScope( $varName );
				// create the three differing replaces
				// default first
				$Variable = "{\$variables${variable}[ '_self_' ]}";
				$Needle = '#' . $vars[ 0 ][ $index ] . '(?![+\-*/]*[0-9]+)#m';
				
				if ( $vars[ 1 ][ $index ] != '' )
				{ // this variable is a filter argument, somewhat different machinations are needed
					//continue;
					$Needle = str_replace( '#' . $vars[ 1 ][ $index ], '#', $Needle );
					$Variable = substr( substr( $Variable, 1 ), 0, -1 );
					
					$code = preg_replace( str_replace( array( '.', '|' ), array( '\.', '\|' ), $Needle ), $Variable, $code );
					$replacedVars[ $varName ] = TRUE;
					
					// now we try to refind this one and add it to the variable scope
					$Var = $this->_escapeForPreg( $Variable );
					preg_match_all( "#([:|])*\{([a-zA-Z.:0-9_\-]+?)(\|[a-zA-Z0-9:|$'.\[\]_ ]+$Var)\}([+\-*/]*[0-9]+)*#m", $code, $Tvars, PREG_PATTERN_ORDER );
					
					for ( $i = 0; $i < count( $Tvars ); $i++ )
					{
						for ( $j = 0; $j < count ( $Tvars[ $i ] ); $j++ )
						{
							$vars[ $i ][] = $Tvars[ $i ][ $j ];
						}
					}
					continue;
				}
				
				if ( !empty( $vars[ 3 ][ $index ] ) )
				{ // needs a filter
					$filters = explode( '|', $vars[ 3 ][ $index ] ); // in case more than one is needed
					array_shift( $filters );
					$needle = '#{' . $this->_escapeForPreg( substr( substr( $vars[ 0 ][ $index ], 1 ), 0, -1 ) ) . '}#m';
					$break = FALSE;
					$variable = "\$variables${variable}[ '_self_' ]";
					
					for ( $i = 0; $i < count( $filters ); $i++ )
					{
						$filter = $filters[ $i ];
						$filter = explode( ':', $filter ); // possible arguments
						$name = array_shift( $filter );
						
						$args = implode( "', '", $filter );
						$args = ( !empty( $args ) ) ? "'" . $args . "'" : '';
						$args = ( !empty( $args ) ) ? $variable . ', ' . $args : $variable;
						// if itæs a variable it mustn't be in quotes
						$args = str_replace( array( '\'$variables', ']\'' ), array( '$variables', ']' ), $args );
						$variable = "\$filter_${name}->main( $args )";

						if ( !$neededFiltersHash[ $name ] )
						{ // need to make sure it exists and eventually gets included
							if ( is_readable( $Cl_root_path . 'filters/' . $name . phpEx ) )
							{ // goody, it can be used
								$neededFilters[] = $name;
								$neededFiltersHash[ $name ] = TRUE;
							}else
							{ // if the first needed filter isn't there, no use trying others the result will be unexpected either way
								$break = TRUE;
								break;
							}
						}
					}
					
					if ( !$break )
					{ // went well
						$Variable = "${delimiter}\$TemplateOut .= ${variable};$initiator";
						$Needle = $needle;
					}
				}elseif ( !empty( $vars[ 4 ][ $index ] ) )
				{ // has some simple algebraic stuff added
					$Variable = "{(\$variables${variable}[ '_self_' ]{$vars[ 3 ][ $index ]})}";
					$Needle = '#' . preg_replace( '#[+\-*]#', '\\\$0', $vars[ 0 ][ $index ] ) . '#m';
				}
				
				// do the replaces;
				$code = preg_replace( str_replace( array( '.', '|' ), array( '\.', '\|' ), $Needle ), $Variable, $code );
				$replacedVars[ $varName ] = TRUE;
			}
		}

		// fix the mistakes made by the blatantly simple variable replace
		for ( $count = 1; $count > 0; )
		{ // otherwise several of these in a single line don't work
			$code = preg_replace( '#(<!--.*?)\'*\{(\(*)(\$variables.*?)(\)*)\}\'*(.*?-->)#', '$1 $2$3$4 $5', $code, -1, $count );
		}
		
		//
		// switches, loops, et cetera
		//
		preg_match_all( "#<!--\s*?([A-Z]+)(\s+?|:+?)(.*?)\s+?(.*?)\s*?-->#m", $code, $switches );
		for ( $index = 0; $index < count( $switches[ 1 ] ); $index++ )
		{
			$original = $switches[ 0 ][ $index ];
			$action = $switches[ 1 ][ $index ];
			$name = $switches[ 3 ][ $index ];
			$pseudo = $switches[ 4 ][ $index ];
			
			// this enables names to be variables as far as the template is concerned
			$name = ( $name{0} == '$' ) ? eval( 'return ' . str_replace( '$variables' , '$this->template', $name ) ) : $name;
			
			switch ( $action )
			{
				case 'END':
					$Line = $delimiter . ' }' . $initiator;
					break;
				case 'BEGIN':
					$name2 = substr( strrchr( $name, "." ), 1 );
					$name2 = ( empty( $name2 ) ) ? $name : $name2;
					$variable = $this->_varScope( $name );
					$variable = str_replace( array( "' ]", "..' ]" ), ".' ]", $variable );
					$Line = $delimiter . " for( \$$name2 = 0; \$$name2 < count( \$variables$variable ); \$$name2++ ) { if ( !\$miscellaneous[ 'switches' ]$variable ) { continue; }" . $initiator;
					break;
				case 'IF':
				case 'ELSEIF':
				case 'ELSE':
					// this prevents the previous practice of putting apostrophes around inpredictable
					// variables in checks from becoming a problem
					$pseudo = str_replace( array( "'\$", "]'" ), array( '$', ']' ), $pseudo );
					$command = ( $action == 'IF' ) ? strtolower( $action ) : '}' . strtolower( $action );
					$Line = $delimiter . "  $command " . $this->_parsecode( $pseudo ) . ' { ' . $initiator;
					break;
				case 'EVEN':
					$Line = $delimiter . " if ( $pseudo % 2 == 0 || $pseudo == 0 ) { " . $initiator;
					break;
				case 'ODD':
					$Line = $delimiter . " if ( $pseudo % 2 != 0 ) { " . $initiator;
					break;
				case 'NOT':
				case 'FALSE':
					$Line = $delimiter . " if ( (bool) $pseudo == FALSE ) { " . $initiator;
					break;
				case 'YES':
				case 'TRUE':
					$Line = $delimiter . " if ( (bool) $pseudo == TRUE ) { " . $initiator;
					break;
				case 'EMPTY':
					$Line = $delimiter . " if ( empty( $pseudo ) ) { " . $initiator;
					break;
				case 'UNEMPTY':
					$Line = $delimiter . " if ( !empty( $pseudo ) ) { " . $initiator;
					break;
				case 'INCLUDE':
					// need all the counters to be passed
					if ( !empty( $Counters ) )
					{
						$CountersList = ', Array( ' . str_replace( '$', '\'', implode( '\', ', explode( ', ', substr( $Counters, 2 ) ) ) ) . '\' )';
					}else
					{
						$CountersList = '';
					}
					
					if ( $pseudo{0} == '$' )
					{ // we go through the array of possible values, compile everything needed and let the compiled php do the rest
						$array = str_replace( '$variables' , '$this->template_vars', $pseudo );
						preg_match( '#\[ \'(.*?)\.\' \]#', $array, $match );
						$block = $match[ 1 ];
						$Line = '';
						for ( $i = 0; $i < count( $this->template_vars[ "$block." ] ); $i++ )
						{
							$name = str_replace( '$' . $block, $i, $array );
							
							//echo ${$name} . "<br />";
							$name = eval( "return $name;" );
							//echo $this->template_vars[ 'overview.' ][ 0 ][ 'WHAT' ][ '_self_' ];
							
							if ( isset( $this->template_files[ $name ] ) )
							{ // file known
								//need to compile the file
								$f = $this->_getfile( $name );
								if ( $f != 'COMPILED' )
								{
									$this->_compiler( $f, $name );
								}
								$filename =  str_replace( array( $this->folder, '/' ), array( '', '.' ), $this->template_files[ $name ][ 'file' ] );
								$lin = " include_once( './cache/template." . $filename . '.' . $userdata[ 'user_skin' ] . phpEx ."' ); \$TemplateOut .= template_$name(  \$variables, \$miscellaneous $CountersList $Counters );";
							}else
							{ // file unknown
								$lin = '';
							}
							$Line .= $lin;
						}
						$Line = ( !empty( $Line ) ) ? $delimiter . $Line . $initiator : '';
					}else
					{ // the inclusion is static, less work
						if ( isset( $this->template_files[ $name ] ) )
						{ // file known
							// need to compile the file
							$f = $this->_getfile( $name );
							if ( $f != 'COMPILED' )
							{
								$this->_compiler( $f, $name );
							}
							$filename =  str_replace( array( $this->folder, '/' ), array( '', '.' ), $this->template_files[ $name ][ 'file' ] );
							$Line = $delimiter . " include_once( './cache/template." . $filename . '.' . $userdata[ 'user_skin' ] . phpEx ."' ); \$TemplateOut .= template_$name(  \$variables, \$miscellaneous $CountersList $Counters );" . $initiator;
						}else
						{ // file unknown
							$Line = '';
						}
					}
					break;
				case 'INCLUDEPHP':
					// fetch the code and parse it
					$php = $this->_parsephp( @file_get_contents( $name ) );
					// write it down
					$name =  'template..php.' . basename( $name );
					if ( $f = @fopen( $Cl_root_path . 'cache/' . $name, 'w' ) )
					{
						if ( @fwrite( $f, $php ) )
						{
							$Line = $delimiter . " include( './$name' ); " . $initiator;
						}else
						{
							$Line = '';
						}
					}else
					{
						$Line = '';
					}
					break;
				case 'ASSIGN':
					$var = $this->_varScope( $name ); // transform the scope into array thingies
					$pseudo = ( $pseudo{0} == '\'' ) ? eval( 'return ' . str_replace( '$variables' , '$this->template', $pseudo ) ) : $pseudo; // the value can be given as a variable
					if ( $pseudo{0} == '(' && $pseudo{strlen( $pseudo )-1} == ')' )
					{ // dealing with an expression
						$expr = substr( $pseudo, 1, -1 );
						$Line = $delimiter . "\$variables${var}[ '_self_' ]$expr;" . $initiator;
					}else
					{ // just assignment
						$val = ( is_numeric( $pseudo ) ) ? $pseudo : "'$pseudo'"; // make the value string
						
						$Line = $delimiter . "\$variables${var} = Array(); \$variables${var}[ '_self_' ] = $val;" . $initiator;
					}
					break;
				case 'EXECUTE':
					$args = explode( ',', $pseudo );
					for ( $i = 0; $i < count( $args ); $i++ )
					{
						$args[ $i ] = trim( $args[ $i ] ); // take care of possible spaces around commas
						$args[ $i ] = ( !is_numeric( $args[ $i ] ) && $args[ $i ]{0} != '$' ) ? "'${args[ $i ]}'" : $args[ $i ];
					}
					$args = ( count( $args ) > 0 ) ? ', ' . implode( ', ', $args ) : '';
					
					$Line = $delimiter . " \$TemplateOut .= function_$name( \$variables, \$miscellaneous $Counters $args ); " . $initiator;
					break;
				case 'CYCLE':
					$values = $name;
					$arrName = md5( $values );
					$values = "'" . str_replace( ',', "','", $values ) . "'";
					
					$Line = $delimiter . "\$$arrName = array( $values );  if ( !isset( \$${arrName}_i ) ) { \$${arrName}_i = 0; } \$TemplateOut .= \$${arrName}[ \$${arrName}_i ];  if ( \$${arrName}_i >= count( \$$arrName )-1  ) { \$${arrName}_i = 0; }else{ \$${arrName}_i++; }" . $initiator;
					break;
				
			}
			$code = str_replace( $original, $Line, $code );
		}
		
		if ( !$functionBody )
		{ // add the function crap into the compiled
			$compiled .= implode( "\n", $this->functions );
		}
		// the head of the template function
		$forExtras = "if ( !empty( \$extraList ) )\n{\n" .
					"	for ( \$i = 0; \$i < count( \$extraList ); \$i++ )\n" . 
					"	{\n" . 
					"		\${\$extraList[ \$i ]} = func_get_arg( \$i+3 );\n" . 
					"	}\n" . 
					"}\n";
		$compiled .= ( !$functionBody ) ? "// start of the function\n\nfunction template_$handle( \$variables, \$miscellaneous, \$extraList = Array() )\n{\n\$TemplateOut = '';\n $forExtras\n$initiator" : "\$TemplateOut = '';\n$initiator";
		// add the filters
		for ( $i = 0; $i < count( $neededFilters ); $i++ )
		{
			$compiled .= "${delimiter}include( 'filters/${neededFilters[ $i ]}' . phpEx ); \n\$filter_${neededFilters[ $i ]} = new ${neededFilters[ $i ]}();\n\n$initiator";
		}
		// add the code to the compiled stuff
		$compiled .= "${code}${delimiter}\nreturn \$TemplateOut;\n";
		$compiled .= ( !$functionBody ) ? "\n } \n ?>" : '';
		
		// put the plain texts back where they belong
		for ( $i = 0; $i < count( $plainText[ 0 ] ); $i++ )
		{
			$compiled = str_replace( '<!-- PLAIN ' . $i . ' --!>', $plainText[ 1 ][ $i ], $compiled );
		}
		
		// remove all html comments
		$compiled = preg_replace( "#<!--.*?-->#",'', $compiled );
		
		// a bit of speed improvement
		$compiled = preg_replace( '#\{(\(*)(\$variables.*?)(\)*)\}#', '" . $1$2$3 . "', $compiled );
		
		// $j = fopen( $Cl_root_path . 'cache/' . $handle. '.php', 'w' );
		// fwrite( $j, $compiled );
		// fclose( $j );
		
		// write down the compiled template
		$filename =  str_replace( array( $this->folder, '/' ), array( '', '.' ), $this->template_files[ $handle ][ 'file' ] );
		if ( !$f = fopen( $Cl_root_path . 'cache/template.' . $filename . '.' . $userdata[ 'user_skin' ] . phpEx, 'w' ) )
		{
			$errors->report_error( 'Error writing compiled template', CRITICAL_ERROR, 'template', '_compiler', __LINE__, ERROR_RAW );
		}
		if ( !fwrite( $f, $compiled ) )
		{
			$errors->report_error( 'Error writing compiled template', CRITICAL_ERROR, 'template', '_compiler', __LINE__, ERROR_RAW );
		}
		fclose( $f );
		
		// store some info
		$filename =  str_replace( array( $this->folder, '/' ), array( '', '.' ), $this->template_files[ $handle ][ 'file' ] );
		$this->template_files[ $handle ][ 'compiled' ] = TRUE;
		$cache->push( "template.$filename.time",EXECUTION_TIME, FALSE, ESSENTIAL );
		
		if ( !$functionBody )
		{
			return TRUE;
		}else
		{
			return $compiled;
		}
	}
	
	//
	// End template class
	//
}

?>
