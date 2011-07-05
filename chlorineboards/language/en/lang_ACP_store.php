<?php


/**
*    store language [English]
*     @author              swizec
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        2nd March 2007
*     @package		     store
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'Store';
$lang[ 'Side_manage' ] = 'Manage items';
$lang[ 'Side_categories' ] = 'Manage categories';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';

$lang[ 'Manage_title' ] = 'Manage items';
$lang[ 'Manage_explain' ] = 'With this panel you can manage store items. Add new ones by simply filling out the form and clicking submit, for other tasks you first have to choose an existant item.';
$lang[ 'Manage_description' ] = 'Item Description';
$lang[ 'Manage_title2' ] = 'Item title';
$lang[ 'Manage_toedit' ] = 'Select to edit';
$lang[ 'Manage_language' ] = 'Language';
$lang[ 'Manage_delete' ] = 'Hide from public';
$lang[ 'Manage_category' ] = 'Category';
$lang[ 'Manage_price' ] = 'Price';
$lang[ 'Manage_thumbnail' ] = 'Thumbnail';
$lang[ 'Manage_success' ] = 'The changes have been succesfully comited<br /><br /><a href="%s">Back whence you came</a>';
$lang[ 'Manage_upload' ] = 'Upload new';

$lang[ 'Cat_name' ] = 'Name';
$lang[ 'Cat_lang' ] = 'Language';
$lang[ 'Cat_title' ] = 'Categories';
$lang[ 'Cat_explain' ] = 'Here you can manage the categories under which items will be classified';
$lang[ 'Cat_delete' ] = 'Delete';
$lang[ 'Cat_done' ] = 'The category has been succesfully edited';
$lang[ 'Cat_choose' ] = 'Choose to edit';

$lang[ 'Month_0' ] = 'January';
$lang[ 'Month_1' ] = 'February';
$lang[ 'Month_2' ] = 'March';
$lang[ 'Month_3' ] = 'April';
$lang[ 'Month_4' ] = 'May';
$lang[ 'Month_5' ] = 'June';
$lang[ 'Month_6' ] = 'July';
$lang[ 'Month_7' ] = 'August';
$lang[ 'Month_8' ] = 'September';
$lang[ 'Month_9' ] = 'October';
$lang[ 'Month_10' ] = 'November';
$lang[ 'Month_11' ] = 'December';

?>