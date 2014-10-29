#!/usr/local/bin/php-cgi -f
<?php
// minidlna extension for NAS4Free
// created by Kruglov Alexey

require_once("config.inc");
require_once("functions.inc");
require_once("install.inc");
require_once("util.inc");
require_once("tui.inc");
$arch = $g['arch'];
$platform = $g['platform'];
if (($arch != "i386" && $arch != "amd64") && ($arch != "x86" && $arch != "x64")) { echo "unsupported architecture\n"; exit(1);  }
if ($platform != "embedded" && $platform != "full" && $platform != "livecd" && $platform != "liveusb") { echo "unsupported platform\n";  exit(1); }
$upgrade = 0;
// Display installation option

$amenuitem['1']['tag'] = "1";
$amenuitem['1']['item'] = "Install_minidlna";
$amenuitem['2']['tag'] = "2";
$amenuitem['2']['item'] = "Uninstall_minidlna";
$amenuitem['3']['tag'] = "3";
$amenuitem['3']['item'] = "Exit from here";

$result = tui_display_menu("Choose  option", "Select Install or uninstal or Upgrade", 70, 12, 8, $amenuitem, $installopt);
if (0 != $result)  exit(0);
if ($installopt == 3 ) { exit;}

// Remove minidlna section
		if ($installopt == 2 ) { 
			if (is_file("/var/run/minidlna.pid")) { exec ($config['minidlna']['homefolder']."bin/minidlnad stop");}
	
			if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
				for ($i = 0; $i < count($config['rc']['postinit']['cmd']);) {
					if (preg_match('/minidlna/', $config['rc']['postinit']['cmd'][$i])) {	unset($config['rc']['postinit']['cmd'][$i]);} else{}
					++$i;
				}
			}
			if ( is_array($config['rc']['shutdown'] ) && is_array( $config['rc']['shutdown']['cmd'] ) ) {
				for ($i = 0; $i < count($config['rc']['shutdown']['cmd']); ) {
					if (preg_match('/minidlna/', $config['rc']['shutdown']['cmd'][$i])) {	unset($config['rc']['shutdown']['cmd'][$i]); } else {}
					++$i;	
				}
			}
			if ( is_array($config['cron'] ) && is_array( $config['cron']['job'] )) {
				$index = array_search_ex("minidlna", $config['cron']['job'], "desc");
				if (false !== $index) { unset($config['cron']['job'][$index]);	}
			}
	
	//unlink created bin links
		if ( is_link ( "/usr/local/sbin/minidlna") ) { unlink("/usr/local/sbin/minidlna"); }
		if ( is_link ( "/usr/local/lib/libexif.so.12") ) { unlink("/usr/local/lib/libexif.so.12"); }
		if ( is_link ( "/usr/local/lib/libFLAC.so.10") ) { unlink("/usr/local/lib/libFLAC.so.10"); }
		if ( is_link ( "/usr/local/lib/libavcodec.so.1") ) { unlink("/usr/local/lib/libavcodec.so.1"); }
		if ( is_link ( "/usr/local/lib/libavutil.so.1") ) { unlink("/usr/local/lib/libavutil.so.1"); }
		if ( is_link ( "/usr/local/lib/libavformat.so.1") ) { unlink("/usr/local/lib/libavformat.so.1"); }
		if ( is_link ( "/usr/local/lib/liborc-0.4.so.0") ) { unlink("/usr/local/lib/liborc-0.4.so.0"); }
		if ( is_link ( "/usr/local/lib/libschroedinger-1.0.so.11") ) { unlink("/usr/local/lib/libschroedinger-1.0.so.11"); }
		if ( is_link ( "/usr/local/lib/libx264.so.125") ) { unlink("/usr/local/lib/libx264.so.125"); }
		if ( is_link ( "/usr/local/lib/libsqlite3.so.0") ) { unlink("/usr/local/lib/libsqlite3.so.0"); }
		if ( is_link ( "/etc/rc.d/minidlna") ) { 	unlink("/etc/rc.d/minidlna"); }
	
	//remowe web pages
		if (is_dir ("/usr/local/www/ext/minidlna")) {
			foreach ( glob( "{$config['minidlna']['homefolder']}ext*.php" ) as $file ) {
				  $file = str_replace("{$config['minidlna']['homefolder']}ext", "/usr/local/www", $file);
				  if ( is_link( $file ) ) { unlink( $file ); } else {}	
			}
		mwexec ("rm -rf /usr/local/www/ext/minidlna");
		if ( is_link( "/usr/local/www/clear.png" ) ) { unlink(  "/usr/local/www/clear.png" );}
		if ( is_link( "/usr/local/www/status_scan.png" ) ) { unlink(  "/usr/local/www/status_scan.png" );}
		}
	
	// restore backuped files 
		if (is_dir($config['minidlna']['homefolder']."backup000")) /*not need restore for first version */ {
			copy ($config['minidlna']['homefolder']."backup000/services_upnp.php", "/usr/local/www/services_upnp.php" );
			copy ($config['minidlna']['homefolder']."backup000/system_cron.php", "/usr/local/www/system_cron.php" );
			copy ( $config['minidlna']['homefolder']."backup000/system_cron_edit.php", "/usr/local/www/system_cron_edit.php");
		}

	//remove minidlna section from config.xml
		$cmd = "chmod -R 777 ".$config['minidlna']['homefolder'];
		exec ($cmd);
		if ( is_array($config['minidlna'] ) ) { unset( $config['minidlna'] ); write_config();}
	
		echo "minidlna entries removed. Remove files manually\n"; 
	
	}
	


///  Install minidlna on NAS4Free
	if ($installopt == 1 ) {
		if (!is_array ($config['minidlna'] )) /*do install*/goto install;
	// upgrade
		$upgrade = 1;
		if (is_file("/var/run/minidlna.pid")) { exec ($config['minidlna']['homefolder']."bin/minidlnad stop");}
		$config['upnp']['name'] = $config['minidlna']['name'];
		$config['upnp']['port'] = $config['minidlna']['port'];
		$config['upnp']['if'] = $config['minidlna']['if'];
		$config['upnp']['notify_int'] =  $config['minidlna']['notify_int'];
		$config['upnp']['strict'] =  isset($config['minidlna']['strict']) ? true : false;
		$config['upnp']['tivo'] =  isset($config['minidlna']['notify_int']) ? true : false;
		$config['upnp']['loglevel'] =  $config['minidlna']['loglevel'];
		$config['upnp']['home'] =  $config['minidlna']['homefolder']."db";
		$config['upnp']['content'] =  $config['minidlna']['content'];
		$config['upnp']['server_t'] =  "off";
		unset($config['upnp']['enable']);
      // kill links and files
		if ( is_link ( "/usr/local/sbin/minidlna") ) { unlink("/usr/local/sbin/minidlna"); }
		if ( is_link ( "/usr/local/lib/libexif.so.12") ) { unlink("/usr/local/lib/libexif.so.12"); }
		if ( is_link ( "/usr/local/lib/libFLAC.so.10") ) { unlink("/usr/local/lib/libFLAC.so.10"); }
		if ( is_link ( "/usr/local/lib/libavcodec.so.1") ) { unlink("/usr/local/lib/libavcodec.so.1"); }
		if ( is_link ( "/usr/local/lib/libavutil.so.1") ) { unlink("/usr/local/lib/libavutil.so.1"); }
		if ( is_link ( "/usr/local/lib/libavformat.so.1") ) { unlink("/usr/local/lib/libavformat.so.1"); }
		if ( is_link ( "/usr/local/lib/liborc-0.4.so.0") ) { unlink("/usr/local/lib/liborc-0.4.so.0"); }
		if ( is_link ( "/usr/local/lib/libschroedinger-1.0.so.11") ) { unlink("/usr/local/lib/libschroedinger-1.0.so.11"); }
		if ( is_link ( "/usr/local/lib/libx264.so.125") ) { unlink("/usr/local/lib/libx264.so.125"); }
		if ( is_link ( "/usr/local/lib/libsqlite3.so.0") ) { unlink("/usr/local/lib/libsqlite3.so.0"); }
		if ( is_link ( "/etc/rc.d/minidlnad") ) { 	unlink("/etc/rc.d/minidlna"); }
	
	// kill webpages, but not kill startup, shutdown and cron
		if (is_dir ("/usr/local/www/ext/minidlna")) {
			foreach ( glob( "{$config['minidlna']['homefolder']}ext/*.php" ) as $file ) {
				$file = str_replace("{$config['minidlna']['homefolder']}ext", "/usr/local/www", $file);
				if ( is_link( $file ) ) { unlink( $file ); } else {}	
			}
		mwexec ("rm -rf /usr/local/www/ext/minidlna");
		if ( is_link( "/usr/local/www/clear.png" ) ) { unlink(  "/usr/local/www/clear.png" );}
		if ( is_link( "/usr/local/www/status_scan.png" ) ) { unlink(  "/usr/local/www/status_scan.png" );}
		}
	//if (strlen($config['minidlna']['homefolder']) > 10 && $config['minidlna']['homefolder'][strlen($config['minidlna']['homefolder'])-1] == "/") {$config['minidlna']['homefolder'] =  substr($config['minidlna']['homefolder'], 0, strlen($config['minidlna']['homefolder'])-1);} 
     
		$cmd = "rm -fr ".$config['minidlna']['homefolder']."bin";
		exec ($cmd);
		$cmd = "rm -fr ".$config['minidlna']['homefolder']."ext";
		exec ($cmd);
		$cmd = "rm -fr ".$config['minidlna']['homefolder']."web";
		exec ($cmd);
      
		if ( is_array($config['minidlna'] ) ) { unset( $config['minidlna'] );}
		write_config();
	// end upgrade cleanup	
	// install new files 
		install:
		$cwdir = getcwd();
		$config['minidlna'] = array();
		$path1 = pathinfo($cwdir);
			//$config['minidlna']['homefolder'] =  $path1['dirname']."/minidlna/";
		$cwdir = $path1['dirname']."/minidlna/";
		file_put_contents('/tmp/minidlna.install', $cwdir);
			//write_config();
		if (0== $install ) {
			$i = 0;
				if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
					for ($i; $i < count($config['rc']['postinit']['cmd']);) {
						if (preg_match('/minidlna/', $config['rc']['postinit']['cmd'][$i])) 	break;
						++$i;	
						} 	
					}
				$config['rc']['postinit']['cmd'][$i] = $cwdir."minidlna_start.php";
			$i =0;
				if ( is_array($config['rc']['shutdown'] ) && is_array( $config['rc']['shutdown']['cmd'] ) ) {
					for ($i; $i < count($config['rc']['shutdown']['cmd']); ) {
						if (preg_match('/minidlnad/', $config['rc']['shutdown']['cmd'][$i])) 	break;
						++$i;
						} 
					}
				$config['rc']['shutdown']['cmd'][$i] = $cwdir."bin/minidlnad stop";
			write_config();
		}
	// create folders and copy files
		if (!is_dir ("bin")) mkdir ("bin",0755);
		if (!is_dir ("ext")) mkdir ("ext",0755);
		if (!is_dir ("web")) mkdir ("web",0755);
		if (!is_dir ("db")) mkdir ("db",0777);
		chmod ("db",0777);
		if (($arch == "i386") || ($arch == "x86")) { exec( "cp tmp/bin86/* bin/" ); }
		elseif (($arch == "amd64") || ($arch == "x64")) { exec( "cp tmp/bin64/* bin/" ); }
		else {echo "not supported  \n"; exit;}
		exec( "cp tmp/ext/* ext/" );
		exec( "cp -r tmp/web/* web/" );
	// create links	
		if ( !is_link ( "/usr/local/sbin/minidlna") || !is_file ("/usr/local/sbin/minidlna")) { 	$cmd = "ln -s ".$cwdir."bin/minidlna /usr/local/sbin/minidlna"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libexif.so.12") || !is_file ( "/usr/local/lib/libexif.so.12")) { $cmd = "ln -s ".$cwdir."bin/libexif.so.12 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libFLAC.so.10") || !is_file ( "/usr/local/lib/libFLAC.so.10")) { $cmd = "ln -s ".$cwdir."bin/libFLAC.so.10 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libavcodec.so.1") || !is_file ( "/usr/local/lib/libavcodec.so.1")) { $cmd = "ln -s ".$cwdir."bin/libavcodec.so.1 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libavutil.so.1") || !is_file ( "/usr/local/lib/libavutil.so.1")) { $cmd = "ln -s ".$cwdir."bin/libavutil.so.1 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libavformat.so.1") || !is_file ( "/usr/local/lib/libavformat.so.1")) { $cmd = "ln -s ".$cwdir."bin/libavformat.so.1 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/liborc-0.4.so.0") || !is_file ( "/usr/local/lib/liborc-0.4.so.0")) { $cmd = "ln -s ".$cwdir."bin/liborc-0.4.so.0 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libschroedinger-1.0.so.11") || !is_file ( "/usr/local/lib/libschroedinger-1.0.so.11")) { $cmd = "ln -s ".$cwdir."bin/libschroedinger-1.0.so.11 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libx264.so.125") || !is_file ( "/usr/local/lib/libx264.so.125")) { $cmd = "ln -s ".$cwdir."bin/libx264.so.125 /usr/local/lib/"; exec ($cmd); }
		if ( !is_link ( "/usr/local/lib/libsqlite3.so.0") || !is_file ( "/usr/local/lib/libsqlite3.so.0"))  { $cmd = "ln -s ".$cwdir."bin/libsqlite3.so.0 /usr/local/lib/";  exec ($cmd); }
		if ( !is_link ( "/etc/rc.d/minidlnad") || !is_file ("/etc/rc.d/minidlnad")) { 	$cmd = "ln -s ".$cwdir."bin/minidlnad /etc/rc.d/minidlna"; exec ($cmd); }

		$cmd = "rm -rf ".$cwdir."tmp";
		exec ( $cmd ); // remove sources

	 // make backup  files
		if (!file_exists($cwdir.'backup000')) { 
			if(false == mkdir($cwdir.'backup000', 0755, false)) { 
				echo "Wow, I cannot create folder for backup!!" ; exit(1); 
			} 
		} else {
			if (is_dir($cwdir.'backup000') ) {echo "Where from backup-folder?";exit(1);} }
		rename ("/usr/local/www/services_upnp.php", $cwdir."backup000/services_upnp.php");
		rename ("/usr/local/www/system_cron.php", $cwdir."backup000/system_cron.php");
		rename ("/usr/local/www/system_cron_edit.php", $cwdir."backup000/system_cron_edit.php");
	// make webinterface
		mwexec ("mkdir -p /usr/local/www/ext/minidlna");
		copy ($cwdir."ext/menu.inc", "/usr/local/www/ext/minidlna/menu.inc");
		copy ($cwdir."ext/function.php", "/usr/local/www/ext/minidlna/function.php");
		if ( !is_link ( "/usr/local/www/extensions_minidlna.php")) { $cmd = "ln -s ".$cwdir."ext/extensions_minidlna.php /usr/local/www/extensions_minidlna.php"; exec ($cmd); }
		if ( !is_link ( "/usr/local/www/clear.png")) { $cmd = "ln -s ".$cwdir."ext/clear.png /usr/local/www/clear.png"; exec ($cmd); }
		if ( !is_link( "/usr/local/www/status_scan.png" ) ) { $cmd = "ln -s ".$cwdir."ext/status_scan.png /usr/local/www/status_scan.png"; exec ($cmd); }
		if ( !is_link ( "/usr/local/www/extensions_minidlna_log.php")) { $cmd = "ln -s ".$cwdir."ext/extensions_minidlna_log.php /usr/local/www/extensions_minidlna_log.php"; exec ($cmd); }
		if ( !is_link ( "/usr/local/www/services_upnp.php")) { $cmd = "ln -s ".$cwdir."ext/services_upnp.php /usr/local/www/services_upnp.php"; exec ($cmd); }
		if ( !is_link ( "/usr/local/www/system_cron.php")) { $cmd = "ln -s ".$cwdir."ext/system_cron.php /usr/local/www/system_cron.php"; exec ($cmd); }
		if ( !is_link ( "/usr/local/www/system_cron_edit.php")) { $cmd = "ln -s ".$cwdir."ext/system_cron_edit.php /usr/local/www/system_cron_edit.php"; exec ($cmd); }
	// webview attach
		$mdlnawconfinc = "<?php \$path_to_db = \"{$cwdir}db/files.db\"; \$debug_sqls = false; \$download   = true; ?>" ;
		file_put_contents ( "{$cwdir}web/minidlna/inc/config.inc.php", $mdlnawconfinc );
		$dlcapi = file($cwdir."web/minidlna/script/dlcapi.class.php");
		$dlcapi[65] = "const dlc_cache_keys_filename = '{$cwdir}web/minidlna/jd/dlcapicache.txt';\n";
		$str = '';
		foreach ( $dlcapi as $lines ) { $str .= $lines; }
		file_put_contents($cwdir.'web/minidlna/script/dlcapi.class.php', $str);
		exec ("chmod 777 ".$cwdir."web/minidlna/jd/tmp");
	//create .user.ini file for webviewer
		$phpinimain = file("/usr/local/etc/php.ini");
		$phpinimain[10]="include_path = \".:/etc/inc:/usr/local/www:".$cwdir."web/minidlna\"\n";
		$str = '';
		foreach ( $phpinimain as $lines ) { $str .= $lines; }
		file_put_contents($cwdir.'web/minidlna/.user.ini', $str);
		file_put_contents("/tmp/minidlna.install",  $cwdir );

		echo "Please go to extension page and push \"Save\" button for complete \n";
		exit;
}

?>