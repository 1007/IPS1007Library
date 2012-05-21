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


/***************************************************************************//**
* @ingroup plugwise
* @{
* @defgroup Plugwise_Controller Plugwise Controller
* @{
*
* @file       Plugwise_Controller.ips.php
* @author     Axel Philippson (axelp) , Juergen Gerharz (1007)
* @version    Version 1.0.0
* @date       18.05.2012
*
*
*  @brief   Plugwise Controller
*	1) Maarten Damen (http://www.maartendamen.com/wp-content/uploads/downloads/2010/08/Plugwise-unleashed-0.1.pdf)
*  2) Jannis (http://www.ip-symcon.de/forum/f53/plugwise-ohne-server-direkt-auslesen-schalten-17348/)
*
* @todo
*	Befehl 0018 meldet mir immer nur genau 11 Circles - ich habe aber 18.
*  Anlegen des Netzwerks immer noch mit der 'Source'
*******************************************************************************/


	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  	IPSUtils_Include("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles   = get_ObjectIDByPath($CircleDataPath);
	$OtherDataPath  = "Program.IPSLibrary.data.hardware.Plugwise.Others";
   $idCatOthers    = get_ObjectIDByPath($OtherDataPath);

	switch ($_IPS['SENDER'])
			{
			Case "RunScript"			:	break;
			Case "Execute"				:	break;
			Case "TimerEvent"			:	request_circle_data();
												update_data1data2();	break;
			Case "Variable"			:	break;
			Case "WebFront"			:  handle_webfront($_IPS['VARIABLE']);  break;
			Case "RegisterVariable"	:	$buf = $IPS_VALUE;
												switch ((substr($buf,0,4)))
													{
													case "0000":	plugwise_0000_received($buf);	break;
													case "0003":	plugwise_0003_received($buf);	break;
													case "0011":  	plugwise_0011_received($buf);	break;
													case "0013":	plugwise_0013_received($buf); break;
													case "0019":   plugwise_0019_received($buf);	break;
													case "0024":   plugwise_0024_received($buf);	break;
													case "0027":   plugwise_0027_received($buf);	break;
													case "0049": 	plugwise_0049_received($buf);	break;
													case "003F":   plugwise_003F_received($buf);	break;
	   											default	  :   logging( "Unbekanntes Telegramm [".$buf . "]","plugwiseunknown.log"); break;
													}
			default						:	break;
			}

   berechne_gruppenverbrauch();

	hole_gesamtverbrauch();
	
	dummy();

/***************************************************************************//**
* Dummy Routine gegen DoxygenBug
* wenn foreach() als letzter Befehl im Kopf dann wird foreach
* als Routine/Variable dokumentiert
*******************************************************************************/
function dummy()
  {
  }

/***************************************************************************//**
*	"0000" empfangen	-  Stick
*  
*******************************************************************************/
function plugwise_0000_received($buf)
	{
	GLOBAL $idCatCircles;

	switch ( substr($buf,8,4) )
		{
		case "00C1":	// Schauen ob alles empfangen wurde
		 					// print "Befehl von Stick empfangen";
		 					logging( "R - ".$buf ."[".substr($buf,4,4)."] 00C1 empfangen Befehl bestaetigt");
							break;

		case "00D8":  	// eingeschaltet
		 					// print "Eingeschaltet MAC".substr($buf,12,16);
		 					logging( "R - ".$buf ." Circle eingeschaltet");
							$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
							SetValue(IPS_GetVariableIDByName ("Status", $myCat),True);
							break;

		case "00DE":  	// ausgeschaltet
							// print "Ausgeschaltet MAC".substr($buf,12,16);
							logging( "R - ".$buf ." Circle ausgeschaltet");
							$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
							SetValue(IPS_GetVariableIDByName ("Status", $myCat),False);
							break;

		case "00E1":   // Ein Circle nicht erreichbar
							// print "Achtung: ein Circle ist nicht erreichbar: ".$buf;
							logging( "R - ".$buf ." Circle nicht erreichbar");
							break;

		case "00D7": 	// Antwort auf 0016 - Uhrzeit stellen
					   	$mac = substr($buf,12,16);
							$myCat = IPS_GetObjectIDByIdent($mac, $idCatCircles);
					   	print "Uhrzeit gestellt auf ".IPS_GetName($myCat).": ".$buf;
					   	// print "Uhrzeit gestellt auf  ".IPS_GetName($myCat);
							break;

		case "00DD":  	// Antwort auf 0008 - Anfrage nach Circle+
					   	$macplus = substr($buf,12,16);

							// Dummy Instanz für Circle+ anlegen
							$myCat = @IPS_GetObjectIDByIdent($macplus, $idCatCircles);
							if ($myCat == false)
								createCircle($macplus, $idCatCircles);

					   	PRINT "PW MC+:".$macplus.", Now searching for Circles...";
							for( $i = 0; $i < 64; $i++)
								{
								$cmd = "0018".$macplus.str_pad($i, 2 ,'0', STR_PAD_LEFT);
								logging( "S - ".$cmd . " Searching Circles");
						   	PW_SendCommand($cmd);
								}
							break;

		default:       // unbekannte Antwort
							// print "Fehler von Stick: ".$buf;  //bei allem anderen
							logging( "Unbekanntes Telegramm [".$buf . "]","plugwiseunknown.log");
							break;

		}
	}

/***************************************************************************//**
*	"0003" empfangen	- ???
*  Antwort auf "0001" - ???
*******************************************************************************/
function plugwise_0003_received($buf)
	{
	GLOBAL $idCatCircles;

	//PW_SendCommand("000A");

	}
/***************************************************************************//**
*	"0011" empfangen	-  Init
*  Antwort auf "000A" - stick initialization
*******************************************************************************/
function plugwise_0011_received($buf)
	{
	GLOBAL $idCatCircles;

	
	// Kalibrierungsdaten aller Circles abrufen
	foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
		{	// alle Unterobjekte
		$id_info = IPS_GetObject($item);
		//PW_SendCommand("0026".$id_info['ObjectIdent']);
		}
	$stick = substr($buf,8,16);
	
	//PW_SendCommand("0004"."00000000000000000000".$stick);
	
	$text = "Init [".$buf."] " .$stick;
	logging($text,'plugwiseinit.log' );
		
	}
	
/***************************************************************************//**
*	"0013" empfangen	-  aktueller Stromverbrauch empfangen
*  Antwort auf "0012" Power information request (current)
*******************************************************************************/
function plugwise_0013_received($buf)
	{
	GLOBAL $idCatCircles;

	$mcID = substr($buf,8,16);
	$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
	$pulse = substr($buf,28,4);

	if ( !$myCat)
	   {
		$text = "Stromverbrauch von unbekannt empfangen [".$buf."]";
		logging($text,'plugwisepowerinformation.log' );
		return;
	   }


	If ($pulse == "FFFF")
		{	// Circle ausgeschaltet, meldet FFFF
		SetValue(CreateVariable("Leistung", 2, $myCat, 0, "~Watt.3680", 0), 0);
		$text = $myCat . " Circle ausgeschaltet. " . $buf;
		}
	else
		{
		$gainA	 = GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
		$gainB	 = GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
		$offTotal = GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
		$offNoise = GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));

		// Aktueller Verbrauch in Watt ermitteln
		$value 	 = hexdec($pulse)/8;
		$out 		 = (pow(($value+$offNoise),2)*$gainB)+(($value+$offNoise)*$gainA)+$offTotal;
		$Leistung = (($out ) / 468.9385193)*1000;
		$Leistung = round($Leistung,1);

		SetValueFloat(CreateVariable("Leistung", 2, $myCat, 0, "~Watt.3680", 0), $Leistung);
		SetValue(IPS_GetVariableIDByName("Error", $myCat),0);

		$text = IPS_GetName($myCat) . " Aktueller Stromverbrauch. " . $Leistung ." [".$buf."]";

		}
		
	logging($text,'plugwisepowerinformation.log' );

	}
	
/***************************************************************************//**
*	"0019" empfangen	-  Adressenabfrage
*  Antwort auf "0018" - Detect Circles
*******************************************************************************/
function plugwise_0019_received($buf)
	{
	GLOBAL $idCatCircles;

	$mcID = substr($buf,8,16);
	$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
	$pulse = substr($buf,28,4);

	if ( !$myCat)
	   {
		$text = "Adresse von unbekannt empfangen [".$buf."]";
		logging($text,'plugwiseadresse.log' );
		return;
	   }

	$text = IPS_GetName($myCat) . "Adresse empfangen [".$buf."] ";

	$mac = substr($buf,24,16);
	if (!($mac == "FFFFFFFFFFFFFFFF"))
		{
		$myCat = IPS_GetObjectIDByIdent($mac, $idCatCircles);
		if ($myCat == false)
			{
			$text = $text . "Circle wird angelegt";
			createCircle($mac, $idCatCircles);
			}
		else
			{
			// Circle bereits vorhanden
			}
		}
	else  // Circle meldet sich nicht
		{

		}


 	logging($text,'plugwiseadresse.log' );


	}
	
/***************************************************************************//**
*	"0024" empfangen	-  Statusdaten empfangen
*  Antwort auf "0023" - Device information request
*******************************************************************************/
function plugwise_0024_received($buf)
	{
	GLOBAL $idCatCircles;

	$mcID = substr($buf,8,16);
	$myCat = @IPS_GetObjectIDByIdent($mcID, $idCatCircles);

	if ( !$myCat)
	   {
		$text = "Status von unbekannt empfangen [".$buf."]";
		logging($text,'plugwisestatus.log' );
		return;
	   }

	SetValue(IPS_GetVariableIDByName("Status",$myCat),substr($buf,41,1));

	//SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),intval((hexdec(substr($buf,32,8)) - 278528) / 32));
	
	//SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),substr($buf,32,8));
   SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),intval((hexdec(substr($buf,32,8)))));
   
	$text = IPS_GetName($myCat);
	$text = $text." Logadresse[".intval((hexdec(substr($buf,32,8)) - 278528) / 32)."][".hexdec(substr($buf,32,8))."]";

	//$text = $text."[".substr($buf,24,2)." ".substr($buf,26,2)." ".substr($buf,28,4)."] ";

	$text = $text. " Hardwareversion: ".substr($buf,44,4)."-".substr($buf,48,4)."-".substr($buf,52,4);
	$text = $text. " Softwareversion: ".date('d.m.Y h:i:s',hexdec(substr($buf,56,8)))." ";

 	logging($text,'plugwisestatus.log' );

	}

/***************************************************************************//**
*	"0027" empfangen	-  Kalibrierungsdaten empfangen
*  Antwort auf "0026" Calibration request
*******************************************************************************/
function plugwise_0027_received($buf)
	{
	GLOBAL $idCatCircles;

	$myCat = IPS_GetObjectIDByIdent(substr($buf,8,16), $idCatCircles);

	if ( !$myCat)
	   {
		$text = "Kalibrierungsdaten von unbekannt empfangen [".$buf."]";
		logging($text,'plugwisecalibration.log' );
		return;
	   }
	   
	SetValueFloat(CreateVariable("gaina",2,$myCat,0,""),bintofloat(substr($buf,24,8)));
	SetValueFloat(CreateVariable("gainb",2,$myCat,0,""),bintofloat(substr($buf,32,8)));
	SetValueFloat(CreateVariable("offTotal",2,$myCat,0,""),bintofloat(substr($buf,40,8)));
	if (substr($buf,48,8)=="00000000")
		SetValueFloat(CreateVariable("offNoise",2,$myCat,0,""),0);
	else
		SetValueFloat(CreateVariable("offNoise",2,$myCat,0,""),bintofloat(substr($buf,48,8)));

	$text = IPS_GetName($myCat) . "Kalibrierungsdaten empfangen [".$buf."]";
	
	logging($text,'plugwisecalibration.log' );
	
	}
/***************************************************************************//**
*	"003F" empfangen	-  aktuelle Uhrzeit empfangen
*  Antwort auf "003E" - Uhrzeit auslesen
*******************************************************************************/
function plugwise_003F_received($buf)
	{
	GLOBAL $idCatCircles;

	$mcID = substr($buf,8,16);
	$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);

	$myTime = mktime(hexdec(substr($buf,24,2)) + (date("Z")/3600),
					hexdec(substr($buf,26,2)), hexdec(substr($buf,28,2)), 0,0,0);
   $std = date("H", $myTime);
	$min = date("i", $myTime);
	$sek = date("s", $myTime);
	$m1  = ($std*60*60) + ($min*60) + $sek;
	$std = date("H", time());
	$min = date("i", time());
	$sek = date("s", time());
	$m2  = ($std*60*60) + ($min*60) + $sek;

 	$abweichung = $m1 - $m2;
	$text = IPS_GetName($myCat).": ".date("H:i:s", $myTime)." Abweichung: " . $abweichung ." Sekunden. " .unixtime2pwtime();

	if ( $abweichung > 120 ) // Abweichung groesser 120 Sekunden
		{
		$text = $text . " Uhrzeit wird gestellt.";
 		PW_SendCommand("0016".IPS_GetName($myCat).unixtime2pwtime());
		}

	logging($text,'plugwisetime.log' );

	}

/***************************************************************************//**
*	"0049" empfangen	-  Bufferdaten empfangen
*  Antwort auf "0048" Buffer request
*******************************************************************************/
function plugwise_0049_received($buf)
	{
	GLOBAL $idCatCircles;

	$myCat = IPS_GetObjectIDByIdent(substr($buf,8,16), $idCatCircles);

	$LogAddressRaw = substr($buf,88,8);

	if ( !$myCat)
	   {
		$text = "Bufferdaten von unbekannt empfangen [".$buf."]";
		logging($text,'plugwisebuffer.log' );
		return;
	   }


	// Kalibrierungsdaten laden
	$gaina	 = GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
	$gainb	 = GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
	$offTotal = GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
	$offNoise = GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));

	$verbrauch = 0;

	// Korrecktes Logdate aus den Vier Werten im Buffer herausfinden
	$usedlogdate = 0;

	$logdate = pwtime2unixtime(substr($buf,24,8));
	print "\nLetztes Logdate: ".date("c",$logdate);

	$time = time() - 3600;
	
   
	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,32,8)), $offNoise, $offTotal, $gaina, $gainb);
		}

	$logdate = pwtime2unixtime(substr($buf,40,8));
	print "\nLetztes Logdate: ".date("c",$logdate);

	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,48,8)), $offNoise, $offTotal, $gaina, $gainb);
		}

	$logdate = pwtime2unixtime(substr($buf,56,8));
	print "\nLetztes Logdate: ".date("c",$logdate);

	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,64,8)), $offNoise, $offTotal, $gaina, $gainb);
		}

	$logdate = pwtime2unixtime(substr($buf,72,8));
	print "\nLetztes Logdate: ".date("c",$logdate);

	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,80,8)), $offNoise, $offTotal, $gaina, $gainb);
		}

	echo "\nVerbrauch:".$verbrauch;
   $usedlogdate = 1;
	if ($usedlogdate == 0)
		{
		$id_log = IPS_GetVariable(IPS_GetVariableIDByName ("LogAddress", $myCat));
		if ($id_log["VariableChanged"] > (time()-10*60))
			{
			$id_info = IPS_GetObject($myCat);
			$LogAddress = GetValue(IPS_GetVariableIDByName ("LogAddress", $myCat));
			print "\nPW0048 - ".IPS_GetName($myCat)." - ";
			print "\nBuffer mit akt. LogAddress ".$LogAddress." enthält keine aktuellen Werte für die Zeit ".date("c",time()).", es wird versucht den Buffer mit LogAdress ".($LogAddress-1)." zu lesen";
			$LogAddress = 278528 + (32 * ($LogAddress-1));
			$LogAddress = str_pad(strtoupper(dechex($LogAddress)), 8 ,'0', STR_PAD_LEFT);
			//PW_SendCommand("0048".$id_info['ObjectIdent'].$LogAddress);

			}
		else
			{
			$LogAddress = GetValue(IPS_GetVariableIDByName ("LogAddress", $myCat));
			print "PW0048 - ".IPS_GetName($myCat)." - ";
			print "Buffer mit akt. LogAddress ".$LogAddress." enthält keine aktuellen Werte für die Zeit ".date("c",time()).", Timing-Problem?";
			};
		}
	else
		{
		$varGesamtverbrauch = IPS_GetVariableIDByName("Gesamtverbrauch",$myCat);
		$oldVerbrauch = GetValueFloat($varGesamtverbrauch);

		PRINT "\nPW0048 - ".IPS_GetName($myCat).":\n";
		print "\nLogdate: ".date("c",$usedlogdate)."\n";
		print "Verbrauch/Stunde: ".$verbrauch."\n";
		print "Alter Gesamtverbrauch: ".$oldVerbrauch."\n";
		print "Neuer Gesamtverbrauch: ".($verbrauch + $oldVerbrauch)."\n";
		SetValueFloat ($varGesamtverbrauch,$verbrauch + $oldVerbrauch);
		};


		$text =  "PW0049 Buffer - ".IPS_GetName($myCat) . "[".$buf."]";
		$text = $text . "\nLogadresse:" .$LogAddressRaw;
		$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,24,8))).": ";
		$text = $text . pulsesToKwh(hexdec(substr($buf,32,8)), $offNoise, $offTotal, $gaina, $gainb);
		$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,40,8))).": ";
		$text = $text . pulsesToKwh(hexdec(substr($buf,48,8)), $offNoise, $offTotal, $gaina, $gainb);
		$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,56,8))).": ";
		$text = $text . pulsesToKwh(hexdec(substr($buf,64,8)), $offNoise, $offTotal, $gaina, $gainb);
		$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,72,8))).": ";
		$text = $text . pulsesToKwh(hexdec(substr($buf,80,8)), $offNoise, $offTotal, $gaina, $gainb);
		logging($text,'plugwisebuffer.log' );

	}
	
/***************************************************************************//**
*	Handle Webfront - Circle ein/aus schalten
*******************************************************************************/
function handle_webfront($variable)
	{
	$id = IPS_GetParent($variable);
	$id_info = IPS_GetObject($id);
	if ($_IPS['VALUE'] == 1)
		{
		$action = 1;
		}
	else
		{
		$action = 0;
		}
	$cmd = "0017".$id_info['ObjectIdent']."0".$action;
	PW_SendCommand($cmd);

	}
	
/***************************************************************************//**
*	Alle Circles durchlaufen, Status und Verbrauch lesen
*******************************************************************************/
function request_circle_data()
	{
	GLOBAL $idCatCircles;

	$now = time();
	
	foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
		{  // alle Unterobjekte durchlaufen
		$obj = IPS_GetVariable(IPS_GetObjectIDByIdent("Leistung",$item));
		$t = ($now - ($obj["VariableUpdated"]))/60; // Zeit in Minuten wann letzte Aktualisierung
      SetValue(IPS_GetVariableIDByName ("LastMessage", $item),$t);
      if ( $t > 5 )  // laenger als 5 Minuten keine Daten
      	SetValue(IPS_GetVariableIDByName ("Error", $item),1);
		else
			SetValue(IPS_GetVariableIDByName ("Error", $item),0);

		$id_info = IPS_GetObject($item);

		PW_SendCommand("0012".$id_info['ObjectIdent']);
		PW_SendCommand("0023".$id_info['ObjectIdent']);
		}
	}
  
  
/***************************************************************************//**
* Gesamtverbrauch holen
* Im Konfigurationsfile die IDs der Variablen holen und in Data schreiben
* Zum Einbinden von Stromzaehlern zB (EKM)
*******************************************************************************/
function hole_gesamtverbrauch()
	{
	GLOBAL $idCatOthers;
	
	$id1 = IPS_GetObjectIDByIdent('Gesamt',$idCatOthers);

	if ( IPS_ObjectExists(ID_GESAMTVERBRAUCH) )
	   {
      $d = GetValue(ID_GESAMTVERBRAUCH);
		$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$id1);

		SetValue($id,$d);
		
	   }

	if ( IPS_ObjectExists(ID_LEISTUNG) )
	   {
      $d = GetValue(ID_LEISTUNG);
		$id = IPS_GetObjectIDByIdent('Leistung',$id1);
		SetValue($id,$d);


	   }

	
	}

/***************************************************************************//**
* Gruppenverbrauch berechnen
*******************************************************************************/
function berechne_gruppenverbrauch()
	{
	GLOBAL $CircleGroups;
	GLOBAL $idCatCircles;
	GLOBAL $idCatOthers;

	$others = IPS_GetChildrenIDs($idCatOthers);


   $array = array();
   foreach ( $CircleGroups as $group )
      {
      if ( $group[0] != "" )
         {
			$array_leistung[$group[2]] = 0;
			$array_verbrauch[$group[2]] = 0;
			}
		}
	
	foreach ( $CircleGroups as $group )
		{
      if ( $group[0] != "" )
         {

		$gruppe = $group[2];
		$mac 	  = $group[0];

		$gruppenid = IPS_GetObjectIDByIdent($gruppe,$idCatOthers);
		
		$id = @IPS_GetObjectIDByIdent($mac,$idCatCircles);
		if ( $id )
		   {
		   $leistung  = GetValue(IPS_GetObjectIDByIdent('Leistung',$id));
		   $verbrauch = GetValue(IPS_GetObjectIDByIdent('Gesamtverbrauch',$id));

			$array_leistung[$gruppe] = $array_leistung[$gruppe] + $leistung;
			$array_verbrauch[$gruppe] = $array_verbrauch[$gruppe] + $verbrauch;

			}
		}
		}

	$keys = array_keys($array_leistung);
	
	foreach ( $keys as $gruppe )
	   {
		$gruppenid = IPS_GetObjectIDByIdent($gruppe,$idCatOthers);

		$wert = $array_leistung[$gruppe];
		$id = IPS_GetObjectIDByIdent('Leistung',$gruppenid);
		SetValue($id,$wert);

		$wert = $array_verbrauch[$gruppe];
		$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$gruppenid);
		SetValue($id,$wert);

	   }


	}

/***************************************************************************//**
* @}
* @}
*******************************************************************************/
?>