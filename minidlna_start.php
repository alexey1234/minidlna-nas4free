#!/usr/local/bin/php-cgi -f
<?php
require_once("config.inc");
require_once("functions.inc");
require_once("install.inc");
require_once("util.inc");
if ( !is_dir ( '/usr/local/www/ext')) { $cmd = "mkdir -p /usr/local/www/ext"; exec ($cmd);  }
	$cmd = "ln -s ".$config['minidlna']['homefolder']."/ext/minidlna /usr/local/www/ext/";
	exec ($cmd); 
if ( !is_link ( "/usr/local/www/extensions_minidlna.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/extensions_minidlna.php /usr/local/www/extensions_minidlna.php"; exec ($cmd); }
if ( !is_link ( "/usr/local/www/extensions_minidlna_log.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/extensions_minidlna_log.php /usr/local/www/extensions_minidlna_log.php"; exec ($cmd); }
if ( !is_link ( "/usr/local/www/extensions_minidlna_config.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/extensions_minidlna_config.php /usr/local/www/extensions_minidlna_config.php"; exec ($cmd); }

if ( !is_link ( "/usr/local/www/system_cron_edit1.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/system_cron_edit1.php /usr/local/www/system_cron_edit1.php"; exec ($cmd); }
if ( !is_link ( "/usr/local/www/system_cron1.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/system_cron1.php /usr/local/www/system_cron1.php"; exec ($cmd); }

if ( !is_link ( "/etc/rc.d/minidlna") || !is_file ( "/etc/rc.d/minidlna")) { $cmd ="ln -s ".$config['minidlna']['homefolder']."/ext/minidlna.sh /etc/rc.d/minidlna"; exec ($cmd); }
if (isset($config['minidlna']['enable'])) {
rc_update_rcconf("minidlna", "enable"); 
rc_start_service("minidlna");
}

?>