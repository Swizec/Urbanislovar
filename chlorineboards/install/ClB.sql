CREATE TABLE `ClB_config` (
  `config_name` varchar(255) NOT NULL default '',
  `config_value` text NOT NULL default '',
  `config_method` varchar(6) NOT NULL default 'normal'
);

INSERT INTO `ClB_config` VALUES ('SESSIONS_TABLE', '$prefixsessions', 'define');
INSERT INTO `ClB_config` VALUES ('session_expire', '3600', 'normal');
INSERT INTO `ClB_config` VALUES ('session_floodlimit', '10', 'normal');
INSERT INTO `ClB_config` VALUES ('session_method', 'cookie', 'normal');
INSERT INTO `ClB_config` VALUES ('session_path', '$path', 'normal');
INSERT INTO `ClB_config` VALUES ('session_domain', '$domain', 'normal');
INSERT INTO `ClB_config` VALUES ('session_secure', '$secure', 'normal');
INSERT INTO `ClB_config` VALUES ('script_path', '$path2', 'normal');
INSERT INTO `ClB_config` VALUES ('MODULES_TABLE', '$prefixmodules', 'define');
INSERT INTO `ClB_config` VALUES ('SES_METHOD_COOKIE', 'cookie', 'define');
INSERT INTO `ClB_config` VALUES ('SES_METHOD_COOKIELESS', 'cookieless', 'define');
INSERT INTO `ClB_config` VALUES ('MOD_FETCH_PARENT', 'byparent', 'define');
INSERT INTO `ClB_config` VALUES ('MOD_FETCH_NAME', 'byname', 'define');
INSERT INTO `ClB_config` VALUES ('MOD_FETCH_ID', 'byid', 'define');
INSERT INTO `ClB_config` VALUES ('MOD_FETCH_MODE', 'bymode', 'define');
INSERT INTO `ClB_config` VALUES ('key_prefix', '$keyprefix', 'normal');
INSERT INTO `ClB_config` VALUES ('ERROR_GUI', 'showpretty', 'define');
INSERT INTO `ClB_config` VALUES ('ERROR_RAW', 'showraw', 'define');
INSERT INTO `ClB_config` VALUES ('GENERAL_ERROR', 'general_error', 'define');
INSERT INTO `ClB_config` VALUES ('CRITICAL_ERROR', 'critical_error', 'define');
INSERT INTO `ClB_config` VALUES ('ADD_SLASHES', 'addslashes', 'define');
INSERT INTO `ClB_config` VALUES ('REM_SLASHES', 'remslashes', 'define');
INSERT INTO `ClB_config` VALUES ('def_template', 'BlueSilver', 'normal');
INSERT INTO `ClB_config` VALUES ('tplEx', '.tpl', 'define');
INSERT INTO `ClB_config` VALUES ('site_charset', 'utf-8', 'normal');
INSERT INTO `ClB_config` VALUES ('MODE_URL', 'mode', 'define');
INSERT INTO `ClB_config` VALUES ('def_lang', 'en', 'normal');
INSERT INTO `ClB_config` VALUES ('COUNTED', 'counted', 'define');
INSERT INTO `ClB_config` VALUES ('NONCOUNTED', 'noncounted', 'define');
INSERT INTO `ClB_config` VALUES ('Cl_version', '0.6.6', 'normal');
INSERT INTO `ClB_config` VALUES ('ESSENTIAL', 'essential', 'define');
INSERT INTO `ClB_config` VALUES ('NOT_ESSENTIAL', 'not_essential', 'define');
INSERT INTO `ClB_config` VALUES ('NEW', 'new', 'define');
INSERT INTO `ClB_config` VALUES ('LAST', 'last', 'define');
INSERT INTO `ClB_config` VALUES ('sitename', '$sitename', 'normal');
INSERT INTO `ClB_config` VALUES ('GUEST', '-99', 'define');
INSERT INTO `ClB_config` VALUES ('INACTIVE', '-98', 'define');
INSERT INTO `ClB_config` VALUES ('ADMIN', '0', 'define');
INSERT INTO `ClB_config` VALUES ('SUPER_MOD', '1', 'define');
INSERT INTO `ClB_config` VALUES ('MOD', '2', 'define');
INSERT INTO `ClB_config` VALUES ('USER', '3', 'define');
INSERT INTO `ClB_config` VALUES ('USERS_TABLE', '$prefixusers', 'define');
INSERT INTO `ClB_config` VALUES ('SUBMODE_URL', 'submode', 'define');
INSERT INTO `ClB_config` VALUES ('main_module', '', 'normal');
INSERT INTO `ClB_config` VALUES ('MODULES_HASH_TABLE', '$prefixhash', 'define');
INSERT INTO `ClB_config` VALUES ('cfgEx', '.cfg', 'define');
INSERT INTO `ClB_config` VALUES ('admin_email', '$adminmail', 'normal');
INSERT INTO `ClB_config` VALUES ('meta_description', '', 'normal');
INSERT INTO `ClB_config` VALUES ('meta_keywords', '', 'normal');
INSERT INTO `ClB_config` VALUES ('nosid4bots', '1', 'normal');
INSERT INTO `ClB_config` VALUES ('pagecache_time', '300', 'normal');
INSERT INTO `ClB_config` VALUES ('pagecache_on', '1', 'normal');
INSERT INTO `ClB_config` VALUES ('CONFIG_TABLE', '$prefixconfig', 'define');
INSERT INTO `ClB_config` VALUES ('template_compiler', '1', 'normal');
INSERT INTO `ClB_config` VALUES ('use_SEO', '1', 'normal');


CREATE TABLE `ClB_hash` (
  `callsign` varchar(50) NOT NULL default '',
  `announce_methods` text NOT NULL,
  `accept_methods` text NOT NULL,
  `mod_methods` text NOT NULL
);

CREATE TABLE `ClB_modules` (
  `mod_id` int(11) NOT NULL auto_increment,
  `mod_name` varchar(100) NOT NULL default '',
  `mod_parent` varchar(100) NOT NULL default '',
  `mod_methods` text NOT NULL,
  KEY `mod_id` (`mod_id`)
) ;
INSERT INTO `ClB_modules` (`mod_id`, `mod_name`, `mod_parent`, `mod_methods`) VALUES (1, 'index', '', ''),
(2, 'ACP', '0;', 'ACP->show_acp;'),
(3, 'filebrowser', '0;', 'filebrowser->display;'),
(4, 'UCP', '0;', 'UCP->show_ucp;');

CREATE TABLE `ClB_sessions` (
  `id` varchar(32) NOT NULL default '',
  `time_start` int(11) NOT NULL default '0',
  `time_lastactive` int(11) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  `user_id` int(11) NOT NULL default '0',
  `autolog` tinyint(1) NOT NULL default '0'
) ENGINE=HEAP;

CREATE TABLE `ClB_users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `username` varchar(40) NOT NULL default '',
  `password` varchar(30) NOT NULL default '',
  `user_email` varchar(255) NOT NULL default '',
  `user_level` int(5) NOT NULL default '-98',
  `user_timeformat` varchar(15) NOT NULL default 'D M d, Y H:i',
  `user_lang` VARCHAR( 5 ) NOT NULL ,
  `user_skin` VARCHAR( 50 ) NOT NULL ,
  PRIMARY KEY  (`user_id`)
)  ;

INSERT INTO `ClB_users` (username, password, user_email, user_level, user_timeformat) VALUES ( 'Guest', 'guest', '', -99, 'D M d, Y H:i');
INSERT INTO `ClB_users` (username, password, user_email, user_level, user_timeformat) VALUES ('$adminname', '$adminpass', '$adminmail', 0, 'D M d, Y H:i');
