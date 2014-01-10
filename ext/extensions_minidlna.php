<?php
/*
	extensions_minidlna.php

*/
require("auth.inc");
require("guiconfig.inc");
require("services.inc");
require("ext/minidlna/function.php");

$a_interface = get_interface_list();

// Use first interface as default if it is not set.
if (empty($pconfig['if']) && is_array($a_interface))
	$pconfig['if'] = key($a_interface);

$pgtitle = array(gettext("Services"),gettext("Minidlna"));

if (!isset($config['minidlna']) || !is_array($config['minidlna']))
	$config['minidlna'] = array();

if (!isset($config['minidlna']['content']) || !is_array($config['minidlna']['content'])) $config['minidlna']['content'] = array();
sort($config['minidlna']['content']);
$pconfig['enable'] = isset($config['minidlna']['enable']);
$pconfig['name'] = !empty($config['minidlna']['name']) ? $config['minidlna']['name'] : "";
$pconfig['if'] = !empty($config['minidlna']['if']) ? $config['minidlna']['if'] : "";
$pconfig['port'] = !empty($config['minidlna']['port']) ? $config['minidlna']['port'] : "8200";
$pconfig['serial'] = !empty($config['minidlna']['serial']) ? $config['minidlna']['serial'] : "12345678";
$pconfig['model'] = !empty($config['minidlna']['model']) ? $config['minidlna']['model'] : "1";
$pconfig['notify_int'] = !empty($config['minidlna']['notify_int']) ? $config['minidlna']['notify_int'] : "60";
$pconfig['strict'] = isset($config['minidlna']['strict']);
$pconfig['rescan'] = !empty($config['minidlna']['rescan']) ? $config['minidlna']['rescan'] : "manual";
$pconfig['tivo'] = isset($config['minidlna']['tivo']);
$pconfig['content'] = $config['minidlna']['content'];
$pconfig['loglevel'] = !empty($config['minidlna']['loglevel']) ? $config['minidlna']['loglevel'] : "warn";
$pconfig['webview'] = isset($config['minidlna']['webview']);
// Set name to configured hostname if it is not set.
if (empty($pconfig['name']))
	$pconfig['name'] = $config['system']['hostname'];



if ($_POST) {
	

	unset($input_errors);
// Input validation.
	if ( empty ($_POST['content'])) $input_errors[] = "Please define Media content folder";
	$pconfig = $_POST;

	if (empty($input_errors)) {
		$config['minidlna']['enable'] = isset($_POST['enable']) ? true : false;
		$config['minidlna']['name'] = $_POST['name'];
		$config['minidlna']['if'] = $_POST['if'];
		$config['minidlna']['port'] = $_POST['port'];
		$config['minidlna']['serial'] = $_POST['serial'];
		$config['minidlna']['notify_int'] = $_POST['notify_int'];
		$config['minidlna']['model'] = $_POST['model'];
		$config['minidlna']['strict'] = isset($_POST['strict']) ? true : false;
		$config['minidlna']['rescan'] = $_POST['rescan'];
		$config['minidlna']['tivo'] =  isset($_POST['tivo']) ? true : false;
		$config['minidlna']['content'] = !empty($_POST['content']) ? $_POST['content'] : array();
		$config['minidlna']['loglevel'] =  $_POST['loglevel'];
		$config['minidlna']['rescan'] = $_POST['rescan'];
		$config['minidlna']['webview'] = isset($_POST['webview']) ? true : false;			
		write_config();
		if (true == isset ($config['minidlna']['enable'])) {
		$cmd = $config['minidlna']['homefolder']."bin/minidlnad stop";
		mwexec ($cmd);
		write_dlnaconfig ();
		$cmd = $config['minidlna']['homefolder']."bin/minidlnad start";
		mwexec ($cmd);
		$savemsg1 = "Minidlna begin to scan folder, please wait..";
		if (isset($config['minidlna']['webview'])) {   write_webservconf (); 
			$dlcapi = file($config['minidlna']['homefolder']."web/minidlna/script/dlcapi.class.php");
			$if = get_ifname($pconfig['if']);
			$ipaddr = get_ipaddr($if);
			$webport = 80 +  $pconfig['port'];
			$url = htmlspecialchars("http://{$ipaddr}:{$webport}");
			$dlcapi[63] = "const dlc_content_generator_url = '{$url}';\n";
			$str = '';
			foreach ( $dlcapi as $lines ) {  $str .= $lines;  }
			file_put_contents($config['minidlna']['homefolder'].'web/minidlna/script/dlcapi.class.php', $str);
			$cmd =  $config['minidlna']['homefolder']."bin/webserver restart";
			mwexec ($cmd);
			$cmd = $config['minidlna']['homefolder']."bin/webserver status | awk '{print(int($6))}'";
			$check_server = exec ($cmd);
			if ( false == is_numeric($check_server)) {$input_errors[] = "Problem with webserver detected. Server cannot start";} 
			}	
			if (isset( $_POST['webcache'])) { $cmd = "rm -f ".$config['minidlna']['homefolder']."web/minidlna/jd/tmp/*"; exec ($cmd); }
			if ($_POST['rescan'] == 'schedule') { $savemsg1 = "Please configure rescan schedule"; header("Location: extensions_minidlna.php"); }
		}
		else { $cmd = $config['minidlna']['homefolder']."bin/minidlnad stop"; 	mwexec ($cmd);
			$cmd =  $config['minidlna']['homefolder']."bin/webserver stop"; mwexec ($cmd);
}

}
	
		
	
}

?>

<?php include("fbegin.inc"); ?>
<script type="text/javascript">
<!--
function enable_change(enable_change) {
	var endis = !(document.iform.enable.checked || enable_change);
	document.iform.name.disabled = endis;
	document.iform.if.disabled = endis;
	document.iform.port.disabled = endis;
	document.iform.content.disabled = endis;
	document.iform.contentaddbtn.disabled = endis;
	document.iform.contentchangebtn.disabled = endis;
	document.iform.contentdeletebtn.disabled = endis;
	document.iform.contentdata.disabled = endis;
	document.iform.contentbrowsebtn.disabled = endis;
	document.iform.loglevel.disabled = endis;
	document.iform.notify_int.disabled = endis;
	document.iform.model.disabled = endis;
	document.iform.serial.disabled = endis;
	document.iform.strict.disabled = endis;
	document.iform.tivo.disabled = endis;
	document.iform.rescan.disabled = endis;
	document.iform.webview.disabled = endis;
	document.iform.webcache.disabled = endis;
	document.iform.url.disabled = endis;
}
function web_change() {
	switch(document.iform.webview.checked) {
		case false:
			showElementById('url_tr','hide');
			showElementById('webcache_tr','hide');
			break;

		case true:
			showElementById('url_tr','show');
			showElementById('webcache_tr','show');
			break;
	}
}
//-->
</script>

<form action="extensions_minidlna.php" method="post" name="iform" id="iform">

	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabact">
				<a href="extensions_minidlna.php"><span><?=gettext("Main")?></span></a>
			</li>
			<li class="tabinact">
				<a href="extensions_minidlna_rescan.php"><span><?=gettext("Rescan")?></span></a>
			</li>
		    <li class="tabinact"><a href="extensions_minidlna_log.php"><span><?=gettext("Log");?></span></a></li>
		</ul>
	</td></tr>
		<tr>
			<td class="tabcont">
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<?php if (!empty($savemsg1)) print_info_box($savemsg1); ?>
				<?php if (file_exists($d_upnpconfdirty_path)) print_config_change_box();?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php $minidlnavers = exec ("minidlna -V"); $titlelinevalue = "Minidlna A/V Media Server " . $minidlnavers;?>
				<?php html_titleline_checkbox("enable", $titlelinevalue, !empty($pconfig['enable']) ? true : false, gettext("Enable"), "enable_change(false)" ); ?>
				
					<?php html_inputbox("name", gettext("Name"), $pconfig['name'], gettext("UPnP friendly name."), true, 20);?>
					<!--
					<?php html_interfacecombobox("if", gettext("Interface"), $pconfig['if'], gettext("Interface to listen to."), true);?>
					-->
				<tr>
					<td width="22%" valign="top" class="vncellreq"><?=gettext("Interface");?></td>
					<td width="78%" class="vtable">
					<select name="if" class="formfld" id="xif">
						<?php foreach($a_interface as $if => $ifinfo):?>
							<?php $ifinfo = get_interface_info($if); if (("up" == $ifinfo['status']) || ("associated" == $ifinfo['status'])):?>
							<option value="<?=$if;?>"<?php if ($if == $pconfig['if']) echo "selected=\"selected\"";?>><?=$if?></option>
							<?php endif;?>
						<?php endforeach;?>
					</select>
					<br /><?=gettext("Interface to listen to.");?>
					</td>
				</tr>
					<?php html_inputbox("port", gettext("Port"), $pconfig['port'], sprintf(gettext("Port to listen on. Only dynamic or private ports can be used (from %d through %d). Default port is %d."), 1025, 65535, 8200), true, 5);?>
					<!--<?php html_filechooser("home", gettext("Database directory"), $pconfig['home'], gettext("Location where the content database file will be stored."), $g['media_path'], true, 60);?>-->
					<?php html_folderbox("content", gettext("Content"), !empty($pconfig['content']) ? $pconfig['content'] : array(), gettext("Location of the files to share."), $g['media_path'], true);?>
					<?php html_inputbox("notify_int", gettext("Discover interval "), $pconfig['notify_int'], gettext("how often MiniDLNA broadcasts its availability on the network; default is every 60 seconds"), true, 5);?>
					<?php html_inputbox("model", gettext("Model number "), $pconfig['model'], gettext("Set the model number reported to clients. Default is 1"), true, 5);?>
					<?php html_inputbox("serial", gettext("Serial "), $pconfig['serial'], gettext("Set the serial number reported to clients. Default is 12345678"), true, 8);?>
					<?php html_checkbox ("strict", gettext("Strict DLNA"), !empty($pconfig['strict']) ? true : false, gettext(""), gettext("if checked will strictly adhere to DLNA standards which will allow server-side downscaling of very large JPEG images and may hurt JPEG serving performance on Sony DLNA products"), false);?>
					<?php html_checkbox ("tivo", gettext("Enable TiVo"), !empty($pconfig['tivo']) ? true : false, gettext(""), gettext("Enable digital video recorder (DVR) developed and marketed by TiVo, Inc"), false);?>
					<?php html_combobox("loglevel", gettext("Log level"), $pconfig['loglevel'], array("off" => gettext("Off"), "fatal" => gettext("fatal"), "error" => gettext("error"), "warn" => gettext("warning"), "info" => gettext("info"),"debug" => gettext("debug")), "", false, false, "" );?>
					<?php html_combobox("rescan", gettext("Rescan option"), $pconfig['rescan'], array("manual" => gettext("Manual"), "schedule" => gettext("Schedule")), "", false, false, "" );?>
					<?php html_checkbox ("webview", gettext("Enable WebViewer"), !empty($pconfig['webview']) ? true : false, gettext(""), gettext("Enable web viewer for Minidlna"), false, "web_change()" );?>
					<?php
					$if = get_ifname($pconfig['if']);
					$ipaddr = get_ipaddr($if);
					$webport = 80 +  $pconfig['port'];
					$url = htmlspecialchars("http://{$ipaddr}:{$webport}");
					$text = "<a href='{$url}' target='_blank'>{$url}</a>";
					?>
					<?php html_text("url", gettext("URL"), $text);?>
					<?php html_checkbox ("webcache", gettext("Web cache"), false, gettext("Clear web cache now"), gettext(""), false );?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" onclick="onsubmit_content(); enable_change(true)" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
			</td>
		</tr>
	</table>
	<?php include("formend.inc");?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
web_change();
//-->
</script>

<?php include("fend.inc");?>