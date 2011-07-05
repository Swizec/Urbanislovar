<?php


/**
*    ShowEvent language [English]
*     @author              swizec;
*     @contact          swizec@swizec.com
*     @version               0.1.0
*     @since        20th April 2007
*     @package		     ShowEvent
*     @license http://opensource.org/licenses/gpl-license.php
* @filesource
*/

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}

$lang[ 'Sidebar_title' ] = 'ShowEvent';
$lang[ 'Side_manage' ] = 'Manage shows';
$lang[ 'Side_categories' ] = 'Manage categories';
$lang[ 'Side_mails' ] = 'Invites';

$lang[ 'Wrong_mode' ] = 'The mode you have requested is not recognised.';
$lang[ 'Wrong_form' ] = 'The form was wrongly submitted';

$lang[ 'Manage_title' ] = 'Manage shows and events';
$lang[ 'Manage_explain' ] = 'With this panel you can manage shows and events. Add new ones by simply filling out the form and clicking submit, for other tasks you first have to choose an existant show or event.';
$lang[ 'Manage_description' ] = 'Show/Event Description';
$lang[ 'Manage_excerpt' ] = 'Show/Event Description Excerpt';
$lang[ 'Manage_title2' ] = 'Show/Event title';
$lang[ 'Manage_subtitle' ] = 'Subtitle';
$lang[ 'Manage_event' ] = 'Event';
$lang[ 'Manage_show' ] = 'Show';
$lang[ 'Manage_toedit' ] = 'Select to edit';
$lang[ 'Manage_language' ] = 'Language';
$lang[ 'Manage_isevent' ] = 'Check if event, uncheck if show';
$lang[ 'Manage_delete' ] = 'Hide from public';
$lang[ 'Manage_location' ] = 'Location';
$lang[ 'Manage_category' ] = 'Category';
$lang[ 'Manage_time_to' ] = 'Descriptive to (time)';
$lang[ 'Manage_time_from' ] = 'Descriptive from (time)';
$lang[ 'Manage_time_duration' ] = 'Descriptive time duration';
$lang[ 'Manage_time_to_stamp' ] = 'To';
$lang[ 'Manage_time_from_stamp' ] = 'From';
$lang[ 'Manage_price' ] = 'Price';
$lang[ 'Manage_thumbnail' ] = 'Thumbnail';
$lang[ 'Manage_success' ] = 'The changes have been succesfully comited<br /><br /><a href="%s">Back whence you came</a>';
$lang[ 'Manage_upload' ] = 'Upload new';
$lang[ 'Manage_gallery' ] = 'Gallery';
$lang[ 'Manage_delete2' ] = 'Remove';
$lang[ 'Manage_addimg' ] = 'Add an image';
$lang[ 'Manage_schedule' ] = 'Schedule';
$lang[ 'Manage_additional' ] = 'Additional';

$lang[ 'Cat_name' ] = 'Name';
$lang[ 'Cat_lang' ] = 'Language';
$lang[ 'Cat_title' ] = 'Categories';
$lang[ 'Cat_explain' ] = 'Here you can manage the categories under which shows and events will be classified';
$lang[ 'Cat_delete' ] = 'Delete';
$lang[ 'Cat_done' ] = 'The category has been succesfully edited';
$lang[ 'Cat_choose' ] = 'Choose to edit';
$lang[ 'Cat_description' ] = 'Description';
$lang[ 'Cat_parent' ] = 'Parent';
$lang[ 'Cat_image' ] = 'Image';

$lang[ 'Invites_title' ] = 'Invitations';
$lang[ 'Invites_explain' ] = 'Below is a list of invitations that need to be sent out today. You can edit the message and then send out the emails.';
$lang[ 'Invites_name' ] = 'Name';
$lang[ 'Invites_mail' ] = 'E-Mail';
$lang[ 'Invites_item' ] = 'Show/Event';
$lang[ 'Invites_language' ] = 'Languge';
$lang[ 'Invites_send' ] = 'Send';
$lang[ 'Invites_messages' ] = 'The e-mail in all the needed languages, you can change these. The words encapsulated by # will be replaced by their values, so please include them somewhere in the message.';
$lang[ 'Invites_message' ] = "Greetings #name#,\r\n\r\nYou are kindly invited to #item# that starts on #time# as per your request.\r\n\r\nBest regards,\r\nThe managment";
$lang[ 'Invites_unsubscribe' ] = "You are subsrcibed to all future reminders. To unsubscribe visit this address: %s";
$lang[ 'Invites_sending' ] = 'The E-Mails are being sent. Please do not close this window lest you interrupt the process.';
$lang[ 'Invites_subject' ] = 'E-Mail Reminder';
$lang[ 'Invites_status' ] = '%d out of %d E-Mails sent with %d failed sends.';
$lang[ 'Invites_none' ] = 'No E-Mails to send right now';

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