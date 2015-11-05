#!/usr/local/bin/php-cgi -f
<?php
require_once("config.inc");
require_once("functions.inc");
require_once("install.inc");
require_once("util.inc");
if ( !is_dir ( '/usr/local/www/ext')) { mkdir("/usr/local/www/ext", 0755);  }
if ( !is_dir ( '/usr/local/www/ext/minidlna')) { mkdir("/usr/local/www/ext/minidlna", 0755);  }
copy($config['minidlna']['homefolder']."/ext/minidlna/menu.inc", "/usr/local/www/ext/minidlna/menu.inc");
if ( !is_link ( "/usr/local/www/ext/minidlna/function.php")) { symlink ( $config['minidlna']['homefolder']."/ext/minidlna/function.php" , "/usr/local/www/ext/minidlna/function.php" );}
if ( !is_link ( "/usr/local/www/extensions_minidlna.php")) { symlink ( $config['minidlna']['homefolder']."/ext/minidlna/extensions_minidlna.php" , "/usr/local/www/extensions_minidlna.php" ); }
if ( !is_link ( "/usr/local/www/extensions_minidlna_log.php")) {  symlink ( $config['minidlna']['homefolder']."/ext/minidlna/extensions_minidlna_log.php" , "/usr/local/www/extensions_minidlna_log.php" ); }
if ( !is_link ( "/usr/local/www/extensions_minidlna_config.php")) { symlink ( $config['minidlna']['homefolder']."/ext/minidlna/extensions_minidlna_config.php" , "/usr/local/www/extensions_minidlna_config.php" );}
if ( !is_link ( "/usr/local/www/system_cron_edit1.php")) {  symlink ( $config['minidlna']['homefolder']."/ext/minidlna/system_cron_edit1.php" , "/usr/local/www/system_cron_edit1.php" );}
if ( !is_link ( "/usr/local/www/system_cron1.php")) { symlink ( $config['minidlna']['homefolder']."/ext/minidlna/system_cron1.php" , "/usr/local/www/system_cron1.php" );}
if ( !is_link ( "/usr/local/www/status_scan.png")) { symlink ( $config['minidlna']['homefolder']."/ext/minidlna/status_scan.png" , "/usr/local/www/status_scan.png" );}

if ( !is_link ( "/etc/rc.d/minidlna") || !is_file ( "/etc/rc.d/minidlna")) { symlink ( $config['minidlna']['homefolder']."/ext/minidlna.sh" , "/etc/rc.d/minidlna" );}
if (isset($config['minidlna']['enable'])) {
rc_update_rcconf("minidlna", "enable"); 
rc_start_service("minidlna");
}

?>