<?php
/**
*     defines the install class.
*     @file                install.php
*     @see install
*/
/**
* This is the basic db initialisator for fresh installations
*     @class		   install
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.3.2
*     @since        18th September 2005
*     @package		     ClB_base
*     @subpackage	     basic_install
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
* 
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// vars explanation
// template :: the template engine class
// encryption :: the encryption engine class
// Sajax :: the ajax engine
// lang :: the language stuff

// create this class
$vars = array( 'template', 'encryption', 'Sajax', 'lang' );
$visible = array( 'private', 'private', 'private', 'private' );
eval( Varloader::createclass( 'install', $vars, $visible ) );
// end class creation

class install extends install_def
{	
	/**
	*  this creates an object of this class
	* @usage $install = new install(  );
	*/
	function install( )
	{
		// this is some basic classes we need to run this thing properly
		global $Cl_root_path;
		
		$this->template = new Template( '', $Cl_root_path . 'install/', FALSE ); // template engine
		$this->encryption = new Encryption( ); // encryption engine
		$this->Sajax = new Sajax( FALSE, 'GET' );
		$this->security = new Security( FALSE ); // security engine
		// load lang stuff
		include( $Cl_root_path . 'install/lang.php' );
		$this->lang = $lang;
		
		// enparse all globals for sql security
		$parsed = $this->security->parsevar( array( $_POST, $_GET, $_SESSION, $_SERVER, $_ENV, $_COOKIE, $_FILES ), ADD_SLASHES ); 
		$_POST = $parsed[ 0 ];
		$_GET = $parsed[ 1 ];
		$_SESSION = $parsed[ 2 ];
		$_SERVER = $parsed[ 3 ];
		$_ENV = $parsed[ 4 ];
		$_COOKIE = $parsed[ 5 ];
		$_FILES = $parsed[ 6 ];
		
		// some basic constants needed
		define( 'ADD_SLASHES', 'addslashes' );
		define( 'REM_SLASHES', 'remslashes' );
	}
	
	/**
	*  generates the header of the board
	* @usage $basic_gui->makeheader();
	*/
	function makeheader( )
	{
		global $board_config, $Cl_root_path;
		
		// load the template file
		$this->template->assign_files( array(
			'header' => 'header.tpl'
		) );
		
		// some stuff to add to the image array
		$this->template->assign_var_levels( '', 'IMG', array(
			'LOGO' => $Cl_board_config . 'images/ClB_logo.png',
		) );
		
		// the title and stuff
		$this->template->assign_var_levels( '', 'HEAD', array(
			'SITENAME' => $this->lang[ 'title' ],
			'CONTENT_ENCODING' => 'iso-8859-1',
		) );
		
		// output the header
		$this->template->output( 'header' );
	}
	
	/**
	*  generates the footer of the board
	* @usage $basic_gui->makefooter();
	*/
	function makefooter( )
	{
		global $template, $board_config, $basic_lang;
		
		// load the template file
		$this->template->assign_files( array(
			'footer' => 'footer.tpl'
		) );
		
		// output it
		$this->template->output( 'footer' );
	}
	
	/**
	*  recursively chmods a folder
	* @param string $folder the target
	* @param octal $mode the chmod
	* @returns bool bool of success
	*/
	function chmodparent( $folder, $mode )
	{
		$fold = explode( '/', $folder );
		$folder = '';
		
		foreach ( $fold as $now )
		{
			$folder .= $now . '/';
			if ( $now == '.' || $now == '..' )
			{
				continue;
			}
			$change = '$good = @chmod( \'' . $folder . '\', ' . $mode . ' );';
			eval( $change );
			if ( !$good )
			{
				return FALSE;
			}
		}
		return TRUE;
	}
	
	/**
	*  turns all newline characters into '\n'
	* @param string $str the target
	* @returns string the string
	*/
	function gennuline( $str )
	{
		return str_replace( "\r", "\n", str_replace( "\r\n", "\n", $str ) );
	}
	
	/**
	*  this does the installation part of things
	* @usage $install->commence( );
	*/
	function commence( )
	{
		global $Cl_root_path;
	
		$this->makeheader(); // the top of the page
		
		// see if the thing is already installed perhaps
		if ( defined( 'CLB_INSTALLED' ) && CLB_INSTALLED == TRUE )
		{
			echo "The installation was already done";
			$this->makefooter();
			ob_end_flush();
			exit;
		}
		
		// determine the level
		$level = ( !empty( $_GET[ 'level' ] ) ) ? $this->security->parsevar( $_GET[ 'level' ], REM_SLASHES ) : 0;
		
		// so the template knows what the level is
		$this->template->assign_vars( array(
			'LEVEL' => $level
		) );
		
		// do upon the level
		switch ( $level )
		{
			case 0:
				global $db_data;
			
				// load up teh form
				$this->template->assign_files( array(
					'body' => 'main.tpl'
				) );
				// some basic vars
				$this->template->assign_vars( array(
					'S_ACTION' => '?level=1',
					
					'L_GREET' => $this->lang[ 'Greet' ],
					
					'L_SQL' => $this->lang[ 'Sql_conf' ],
					'L_DBTYPE' => $this->lang[ 'Sql_type' ],
					'L_MYSQL' => $this->lang[ 'Sql_mysql' ],
					'L_MYSQL4' => $this->lang[ 'Sql_mysql4' ],
					'L_DB2' => $this->lang[ 'Sql_db2' ],
					'L_MSACCESS' => $this->lang[ 'Sql_msaccess' ],
					'L_MSSQLODBC' => $this->lang[ 'Sql_mssqlodbc' ],
					'L_MSSQL' => $this->lang[ 'Sql_mssql' ],
					'L_POSTGRES7' => $this->lang[ 'Sql_postgres7' ],
					
					strtoupper( $db_data[ 'type' ] ) => 'selected',
					
					'L_DBHOST' => $this->lang[ 'Sql_host' ],
					'L_DBUSER' => $this->lang[ 'Sql_user' ],
					'L_DBPASS' => $this->lang[ 'Sql_pass' ],
					'L_DBNAME' => $this->lang[ 'Sql_name' ],
					'L_DBPREF' => $this->lang[ 'Sql_pref' ],
					
					'L_CHC' => $this->lang[ 'Chc_conf' ],
					'L_CHCIP' => $this->lang[ 'Chc_ip' ],
					'L_CHCPORT' => $this->lang[ 'Chc_port' ],
					'L_CHCTYPE' => $this->lang[ 'Chc_type' ],
					'L_CHCDSK' => $this->lang[ 'Chc_dsk' ],
					'L_CHCMEM' => $this->lang[ 'Chc_mem' ],
					'L_CHCENABLE' => $this->lang[ 'Chc_enable' ],
					
					'L_SRV' => $this->lang[ 'Srv_conf' ],
					'L_SRVDOMAIN' => $this->lang[ 'Srv_domain' ],
					'L_SRVPATH' => $this->lang[ 'Srv_path' ],
					'L_SCRPATH' => $this->lang[ 'Scr_path' ],
					'L_SRVEXP' => $this->lang[ 'Srv_exp' ],
					'L_SRVSEC' => $this->lang[ 'Srv_sec' ],
					'L_SRVNAME' => $this->lang[ 'Srv_name' ],
					
					'DOMAIN' => $_SERVER[ 'SERVER_NAME' ],
					'PATH' => str_replace( strrchr( $_SERVER[ 'SCRIPT_NAME' ], '/' ), '', $_SERVER[ 'SCRIPT_NAME' ] ) . '/',
					'EXPIRE' => 3600,
					
					'L_ADM' => $this->lang[ 'Adm_conf' ],
					'L_ADMMAIL' => $this->lang[ 'Adm_mail' ],
					'L_ADMNAME' => $this->lang[ 'Adm_name' ],
					'L_ADMPASS1' => $this->lang[ 'Adm_pass1' ],
					'L_ADMPASS2' => $this->lang[ 'Adm_pass2' ],
				) );
				// print the page
				$this->template->output( 'body' );
				break;
			case 1:
				// check for pass mismatch first eh
				$adminpass = ( isset( $_POST[ 'admpass1' ] ) ) ? $_POST[ 'admpass1' ] : '';
				$pass2 = ( isset( $_POST[ 'admpass2' ] ) ) ? $_POST[ 'admpass2' ] : '';
				if ( $adminpass != $pass2 )
				{ // no
					echo $this->lang[ 'Mismatch_pass' ];
					$this->makefooter();
					ob_end_flush();
					exit;
				} 
				
				// gen_config.php
				// chmod the gen_config to a writable mode
				$this->chmodparent( $Cl_root_path . 'kernel/config/', 0744 );
				chmod( $Cl_root_path . 'kernel/config/gen_config.php', 0744 );
				
				// save the posted values in a more sensible way
				$dbhost = ( isset( $_POST[ 'dbhost' ] ) ) ? $_POST[ 'dbhost' ] : '';
				$dbuser = ( isset( $_POST[ 'dbuser' ] ) ) ? $_POST[ 'dbuser' ] : '';
				$dbpass = ( isset( $_POST[ 'dbpass' ] ) ) ? $_POST[ 'dbpass' ] : '';
				$dbname = ( isset( $_POST[ 'dbname' ] ) ) ? $_POST[ 'dbname' ] : '';
				$dbpref = ( isset( $_POST[ 'dbpref' ] ) ) ? $_POST[ 'dbpref' ] : '';
				
				// get config file contents
				$file = file_get_contents( $Cl_root_path . 'kernel/config/gen_config.php' );
				// do the replaces
				$search = array(
						'$db_data[ \'username\' ] = \'\';',
						'$db_data[ \'password\' ] = \'\';',
						'$db_data[ \'name\' ] = \'\';',
						'$db_data[ \'host\' ] = \'\';',
						'$db_data[ \'table_prefix\' ] = \'\';',
						'$admin_email = \'\'',
						'?>'
					);
				$replace = array(
						'$db_data[ \'username\' ] = \'' . $dbuser . '\';',
						'$db_data[ \'password\' ] = \'' . $dbpass . '\';',
						'$db_data[ \'name\' ] = \'' . $dbname . '\';',
						'$db_data[ \'host\' ] = \'' . $dbhost . '\';',
						'$db_data[ \'table_prefix\' ] = \'' . $dbpref . '\';',
						'$admin_email = \'' . $_POST[ 'admmail' ] . '\'',
						'define( \'CLB_INSTALLED\', TRUE );' . "\n" . '?>'
					);
				$file = str_replace( $search, $replace, $file );
				
				// write it back
				$f = fopen( $Cl_root_path . 'kernel/config/gen_config.php', 'wb' );
				fwrite( $f, $file );
				fclose( $f );
				
				// chmod it back to locked
				chmod( $Cl_root_path . 'kernel/config/gen_config.php', 0544 );
				
				// cache_config.php
				// make it writable
				chmod( $Cl_root_path . 'kernel/config/cache_config.php', 0744 );
				
				// save the posted values in a more sensible way
				$chcip = ( isset( $_POST[ 'chcip' ] ) ) ? $_POST[ 'chcip' ] : '';
				$chcport = ( isset( $_POST[ 'chcport' ] ) ) ? intval( $_POST[ 'chcport' ] ) : '';
				$chctype = ( isset( $_POST[ 'chctype' ] ) ) ? $_POST[ 'chctype' ] : '';
				$chcenable = ( isset( $_POST[ 'chcenable' ] ) ) ? 'TRUE' : 'FALSE';
				// generate the prefix
				$chcpref = base64_encode( date( 'l - d F Y - H:i:s - T', time() ) );
				
				// get the config file contents
				$file = file_get_contents( $Cl_root_path . 'kernel/config/cache_config.php' );
				// do the replaces
				$search = array(
						'$cache_config[ \'prefix\' ] = \'\';',
						'$cache_config[ \'ip\' ][ 0 ] = \'\';',
						'$cache_config[ \'enabled\' ] = \'\';',
						'$cache_config[ \'type\' ] = \'\';'
					);
				$replace = array(
						'$cache_config[ \'prefix\' ] = \'' . $chcpref . '\';',
						'$cache_config[ \'ip\' ][ 0 ] = \'' . $chcip . ':' . $chcport . '\';',
						'$cache_config[ \'enabled\' ] = \'' . $chcenable . '\';',
						'$cache_config[ \'type\' ] = \'' . $chctype . '\';',
					);
				$file = str_replace( $search, $replace, $file );
				
				// write it back
				$f = fopen( $Cl_root_path . 'kernel/config/cache_config.php', 'wb' );
				fwrite( $f, $file );
				fclose( $f );
				
				// lock it back
				chmod( $Cl_root_path . 'kernel/config/cache_config.php', 0544 );
				// chmod the folder back
				$this->chmodparent( $Cl_root_path . 'kernel/config/', 0544 );
				
				// do the sql now
				$domain = ( isset( $_POST[ 'srvdomain' ] ) ) ? $_POST[ 'srvdomain' ] : '';
				$path = ( isset( $_POST[ 'srvpath' ] ) ) ? $_POST[ 'srvpath' ] : '';
				$path2 = ( isset( $_POST[ 'scrpath' ] ) ) ? $_POST[ 'scrpath' ] : '';
				$expire = ( isset( $_POST[ 'srvexp' ] ) ) ? intval( $_POST[ 'srvexp' ] ) : '';
				$sitename = ( isset( $_POST[ 'srvname' ] ) ) ? $_POST[ 'srvname' ] : '';
				$secure = ( isset( $_POST[ 'srvsec' ] ) ) ? '1' : '0';
				$adminname = ( isset( $_POST[ 'admname' ] ) ) ? $_POST[ 'admname' ] : '';
				$adminmail = ( isset( $_POST[ 'admmail' ] ) ) ? $_POST[ 'admmail' ] : '';
				// make the key prefix
				$keyprefix = str_shuffle( 'aGedFDTkomzJyVjgHpqSZLAcBIUrCfvKsMbEOihwulWNRYnxPXtQ_' );
				
				// get the sql
				$sql = file_get_contents( $Cl_root_path . 'install/ClB.sql' );
				// encrypt admin password
				$pref = $keyprefix . sha1( $keyprefix ) . $domain;
				$key = $pref . base64_encode( $adminmail );
				
 				$adminpass = $this->security->parsevar( $this->encryption->encrypt( $key, $adminpass, 30 ), ADD_SLASHES, TRUE );
 				
				// parse it
				$search = array(
						'$domain',
						'$path2',
						'$path',
						'$expire',
						'$sitename',
						'$keyprefix',
						'$secure',
						'ClB_',
						'$adminname',
						'$adminmail',
						'$adminpass',
					);
				$replace = array(
						$domain,
						$path2,
						$path,
						$expire,
						$sitename,
						$keyprefix,
						$secure,
						$dbpref,
						$adminname,
						$adminmail,
						$adminpass,
					);
				$sql = str_replace( $search, $replace, $sql );	
				// connect to the db
				echo $sql;
				die();
				
				global $db_data;
				include ( $Cl_root_path . 'kernel/database/' . $db_data[ 'type' ] . phpEx );
				$db = new sql_db( $dbhost, $dbuser, $dbpass, $dbname, false);
				if(!$db->db_connect_id)
				{
					echo 'Could not connect to the database';
					$this->makefooter();
					ob_end_flush();
					exit;
				}
				
				// execute the sql
				foreach ( explode( ";\n", $sql ) as $sql )
				{
					if ( empty( $sql ) )
					{
						continue;
					}
					if ( !$res = $db->sql_query( $sql ) )
					{
						echo $this->lang[ 'Sql_fail' ];
						echo '<br>' . mysql_error();
						echo '<br>' . $sql;
						$this->makefooter();
						ob_end_flush();
						exit;
					}
				}
				echo $this->lang[ 'Install_done' ];
				break;
		}
		
		$this->makefooter(); // teh very bottom
		
		ob_end_flush(); // flush buffered page
	}
	
	//
	// End class cache
	//
}
?>