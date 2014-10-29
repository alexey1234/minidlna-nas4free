// Dynatree Aktionen
// Quelle: http://wwwendt.de/tech/dynatree/
$(function(){
  $("#tree").dynatree({
    onActivate: function(node) {
      document.getElementById('main_frame').src = "inc/abfrage.inc.php?id=" + node.data.id + "&detail=false";
      document.getElementById('tree_sync').style.width = document.getElementById('tree').scrollWidth + "px";
    }
  });
}); 

function syncScroll() {
  /* Scrollbars der Auflistung syncronisieren (Scrollbar oben + unten gleich) */
  document.getElementById('tree_sync').style.width = document.getElementById("tree").scrollWidth + "px";
}

// Syncronisiertes Scrollen
$(function(){
    $(".wrapper1").scroll(function(){
        if(document.getElementById('tree_sync').style.width!=document.getElementById('tree').scrollWidth + "px") {
          document.getElementById('tree_sync').style.width = document.getElementById('tree').scrollWidth + "px";
        } else {
          $(".wrapper2").scrollLeft($(".wrapper1").scrollLeft());
        }
    });
    $(".wrapper2").scroll(function(){
        if(document.getElementById('tree_sync').style.width!=document.getElementById('tree').scrollWidth + "px") {
          document.getElementById('tree_sync').style.width = document.getElementById('tree').scrollWidth + "px";
        } else {
          $(".wrapper1").scrollLeft($(".wrapper2").scrollLeft());
        }
    });
});
    
function iSrc(sel){
  if(sel.value) {
    target = sel.value;
    selID  = sel.name;
  } else {
    target = sel;
    selID = "tree";
  }
  // IFrame neuladen mit uebergabe parameter
  document.getElementById('main_frame').src = "inc/abfrage.inc.php?" + target;
  document.frm_suche.suchen.value = "";
  document.frm_suche.fclass.selectedIndex = 0;
  if(selID=="artist") { document.getElementById('genre').selectedIndex = 0;  }
  if(selID=="genre")  { document.getElementById('artist').selectedIndex = 0; }
  if(selID=="tree")   {
    document.getElementById('genre').selectedIndex = 0;
    document.getElementById('artist').selectedIndex = 0;
  }
}
