<?php


/**
*    MoreContent language [English]
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        21st February 2006
*     @package		     MoreContent
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'MoreContent';
$lang[ 'Side_manage' ] = 'Administer';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';

$lang[ 'Manage_title' ] = 'Administration';
$lang[ 'Manage_explain' ] = 'Use the menus as if they were real and use the editor at the bottom of the page to administer the content of the current page.';

$lang[ 'Menu_title' ] = 'Menu';
$lang[ 'Menu_na' ] = 'N/A';
$lang[ 'Menu_add' ] = 'Add Menu';
$lang[ 'Menu_addtitle' ] = 'Title';
$lang[ 'Menu_added' ] = 'The menu was succesfully added';
$lang[ 'Menu_delete' ] = 'Delete entry';
$lang[ 'Menu_deleted' ] = 'Entry succesfully deleted';
$lang[ 'Menu_change' ] = 'Change title';
$lang[ 'Menu_changed' ] = 'Title succesfully changed';
$lang[ 'Menu_up' ] = 'Back to parent';

$lang[ 'Content_done' ] = 'The content was succesfully modified';

?>