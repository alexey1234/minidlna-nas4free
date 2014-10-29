<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="de">
<head>
 <meta http-equiv="content-type" content="text/html; charset=utf-8" />
 <?php
  // Browserlanguage
  //error_reporting(5); 
  // * Verfuegbare Sprachen einlesen
  if($lang_dir = opendir('lng')) {
    while(false !== ($file = readdir($lang_dir))) {
      if(strpos($file,".lng")) $lang[] = $file;
    }
  }
  if(!isset($_SESSION['lang'])) {
    (in_array($_SERVER['HTTP_ACCEPT_LANGUAGE'].".lng",$lang)) ?  $_SESSION['lang'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'] : $_SESSION['lang'] = "de"; 
  } elseif (isset($_GET['lang'])) {
    (in_array($_GET['lang'].".lng",$lang)) ? $_SESSION['lang'] = $_GET['lang'] : $_SESSION['lang'] = "de";
  } 
  // Sprache einbinden
  require_once("lng/".$_SESSION['lang'].".lng");
  // Config einbinden
  require_once("inc/config.inc.php");
  // Klasse einbinden
  require_once("default/fkt.allgemein.inc.php");
  require_once("default/cls.error.inc.php");  
  // Klasse instanzieren
  try
  {
    $qPHP = new PHP_func();
    // DB Oeffnen
    $db   = new SQLite3($path_to_db);
  } catch(ExtException $e) {
    print $e->errorMessage();  
  }
  // Sortierung
  if(!isset($_SESSION['sortierung'])) $_SESSION['sortierung'] = "`OB`.`OBJECT_ID`";
  if(isset($_REQUEST['sortierung'])) $_SESSION['sortierung'] = $db->escapeString($_REQUEST['sortierung']);
  // Style
  if(!isset($_SESSION['style']))  $_SESSION['style'] = "default";
  if(isset($_REQUEST['style']))  $_SESSION['style'] = $db->escapeString($_REQUEST['style']);
  // Suche
  if(!isset($_POST['senden']) || isset($_POST['artist']) || isset($_POST['genre'])) {
    $_SESSION['suchen'] = "";
    $_SESSION['fclass'] = "";
  } else {
    $_SESSION['suchen'] = $db->escapeString($_POST['suchen']);
    $_SESSION['fclass'] = $db->escapeString($_POST['fclass']);
  }
  ?> 
 <meta name="language" content="<?php print $_SESSION['lang']; ?>" />
 <meta name="content-language" content="<?php print $_SESSION['lang']; ?>" />
 <title><?php print $mlng['head']; ?></title>
 <link rel="stylesheet" type="text/css" href="css/default.css" />
 <link rel="stylesheet" type="text/css" href="css/<?php print $_SESSION['style']; ?>/ui.dynatree.css" />
 <link rel="shortcut icon" href="img/minidlna.ico" />
 <!-- Dynatree-JScript: Quelle: http://code.google.com/p/dynatree/ -->
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.js" type="text/javascript"></script>
 <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.js" type="text/javascript"></script>
 <script src='script/jquery.cookie.js' type='text/javascript'></script>
 <script src='script/jquery.dynatree.js' type="text/javascript"></script>
 <script src='script/jquery.usertree.js' type="text/javascript"></script> 
</head>
<body onload="javascript:syncScroll();">
<table>
<tr>
 <td class="logo" rowspan="2"><a href="https://sourceforge.net/projects/minidlna-web/" target="_blank"><img src="img/minidlna.jpg" alt="minidlna" border="0px" /></a></td>
 <td colspan="3" class="lang">
 <?php
 print "<u>".$mlng['lang'].":</u> ";
 foreach($lang as $language) {
  $img = $qPHP->istr_replace(".lng",".jpg",$language);
  $lng = $qPHP->istr_replace(".lng","",$language);
  print "<a href='?lang=$lng'><img src='lng/$img' alt='$language' border='1px solid #000000' /></a> ";
 }
 ?>
 </td>
</tr>
<tr>
 <td class="title" colspan="3"><?php print $mlng['title']; ?></td>
</tr>
<tr>
 <td class="links" rowspan="3">
 <b><?php print $mlng['db']; ?></b><br />
 <div class="wrapper1">
  <div id="tree_sync" style="height: 20px;">
  <!-- Dieser Block dient der Scrollbar oberhalb des Trees -->
  </div>
 </div>
 <div class="wrapper2">
 <div id="tree">
  <?php 
  $sql = "SELECT `OBJECT_ID`,`PARENT_ID`,`NAME`,`DT`.`PATH` 
            FROM `OBJECTS` `OB`
       LEFT JOIN `DETAILS` `DT`
              ON `OB`.`DETAIL_ID`=`DT`.`ID`
           WHERE `OB`.`CLASS`='container.storageFolder' AND
                 `DT`.`PATH`<>'' AND `PARENT_ID` NOT LIKE '64%'
        ORDER BY ".$_SESSION['sortierung'];
  $res = $db->query($sql);
  $cnt = 0;
  while($zeilen = $res->fetchArray()) {
    // Aktuelle ID in Einzelteile zerlegen
    ($_SESSION['sortierung']=="`OBJECT_ID`") ? $oId = explode("$",$zeilen[0]) : $oId = explode("/",$zeilen[3]);
    // Pruefung: ID vom letzen Eintrag vs. aktuelle ID => Item oder SubItem
    if($cnt<count($oId)) { 
      print "<ul>";            
      $cnt = count($oId);
    } elseif($cnt>count($oId)) {
      // Kleiner Trick um Items sauber zu schliessen
      for($x=0;$x<($cnt-count($oId));$x++) {
        print "</ul>";
      }
      $cnt = count($oId);
    }
    // Ausgabe des einzelnen Item
    print "<li data=\"id: '".$zeilen[0]."'\" class='folder'>".$zeilen['NAME'];
  }
  $res->finalize();
 ?>
 </div> 
 </div>
 </td>
 <td class="einstellungen" width="150px">
  <form method="post" action="." name="frm_style">
  <b><?php print $mlng['style']; ?></b>
  <select name="style" onchange="javascript:document.frm_style.submit();">
   <?php
   // Verfuegbare Style ermitteln
   if($style_dir = opendir('css')) {
    while(false !== ($file = readdir($style_dir))) {
      ($file==$_SESSION['style']) ? $style_sel = " selected='selected'" : $style_sel = "";
      if(is_dir("css/".$file) && $file!="." && $file!="..") print "<option value='$file'$style_sel>$file</option>\n"; 
    }
   }
   ?>
  </select>
  </form>
 </td>
 <td class="einstellungen" colspan="2">
  <form method="post" action="." name="frm_suche">
  <b><?php print $mlng['search']; ?>:</b>
  <input type="text" name="suchen" size="50%" value="<?php print $_SESSION['suchen']; ?>" />
  <select name="fclass">
   <option value=''>--- <?php print $mlng['search_c']; ?> ---</option>
   <?php
   function prepData($value) {
    global $qPHP;
    $value = $qPHP->istr_replace("container.","",$value);
    $value = $qPHP->istr_replace("item.","",$value);
    $value = $qPHP->istr_replace("."," : ",$value);
    return $value;
   }
   
   $sql = "SELECT `CLASS` FROM `OBJECTS` GROUP BY `CLASS`";
   $res = $db->query($sql);
   while($zeilen = $res->fetchArray()) {
    ($zeilen['CLASS']==$_SESSION['fclass']) ? $class_sel = " selected='selected'" : $class_sel = "";
    print "<option value='".$zeilen['CLASS']."'$class_sel>".prepData($zeilen['CLASS'])."</option>\n"; 
   }
   ?>
  </select>
  <input type="submit" name="senden" value="<?php print $mlng['search']; ?>" />
  </form>
 </td>
</tr>
<tr>
 <td class="einstellungen">
 <form method="post" action="." name="frm_sort">
  <b><?php print $mlng['sort']; ?>:</b>
  <select name="sortierung" onchange="javascript:document.frm_sort.submit();">
   <option value="`OB`.`OBJECT_ID`">DBID</option>
   <option value="`DT`.`PATH`"<?php if($_SESSION['sortierung']!="`OB`.`OBJECT_ID`") { print " selected='selected'"; } ?>><?php print $mlng['sort_c']; ?></option>
  </select>
  </form> 
 </td>
 <td class="einstellungen">
 <b><?php print $mlng['artist']; ?>:</b>
 <select name="artist" id="artist" onchange="javascript:iSrc(this);">
 <?php
  $sql = "SELECT `ARTIST` 
            FROM `DETAILS`
           WHERE `ARTIST`<>'' 
        GROUP BY `ARTIST`";
  $res = $db->query($sql);
  while($zeilen = $res->fetchArray()) {
    print "<option value='artist=".$zeilen['ARTIST']."'>".$qPHP->shortName($zeilen['ARTIST'],25)."</option>\n";  
  }
  $res->finalize(); 
 ?> 
 </select>
 </td>
 <td class="einstellungen">
 <b><?php print $mlng['genre']; ?>:</b>
 <select name="genre" id="genre" onchange="javascript:iSrc(this);">
 <?php
  $sql = "SELECT `GENRE` 
            FROM `DETAILS`
           WHERE `GENRE`<>'' 
        GROUP BY `GENRE`";
  $res = $db->query($sql);
  while($zeilen = $res->fetchArray()) {
    print "<option value='genre=".$zeilen['GENRE']."'>".$qPHP->shortName($zeilen['GENRE'],25)."</option>\n";  
  }
  $res->finalize(); 
 ?>
 </select>
 </td>
<tr>
 <td class="main" colspan="3">
  <iframe src="inc/abfrage.inc.php" id="main_frame" name="main_frame" class="main_frame">
  </iframe>
 </td>
</tr>
</table>
</body>
</html>