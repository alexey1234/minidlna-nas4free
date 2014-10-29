<?php
/*
	extensions_minidlna.php

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
If ($_GET) {
	if (isset($_GET['act']) && $_GET['act'] == "clear") { $cmd = "rm -f ".$config['minidlna']['homefolder']."web/minidlna/jd/tmp/*"; exec ($cmd); } 
}
if ($_POST) {
      if (isset($_POST['apply']) && $_POST['apply'] == "Apply changes" ) {
				$retval = 0;
				if (!file_exists($d_sysrebootreqd_path)) {
						$retval |= updatenotify_process("cronjob", "cronjob_process_updatenotification");
						config_lock();
						$retval |= rc_update_service("cron");
						config_unlock();
					}
				$savemsg = get_std_save_message($retval);
				if ($retval == 0) {
				updatenotify_delete("cronjob");
						}
		header("Location: extensions_minidlna.php");
			exit;
	}
	if (isset($_POST['submit1']) && ($_POST['submit1'] == "Save")) {
		
			if (!empty($config['minidlna']['homefolder']) && !empty($config['minidlna']['version'])) {
						$input_errors[] = "Extension configured, no need push on button!";
						header("Location: extensions_minidlna.php");
						exit;
					}
			if (empty($_POST['homefolder'])) {
					$input_errors[] = "Homefolder must be defined";
					goto out;
					
					}	
			$config['minidlna']['homefolder'] = $_POST['homefolder'];
			$config['minidlna']['version'] = "5";
			write_config();
			if (is_file ("/tmp/minidlna.install")) { unlink ("/tmp/minidlna.install"); }
			header("Location: services_upnp.php");
						exit;
	}
	if (isset($_POST['submit1']) && ($_POST['submit1'] == "Uninstall")) {

		//uninstall procedure
			  exec ("/etc/rc.d/minidlna stop");
	
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
			 if (false !== $index) { unset($config['cron']['job'][$index]); }
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
		      foreach ( glob( $config['minidlna']['homefolder']."ext/*.php" ) as $file ) {
		      $file = str_replace($config['minidlna']['homefolder']."ext", "/usr/local/www", $file);
		      if ( is_link( $file ) ) { unlink( $file ); } else {}	}
		      mwexec ("rm -rf /usr/local/www/ext/minidlna");
		      if ( is_link( "/usr/local/www/clear.png" ) ) { unlink(  "/usr/local/www/clear.png" );}
		    }
	
	
/// restore backuped files
		copy ($config['minidlna']['homefolder']."backup000/services_upnp.php", "/usr/local/www/services_upnp.php" );
		copy ($config['minidlna']['homefolder']."backup000/system_cron.php", "/usr/local/www/system_cron.php" );
		copy ( $config['minidlna']['homefolder']."backup000/system_cron_edit.php", "/usr/local/www/system_cron_edit.php");			    
		    
//remove minidlna section from config.xml
		if ( is_array($config['minidlna'] ) ) { unset( $config['minidlna'] ); write_config();}
		header("Location: /");
		exit;
		}
	if (isset($_POST['submit2']) && ($_POST['submit2'] == "Save")) {
		unset($input_errors);
				
		// Input validation.
		if (false == is_port($_POST['webport'])) $input_errors[] = sprintf( gettext("The attribute '%s' is an invalid port number."), $_POST['webport']); 
		if (services_is_port_used($_POST['webport'], "upnp"))
			$input_errors[] = sprintf(gettext("The attribute 'Port': port '%ld' is already taken by another service."), $_POST['webport']);
		if (empty($input_errors)) {
			
			$config['minidlna']['webview'] = isset($_POST['webview']) ? "on" : false;
			$config['minidlna']['webport'] = $_POST['webport'];
			
			write_config();
			
			
			if (isset($config['minidlna']['webview'])) {   
					write_webservconf (); 
					copy ($config['minidlna']['homefolder']."web/webserver.conf", "/var/etc/web_dlna.conf");
					$dlcapi = file($config['minidlna']['homefolder']."web/minidlna/script/dlcapi.class.php");
					$if = get_ifname($pconfig['if']);
					$ipaddr = get_ipaddr($if);
					$webport = $config['minidlna']['webport'];
					$url = htmlspecialchars("http://{$ipaddr}:{$webport}");
					$dlcapi[63] = "const dlc_content_generator_url = '{$url}';\n";
					$str = '';
					foreach ( $dlcapi as $lines ) {  $str .= $lines;  }
					file_put_contents($config['minidlna']['homefolder'].'web/minidlna/script/dlcapi.class.php', $str);
					$retval = mwexec ($config['minidlna']['homefolder']."bin/webserver restart");
					if ( 0 != $retval) {
							$input_errors[] = "Problem with webserver detected. Server cannot start";
							} 
				} else { mwexec ($config['minidlna']['homefolder']."bin/webserver stop"); }
			sleep(1);
		}
	}
	if (isset($_POST['submit3']) && ($_POST['submit3'] == "Save")) {
		unset($input_errors);
		// Input validation.
		if (false == is_numericint($_POST['notifyint'])) {$input_errors[] = "Must be integer value"; goto out; }
		if (false == is_numericint($_POST['notifyhold'])) {$input_errors[] = "Must be integer value"; goto out; }
		$pconfig = $_POST;
		$config['minidlna']['autorescan'] = isset($_POST['autorescan']) ? "on" : false;
		$config['minidlna']['notifyint'] = $_POST['notifyint'];
		$config['minidlna']['notifyhold'] = $_POST['notifyhold'];
		write_config();	
		if (isset($config['minidlna']['autorescan'])) {
			$retval = mwexec ($config['minidlna']['homefolder']."bin/scanner.sh start scanmedia");
			if ($retval==1) {$input_errors[] = "Something wrong into system. Try Disable ->Save --> Enable" .$retval; goto out;} else {$mess = "Scanner begin to work";}
			} else {
			$retval = mwexec ($config['minidlna']['homefolder']."bin/scanner.sh stop scanmedia");
			if ($retval==1) {$input_errors[] = "Something wrong into system."; goto out;} else {$mess = "Scanner stopped";}
			}
	}
}

/// This is main part.
if (true == is_file("/tmp/minidlna.install") ) { $pconfig['homefolder'] = file_get_contents("/tmp/minidlna.install"); } else
{
if (isset($config['minidlna']['autorescan'])) $pconfig['autorescan'] = "checked"; else unset ($pconfig['autorescan']);
$pconfig['notifyint'] = !empty($config['minidlna']['notifyint']) ? $config['minidlna']['notifyint'] : 5;
$pconfig['notifyhold'] = !empty($config['minidlna']['notifyhold']) ? $config['minidlna']['notifyhold'] : 10;
if (isset($config['minidlna']['webview'])) $pconfig['webview'] = "checked"; else unset ($pconfig['webview']);
$pconfig['webport'] = !empty($config['minidlna']['webport']) ? $config['minidlna']['webport'] : 8280;
$pconfig['homefolder'] = $config['minidlna']['homefolder'];

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



if (is_ajax()) {
	$upnpinfo = system_get_upnpinfo();
	render_ajax($upnpinfo);
}

}

function cronjob_process_updatenotification($mode, $data) {
	global $config;

	$retval = 0;

	switch ($mode) {
		case UPDATENOTIFY_MODE_NEW:
		case UPDATENOTIFY_MODE_MODIFIED:
			break;
		case UPDATENOTIFY_MODE_DIRTY:
			if (is_array($config['cron']['job'])) {
				$index = array_search_ex($data, $config['cron']['job'], "uuid");
				if (false !== $index) {
					unset($config['cron']['job'][$index]);
					write_config();
				}
			}
			break;
	}

	return $retval;
}
function system_get_upnpinfo() {
	global $config;
	$tabledata = array();
	$tabledata['server'] = $config['upnp']['server_t'];
	switch ($config['upnp']['server_t']) {
		case "fuppes":
			$tabledata['version'] =  exec("fuppesd -v | grep version| awk '{print$3}'");
			$tabledata['pidstatus'] = exec ("ps ax | grep fuppes | grep -v grep | awk '{print$1}'");
				
			break;
		case "minidlna":
			$tabledata['version'] = exec ("minidlna -V | awk '{print$2}'");
			$upnpip = get_ipaddr($config['upnp']['if']);
			$presurl = "http://".$upnpip.":".$config['upnp']['port'];
			$file_headers = @get_headers($presurl);
			if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
				$tabledata['pidstatus'] = false;
				} else {
				$page = file_get_contents($presurl);
				$DOM = new DOMDocument;
				$DOM->loadHTML($page);
				$items = $DOM->getElementsByTagName('i') -> item(0);
				if (empty ($items) ) {
					 $tabledata['pidstatus'] = exec ("ps ax | grep minidlna | grep -v grep | awk '{print$1}'");
					} else {
					  $tabledata['pidstatus'] = 1;
					}
				}
			
			//$tabledata['pidstatus'] = exec ("ps ax | grep minidlna | grep -v grep | awk '{print$1}'");
				
			break;
		case "off";
		$tabledata['version'] = "stopped";
		$tabledata['status'] = "stopped";
		break;
	}
	$tabledata['webviewtbl'] = exec ("ps ax | grep web_dlna | grep -v grep | awk '{print$1}'");
	$tabledata['notifiertbl'] = exec ("ps ax | grep scanmedia | grep -v grep | awk '{print$1}'");
	return $tabledata;
}
function system_get_upnpinfo_ex($key) {
	$ar = system_get_upnpinfo();
	$retval = $ar[$key];
	return $retval;
}	
out:
?>

<?php include("fbegin.inc"); ?>
<script type="text/javascript">//<![CDATA[


$(document).ready(function(){
	var gui = new GUI;
	gui.recall(6000, 6000, 'extensions_minidlna.php', null, function(data) {
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
						}
			      else { 
				$('#pidstatusimg').attr('src', 'status_disabled.png');
				$('#pidstatusimg').attr('title', 'Stopped');
				}} }	
		if (typeof(data.webviewtbl) !== 'undefined') {
			if ((data.webviewtbl) > 0 ) {
				  $('#webviewimg').attr('src', 'status_enabled.png');
				  $('#webviewimg').attr('title', 'Runing wit PID=' + data.webviewtbl);
			    } else { 
				  $('#webviewimg').attr('src', 'status_disabled.png');
				  $('#webviewimg').attr('title', 'Stopped');
				  }}
		if (typeof(data.notifiertbl) !== 'undefined' ) {
			if ((data.notifiertbl) > 0 ) { 
				  $('#notifierimg').attr('src', 'status_enabled.png');
				  $('#notifierimg').attr('title', 'Runing wit PID=' + data.notifiertbl);
			    } else { 
				  $('#notifierimg').attr('src', 'status_disabled.png');
				   $('#notifierimg').attr('title', 'Stopped');
				  }}
	});
	$('#uninstall').change(function() {
	
		if($('#uninstall').is(":checked")) {
			$('#submit1').prop('value','Uninstall');
				}else{
			$('#submit1').prop('value','Save');
				}
		});
	$('#uninstall').change();
	$('#webview').change(function() {
		if($('#webview').is(":checked")) {
			$('#webport_td').show();
			$('#cleancache').show();
				} else {
			$('#webport_td').hide();
			$('#cleancache').hide();
			}
		});
	$('#webview').change();
	$('#autorescan').change(function() {
		if($('#autorescan').is(":checked")) {
			$('#notifyint_td').show();
			$('#notifyhold_td').show();
				} else {
			$('#notifyint_td').hide();
			$('#notifyhold_td').hide();
			}
		});
	$('#autorescan').change();
});

</script>


	<table width="100%" border="0" cellpadding="0" cellspacing="0">
	<tr><td class="tabnavtbl">
		<ul id="tabnav">
			<li class="tabact">
				<a href="extensions_minidlna.php"><span><?=gettext("Main")?></span></a>
			</li>
			
			
		    <li class="tabinact"><a href="extensions_minidlna_log.php"><span><?=gettext("Log");?></span></a></li>
		</ul>
	</td></tr>
		<tr>
			<td class="tabcont">
				<form action="extensions_minidlna.php" method="post" name="wrap0" id="wrap0">
				<?php if (updatenotify_exists("cronjob"))  print_config_change_box();?>
				
				 <?php include("formend.inc");?>
				 </form>
				  <?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				
				
				<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php if ( false == is_file("/tmp/minidlna.install") ): ?>
				<tr id='checksts_tr'>
					<td width='22%' valign='top' class='vncell'><label for='name'><?php $minidlnavers = exec ("minidlna -V");  $titlelinevalue = "On board " . $minidlnavers; echo (gettext($titlelinevalue));?></label></td>
					<td width='78%' class='vtable'>
						<table class="formdata" width="100%" border="0" cellpadding="1" cellspacing="0">
						 <tr>
							<td width="10%" class="listhdrlr">&nbsp;</td>
													
							<td width="20%" class="listhdrlr"><?=gettext("Server");?></td>
							<td width="20%" class="listhdrlr"><?=gettext("Version");?></td>
							<td width="20%" class="listhdrlr"><?=gettext("Server status");?></td>
							<td width="20%" class="listhdrlr"><?=gettext("Web viewer");?></td>
							<td width="25%" class="listhdrlr"><?=gettext("Notifier");?></td>
							
							<td  class="list"></td>
								
						</tr>
						 <tr id="resultview">
						 
							<td width="10%" class="listhdrlr">&nbsp;</td>
							<td width="15%" class="listr" name="server"  id="server"></td>
							
							<td width="15%" class="listr" name="version" id="version"></td>
							<td width="15%" class="listr" name="pidstatus" id="pidstatus"><img id="pidstatusimg" src="status_disabled.png" border="0" alt="Stopped" /></td>
						
							<td width="15%" class="listr" name="webviewtbl" id="webviewtbl"><img id="webviewimg" src="status_disabled.png" border="0" alt="Stopped" /></td>
							
							<td width="20%" class="listr" id="notifiertbl"><img id="notifierimg" src="status_disabled.png" border="0" alt="Stopped" /></a>
							
							
							<td  class="list"></td>
								
						</tr>
						</table>
					</td>	
				</tr>
				<?php endif; ?>
				<tr><td colspan='2' class='list' height='6'></td></tr>
				<tr id='homefolder_tr'>
					<td width='22%' valign='top' class='vncell'><label for='homefolder'>Homefolder for extension</label></td>
					<td width='78%' class='vtable'>
					<form action="extensions_minidlna.php" method="post" name="wrap1" id="wrap1">
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
				<tr id='webview_tr'>
					<td width='22%' valign='top' class='vncell'><label for='webview'>Webview</label></td>
					<td width='78%' class='vtable'>
						<form action="extensions_minidlna.php" method="post" name="wrap2" id="wrap2">
						<table class="formdata" width="100%" border="0" >
						 <tr>
						    	<td width='33%'>
							      <input name='webview' type='checkbox' class='formfld' id='webview' <?=isset($pconfig['webview']) ? "checked" : false ?>  />&nbsp;
							      Enable webview plugin<br />
							      <span class='vexpl'></span>
							</td>
							<td id='webport_td' width='33%' >
								Port:&nbsp; <input name='webport' type='text' class='formfld' id='webport' size='5' value="<?=$pconfig['webport'];?>"  />&nbsp;&nbsp;
								<?php
					$if = get_ifname($pconfig['if']);
					$ipaddr = get_ipaddr($if);
					
					$url = htmlspecialchars("http://{$ipaddr}:{$pconfig['webport']}");
					$text = "<a href='{$url}' target='_blank'>URL</a>";
					?><?=$text;?> 
								<br /><span class='vexpl'></span>
							</td>
							<td width='33%' id="cleancache">
								<a href="extensions_minidlna.php?act=clear"><img src="clear.png" title="<?=gettext("Edit job");?>" border="0" alt="<?=gettext("Edit job");?>" /></a>
							</td>
							
						</tr>
						<tr><td colspan='2' class='list' height='8'></td></tr>
						<tr>
							<td width='33%' >
								 <input name="submit2" type="submit" class="formbtn" id='submit2' value="Save" align="center" />&nbsp;&nbsp;
							</td>
						</tr>
						</table>
						<?php include("formend.inc");?>
</form>
					</td>	
				</tr>	
				<tr><td colspan='2' class='list' height='6'></td></tr>
				<tr id='autorescan_tr'>
					<td width='22%' valign='top' class='vncell'><label for='autorescan_left'>Automatic rescan</label></td>
					<td width='78%' class='vtable'>
					<form action="extensions_minidlna.php" method="post" name="wrap3" id="wrap3">
						<table class="formdata" width="100%" border="0" cellpadding="1" cellspacing="0">
						 <tr>					     
							<td width='30%'>
							      <input name='autorescan' type='checkbox' class='formfld' id='autorescan' <?=isset($pconfig['autorescan']) ? "checked" : false ?>  />&nbsp;
							      <br /><span class='vexpl'>Enable automatic rescan</span>
							 </td>
							 <td width='35%' id='notifyint_td'>
							      <input name='notifyint' type='text' class='formfld' id='notifyint' size='10' value=<?=$pconfig['notifyint']; ?> />&nbsp;Check interval <b>T1</b>, seconds
							      <br /><span class='vexpl'>Server begin rescan mediafolder when detect new file automatically. But if file is large, scanner can detect the start time of the file upload. The timeout T1 can prevent the start of the process ahead of time. I recommend value is 100 sec</span>
						      </td>
						       <td width='35%' id='notifyhold_td'>
							      <input name='notifyhold' type='text' class='formfld' id='notifyhold' size='10' value=<?=$pconfig['notifyhold']; ?> />&nbsp;Cicle interval <b>T2</b>, seconds.
							      <br /><span class='vexpl'>If the scanner is not found no change in the media folder, this value determines the length of the loop search </span>
						      </td>
						</tr>
						<tr><td colspan='2' class='list' height='8'></td></tr>
						<tr>
						      <td width='25%' >
								 <input name="submit3" type="submit" class="formbtn"  id='submit3' value="Save" align="center" />&nbsp;&nbsp;
							</td>
						</tr>
						</table>
						<?php include("formend.inc");?>
</form>
					</td>	
				</tr>	
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
					<a href="system_cron_edit.php?uuid=<?=$uuid;?>"><img src="e.gif" title="<?=gettext("Edit job");?>" border="0" alt="<?=gettext("Edit job");?>" /></a>
					<a href="system_cron.php?act=del&amp;uuid=<?=$uuid;?>" onclick="return confirm('<?=gettext("Do you really want to delete this cron job?");?>')"><img src="x.gif" title="<?=gettext("Delete job");?>" border="0" alt="<?=gettext("Delete job");?>" /></a>
								</td>
							<?php else: ?>
							<td class="list" colspan="5"></td>
							<td class="list">
								    <?php if ($config['upnp']['server_t'] =="minidlna"): ?> 
								    <a href="system_cron_edit.php?&amp;command=/etc/rc.d/minidlna rescan&amp;desc=minidlna"><img src="plus.gif" title="<?=gettext("Add job");?>" border="0" alt="<?=gettext("Add job");?>" /></a>
								    <?php elseif ($config['upnp']['server_t'] =="fuppes"): ?>
								    <a href="system_cron_edit.php?&amp;command=/etc/rc.d/fuppes rebuilddb&amp;desc=minidlna"><img src="plus.gif" title="<?=gettext("Add job");?>" border="0" alt="<?=gettext("Add job");?>" /></a>
								    <?php endif;?>
								    </td>
							<?php endif;?>
							  </tr>
							 
							
						</table>
					</td>	
				</tr>
				<?php endif;?>
				</table>
				
			</td>
		</tr>
	</table>
	


<?php include("fend.inc");?>
