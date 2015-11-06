<?php
/*
	extensions_minidlna.php

*/
require("auth.inc");
require("guiconfig.inc");
require("services.inc");
require("ext/minidlna/function.php");

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
$pconfig['loglevel'] = !empty($config['minidlna']['loglevel']) ? $config['minidlna']['loglevel'] : ".";
$pconfig['tivo'] = isset($config['minidlna']['tivo']);
$pconfig['content'] = $config['minidlna']['content'];
$pconfig['container'] = !empty($config['minidlna']['container']) ? $config['minidlna']['container'] : "warn";

unset ($tmpconfig);
$a_cronjob = &$config['cron']['job'];
if (FALSE !== ($cnid = array_search_ex("minidlna", $a_cronjob, "desc"))) {
	$tmpconfig['enable'] = isset($a_cronjob[$cnid]['enable']);
	$tmpconfig['uuid'] = $a_cronjob[$cnid]['uuid'];
	$uuid = $a_cronjob[$cnid]['uuid'];
	$tmpconfig['desc'] = $a_cronjob[$cnid]['desc'];
	$tmpconfig['minute'] = $a_cronjob[$cnid]['minute'];
	$tmpconfig['hour'] = $a_cronjob[$cnid]['hour'];
	$tmpconfig['day'] = $a_cronjob[$cnid]['day'];
	$tmpconfig['month'] = $a_cronjob[$cnid]['month'];
	$tmpconfig['weekday'] = $a_cronjob[$cnid]['weekday'];
	$tmpconfig['all_mins'] = $a_cronjob[$cnid]['all_mins'];
	$tmpconfig['all_hours'] = $a_cronjob[$cnid]['all_hours'];
	$tmpconfig['all_days'] = $a_cronjob[$cnid]['all_days'];
	$tmpconfig['all_months'] = $a_cronjob[$cnid]['all_months'];
	$tmpconfig['all_weekdays'] = $a_cronjob[$cnid]['all_weekdays'];
	$tmpconfig['who'] = $a_cronjob[$cnid]['who'];
	$tmpconfig['command'] = $a_cronjob[$cnid]['command'];
	if ( $tmpconfig['all_mins'] == 0 ) $a_pconfig['schedule']['minutes'] = implode (',', $tmpconfig['minute'] ); else $a_pconfig['schedule']['minutes'] = "All";
	if ( $tmpconfig['all_hours'] == 0 ) $a_pconfig['schedule']['hours'] = implode (',', $tmpconfig['hour'] ); else  $a_pconfig['schedule']['hours'] = "All";
	if ( $tmpconfig['all_days'] == 0 ) $a_pconfig['schedule']['days'] = implode (',', $tmpconfig['day'] ); else $a_pconfig['schedule']['days'] = "All";
	if ( $tmpconfig['all_months'] == 0 ) $a_pconfig['schedule']['months'] = implode (',', $tmpconfig['month'] ); else $a_pconfig['schedule']['months'] = "All";
	if ( $tmpconfig['all_weekdays'] == 0 ) $a_pconfig['schedule']['weekdays'] = implode (',', $tmpconfig['weekday'] ); else $a_pconfig['schedule']['weekdays'] = "All";
	
} else { 	unset ($a_pconfig['schedule']); }


if ($_POST) {
	if (isset($_POST['Submit']) && $_POST['Submit'] === "Save") {

	unset($input_errors);
// Input validation.
	if ( empty ($_POST['content'])) $input_errors[] = "Please define Media content folder";
	$pconfig = $_POST;

	if (empty($input_errors)) {
		if (isset ($config['minidlna']['content']) || is_array ($config['minidlna']['content'])) $currentconfig = $config['minidlna']; else unset($currentconfig);
		
		$config['minidlna']['enable'] = isset($_POST['enable']) ? true : false;
		$config['minidlna']['name'] = $_POST['name'];
		$config['minidlna']['if'] = $_POST['if'];
		$config['minidlna']['port'] = $_POST['port'];

		$config['minidlna']['notify_int'] = $_POST['notify_int'];

		$config['minidlna']['strict'] = isset($_POST['strict']) ? true : false;

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
			if (count ($check_differences) > 0 ) {
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
	if (isset($_POST['apply']) && $_POST['apply'] === "Apply changes") {
		
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

<form action="extensions_minidlna.php" method="post" name="iform" id="iform">

	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabact">
				<a href="extensions_minidlna.php"><span><?=gettext("Main")?></span></a>
			</li>
			
		    <li class="tabinact"><a href="extensions_minidlna_log.php"><span><?=gettext("Log");?></span></a></li>
			<li class="tabinact">
				<a href="extensions_minidlna_config.php"><span><?=gettext("Maintanance")?></span></a>
			</li>
		</ul>
	</td></tr>
		<tr>
			<td class="tabcont">
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
				
				<?php if (updatenotify_exists("minidlna" )) print_config_change_box();?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
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
				<?php html_minidlnabox("content", gettext("Content"), !empty($pconfig['content']) ? $pconfig['content'] : array(), gettext("Location of the files to share."), $g['media_path'], true);?>
					<?php html_combobox("container", gettext("Container"), $pconfig['container'], array("." => "Standard", "B" =>"Browse Directory", "M" => "Music", "V" => "Video", "P" => "Pictures"), "Use different container as root of the tree", false, false, "" );?>

					<?php html_checkbox ("strict", gettext("Strict DLNA"), !empty($pconfig['strict']) ? true : false, gettext(""), gettext("if checked will strictly adhere to DLNA standards which will allow server-side downscaling of very large JPEG images and may hurt JPEG serving performance on Sony DLNA products"), false);?>
					<?php html_checkbox ("tivo", gettext("Enable TiVo"), !empty($pconfig['tivo']) ? true : false, gettext(""), gettext("Enable digital video recorder (DVR) developed and marketed by TiVo, Inc"), false);?>
					<?php html_combobox("loglevel", gettext("Log level"), $pconfig['loglevel'], array("off" => gettext("Off"), "fatal" => gettext("fatal"), "error" => gettext("error"), "warn" => gettext("warning"), "info" => gettext("info"),"debug" => gettext("debug")), "", false, false, "" );?>
					<?php //html_combobox("rescan", gettext("Rescan option"), $pconfig['rescan'], array("manual" => gettext("Manual"), "schedule" => gettext("Schedule")), "", false, false, "" );?>
					<?php
					$if = get_ifname($pconfig['if']);
					$ipaddr = get_ipaddr($if);
					$url = htmlspecialchars("http://{$ipaddr}:{$pconfig['port']}");
					$text = "<a href='{$url}' target='_blank'>{$url}</a>";
					?>
					<?php html_text("url", gettext("Presentation"), $text);?>
					<tr><td colspan='2' class='list' height='6'></td></tr>
				<tr id='shedrescan_tr'>
					<td width='22%' valign='top' class='vncell'><label for='name'>Sheduled rescan Minidlna database</label></td>
					<td width='78%' class='vtable'>
						<table class="formdata" width="100%" border="0" cellpadding="1" cellspacing="0">
							<tr>
								<td class="listhdrr"><?=gettext("Minutes");?></td>
								<td class="listhdrr"><?=gettext("Hours");?></td>
								<td class="listhdrr"><?=gettext("Days");?></td>
								<td class="listhdrr"><?=gettext("Months");?></td>
								<td class="listhdrr"><?=gettext("Week days");?></td>
								<td class="list"></td>
							 </tr>
							 <tr>
							<?php  if ( is_array ( $a_pconfig['schedule'] ) ):?>
								<td class="listr"><?=$a_pconfig['schedule']['minutes']?></td>
								<td class="listr"><?=$a_pconfig['schedule']['hours']?></td>
								<td class="listr"><?=$a_pconfig['schedule']['days']?></td>
								<td class="listr"><?=$a_pconfig['schedule']['months']?></td>
								<td class="listr"><?=$a_pconfig['schedule']['weekdays']?></td>
						
								<td valign="middle" nowrap="nowrap" class="list">
					<a href="system_cron_edit1.php?uuid=<?=$uuid;?>"><img src="e.gif" title="<?=gettext("Edit job");?>" border="0" alt="<?=gettext("Edit job");?>" /></a>
					<a href="system_cron1.php?act=del&amp;uuid=<?=$uuid;?>" onclick="return confirm('<?=gettext("Do you really want to delete this cron job?");?>')"><img src="x.gif" title="<?=gettext("Delete job");?>" border="0" alt="<?=gettext("Delete job");?>" /></a>
								</td>
							<?php else: ?>
							<td class="list" colspan="5"></td>
							<td class="list">
								    
								    <a href="system_cron_edit1.php?&amp;command=/etc/rc.d/minidlna rescan&amp;desc=minidlna"><img src="plus.gif" title="<?=gettext("Add job");?>" border="0" alt="<?=gettext("Add job");?>" /></a>
								   
								   
								    </td>
							<?php endif;?>
							  </tr>
				
										</table>
					</td>	
				</tr>
				
				
			</td>
		</tr>
		
	</table>
	<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?="Save";?>" onclick="onsubmit_content(); enable_change(true)" />
					<input name="uuid" type="hidden" value="<?=$pconfig['uuid'];?>" />
				</div>
	<?php 	include("formend.inc");?>
</form>
<script type="text/javascript">
<!--
enable_change(false);
//-->
</script>

<?php include("fend.inc");?>