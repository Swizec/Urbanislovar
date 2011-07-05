<?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:            lang_login.php[English]                  //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:      08th December 2005                       //
//     version:               0.1.1                              //
//                                                               //
///////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////
//                                                               //
// This program is free software; you can redistribute it        //
// and/or modify it under the terms of the GNU General Public    //
// License as published by the Free Software Foundation;         //
// either version 2 of the License, or (at your option)          //
// any later version.                                            //
//                                                               //
///////////////////////////////////////////////////////////////////

//
// the general language stuff for the board
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

// index
$lang[ 'index_head' ] = 'Login';
$lang[ 'index_login' ] = 'Login';
$lang[ 'index_password' ] = 'Password';
$lang[ 'index_username' ] = 'Username';
$lang[ 'index_welcome' ] = 'In order to login you need to be registered, doing so takes only a couple of clicks and grants you advanced capabilities and even special permissions if the website administrator has set them. Prior to registering you should be familiar with any terms of service pertaining to this website, but most of all you should not be a bot.';
$lang[ 'index_autolog' ] = 'Log me in automatically each visit';

$lang[ 'itool_username' ] = 'Enter the username with which you have registered to this website';
$lang[ 'itool_password' ] = 'Enter the password that you have assigned to your username';

// login
$lang[ 'log_errform' ] = 'The form was wrongly submitted. <br/> <a href="%s">Back to login</a>';
$lang[ 'log_errempty' ] = 'You seem to have left either the username or the password field empty, both are needed to login. <br/> <a href="%s">Back to login</a>';
$lang[ 'log_erruser' ] = 'The username you have entered does not exist within the database. <br/> <a href="%s">Back to login</a>';
$lang[ 'log_errpassword' ] = 'You have entered the wrong password and thus cannot be logged in. <br/> <a href="%s">Back to login</a>';
$lang[ 'log_in' ] = 'You have succesfully logged into this website. <br/> <a href="%s">Back to index</a>';
$lang[ 'log_forgotpass' ] = 'I forgot my password';
$lang[ 'log_register' ] = 'Register';
$lang[ 'log_toadmin' ] = 'Control panel';

// fetch pass
$lang[ 'forgot_head' ] = 'Retrieving password';
$lang[ 'forgot_welcome' ] = 'In order to receive your password by e-mail you need to provide the e-mail that you used to register otherwise the password you will recieve will not be decoded properly.';
$lang[ 'forgot_email' ] = 'E-Mail';
$lang[ 'forgot_mail' ] = "This e-mail has been sent to you upon your request to fetch your account's login password on %s. The username has not been provided for security reasons, meaning you should already know it.<br><br><br>You can login with your password <a href=\"%s\">here</a><br><br><br>Password: %s<br><br><br>%s administration.";
$lang[ 'forgot_mailsubject' ] = '%s password retrieval';
$lang[ 'forgot_done' ] = 'An e-mail has succesfully been dispatched to the address you provided. <br/> <a href="%s">Return to index</a>';

$lang[ 'ftool_email' ] = 'Enter exactly the same email as was used for registration';

$lang[ 'forgot_errform' ] = 'The form seems to have been wrongly submitted. <br/> <a href="%s">Back</a>';
$lang[ 'forgot_errempty' ] = 'You need to input an e-mail. <br/> <a href="%s">Back</a>';
$lang[ 'forgot_errfind' ] = 'The e-mail you have entered does not exist within our database. <br/> <a href="%s">Back</a>';
$lang[ 'forgot_errsend' ] = 'An unknown error has occured whilst trying to send you an e-mail with your password. <br/> <a href="%s">Back</a>';

// logout
$lang[ 'logout_msg' ] = 'You have been logged out and any permanent cookie that was set has been deleted. <br/> <a href="%s">Back to index</a>';

// register
$lang[ 'reg_head' ] = 'Registration';
$lang[ 'reg_welcome' ] = 'This website might require that you are registered to do certain things or use some features and this is where you accomplish just that. Provide the essential information that is needed and you will be able to login and do those things.';
$lang[ 'reg_username' ] = 'Username';
$lang[ 'reg_email1' ] = 'E-Mail';
$lang[ 'reg_email2' ] = 'Confirm E-Mail';
$lang[ 'reg_pass1' ] = 'Password';
$lang[ 'reg_pass2' ] = 'Confirm password';
$lang[ 'reg_captcha' ] = 'CAPTCHA';
$lang[ 'reg_captchainfo' ] = 'Click here to get the image.';
$lang[ 'reg_mailsubj' ] = '%s Registration';
$lang[ 'reg_mailbody' ] = 'You have received this e-mail because on %s you have registered for an account at %s. We hope that the stay will be as pleasant as possible and that you will find our service useful and helpful.<br/><br/>Here is your login information, keep it secret from anyone other than yourself.<br/>Username: %s<br/>Password: %s<br/><br/>You can login with your new account by visiting this link: <a href="%s">Click me</a>';
$lang[ 'reg_done' ] = 'You have been succesfully registered to this website.<br/>%s<br/><a href="%s">Return to index</a>';
$lang[ 'reg_mailno' ] = 'There was an attempt to send you a welcoming e-mail but it failed.';
$lang[ 'reg_mailyes' ] = 'A welcoming e-mail has been sent to the address you provided.';

$lang[ 'rtool_username' ] = 'This is the name of your account. It will show next to your actions and it is needed to login.';
$lang[ 'rtool_email1' ] = 'Your E-Mail is essential for the login process and password retrieval if you ever need it.';
$lang[ 'rtool_email2' ] = 'Insert exactly the same E-Mail as before';
$lang[ 'rtool_pass1' ] = 'The password is needed for login, keep it secret and keep it safe. Do NOT share it with anyone.';
$lang[ 'rtool_pass2' ] = 'Insert exactly the same password as before';
$lang[ 'rtool_captcha' ] = 'Write what you see in the picture into the box below';

$lang[ 'reg_errform' ] = 'The form was wrongly submitted.<br/><a href="%s">Return to registration</a>';
$lang[ 'reg_errempty' ] = 'You have left empty fields in the registration form; They must all be filled out.<br/><a href="%s">Return to registration</a>';
$lang[ 'reg_errcaptcha' ] = 'The CAPTCHA code you have entered does not match the one on the image. Return and fix the problem, if you were having problems reading it get a new one by clicking the image again.<br/><a href="%s">Return to registration</a>';
$lang[ 'reg_errmail' ] = 'The E-Mails you entered did not match or they were not E-Mails.<br/><a href="%s">Return to registration</a>';
$lang[ 'reg_errpass' ] = 'The passwords you entered did not match or they were shorter than 5 characters.<br/><a href="%s">Return to registration</a>';

?>