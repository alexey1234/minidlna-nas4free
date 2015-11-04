<?php
/*
extensions_minidlna_config.php
*/
ob_start();
require("auth.inc");
require("guiconfig.inc");
require("services.inc");
require("ext/minidlna/function.php");

$a_interface = get_interface_list();

// Use first interface as default if it is not set.
if (empty($pconfig['if']) && is_array($a_interface))
	$pconfig['if'] = key($a_interface);

$pgtitle = array(gettext("Services"),gettext("Minidlna"), gettext ("Maitanance and tools"));

if (!isset($config['minidlna']) || !is_array($config['minidlna']))
	$config['minidlna'] = array();
if (true == is_file("/tmp/minidlna.install") ) { 
	$pconfig['homefolder'] = file_get_contents("/tmp/minidlna.install"); 
	} else {
	$pconfig['homefolder'] = $config['minidlna']['homefolder'];
	}
if (is_ajax()) {
	$upnpinfo = system_get_upnpinfo();
	render_ajax($upnpinfo);
}

function system_get_upnpinfo() {
	global $config;
	$tabledata = array();
	$tabledata['server'] = "minidlna";
	$tabledata['version'] = exec ("minidlnad -V | awk '{print$2}'");
			$upnpip = get_ipaddr($config['upnp']['if']);
			if (is_file("/var/run/minidlna/upnp-av.scan") ) { $tabledata['pidstatus'] = 1; } else {
			$presurl = "http://".$upnpip.":".$config['upnp']['port'];
			$file_headers = @get_headers($presurl);
			if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
				$tabledata['pidstatus'] = false;
				} else {
					 $tabledata['pidstatus'] = exec ("ps ax | grep minidlna | grep -v grep | awk '{print$1}'");
					} 
				}
			
			//$tabledata['pidstatus'] = exec ("ps ax | grep minidlna | grep -v grep | awk '{print$1}'");

	return $tabledata;
}
?>
<?php include("fbegin.inc"); ?>
<script type="text/javascript">//<![CDATA[

$(document).ready(function(){
	var gui = new GUI;
	gui.recall(3000, 3000, 'extensions_minidlna_config.php', null, function(data) {
			$('#server').text(data.server);
			$('#version').text(data.version);
		if (typeof(data.pidstatus) !== 'undefined') {
			if ((data.pidstatus) > 2 ) {
				$('#pidstatusimg').attr('src', 'status_enabled.png');
				$('#pidstatusimg').attr('title', 'Runing wit PID=' + data.pidstatus);
			    } else {
					if  ((data.pidstatus) == 1 ) { 
						$('#pidstatusimg').attr('src', 'status_scan.png');
						$('#pidstatusimg').attr('title', 'Scan in progress');
						} else { 
				$('#pidstatusimg').attr('src', 'status_disabled.png');
				$('#pidstatusimg').attr('title', 'Stopped');
				}} }	
		
	});
	$('#uninstall').change(function() {
	
		if($('#uninstall').is(":checked")) {
			$('#submit1').prop('value','Uninstall');
				}else{
			$('#submit1').prop('value','Save');
				}
		});
	$('#uninstall').change();

});

</script>



	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabinact"><a href="extensions_minidlna.php"><span><?=gettext("Main")?></span></a></li>
			<li class="tabinact"><a href="extensions_minidlna_log.php"><span><?=gettext("Log");?></span></a></li>
			<li class="tabact"><a href="extensions_minidlna_config.php"><span><?=gettext("Maintanance");?></span></a></li>
		</ul>
	</td></tr>
		<tr>
			<td class="tabcont">
				
				  <?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php if ( false == is_file("/tmp/minidlna.install") ): ?>
					<tr id='checksts_tr'>
					<td width='22%' valign='top' class='vncell'><label for='name'><?php $minidlnavers = exec ("minidlna -V");  $titlelinevalue = "On board " . $minidlnavers; echo (gettext($titlelinevalue));?></label></td>
					<td width='78%' class='vtable'>
						<table class="formdata" width="100%" border="0" cellpadding="1" cellspacing="0">
						 <tr>
							<td width="30%" class="listhdrlr"><?=gettext("Server");?></td>
							<td width="30%" class="listhdrlr"><?=gettext("Version");?></td>
							<td width="30%" class="listhdrlr"><?=gettext("Server status");?></td>
						</tr>
						 <tr id="resultview">
							<td  class="listr" name="server"  id="server"></td>
							<td  class="listr" name="version" id="version"></td>
							<td  class="listr" name="pidstatus" id="pidstatus"><img id="pidstatusimg" src="status_disabled.png" border="0" alt="Stopped" /></td>
						</tr>
						</table>
					</td>	
					</tr>
					<?php endif; ?>
				<tr><td colspan='2' class='list' height='6'></td></tr>
				<tr id='homefolder_tr'>
					<td width='22%' valign='top' class='vncell'><label for='homefolder'>Homefolder for extension</label></td>
					<td width='78%' class='vtable'>
<form action="extensions_minidlna_config.php" method="post" name="wrap1" id="wrap1">
						  <table class="formdata" width="100%" border="0">
							<tr><td width='65%'>
								  <input name='homefolder' type='text' class='formfld' id='homefolder' size='67' value=<?=$pconfig['homefolder']?>  />
								  <br /><span class='vexpl'>Path, where extension live .</span>
							    </td>
							    <td width='35%'>
								    <?php if ( false == is_file("/tmp/minidlna.install") ): ?>
								   Check  for uninstall<input name='uninstall' type='checkbox' class='formfld' id='uninstall' />&nbsp;
								   <?php endif;?>
								  <input name="submit1" type="submit" class="formbtn" id='submit1' value="Save" align="center" />
								 
							 </td></tr>
						  </table>
						  <?php include("formend.inc");?>
</form>
					</td>
				</tr>
				<?php if ( false == is_file("/tmp/minidlna.install") ): ?>
				<tr><td colspan='2' class='list' height='6'></td></tr>
				<form action="exec.php" method="post" name="iform" id="iform" >
		<table width="100%" border="0" cellpadding="6" cellspacing="0">
		<?php 
			html_titleline(gettext("Update Availability")); 
			html_text($confconv, gettext("Current Status"),"The latest version on GitHub is: " . $git_ver . "<br /><br />Your version is: " . $brig_ver ); 
			?> 
			<tr>
			
			<td width="22%" valign="top" class="vncell">Update your installation&nbsp;</td>
			<td width="78%" class="vtable">
			<?=gettext("Click below to download and install the latest version.");?><br />
				<div id="submit_x">
					<input id="thebrig_update" name="thebrig_update" type="submit" class="formbtn" value="<?=_THEBRIG_UPDATE_BUTTON;?>" onClick="return confirm('<?=_THEBRIG_INFO_MGR;?>');" /><br />
				</div>
				<input name="txtCommand" type="hidden" value="<?="sh /tmp/thebrig_install.sh {$config['thebrig']['rootfolder']} 3";?>" />
			</td>
			

	</table><?php include("formend.inc");?>
</form>
<?php endif;?>
	</table>
	


<?php include("fend.inc");?>