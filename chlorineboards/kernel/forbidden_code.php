<?php

/**
*     defines the forbidden_code class
*     @file                forbidden_code.php
*     @see Forbidden_code
*/
/**
* This is all the code that gets removed when
* parsing code with the template engine
*     @class		   Forbidden_code
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.2
*     @since        25th May 2005
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
// php :: regexes for unsafe php code

// create this class
global $Varloader;;
$vars = array( 'php' );
$visible = array( 'public' );
eval( Varloader::createclass( 'forbidden_code', $vars, $visible ) );
// end class creation

class Forbidden_code extends forbidden_code_def
{
	/**
	* for creating a new list
	*/
	function forbidden_code( )
	{
		$this->php = $this->makephp();
	}

//
// PHP
//
	/**
	* returns the array with forbidden php code
	* @return mixed
	*/
	function makephp( ) 
	{
		$php = array(
			// general functions( usable for making external unchecked php execute
			'#(include|exec|eval|escapeshellarg|escapeshellcmd|passthru|shell_exec|system)\(.*?\)#i',
			'#(proc|ncurses|ssh2.*?)\(.*?\)#i',
			
			// database functions
			
			/* dba db */ '#(dba.*?)\(.*?\)#i',
			/* dbase db */ '#(dbase.*?)\(.*?\)#i',
			/* dbm db */ '#(dbm.*?)\(.*?\)#i',
			/* dbplus db */ '#(dbplus.*?)\(.*?\)#i',
			/* dbx */ '#(dbx.*?)\(.*?\)#i',
			/* front base */ '#(fbsql.*?)\(.*?\)#i',
			/* filepro */ '#(filepro.*?)\(.*?\)#i',
			/* firebird ibase */ '#(ibase.*?)\(.*?\)#i',
			/* IBM db2 */ '#(db2.*?)\(.*?\)#i',
			/* informix */ '#(ifx.*?)\(.*?\)#i',
			/* ingres II */ '#(ingres.*?)\(.*?\)#i',
			/* maxdb */ '#(maxdb.*?)\(.*?\)#i',
			/* mSQL */ '#(msql.*?)\(.*?\)#i',
			/* mssql */ '#(mssql.*?)\(.*?\)#i',
			/* mysql */ '#(mysql|mysqli.*?)\(.*?\)#i',
			/* oracle */ '#(oci|lob|ora.*?)\(.*?\)#i',
			/* ovrimos */ '#(ovrimos.*?)\(.*?\)#i',
			/* pdo */ '#(pdo.*?)\(.*?\)#i',
			/* pgsql */ '#(pg.*?)\(.*?\)#i',
			/* SQlite */  '#(sqlite.*?)\(.*?\)#i',
			/* odbc */ '#(odbc.*?)\(.*?\)#i',
				
			// file handling functions
			
			/* compression */ '#(bz|rar|zip.*?)\(.*?\)#i', '#(readgzfile|zlib_get_coding_type)\(.*?\)#i',
			/* pdf */ '#(cpdf|pdf.*?)\(.*?\)#i',
			/* direct IO */ '#(dio.*?)\(.*?\)#i',
			/* directory */ '#(chdir|chroot|dir|closedir|getcwd|opendir|reddir|rewinddir|scandir)\(.*?\)#i',
			/* XML */ '#(dom|xptr|xpath|simplexml|libxml|xml|xmlrpc.*?)\(.*?\)#i',
			/* .net */ '#(dotnet.*?)\(.*?\)#i',
			/* file alteration monitor */ '#(fam_.*?)\(.*?\)#i',
			/* general */ '#(basename|chgrp|chmod|chown|clearstatcache|copy|delete|fclose|feof|fflush|fgetc|fgetcsv|fgets|fgetss|flock|fnmatch|fopen|fpassthru|fputc|fputcsv|fputs|fread|fscanf|fseek|fstat|ftell|ftruncate|fwrite|glob|is_dir|is_executable|is_file|is_link|is_readbale|is_uploaded_file|is_writable|is_writeable|link|lstat|mkdir|move_uploaded_file|parse_ini_file|pathinfo|pclose|pclose|popen|readfile|readlink|realpath|rename|rewind|rmdir|set_file_buffer|stream_set_write_buffer|stat|symlink|tempnam|tmpfile|touch|umask|unlink)\(.*?\)#i',
			'#(file.*?)\(.*?\)#i',
			/* ftp */ '#(ftp.*?)\(.*?\)#i',
			/* id3 tags */ '#(id3.*?)\(.*?\)#i',
			/* images */ '#(image|ipt.*?)\(.*?\)#i', '#(jpeg|png.*?)\(.*?\)#i',
			/* ldap */ '#(ldap.*?)\(.*?\)#i',
			/* posix */ '#(posix.*?)\(.*?\)#i',
			/* misc */ '#(xattr|xdiff|xslt|yaz.*?)\(.*?\)#i',
				
			// network
				
			'#(checkdnssr|closelog|define_syslog_variables|openlog|pfsockopen|syslog)\(.*?\)#i',
			'#(debugger|dns|inet|socket|stream.*?)\(.*?\)#i',
				
			// error reporting
				
			'#(debug|error.*?)\(.*?\)#i',
			'#(restore_error_handler|restore_exception_handler|set_error_handler|set_exception_handler)\(.*?\)#i',
			
			// php info stuff
			'#(assert_options|assert|dl|extensions_loaded|main|memory_get_usage|putenv|restore_include_path|set_include_path|set_magic_quotes|set_magic_quotes_runtime|version_compare)\(.*?\)#i',
			'#(get|ini|php|zend|yp.*?)\(.*?\)#i',
			
			// other
			
			/* forms like pdf */'#(fdf.*?)\(.*?\)#i',
			/* function handling */'#(call_user_func_array|call_user_func|func_get_arg|func_get_args|func_num_args|function_exists|get_defined_functions|register_shutdown_function|register_tick_function|unregister_tick_function)\(.*?\)#i',
			/* http things */ '#(header|headers_list|headers_sent|setcookie|setrawcookie)\(.*?\)#i',
			/* virtual servers */ '#(iis.*?)\(.*?\)#i',
			/* encryption */ '#(mcrypt|openssl.*?)\(.*?\)#i',
			/* cache */ '#(memcache.*?)\(.*?\)#i',
			/* misc */ '#(die|exit.*?)\(.*?\)#i',
			/* sessions */ '#(msession.*?)\(.*?\)#i',
			/* output control */ '#(ob|output.*?)\(.*?\)#i', '#(flush)\(.*?\)#i',
			/* process control */ '#(pcntl.*?)\(.*?\)#i',
		);
		
		return $php;
	}

}

?>