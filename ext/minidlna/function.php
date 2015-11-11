<?php
/*
	function.php
*/

// minidlna folder box
 // Added drop-down for folder description
 // Some as html_folderbox
 class HTMLFolderBox1 extends HTMLBaseControl {
	var $_path = "";

	function __construct($ctrlname, $title, $value, $description = "") {
		parent::__construct($ctrlname, $title, $value, $description);
	}

	function GetPath() {
		return $this->_path;
	}

	function SetPath($path) {
		$this->_path = $path;
	}

	function RenderCtrl() {
		$ctrlname = $this->GetCtrlName();
		$value = $this->GetValue();
		$path = $this->GetPath();

		echo "    <script type='text/javascript'>\n";
		echo "    //<![CDATA[\n";
		echo "    function onchange_{$ctrlname}() {\n";
		echo "      var value1 = document.getElementById('{$ctrlname}');\n";
		echo "      if (value1.value.charAt(0) != '/') {\n";
		echo "      document.getElementById('{$ctrlname}data').value = value1.value.substring(2,(value1.value.length));\n";
		echo "      document.getElementById('{$ctrlname}filetype').value = value1.value.charAt(0);\n";
		echo "        }else{\n";
		echo "      document.getElementById('{$ctrlname}data').value = document.getElementById('{$ctrlname}').value;\n";
		echo "      document.getElementById('{$ctrlname}filetype').value = '';\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_add_{$ctrlname}() {\n";
		echo "      var value1 = document.getElementById('{$ctrlname}data').value;\n";
		echo "      var valuetype = document.getElementById('{$ctrlname}filetype').value;\n";
		echo "      if (valuetype != '') {\n";
		echo "      var valuetype = valuetype + ',';\n";
		echo "          }\n";
		echo "      var value = valuetype +  value1;\n";
		echo "      if (value != '') {\n";
		echo "        var found = false;\n";
		echo "        var element = document.getElementById('{$ctrlname}');\n";
		echo "        for (var i = 0; i < element.length; i++) {\n";
		echo "          if (element.options[i].text == value) {\n";
		echo "            found = true;\n";
		echo "            break;\n";
		echo "          }\n";
		echo "        }\n";
		echo "        if (found != true) {\n";
		echo "          element.options[element.length] = new Option(value, value, false, true);\n";
		echo "          document.getElementById('{$ctrlname}data').value = '';\n";
		echo "        }\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_delete_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      if (element.value != '') {\n";
		echo "        var msg = confirm('".htmlspecialchars(gettext("Do you really want to remove the selected item from the list?"), ENT_QUOTES)."');\n";
		echo "        if (msg == true) {\n";
		echo "          element.options[element.selectedIndex] = null;\n";
		echo "          document.getElementById('{$ctrlname}data').value = '';\n";
		echo "        }\n";
		echo "      } else {\n";
		echo "        alert('".htmlspecialchars(gettext("Select item to remove from the list"), ENT_QUOTES)."');\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onclick_change_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      if (element.value != '') {\n";
		echo "        var value1 = document.getElementById('{$ctrlname}data').value;\n";
		echo "      var valuetype = document.getElementById('{$ctrlname}filetype').value;\n";
		echo "      if (valuetype != '') {\n";
		echo "      var valuetype = valuetype + ',';\n";
		echo "          }\n";
		echo "      var value = valuetype +  value1;\n";
		echo "        element.options[element.selectedIndex].text = value;\n";
		echo "        element.options[element.selectedIndex].value = value;\n";
		echo "      }\n";
		echo "    }\n";
		echo "    function onsubmit_{$ctrlname}() {\n";
		echo "      var element = document.getElementById('{$ctrlname}');\n";
		echo "      for (var i = 0; i < element.length; i++) {\n";
		echo "        if (element.options[i].value != '')\n";
		echo "          element.options[i].selected = true;\n";
		echo "      }\n";
		echo "    }\n";
		echo "    //]]>\n";
		echo "    </script>\n";
		echo "    <select name='{$ctrlname}[]' class='formfld' id='{$ctrlname}' multiple='multiple'  style='width: 350px' onchange='onchange_{$ctrlname}()'>\n";
		foreach ($value as $valuek => $valuev) {
			echo "      <option value='{$valuev}' {$optparam}>{$valuev}</option>\n";
		}
		echo "    </select>\n";
		echo "    <input name='{$ctrlname}deletebtn' type='button' class='formbtn' id='{$ctrlname}deletebtn' value='".htmlspecialchars(gettext("Delete"), ENT_QUOTES)."' onclick='onclick_delete_{$ctrlname}()' /><br />\n";
		echo "    <select name='{$ctrlname}filetype' class='formfld' id='{$ctrlname}filetype' > ";
		echo "  		<option value='P'>Picturies</option>";
		echo "			<option value='A'>Audio</option>";
		echo "			<option value='V'>Video</option>";
		echo "			<option value=''>All</option> </select>";

		echo "    <input name='{$ctrlname}data' type='text' class='formfld' id='{$ctrlname}data' size='60' value='' />\n";
		echo "    <input name='{$ctrlname}browsebtn' type='button' class='formbtn' id='{$ctrlname}browsebtn' onclick='ifield = form.{$ctrlname}data; filechooser = window.open(\"filechooser.php?p=\"+encodeURIComponent(ifield.value)+\"&amp;sd={$path}\", \"filechooser\", \"scrollbars=yes,toolbar=no,menubar=no,statusbar=no,width=550,height=300\"); filechooser.ifield = ifield; window.ifield = ifield;' value='...' />\n";
		echo "    <input name='{$ctrlname}addbtn' type='button' class='formbtn' id='{$ctrlname}addbtn' value='".htmlspecialchars(gettext("Add"), ENT_QUOTES)."' onclick='onclick_add_{$ctrlname}()' />\n";
		echo "    <input name='{$ctrlname}changebtn' type='button' class='formbtn' id='{$ctrlname}changebtn' value='".htmlspecialchars(gettext("Change"), ENT_QUOTES)."' onclick='onclick_change_{$ctrlname}()' />\n";
	}
}

function html_minidlnabox($ctrlname, $title, $value, $desc, $path, $required = false, $readonly = false) {
	$ctrl = new HTMLFolderBox1($ctrlname, $title, $value, $desc);
	$ctrl->SetRequired($required);
	$ctrl->SetReadOnly($readonly);
	$ctrl->SetPath($path);
	$ctrl->Render();
}
function system_get_upnpinfo() {
	global $config;
	$tabledata = array();
	$tabledata['server'] = "minidlna";
	$tabledata['version'] = exec ("minidlnad -V | awk '{print$2}'");
			$upnpip = get_ipaddr($config['minidlna']['if']);
			if (is_file("/var/run/minidlna/upnp-av.scan") ) { $tabledata['pidstatus'] = 1; } else {
			$presurl = "http://".$upnpip.":".$config['minidlna']['port'];
			$file_headers = @get_headers($presurl);
			if($file_headers[0] == 'HTTP/1.1 404 Not Found') {
				$tabledata['pidstatus'] = false;
				} else {
					 $tabledata['pidstatus'] = exec ("ps ax | grep minidlna | grep -v grep | awk '{print$1}'");
					} 
				}
	return $tabledata;
}
?>
