<?php
// Aufruf mittels: throw new ExtException(Datei-Quelle, Fehlermeldung, Zeile, Klasse, Funktion)
class ExtException extends Exception
 {
 private $err_src;      // Dateiquelle (Filename, pfad)
 private $err_msg;      // Fehlermeldung
 private $err_line;     // Zeile im Quellcode
 private $err_cls;      // Klasse
 private $err_fkt;      // Funktion
 
  function __construct($err_src, $err_msg, $err_line, $err_cls, $err_fkt)
  {
    $this->err_src = $err_src;
    $this->err_msg = $err_msg;
    $this->err_line = $err_line;
    $this->err_cls = $err_cls;
    $this->err_fkt = $err_fkt;
  }
 
  public function errorMessage()
  {
    $ausgabe = "<table style='border: 2px solid #ff0000;' width='100%'>\n";
    if($this->err_src!="")  { $ausgabe .= "<tr>\n<td style='text-align:left; font-weight: bold; border-right: 2px solid #cfcfcf;'>Quelle:</td><td style='text-align:left;'>".$this->err_src."</td></tr>\n"; }  
    if($this->err_msg!="")  { $ausgabe .= "<tr>\n<td style='text-align:left; font-weight: bold; border-right: 2px solid #cfcfcf;'>Message:</td><td style='text-align:left;'>".$this->err_msg."</td></tr>\n"; }
    if($this->err_line!="") { $ausgabe .= "<tr>\n<td style='text-align:left; font-weight: bold; border-right: 2px solid #cfcfcf;'>Zeile:</td><td style='text-align:left;'>".$this->err_line."</td></tr>\n"; }
    if($this->err_cls!="")  { $ausgabe .= "<tr>\n<td style='text-align:left; font-weight: bold; border-right: 2px solid #cfcfcf;'>Klasse:</td><td style='text-align:left;'>".$this->err_cls."</td></tr>\n";  }
    if($this->err_fkt!="")  { $ausgabe .= "<tr>\n<td style='text-align:left; font-weight: bold; border-right: 2px solid #cfcfcf;'>Funktion:</td><td style='text-align:left;'>".$this->err_fkt."</td></tr>\n"; }
    $ausgabe .= "</table>\n";
    return $ausgabe;
  }
 }
?>
