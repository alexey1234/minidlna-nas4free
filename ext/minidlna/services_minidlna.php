<?php
/*
	extensions_minidlna.php

*/
require("auth.inc");
require("guiconfig.inc");
require("services.inc");
unset($currentconfig);
$homechanged =0;
if (is_file("/tmp/minidlna.install")) header("Location: extensions_minidlna_config.php");
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
$pconfig['name'] = !empty($config['minidlna']['name']) ? $config['minidlna']['name'] : $pconfig['name'] = $config['system']['hostname'];
$pconfig['if'] = !empty($config['minidlna']['if']) ? $config['minidlna']['if'] : "";
$pconfig['port'] = !empty($config['minidlna']['port']) ? $config['minidlna']['port'] : "8200";
$pconfig['notify_int'] = !empty($config['minidlna']['notify_int']) ? $config['minidlna']['notify_int'] : "60";
$pconfig['strict'] = isset($config['minidlna']['strict']);
$pconfig['loglevel'] = !empty($config['minidlna']['loglevel']) ? $config['minidlna']['loglevel'] : "warn";
$pconfig['tivo'] = isset($config['minidlna']['tivo']);
$pconfig['content'] = $config['minidlna']['content'];
$pconfig['container'] = !empty($config['minidlna']['container']) ? $config['minidlna']['container'] : "B";
$pconfig['inotify'] = isset($config['minidlna']['inotify']);
$pconfig['home'] = $config['minidlna']['home'];

if ($_POST) {
	
	if (isset($_POST['Submit']) && $_POST['Submit']) {
file_put_contents("/tmp/postsubmit", serialize($_POST));
	unset($input_errors);
// Input validation.
	if ( !is_array ($_POST['content'])) $input_errors[] = "Please define Media content folder";
	if ( empty ($_POST['home']) || !is_dir ($_POST['home'])) $input_errors[] = "Location where the database with media contents not valid";
	$pconfig = $_POST;

	if (empty($input_errors)) {
		if (isset ($config['minidlna']['content']) || is_array ($config['minidlna']['content'])) $currentconfig = $config['minidlna']; else unset($currentconfig);
		if ($config['minidlna']['home'] !== $_POST['home'] ) {
			$homechanged = 1; 
			chown($_POST['home'], "dlna");
			chmod ($_POST['home'], 0755);
			}
		$config['minidlna']['enable'] = isset($_POST['enable']) ? true : false;
		$config['minidlna']['name'] = $_POST['name'];
		$config['minidlna']['if'] = $_POST['if'];
		$config['minidlna']['port'] = $_POST['port'];

		$config['minidlna']['notify_int'] = $_POST['notify_int'];
		$config['minidlna']['home'] =  $_POST['home'];
		$config['minidlna']['strict'] = isset($_POST['strict']) ? true : false;
		$config['minidlna']['inotify'] = isset($_POST['inotify']) ? true : false;
		$config['minidlna']['tivo'] =  isset($_POST['tivo']) ? true : false;
		$config['minidlna']['content'] = $_POST['content'];
		$config['minidlna']['loglevel'] =  $_POST['loglevel'];
		$config['minidlna']['container'] =  $_POST['container'];
		
		if (empty ($currentconfig['content'])) {
		updatenotify_set("minidlna", UPDATENOTIFY_MODE_NEW, "Media database begin proccessing");
		}	else {
			$a_content = $config['minidlna']['content'];
			$b_content = $currentconfig['content'];
			sort ($a_content);
			sort ($b_content);
			$check_differences = array_merge (  array_diff_assoc ( $a_content ,$b_content ), array_diff_assoc ( $b_content ,  $a_content));
			if (count ($check_differences) > 0 || $homechanged == 1) {
				updatenotify_set("minidlna", UPDATENOTIFY_MODE_MODIFIED, "Minidlna begin rescan database");
					} else {
				updatenotify_set("minidlna", UPDATENOTIFY_MODE_DIRTY, "Minidlna reloaded");
					}
		}
		write_config();
		header("Location: extensions_minidlna.php");
		exit;
		}
	} // End POST save
	if (isset($_POST['apply']) && $_POST['apply']) {
file_put_contents("/tmp/postapply", serialize($_POST));		
			$retval =0;
			if (!file_exists($d_sysrebootreqd_path)) {
					
					
					config_lock();
					$retval != rc_stop_service('minidlna') ;
					$retval = $retval << 1;
					$retval |=  rc_update_service ( 'minidlna' );
					$retval = $retval << 1;
					$retval |= rc_update_service("mdnsresponder");
					config_unlock();
					$savemsg = get_std_save_message($retval);
					if ($retval === 0) { 
					unset ($savemsg);
					$notification = updatenotify_get("minidlna");					
					$savemsg = $notification[0]['data'];
					updatenotify_delete("minidlna"); }
				}
	}
}
include("fbegin.inc"); ?>

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
	document.iform.container.disabled = endis;
	document.iform.notify_int.disabled = endis;
	document.iform.strict.disabled = endis;
	document.iform.tivo.disabled = endis;
}
//-->
</script>

<form action="services_minidlna.php" method="post" name="iform" id="iform">
<?php if (true === isset($config['upnp']['enable'])) $savemsg = "Fuppes enabled. If you want to use Minidlna , disable Fuppes in first"; ?>
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tabcont">
			
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
				<?php if (updatenotify_exists("minidlna" )) print_config_change_box();?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">

				<tr><td class="tabnavtbl">
				<ul id="tabnav">
					<li class="tabinact"><a href="services_fuppes.php"><span><?=gettext("Fuppes")?></span></a></li>
				    <li class="tabact"><a href="services_minidlna.php"><span><?=gettext("Minidlna");?></span></a></li>
				</ul>
				</td></tr>
				
				
				<?php if (false === isset($config['upnp']['enable'])) : ?>
				<?php html_titleline_checkbox("enable", gettext("Minidlna A/V Media Server"), !empty($pconfig['enable']) ? true : false, gettext("Enable"), "enable_change(false)" ); ?>
							
					<?php html_inputbox("name", gettext("Name"), $pconfig['name'], gettext("UPnP friendly name."), true, 20);?>
					<!--
					<?php html_interfacecombobox("if", gettext("Interface"), $pconfig['if'], gettext("Interface to listen to."), true);?>
					-->
				<tr>
					<td width="22%" valign="top" class="vncellreq"><?=gettext("Network settings");?></td>
					<td width="78%" class="vtable">
					<table>
						<tr><td width="30%"><b><?=gettext("Interface");?></b></td><td width="40%"><b><?=gettext("Port");?></b></td><td width="30%"><b><?=gettext("Discover interval ");?></b></td></tr>
						<tr><td>
								<select name="if" class="formfld" id="xif">
						<?php foreach($a_interface as $if => $ifinfo):?>
							<?php $ifinfo = get_interface_info($if); if (("up" == $ifinfo['status']) || ("associated" == $ifinfo['status'])):?>
								<option value="<?=$if;?>"<?php if ($if == $pconfig['if']) echo "selected=\"selected\"";?>><?=$if?></option>
							<?php endif;?>
						<?php endforeach;?>
								</select>
					<br /><?=gettext("Interface to listen to.");?>
					</td>
					<td><input name='port' type='text' class='formfld' id='port' size='5' value="<?=$pconfig['port'];?>"  />
					<br /><span class='vexpl'>Port to listen on. Only dynamic or private ports can be used (from 1025 through 65535). Default port is 8200.</span>
					</td>
					<td><input name='notify_int' type='text' class='formfld' id='notify_int' size='5' value="<?=$pconfig['notify_int'];?>"  />
						<br /><span class='vexpl'>how often MiniDLNA broadcasts its availability on the network; default is every 60 seconds</span>
					</td>
					</tr>
						
					</table>
					</td>
				</tr>
				<?php html_folderbox("content", gettext("Content"), !empty($pconfig['content']) ? $pconfig['content'] : array(), gettext("Location of the files to share."), $g['media_path'], true);?>
					<?php html_filechooser("home", gettext("Database directory"), $pconfig['home'], gettext("Location where the database with media contents will be stored."), $g['media_path'], true, 67);?>

					<?php html_checkbox("inotify", gettext("Inotify"), !empty($pconfig['inotify']) ? true : false, gettext("Check this to enable inotify monitoring to automatically discover new files"), "" ); ?>
		
					<?php html_combobox("container", gettext("Container"), $pconfig['container'], array("." => "Standard", "B" =>"Browse Directory", "M" => "Music", "V" => "Video", "P" => "Pictures"), "Use different container as root of the tree", false, false, "" );?>

					<?php html_checkbox ("strict", gettext("Strict DLNA"), !empty($pconfig['strict']) ? true : false,  "if checked will strictly adhere to DLNA standards which will allow server-side downscaling of very large JPEG images and may hurt JPEG serving performance on Sony DLNA products","", false);?>
					<?php html_checkbox ("tivo", gettext("Enable TiVo"), !empty($pconfig['tivo']) ? true : false,  "Enable digital video recorder (DVR) developed and marketed by TiVo, Inc", "",false);?>
					<?php html_combobox("loglevel", gettext("Log level"), $pconfig['loglevel'], array("off" => gettext("Off"), "fatal" => gettext("fatal"), "error" => gettext("error"), "warn" => gettext("warning"), "info" => gettext("info"),"debug" => gettext("debug")), "", false, false, "" );?>
							<?php
					$if = get_ifname($pconfig['if']);
					$ipaddr = get_ipaddr($if);
					$url = htmlspecialchars("http://{$ipaddr}:{$pconfig['port']}");
					$text = "<a href='{$url}' target='_blank'>{$url}</a>";
					?>
					<?php html_text("url", gettext("Presentation"), $text);?>
					<tr><td colspan='2' class='list' height='6'></td></tr>

		</td>
	</tr>	
	</table>
	<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?="Save";?>" onclick="onsubmit_content(); enable_change(true)" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
				<?php endif; ?>
				
	<?php 	include("formend.inc");?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>

<?php include("fend.inc");?>
