<?php
/**
*     @file                lang.php
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0;
*     @since        18th September 2005
*     @package		     ClB_base
*     @subpackage	     basic_install
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
* @uses
* This is the language stuff for the install
*/


$lang[ 'title' ] = 'Chlorine Boards initialization';

$lang[ 'Greet' ] = 'This is the post-install screen of the Chlorine Boards base. You are seeing this because it would be too complex to do this step via the console with which you have just installed the boards, what this will do is ask you to input some basic configuration and then set up the database (which should already exist) and then you will be asked to go back to the console and finish up the install by installing the language and template files';

$lang[ 'Sql_conf' ] = 'Database Configuration';
$lang[ 'Sql_host' ] = 'Database Server Hostname';
$lang[ 'Sql_user' ] = 'Database Username';
$lang[ 'Sql_pass' ] = 'Database Password';
$lang[ 'Sql_name' ] = 'Database Name';
$lang[ 'Sql_pref' ] = 'Table Prefix';

$lang[ 'Chc_conf' ] = 'Cache Configuration';
$lang[ 'Chc_ip' ] = 'Cache Server IP';
$lang[ 'Chc_port' ] = 'Cache Server Port';
$lang[ 'Chc_type' ] = 'Cache Type';
$lang[ 'Chc_dsk' ] = 'Disk (local)';
$lang[ 'Chc_mem' ] = 'Memory (remote)';
$lang[ 'Chc_enable' ] = 'Enable Cache';

$lang[ 'Srv_conf' ] = 'Some Basic Server Configuration';
$lang[ 'Srv_domain' ] = 'Domain Name';
$lang[ 'Srv_path' ] = 'Session Path';
$lang[ 'Srv_exp' ] = 'Session Expire Time (in seconds)';
$lang[ 'Srv_sec' ] = 'Use Secure Sessions';
$lang[ 'Srv_name' ] = 'Website Name';
$lang[ 'Scr_path' ] = 'Script Path';

$lang[ 'Adm_conf' ] = 'Administrator Configuration';
$lang[ 'Adm_mail' ] = 'Administrator E-mail';
$lang[ 'Adm_name' ] = 'Administrator Username';
$lang[ 'Adm_pass1' ] = 'Administrator Password';
$lang[ 'Adm_pass2' ] = 'Administrator Password (confirm)';

$lang[ 'Mismatch_pass' ] = 'The entered passwords do not match';
$lang[ 'Sql_fail' ] = 'Initiating the database failed';
$lang[ 'Install_done' ] = 'Installation is now complete. You should return to the console and delete the folder install and the file install.php. It is advised that you now install a template and a language, then proceed to installing the modules you want.';

?>