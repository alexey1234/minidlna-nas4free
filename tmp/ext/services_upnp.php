<?php
/*
	services_upnp.php
	
*/
require("auth.inc");
require("guiconfig.inc");
require("services.inc");
require_once("ext/minidlna/function.php");

if (!isset($config['upnp']) || !is_array($config['upnp']))
	$config['upnp'] = array();

if (!isset($config['upnp']['content']) || !is_array($config['upnp']['content']))
	$config['upnp']['content'] = array();

if ( count($config['minidlna']) < 2 ) /*not configured*/{ header("Location: extensions_minidlna.php"); exit;}

sort($config['upnp']['content']);


$pconfig ['server_t'] = $config ['upnp']['server_t'];
$pconfig['name'] = !empty($config['upnp']['name']) ? $config['upnp']['name'] :  $config['system']['hostname'];
$pconfig['if'] = !empty($config['upnp']['if']) ? $config['upnp']['if'] : "";
if (empty ($config['upnp']['port'] )) {
	if ($config ['upnp']['server_t'] == "fuppes" )  $pconfig['port'] = 49152; else  $pconfig['port'] = 8200;
	} else $pconfig['port'] = $config['upnp']['port'];


$pconfig['home'] = $config['upnp']['home'] ;
$pconfig['profile'] = $config['upnp']['profile'];
$pconfig['deviceip'] = !empty($config['upnp']['deviceip']) ? $config['upnp']['deviceip'] : "";
$pconfig['transcoding'] = isset($config['upnp']['transcoding']) ? true : false;
$pconfig['tempdir'] = !empty($config['upnp']['tempdir']) ? $config['upnp']['tempdir'] : "";
$pconfig['content'] = $config['upnp']['content'];

$pconfig['loglevel'] = !empty($config['upnp']['loglevel']) ? $config['upnp']['loglevel'] : "warn";
$pconfig['notify_int'] = !empty($config['upnp']['notify_int']) ? $config['upnp']['notify_int'] : "600";
$pconfig['strict'] = isset($config['upnp']['strict']) ? true : false;
$pconfig['tivo'] = isset($config['upnp']['tivo']) ? true : false;
$pconfig['extconfig'] = isset( $config['upnp']['extconfig']) ? "checked":false;
$pconfig['extconfigpath'] = $config['upnp']['extconfigpath'];

if ($_POST) {
	 if (isset($_POST['Submit']) && $_POST['Submit'] === "Save") {
		
		unset($input_errors);
		$post['server_t'] = $_POST['server_t'];
		$post['name'] = $_POST['name'];
		$post['if'] = $_POST['if'];
		$post['port'] = $_POST['port'];
		$post['content'] =  sanitize_filechoicer ($_POST['content'],14);
		if ($_POST['server_t'] == "fuppes") { $post['home'] =   sanitize_filechoicer ( $_POST['home'] ,14);} else { $post['home'] = $config['minidlna']['homefolder']."db"; }
		if ($_POST['server_t'] == "fuppes")  { $post['profile'] =  $_POST['profile'];} else { unset($post['profile']); }
		if ($_POST['server_t'] == "fuppes") { $post['deviceip'] =   $_POST['deviceip'] ;} else { unset($post['deviceip']); }
		if ($_POST['server_t'] == "fuppes") { if ( isset($_POST['transcoding'])) { $post['transcoding'] = true; } else { unset($post['transcoding']); } }
		if ($_POST['server_t'] == "fuppes") { $post['tempdir'] =  sanitize_filechoicer ( $_POST['tempdir'] ,14);} else { unset($post['tempdir']); }
		if ($_POST['server_t'] == "minidlna") {
			$post['notify_int'] = $_POST['notify_int'];
			$post['strict'] = isset($_POST['strict']) ? true : false;
			$post['tivo'] =  isset($_POST['tivo']) ? true : false;
			$post['loglevel'] =  $_POST['loglevel'];
			$post['extconfig'] = isset ($_POST['extconfig']) ? "checked" : false;
			$post['extconfigpath'] =  $_POST['extconfigpath'];
		} else {
			unset($post['notify_int']);
			unset ($post['strict']);
			unset ($post['tivo']);
			unset ($post['loglevel']);
			unset($post['extconfig']);
			unset($post['extconfigpath']);
		}
				//print_r ($post);		
	// Input validation.
	// Need add repair after filechoicer and use $pconfig instead $_POST
	 if ($post['server_t'] != "off") {
		$reqdfields = explode(" ", "name if port content home");
		$reqdfieldsn = array(gettext("Name"), gettext("Interface"), gettext("Port"), gettext("Media library"), gettext("Database directory"));
		$reqdfieldst = explode(" ", "string string port array string");

		if ("Terratec_Noxon_iRadio" === $post['profile']) {
			$reqdfields = array_merge($reqdfields, array("deviceip"));
			$reqdfieldsn = array_merge($reqdfieldsn, array(gettext("Device IP")));
			$reqdfieldst = array_merge($reqdfieldst, array("ipaddr"));
		}

		if (isset($post['transcoding'])) {
			$reqdfields = array_merge($reqdfields, array("tempdir"));
			$reqdfieldsn = array_merge($reqdfieldsn, array(gettext("Temporary directory")));
			$reqdfieldst = array_merge($reqdfieldst, array("string"));
		}

		do_input_validation($post, $reqdfields, $reqdfieldsn, $input_errors);
		do_input_validation_type($post, $reqdfields, $reqdfieldsn, $reqdfieldst, $input_errors);

		// Check if port is already used.
		if (services_is_port_used($post['port'], "upnp"))
			$input_errors[] = sprintf(gettext("The attribute 'Port': port '%ld' is already taken by another service."), $_POST['port']);

		// Check port range.
		if ($post['port'] && ((1024 > $post['port']) || (65535 < $post['port']))) {
			$input_errors[] = sprintf(gettext("The attribute '%s': use a port in the range from %d to %d."), gettext("Port"), 1025, 65535);
		}
	 }
		
	// Compare config and post for find differences, need to execute rebuild database
	//MODE NEW - when server  must make first run after of ( also if server switched )
	// Modemodified - this condition in case work some server, but may be need rebuild database
	// mode dirty - just reload server without rebuilt database.	
		$currentconfig = $config['upnp'];
		switch ($post['server_t']) { //will 
			case "fuppes":  // will fuppes
					
					switch ($currentconfig['server_t']) {
						case "fuppes": // was before post.  In this case server continue work as fuppes
							$a_content = $post['content'];
							$b_content = $currentconfig['content'];
							sort ($a_content);
							sort ($b_content);
							$check_differences = array_merge (  array_diff_assoc ( $a_content ,$b_content ), array_diff_assoc ( $b_content ,  $a_content));
							if (count ($check_differences) > 0 ) { 	updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['upnp']['home']);
								} else {
											updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, "");
								}
							break ;
						case "minidlna":
						//minidlna --> fuppes.  Need stop minidlna, check condition(database and pathes for fuppes and continue 
						
							if (0 == rc_is_service_running ("minidlna")) { rc_stop_service( "minidlna" ); }
							rc_update_rcconf("minidlna", "disable");
							if (is_file($config['upnp']['home']."/fuppes.db")) {
								//coldstart
										updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['upnp']['home']);
								} else {
										updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, "start");
									}
							break ;
						case "off": 
							//fuppes start from stop state,,  Check conditions (database and pathes)
							if (is_file($config['upnp']['home']."/fuppes.db")) {
										//coldstart
									updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['upnp']['home']); 
								} else { 
									updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, "start"); 
								}				
							break ;
					}
							
				break;
			case "minidlna": //will
					
				switch ($currentconfig['server_t']) {
							case "fuppes":  //was
								/// fuppes ---> minidlna/ Stop fuppes, check conditions and start minidlna.
									if (0 == rc_is_service_running ("fuppes")) {rc_stop_service( "fuppes" ); }
									rc_update_rcconf("fuppes", "disable");
									if (!is_file($config['minidlna']['homefolder']."db/files.db")) {
								//coldstart
												updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, "change");
										} else {
											updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['minidlna']['homefolder']."/db");
										}
								break ;
							case "minidlna":
						// minidlna continue work						
									
								switch ($post['extconfig']) {
											case true:
													switch ($currentconfig['extconfig']) {
															case true:
						// current and previous configurations use external config
						// better way allways ask about rebuilt
																$a_path = get_ext_db_path ($post['extconfigpath']);
																if ($a_path['error'] === 0) {$path_new = $a_path['value'];} else { $input_errors[]=$a_path['error']; break 4;	}
																updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $path_new);
																break 4;
															case false:  	// from xml ---> external
																	$a_path = get_ext_db_path ($post['extconfigpath']);
																	if ($a_path['error'] === 0) {$path_new = $a_path['value'];} else { $input_errors[]=$a_path['error']; break 4;	}
																	if ($path_new == $config['minidlna']['homefolder']."db") {
																				updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $path_new);
																		} else { 
																			if(! file_exists($path_new."/files.db")) { updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, $path_new."/files.db"); } // new database
																				else { 	updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $path_new); }
																				}		
																break 4;
														}
												break;
											case false :
													
												switch ($currentconfig['extconfig']) {
															case true:  		//external --> xml
																$a_path = get_ext_db_path ($currentconfig['extconfigpath']);
																if ($a_path['error'] === 0) {$path_prev  = $a_path['value'];} else {$input_errors[]=$a_path['error'];}
																if ($path_prev == $config['minidlna']['homefolder']."/db") {
																					updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['minidlna']['homefolder']."db");
																	} else {
																			if(! file_exists(  $config['minidlna']['homefolder']."db/files.db" )) {
																					updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, "start");
																				} else { updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['minidlna']['homefolder']."db"); }
							
																			}
																break 2;
															case false:  			// xml === xml,   begin check mediafolders
																$a_content = $post['content'];
																$b_content = $currentconfig['content'];
																sort ($a_content);
																sort ($b_content);
																$check_differences = array_merge (  array_diff_assoc ( $a_content ,$b_content ), array_diff_assoc ( $b_content ,  $a_content));
																if (count ($check_differences) > 0 ) {
																				updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['minidlna']['homefolder']."db");
																		} else {
																				updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, "leave");
																		}											
					
																break;
													}
												break;
										}
										
							break ;
					case "off":
								//minidlna start from stop state
									// check database
							if (!is_file($config['minidlna']['homefolder']."db/files.db")) {
									//coldstart
								updatenotify_set("upnp", UPDATENOTIFY_MODE_NEW, "start");
							} else {
								updatenotify_set("upnp", UPDATENOTIFY_MODE_MODIFIED, $config['minidlna']['homefolder']."/db");
						}
						break ;
					}
					
				break;
			case "off":
					
				switch ($currentconfig['server_t']) {
							case "fuppes":
									if (0 == rc_is_service_running ("fuppes")) { rc_stop_service( "fuppes" ); }
									rc_update_rcconf("fuppes", "disable");
						
								break;						
							case "minidlna": // minidlna will disabled and shutting
									if ( rc_is_service_running( "minidlna" ) == 0 ) {	rc_stop_service( "minidlna" );	}
									rc_update_rcconf("minidlna", "disable");
								break ;
							case "off":  // Just reload page
									header("Location: services_upnp.php");
									exit;
								break ;
							
					}
				break ;
					
		}
///----------------------------------------------------- Stop swiches --------------////		
		if (empty($input_errors)) { //write config to xml
		
				switch ( $post['server_t'] ) {
					case "fuppes":
							$config['upnp'] = $post;
							$config['upnp']['enable'] = true;
							$config['minidlna']['enable'] = false;
						break;
					case "minidlna":
							$config['upnp'] = $post;
							$config['minidlna']['enable'] = true;
							$config['upnp']['enable'] = false;
						break;
					case "off":
							$config['upnp']['enable'] = false;
							$config['minidlna']['enable'] = false;
							$config['upnp']['server_t'] ="off";
						break;
				}

			write_config();
			header("Location: services_upnp.php");
			exit;
		
		} 
	
	}/// END _POST Save
	if (isset($_POST['apply']) && $_POST['apply'] === "Apply changes") {
		// standart input
			$retval =0;
			if (!file_exists($d_sysrebootreqd_path)) {
					$retval != upnp_stopall_procedure() ;
					$servicename = $config['upnp']['server_t'];
					config_lock();
					$retval |=  rc_update_service ( $servicename );
					$retval |= rc_update_service("mdnsresponder");
					config_unlock();
					$savemsg = get_std_save_message($retval);
					if ($retval == 0) { updatenotify_delete("upnp"); }
				}
	}
	if (isset($_POST['rebuild']) && $_POST['rebuild'] === "Rebuild") {
		// standart input
			$mode = updatenotify_get("upnp");
			$retval = 0;
			if (!file_exists($d_sysrebootreqd_path)) {
					If ($config['upnp']['server_t'] == "fuppes") {  $db_name = "fuppes" ;} else { $db_name = "files"; }
			 		$retval != upnp_stopall_procedure();
			 		$path2db = $mode[0]['data'];
					$servicename = $config['upnp']['server_t'];
					sleep (1);
					$timenow = time();
					if (is_file( $config['upnp']['home']."/".$db_name . ".db" )) {
							$retval1 = rename ( $path2db ."/" . $db_name. ".db", $path2db . "/" . $timenow . $db_name . ".bak" );
							if($retval1) $retval = $retval; else $retval = 2;
							exec ("gzip -9 {$path2db}/{$timenow}{$db_name}.bak"); 
						} 
					$dir = opendir ($config['upnp']['home']);
					$i = 0;
					while (false !== ($file = readdir($dir))) {
						if (strpos($file, '.gz',1) ) {
								$i++;
							}
						}
					if ($i > 10) { $warning_mess = " You have more then 10 ( " . $i ." ) backuped files.  You need all backups ?"; }
								
					config_lock();
					$retval |=  rc_update_service( $servicename );
					$retval |= rc_update_service("mdnsresponder");
					config_unlock();
					$savemsg = get_std_save_message($retval);
					if ($retval == 0) { updatenotify_delete("upnp"); }
			}
	}
	if (isset($_POST['leave']) && $_POST['leave'] === "Leave_current") {
	$mode = updatenotify_get("upnp");
		if (!file_exists($d_sysrebootreqd_path)) {
			$retval = 0;
			$retval |= upnp_stopall_procedure() ;
			$servicename = $config['upnp']['server_t'];
			config_lock();
			$retval |=  rc_update_service ( $servicename );
			$retval |= rc_update_service("mdnsresponder");
			config_unlock();
			$savemsg = get_std_save_message($retval);
			if ($retval == 0) { updatenotify_delete("upnp"); }
		}
	}
}  // END POST
// Begin webface
$pgtitle = array(gettext("Services"),gettext("DLNA/UPnP"));
$a_interface = get_interface_list();

// Use first interface as default if it is not set.
if (empty($pconfig['if']) && is_array($a_interface))
	$pconfig['if'] = key($a_interface);
?>
<?php include("fbegin.inc");?>

<script type="text/javascript">//<![CDATA[
$(document).ready(function(){
	var gui = new GUI;
	$('#server_t').change(function(){
		switch ($('#server_t').val()) {
		case "fuppes":
			$('#name_tr').show();
			$('#extconfig_tr').hide();
			$('#extconfigpath_tr').hide();
			$('#if_tr').show();
			$('#port_tr').show();
			$('#home_tr').show();
			$('#content_tr').show();
			$('#profile_tr').show();
			$('#profile').prop("disabled", false);
			
			$('#transcoding_tr').show();
			$('#transcoding').prop("disabled", false);
			$('#tempdir_tr').hide();
			$('#strict_tr').hide();
			$('#tivo_tr').hide();
			$('#notify_int_tr').hide();
			$('#loglevel_tr').hide();
			$('#url_tr').show();
			break;
		
		case "minidlna":
			$('#name_tr').show();
			$('#extconfig_tr').show();
			$('#extconfigpath_tr').hide();
			$('#if_tr').show();
			$('#port_tr').show();
			$('#home_tr').hide();			
			$('#content_tr').show();
			$('#profile_tr').hide();
			$('#profile').prop("disabled", true);
			$('#deviceip_tr').hide();
			$('#transcoding_tr').hide();
			$('#transcoding').prop("disabled", true);
			$('#tempdir_tr').hide();
			$('#strict_tr').show();
			$('#tivo_tr').show();
			$('#notify_int_tr').show();
			$('#loglevel_tr').show();
			$('#url_tr').show();
			break;
			
		case "off": 
			$('#name_tr').hide();			
			$('#extconfig_tr').hide();
			$('#extconfigpath_tr').hide();
			$('#if_tr').hide();
			$('#port_tr').hide();
			$('#home_tr').hide();
			$('#content_tr').hide();
			$('#profile_tr').hide();
			$('#deviceip_tr').hide();
			$('#transcoding_tr').hide();
			$('#tempdir_tr').hide();
			$('#strict_tr').hide();
			$('#tivo_tr').hide();
			$('#notify_int_tr').hide();
			$('#loglevel_tr').hide();
			$('#url_tr').hide();
			break;
		}
	});
	
	
	$('#extconfig').change(function() {
		switch ($('#extconfig').is(':checked')) {
		case true :
			$('#extconfigpath_tr').show(1500);
			$('#name_tr').hide(1200);
			$('#if_tr').hide(1100);
			$('#port_tr').hide(1000);
			$('#content_tr').hide(900);
			$('#strict_tr').hide(800);
			$('#tivo_tr').hide(700);
			$('#notify_int_tr').hide(600);
			$('#loglevel_tr').hide(500);
			break;
			
		case false :	
			$('#extconfigpath_tr').hide(500);
			$('#name_tr').show(1500);
			$('#if_tr').show(1500);
			$('#port_tr').show(1500);
			$('#content_tr').show(1500);
			$('#strict_tr').show(1500);
			$('#tivo_tr').show(1500);
			$('#notify_int_tr').show(1500);
			$('#loglevel_tr').show(1500);
			break;
            } 
        });
	$('#extconfig').change();
	$('#transcoding').live('click',function() {
		if ($('#transcoding').is(':checked')) { 
			$('#tempdir_tr').show();
		    } else {
			$('#tempdir_tr').hide();
		 } 
        });
        $('#profile').change(function(){
		
		switch ($('#profile :selected').val()) {
			case "Terratec_Noxon_iRadio":
			 
			$('#deviceip_tr').show();
			break;
			default:
			$('#deviceip_tr').hide();
			break;
		 } 
	});	
	$('#profile').change();
	$('#server_t').change();
});
//]]>
</script>
<form action="services_upnp.php" method="post" name="iform" id="iform">
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td class="tabcont">
				<?php if (!empty($input_errors)) print_input_errors($input_errors); ?>
				<?php if (!empty($savemsg)) print_info_box($savemsg); ?>
				<?php if (!empty($warning_mess)) print_warning_box($warning_mess); ?>
				<?php if (updatenotify_exists_mode("upnp", 0 )) print_config_change_box();?>
				<?php if (updatenotify_exists_mode("upnp", 1 )) print_upnp_dbchange_box();?>
			<table width="100%" border="0" cellpadding="6" cellspacing="0">
				<?php html_titleline(gettext("DLNA/UPnP Media Server")); ?>
				<?php html_combobox("server_t", gettext("Server type"), $pconfig['server_t'], array("fuppes" => gettext("UPnP/Fuppes"), "minidlna" => "MiniDLNA", off => "Off"), gettext("Compliant server to be used."), true, false, "");?>
				<?php html_checkbox ("extconfig", gettext("Enable External config"), !empty($pconfig['extconfig']) ? true : false, gettext(""), gettext("Use external config for minidlna"), false, "" );?>
				<?php html_filechooser("extconfigpath", gettext("Path"), $pconfig['extconfigpath'], sprintf(gettext("File path (e.g. / mnt / sharename / foler / with / config / minidlna.conf) .") ), $config['minidlna']['homefolder'], isset($pconfig['extconfig']) ? true : false);?>
				<?php html_inputbox("name", gettext("Name"), $pconfig['name'], gettext("Give your media library a friendly name."), true, 35);?>
				<tr id='if_tr'>
					<td width="22%" valign="top" class="vncellreq"><?=gettext("Interface selection");?></td>
					<td width="78%" class="vtable">
					<select name="if" class="formfld" id="xif">
						<?php foreach($a_interface as $if => $ifinfo):?>
							<?php $ifinfo = get_interface_info($if); if (("up" == $ifinfo['status']) || ("associated" == $ifinfo['status'])):?>
							<option value="<?=$if;?>"<?php if ($if == $pconfig['if']) echo "selected=\"selected\"";?>><?=$if?></option>
							<?php endif;?>
						<?php endforeach;?>
					</select>
					<br /><?=gettext("Select which interface to use. (only selectable if your server has more than one)");?>
					</td>
				</tr>
				<?php  if($pconfig['server_t'] == "fuppes") $defaultport = 49152; else  $defaultport = 8200;
					html_inputbox("port", gettext("Port"), $pconfig['port'], sprintf(gettext("Port to listen on. Only dynamic or private ports can be used (from %d through %d). Default port is %d."), 1025, 65535,$defaultport  ), true, 5);?>
				<?php html_filechooser("home", gettext("Database directory"), $pconfig['home'], gettext("Location where the database with media contents will be stored."), $g['media_path'], true, 67);?>
				<?php html_folderbox("content", gettext("Media library"), !empty($pconfig['content']) ? $pconfig['content'] : array(), gettext("Set the content location(s) to or from the media library."), $g['media_path'], true);?>
				<?php html_combobox("profile", gettext("Profile"), $pconfig['profile'], array("default" => gettext("Default"), "DLNA" => "DLNA", "Denon_AVR" => "DENON Network A/V Receiver", "PS3" => "Sony Playstation 3", "Telegent_TG100" => "Telegent TG100", "ZyXEL_DMA1000" => "ZyXEL DMA-1000", "Helios_X3000" => "Helios X3000", "DLink_DSM320" => "D-Link DSM320", "Microsoft_XBox360" => "Microsoft XBox 360", "Terratec_Noxon_iRadio" => "Terratec Noxon iRadio", "Yamaha_RXN600" => "Yamaha RX-N600", "Loewe_Connect" => "Loewe Connect"), gettext("Compliant profile to be used."), true, false, "");?>
				<?php html_inputbox("deviceip", gettext("Device IP"), $pconfig['deviceip'], gettext("The device's IP address."), true, 20);?>
				<?php html_checkbox("transcoding", gettext("Transcoding"), $pconfig['transcoding'], gettext("Enable transcoding."), "", false, "");?>
				<?php html_filechooser("tempdir", gettext("Temporary directory"), $pconfig['tempdir'], gettext("Temporary directory to store transcoded files."), $g['media_path'], true, 67);?>
				<?php html_checkbox ("strict", gettext("Strict DLNA"), !empty($pconfig['strict']) ? true : false, gettext(""), gettext("if checked will strictly adhere to DLNA standards which will allow server-side downscaling of very large JPEG images and may hurt JPEG serving performance on Sony DLNA products"), false);?>
				<?php html_checkbox ("tivo", gettext("Enable TiVo"), !empty($pconfig['tivo']) ? true : false, gettext(""), gettext("Enable digital video recorder (DVR) developed and marketed by TiVo, Inc"), false);?>
				<?php html_inputbox("notify_int", gettext("Discover interval "), $pconfig['notify_int'], gettext("how often MiniDLNA broadcasts its availability on the network; default is every 600 seconds"), false, 5);?>
				<?php html_combobox("loglevel", gettext("Log level"), $pconfig['loglevel'], array("off" => gettext("Off"), "fatal" => gettext("fatal"), "error" => gettext("error"), "warn" => gettext("warning"), "info" => gettext("info"),"debug" => gettext("debug")), "", false, false, "" );?>
					<?php html_separator();?>
				<?php $if = get_ifname($pconfig['if']);
						  $ipaddr = get_ipaddr($if);
						  if ($pconfig['server_t'] == "fuppes") {
							$url = htmlspecialchars("http://{$ipaddr}:{$pconfig['port']}");
							html_titleline(gettext("Administrative WebGUI"));
							$text = "<a href='{$url}' target='_blank'>{$url}</a>";
							html_text("url", gettext("URL"), $text);
						} elseif ($pconfig['server_t'] == "minidlna") { 
						html_titleline(gettext("Minidlna Webviewer"));
						$if = get_ifname($pconfig['if']);
						$ipaddr = get_ipaddr($if);
						
						$webport = $config['minidlna']['webport'];
						$url = htmlspecialchars("http://{$ipaddr}:{$webport}");
						if (isset($config['minidlna']['webview'])) {
							$text = "<a href='{$url}' target='_blank'>{$url}</a>";
							} else {
							$text= gettext("Webview not enabled");
							}
						html_text("url", gettext("URL"), $text);
					} else {}
					 ?>
				</table>
				<div id="submit">
					<input name="Submit" type="submit" class="formbtn" value="<?=gettext("Save");?>" onclick="onsubmit_content(); enable_change(true)" />
				</div>
			</td>
		</tr>
	</table>
	<?php include("formend.inc");?>
</form>


<?php include("fend.inc");?>