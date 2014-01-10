<?php
/*
	function.php
*/
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
$testarray = $config['minidlna']['content']; 
$file = $config['minidlna']['homefolder']."web/webserver.conf";
$handle=fopen($file, "w");
If (isset($config['minidlna']['port'])) { $webport = 80 + $config['minidlna']['port']; fwrite ($handle, "server.port = ".$webport."\n");} else {} 
If (is_dir ( $config['minidlna']['homefolder']."web")) {  fwrite ($handle, "server.document-root = \"{$config['minidlna']['homefolder']}web/minidlna/\"\n");} else {}
fwrite ($handle, "server.bind = \"{$config['interfaces']['lan']['ipaddr']}\"\n");
foreach ($testarray as $item ) { 
$items = substr($item, 0, strlen($item)-1); 
$path2f = pathinfo($items);
fwrite ($handle, "alias.url += ( \"/{$path2f['basename']}/\" => \"{$item}\" )\n" );
fwrite ($handle, "url.rewrite-once += ( \"{$item}(.+)?\" => \"/{$path2f['basename']}/$1\" )\n" );
}
fwrite ($handle, "include \"webserver.inc\"\n"); 
fclose($handle); 
}
?>