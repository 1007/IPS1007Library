<?



function PW_SendCommand($cmd)
{
	//$REGVAR = 38691; // ID der Registervariable
	//$REGVAR = findRegVar($IPS_SELF);

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
function unixtime2pwtime() {

	// Gibt das aktuelle Datum/Uhrzeit im Plugwise-Format zurück (Zeitzone UTC!)

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


	print "PW Create Circle: ".$mac;
	$item = CreateInstance($mac, $parentID, "{485D0419-BE97-4548-AA9C-C083EB82E61E}", $Position=0);
	$id_info = IPS_GetObject($item);
	IPS_SetIdent ($item, $mac);


 	$gruppe = "";
	$name   = "";

	foreach( $CircleGroups as $circle )
	   {
		if ( $circle[0] == $mac )
		   {
		   $name   = $circle[1];
		   $gruppe = $circle[2];
		   break;
		   }
	   }



	// CreateVariable ($Name, $Type, $Parent, $Position, $Profile, $Action=0, $ValueDefault='', $Icon="")
	// CreateVariable ($Name, $Type, $Parent, $Position, $Profile, $Action=0, $ValueDefault='', $Icon="")
	$CategoryIdApp = get_ObjectIDByPath('Program.IPSLibrary.app.hardware.Plugwise');
	$ScriptId = IPS_GetScriptIDByName('Plugwise_Controller', $CategoryIdApp );

	$id1 = CreateVariable("Status", 0, $item, 0, "~Switch", $ScriptId, false);
	$id2 = CreateVariable("Leistung", 2, $item, 0, "~Watt.3680", 0, 0);
	$id3 = CreateVariable("Gesamtverbrauch", 2, $item, 0, "~Electricity", 0, 0); //~Electricity

	$id4 = CreateVariable("WebData1", 3, $item, 0, "~HTMLBox", 0, 0);
	$id5 = CreateVariable("WebData2", 3, $item, 0, "~HTMLBox", 0, 0);

  AC_SetLoggingStatus($archive_id, $id2, True); // Logging einschalten
  AC_SetAggregationType($archive_id, $id2, 1); // Logging auf Zähler setzen
  AC_SetLoggingStatus($archive_id, $id3, True); // Logging einschalten
  AC_SetAggregationType($archive_id, $id3, 1); // Logging auf Zähler setzen


  /*
  if ( $name != "" )
    if ( $gruppe != "" )
        {
        $VisuPath   = get_ObjectIDByPath("Visualization.WebFront.Hardware.Plugwise.$gruppe.$name");
        $MobilePath = get_ObjectIDByPath("Visualization.Mobile.Hardware.Plugwise.$gruppe.$name");

        if ( $VisuPath )
          {
          CreateLink ("Status",         $id1, $VisuPath, 0, "");
          CreateLink ("Leistung",       $id2, $VisuPath, 0, "");
          CreateLink ("Gesamtverbrauch",$id3, $VisuPath, 0, "");
          CreateLink ("Status",         $id1, $MobilePath, 0, "");
          CreateLink ("Leistung",       $id2, $MobilePath, 0, "");
          CreateLink ("Gesamtverbrauch",$id3, $MobilePath, 0, "");

          }
        }

  */



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




	// Kalibrierungsdaten vom Circle abrufen
	PW_SendCommand("0026".$mac);

	// Zeit stellen
 	PW_SendCommand("0016".$mac.unixtime2pwtime());

	// Status abfragen
	PW_SendCommand("0012".$mac);
	PW_SendCommand("0023".$mac);

}
?>