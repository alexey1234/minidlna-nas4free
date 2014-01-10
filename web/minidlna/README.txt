Installationsanleitung
**********************
Voraussetzung:
* Apache2
* PHP5<
* PHP_SQLITE3

Installation:
* Download minidlna.tar in ein Verzeichnis des Webservers
* Entpacken: tar -xvf minidlna.tar
* Berechtigungen anpassen:
  (NUR wenn Downloadmodul verwendet werden soll!!!)
  chmod 777 -R jd  
* Config-File-Anpassen (inc/config.inc.php)
  <snip>
  $path_to_db = {Absoluter Pfad zur Datenbank} (bsp.: "/var/lib/minidlna/files.db");
  $debug_sqls = {true = DB-Queries anzeigen, false = keine Ausgabe der DB-Queries} (default: false)
  $download   = {true = Download der Files ermöglichen, false = kein Download} (default: false)
  </snip>
  
Styles
******
Um ein neues Style zu installieren:
* Erstelle ein Verzeichnis im Ordner "css"
* Erstelle in dem neuen Verzeichnis eine Datei: "ui.dynatree.css"
(Default)
default = Default-Style (Old Windows)
vista   = Vista-Style (Vista Windows)
none    = Keine Bilder (normale Auflistung ohne Symbole)

Sprachen
********
Alle Sprachen befinden sich im Ordner "lng"
* ?.lng = Sprachdatei
* ?.jpg = Sprachbild (24x12px [BxH])

Sprachkürzel [2stellig]
(Default)
de.lng  = Deutsch
en.lng  = Englisch
es.lng  = Spanisch
fr.lng  = Französisch
it.lng  = Italienisch  

TIPS
****
Bei Zugriffsfehlern auf die files.db des minidlna einen HardLink (NICHT Softlink) setzen:

01. im Webverzeichnis: ln /var/lib/minidlna/files.db .
02. Config-File-Anpassen (inc/config.inc.php)
    <snip>
    $path_to_db = {Absoluter Pfad zur Datenbank} (bsp.: "/var/www/localhost/htdocs/minidlna/files.db");
    </snip>