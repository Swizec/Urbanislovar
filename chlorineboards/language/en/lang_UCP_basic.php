<?php

/**
* UCP register panel language stuff -- English
*     @class		   lang_UCP_basic
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        7th Decemberl 2006
*     @license http://opensource.org/licenses/gpl-license.php
*     @filesource
*/
// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'Basic Details';
$lang[ 'Sidebar_pass' ] = 'Password and e-mail';
$lang[ 'Sidebar_lang' ] = 'Change language';
$lang[ 'Sidebar_skin' ] = 'Change skin';

$lang[ 'lang_title' ] = 'Language setting';
$lang[ 'lang_explain' ] = 'Here you can change the language in which this website is shown to you. When you set this the settings of your browser no longer matter as the setting has precedence over the browser. The box below will show a preview of your setting';
$lang[ 'lang_name' ] = 'Language';
$lang[ 'lang_browser' ] = 'Browser defined';
$lang[ 'lang_select' ] = 'Select';
$lang[ 'lang_done' ] = 'The language %s has been succesfully chosen as the display language of the website';
$lang[ 'lang_fuck' ] = 'The website doesn\'t support language %s';

$lang[ 'skin_title' ] = 'Skin setting';
$lang[ 'skin_explain' ] = 'Here you can change the appearance of this website according to some predefined skins.';
$lang[ 'skin_name' ] = 'Skin';
$lang[ 'skin_select' ] = 'Select';
$lang[ 'skin_done' ] = 'The skin %s has been succesfully chosen as the skin of the website';
$lang[ 'skin_fuck' ] = 'The website doesn\'t support skin %s';

$lang[ 'password_title' ] = 'Password/E-Mail settings';
$lang[ 'password_explain' ] = 'In this panel you can change your e-mail or password. To change either you will have to input your old password for security reasons. Leave the option you do not want to change empty.';
$lang[ 'password_oldpass' ] = 'Old password';
$lang[ 'password_newpass' ] = 'New password';
$lang[ 'password_password' ] = 'Password';
$lang[ 'password_password2' ] = 'Retype password';
$lang[ 'password_errpassword' ] = 'You have entered a wrong password';
$lang[ 'password_nomatch' ] = 'The passwords you have entered do not match';
$lang[ 'password_nochange' ] = 'You have not changed any personal information';
$lang[ 'password_passchanged' ] = 'The password has been succesfully changed';
$lang[ 'password_newmail' ] = 'New E-Mail';
$lang[ 'password_oldmail' ] = 'Current E-Mail';
$lang[ 'password_mailto' ] = 'Change to';
$lang[ 'password_mailchanged' ] = 'The e-mail has been succesfully changed';
$lang[ 'password_mailbody' ] = 'You have received this e-mail because you have changed personal information on %s for an account at %s. We hope that your continued stay will be a pleasant one.<br/><br/>Here is your new login information, keep it secret from anyone other than yourself.<br/>Username: %s<br/>Password: %s<br/><br/>';
$lang[ 'password_mailsubj' ] = 'Account details changed';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not supported.';
$lang[ 'Wrong_form' ] = 'Wrongly submitted form';

?>