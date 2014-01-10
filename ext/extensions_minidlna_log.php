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
	
		"visible" => TRUE,
		"desc" => gettext("UPnP"),
		"logfile" => $config['minidlna']['homefolder']."minidlna.log",
		"filename" => "minidlna.log",
		"type" => "plain",
		"pattern" => "/^(\[\S+\s+\S+\]+\s)(\S+\:\s)(.+)/",
		"columns" => array(
			array("title" => gettext("Date & Time"), "class" => "listlr", "param" => "nowrap=\"nowrap\"", "pmid" => 1),
			array("title" => gettext("Who"), "class" => "listr", "param" => "nowrap=\"nowrap\"", "pmid" => 2),
			array("title" => gettext("Event"), "class" => "listr", "param" => "", "pmid" => 3)
		))
;

$pgtitle = array(gettext("Minidlna "), gettext(" Log"));

if (isset($_POST['clear']) && $_POST['clear']) {
	log_clear($loginfo);
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

function log_clear($loginfo) {
	if (!is_array($loginfo))
		return;

	switch ($loginfo['type']) {
		case "clog":
			exec("/usr/sbin/clog -i -s {$loginfo['size']} {$loginfo['logfile']}");
			break;

		case "plain":
			exec("/bin/cat /dev/null > {$loginfo['logfile']}");
	}
}

function log_download($loginfo) {
	if (!is_array($loginfo))
		return;

	$fs = get_filesize($loginfo['logfile']);

	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename={$loginfo['filename']}");
	header("Content-Length: {$fs}");
	header("Pragma: hack");

	switch ($loginfo['type']) {
		case "clog":
			exec("/usr/sbin/clog {$loginfo['logfile']}", $content);
			echo implode("\n", $content);
			break;

		case "plain":
			readfile($loginfo['logfile']);
	}
}

?>
<?php include("fbegin.inc");?>

<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
		<td class="tabnavtbl">
			<ul id="tabnav">
				<li class="tabinact"><a href="extensions_minidlna.php" title="<?=gettext("Reload page");?>"><span><?=gettext("Main");?></span></a></li>
				<li class="tabinact"><a href="extensions_minidlna_rescan.php"><span><?=gettext("Rescan");?></span></a></li>
				<li class="tabact"><a href="extensions_minidlna_log.php"><span><?=gettext("Log");?></span></a></li>
			</ul>
		</td>
	</tr>	
	<tr>
    <td class="tabcont">
    	<form action="extensions_minidlna_log.php" method="post" name="iform" id="iform">
				<input name="clear" type="submit" class="formbtn" value="<?=gettext("Clear");?>" />
				<input name="download" type="submit" class="formbtn" value="<?=gettext("Download");?>" />
				<input name="refresh" type="submit" class="formbtn" value="<?=gettext("Refresh");?>" />
				<br /><br />
				<table width="100%" border="0" cellpadding="0" cellspacing="0">
				  <?php log_display($loginfo);?>
				</table>
				<?php include("formend.inc");?>
			</form>
		</td>
  </tr>
</table>

<?php include("fend.inc");?>
