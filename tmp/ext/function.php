<?php
/*
	function.php
*/
$Rebuild_db_req = "/var/run/rebuild.req";


function print_upnp_dbchange_box() {
	echo "<div id='applybox'>";
	print_info_box("Find existing database, but setting on config need rebuilt it. What do you want?");
	echo "<input name=\"rebuild\" type=\"submit\" class=\"formbtn\" id=\"rebuild\" value=\"Rebuild\" />";
	echo "<input name=\"leave\" type=\"submit\" class=\"formbtn\" id=\"leave\" value=\"Leave_current\" />";
	echo "</div>";
}

function write_dlnaconfig () {
global $config, $g;
$testarray = array();
// copy part of config in temporary array
$testarray = $config['minidlna']['content']; 
$file = $config['minidlna']['homefolder']."minidlna.conf";
$handle=fopen($file, "w");
If (isset($config['minidlna']['name'])) { fwrite ($handle, "friendly_name=".$config['minidlna']['name']."\n");} else {}
If (isset($config['minidlna']['if'])) { fwrite ($handle, "network_interface=".$config['minidlna']['if']."\n");} else {}
If (isset($config['minidlna']['port'])) { fwrite ($handle, "port=".$config['minidlna']['port']."\n");} else {}
If (isset($config['minidlna']['serial'])) { fwrite ($handle, "serial=".$config['minidlna']['serial']."\n");} else {}
If (isset($config['minidlna']['model'])) { fwrite ($handle, "model_number=".$config['minidlna']['model']."\n");} else {}
If (isset($config['minidlna']['strict'])) { fwrite ($handle, "strict_dlna=yes\n");} else {fwrite ($handle, "strict_dlna=no\n");}
If (isset($config['minidlna']['notify_int'])) { fwrite ($handle, "notify_interval=".$config['minidlna']['notify_int']."\n");} else {}
If (isset($config['minidlna']['tivo'])) { fwrite ($handle, "enable_tivo=yes\n");} else {fwrite ($handle, "enable_tivo=no\n");}
// fwrite ($handle, "presentation_url=10.0.0.25:8200\n");
fwrite ($handle, "inotify=no\n");
fwrite ($handle, "minissdpdsocket=/var/run/minissdpd.sock\n");
fwrite ($handle, "root_container=B\n");
fwrite ($handle, "db_dir=".$config['minidlna']['homefolder']."db\n");
$logdir = substr($config['minidlna']['homefolder'], 0, strlen($item)-1);
fwrite ($handle, "log_dir=".$logdir."\n");
fwrite ($handle, "log_level=general,artwork,database,inotify,scanner,metadata,http,ssdp,tivo=".$config['minidlna']['loglevel']."\n");
fwrite ($handle, "album_art_names=Cover.jpg/cover.jpg/AlbumArtSmall.jpg/albumartsmall.jpg/AlbumArt.jpg/albumart.jpg/Album.jpg/album.jpg/Folder.jpg/folder.jpg/Thumb.jpg/thumb.jpg\n");


foreach ($testarray as $key => $item ) { $items = substr($item, 0, strlen($item)-1); fwrite ($handle, "media_dir=".$items."\n");}
fclose($handle);
}
function write_webservconf () {
global $config, $g;

$testarray = array();
$testarray = $config['upnp']['content']; 
$file = $config['minidlna']['homefolder']."web/webserver.conf";
$handle=fopen($file, "w");
If (isset($config['minidlna']['webport'])) {  fwrite ($handle, "server.port = ".$config['minidlna']['webport']."\n");} else {} 
If (is_dir ( $config['minidlna']['homefolder']."web")) {  fwrite ($handle, "server.document-root = \"{$config['minidlna']['homefolder']}web/minidlna/\"\n");} else {}
$serveripadr=get_ipaddr($config['upnp']['if']);
fwrite ($handle, "server.bind = \"{$serveripadr}\"\n");
foreach ($testarray as $item ) { 
$items =$item; 
$path2f = pathinfo($items);
fwrite ($handle, "alias.url += ( \"/{$path2f['basename']}/\" => \"{$item}/\" )\n" );
fwrite ($handle, "url.rewrite-once += ( \"{$item}/(.+)?\" => \"/{$path2f['basename']}/$1\" )\n" );
}
fwrite ($handle, "include \"{$config['minidlna']['homefolder']}web/webserver.inc\"\n"); 
fclose($handle); 
}

function sanitize_filechoicer ($input,$strlen) {
if (is_array($input)) {
	$content = array();
	foreach ($input as $i => $path ) {
			if (strlen($path) > 14 && $path[strlen($path)-1] == "/") {
				$path1 = substr($path, 0, strlen($path)-1);
				$content[$i] = $path1;
			} else $content[$i] = $path;
		}
	$output = $content;
	} else  {
	if (strlen($input) > $strlen && $input[strlen($input)-1] == "/") {$output =  substr($input, 0, strlen($input)-1);} else {$output = $input;}
	}
return $output;
}
function check_db_file ($switch) {
	global $post, $input_errors;
	$return = 0;
	switch ($switch) {
		case "fuppes":
			if (is_file($post['home']."/fuppes.db")) { 
				$return = "print_ask_box"; 
			} else {
				$return = "print_standart_box";
			}
		break;
		case "minidlna":
			if(isset ($post['extconfig'])) {
				if (false === ($extconfigpath = get_ext_db_path ())) {
					$input_errors[] = "defined external configuration file not found, please check it";
					$return = FALSE;
					} else {
					$return = "print_ask_box";
					}
				} else {
					if (is_file($post['home']."/files.db")) { 
						$return = "print_ask_box"; 
						} else {
						$return = "print_standart_box";
						}
				}
	break;
	}
return $return;
}
function rc_update_rcconf_ext($name) {
	global $config;
	$data = @file_get_contents("/etc/rc.d/$name");
	$search = "/RCVAR: (.*)/";
	if (!preg_match($search, $data, $rcvar)) {
		return 0;
	}

	// Update /etc/rc.conf
	switch ($config['upnp']['server_t']) {
		case "fuppes" :
				if (isset($config['upnp']['enable'])) $state = "enable"; else $state = "disable";
			break;
		case "minidlna" :
				if (isset($config['minidlna']['enable'])) $state = "enable"; else $state = "disable";
			break;
		case "off" ;
				$state = "disable";
				$retval |= mwexec("/usr/local/sbin/rconf service {$state} minidlna");
				$retval |= mwexec("/usr/local/sbin/rconf service {$state} fuppes");
				return 0;
			break;
	}
	$retval = mwexec("/usr/local/sbin/rconf service {$state} {$rcvar[1]}");

	return $retval;
}
function get_ext_db_path ($path) {
	$retval['error'] = 0;
	$retval['value'] = '';
	if (!is_file ($path))  $retval['error'] = "file not found";
	$e_path = file($path);
	$a_path = preg_grep("|^db_dir=|", $e_path);
	sort($a_path);
	foreach ($a_path as $pieces ) {
		$tempor=explode("=",$pieces);
		$path2db[] = trim($tempor[1]);
	}
	$finded_n = count ($path2db);
	
	if ($finded_n > 1 ) {$retval['error'] = "multiple find";} else {$retval['error'] = 0; $retval['value'] = $path2db[0];}
	
	return $retval;
}
// Stop service.
// Return 0 if successful, otherwise 1.
function rc_onestop_service($name) {
	// Execute script
	$retval = rc_exec_script("/etc/rc.d/{$name} onestop");
	if (0 == $retval) {
		write_log("{$name} service stopped");
	}	else {
		write_log("Failed to stop service {$name}");
	}
	return $retval;
}
function upnp_restart_procedure() {
	global $conig; 
	$retval = 0;
		if ($config['upnp']['server_t'] == "fuppes") {
			config_lock();
			$test = rc_is_service_running("minidlna");
			if ($test == 0) {
				$retval |= rc_onestop_service("minidlna");
				$retval |= rc_update_rcconf("minidlna","disable");
				}
			$retval |= rc_update_service("fuppes");
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		} else {
			config_lock();
			$test = rc_is_service_running("fuppes");
			if ($test == 0) {
				$retval |= rc_onestop_service("fuppes");
				$retval |= rc_update_rcconf("fuppes","disable");
				}
				$retval |= rc_update_service("minidlna");
				$retval |= rc_update_service("mdnsresponder");
			config_unlock();
		}
	return $retval;
}
function upnp_stopall_procedure() {
	global $conig; 
	$retval = 0;
	config_lock();
		if ($config['upnp']['server_t'] == "fuppes") {
			
			$test = rc_is_service_running("minidlna");
			if ($test == 0) {
				$retval |= rc_onestop_service("minidlna");
				$retval |= rc_update_rcconf("minidlna","disable");
				}
			$test = rc_is_service_running("fuppes");
			if ($test == 0) {
				$retval |= rc_onestop_service("fuppes");
				}
			
		} elseif ($config['upnp']['server_t'] == "minidlna") {
			$test = rc_is_service_running("fuppes");
			if ($test == 0) {
				$retval |= rc_onestop_service("fuppes");
				$retval |= rc_update_rcconf("fuppes","disable");
				}
			$test = rc_is_service_running("fuppes");
			if ($test == 0) {
				$retval |= rc_onestop_service("minidlna");
				}
			
		}
	config_unlock();
	return $retval;
}

?>