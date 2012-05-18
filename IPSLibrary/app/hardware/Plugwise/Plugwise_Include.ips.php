<?
/**
* This file is part of the IPSLibrary.
*
* The IPSLibrary is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published
* by the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* The IPSLibrary is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with the IPSLibrary. If not, see http://www.gnu.org/licenses/gpl.txt.
*/

update_data1data2();

function PW_SendCommand($cmd)
{

	switch ( substr($cmd,0,4 ) )
	   {
	   case	"0012": 	logging( "S - ".$cmd." Power information request (current)"); break;
		case	"0016":	logging( "S - ".$cmd." Clock set request"); break;
		case	"0017":	logging( "S - ".$cmd." Device Ein/Aus"); break;
		case	"0018":	logging( "S - ".$cmd." Search Circle"); break;
		case	"0023":	logging( "S - ".$cmd." Device information request"); break;
		case	"0026":	logging( "S - ".$cmd." Kalibrierungsdaten abrufen"); break;
		case	"003E":	logging( "S - ".$cmd." Clock information request"); break;
		case	"0048":	logging( "S - ".$cmd." Power buffer information"); break;

	   default     :  logging( "S - ".$cmd." unbekannter Befehl"); break;
		}
		

	$comid = @IPS_GetInstanceIDByName('PlugwiseCOM',0);
	$i = (IPS_GetInstance($comid));
	$i = $i['InstanceStatus'];

	if ( $i != 102 ) { echo "\nCOMPort nicht offen"; return ; }

	$REGVAR = get_ObjectIDByPath('Hardware.Plugwise.PlugwiseRegisterVariable');

	$ausgabe=strtoupper(dechex(calculate_common_crc16c($cmd)));
	$ausgabe = str_pad($ausgabe, 4 ,'0', STR_PAD_LEFT); //mit nullen auffüllen
	$cmd.= $ausgabe;
	RegVar_SendText($REGVAR,"\x05\x05\x03\x03".$cmd."\x0D\x0A");
	IPS_Sleep(300);
	
}

function PW_SwitchMode($InstanceID, $DeviceOn)
{
	// PRINT "PW_SwitchMode - PW Device: ".$InstanceID.", DeviceON: ".$DeviceOn;
	// IPS_LogMessage("PW_SwitchMode", "run");

   // Zum Schalten
	$id_info = IPS_GetObject($InstanceID);
	$cmd = "0017".$id_info['ObjectIdent']."0".$DeviceOn;
   PW_SendCommand($cmd);
}

// this function is used to calculate the (common) crc16c for an entire buffer
function calculate_common_crc16c($buffer)
{
    $crc16c = 0x0000;  // the crc initial value laut www.maartendamen.com
    $buffer_length = strlen($buffer);
    for ($i = 0; $i < $buffer_length; $i++)
    {
        $ch = ord($buffer[$i]);
        $crc16c = update_common_crc16c($ch, $crc16c);
    }
    return $crc16c;
}
// this function is used to calculate the (common) crc16c byte by byte
// $ch is the next byte and $crc16c is the result from the last call, or 0xffff initially
function update_common_crc16c($ch, $crc16c)
{
    $crc16c_polynomial = 0x11021;   //auch laut maartendamen
    // This comment was in the code from
    // http://www.joegeluso.com/software/articles/ccitt.htm
    // Why are they shifting this byte left by 8 bits??
    // How do the low bits of the poly ever see it?
    $ch <<= 8;
    for($i = 0; $i < 8; $i++)
    {
        if (($crc16c ^ $ch) & 0x8000)
        {
            $xor_flag = true;
        }
        else
        {
            $xor_flag = false;
        }
        $crc16c = $crc16c << 1;
        if ($xor_flag)
        {
            $crc16c = $crc16c ^ $crc16c_polynomial;
        }
        $ch = $ch << 1;
    }
    // mask off (zero out) the upper two bytes
    $crc16c = $crc16c & 0x0000ffff;
    return $crc16c;
}

/*
function pwtime2unixtime2($pwtime) {

	// Konvertiert Datumsangaben des Plugwise-Formats in Unixtime

	$year = hexdec(substr($pwtime,0,2));
	$month = hexdec(substr($pwtime,2,2));
	$minutes = hexdec(substr($pwtime,4,4));
	// date_default_timezone_set('UTC');
	return mktime(0, $minutes, 0, $month, 0,$year);
	// date_default_timezone_set('Europe/Berlin');
	// $utcDateTime = date("c",mktime(0, $minutes, 0, $month, 0,$year));
	// date_default_timezone_set("Europe/Berlin");
   // return strtotime($utcDateTime);
}
*/

function pwtime2unixtime($pwdate) {

	// Konvertiert Plugwise Zeit in lokale Zeit und gibt den Timestamp zurück

	$jahr = 2000+hexdec(substr($pwdate,0,2));
	$monat =hexdec(substr($pwdate,2,2));
	$stunden = (hexdec(substr($pwdate,4,4))/60);
	$min=(hexdec(substr($pwdate,4,4))%60);
	$tag=floor(1+($stunden/24));
	$h = ($stunden%24);
	$offsetgmt = (date("Z")/3600);             // Offset zur GMT in Stunden
	return mktime($h + $offsetgmt, $min, 0, $monat, $tag, $jahr);
}

function bintofloat($in)
{
    $in=hexdec($in);
      $binary = str_pad(decbin($in),32,"0", STR_PAD_LEFT);
    $fb = $binary[0];
    $exp = bindec(substr($binary, 1, 8));
    $m = bindec(substr($binary, 9, 23));
    return pow(-1,$fb) * (1+$m/(pow(2,23))) * pow(2,$exp-127);
}


/*
function pulsesToKwh($value, $offRuis, $offTot, $gainA, $gainB) {
		if ($value == hexdec("FFFFFFFF")) {
			return 0;
		} else {
			$pulses = (((pow($value + $offRuis, 2.0) * $gainB) + (($value + $offRuis) * $gainA)) + $offTot);
			$result = $pulses / 3600 / 468.9385193;
			return $result;
		}
	}
*/

function pulsesToKwh($value, $offRuis, $offTot, $gainA, $gainB) {
        if ($value == hexdec("FFFFFFFF")) {
            return 0;
        } else {
           $value = $value / 3600;
            $pulses = (pow(($value + $offRuis), 2) * $gainB) + (($value + $offRuis) * $gainA) + $offTot;
            $result = (($pulses / 3600) / 468.9385193)*3600;
            return $result;
        }
    }

function findRegVar($id) {
	foreach(IPS_GetChildrenIDs(IPS_GetParent($id)) as $item){   // alle Unterobjekte durchlaufen
		$id_info = IPS_GetObject($item);
		if ($id_info["ObjectType"] == 1){
			// Instanz gefunden
			$myInstance = IPS_GetInstance($item);
			if ($myInstance["ModuleInfo"]["ModuleID"] == "{F3855B3C-7CD6-47CA-97AB-E66D346C037F}") {
				// Instanz ist eine Register Variable
				return $item;
			}
		}
	}
}

//******************************************************************************
// Gibt das aktuelle Datum/Uhrzeit im Plugwise-Format zurück (Zeitzone UTC!)
//******************************************************************************
function unixtime2pwtime() {

	$vorstellen = 1;

	$jahr=str_pad(strtoupper(dechex(gmdate("y"))), 2 ,'0', STR_PAD_LEFT);
	$monat=str_pad(strtoupper(dechex(gmdate("m"))), 2 ,'0', STR_PAD_LEFT);
	$mingesamt=str_pad(strtoupper(dechex(((gmdate("j")-1)*24+(gmdate("G")))*60+(gmdate("i")+$vorstellen))), 4 ,'0', STR_PAD_LEFT);
	$logzurueck = 'FFFFFFFF';
	$h=str_pad(strtoupper(dechex(gmdate("G"))), 2 ,'0', STR_PAD_LEFT);
	$m=str_pad(strtoupper(dechex(gmdate("i")+$vorstellen)), 2 ,'0', STR_PAD_LEFT);
	$s=str_pad(strtoupper(dechex(gmdate("s"))), 2 ,'0', STR_PAD_LEFT);
	$dow=str_pad(strtoupper(dechex(gmdate("N"))), 2 ,'0', STR_PAD_LEFT);
	return($jahr.$monat.($mingesamt).$logzurueck.$h.$m.$s.$dow);
}



function createCircle($mac, $parentID){
	
	GLOBAL $CircleGroups;

      //  Archive ID ermitteln
  foreach ( IPS_GetInstanceListByModuleType(0) as $modul )
    {
		$instance = IPS_GetInstance($modul);
		if ( $instance['ModuleInfo']['ModuleName'] == "Archive Control" ) { $archive_id = $modul; break; }
	  }
  if ( !isset($archive_id) ) { echo "\nArchive Control nicht gefunden!"; die(); }


	print "\nPW Create Circle: ".$mac;
	$item = CreateInstance($mac, $parentID, "{485D0419-BE97-4548-AA9C-C083EB82E61E}", $Position=0);
	$id_info = IPS_GetObject($item);
	IPS_SetIdent ($item, $mac);


 	$gruppe = "";
	$name   = "";
  $einaus = "";
  
	foreach( $CircleGroups as $circle )
	   {
		if ( $circle[0] == $mac )
		   {
		   $name   = $circle[1];
		   $gruppe = $circle[2];
		   $einaus = $circle[3];
		   break;
		   }
	   }



	// CreateVariable ($Name, $Type, $Parent, $Position, $Profile, $Action=0, $ValueDefault='', $Icon="")
	// CreateVariable ($Name, $Type, $Parent, $Position, $Profile, $Action=0, $ValueDefault='', $Icon="")
	$CategoryIdApp = get_ObjectIDByPath('Program.IPSLibrary.app.hardware.Plugwise');
	$ScriptId = IPS_GetScriptIDByName('Plugwise_Controller', $CategoryIdApp );
 
  $id1 = CreateVariable("Status", 0, $item, 0, "~Switch", false, false);
  if ( $einaus == "1" )
    {
    echo "\nBei $id1 Actionscript setzen"; 
    IPS_SetVariableCustomAction($id1,$ScriptId);
    }
   
	$id2 = CreateVariable("Leistung", 2, $item, 0, "~Watt.3680", 0, 0);
	$id3 = CreateVariable("Gesamtverbrauch", 2, $item, 0, "~Electricity", 0, 0); //~Electricity

	$id4 = CreateVariable("WebData1", 3, $item, 0, "~HTMLBox", 0, 0);
	$id5 = CreateVariable("WebData2", 3, $item, 0, "~HTMLBox", 0, 0);

  AC_SetLoggingStatus($archive_id, $id2, True); // Logging einschalten
  AC_SetAggregationType($archive_id, $id2, 1); // Logging auf Zähler setzen
  AC_SetLoggingStatus($archive_id, $id3, True); // Logging einschalten
  AC_SetAggregationType($archive_id, $id3, 1); // Logging auf Zähler setzen



	// $myVar = CreateVariable("Pulses Stunde", 2, $item, 0, "", 0);
	// IPS_SetHidden($myVar, True);
	// $myVar = CreateVariable("LogAddress", 3, $item, 0, "", "");
	// IPS_SetHidden($myVar, True);
	$myVar = CreateVariable("gaina",2,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
	$myVar = CreateVariable("gainb",2,$item,0,"", 0,0);
	IPS_SetHidden($myVar, True);
	$myVar = CreateVariable("offTotal",2,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
	$myVar = CreateVariable("offNoise",2,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
   $myVar = CreateVariable("LogAddress", 1,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
   $myVar = CreateVariable("Error", 1,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
   $myVar = CreateVariable("LastMessage", 3,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);




	// Kalibrierungsdaten vom Circle abrufen
	PW_SendCommand("0026".$mac);

	// Zeit stellen
 	PW_SendCommand("0016".$mac.unixtime2pwtime());

	// Status abfragen
	PW_SendCommand("0012".$mac);
	PW_SendCommand("0023".$mac);

}


function update_data1data2()
	{
	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";

   $IdData   = get_ObjectIDByPath($CircleDataPath);

	foreach ( IPS_GetChildrenIDs($IdData) as $parent )
		{
      update_data1data2_sub($parent);
		}

	$OtherDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Others";

   $IdData   = get_ObjectIDByPath($OtherDataPath);

	foreach ( IPS_GetChildrenIDs($IdData) as $parent )
		{
      update_data1data2_sub($parent,true);
		}

		
	}
		
function update_data1data2_sub($parent,$groups = false)
	{
	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");


		$data1id    = IPS_GetVariableIDByName('WebData1',$parent);
		$data2id    = IPS_GetVariableIDByName('WebData2',$parent);
		$gesamtid   = IPS_GetVariableIDByName('Gesamtverbrauch',$parent);
		$leistungid = IPS_GetVariableIDByName('Leistung',$parent);
		$error      = @GetValue(IPS_GetVariableIDByName('Error',$parent));
		
		$dateleistung = IPS_GetVariable($leistungid);
		$dateleistung = date('H:i:s',$dateleistung['VariableUpdated']);
		
		$leistung = round(GetValue($leistungid),1);
      $gesamt   = round(GetValue($gesamtid),1);
      $akt_tk   = aktuelle_kosten($parent,$leistung,$groups);  // aktuelle Kosten und Tarif
      $kosten   = $akt_tk['KOSTEN'];
      $akt_tarif= $akt_tk['TARIF'];
      $kt_preis = $akt_tk['PREISKWH'];

		$akt_tarif= $akt_tarif . " " . $kt_preis ." Cent/kWh";

      $waehrung = "Cent/h";
      $vergleich = 0.1 ;
      if ( $kosten > $vergleich )
         { 
         $kosten = $kosten/100;
         $waehrung = "Euro/h";
         }
      $kosten = round($kosten,2);
      
      $array = statistikdaten($gesamtid);
      $verbrauch_heute   = $array['VERBRAUCH_HEUTE'];
      $verbrauch_gestern = $array['VERBRAUCH_GESTERN'];

      
		$hintergrundfarbe = "#003366";
		$fontsize = "17px";
		
      
		$html1 = "";
		$html1 = $html1 . "<table border='0' bgcolor=$hintergrundfarbe width='100%' height='200' cellspacing='0'  >";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td style='text-align:left;'>";
		$html1 = $html1 . "<span style='font-family:arial;color:white;font-size:15px;'>Aktuell<br></span>";
		$html1 = $html1 . "<span style='font-family:arial;color:white;font-size:15px;'></span></td>";
		$html1 = $html1 . "<td align=center><span style='font-family:arial;font-weight:bold;color:white;font-size:20px;'>$dateleistung </span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:20px;'>Uhr</span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:15px'>Leistung</span></td>";
		$html1 = $html1 . "<td align=center><span style='font-family:arial;font-weight:bold;color:yellow;font-size:40px'>$leistung</span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:25px;'>Watt</span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:15px;'>Kosten</span></td>";
		$html1 = $html1 . "<td align=center><span style='font-family:arial;font-weight:bold;color:yellow;font-size:40px;'>$kosten</span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:25px;'>$waehrung</span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:12px;'>Tarif</span></td>";
		$html1 = $html1 . "<td align=center><span style='font-family:arial;font-weight;color:white;font-size:12px;'>$akt_tarif</span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:12px;'></span></td>";
		$html1 = $html1 . "</tr>";

		$html1 = $html1 . "</table>";

		if ( $error != 0 )
		   $html1 = "Circle ausgefallen !".$error;
      SetValueString($data1id,$html1);
		$fontsize  = "16px";
		$fontsize1 = "20px";

		$html1 = "";
		$html1 = $html1 . "<table border='0' bgcolor=$hintergrundfarbe width='100%' height='200' cellspacing='10'>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td style='text-align:left;'><span style='font-family:arial;color:white;font-size:$fontsize;'>Verbrauch Gesamt</span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize1;'>$gesamt</span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'>kWh</span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize'>Verbrauch Heute</span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize1'>$verbrauch_heute</span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'>kWh</span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'>Verbrauch Gestern</span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize1;'>$verbrauch_gestern</span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'>kWh</span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td style='text-align:left;'><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize'></span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize'></span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "</tr>";
		$html1 = $html1 . "<tr>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=right><span style='font-family:arial;font-weight:bold;color:#FFCC00;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "<td align=left><span style='font-family:arial;color:white;font-size:$fontsize;'></span></td>";
		$html1 = $html1 . "</tr>";

		$html1 = $html1 . "</table>";

      SetValueString($data2id,$html1);

		


	}


function statistikdaten($gesamtid)
	{

   $instances = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
	$archive   = $instances[0];

	$array['VERBRAUCH_HEUTE'] = 0;
	$array['VERBRAUCH_GESTERN'] = 0;


	$start = mktime(0,0,0,date("m"),date("d")-1,date("Y"));
	$ende  = mktime(23,59,59,date("m"),date("d")-1,date("Y"));
	$data = AC_GetLoggedValues($archive,$gesamtid,$start,$ende,-1);
	if ( $data )
	   {
		$start_wert = floatval($data[0]['Value']);
		$ende_wert  = floatval($data[count($data)-1]['Value']);
		$diff_wert  = $start_wert - $ende_wert ;
		}
	$array['VERBRAUCH_GESTERN'] = round($diff_wert,1);
	
	$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$ende  = mktime(23,59,59,date("m"),date("d"),date("Y"));
	$data = AC_GetLoggedValues($archive,$gesamtid,$start,$ende,-1);
	if ( $data )
	   {
		$start_wert = floatval($data[0]['Value']);
		$ende_wert  = floatval($data[count($data)-1]['Value']);
		$diff_wert  = $start_wert - $ende_wert ;
		}
	$array['VERBRAUCH_HEUTE'] = round($diff_wert,1);

	
	return $array;
	
	}
	
function aktuelle_kosten($parent,$leistung,$groups = false)
	{
	GLOBAL $Stromtarife;
	GLOBAL $CircleGroups;
	
	$debug = false;
	
	$akt_kt['KOSTEN'] = 0;
	$akt_kt['TARIF'] = "";

	
	$object = IPS_GetObject($parent);
	$ident = $object['ObjectIdent'];
	
	
	$tarifgruppe = false;
	foreach( $CircleGroups as $circle )
	   {
	      //Tarifgruppe fuer diesen Circle suchen
	   if ( $circle[0] == $ident )
	      { $tarifgruppe = $circle[6] ; break ; }

		$tarifgruppe = $circle[6] ;   // Wenn keine Gruppe gefunden dann letzten Eintrag
	   }
	

	if ( !$tarifgruppe )
	   return(0);           // Keine Tarifgruppe gefunden



	   
	if ( $debug ) echo "\n" . $tarifgruppe;
	
	$now = time();
	$heute = date('d.m.Y');
	if ( $debug )  echo "\nNow:". $now ." - " . $heute;
	$aktpreiskwh = 0;
	$akttarifname = "?";
	foreach ( $Stromtarife as $zeitraum )
	   {
	   $startdate = $zeitraum[0];
	   $endedate  = $zeitraum[1];

      $startdatex = strtotime($startdate);
      $endedatex  = strtotime($endedate);
      //echo "\n------------------------------------------------";
		//echo "\n - " .$startdatex . " - " . $endedatex;
		// in welchem Jahreszeitraum befinden wird uns ?
		if ( $now > $startdatex  and $now < $endedatex  )
			{
			if ( $debug ) echo "\nJahreszeitraum vom $startdate - $endedate";
			if ( $zeitraum[2] == $tarifgruppe)
				{
				if ( $debug ) print_r($zeitraum);
				$tarifname = $zeitraum[3];
				$startzeit = $zeitraum[4];
				$endezeit  = $zeitraum[5];
				$preiskwh  = $zeitraum[6];

					if ( $preiskwh > 0 and $tarifname != "" and $endezeit != "" and
								$startzeit != "" )
						{
						$starttimestring = $heute . " " . $startzeit;
						$endetimestring  = $heute . " " . $endezeit;
						$starttimestamp  = strtotime($starttimestring);
						$endetimestamp   = strtotime($endetimestring);

						if ( $debug ) echo "\n".$tarifname . " " . strtotime($endezeit);
						if ( $debug ) echo "\n $tarifname ";
						if ( $debug ) echo "\n $starttimestring - $starttimestamp";
						if ( $debug ) echo "\n $endetimestring - $endetimestamp";
						$ok = true;
						if ( $now <= $starttimestamp ) {  $ok = false; }
						if ( $now >= $endetimestamp  ) {   $ok = false; }
						if ( $ok )
						   {
						   if ( $debug ) echo "ok";
							$akttarifname = $tarifname;
							$aktpreiskwh = $preiskwh;
						   }



					   }

			   }
			}

		}





	if ( $debug ) echo "\nDer aktuelle Tarifname = $akttarifname - $aktpreiskwh ";

	$watt = $leistung;
	$kwh = ($watt/1000) * 1 ;
	$kosten = round(($kwh * $aktpreiskwh),2);
	//echo "\nWatt:$watt $kwh Preis pro Stunde : $kosten ";
	// Rueckgabe als hunderstel Cent

	$akt_kt['KOSTEN']   = $kosten;
	$akt_kt['TARIF']    = $akttarifname;
	$akt_kt['PREISKWH'] = $aktpreiskwh;

	return($akt_kt);
	}
	
//******************************************************************************
// Logging
//******************************************************************************
function logging($text,$file = 'plugwise.log' )
	{
	if ( !LOG_MODE )
	   return;
	$ordner = IPS_GetKernelDir() . "logs\\Plugwise";
   if ( !is_dir ( $ordner ) )
		mkdir($ordner);

   if ( !is_dir ( $ordner ) )
	   return;
      
	$logdatei = IPS_GetKernelDir() . "logs\\Plugwise\\" . $file;
	$datei = fopen($logdatei,"a+");
	fwrite($datei, date("d.m.Y H:i:s")." ". $text . chr(13));
	fclose($datei);

	}
//******************************************************************************

	
?>