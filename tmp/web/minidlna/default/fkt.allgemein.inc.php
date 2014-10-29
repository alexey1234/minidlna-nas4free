<?php
class PHP_func {
/*******************************************************************************
 *                                                                             *
 *                      MySQL-Verbindungsaufbau                                * 
 *                                                                             *
 ******************************************************************************/

  // anderen Klassen zugaenglich : protected
 //protected $prot;          // Geschützte Variable
 
 // Oeffentliche Variablen : public
 //fkt: text_split()
 public $SplitArray;              // Array mit gesplitteten Werten
 public $SplitArray2;             // Array mit gesplitteten Werten
 public $SplitUBound;             // Anzahl der Splittergebnisse
 public $SplitUBound2;            // Anzahl der Splittergebnisse
 public $TextFindPos;			        // Position des gefundenen Textes
 public $FileName;                // Dateiname           
  
 // interne Variablen : private
 //private $priv;             // Private Variable
 
 function __construct()
 {
  // Alles hier wird default bei Instanzierung aufgerufen
 }
 
 function __destruct()
 {
  // Alles hier wird bei  Scriptende aufgerufen
 }
 
 function __get($strVariable)
 {
  // Aufruf einer nicht definierten Variable
  throw new ExtException(basename(__FILE__),"Variable '<b>".$strVariable."
            </b>' existiert nicht!",__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
 }
 
 function __set($strVariable,$strValue) {
  // Aufruf einer nicht definierten Variable, aber Weiterverarbeitung
  switch($strVariable) {
    default:
      throw new ExtException(basename(__FILE__),"Variable '<b>"
            .$strVariable."</b>' existiert nicht!",__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
      break;
  }
 }

  //Zu Seite wechseln
  public function NavigatePage($seite) {
    echo "<script language='javascript'>\n";
    echo "  location.replace('".$seite."');\n";
    echo "</script>\n";
  } 
   
  //Seite neu laden
  public function ReloadPage() {
    echo "<script language='javascript'>\n";
    echo "  location.reload();\n";
    echo "</script>\n";
  }
  
  // Text ersetzen
  public function istr_replace($search,$replace,$source) {
    if($source!="") {
      $new_string = str_replace($search,$replace,$source);
      if($new_string!="") {
        return $new_string;
      } else {
        throw new ExtException(basename(__FILE__),"Textersetzung: $search durch $replace"
                  ." in String($source) schlug fehl",__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
      }
    }  
  }

  // Text suchen
  public function text_find($search,$source,$start=-1) {
    // $regEx (default: false)
      //      -> true, dann muss $search eine regulare Expression sein!!!
    if($start!=-1) {
      $search2 = substr($search,1,strlen($search)-2);
      $TextFindPos = strpos($search2,$source,$start);
      $TextFindPos = $search2;
    }
    if(preg_match($search,$source)==1) {
      return true;
    } else {
      return false;
    }                               
  }
  
  // Text in Array Splitten
  public function text_split($search,$source,$retError=0,$limit=0) {
    /* Example for split:
    '//'          Einzelne Zeichen
    '/ /'         Spaces
    $limit => wieviel mal geteilt werden darf .., default: 0 (parameter weglassen ;)
    $retError =>  Return Error, wenn kein Split (default), 1 = Return $source
    */
    $this->SplitArray = preg_split($search,$source,$limit);
    $this->SplitUBound = count($this->SplitArray)-1;
    if(!$this->SplitUBound) {
      if($retError==0) {
        throw new ExtException(basename(__FILE__),"Textaufteilen: $source bei $search aufteilen"
                  ." schlug fehl",__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
      } else {
        $this->SplitUBound = 0;
      }
    }
  }

  // Text in Array Splitten (Sekundärfunktion)
  public function text_split2($search,$source,$retError=0,$limit=0) {
    /* Example for split:
    '//'          Einzelne Zeichen
    '/ /'         Spaces
    $limit => wieviel mal geteilt werden darf .., default: 0 (parameter weglassen ;)
    $retError =>  Return Error, wenn kein Split (default), 1 = Return $source
    */
    $this->SplitArray2 = preg_split($search,$source,$limit);
    $this->SplitUBound2 = count($this->SplitArray2)-1;
    if(!$this->SplitUBound2) {
      if($retError==0) {
        throw new ExtException(basename(__FILE__),"Textaufteilen: $source bei $search aufteilen"
                  ." schlug fehl",__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
      } else {
        $this->SplitUBound2 = 0;
      }
    }
  }  
  
  // Array Leestring durch integer 0 ersetzen
  public function rZero($searchVal) {
    if($searchVal=="") {
      return 0;
    } else {
      return $searchVal;
    }
  }
  
  // Umlaute ersetzen
  public function RUml($text) {
    $umlaute = array("Ä","Ö","Ü","ä","ö","ü","ß");
    $korrekt = array("Ae","Oe","Ue","ae","oe","ue","ss");
    $text = str_replace($umlaute,$korrekt,$text);
    return $text; 
  }
  
  // Text abkuerzen, zu lange Texte mit .. verkuerzen
  public function shortName($text,$laenge=15) {
    if(strlen($text)>$laenge) {
      return substr($text,0,$laenge)."...";
    } else {
      return $text;
    }
  }
  
    /* Verschluesseln */
  public function encrypt($string, $key) {
    $result = '';
    for($i=0; $i<strlen($string); $i++) {
      $char = substr($string, $i, 1);
      $keychar = substr($key, ($i % strlen($key))-1, 1);
      $char = chr(ord($char)+ord($keychar));
      $result.=$char;
    }

    return base64_encode($result);
  }

  /* Entschluesseln */
  public function decrypt($string, $key) {
    $result = '';
    $string = base64_decode($string);

    for($i=0; $i<strlen($string); $i++) {
      $char = substr($string, $i, 1);
      $keychar = substr($key, ($i % strlen($key))-1, 1);
      $char = chr(ord($char)-ord($keychar));
      $result.=$char;
    }

    return $result;
  }
  
  // PW Generation
  function register_generate_salt() {
    $pattern = "1234567890abcdefghijklmnopqrstuvwxyz";
    for ($i=0; $i<10; $i++)
    {
      if (isset($key))
        $key .= $pattern{rand(0,35)};
      else
        $key = $pattern{rand(0,35)};
    }
    return $key;
  }
  
  function create_passwd ($pattern="") {
    // Optionale Mitgabe des "pattern"
    if($pattern=="") { $pattern = "2345679abcdefghjkmnpqrstuvwxyz"; }
    for ($i=0; $i<10; $i++)
    {
      if (isset($passwd))
        $passwd .= $pattern{rand(0,29)};
      else
        $passwd = $pattern{rand(0,29)};
      }
    return $passwd;
  }
  
  function mysql2date($mysql_date,$format="") {
    // Format: zb. "d.m.Y" oder "D, d.M.Y"
    if($mysql_date!=NULL) {
      $this->text_split("/-/",$mysql_date);
      if($format=="" && $this->SplitUBound==2) {
        return $this->SplitArray[2].".".$this->SplitArray[1].".".$this->SplitArray[0];
      } elseif($format!="" & $this->SplitUBound==2) {
        $timestr = mktime(0,0,0,$this->SplitArray[1],$this->SplitArray[2],$this->SplitArray[0]);
        return date($format, $timestr);
      } else {
        $this->SplitUBound;
      } 
    } else {
      throw new ExtException(basename(__FILE__),"Input: $mysql_date ist kein gueltiges MySQL-Datum (YYYY-MM-DD)"
      ,__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
    }
  }
  
  function arab2roem($arabische_zahl) {
   // Quelle: http://www.roemische-ziffern.de/Roemische-Zahlen-PHP-berechnen.html
   $ar_r = array( "M","CM","D","CD","C","XC","L","XL","X","IX","V","IV","I");
   $ar_a = array(1000, 900,500, 400,100, 90,  50, 40,  10,   9,  5,   4,  1);

   for ($count=0; $count < count($ar_a); $count++) {
      while ($arabische_zahl >= $ar_a[$count]) {
         $roemische_zahl .= $ar_r[$count];
         $arabische_zahl -= $ar_a[$count];
      }
   }
   return $roemische_zahl;
  }
  
  // Dateiendung ermitteln
  public function dateiendung($dateiname) {
    $pathinfo = pathinfo($dateiname);
    if(!$pathinfo['extension']) {
      throw new ExtException(basename(__FILE__),"Datei: $dateiname hat keine Dateiendung!"
      ,__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
    } else {
      return $pathinfo['extension'];
    }  
  }

  function create_filename ($pre="") {
    // Erzeugen eines einmaligen Dateinamens mittels MicroTIME
    $new_filename = microtime();
    $this->text_split('/ /',$new_filename);
    $new_filename = $this->SplitArray[0] + $this->SplitArray[1];
    ($pre!="") ? $this->FileName = $this->RUml($pre)."_".$new_filename : $this->FileName = $new_filename;
  }
  
  function mailme($from, $to,$betreff,$body,$add_to="",$filename="") {
    // Funktion fuer den eMail-Versand   
    $header = "From: $from\n";
    if($add_to!="") { $header .= $add_to; }
    $header .= "MIME-Version: 1.0\n";
    $header .= "X-Mailer: PHP/".phpversion()."\n";
    $header .= "X-Sender-IP: ".$_SERVER['REMOTE_ADDR']."\n";
    if($filename!="") {
      $mailfile = basename($filename);
      $boundary = strtoupper(md5(uniqid(time())));
      $header .= "Content-Type: multipart/mixed; boundary=$boundary\n";
      $header .= "This is a multi-part message in MIME format -- Dies ist eine mehrteilige Nachricht im MIME-Format\n";
      $header .= "--$boundary";
      $header .= "\nContent-Type: text/plain";
      $header .= "\nContent-Transfer-Encoding: 8bit";
      $header .= "\n$body\n";
      $file_content = fread(fopen($filename,"r"),filesize($filename));
      $file_content = chunk_split(base64_encode($file_content));
      $header .= "--$boundary\n";
      $header .= "Content-Type: application/octetstream; name=\"$mailfile\"";
      $header .= "\nContent-Transfer-Encoding: base64";
      $header .= "\nContent-Disposition: attachment; filename=\"$mailfile\"";
      $header .= "\n$file_content";
      $header .= "--$boundary--";
    }
    $result = mail($to,$betreff,$body,$header);
    if($result) {
        print "<br /><b>eMail wurde erfolgreich versendet</b>";
      } else {
        throw new ExtException(basename(__FILE__),"eMail von: $from konnte nicht an $to gesendet werden.<br />
        $betreff\n<b>Text:</b> $body<br />Dateiname: $filename<br />AddHead: $add_to",__LINE__, __CLASS__ . "{}", __FUNCTION__. "()");
      }         
  }
} 
?>
