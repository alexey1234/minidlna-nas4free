 <div id="tree">
  <?php 
  $sql = "SELECT `OBJECT_ID`,`PARENT_ID`,`NAME`,`DT`.`PATH` 
            FROM `OBJECTS` `OB`
       LEFT JOIN `DETAILS` `DT`
              ON `OB`.`DETAIL_ID`=`DT`.`ID`
           WHERE `OB`.`CLASS`='container.storageFolder' AND
                 `DT`.`PATH`<>'' AND `PARENT_ID` NOT LIKE '64%'
        ORDER BY ".$_SESSION['SOrder'];
  $res = $db->query($sql);
  $cnt = 0;
  while($zeilen = $res->fetchArray()) {
    ($sort_order=="`OBJECT_ID`") ? $oId = explode("$",$zeilen[0]) : $oId = explode("/",$zeilen[3]);
    if($cnt<count($oId)) { 
      print "<ul>";            
      $cnt = count($oId);
    } elseif($cnt>count($oId)) {
      for($x=0;$x<($cnt-count($oId));$x++) {
        print "</ul>";
      }
      $cnt = count($oId);
    }
    print "<li data=\"id: '".$zeilen[0]."'\" class='folder'>".$zeilen['NAME'];
  }
  $res->finalize();
 ?>
 </div>