<?php
/*
extensions_minidlna_config.php
*/
define (MINIDLNA_VERSION,2);
require("auth.inc");
require("guiconfig.inc");
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
if (isset ($_POST["submit1"]) && $_POST["submit1"] =="Save") {
	
			if (!empty($config['minidlna']['homefolder'])) {
						$input_errors[] = "Extension configured, no need push on button!";
						unlink_if_exists("/tmp/minidlna.install");
						goto out;
					}
			if (empty($_POST['homefolder'])) {
					$input_errors[] = "Homefolder must be defined";
					goto out;
					
					}	
			$config['minidlna']['homefolder'] = $_POST['homefolder'];
			$i = 0;
			if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
		    for ($i; $i < count($config['rc']['postinit']['cmd']);) {
		    if (preg_match('|ext\/minidlna/|', $config['rc']['postinit']['cmd'][$i])) 	break;
				++$i;	} 	
			$config['rc']['postinit']['cmd'][$i] = $config['minidlna']['homefolder']."/minidlna_start.php";	
				}
			
			write_config();
			unlink_if_exists("/tmp/minidlna.install");
			header("Location: extensions_minidlna_config.php");
						exit;
}	elseif (isset($_POST['submit1']) && ($_POST['submit1'] == "Uninstall")) {

		//uninstall procedure
			 rc_stop_service('minidlna');
	
		if ( is_array($config['rc']['postinit'] ) && is_array( $config['rc']['postinit']['cmd'] ) ) {
			for ($i = 0; $i < count($config['rc']['postinit']['cmd']);) {
				if (preg_match('/minidlna_start/', $config['rc']['postinit']['cmd'][$i])) {	unset($config['rc']['postinit']['cmd'][$i]);} else{}
				++$i;
			  }
		    }
		
		if ( is_array($config['cron'] ) && is_array( $config['cron']['job'] )) {
			 $index = array_search_ex("minidlna", $config['cron']['job'], "desc");
			 if (count($config['cron']['job']) > 1) {
				if (false !== $index) { unset($config['cron']['job'][$index]); }
			 } else {
				 if (false !== $index) { unset($config['cron']['job']); }
			 }
		    }

		if ( is_link ( "/etc/rc.d/minidlna") ) { 	unlink("/etc/rc.d/minidlna"); }

//remowe web pages
		if (is_dir ("/usr/local/www/ext/minidlna")) {
		      foreach ( glob( $config['minidlna']['homefolder']."/ext/minidlna/*.php" ) as $file ) {
		      $file = str_replace($config['minidlna']['homefolder']."/ext/minidlna", "/usr/local/www", $file);
		      if ( is_link( $file ) ) { unlink( $file ); } else {}	}
		      mwexec ("rm -rf /usr/local/www/ext/minidlna");
		      if ( is_link( "/usr/local/www/status_scan.png" ) ) { unlink(  "/usr/local/www/status_scan.png" );}
		    }
	    
//remove minidlna section from config.xml
		if ( is_array($config['minidlna'] ) ) { unset( $config['minidlna'] ); write_config();}
		header("Location: /");
		exit;
		} else {
unlink_if_exists ("/tmp/extensions_minidlna_config.php");
$connected = @fsockopen("www.github.com", 80); 
if ( $connected ) {
	fclose($connected);
	unset($gitconfigfile);
	$gitconfigfile = file_get_contents("https://raw.githubusercontent.com/alexey1234/minidlna-nas4free/master/ext/minidlna/extensions_minidlna_config.php");
	$git_ver = preg_split ( "/MINIDLNA_VERSION,/", $gitconfigfile);
	$git_ver = 0 + $git_ver[1];
	mwexec2 ( "fetch {$fetch_args} -o /tmp/install.sh https://raw.githubusercontent.com/alexey1234/minidlna-nas4free/master/install.sh" , $garbage , $fetch_ret_val ) ;
				if ( is_file("/tmp/install.sh" ) ) {
					// Fetch of install.sh succeeded
					mwexec ("chmod a+x /tmp/install.sh");
				}	
				else {					
					$input_errors[]="There seems to be a networking issue. I can't reach GitHub to retrieve the file. <br />Please check <a href='/system.php'>DNS</a> and other <a href='/interfaces_lan.php'>networking settings</a>. <br />Alternatively, try it again to see if there was some transient network problem.";
				}  // end of failed install.sh fetch	
			
		} // end of successful internet connectivity test
	
	
if (is_ajax()) {
	$upnpinfo = system_get_upnpinfo();
	render_ajax($upnpinfo);
}
}

out:
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
							<td  class="listr" name="pidstatus" id="pidstatus"><img id="pidstatusimg" src="status_disabled.png" border="0" /></td>
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
			html_text($confconv, gettext("Current Status"),"The latest version on GitHub is: " . $git_ver . "<br /><br />Your version is: " . MINIDLNA_VERSION ); 
			?> 
			<tr>
			
			<td width="22%" valign="top" class="vncell">Update your installation&nbsp;</td>
			<td width="78%" class="vtable">
			<?=gettext("Click below to download and install the latest version.");?><br />
				<div id="submit_x">
					<input id="minidlna_update" name="minidlna_update" type="submit" class="formbtn" value="Update" onClick="return confirm('<?="Minidlna will be stopped, and the latest version will be downloaded. Are you sure you want to continue?";?>');" /><br />
				</div>
				<input name="txtCommand" type="hidden" value="<?="sh /tmp/install.sh {$config['minidlna']['homefolder']}";?>" />
			</td>
			

	</table><?php include("formend.inc");?>
</form>
<?php endif;?>
	</table>
	


<?php include("fend.inc");?>
