<?php
/* 
extensions_minidlna_log.php
*/
require("auth.inc");
require("guiconfig.inc");
// require("diag_log.inc");
require_once("globals.inc");
require_once("rc.inc");

$loginfo = array(
	array (
	
		"visible" => TRUE,
		"desc" => gettext("Minidlna"),
		"logfile" => $config['minidlna']['homefolder']."minidlna.log",
		"filename" => "minidlna.log",
		"type" => "plain",
		"pattern" => "/^(\[\S+\s+\S+\]+\s)(minidlna.c\:\d+:)(.+)/",
		"columns" => array(
			array("title" => gettext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gettext("Who"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gettext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gettext("minissdp"),
		"logfile" => $config['minidlna']['homefolder']."minidlna.log",
		"filename" => "minidlna.log",
		"type" => "plain",
		"pattern" => "/^(\[\S+\s+\S+\]+\s)(minissdp.c\:\d+:)(.+)/",
		"columns" => array(
			array("title" => gettext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gettext("Who"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gettext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gettext("scanner"),
		"logfile" => $config['minidlna']['homefolder']."minidlna.log",
		"filename" => "minidlna.log",
		"type" => "plain",
		"pattern" => "/^(\[\S+\s+\S+\]+\s)([scanner]\S+\:\s)(.+)/",
		"columns" => array(
			array("title" => gettext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gettext("Who"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gettext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		)),
	array(
		"visible" => TRUE,
		"desc" => gettext("+ others"),
		"logfile" => $config['minidlna']['homefolder']."minidlna.log",
		"filename" => "minidlna.log",
		"type" => "plain",
		"pattern" => "/^(\[\S+\s+\S+\]+\s)([upnp]\S+\:\s)(.+)/",
		"columns" => array(
			array("title" => gettext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gettext("Who"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gettext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		))	
);

$pgtitle = array(gettext("Minidlna "), gettext(" Log"));
if (isset($_GET['log']))
	$log = $_GET['log'];
if (isset($_POST['log']))
	$log = $_POST['log'];
if (empty($log))
	$log = 0;
	
if (isset($_POST['clear']) && $_POST['clear']) {
	unlink($config['minidlna']['homefolder']."minidlna.log");
	$db=fopen($config['minidlna']['homefolder']."minidlna.log", w);
	fclose (db);
	chmod($config['minidlna']['homefolder']."minidlna.log",0666);
	header("Location: extensions_minidlna_log.php");
	exit;
}

if (isset($_POST['download']) && $_POST['download']) {
	log_download($loginfo);
	exit;
}

if (isset($_POST['refresh']) && $_POST['refresh']) {
	header("Location: extensions_minidlna_log.php");
	exit;
}

function log_get_contents($logfile, $type) {


	$content = array();

	$param = (isset($config['syslogd']['reverse']) ? "-r " : "");
	$param .= "-n 200";

	switch ($type) {
		case "clog":
			exec("/usr/sbin/clog {$logfile} | /usr/bin/tail {$param}", $content);
			break;

		case "plain":
			exec("/bin/cat {$logfile} | /usr/bin/tail {$param}", $content);
	}

	return $content;
}

function log_display($loginfo) {
	if (!is_array($loginfo))
		return;

	// Create table header
	echo "<tr>";
	foreach ($loginfo['columns'] as $columnk => $columnv) {
		echo "<td {$columnv['param']} class='" . (($columnk == 0) ? "listhdrlr" : "listhdrr") . "'>".htmlspecialchars($columnv['title'])."</td>\n";
	}
	echo "</tr>";

	// Get log file content
	$content = log_get_contents($loginfo['logfile'], $loginfo['type']);
	if (empty($content))
		return;

	// Create table data
	foreach ($content as $contentv) {
		// Skip invalid pattern matches
		$result = preg_match($loginfo['pattern'], $contentv, $matches);
		if ((FALSE === $result) || (0 == $result))
			continue;

		// Skip empty lines
		if (count($loginfo['columns']) == 1 && empty($matches[1]))
			continue;

		echo "<tr valign=\"top\">\n";
		foreach ($loginfo['columns'] as $columnk => $columnv) {
			echo "<td {$columnv['param']} class='{$columnv['class']}'>" . htmlspecialchars($matches[$columnv['pmid']]) . "</td>\n";
		}
		echo "</tr>\n";
	}
}





?>
<?php include("fbegin.inc");?>
<script type="text/javascript">
<!--
function log_change() {
	// Reload page
	window.document.location.href = 'extensions_minidlna_log.php?log=' + document.iform.log.value;
}
//-->
</script>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="extensions_minidlna.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Main");?></span></a></li>
				
				<li class="tabact"><a href="extensions_minidlna_log.php"><span><?=gettext("Log");?></span></a></li>
			</ul>
		</td>
	</tr>	
	<tr>
    <td class="tabcont">
    	<form action="extensions_minidlna_log.php" method="post" name="iform" id="iform">
				<select id="log" class="formfld" onchange="log_change()" name="log">
					<?php foreach($loginfo as $loginfok => $loginfov):?>
					<?php if (FALSE === $loginfov['visible']) continue;?>
					<option value="<?=$loginfok;?>" <?php if ($loginfok == $log) echo "selected=\"selected\"";?>><?=htmlspecialchars($loginfov['desc']);?></option>
					<?php endforeach;?>
				<input name="clear" type="submit" class="formbtn" value="<?=gettext("Clear");?>" />
				
				<input name="refresh" type="submit" class="formbtn" value="<?=gettext("Refresh");?>" />
				<br /><br />
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				  <?php log_display($loginfo[$log]);?>
				</table>
				<?php include("formend.inc");?>
			</form>
		</td>
  </tr>
</table>

<?php include("fend.inc");?>
