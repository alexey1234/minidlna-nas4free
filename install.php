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

// Display installation option

$amenuitem['1']['tag'] = "1";
$amenuitem['1']['item'] = "install_minidlna";
$amenuitem['2']['tag'] = "2";
$amenuitem['2']['item'] = "uninstall_minidlna";

$result = tui_display_menu("Choose  option", "Select Install or uninstal", 60, 10, 6, $amenuitem, $installopt);
if (0 != $result)  exit(0);


// Remove minidlna section
if ($installopt == 2 ) { 
	exec ("killall minidlna");
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
		if (false !== $index) {
					unset($config['cron']['job'][$index]);
		}
	}
	if (is_dir ("/usr/local/www/ext/minidlna")) {
	foreach ( glob( "{$config['minidlna']['homefolder']}conf/ext/minidlna/*.php" ) as $file ) {
	$file = str_replace("{$config['minidlna']['homefolder']}conf/ext/minidlna", "/usr/local/www", $file);
	if ( is_link( $file ) ) { unlink( $file ); } else {}	}
	mwexec ("rm -rf /usr/local/www/ext/minidlna");
	}
	//unlink created bin links
	


//remove minidlna section from config.xml
	if ( is_array($config['minidlna'] ) ) { unset( $config['minidlna'] ); write_config();}
	echo "minidlna entries removed. Remove files manually"; }
echo "\n";


//  Install minidlna on NAS4Free
if ($installopt == 1 ) {
	$cwdir = getcwd();
		if ( !isset($config['minidlna']) || !is_array($config['minidlna'])) 
		{	$config['minidlna'] = array();
			$path1 = pathinfo($cwdir);
			$config['minidlna']['homefolder'] =  $path1['dirname']."/minidlna/";
			$cwdir = $config['minidlna']['homefolder'];
			write_config();
		}
		else { echo "Minidlna already installed"; echo "\n"; exit;}
	$i = 0;
		if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
		    for ($i; $i < count($config['rc']['postinit']['cmd']);) {
		    if (preg_match('/minidlnad/', $config['rc']['postinit']['cmd'][$i])) 	break;
		++$i;	} 	}
		  $config['rc']['postinit']['cmd'][$i] = $cwdir."minidlna_start.php";
	$i =0;
		if ( is_array($config['rc']['shutdown'] ) && is_array( $config['rc']['shutdown']['cmd'] ) ) {
		for ($i; $i < count($config['rc']['shutdown']['cmd']); ) {
		if (preg_match('/minidlnad/', $config['rc']['shutdown']['cmd'][$i])) 	break;
		++$i;} 	}
		  $config['rc']['shutdown']['cmd'][$i] = $cwdir."bin/minidlnad stop";
		  write_config();

if (($arch == "i386") || ($arch == "x86")) { exec( "cp tmp/bin86/* bin/" ); }
elseif (($arch == "amd64") || ($arch == "x64")) { exec( "cp tmp/bin64/* bin/" ); }
else echo "not supported  \n";
if ( !is_link ( "/usr/local/sbin/minidlna")) { 	$cmd = "ln -s ".$config['minidlna']['homefolder']."bin/minidlna /usr/local/sbin/minidlna"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libexif.so.12")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libexif.so.12 /usr/local/lib/"; exec ($cmd); }
if ( !is_link ( "/usr/local/lib/libFLAC.so.10")) { $cmd = "ln -s ".$config['minidlna']['homefolder']."bin/libFLAC.so.10 /usr/local/lib/"; exec ($cmd); }
$cmd = "rm -rf ".$config['minidlna']['homefolder']."tmp";
exec ( $cmd );

mwexec ("mkdir -p /usr/local/www/ext/minidlna");
mwexec ("cp {$config['minidlna']['homefolder']}ext/* /usr/local/www/ext/minidlna/");
if ( !is_link ( "/usr/local/www/extensions_minidlna.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/extensions_minidlna.php /usr/local/www/extensions_minidlna.php"; exec ($cmd); }
if ( !is_link ( "/usr/local/www/extensions_minidlna_rescan.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/extensions_minidlna_rescan.php /usr/local/www/extensions_minidlna_rescan.php"; exec ($cmd); }
if ( !is_link ( "/usr/local/www/extensions_minidlna_log.php")) { $cmd = "ln -s /usr/local/www/ext/minidlna/extensions_minidlna_log.php /usr/local/www/extensions_minidlna_log.php"; exec ($cmd); }
$mdlnawconfinc = "<?php \$path_to_db = \"{$config['minidlna']['homefolder']}db/files.db\"; \$debug_sqls = false; \$download   = true; ?>" ;
file_put_contents ( "{$config['minidlna']['homefolder']}web/minidlna/inc/config.inc.php", $mdlnawconfinc );
$dlcapi = file($config['minidlna']['homefolder']."web/minidlna/script/dlcapi.class.php");
$dlcapi[65] = "const dlc_cache_keys_filename = '{$config['minidlna']['homefolder']}web/minidlna/jd/dlcapicache.txt';\n";
$str = '';
foreach ( $dlcapi as $lines ) { $str .= $lines; }
file_put_contents($config['minidlna']['homefolder'].'web/minidlna/script/dlcapi.class.php', $str);

require_once("{$config['minidlna']['homefolder']}minidlna_start.php");
echo "installation complete \n";
}

?>