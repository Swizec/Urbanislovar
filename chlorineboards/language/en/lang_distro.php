 <?php


///////////////////////////////////////////////////////////////////
//                                                               //
//     file:            lang_distro.php[English]                 //
//     scripter:              swizec                             //
//     contact:          swizec@swizec.com                       //
//     started on:       04th November 2005                      //
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
// the language stuff for the distro module
//

// basic security
if ( !defined( 'RUNNING_CL' ) )
{
	ob_clean();
	die( 'You bastard, this is not for you' );
}


// index
$lang[ 'Inx_title' ] = 'Welcome';
$lang[ 'Inx_welcome' ] = 'Chlorine Boards is an advance open source website solution. What started out as a module oriented board system has grown out of the box and has become the first open source website solution with selfinstallable modules. With the use of appropriate modules you can make it into anything your imagination can think of, it only takes a simple command to perform the magic.';
$lang[ 'Inx_features' ] = 'Main Features';
$lang[ 'Inx_featurelist' ] = '<li><b>Ease of use</b></li><li><b>Module oriented</b></li><li><b>Memory caching</b></li><li><b>Multilingual interface</b></li><li><b>Supports most popular database servers</b></li><li><b>Snazzy console</b></li><li><b>Customization via templates</b></li><li><b>Great security</b></li><li><b>Very fast</b></li>';
$lang[ 'Inx_note' ] = 'Hosting donated by <a href="http://www.unimatrix-one.org" target="_blank">Unimatrix-one.org</a>';

// download
$lang[ 'dl_zip' ] = 'DL zip';
$lang[ 'dl_gz' ] = 'DL tar.gz';
$lang[ 'dl_bz2' ] = 'DL tar.bz2';
$lang[ 'tool_zip' ] = 'Download the console packed as a .zip file';
$lang[ 'tool_gz' ] = 'Download the console packed as a .tar.gz file';
$lang[ 'tool_bz2' ] = 'Download the console packed as a .tar.bz2 file';
$lang[ 'Version' ] = 'v0.6.0';
$lang[ 'dl_title' ] = 'Download';
$lang[ 'dl_date' ] = 'ClB console version %s as of %s';
$lang[ 'dl_explanation' ] = 'Here you can install the wonderful Chlorine Boards Console with which you will, in a shell-like environment, be able to install ClB and all the modules you need for a working website. The console comes with a simple command set and great documentation you can check any time you need. The console is very secure due to the secure key that is unique for every board and is used to encrypt your login detail.';
$lang[ 'dl_instr' ] = 'Instructions';
$lang[ 'dl_instrtext' ] = 'Basically all you have to do is download the console and the key, put them together, and you\'re flying. But if you need more detailed instructions feel free to check out the <a href="%s"><b>Documentation</b></a>';
$lang[ 'dl_license' ] = 'License Agreement';
$lang[ 'dl_key' ] = 'Download admin key';
$lang[ 'dl_key_title' ] = 'Admin Key';
$lang[ 'dl_discl1' ] = '<b>Disclaimer #1:</b> The console has so far only been tested on linux systems and while it is possible it might not work on other platforms but all effort has been made to make such an eventuality as least likely as possible.';
$lang[ 'dl_discl2' ] = '<b>Disclaimer #2:</b> The admin key is in no way a registration key or something of a similar sort. It is used only for security reasons and nothing else. You may do anything you like with it, for al we care you may even distribute it freely, though that would be unadvised because it would be a big security risk.';

// list
$lang[ 'title_funct' ] = 'Functionality';
$lang[ 'title_cosm' ] = 'Cosmetics';
$lang[ 'title_basic' ] = 'Core';
$lang[ 'title_lang' ] = 'Language';
$lang[ 'title_add' ] = 'Add module';
$lang[ 'desc_funct' ] = 'Functionality modules are modules that add features to your website. They are the working mule of the package as they provide things like login or a blog.';
$lang[ 'desc_cosm' ] = 'Cosmetics modules deal with the look and feel of your website. They are comprised mostly of different templates and module specific template packs.';
$lang[ 'desc_basic' ] = 'The Chlorine Boards core package is in here. Most of these modules are automatically pulled in on the initial install and are separated so that maintenance is simpler.';
$lang[ 'desc_lang' ] = 'Language modules are modules that provide support for different languages to your website or add language specifics for a certain module.';

$lang[ 'Callsign' ] = 'Callsign';
$lang[ 'Author' ] = 'Author';
$lang[ 'Description' ] = 'Description';
$lang[ 'Time' ] = 'Upload date';
$lang[ 'Version' ] = 'Available versions';
$lang[ 'Spawn' ] = 'Spawn installs';
$lang[ 'Request' ] = 'Request installs';
$lang[ 'Fetch' ] = 'Fetch restriction';
$lang[ 'Useopts' ] = 'Use flags';
$lang[ 'Summons' ] = 'Summoned times';

$lang[ 'None' ] = 'None';
$lang[ 'Nope' ] = 'No modules';
$lang[ 'Nope_txt' ] = 'Sorry, there are no modules to display in this section.';

$lang[ 'Map' ] = 'File map';
$lang[ 'No_map' ] = 'This module doesn\'t have a filemap';
$lang[ 'Sql' ] = 'Sql alterations';
$lang[ 'No_sql' ] = 'This module doesn\'t perfrom any SQL';
$lang[ 'Config' ] = 'Configuration';
$lang[ 'No_config' ] = 'No config changes are performed by this module';
$lang[ 'Source' ] = 'View source';
$lang[ 'Methods' ] = 'Methods';
$lang[ 'Announce' ] = 'Announce events';
$lang[ 'Accept' ] = 'Accept events';

// add
$lang[ 'add_welcome' ] = 'The ClB project is thankful for your contribution and endeavour to bring more useful features to it.<br />Uploading your module makes it instantly installable through the ClB console so make sure that whatever you upload is at least stable enough to be installed.';
$lang[ 'add_author' ] = 'Author';
$lang[ 'tools_author' ] = 'Insert a short nickname by which users will be able to tell which other modules you have made.';
$lang[ 'add_name' ] = 'Name';
$lang[ 'tools_name' ] = 'Insert a name for your module.';
$lang[ 'add_version' ] = 'Version';
$lang[ 'tools_version' ] = 'Insert a version number of the distribution you are uploading. Use the standard x.y.z versioning.';
$lang[ 'add_description' ] = 'Description';
$lang[ 'tools_description' ] = 'Insert a short description of your module.';
$lang[ 'add_callsign' ] = 'Callsign';
$lang[ 'tools_callsign' ] = 'Insert a unique callsign, it is used to install your module.';
$lang[ 'add_spawn' ] = 'Spawn';
$lang[ 'tools_spawn' ] = 'Insert a list of other modules that get installed after yours. The list is constructed from callsigns with ; in between. You may use the drop-down box in the right.';
$lang[ 'add_request' ] = 'Dependancy';
$lang[ 'tools_request' ] = 'Insert a list of modules yours needs to be isntallable. The list is constructed from callsigns with ; in between. You may use the drop-down box in the right.';
$lang[ 'add_fetch' ] = 'Fetch restriction';
$lang[ 'tools_fetch' ] = 'Disable fetching from this server. You need to provide an alternative download location for the .zip file.';
$lang[ 'add_useopts' ] = 'Use opts.';
$lang[ 'tools_useopts' ] = 'Insert a list of modules that are installed after checking the USE flags. The list is constructed from callsigns with ; in between. You may use the drop-down box in the right.';
$lang[ 'add_files' ] = 'Files.zip';
$lang[ 'tools_files' ] = 'Upload your files.zip package here.';
$lang[ 'add_map' ] = 'Filemap.txt';
$lang[ 'tools_map' ] = 'Upload your filemap.txt file here.';
$lang[ 'add_sql' ] = 'Sql.sql';
$lang[ 'tools_sql' ] = 'Upload your sql.sql file here.';
$lang[ 'add_config' ] = 'Config.txt';
$lang[ 'tools_config' ] = 'Upload your config.txt file here.';
$lang[ 'add_announce' ] = 'Announced events';
$lang[ 'tools_announce' ] = 'Input a list of events this module announces. Separate them by ;';
$lang[ 'add_accept' ] = 'Accepted events';
$lang[ 'tools_accept' ] = 'Input a list of events this module accepts. Separate them by ;';
$lang[ 'add_methods' ] = 'Module methods';
$lang[ 'tools_methods' ] = 'Input a list of methods within this module in the format of: <br/><b>event->executed_function;</b>';

$lang[ 'add_errform' ] = 'Wrongly submitted form.';
$lang[ 'add_hackerr' ] = 'The file "%s" seems to be something other than an upload which is very wrong.';
$lang[ 'add_errsize1' ] = 'The file "%s" exceeds the maximum file size set in php.ini.';
$lang[ 'add_errsize2' ] = 'The file "%s" exceeds the maximum file size set in the HTML form.';
$lang[ 'add_errpart' ] = 'The file "%s" was only partialy uploaded.';
$lang[ 'add_errtmp' ] = 'The temporary folder for uploaded files seems to be missing.';
$lang[ 'add_errwrt' ] = 'The file "%s" could not be written to disk.';
$lang[ 'add_errnum' ] = 'There were no files uploaded, you need at least one.';
$lang[ 'add_errmime' ] = 'File "%s" is of mime type %s but should be of %s';
$lang[ 'add_errinfo' ] = 'The critical field %s was left empty';
$lang[ 'add_errcall' ] = 'Callsign %s already exists in the database. Either provide an update or choose a different callsign.';
$lang[ 'add_errperm' ] = 'Directory "%s" is not writable and could not be chmodded.';
$lang[ 'add_errup' ] = 'There was an error uploading the file %s';

$lang[ 'add_OK' ] = 'Module %s v %s was succesfuly uploaded.';

?>