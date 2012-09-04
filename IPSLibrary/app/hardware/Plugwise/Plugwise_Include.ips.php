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

	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Profile.inc.php","IPSLibrary::config::hardware::Plugwise");

create_css3menu();

/***************************************************************************//**
*  Plugwise Protocol
*******************************************************************************/

/***************************************************************************//**
*  command number="000d"
*           Ping senden
*
*  vnumber="1.0"     Plugwise.IO.Commands.V10.PWPingRequestV1_0
*  	name="macId" 	length="16"
*******************************************************************************/
function PWPingRequest($macID,$vnumber=false)
	{
	PW_SendCommand("000D".$macID);
	}

/***************************************************************************//**
*  command number="003e"         
*           Uhrzeit lesen
*
*  vnumber="1.0"     Plugwise.IO.Commands.V10.PWGetClockRequestV1_0
*  	name="macId" 	length="16"
*	vnumber="1.1" 		Plugwise.IO.Commands.V20.PWGetClockRequestV1_1
*     name="macId" 	length="16"
*******************************************************************************/
function PWGetClockRequest($macID,$vnumber=false)
	{
	PW_SendCommand("003E".$macID);
	}

 
/***************************************************************************//**
*  Sendet ein Kommando an Plugwise
*******************************************************************************/
function PW_SendCommand($cmd)
{

	// Hier nur Logging
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
	
	if ( defined('WAIT_TIME') )
		$ms = WAIT_TIME;
	else
		$ms = 300;

	IPS_Sleep($ms);
	
}


/***************************************************************************//**
*	
*******************************************************************************/
function PW_SwitchMode($InstanceID, $DeviceOn)
{
	// PRINT "PW_SwitchMode - PW Device: ".$InstanceID.", DeviceON: ".$DeviceOn;
	// IPS_LogMessage("PW_SwitchMode", "run");

   // Zum Schalten
	$id_info = IPS_GetObject($InstanceID);
	$cmd = "0017".$id_info['ObjectIdent']."0".$DeviceOn;
   PW_SendCommand($cmd);
}

/***************************************************************************//**
*	this function is used to calculate the (common) crc16c for an entire buffer
*******************************************************************************/
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

/***************************************************************************//**
*	this function is used to calculate the (common) crc16c byte by byte
*  $ch is the next byte and $crc16c is the result from the last call, or 0xffff initially
*******************************************************************************/
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

/***************************************************************************//**
*	Konvertiert Plugwise Zeit in lokale Zeit und gibt den Timestamp zurück
*******************************************************************************/
function pwtime2unixtime($pwdate)
	{
	$jahr = 2000+hexdec(substr($pwdate,0,2));
	$monat =hexdec(substr($pwdate,2,2));
	$stunden = (hexdec(substr($pwdate,4,4))/60);
	$min=(hexdec(substr($pwdate,4,4))%60);
	$tag=floor(1+($stunden/24));
	$h = ($stunden%24);
	$offsetgmt = (date("Z")/3600);             // Offset zur GMT in Stunden
	return mktime($h + $offsetgmt, $min, 0, $monat, $tag, $jahr);

	}

/***************************************************************************//**
*	Binaer to Float
*******************************************************************************/
function bintofloat($in)
{
    $in=hexdec($in);
      $binary = str_pad(decbin($in),32,"0", STR_PAD_LEFT);
    $fb = $binary[0];
    $exp = bindec(substr($binary, 1, 8));
    $m = bindec(substr($binary, 9, 23));
    return pow(-1,$fb) * (1+$m/(pow(2,23))) * pow(2,$exp-127);
}


/***************************************************************************//**
*	Pulse zu kWh Umwandlung
*******************************************************************************/
function pulsesToKwh($value, $offRuis, $offTot, $gainA, $gainB)
	{
   if ($value == hexdec("FFFFFFFF") or $value == 0)
		{
      return 0;
      }
	else
		{
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


/***************************************************************************//**
*	Gibt das aktuelle Datum/Uhrzeit im Plugwise-Format zurück (Zeitzone UTC!)
*******************************************************************************/
function unixtime2pwtime() {

	$vorstellen = 1;

	$vorstellen = 0;  // keine Minute vorstellen

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


/***************************************************************************//**
*	Create eine Circle mit allen Variablen
*******************************************************************************/
function createCircle($mac, $parentID){
	
	GLOBAL $CircleGroups;
	GLOBAL $Profil_Plugwise_Leistung;
  	GLOBAL $Profil_Plugwise_Verbrauch;
  	GLOBAL $Profil_Plugwise_Switch;

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

  if ( $name != "" )
      IPS_SetName($item,$name);
      
	$CategoryIdApp = get_ObjectIDByPath('Program.IPSLibrary.app.hardware.Plugwise');
	$ScriptId = IPS_GetScriptIDByName('Plugwise_Controller', $CategoryIdApp );

	//$id1 = @IPS_GetVariableIDByName("Status",$item) ;
	//if ( $id1 == false )
	$id1 = CreateVariable("Status", 0, $item, 0, $Profil_Plugwise_Switch[0], false, true);
  $einaus = intval($einaus);
  
  if ( $einaus > 0 )
    {
    echo "\nBei $id1 Actionscript setzen"; 
    IPS_SetVariableCustomAction($id1,$ScriptId);

	 if ( $einaus > 1 )
    if ( IPS_VariableExists($einaus) )
	 	{
	 	echo "\nCreate Trigger:" ;
	 	CreateEvent($einaus,$einaus,$ScriptId,0);
		}
    
    }

	//$id2 = @IPS_GetVariableIDByName("Leistung",$item) ;
	//if ( $id2 == false )
		$id2 = CreateVariable("Leistung", 2, $item, 0, $Profil_Plugwise_Leistung[0], 0, 0);

	//$id3 = @IPS_GetVariableIDByName("Gesamtverbrauch",$item) ;
	//if ( $id3 == false )
		$id3 = CreateVariable("Gesamtverbrauch", 2, $item, 0, $Profil_Plugwise_Verbrauch[0], 0, 0);

	$Profil_Plugwise_Kosten = 'Plugwise_Kosten';
	$id4 = CreateVariable("Kosten", 2, $item, 0, $Profil_Plugwise_Kosten, 0, 0);
	

  $aggtype = 1;   // Zaehler
  if ( defined('AGGTYPE') )
        $aggtype = AGGTYPE;

    $archivlogging = true;
    if ( defined('ARCHIVLOGGING') )
        $archivlogging = ARCHIVLOGGING;

   if ($archivlogging == true)
      {
      if ( defined('AGGTYPELEISTUNG') )
      	$aggtype = AGGTYPELEISTUNG;

  		AC_SetLoggingStatus($archive_id  , $id2, True);   	// Logging einschalten
  		AC_SetAggregationType($archive_id, $id2,$aggtype); // Logging auf Type setzen
      IPS_ApplyChanges($archive_id);

  		if ( defined('AGGTYPEVERBRAUCH') )
      	$aggtype = AGGTYPEVERBRAUCH;

  		AC_SetLoggingStatus($archive_id  , $id3, True); 	// Logging einschalten
  		AC_SetAggregationType($archive_id, $id3, $aggtype);// Logging auf Type setzen
      IPS_ApplyChanges($archive_id);

  		AC_SetLoggingStatus($archive_id  , $id4, True); 	// Logging einschalten
  		//AC_SetAggregationType($archive_id, $id4, $aggtype);// Logging auf Type setzen
      IPS_ApplyChanges($archive_id);

      
		}

	//$myVar = CreateVariable("gaina",2,$item,0,"",0,0);
	//IPS_SetHidden($myVar, True);
	//$myVar = CreateVariable("gainb",2,$item,0,"", 0,0);
	//IPS_SetHidden($myVar, True);
	//$myVar = CreateVariable("offTotal",2,$item,0,"",0,0);
	//IPS_SetHidden($myVar, True);
	//$myVar = CreateVariable("offNoise",2,$item,0,"",0,0);
	//IPS_SetHidden($myVar, True);
   $myVar = CreateVariable("LogAddress", 1,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
   $myVar = CreateVariable("Error", 1,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
   $myVar = CreateVariable("LastMessage", 3,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);
   $myVar = CreateVariable("Kalibrierdaten", 3,$item,0,"",0,0);
	IPS_SetHidden($myVar, True);

	// Kalibrierungsdaten vom Circle abrufen
	PW_SendCommand("0026".$mac);

	// Zeit stellen
 	PW_SendCommand("0016".$mac.unixtime2pwtime());

	// Status abfragen
	PW_SendCommand("0012".$mac);
	PW_SendCommand("0023".$mac);

}

/***************************************************************************//**
*	Update die 2 HTMLBoxen im Webfront 
* 
*******************************************************************************/
function update_data1_data2_old()
	{

	$result      = find_id_toshow();
	$type			 = $result['TYPE'];
	$id 			 = intval($result['ID']);
	$idleistung  = intval($result['IDLEISTUNG']);
	$idgesamt 	 = intval($result['IDGESAMT']);
	$parent   	 = intval($result['PARENT']);


	//IPS_logMessage("....",$type."-".$parent."-".$idleistung."-".$idgesamt);
	update_data1data2_sub($parent,$idleistung,$idgesamt);
	
	}

/***************************************************************************//**
*	Update die 2 HTMLBoxen im Webfront und bei Systemsteuerung die Uebersicht
*  Aufgerufen von Timer (Controller)
*******************************************************************************/
function update_data1data2()
	{
   update_data1_data2();
	update_uebersicht_circles();
	}



/***************************************************************************//**
*	Update Uebersicht Circles Systemsteuerung
*******************************************************************************/
function update_uebersicht_circles()
	{
	GLOBAL $CircleGroups;
	GLOBAL $IdGraph;

	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.MENU";
   $IdMenu    = @get_ObjectIDByPath($VisuPath,true);
	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.GRAPH";
   $IdGraph   = @get_ObjectIDByPath($VisuPath,true);
	$AllgPath  = "Visualization.WebFront.Hardware.Plugwise.MENU.Allgemeines";
   $IdAllg    = get_ObjectIDByPath($AllgPath);

	$SystemstPath  = "Visualization.WebFront.Hardware.Plugwise.MENU.Systemsteuerung";
   $IdSystemst    = get_ObjectIDByPath($SystemstPath);

	$id = IPS_GetObjectIDByName('Systemsteuerung',$IdAllg);  // Systemsteuerung
  	if ( !$id ) return;
	if ( @GetValue($id) != 1 ) return;

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	$menupunkt = GetValue($id);
	
	$object = IPS_GetObject($id);
  	$ident = intval($object['ObjectIdent']);

	
   $hintergrundfarbe = "#9B9B9B";

   $imggroesse = "width='90' height='50'";


	$menuarray = array
	   (
	   array(true ,"menu_uebersicht.png"		,"menu_uebersicht_grau.png"		,0),
	   array(true ,"menu_letztedaten.png"		,"menu_letztedaten_grau.png"		,1),
	   array(true ,"menu_softwareversion.png"	,"menu_softwareversion_grau.png"	,2),
	   array(true ,"menu_hardwareversion.png"	,"menu_hardwareversion_grau.png"	,3),
	   array(true ,"menu_leistung.png"			,"menu_leistung_grau.png"			,4),
	   array(true ,"menu_verbrauch.png"			,"menu_verbrauch_grau.png"			,5),
	   array(true ,"menu_ping.png"				,"menu_ping_grau.png"				,6),
	   );


	$menu = "";
	$menu = $menu . "<table border='0' cellspacing='1' cellpadding='0' bgcolor=$hintergrundfarbe width='100%' height='20'>";
	$menu = $menu . "<tr>";

	$imgpath = "/user/Plugwise/images/";

	
	foreach ( $menuarray as $menuitem )
	   {
		$menu = $menu . "<td bgcolor=#000000 width='16%' cellspacing='0' cellpadding='0'>";

		if ( $menuitem[0] == false )
		   {
		   $altfile = IPS_GetKernelDir()."webfront\\user\\Plugwise\\images\\alt_".$menuitem[2];
		   if ( file_exists($altfile) )
		      $file = $imgpath . "alt_" . $menuitem[2];
			else
			   $file = $imgpath . $menuitem[2];

			$menu = $menu . "<img src='$file' ". $imggroesse ." >";
			}

		if ( $menuitem[0] == true and $menuitem[3] != $menupunkt)
		   {
		   $altfile = IPS_GetKernelDir()."webfront\\user\\Plugwise\\images\\alt_".$menuitem[2];
		   if ( file_exists($altfile) )
		      $file = $imgpath . "alt_" . $menuitem[2];
			else
			   $file = $imgpath . $menuitem[2];

		   
			$menu = $menu . "<img src='$file' ". $imggroesse ." onmouseover=\"this.style.cursor = 'pointer'\" ";
			$menu = $menu . "onclick=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\" ";
			$menu = $menu . "ontouchstart=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\">";
			}

		if ( $menuitem[3] == $menupunkt )
		   {
		   $altfile = IPS_GetKernelDir()."webfront\\user\\Plugwise\\images\\alt_".$menuitem[2];
		   if ( file_exists($altfile) )
		      $file = $imgpath . "alt_" . $menuitem[1];
			else
			   $file = $imgpath . $menuitem[1];


			$menu = $menu ."<img src='$file' ". $imggroesse ." onmouseover=\"this.style.cursor = 'pointer'\" ";
			$menu = $menu . "onclick=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\" ";
			$menu = $menu . "ontouchstart=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\">";
			}
		   

		$menu = $menu . "</td>";
	   }


	$menu = $menu . "</tr>";
	$menu = $menu . "</table>";
	//***************************************************************************

	$menu = $menu . "Seite ". ($ident + 1);

	// Erstelle Datenarray
	$data_array = array();
	for($x=0;$x<181;$x++)
		{
		$data_array[$x]['EXIST'] = false ;
		$data_array[$x]['CIRCLEID'] = "" ;
		$data_array[$x]['CIRCLENAME'] = "" ;
		$data_array[$x]['CIRCLESTATUS'] = getRandomBoolean() ;
		$data_array[$x]['CIRCLENEW'] = getRandomBoolean() ;
		$data_array[$x]['CIRCLEERROR'] = getRandomBoolean() ;
		$data_array[$x]['CIRCLESWVERSION'] = "SW?" ;
		$data_array[$x]['CIRCLEHWVERSION'] = "HW?" ;
		$data_array[$x]['CIRCLELASTSEEN'] = 0 ;
		$data_array[$x]['CIRCLEWATT'] = 0 ;
		$data_array[$x]['CIRCLEKWH'] = 0 ;
		$data_array[$x]['CIRCLEPINGMS'] = 0;
		$data_array[$x]['CIRCLEPINGRSSI1'] = 0;
		$data_array[$x]['CIRCLEPINGRSSI2'] = 0;

		}

	$circles = IPS_GetChildrenIDs($idCatCircles);
	$counter = 0;

	foreach ( $circles as $circle )
	   {
		$data_array[$counter]['EXIST'] = true ;

		$object = IPS_GetObject($circle);
	   $data_array[$counter]['CIRCLEID']  = $object['ObjectIdent'];
		$data_array[$counter]['CIRCLENEW'] = false;
		$data_array[$counter]['CIRCLEERROR'] = @GetValue(IPS_GetVariableIDByName('Error',$circle));
		$data_array[$counter]['CIRCLESTATUS'] = @GetValue(IPS_GetVariableIDByName('Status',$circle));
		$last_seen = @GetValue(IPS_GetVariableIDByName('LastMessage',$circle));
		$last_seen = intval($last_seen);
		$data_array[$counter]['CIRCLELASTSEEN'] = date('d.m.Y H:i:s',$last_seen);

		$watt = @number_format(GetValue(IPS_GetVariableIDByName('Leistung',$circle)),1,",","");
		$y = strlen($watt);
		for($x=$y;$x<6;$x++)
		   $watt = "&ensp;".$watt;
		$data_array[$counter]['CIRCLEWATT'] = $watt;

		$kwh = @number_format(GetValue(IPS_GetVariableIDByName('Gesamtverbrauch',$circle)),1,",","");
		$y = strlen($kwh);
		for($x=$y;$x<6;$x++)
		   $kwh = "&ensp;".$kwh;
		$data_array[$counter]['CIRCLEKWH'] = $kwh;


		$array = explode(",",$object['ObjectInfo']);
		if ( isset($array[0]) )
			$data_array[$counter]['CIRCLEHWVERSION'] = $array[0];
		if ( isset($array[1]) )
			$data_array[$counter]['CIRCLESWVERSION'] = $array[1];



		//suche richtigen Namen in der Config
		$data_array[$counter]['CIRCLENAME'] = $object['ObjectIdent'];
		$gefunden = false;
		foreach ( $CircleGroups as $configCircle )
			{
			if ( $object['ObjectIdent'] == $configCircle[0] )
				{
				$data_array[$counter]['CIRCLENAME'] =  $configCircle[1] ;
				$gefunden = true;
				break ;
				}
			}
		if ( $gefunden == false )
		   {
			$data_array[$counter]['CIRCLENEW'] = true;
		   }
		   
	   $info   = $object['ObjectInfo'];

		$counter = $counter + 1;

	   }

	// unbekannte neue Circles in Array einfuegen
   $file = 'plugwise_unknowncircles.log';
	$logdatei = IPS_GetKernelDir() . "logs\\Plugwise\\" . $file;
	if ( file_exists($logdatei) )
		{
		ini_set("auto_detect_line_endings", true);
		$newarray = file($logdatei,FILE_SKIP_EMPTY_LINES);
		$newarr = array_unique($newarray);
		   //print_r($arr);
		$newanzahl = count($newarr);
		foreach( $newarr as $unknowncircle )
		   {
		   $unknowncircle = strtok($unknowncircle,",");
			$data_array[$counter]['EXIST']      = true ;
			$data_array[$counter]['CIRCLEID']   = $unknowncircle;
			$data_array[$counter]['CIRCLENAME'] = $unknowncircle;
			$data_array[$counter]['CIRCLENEW']  = true;
			$counter = $counter + 1;
		   }
		
		}
	// alle eingefuegt


	// Pingdaten einfuegen wenn Menupunkt angewaehlt
	if ( $menupunkt == 6 )
	   {
   	$file = 'plugwiseping.log';
		$pingdatei = IPS_GetKernelDir() . "logs\\Plugwise\\" . $file;
		$pingarr = array();
		if ( file_exists($pingdatei) )
			{
			ini_set("auto_detect_line_endings", true);
			$pingarray = file($pingdatei,FILE_SKIP_EMPTY_LINES);
			$pingarr = array_unique($pingarray);
		
			}

		foreach($pingarr as $pingcircle )
	   	{
	   	$teile = explode(",",$pingcircle);
	   	$teil_id    = @$teile[1];
	   	$teil_rssi1 = @$teile[2];
	   	$teil_rssi2 = @$teile[3];
	   	$teil_ms    = @$teile[4];

			// suche in bereits erstellten array
			$counter = 0;
			foreach($data_array as $d_a )
			   {
				$testtext = $d_a['CIRCLEID']."-".$teil_id;
				
				//IPS_Logmessage("plugwise",$testtext);

				if($d_a['CIRCLEID'] == $teil_id)    // gefunden
				   {
	   			//IPS_Logmessage("plugwise",$d_a['CIRCLEID']);
					$data_array[$counter]['CIRCLEPINGMS']    = $teil_ms;
					$data_array[$counter]['CIRCLEPINGRSSI1'] = $teil_rssi1;
					$data_array[$counter]['CIRCLEPINGRSSI2'] = $teil_rssi2;
					
	            }
				$counter = $counter + 1;
	         }
			}
	   }
	// Ende Pingdaten


	
	$anzahlzeilen  = 9 ;
	$anzahlspalten = 3;
	if (defined('UEBERSICHTSPALTEN'))
	   $anzahlspalten = UEBERSICHTSPALTEN;
	if (defined('UEBERSICHTZEILEN'))
	   $anzahlzeilen = UEBERSICHTZEILEN;

	   
	$start_data    = 27 * $ident;
	
	$start_data    = $anzahlspalten * $anzahlzeilen * $ident;

	$hintergrundfarbe = '#FFFFFF';
	$text = "";
	$text = $text . "<table border='0' cellspacing='1' bgcolor=$hintergrundfarbe width='100%' height='200' cellspacing='0'  >";
	$hintergrundfarbe = '#000000';

	for($x=0;$x<$anzahlzeilen;$x++)
	   {
		$text = $text . "<tr>";
		for($y=0;$y<$anzahlspalten;$y++)
		   {
		   
		   $c_id     	 = $data_array[$start_data]['CIRCLEID'];
		   $c_name   	 = $data_array[$start_data]['CIRCLENAME'];
		   $c_error   	 = $data_array[$start_data]['CIRCLEERROR'];
		   $c_status 	 = $data_array[$start_data]['CIRCLESTATUS'];
		   $c_new    	 = $data_array[$start_data]['CIRCLENEW'];
		   $c_swv    	 = $data_array[$start_data]['CIRCLESWVERSION'];
		   $c_hwv     	 = $data_array[$start_data]['CIRCLEHWVERSION'];
		   $c_ls     	 = $data_array[$start_data]['CIRCLELASTSEEN'];
		   $c_watt   	 = $data_array[$start_data]['CIRCLEWATT'];
		   $c_kwh    	 = $data_array[$start_data]['CIRCLEKWH'];
		   $c_pingms    = $data_array[$start_data]['CIRCLEPINGMS'];
		   $c_pingrssi1 = $data_array[$start_data]['CIRCLEPINGRSSI1'];
		   $c_pingrssi2 = $data_array[$start_data]['CIRCLEPINGRSSI2'];


			$text = $text . "<td width='25%'  bgcolor=$hintergrundfarbe >";
			$circletext = "";

			if ( $data_array[$start_data]['EXIST'] == false )
				{
				$circletext = "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' ><br>&nbsp;";
				}
			else
			   {
				if ( $menupunkt == 0 )
					{
			   	if ( $c_new == false )
			      	{
						if ( $c_error == true  )
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";

			   		if ( $c_status == true  )
			   			$circletext = $circletext . "<img  src='/user/Plugwise/images/status_an.png'  align='absmiddle'>";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_aus.png' align='absmiddle'>";
						}
					
			   	if ( $c_new == true )
			      	{
						$circletext = $circletext . "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' >";
			   		$circletext = $circletext . "<img  src='/user/Plugwise/images/status_neu.png'  align='absmiddle'>";
						}

					$circletext = $circletext . "<FONT  SIZE='4'>&nbsp;&nbsp;&nbsp;" . substr($c_id,-7) . "</FONT>";

					$circletext = $circletext . "<br><center>" .$c_name ."</center>";

					}

				if ( $menupunkt == 1 ) // last seen
					{
			   	if ( $c_new == false )
			      	{
						if ( $c_error == true  )
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";

						$circletext = $circletext . "<FONT  SIZE='3'>&nbsp;&nbsp;".$c_ls."</FONT>";

						$circletext = $circletext . "<br><center>" .$c_name ."</center>";
						}
					else
						$circletext = "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' ><br>&nbsp;";

					}

			
				if ( $menupunkt == 2 ) // Softwareversion
					{
			   	if ( $c_new == false )
			      	{
						if ( $c_error == true  )
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";

						$circletext = $circletext . "<FONT  SIZE='3'>&nbsp;&nbsp;".$c_swv."</FONT>";

						$circletext = $circletext . "<br><center>" .$c_name ."</center>";
						}
					else
						$circletext = "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' ><br>&nbsp;";

					}

				if ( $menupunkt == 3 ) // Hardwareversion
					{
			   	if ( $c_new == false )
			      	{
						if ( $c_error == true  )
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";

						$circletext = $circletext . "<FONT  SIZE='3'>&nbsp;&nbsp;".$c_hwv."</FONT>";

						$circletext = $circletext . "<br><center>" .$c_name ."</center>";
						}
					else
						$circletext = "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' ><br>&nbsp;";

					}

				if ( $menupunkt == 4 ) // Watt
					{
			   	if ( $c_new == false )
			      	{
						if ( $c_error == true  )
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";

			   		if ( $c_status == true  )
			   			$circletext = $circletext . "<img  src='/user/Plugwise/images/status_an.png'  align='absmiddle'>";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_aus.png' align='absmiddle'>";
						}

			   	if ( $c_new == true )
			      	{
						$circletext = $circletext . "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' >";
			   		$circletext = $circletext . "<img  src='/user/Plugwise/images/status_neu.png'  align='absmiddle'>";
						}

					$circletext = $circletext . "<FONT  SIZE='4'>&nbsp;&nbsp;&nbsp;" . $c_watt . " Watt</FONT>";

					$circletext = $circletext . "<br><center>" .$c_name ."</center>";

					}

				if ( $menupunkt == 5 ) // Verbrauch kWh
					{
			   	if ( $c_new == false )
			      	{
						if ( $c_error == true  )
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";

			   		if ( $c_status == true  )
			   			$circletext = $circletext . "<img  src='/user/Plugwise/images/status_an.png'  align='absmiddle'>";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_aus.png' align='absmiddle'>";
						}

			   	if ( $c_new == true )
			      	{
						$circletext = $circletext . "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' >";
			   		$circletext = $circletext . "<img  src='/user/Plugwise/images/status_neu.png'  align='absmiddle'>";
						}

					$circletext = $circletext . "<FONT  SIZE='4'>&nbsp;&nbsp;&nbsp;" . $c_kwh . " kWh</FONT>";

					$circletext = $circletext . "<br><center>" .$c_name ."</center>";

					}

				if ( $menupunkt == 6 ) // Pinganzeige
					{
					
			   	if ( $c_new == false )
			      	{
			      	$circletext = "<table border='0' cellspacing='0'  bgcolor=$hintergrundfarbe width='100%' height='15'   >";
			      	$circletext = $circletext . "<td width='25%'>";
						if ( $c_error == true  )
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
						else
							$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";
							
						$circletext = $circletext . "</td>";
						$circletext = $circletext . "<td title='incomingLastHopRssiTarget' align='absmiddle' width='25%'><FONT  SIZE='3'>".$c_pingrssi1;
						$circletext = $circletext . "</td>";
						$circletext = $circletext . "<td title='lastHopRssiSource' align='absmiddle' width='25%'><FONT  SIZE='3'>".$c_pingrssi2;
						$circletext = $circletext . "</td>";

						$circletext = $circletext . "<td align='absmiddle' width='25%'><FONT  SIZE='3'> ".$c_pingms."<FONT  SIZE='2'>  ms";
						$circletext = $circletext . "</td>";
                  $circletext = $circletext . "</table>";

						//$circletext = $circletext . "<FONT  SIZE='4'>&nbsp;&nbsp;".$c_pingms." ms</FONT>";
						$circletext = $circletext . "<center>" .$c_name ."</center>";
						
						}
					else
						$circletext = "<img  src='/user/Plugwise/images/status_o.png' align='absmiddle' ><br>&nbsp;";

					}




				}
         
			$start_data = $start_data + 1;
         $text = $text . "<span style='font-family:arial;color:white;font-size:14px;'>$circletext</span>";
			$text = $text . "</td>";
		   }
		$text = $text . "</tr>";
	   }

			

	$text = $text . "</table>";

/*
	$text = "";
	$text = $text . "<table border='0' cellspacing='1' bgcolor=$hintergrundfarbe width='100%' height='200' cellspacing='0'  >";

	$anzahl = 0;

   
	$anzahl  = count(IPS_GetChildrenIDs($idCatCircles));
   $circles = IPS_GetChildrenIDs($idCatCircles);

	$counter = 0;
	
	for ( $y = 0;$y<9;$y++)
		{
		$gefunden = false;
		$text = $text . "<tr>";

		for ( $x = 0;$x<3;$x++)
	   	{
	   	$circle = 0;
	   	$name = "FFFFFFFFFFFFFFFF";
	   	$name = "";
			$hintergrundfarbe = '#000000';
			$statustext = "";
			$circletext = ".";
			
			$circletext = "<img  src='/user/Plugwise/images/status_o.png'>";

	   	if ( $counter < $anzahl )
	   	   {
	   	   $id     = $circles[$counter];
	   		$circle = IPS_GetObject($id);
	   		$info   = $circle['ObjectInfo'];
	   		$ident  = $circle['ObjectIdent'];
            $error  = GetValue(IPS_GetVariableIDByName('Error',$id));
            $status = GetValue(IPS_GetVariableIDByName('Status',$id));
            $lastm  = GetValue(IPS_GetVariableIDByName('LastMessage',$id));
				$mac    = "?";
				
				
				//suche richtigen Namen in der Config
				$gefunden = false;
				foreach ( $CircleGroups as $configCircle )
				   {

				   if ( $ident == $configCircle[0] )
				      {
						$name  =  $configCircle[1] ;
						$mac   = substr($configCircle[0],-4);

						
						
						$gefunden = true;
						break ;
						}

				   }

				}

			if ( $gefunden )
			   {
			   $circletext = "";

			   if ( $error == true )
			   	$circletext = $circletext . "<img  src='/user/Plugwise/images/status_offline.png' align='absmiddle' >";
				else
					$circletext = $circletext . "<img  src='/user/Plugwise/images/status_online.png'  align='absmiddle'>";
				
			   if ( $status == true )
			   	$circletext = $circletext . "<img  src='/user/Plugwise/images/status_an.png'  align='absmiddle'>";
				else
					$circletext = $circletext . "<img  src='/user/Plugwise/images/status_aus.png' align='absmiddle'>";
				
				$circletext = $circletext . "<FONT  SIZE='4'>&nbsp;&nbsp;&nbsp;" . substr($ident,-7) . "</FONT><br><center>" .$name ."</center>";
				
				}
			else
			   {
            $circletext = $circletext . "<img  src='/user/Plugwise/images/status_neu.png'>";

				$circletext = $circletext . substr($ident,-7) . "<br>" . IPS_GetName($id) ;
				}
				
				
				
			$text = $text . "<td  width='25%'  bgcolor=$hintergrundfarbe style='text-align:left;'>";
			$text = $text . "<span style='font-family:arial;color:white;font-size:14px;'>$circletext</span>";
			$text = $text . "</td>";
			$counter++;

	   	}
		$text = $text . "</tr>";

	 }

	$text = $text . "</table>";

	*/

	$id = IPS_GetObjectIDByIdent('Uebersicht',$IdGraph);  // Uebersicht Circles

	$html = $menu . "<br>" . $text;

	SetValueString($id,$html);
	
	}

    function getRandomBoolean() {
    return .01 * rand(0, 100) >= .5;
    }
/***************************************************************************//**
*	Update Uebersicht nur bei Systemsteuerung
*******************************************************************************/
function update_uebersicht1()
	{
	GLOBAL $CircleGroups;
	GLOBAL $IdGraph;
	
	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.MENU";
   $IdMenu    = @get_ObjectIDByPath($VisuPath,true);
	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.GRAPH";
   $IdGraph   = @get_ObjectIDByPath($VisuPath,true);
	$AllgPath  = "Visualization.WebFront.Hardware.Plugwise.MENU.Allgemeines";
   $IdAllg    = get_ObjectIDByPath($AllgPath);

	$SystemstPath  = "Visualization.WebFront.Hardware.Plugwise.MENU.Systemsteuerung";
   $IdSystemst    = get_ObjectIDByPath($SystemstPath);

	$id = IPS_GetObjectIDByName('Systemsteuerung',$IdAllg);  // Systemsteuerung
  	if ( !$id ) return;
	if ( @GetValue($id) != 1 ) return;

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	$menupunkt = GetValue($id);
	if ( $menupunkt < 0 or $menupunkt > 5 )
	   {
		$id = IPS_GetObjectIDByIdent('Uebersicht',$IdGraph);  // Uebersicht
   	SetValueString($id,"");
   	return;
	   }
	   
   $hintergrundfarbe = "#FF0000";
   
	$text = "";

	$text = $text . "<table border='0' cellspacing='3' bgcolor=$hintergrundfarbe width='100%' height='200' cellspacing='0'  >";
	$anzahl = 0;

	$anzahl  = count(IPS_GetChildrenIDs($idCatCircles));
   $circles = IPS_GetChildrenIDs($idCatCircles);

	//***************************************************************************
	// Menupunkte 1 - 4
	//***************************************************************************
	if ( $menupunkt < 5 )
	   {
	$counter = 0;
	for ( $y = 0;$y<24;$y++)
	   {
		$text = $text . "<tr>";
		$text1 = "";
		$text2 = "";

		for ( $x = 0;$x<4;$x++)
	   	{
	   	$circle = 0;
	   	$name = "FFFFFFFFFFFFFFFF";
	   	$name = "";
			$hintergrundfarbe = '#000000';
			$statustext = "";
			$text1 ="-";
			$text2 ="-";
	   	if ( $counter < $anzahl )
	   	   {
	   	   $id     = $circles[$counter];
	   		$circle = IPS_GetObject($id);
	   		$info   = $circle['ObjectInfo'];
	   		$name   = $circle['ObjectIdent'];
            $error  = @GetValue(IPS_GetVariableIDByName('Error',$id));
            $status = @GetValue(IPS_GetVariableIDByName('Status',$id));
            $lastm  = @GetValue(IPS_GetVariableIDByName('LastMessage',$id));
				$mac    = "?";
				//suche richtigen Namen in der Config
				foreach ( $CircleGroups as $configCircle )
				   {

				   if ( $name == $configCircle[0] )
				      {
						$name  =  $configCircle[1] ;
						$mac   = substr($configCircle[0],-4);

						$namemac = $mac . " - " . $name;
						break ;
						}

				   }

				switch( $menupunkt)
				   {
				   case 0 :		if ( $error == 0 )
										$hintergrundfarbe = '#009900';
									else
										$hintergrundfarbe = '#CC0000';
									$text2 = $name;
				               break;

				   case 1 :		if ( $status  == 0 )
										$hintergrundfarbe = '#003366';
									else
										$hintergrundfarbe = '#33CC00';
									$text2 = $name;
				               break;

					case 2 :    $array = explode(",",$info);
									$mac = $mac . " - ";
									if ( isset($array[0]) )
										$mac = $mac . $array[0];
									$text2 = $mac;
					            break;

					case 3 :		$array = explode(",",$info);
                           $mac = $mac . " - ";
									if ( isset($array[1]) )
										$mac = $mac . $array[1];
									$text2 = $mac;
					            break;

					case 4 :    $mac = $mac . " - ";

                           $text2 = $mac . $lastm;
					            break;

				   default:    $text1 = ""; $text2 = ""; break;
					}

	   	   }



			$text = $text . "<td  width='25%'  bgcolor=$hintergrundfarbe style='text-align:left;'>";
			$text = $text . "<span style='font-family:arial;color:white;font-size:12px;'>$text1</span>";
			$text = $text . "<span style='font-family:arial;color:white;font-size:12px;'>$text2</span>";
			$text = $text . "</td>";
			$counter++;
			
	   	}
		$text = $text . "</tr>";

	 }
	}
	//***************************************************************************
	// Menupunkt 5 - unbekannte Circles anzeigen
	//***************************************************************************
	if ( $menupunkt == 5 )
	   {
   	$file = 'plugwise_unknowncircles.log';
		$logdatei = IPS_GetKernelDir() . "logs\\Plugwise\\" . $file;
		if ( file_exists($logdatei) )
		   {
		   ini_set("auto_detect_line_endings", true);
		   $array = file($logdatei,FILE_SKIP_EMPTY_LINES);
		   $arr = array_unique($array);
		   //print_r($arr);
			$anzahl = count($arr);
			
			$counter = 0;
			for ( $y = 0;$y<8;$y++)
	   		{
				$text = $text . "<tr>";

				for ( $x = 0;$x<4;$x++)
	   			{
					$text1 = "-";
					$text2 = "";

	   			$hintergrundfarbe = '#000000';
					if ( $counter < $anzahl  )
						$text1 = trim($arr[$counter]);
					if ( strlen($text1) != 18 )
					   $text1 = "-";
					else
					   {
					   $type  = substr($text1,-1);
					   if ( $type == 0 )
					      {
					      $hintergrundfarbe = '#CC6666';
							$text2 = " NEW!";
							}
						if ( $type == 1 )
							{
					      $hintergrundfarbe = '#6699FF';
                     $text2 = " ?";
							}
					   $text1 = substr($text1,0,16);
					   }
					
					$text = $text . "<td  width='25%'  bgcolor=$hintergrundfarbe style='text-align:left;'>";
					$text = $text . "<span style='font-family:arial;color:white;font-size:12px;'>$text1</span>";
					$text = $text . "<span style='font-family:arial;color:white;font-size:12px;'>$text2</span>";
					$text = $text . "</td>";
					$counter++;
	   			}
				$text = $text . "</tr>";

				}

		   }

		}
		
	$text = $text . "</table>";
		
	$id = IPS_GetObjectIDByIdent('Uebersicht',$IdGraph);  // Uebersicht Circles

   SetValueString($id,$text);

	}



/***************************************************************************//**
*  
*******************************************************************************/
function get_count_ingruppe($id,$id1,$name)
	{
	GLOBAL $CircleGroups;
   GLOBAL $ExterneStromzaehlerGroups;

	
	$ingruppe = false;

	if ( $id != 0 )
	   {
		$object = IPS_GetObject($id);
		$ident = $object['ObjectIdent'];

		foreach ($CircleGroups as $circle)
			if ( $circle[0] != "" )
	   		if ( $circle[0] == $ident )
	   	   	{
					$ingruppe = true;
					if ( isset($circle[8]) )
						$ingruppe = $circle[8];
			   	}
		}
	else
	   {
		foreach ($ExterneStromzaehlerGroups as $extern)
			if ( $extern[0] != "" )
	   		if ( $extern[0] == $name )
	   	   	{
					$ingruppe = false;
					if ( isset($extern[8]) )
						$ingruppe = $extern[8];
			   	}

	   }


	return $ingruppe;

	}

function get_count_ingesamt($id,$id1,$name)
	{
	GLOBAL $CircleGroups;
   GLOBAL $ExterneStromzaehlerGroups;

   $ingesamt = false;

	if ( $id != 0 )
	   {
		$object = IPS_GetObject($id);
		$ident = $object['ObjectIdent'];

		foreach ($CircleGroups as $circle)
			if ( $circle[0] != "" )
	   		if ( $circle[0] == $ident )
	   	   	{
					$ingesamt = true;
					if ( isset($circle[7]) )
						$ingesamt = $circle[7];
			   	}
		}
	else
	   {
		foreach ($ExterneStromzaehlerGroups as $extern)
			if ( $extern[0] != "" )
	   		if ( $extern[0] == $name )
	   	   	{
					$ingesamt = true;
					if ( isset($extern[7]) )
						$ingesamt = $extern[7];
			   	}

	   }
	   


	return $ingesamt;


	}

/***************************************************************************//**
*	Update die 2 HTMLBoxen im Webfront ( Sub )
*******************************************************************************/
function update_data1_data2()
	{
	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
	
	$data1id = 0;
	$data2id = 0;

	$csspath    = "/user/Plugwise/";

	$Data1Path  = "Visualization.WebFront.Hardware.Plugwise.DATA1";
   $IdData1    = @get_ObjectIDByPath($Data1Path,true);
	$Data2Path  = "Visualization.WebFront.Hardware.Plugwise.DATA2";
   $IdData2    = @get_ObjectIDByPath($Data2Path,true);



	$result      = find_id_toshow();
	$type			 = $result['TYPE'];
	$id 			 = intval($result['ID']);
	$idleistung  = intval($result['IDLEISTUNG']);
	$idgesamt 	 = intval($result['IDGESAMT']);
	$parent   	 = intval($result['PARENT']);
	$objectname  = $result['OBJECTNAME'];


	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		$object = IPS_GetObject($child);
		if ( $object['ObjectIdent'] == "WEBDATA1" )
		   {
         $data1id = $child;
			}
		}
	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		$object = IPS_GetObject($child);
		if ( $object['ObjectIdent'] == "WEBDATA2" )
		   {
         $data2id = $child;
			}
		}


	if ( $data1id == 0 or $data2id == 0)
	   return;
	   
	
	$error      = @GetValue(IPS_GetVariableIDByName('Error',$parent));
		
	$dateleistung = IPS_GetVariable($idleistung);
	$dateleistung = date('H:i:s',$dateleistung['VariableUpdated']);
		
	$leistung = round(GetValue($idleistung),2);
   $gesamt   = round(GetValue($idgesamt),2);

   $akt_tk   = aktuelle_kosten($type,$parent,$objectname,$leistung);  // aktuelle Kosten und Tarif
   $kosten   = $akt_tk['KOSTEN'];
   $akt_tarif= $akt_tk['TARIF'];
   $kt_preis = $akt_tk['PREISKWH'];

	$akt_tarif= $akt_tarif . " " . $kt_preis ." Cent/kWh";

   $waehrung = "Cent/h";
   $vergleich = 10 ;
      
   if ( $kosten > $vergleich )
      {
      $kosten = $kosten/100;
      $waehrung = "Euro/h";
      }
   $kosten = round($kosten,2);
	
   $array = statistikdaten($idgesamt);
  
   $verbrauch_heute   = $array['VERBRAUCH_HEUTE'];
   $verbrauch_gestern = $array['VERBRAUCH_GESTERN'];


	
	if ( get_count_ingruppe($parent,$id,$objectname) )
      $img_ingruppe = "<img src='/user/Plugwise/blitz2.png' title='Daten in Gruppe enthalten'>";
	else
	   $img_ingruppe = "<img src='/user/Plugwise/leer.png' >";

	if ( get_count_ingesamt($parent,$id,$objectname) )
      $img_ingesamt = "<img src='/user/Plugwise/blitz.png' title='Daten in Gesamt enthalten'>";
	else
	   $img_ingesamt = "<img src='/user/Plugwise/leer.png' >";

   
	$hintergrundfarbe = "#003366";
	$fontsize = "17px";
		
	$html1 = "<head><link rel='stylesheet' type='text/css' href='".$csspath."Plugwise.css'></head><body>";
	$html1 = $html1 . "<table border='0' class='table'>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='zeitschrift'>Aktuell</td>";
	$html1 = $html1 . "<td class='zeitdaten'>$dateleistung</td>";
	$html1 = $html1 . "<td class='zeitschrift'>Uhr</td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='leistungschrift'>Leistung</td>";
	$html1 = $html1 . "<td class='leistungdaten'>$leistung</td>";
	$html1 = $html1 . "<td class='leistungschrift'>Watt</td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='kostenschrift'>Kosten</td>";
	$html1 = $html1 . "<td class='kostendaten'>$kosten</td>";
	$html1 = $html1 . "<td class='kostenschrift'>$waehrung</td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='tarifschrift'>Tarif</td>";
	$html1 = $html1 . "<td class='tarifdaten'>$akt_tarif</td>";
	$html1 = $html1 . "<td class='tarifschrift'>$img_ingesamt$img_ingruppe</td>";
	$html1 = $html1 . "</tr>";

	$html1 = $html1 . "</table></body>";

	if ( $error != 0 )
		$html1 = "<img  width=50% height=50% src='/user/Plugwise/images/circleausgefallen.png' align='absmiddle' >";
		
		
   SetValueString($data1id,$html1);
	$fontsize  = "16px";
	$fontsize1 = "20px";

	$html1 = "<head><link rel='stylesheet' type='text/css' href='".$csspath."Plugwise.css'></head><body>";
	$html1 = $html1 . "<table border='0' class='table'>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='verbrauchschrift'>Verbrauch Gesamt</td>";
	$html1 = $html1 . "<td class='verbrauchdaten'>$gesamt</td>";
	$html1 = $html1 . "<td class='verbrauchschrift'>kWh</td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='verbrauchschrift'>Verbrauch Heute</td>";
	$html1 = $html1 . "<td class='verbrauchdaten'>$verbrauch_heute</td>";
	$html1 = $html1 . "<td class='verbrauchschrift'>kWh</td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='verbrauchschrift'>Verbrauch Gestern</td>";
	$html1 = $html1 . "<td class='verbrauchdaten'>$verbrauch_gestern</td>";
	$html1 = $html1 . "<td class='verbrauchschrift'>kWh</td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='verbrauchschrift'></td>";
	$html1 = $html1 . "<td class='verbrauchdaten'></td>";
	$html1 = $html1 . "<td class='verbrauchschrift'></td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "<tr>";
	$html1 = $html1 . "<td class='verbrauchschrift'></td>";
	$html1 = $html1 . "<td class='verbrauchdaten'></td>";
	$html1 = $html1 . "<td class='verbrauchschrift'></td>";
	$html1 = $html1 . "</tr>";
	$html1 = $html1 . "</table></body>";

   SetValueString($data2id,$html1);

	}


/***************************************************************************//**
*	Statistikdaten liefern ( Heute,Gestern )
*******************************************************************************/
function statistikdaten($gesamtid)
	{
	
   $instances = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
	$archive   = $instances[0];

	$akt_kt['VERBRAUCH_HEUTE'] = "?";
	$akt_kt['VERBRAUCH_GESTERN'] = "?";
	$akt_kt['PREISKWH'] = "?";

	$start = mktime(0,0,0,date("m"),date("d")-1,date("Y"));
	$ende  = mktime(23,59,59,date("m"),date("d")-1,date("Y"));
	$data  = @AC_GetLoggedValues($archive,$gesamtid,$start,$ende,-1);
   /*
	foreach($data as $d)
		{
   	echo "\n";
   	echo date('d.m.Y H:i:s',$d['TimeStamp']);
   	echo " - " .$d['Value'];
		}
	*/
	$diff_wert = 0;
	if ( $data )
	   {
   	
		$ende_wert = floatval($data[0]['Value']);
		$start_wert  = floatval($data[count($data)-1]['Value']);
		$diff_wert  = $ende_wert - $start_wert ;
		}
	if ( $diff_wert < 0 ) $diff_wert = 0;

	$array['VERBRAUCH_GESTERN'] = round($diff_wert,2);
	
	$start = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$ende  = mktime(23,59,59,date("m"),date("d"),date("Y"));
	$data = AC_GetLoggedValues($archive,$gesamtid,$start,$ende,-1);
	/*
	foreach($data as $d)
		{
   	echo "\n";
   	echo date('d.m.Y H:i:s',$d['TimeStamp']);
   	echo " -- " .$d['Value'];

		}
	*/
	
	if ( $data )
	   {
		$ende_wert = floatval($data[0]['Value']);
		$start_wert  = floatval($data[count($data)-1]['Value']);
		//echo "[".$start_wert."-".$ende_wert;
		$diff_wert  = $ende_wert - $start_wert ;
		}
	if ( $diff_wert < 0 ) $diff_wert = 0;
		
	$array['VERBRAUCH_HEUTE'] = round($diff_wert,2);

	//print_r($array);
	return $array;
	
	}

	
/***************************************************************************//**
*	Liefert die aktuellen Kosten nach Tarif
*******************************************************************************/
function aktuelle_kosten($type,$parent,$objectname,$leistung)
	{
	GLOBAL $Stromtarife;
	GLOBAL $CircleGroups;
	GLOBAL $ExterneStromzaehlerGroups;
	GLOBAL $SystemStromzaehlerGroups;
	
	//$type = "-".$type."-";
	
	//IPS_logMessage("....",$type."-".$parent."-".$objectname."-".$leistung);

	$debug = false;
	
	$akt_kt['KOSTEN'] = 0;
	$akt_kt['TARIF'] = "";

	$tarifgruppe = false;

	foreach( $CircleGroups as $circle )
			$standardtarifgruppe = $circle[6] ;   // letzter Eintrag nehmen als Standard



	if ( $type == "ZAEHLER" )  // suche bei Circles
	   {
	   $object = IPS_GetObject($parent);
	   $ident = $object['ObjectIdent'];
		foreach( $CircleGroups as $circle )
	   	{//Tarifgruppe fuer diesen Circle suchen
	   	if ( $circle[0] == $ident )
	      	{
				$tarifgruppe = $circle[6] ;
				break ;
				}
	   	}
		//if ( $tarifgruppe != false )
	   	//IPS_LogMessage("tarif gefnden fuer Circle",$ident."-".$tarifgruppe);
		}
		
	if ( $type == "EXTERN" )  // suche bei Extern
	   {
		foreach( $ExterneStromzaehlerGroups as $extern )
	   	{//Tarifgruppe fuer diesen Circle suchen
	   	if ( $extern[0] == $objectname )
	      	{
				$tarifgruppe = $extern[6] ;
				break ;
				}
	   	}
		//if ( $tarifgruppe != false )
	   	//IPS_LogMessage("Tarif gefunden fuer Extern",$objectname."-".$tarifgruppe);
		}

	if ( $type == "GRUPPE" )  // suche Gruppe
	   {
		if ( $objectname == "Sonstige" )
			$tarifgruppe = $standardtarifgruppe;

		foreach( $ExterneStromzaehlerGroups as $extern ) // suche in Extern
	   	{//Tarifgruppe fuer diesen Circle suchen
	   	//IPS_LogMessage("SucheGruppe",$objectname."-".$extern[1]);
	   	if ( ($extern[1]) == $objectname )
	      	{
				$tarifgruppe = $extern[6] ;
				break ;
				}
	   	}
		foreach( $CircleGroups as $circle ) // suche in Circles
	   	{//Tarifgruppe fuer diesen Circle suchen
	   	//IPS_LogMessage("SucheGruppe",$objectname."-".($circle[2]));
	   	if ( ($circle[2]) == $objectname )
	      	{
				$tarifgruppe = $circle[6] ;
				break ;
				}
	   	}

		//if ( $tarifgruppe != false )
	   	//IPS_LogMessage("Tarif gefunden Gruppe",$objectname."-".$tarifgruppe);

		}




	if ( $type == "GESAMT" )  // Gesamtzaehler
		{
		foreach( $SystemStromzaehlerGroups as $system )
	   	{//Tarifgruppe fuer Gesamt suchen
	   	if ( $system[1] == "SYSTEM_MAIN" )
				{
				$tarifgruppe = $system[6];
				break ;
				}
		   }
		//if ( $tarifgruppe != false )
	   	//IPS_LogMessage("Tarif gefunden fuer Gesamt",$tarifgruppe);

		}
		

	
	if ( !$tarifgruppe )
		{
	   IPS_logMessage("Keine Tarifgruppe gefunden fuer:",$type."-".$objectname."-".$leistung);
	   return(0);           // Keine Tarifgruppe gefunden
		}

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



/***************************************************************************//**
*	Liste der nicht konfigurierten Circles
*******************************************************************************/
function unknowncircles($text,$delete = false,$file = 'plugwise_unknowncircles.log' )
	{

	$ordner = IPS_GetKernelDir() . "logs\\Plugwise";
   if ( !is_dir ( $ordner ) )
		mkdir($ordner);

   if ( !is_dir ( $ordner ) )
	   return;

	$logdatei = IPS_GetKernelDir() . "logs\\Plugwise\\" . $file;

	if ( $delete )
	   {
	   @unlink($logdatei);
		}
	else
	   {
		$datei = fopen($logdatei,"a+");
		fwrite($datei, $text . chr(13));
		fclose($datei);
		}
		
	}

/***************************************************************************//**
*	Gruppe finden fuer Ident
*******************************************************************************/
function find_group($ident="")
	{
	GLOBAL $CircleGroups;
	
	$group = "";
	
	foreach($CircleGroups as $circle)
	   {
	   if ( $circle[0] == $ident )
	      { $group = $circle[2]; break; }
	   }
	   
	//IPS_LogMessage("...",$ident."-".$group);
	return $group;
	}
	

/***************************************************************************//**
*	Mysql-Anbindung
*******************************************************************************/
function mysql_add($table,$time,$geraet,$wert,$id=0,$group="",$logadresse="00000000")
	{
	$text = $table."-".$geraet."-".$wert;
	$logadresse = strtoupper($logadresse);
	
	//IPS_LogMessage("MYSQL",$text);
	
	$mysql = false;
	if ( defined('MYSQL_ANBINDUNG') )
	   $mysql = MYSQL_ANBINDUNG;
	if ( $mysql == false ) return;

   $server = @mysql_connect(MYSQL_SERVER,MYSQL_USER,MYSQL_PASSWORD);
	if ( !$server )
	   {
	   IPS_Logmessage("Plugwise MySql","Server nicht bereit");
		return;
		}

   $db_exist = @mysql_select_db(MYSQL_DATENBANK, $server);
	if (!$db_exist)
		{
	   IPS_Logmessage("Plugwise MySql","Datenbank wird angelegt");
		$mysqlstring = 'CREATE DATABASE ' . MYSQL_DATENBANK .";";
    	$db_exist = mysql_query($mysqlstring);
		}

	if ( !$db_exist )
	   {
	   IPS_Logmessage("Plugwise MySql","Datenbank nicht bereit");
		return;
		}

	if ( MYSQL_TABELLE_LEISTUNG != "" )
	   {
		$result = mysql_query("SHOW TABLES LIKE '".MYSQL_TABELLE_LEISTUNG."'");
		if (@mysql_num_rows($result) == 0)
  			{
 			IPS_Logmessage("Plugwise MySql","Tabelle nicht vorhanden wird erstellt");
			$sql = "CREATE TABLE `" . MYSQL_TABELLE_LEISTUNG . "` ";
			$sql = $sql . "( `ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , ";
			$sql = $sql . "`TIMESTAMP` TIMESTAMP NOT NULL ,";
			$sql = $sql . "`SSID` VARCHAR( 150 )NOT NULL UNIQUE,";
			$sql = $sql . "`DATUMUHRZEIT` DATETIME NOT NULL ,";
			$sql = $sql . "`GERAETEID` INT  ,";
			$sql = $sql . "`GERAETENAME` VARCHAR( 150 )NOT NULL ,";
			$sql = $sql . "`GERAETEGRUPPE` VARCHAR( 150 ),";
   		$sql = $sql . "`LEISTUNG` FLOAT NOT NULL ";
   		$sql = $sql . " ) ENGINE = MYISAM ;";

			$tab_exist = mysql_query($sql);
			}
		else
	   	$tab_exist = true;

		if ( !$tab_exist )
	   	{
	   	IPS_Logmessage("Plugwise MySql","Fehler bei Tabellenerstung Leistung");
			return;
			}
		}


	if ( MYSQL_TABELLE_GESAMT != "" )
		{
		$result = mysql_query("SHOW TABLES LIKE '".MYSQL_TABELLE_GESAMT."'");
		if (@mysql_num_rows($result) == 0)
  			{
 			IPS_Logmessage("Plugwise MySql","Tabelle nicht vorhanden wird erstellt");
			$sql = "CREATE TABLE `" . MYSQL_TABELLE_GESAMT . "` ";
			$sql = $sql . "( `ID` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY , ";
			$sql = $sql . "`TIMESTAMP` TIMESTAMP NOT NULL ,";
			$sql = $sql . "`SSID` VARCHAR( 150 )NOT NULL UNIQUE,";
			$sql = $sql . "`DATUMUHRZEIT` DATETIME NOT NULL ,";
			$sql = $sql . "`GERAETEID` INT  ,";
			$sql = $sql . "`GERAETENAME` VARCHAR( 150 )NOT NULL ,";
			$sql = $sql . "`GERAETEGRUPPE` VARCHAR( 150 ) ,";
   		$sql = $sql . "`GESAMTVERBRAUCH` FLOAT NOT NULL ";
   		$sql = $sql . " ) ENGINE = MYISAM ;";

			$tab_exist = mysql_query($sql);
			}
		else
	   	$tab_exist = true;

		if ( !$tab_exist )
	   	{
	   	IPS_Logmessage("Plugwise MySql","Fehler bei Tabellenerstung Gesamt");
			return;
			}
		}



	if ( $table == MYSQL_TABELLE_LEISTUNG AND MYSQL_TABELLE_LEISTUNG != "")
	   {
	   $gefunden = false;
	   
	   $result = mysql_query("SHOW COLUMNS FROM $table ");
		if (!$result)
			{
    		echo 'Konnte Abfrage nicht ausführen: ' . mysql_error();
    		exit;
			}
		if (mysql_num_rows($result) > 0)
			{
    		while ($row = mysql_fetch_assoc($result))
			 	{
        		$item = $row['Field'];
        		//IPS_LogMessage("Plugwise MySql",$item);
				if ( $item == 'LOGADRESSE' ) $gefunden = true;
        		   
    			}
			}
		if ( $gefunden == false )
		   {
	   	$sql = "ALTER TABLE ".$table." ADD `LOGADRESSE` VARCHAR( 150 );";
      	mysql_query($sql);
      	if ( mysql_error($server) )
      		{
      		$error =  mysql_errno($server) . ": " . mysql_error($server) . "\n";
				IPS_LogMessage("Plugwise MySql",$error);
				}
			}


		$sql = "";
		$sql = $sql . "INSERT INTO ".$table." ";
		$sql = $sql . "(`SSID`,`DATUMUHRZEIT`,`GERAETENAME`,`LEISTUNG`,`GERAETEID`,`GERAETEGRUPPE`,`LOGADRESSE`) ";
		$sql = $sql . "VALUES ('".$time."-".$geraet."','".$time."','".$geraet."',".$wert.",".$id.",'".$group."','".$logadresse."'); ";
		//IPS_LogMessage("Plugwise MySql",$sql);
      mysql_query($sql);
      if ( mysql_error($server) )
      	{
      	$error =  mysql_errno($server) . ": " . mysql_error($server) . "\n";
			IPS_LogMessage("Plugwise MySql",$error);
			}

 	   }
	
	if ( $table == MYSQL_TABELLE_GESAMT AND MYSQL_TABELLE_GESAMT != "")
	   {
	   $gefunden = false;

	   $result = mysql_query("SHOW COLUMNS FROM $table ");
		if (!$result)
			{
    		echo 'Konnte Abfrage nicht ausführen: ' . mysql_error();
    		exit;
			}
		if (mysql_num_rows($result) > 0)
			{
    		while ($row = mysql_fetch_assoc($result))
			 	{
        		$item = $row['Field'];
        		//IPS_LogMessage("Plugwise MySql",$item);
				if ( $item == 'LOGADRESSE' ) $gefunden = true;

    			}
			}
		if ( $gefunden == false )
		   {
	   	$sql = "ALTER TABLE ".$table." ADD `LOGADRESSE` VARCHAR( 150 );";
      	mysql_query($sql);
      	if ( mysql_error($server) )
      		{
      		$error =  mysql_errno($server) . ": " . mysql_error($server) . "\n";
				IPS_LogMessage("Plugwise MySql",$error);
				}
			}




		$sql = "";
		$sql = $sql . "INSERT INTO ".$table." ";
		$sql = $sql . "(`SSID`,`DATUMUHRZEIT`,`GERAETENAME`,`GESAMTVERBRAUCH`,`GERAETEID`,`GERAETEGRUPPE`,`LOGADRESSE`) ";
		$sql = $sql . "VALUES ('".$time."-".$geraet."','".$time."','".$geraet."',".$wert.",".$id.",'".$group."','".$logadresse."'); ";
		//IPS_LogMessage("Plugwise MySql",$sql);
      mysql_query($sql);
      if ( mysql_error($server) )
      	{
      	$error =  mysql_errno($server) . ": " . mysql_error($server) . "\n";
			IPS_LogMessage("Plugwise MySql",$error);
			}

 	   }

   mysql_close($server);


	

	}
	
//******************************************************************************
// versteckt alle Data1 und Data2
//******************************************************************************
function hide_data1data2($hide = true)
	{
	GLOBAL $IdData1;
	GLOBAL $IdData2;

	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		$object = IPS_GetObject($child);
		$hidden = $object['ObjectIsHidden'];

		if ( $hide == true )
      	if ( $hidden == false )
      		{
				IPS_SetHidden($child,true);
				}
		if ( $hide == false )
      	if ( $hidden == true )
      	   {
				IPS_SetHidden($child,false);
				}
		}

	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		$object = IPS_GetObject($child);
		$hidden = $object['ObjectIsHidden'];

		if ( $hide == true )
      	if ( $hidden == false )
      		{
				IPS_SetHidden($child,true);
				}
		if ( $hide == false )
      	if ( $hidden == true )
      	   {
				IPS_SetHidden($child,false);
				}
		}
	}

/***************************************************************************//**
*	Welche IDs sollen im Graph,Data1,Data2 dargestellt werden
*******************************************************************************/
function find_id_toshow()
	{
	GLOBAL $CircleGroups;
	GLOBAL $ExterneStromzaehlerGroups;

	$id_result   = array();

	$id          = false;
	$maxleistung = 0;
	$info        = "";
	$objectname  = "";
	$idleistung  = 0;
	$idgesamt    = 0;
	$type        = "";
	$parent      = 0;
	$objectident = "";

	
	$CircleVisuPath = "Visualization.WebFront.Hardware.Plugwise.MENU.Stromzähler";
  	$CircleIdCData  = get_ObjectIDByPath($CircleVisuPath);

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
  	$CircleIdData   = get_ObjectIDByPath($CircleDataPath);

	$GruppenVisuPath= "Visualization.WebFront.Hardware.Plugwise.MENU.Gruppen";
  	$GroupsIdData   = get_ObjectIDByPath($GruppenVisuPath);

	$OthersDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Others";
  	$GroupsIdOData  = get_ObjectIDByPath($OthersDataPath);

	//***************************************************************************
	// Welcher Stromzaehler soll dargestellt werden
	//***************************************************************************
	if ( $id == false )   // noch nichts gefunden
		foreach ( IPS_GetChildrenIDs($CircleIdCData) as $child )
			if ( GetValueInteger($child) == 1 )
	      	{  // gefunden
				$id = true;
				$type       = "ZAEHLER";
	   		$object     = IPS_GetObject($child);
	      	$info       = $object['ObjectInfo'];
	      	$objectname = $object['ObjectName'];
				
	      	// suche in Circles
	      	$parent = @IPS_GetObjectIDByIdent($info,$CircleIdData);
	      	if ( $parent )
	         	{
					$idleistung = IPS_GetObjectIDByName('Leistung',$parent);
					$idgesamt   = IPS_GetObjectIDByName('Gesamtverbrauch',$parent);
					foreach ($CircleGroups as $circle )
	   				{	// Maxwert fuer Circle aus Config
	    				if ( $info == $circle[0] )
							{
							$maxleistung = $circle[4];
	      				break;
	      				}
						}
					}
				else
				   $id = false ; //Doch kein Circle
		}
		
	//***************************************************************************
	// Welcher externe Stromzaehler soll dargestellt werden
	//***************************************************************************
	if ( $id == false )   // noch nichts gefunden
		foreach($ExterneStromzaehlerGroups as $extern)
			if ( $extern[0] == $info ) // $info wird bei Circlesuche gefunden
				{
				$type        = "EXTERN";
				$id          = true;
				$objectname  = $extern[0];
				$idleistung  = $extern[2];
				$idgesamt    = $extern[3];
				$maxleistung = $extern[4];
				
				break;
				}

	//***************************************************************************
	// kein Stromzaehler oder Extern , dann Gruppe suchen
	//***************************************************************************
	if ( $id == false )   // noch nichts gefunden
		foreach ( IPS_GetChildrenIDs($GroupsIdData) as $child )
			if ( GetValueInteger($child) == 1 )
	      	{
				$id = true;
				$type        = "GRUPPE";
				
				$object      = IPS_GetObject($child);
	      	$ident       = $object['ObjectIdent'];
	      	$idleistung  = IPS_GetObjectIDByIdent('Leistung',IPS_GetObjectIDByIdent($ident,$GroupsIdOData));
	      	$idgesamt     = IPS_GetObjectIDByIdent('Gesamtverbrauch',IPS_GetObjectIDByIdent($ident,$GroupsIdOData));
				$objectname  = $object['ObjectName'];
				$objectident = $object['ObjectIdent'];
				$maxleistung = 0;
				break;
				}

	//***************************************************************************
	// kein Stromzaehler oder Extern oder Gruppe  dann Gesamt
	//***************************************************************************
	if ( $id == false )   // noch nichts gefunden dann Gesamt
		{ // Erst mal diese Daten nehmen
		$id = true;
		$type        = "GESAMT";
		$idgesamt   = IPS_GetObjectIDByIdent('SYSTEM_MAIN',$GroupsIdOData);
		$idleistung = IPS_GetObjectIDByName('Leistung',$idgesamt);
		$idgesamt   = IPS_GetObjectIDByName('Gesamtverbrauch',$idgesamt);
		$objectname = "Gesamt";
//		if ( isset($SystemStromzaehlerGroups) )
//		   {
//			$idleistung  = intval($SystemStromzaehlerGroups[0][2]);
//			$idgesamt    = intval($SystemStromzaehlerGroups[0][3]);
//			}
		}



	if ( $objectname == "SYSTEM_REST" )
      $objectname = "Sonstiges";

	//***************************************************************************
	// 
	//***************************************************************************
	$id_result['TYPE']        = $type;
	$id_result['ID']          = $id;
	$id_result['IDLEISTUNG']  = $idleistung;
	$id_result['IDGESAMT']    = $idgesamt;
	$id_result['MAXLEISTUNG'] = $maxleistung;
	$id_result['INFO']        = $info;
	$id_result['OBJECTNAME']  = $objectname;
	$id_result['PARENT']      = $parent;
	$id_result['OBJECTIDENT'] = $objectident;
	
	return $id_result;
	}
	
/***************************************************************************//**
*	Updated die Daten in Uebersicht,Data1,Data2 ab Version 1.3.xxxx
*******************************************************************************/
function update_webfront_123($was="",$id=0,$clear=false)
	{
	GLOBAL $IdGraph;
	GLOBAL $IdData1;
   GLOBAL $IdData2;

	
	$AppPath	  = "Program.IPSLibrary.app.hardware.Plugwise";
	$IdApp     = get_ObjectIDByPath($AppPath);


	if ( $clear == true )
	   {
	   $id1 = IPS_GetObjectIDByIdent("Uebersicht",$IdGraph);
		SetValueString($id1,"");
		IPS_SetHidden($id1,false);
		$id1 = IPS_GetObjectIDByName('Auswahl',$IdGraph);
		IPS_SetHidden($id1,true);
	   }

	if ( $was == "SYSTEMSTEUERUNG" )
		{
		hide_data1data2();
		IPS_SetHidden(IPS_GetVariableIDByName("Auswahl",$IdGraph),true);
		}
	if ( $was == "AUSWERTUNG" )
		{
		hide_data1data2();
		$id1 = IPS_GetObjectIDByIdent("Uebersicht",$IdGraph);
		SetValueString($id1,"In Vorbereitung");
		}

	if ( $was == "ZAEHLER" or $was == "GRUPPE" or $was == "GESAMT" )
		{
		hide_data1data2(false);
		show_data1data2($id);
      update_data1data2();
		$id = IPS_GetScriptIDByName('Plugwise_Config_Highcharts',$IdApp);
		IPS_RunScript($id);
		}
	if ( $was == "REFRESH" )
		{
      update_data1data2();
		//$id = IPS_GetScriptIDByName('Plugwise_Config_Highcharts',$IdApp);
		//IPS_RunScript($id);
		}

	}
//******************************************************************************
// zeigt die in $id uebergebenen Daten in Data1 und Data2 an
//******************************************************************************
function show_data1data2($id)
	{
	GLOBAL $IdGraph;
	GLOBAL $IdData1;
	GLOBAL $IdData2;


	$object = IPS_GetObject($id);
	$name = $object['ObjectName'];
	$info = $object['ObjectInfo'];

	if ( $object['ObjectID'] == 0 )
		{
	   $name = "Gesamt";
	   $info = "Gesamt";
	   }

	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		$object = IPS_GetObject($child);

		if ( $object['ObjectIdent'] == "WEBDATA1" )
		   {
		   IPS_SetName($child,$name);
         IPS_SetHidden($child,false);
         IPS_SetInfo($child,$info);
         }

		if ( $object['ObjectType'] == 6 )   // Link
		   {
		   if ( $object['ObjectInfo'] == $info )
		   	{
				IPS_SetHidden($child,false);
				}
			else
				{
		   	IPS_SetHidden($child,true);
				}
		   }

		}

	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		$object = IPS_GetObject($child);

		if ( $object['ObjectIdent'] == "WEBDATA2" )
		   {
		   IPS_SetName($child,$name);
         IPS_SetHidden($child,false);
         }
		}

   update_data1_data2();

	}

//******************************************************************************
// leert die HTMLBox
//******************************************************************************
function hide_graph($status = true)
	{
	GLOBAL $IdGraph;
	$id = IPS_GetObjectIDByIdent("Uebersicht",$IdGraph);
	SetValueString($id,"");

	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	IPS_SetHidden($id,true);


	// geht nicht ohne Reload WFC - wahrscheinlich wegen ~HTML
	// IPS_SetHidden($id,$status);
	}


/***************************************************************************//**
*	checked ob eine Zaehleraktion ausgefuehrt werden soll
*******************************************************************************/
function check_zaehleractions()
	{
	GLOBAL $Zaehleractions;

	$debug  = false;
	
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles   = get_ObjectIDByPath($CircleDataPath);

   $instances = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
	$archive   = $instances[0];


	if ( !isset($Zaehleractions) )
	   return;

	foreach ( $Zaehleractions as $action )
	   {
	   
	   $zaehler   = $action[0];
	   $bedingung = $action[1];
	   $wert1 	  = $action[2];
	   $wert2 	  = $action[3];
	   $zeitraum  = $action[4];
		$actionid  = $action[5];
	   $sollwert  = $action[6];
	   $zaehler2  = $action[7];
	
		$object = false;
		$leistung_id = false;
		
		
		// suche Zaehler bei den Circles
		if ( $debug ) IPS_Logmessage("\nSuche Zaehler bei den Circles","");
		$object = @IPS_GetObjectIDByIdent($zaehler,$idCatCircles);
		if ( $object )
		   {
		   $leistung_id = IPS_GetVariableIDByName("Leistung",$object);
		   }

		if ( !$object )
		   {
			echo "\nSuche Zaehler bei den Externen";

		   }
	   
		if ( !$object )
		   {
			echo "\nSuche Zaehler bei den Gruppen";

		   }

		// ab hier Auswertung wenn gefunden
		if ( $leistung_id )
		   {
		   //echo "\ngefunden:".$leistung_id;
			$akt_leistung = GetValue($leistung_id);
		   //echo "\nakt:".$akt_leistung;

			$ende  = time();
			$start = time() - ( $zeitraum * 60 );
			
			//echo "\nStart:".date("H:i:s",$start);
			//echo "\nEnde:".date("H:i:s",$ende);

			
			$datas  = AC_GetLoggedValues($archive,$leistung_id,$start,$ende,0);
		   //print_r($datas);
   		
   		if ( count($datas) == 0 )
				{
				echo "\nKeine Werte vorhanden";
   		   continue;
   		   }
   		   
			if ( $bedingung == "<" )      // kleiner
		      { //echo "kleiner:";
		      $ok = true ;
		      foreach ( $datas as $data )
		            { if ( $debug )  IPS_Logmessage("...",$data['Value']);
		            if ( $data['Value'] >= $wert1 )
		               $ok = false;
		            }
				if ( $ok == false )
				   continue;
		      }
			if ( $bedingung == ">" )      // groesser
		      { //echo "groesse:";
		      $ok = true ;
		      foreach ( $datas as $data )
		            {  //echo "-".$data['Value'];
						if ( $data['Value'] <= $wert1 )
		               $ok = false;
		            }
				if ( $ok == false )
				   continue;
		      }
			if ( $bedingung == "<>" )      // innerhalb eines Bereiches
		      {
		      $ok = true ;
		      foreach ( $datas as $data )
		            {
		            if ( $data['Value'] <= $wert1 )
		               $ok = false;
		            if ( $data['Value'] >= $wert1 )
		               $ok = false;
		            }
				if ( $ok == false )
				   continue;
		      }

				//echo "\nMach was ";
				$object = @IPS_GetObject($actionid);
				
				if ( $object )
				   {
				   if ( $object['ObjectType'] == 2 ) // Variable
				   	{
				   	if ( GetValue($actionid) != $sollwert )
				      	SetValue($actionid,$sollwert);
				      }
				   if ( $object['ObjectType'] == 3 ) // Script
				   	{
				   	$actionarray = array("PWACTION_ZAEHLER" 	=> $zaehler ,
													"PWACTION_BEDINGUNG" => $bedingung,
													"PWACTION_WERT1" 		=> $wert1,
													"PWACTION_WERT2" 		=> $wert2,
													"PWACTION_ZEITRAUM" 	=> $zeitraum,
													"PWACTION_ACTIONID" 	=> $actionid,
													"PWACTION_SOLLWERT" 	=> $sollwert,
													"PWACTION_ZAEHLER2" 	=> $zaehler2
													);
				      IPS_RunScriptEx($actionid,$actionarray);
				      }

				   }
		      
		   }
	   


	   }
	   
	}



/***************************************************************************//**
*	Logging
*******************************************************************************/
function logging($text,$file = 'plugwise.log' ,$force = false)
	{
	
	if ( $file != 'plugwiseerror.log' )
	if ( !$force )
		if ( !LOG_MODE )
	   	return;

	$ordner = IPS_GetKernelDir() . "logs\\Plugwise";
   if ( !is_dir ( $ordner ) )
		mkdir($ordner);

   if ( !is_dir ( $ordner ) )
	   return;

	list($usec, $sec) = explode(" ", microtime());
    
	$time = date("d.m.Y H:i:s",$sec);
	$logdatei = IPS_GetKernelDir() . "logs\\Plugwise\\" . $file;
	$datei = fopen($logdatei,"a+");
	fwrite($datei, $time ." ". $text . chr(13));
	fclose($datei);

	}


/***************************************************************************//**
*	Schaltet einen Circle ein/aus
*  $mac    = ID des Circles
*  $status = true/false
*******************************************************************************/
function circle_on_off($mac,$status)
	{
//	GLOBAL $idCatCircles;
	GLOBAL $CircleGroups;

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles   = get_ObjectIDByPath($CircleDataPath);

	// gehe alle Circle durch und teste ob schalten erlaubt.
	foreach( $CircleGroups as $circle)
	   {
	   
	   if ( $mac == $circle[0] )
	   	if ( intval($circle[3]) == 0 )
	      	{
	      	IPS_LogMessage("Plugwise","Circle ".$mac ." schalten verboten");
	      	return;
	      	}
			else
				{
				//IPS_LogMessage("Plugwise","Circle ".$mac ." schalten erlaubt");
	      	break;
	      	}
	   }



	$parent = $idCatCircles;
	$id = 0;
	$id = @IPS_GetObjectIDByIdent($mac,$parent);

	$id = @IPS_GetVariableIDByName("Status",$id);

	if ( !IPS_VariableExists($id) )
		return;

	if ( $status == true )
		$action = 1;
	if ( $status == false )
		$action = 0;

   SetValue($id,$status);

	$cmd = "0017".$mac."0".$action;
	PW_SendCommand($cmd);


	}
	
/***************************************************************************//**
*	CSS3 Menu bauen
*******************************************************************************/
function create_css3menu()
	{
	IPSUtils_Include ("Plugwise_CSS3Menu.inc.php","IPSLibrary::config::hardware::Plugwise");
	
	GLOBAL $CSS3_Plugwise_Menu;
	GLOBAL $CSS3_Plugwise_CSSFile;

	$VisuPath  	= "Visualization.WebFront.Hardware.Plugwise";
   $IdWebfront	= get_ObjectIDByPath($VisuPath,true);

	$webfrontid = IPS_GetObjectIDByName('Webfront',$IdWebfront);
	
	$cssfile = $CSS3_Plugwise_CSSFile;

	$html = "";
	$counter = 0;
	echo $CSS3_Plugwise_CSSFile;
   $html = "";
   $html = $html . '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd/">';
   $html = $html . '<html dir="ltr">';
	$html = $html . '<head>';
	$html = $html . '<meta http-equiv="content-type" content="text/html; charset=utf-8" />';
//	$html = $html . '<!-- Start css3menu.com HEAD section -->';
	$html = $html . '<link rel="stylesheet" href="/User/Plugwise/css3menu/'.$CSS3_Plugwise_CSSFile.'" type="text/css" />';
	$html = $html . '<!-- End css3menu.com HEAD section -->';
	$html = $html . '</head>';
	$html = $html . '<body style="background-color:#EBEBEB">';
//	$html = $html . '<!-- Start css3menu.com BODY section -->';
	$html = $html . '<ul id="css3menu1" class="topmenu">';

	$widthpx = "178px";


	$aktlevel   = 1;
	$newlevel   = 1;
	$oldlevel   = 1;

	foreach($CSS3_Plugwise_Menu as $menuitem )
	   {
		$counter = $counter + 1;

	   if ( $counter == 1 ) // Kopf
	      {
			$html = $html . '<li class="topfirst"><a class="pressed"  style="'.$widthpx.';">'.$menuitem[2].'</a></li>';
			continue;
			}


		$newlevel = substr_count($menuitem[1], '-');
		$leveldiff = $newlevel - $aktlevel;
		
      if ( $newlevel > $aktlevel )     // neues Untermenu
			$html = $html . '<ul><li class="topfirst"><a href="#">'.$menuitem[2].$newlevel.'-'.$leveldiff.'</a></li>';;

      if ( $newlevel == $aktlevel )
      	$html = $html . '<li class="topfirst"><a href="#">'.$menuitem[2].$newlevel.'-'.$leveldiff.'</a></li>';


      if ( $newlevel < $aktlevel )     //  Untermenu schliessen
      	for ( $x=$leveldiff;$x<0;$x++)
			   {
				$html = $html . '</ul></li>';
				}


		$aktlevel = $newlevel;

	   
	   }

	// Menubottom
	$html = $html . '<li class="toplast"><a class="pressed"  style="'.$widthpx.';"> </a></li>';
	$html = $html . '</ul>';
	
	
	
	SetValueString($webfrontid,$html);
	return $html;
	}



/***************************************************************************//**
* @}
* @}
*******************************************************************************/
?>
