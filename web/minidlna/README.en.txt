Installation Instructions
*************************
Prequisits
* Apache2
* PHP5<
* PHP_SQLITE3

Installation:
* Download minidlna.tar into a directory on your webserver
* Extract: tar -xvf minidlna.tar
* Adjust userrights: 
  (ONLY if you want to use the download-module!!!)
  chmod 777 -R jd  
* Adjust the Config-File 
  <snip>
  $path_to_db = {Absolute path to database} (ex.: "/var/lib/minidlna/files.db");
  $debug_sqls = {true = show DB-Queries, false = no DB-Queries-Output} (default: false)
  $download   = {true = make it possible to download files, false = no download} (default: false)
  </snip>
  
Styles
******
If you want to install a new style (for the Tree-View)
* Create a directory in the folder "css"
* Create in the new directory a file called: "ui.dynatree.css"
(Default)
default = Default-Style (Old Windows)
vista   = Vista-Style (Vista Windows)
none    = No Pictures (normal listing without symbols)

Languages
*********
All the languagefiles are located in the folder "lng"
* ?.lng = Languagefile
* ?.jpg = Languagepicture (24x12px [BxH])

Language (Syntax of Filename: 2 alphanumeric-digit)
(Default)
de.lng  = german
en.lng  = english
es.lng  = spanish
fr.lng  = french
it.lng  = italian  


TIPS
****
If you get an error while accessing the files.db of the minidlna via Web, set a Hardlink (NOT Softlink):

01. in your webaccessable folder: ln /var/lib/minidlna/files.db .
02. correct the Config-File (inc/config.inc.php)
    <snip>
    $path_to_db = {Absolute path to database} (ex.: "/var/www/localhost/htdocs/minidlna/files.db");
    </snip>