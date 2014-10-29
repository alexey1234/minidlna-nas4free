#!/usr/local/bin/php-cgi -f
<?php
require_once("config.inc");
require_once("functions.inc");
require_once("install.inc");
require_once("util.inc");
if ( !isset($config['upnp']['content']) || !isset($config['upnp']['name'])) { echo "Minidlna not configured, please configure it over Extensions|minidlna  tab \n"; exit;}
if ( !is_dir ( '/usr/local/www/ext/minidlna')) { 
	$cmd = "mkdir -p /usr/local/www/ext/minidlna";
	exec ($cmd); 
	$cmd = "cp ".$config['minidlna']['homefolder']."ext/menu.inc /usr/local/www/ext/minidlna/menu.inc";
	exec ($cmd); 
	$cmd = "cp ".$config['minidlna']['homefolder']."ext/function.php /usr/local/www/ext/minidlna/function.php";
	exec ($cmd);
	$cmd = "cp ".$config['minidlna']['homefolder']."ext/clear.png /usr/local/www/clear.png";
	exec ($cmd);
	}
if ( !is_link ( "/usr/local/www/extensions_minidlna.php")) { $cmd = "ln -s {$config['minidlna']['homefolder']}ext/extensions_minidlna.php /usr/local/www/extensions_minidlna.php"; exec ($cmd); }
if ( !is_link ( "/usr/local/www/extensions_minidlna_log.php")) { $cmd = "ln -s {$config['minidlna']['homefolder']}ext/extensions_minidlna_log.php /usr/local/www/extensions_minidlna_log.php"; exec ($cmd); }
unlink ("/usr/local/www/services_upnp.php");
$cmd = "ln -s {$config['minidlna']['homefolder']}ext/services_upnp.php /usr/local/www/services_upnp.php"; exec ($cmd);
unlink ("/usr/local/www/system_cron.php");
$cmd = "ln -s {$config['minidlna']['homefolder']}ext/system_cron.php /usr/local/www/system_cron.php"; exec ($cmd);
unlink ("/usr/local/www/system_cron_edit.php");
$cmd = "ln -s {$config['minidlna']['homefolder']}ext/system_cron_edit.php /usr/local/www/system_cron_edit.php"; exec ($cmd);

// this for reserve
if ( !is_link ( "/usr/local/www/extensions_minidlna.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/extensions_minidlna.php /usr/local/www/extensions_minidlna.php"; exec ($cmd); }
if ( !is_link ( "/usr/local/sbin/minidlna") || !is_file ("/usr/local/sbin/minidlna")) { 	$cmd = "ln -s ".$config['minidlna']['homefolder']."bin/minidlna /usr/local/sbin/minidlna"; exec ($cmd); }
if ( !is_link ( "/etc/rc.d/minidlnad") || !is_file ("/etc/rc.d/minidlnad")) { 	$cmd = "ln -s ".$config['minidlna']['homefolder']."bin/minidlnad /etc/rc.d/minidlna"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libexif.so.12") || !is_file ( "/usr/local/lib/libexif.so.12")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libexif.so.12 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libFLAC.so.10") || !is_file ( "/usr/local/lib/libFLAC.so.10")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libFLAC.so.10 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libavcodec.so.1") || !is_file ( "/usr/local/lib/libavcodec.so.1")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libavcodec.so.1 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libavutil.so.1") || !is_file ( "/usr/local/lib/libavutil.so.1")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libavutil.so.1 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libavformat.so.1") || !is_file ( "/usr/local/lib/libavformat.so.1")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libavformat.so.1 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/liborc-0.4.so.0") || !is_file ( "/usr/local/lib/liborc-0.4.so.0")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/liborc-0.4.so.0 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libschroedinger-1.0.so.11") || !is_file ( "/usr/local/lib/libschroedinger-1.0.so.11")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libschroedinger-1.0.so.11 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libx264.so.125") || !is_file ( "/usr/local/lib/libx264.so.125")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libx264.so.125 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libsqlite3.so.0") || !is_file ( "/usr/local/lib/libsqlite3.so.0")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libsqlite3.so.0 /usr/local/lib/libsqlite3.so.0"; exec ($cmd); }
// clear webcache
$cmd = "rm -f ".$config['minidlna']['homefolder']."web/minidlna/jd/tmp/*"; exec ($cmd);
if (isset($config['minidlna']['enable'])) {
rc_update_rcconf("minidlna", "enable"); 
rc_start_service("minidlna");
}
?>