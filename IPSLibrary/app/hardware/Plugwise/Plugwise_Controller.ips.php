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

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles   = get_ObjectIDByPath($CircleDataPath);
	$OtherDataPath  = "Program.IPSLibrary.data.hardware.Plugwise.Others";
   $idCatOthers    = get_ObjectIDByPath($OtherDataPath);
	
	switch ($_IPS['SENDER'])
			{
			Case "RunScript"			:	break;
			Case "Execute"				:	test();break;
			Case "TimerEvent"			:	request_circle_data();
												berechne_gruppenverbrauch();
												hole_gesamtverbrauch();
												update_data1data2();
												update_uebersicht();
												break;
			Case "Variable"			:	schaltbefehl($_IPS['VARIABLE'],$IPS_VALUE);break;
			Case "WebFront"			:  handle_webfront($_IPS['VARIABLE']);  break;
			Case "RegisterVariable"	:	$buf = $IPS_VALUE;
												switch ((substr($buf,0,4)))
													{
													case "0000":	plugwise_0000_received($buf);	break;
													case "0003":	plugwise_0003_received($buf);	break;
													case "0006":	plugwise_0006_received($buf); break;
													case "0011":  	plugwise_0011_received($buf);	break;
													case "0013":	plugwise_0013_received($buf); break;
													case "0019":   plugwise_0019_received($buf);	break;
													case "0024":   plugwise_0024_received($buf);	break;
													case "0027":   plugwise_0027_received($buf);	break;
													case "0049": 	plugwise_0049_received($buf);	break;
													case "003F":   plugwise_003F_received($buf);	break;
													case "0061":   plugwise_0061_received($buf);	break;
	   											default	  :   logging( "Unbekanntes Telegramm [".$buf . "]","plugwiseunknown.log"); break;
													}
			default						:	break;
			}

	
	dummy();

/***************************************************************************//**
* Dummy Routine gegen DoxygenBug
* wenn foreach() als letzter Befehl im Kopf dann wird foreach
* als Routine/Variable dokumentiert
*******************************************************************************/
function dummy()
  {
  
  
  }

function test()
	{
	



	}
	
/***************************************************************************//**
*  Variablenaenderung
*******************************************************************************/
function schaltbefehl($var,$status)
	{
	GLOBAL $CircleGroups;
	GLOBAL $idCatCircles;
	
	$text = $var ."-".$status;
	
	
	foreach( $CircleGroups as $circle)
	   {
	   $mac = false;
		
	   if ( $var == intval($circle[3]) )
	      {
	      $mac = $circle[0];
	      break;
	      }
	      
	   }

	if ( strlen($mac) > 0 )
	   {
		$parent = $idCatCircles;
		$id = 0;
		$id = @IPS_GetObjectIDByIdent($mac,$parent);

		$id = @IPS_GetVariableIDByName("Status",$id);

		if ( IPS_VariableExists($id) )
		   {
		   if ( $status == 0 )
		      $action = 0;
		   if ( $status == 1 )
		      $action = 1;
		   if ( $status == true )
		      $action = 1;
		   if ( $status == false )
		      $action = 0;

			if ( $status == 0 or $status == 1 )
			   {
		   	$cmd = "0017".$mac."0".$action;
				PW_SendCommand($cmd);
				}
				
		   
		   }

	   }
	   
	   
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
							$id = IPS_GetVariableIDByName ("Status", $myCat);
							if ( GetValue($id) <> true)
								SetValue($id,True);
							break;

		case "00DE":  	// ausgeschaltet
							// print "Ausgeschaltet MAC".substr($buf,12,16);
							logging( "R - ".$buf ." Circle ausgeschaltet");
							$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
							$id = IPS_GetVariableIDByName ("Status", $myCat);
							if ( GetValue($id) <> false)
								SetValue($id,false);
							break;

		case "00E1":   // Ein Circle nicht erreichbar
							// print "Achtung: ein Circle ist nicht erreichbar: ".$buf;
							logging( "R - ".$buf ." Circle nicht erreichbar");
							break;

		case "00D7": 	// Antwort auf 0016 - Uhrzeit stellen
					   	$mac = substr($buf,12,16);
							$myCat = IPS_GetObjectIDByIdent($mac, $idCatCircles);
					   	//print "Uhrzeit gestellt auf ".IPS_GetName($myCat).": ".$buf;
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
								$hex = strtoupper(dechex($i));
								$cmd = "0018".$macplus.str_pad($hex, 2 ,'0', STR_PAD_LEFT);
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

	$text = "COMMAND 3 [".$buf."]";

   logging($text,'plugwiseunknown.log' );
   
	}

/***************************************************************************//**
*	"0006" empfangen	- Circle ist nicht im Netz
*  Antwort auf "000801" 
*******************************************************************************/
function plugwise_0006_received($buf)
	{
	GLOBAL $CircleGroups;

	$mac = substr($buf,8,16);

	// gehe Konfiguration durch ob in Liste
	foreach( $CircleGroups as $circle )
	   {
	   // neuer Circle ist in Liste muss eingebunden werden
	   if ( $mac == $circle[0] )
	      {
	      $text = $mac . " wird versucht einzubinden";
	      logging($text,'plugwiseerror.log' );

	      $cmd = "000701".$mac;
	      PW_SendCommand($cmd);
	      }
	      
	   $text = $circle[0];
	   //IPS_LogMessage("........",$text);
	   }
	   
	unknowncircles($mac.",0");


   //logging($text,'plugwiseunknown.log' );

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
		{	// Circle ausgeschaltet, meldet FFFF ( nicht immer ?? )
			// scheinbar nicht
		$id = IPS_GetVariableIDByName("Leistung",$myCat);
		if ( GetValueFloat($id) != 0 )
			SetValueFloat($id,0);
		
		$id = IPS_GetVariableIDByName ("Status", $myCat);
		//IPS_LogMessage("....",$id);
		if ( GetValue($id) <> false)
			SetValue($id,false);

		$text = $myCat . " Circle ausgeschaltet FFFF . " . $buf;
		logging($text,'plugwiseerror.log' );
		}
	else
		{
		$gainA	 = GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
		$gainB	 = GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
		$offTotal = GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
		$offNoise = GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));

		// keine Kalibrierung
		if ( $gainA == 0 and $gainB == 0 and $offTotal == 0 and $offNoise == 0 )
		   {
		   // Kalibrierungsdaten vom Circle abrufen
		   $id_info = IPS_GetObject($myCat);
			PW_SendCommand("0026".$id_info['ObjectIdent']);
			$pulse = 0;    // Pulse auf Null , da keine Kalibrierung
		   }

		// Aktueller Verbrauch in Watt ermitteln
		if ( hexdec($pulse) > 0 )
		   {
			$value 	 = hexdec($pulse)/8;
			$out 		 = (pow(($value+$offNoise),2)*$gainB)+(($value+$offNoise)*$gainA)+$offTotal;
			$Leistung = (($out ) / 468.9385193)*1000;
			$Leistung = round($Leistung,1);
			}
		else
		   $Leistung = 0;
		   
		if ( $Leistung < 0 )
		   $Leistung = 0;


		$id = IPS_GetVariableIDByName("Leistung",$myCat);
		if ( GetValueFloat($id) != $Leistung )
			SetValueFloat($id,$Leistung);

		$id = IPS_GetVariableIDByName ("Status", $myCat);
		if ( GetValue($id) <> true)
			SetValue($id,True);


		$text = IPS_GetName($myCat) . " Aktueller Stromverbrauch. " . $Leistung ." [".$buf."] Pulse: $pulse";

		}

	$id = IPS_GetVariableIDByName("Error", $myCat);
	if ( GetValue($id ) != 0 )
		SetValue($id,0);

		
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

	$text = IPS_GetName($myCat) . " Adresse empfangen [".$buf."] ";

	$mac = substr($buf,24,16);
	if (!($mac == "FFFFFFFFFFFFFFFF"))
		{
		$myCat = @IPS_GetObjectIDByIdent($mac, $idCatCircles);
		if ($myCat == false)
			{
			unknowncircles($mac.",1");
			$text = $text . "Circle wird angelegt";
			if ( AUTOCREATECIRCLE )
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

   $t = GetValue(IPS_GetVariableIDByName ("LastMessage", $myCat));
	$t = microtime(true) - $t ;
	SetValue(IPS_GetVariableIDByName ("LastMessage", $myCat),$t);


	$einaus = substr($buf,41,1);
	$id = IPS_GetVariableIDByName("Status",$myCat);
	if ( GetValue($id) != $einaus )
		SetValue(IPS_GetVariableIDByName("Status",$myCat),$einaus);

	//SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),intval((hexdec(substr($buf,32,8)) - 278528) / 32));
	
	//SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),substr($buf,32,8));
	
	$logadress = intval((hexdec(substr($buf,32,8))));
	$id = IPS_GetVariableIDByName("LogAddress",$myCat);
	if ( GetValue($id) != $logadress )
   	SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),$logadress);
   
	$text = IPS_GetName($myCat);
	$text = $text." Logadresse[".intval((hexdec(substr($buf,32,8)) - 278528) / 32)."][".hexdec(substr($buf,32,8))."]";

	//$text = $text."[".substr($buf,24,2)." ".substr($buf,26,2)." ".substr($buf,28,4)."] ";

	$hw_version = substr($buf,44,4)."-".substr($buf,48,4)."-".substr($buf,52,4);
	$sw_version = date('d.m.Y h:i:s',hexdec(substr($buf,56,8)));
	$text = $text. " Hardwareversion: ".$hw_version;
	$text = $text. " Softwareversion: ".$sw_version." ";

	$info = $hw_version .",".$sw_version;

	$obj = IPS_GetObject($myCat);
	$obj_info = $obj['ObjectInfo'];

	if ( $obj_info != $info )
		IPS_SetInfo($myCat,$info);

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
*  Keine weiteren Bufferdaten anfordern ( Telegrammflut )
*  entweder ist die letzte Stunde drin oder nicht.
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

	//***************************************************************************
	// Kalibrierungsdaten laden
	//***************************************************************************
	$gaina	 = GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
	$gainb	 = GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
	$offTotal = GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
	$offNoise = GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));

	$verbrauch = 0;
	$bufferstelle = 0;

	$time = time() - 3600;

	//***************************************************************************
	// Korrecktes Logdate aus den Vier Werten im Buffer herausfinden
	//***************************************************************************
	$usedlogdate = 0;

	$logdate = pwtime2unixtime(substr($buf,24,8));

	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,32,8)), $offNoise, $offTotal, $gaina, $gainb);
		$bufferstelle = 1;
		}

	$logdate = pwtime2unixtime(substr($buf,40,8));

	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,48,8)), $offNoise, $offTotal, $gaina, $gainb);
		$bufferstelle = 2;
		}

	$logdate = pwtime2unixtime(substr($buf,56,8));

	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,64,8)), $offNoise, $offTotal, $gaina, $gainb);
		$bufferstelle = 3;
		}

	$logdate = pwtime2unixtime(substr($buf,72,8));

	if ($logdate > $time)
		{
		$usedlogdate = $logdate;
		$verbrauch = pulsesToKwh(hexdec(substr($buf,80,8)), $offNoise, $offTotal, $gaina, $gainb);
		$bufferstelle = 4;
		}

	//***************************************************************************
	// Buffer gefunden
	//***************************************************************************
	if ( $usedlogdate )
	   {
		$varGesamtverbrauch = IPS_GetVariableIDByName("Gesamtverbrauch",$myCat);
		$oldVerbrauch = GetValueFloat($varGesamtverbrauch);

		$obj = IPS_GetVariable($varGesamtverbrauch);
		$ti1 = $obj["VariableChanged"];

		$stunde1 = intval(date('h',$usedlogdate));
		$stunde2 = intval(date('h',$ti1));

		if ( $stunde1 == $stunde2 )
		   {
			$ti1 = date('d.m.Y h:i:s',$ti1);
			$ti2 = date('d.m.Y h:i:s',$usedlogdate);

			//IPS_LogMessage("Stunde bereits gezaehlt",$ti1."-".$ti2);
			}
		else
		   {
			$ti1 = date('d.m.Y h:i:s',$ti1);
			$ti2 = date('d.m.Y h:i:s',$usedlogdate);

			//IPS_LogMessage("Stunde wird gezaehlt",$ti1."-".$ti2);

			$neuerverbrauch = $verbrauch + $oldVerbrauch;
         if (GetValue($varGesamtverbrauch) != $neuerverbrauch )
				SetValueFloat ($varGesamtverbrauch,$neuerverbrauch);

		   }

	   }
	   

	$text =  "PW0049 Buffer - ".IPS_GetName($myCat) . "[".$buf."]";
	$text = $text . "\nLogadresse:" .$LogAddressRaw . " Logstelle:".$bufferstelle;
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
*	"0061" empfangen	- Circle hinzugefuegt
*  Antwort auf ?
*******************************************************************************/
function plugwise_00061_received($buf)
	{
	GLOBAL $CircleGroups;

	$mac = substr($buf,8,16);

	$text = "Circle hinzugefuegt [0061] :" . $mac;
	
	
	// Uhrzeit stellen
   PW_SendCommand("0016".$mac.unixtime2pwtime());
   //logging($text,'plugwiseaddcircle.log' );
	logging($text,'plugwiseerror.log' );
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
		$obj = IPS_GetVariable(IPS_GetObjectIDByIdent("LastMessage",$item));
		$t = ($now - ($obj["VariableUpdated"]))/60; // Zeit in Minuten wann letzte Aktualisierung
      //SetValue(IPS_GetVariableIDByName ("LastMessage", $item),$t);
		//IPS_LogMessage(".........",$t);
		$id = IPS_GetObjectIDByIdent("LastMessage",$item);
		$t = GetValue($id);
      if ( $t > 5 )  // laenger als 5 Minuten keine Daten
      	{
      	$id = IPS_GetVariableIDByName("Error", $item);
			if ( GetValue($id ) != 1 )
				SetValue($id,1);
			}
		else
		   {
      	$id = IPS_GetVariableIDByName("Error", $item);
			if ( GetValue($id ) != 0 )
				SetValue($id,0);
			}
			
		$id_info = IPS_GetObject($item);

		PW_SendCommand("0012".$id_info['ObjectIdent']);
		
		SetValue(IPS_GetVariableIDByName ("LastMessage", $item),microtime(true));

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
	GLOBAL $idCatCircles;
	
	
	$id1 = IPS_GetObjectIDByIdent("SYSTEM_MAIN",$idCatOthers);
	
	if ( ID_GESAMTVERBRAUCH != 0 )
		if ( IPS_ObjectExists(ID_GESAMTVERBRAUCH) )
	   	{
      	$d = GetValue(ID_GESAMTVERBRAUCH);
			$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$id1);
			if (GetValue($id) <> $d)
				SetValue($id,$d);
		      
	   	}
   if ( ID_LEISTUNG != 0 )
		if ( IPS_ObjectExists(ID_LEISTUNG) )
	   	{
      	$d = GetValue(ID_LEISTUNG);
			$id = IPS_GetObjectIDByIdent('Leistung',$id1);
			if (GetValue($id) <> $d)
				SetValue($id,$d);

	   	}

   if ( ID_LEISTUNG == 0 and ID_GESAMTVERBRAUCH == 0)
      {
		$l = 0;
		$g = 0;
		foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
			{  
			$data = GetValueFloat(IPS_GetObjectIDByIdent("Leistung",$item));
			$l = $l + $data;
			$data = GetValueFloat(IPS_GetObjectIDByIdent("Gesamtverbrauch",$item));
			$g = $g + $data;

			}

		$id = IPS_GetObjectIDByIdent('Leistung',$id1);
      if (GetValue($id) <> $l)
			SetValue($id,$l);
      
		$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$id1);
		if (GetValue($id) <> $g)
      	SetValue($id,$g);

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

		$gruppenid = IPS_GetObjectIDByIdent(umlaute_ersetzen($gruppe),$idCatOthers);
		
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
		$gruppenid = IPS_GetObjectIDByIdent(umlaute_ersetzen($gruppe),$idCatOthers);

		$wert = $array_leistung[$gruppe];
		$id = IPS_GetObjectIDByIdent('Leistung',$gruppenid);
		if (GetValue($id) <> $wert)
			SetValue($id,$wert);

		$wert = $array_verbrauch[$gruppe];
		$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$gruppenid);
      if (GetValue($id) <> $wert)
			SetValue($id,$wert);

	   }


	}

/***************************************************************************//**
* @}
* @}
*******************************************************************************/
?>