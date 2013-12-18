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

	//$time = date("Y-m-d H:i:s",time());
	//mysql_add(MYSQL_TABELLE_GESAMT,$time, "Test" ,1007);
	//mysql_add(MYSQL_TABELLE_LEISTUNG,$time, "Test" ,1007);

	switch ($_IPS['SENDER'])
			{
			Case "RunScript"			:	break;
			Case "Execute"				:	break;
			Case "TimerEvent"			:	ping_circles();
												berechne_gruppenverbrauch();
												hole_gesamtverbrauch();
												berechne_restverbrauch();
												update_webfront_123("REFRESH");
												request_circle_data();
												check_zaehleractions();
												break;
			Case "Variable"			:	schaltbefehl($_IPS['VARIABLE'],$IPS_VALUE);break;
			Case "WebFront"			:  handle_webfront($_IPS['VARIABLE']);  break;
			Case "RegisterVariable"	:	$buf = $IPS_VALUE;
												switch ((substr($buf,0,4)))
													{
													case "0000":	plugwise_0000_received($buf);	break;
													case "0003":	plugwise_0003_received($buf);	break;
													case "0006":	plugwise_0006_received($buf); break;
													case "000E":	plugwise_000E_received($buf); break;
													case "0011":  	plugwise_0011_received($buf);	break;
													case "0013":	plugwise_0013_received($buf); break;
													case "0019":   plugwise_0019_received($buf);	break;
													case "0024":   plugwise_0024_received($buf);	break;
													case "0027":   plugwise_0027_received($buf);	break;
													case "0049": 	plugwise_0049_received($buf);	break;
													case "003A":   plugwise_003A_received($buf);	break;
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




/***************************************************************************//**
*  Sende ein Ping "000D" an alle Circles
*******************************************************************************/
function ping_circles()
	{

	GLOBAL $CircleGroups;

	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.GRAPH";
   $IdGraph   = @get_ObjectIDByPath($VisuPath,true);
	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	$menupunkt = GetValue($id);

	if ( $menupunkt != 6 )
	   return;

   $file = 'plugwiseping.log';
	$logdatei = IPS_GetKernelDir() . "logs\\Plugwise\\" . $file;
	if ( file_exists($logdatei) )
		unlink($logdatei);
		
	foreach( $CircleGroups as $circle)
	   {
	   $mac = $circle[0];
		$cmd = "000D".$mac;
		PW_SendCommand($cmd,$mac);
	   }

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

		if ( $status == 0 )
			$status = false;
		if ( $status == 1 )
		   $status = true;

	   circle_on_off($mac,$status);

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
							//if ( GetValue($id) <> true)
							SetValue($id,True);
							break;

		case "00DE":  	// ausgeschaltet
							// print "Ausgeschaltet MAC".substr($buf,12,16);
							logging( "R - ".$buf ." Circle ausgeschaltet");
							$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
							$id = IPS_GetVariableIDByName ("Status", $myCat);
							//if ( GetValue($id) <> false)
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
	      PW_SendCommand($cmd,$mac);
	      }
	      
	   $text = $circle[0];
	   //IPS_LogMessage("........",$text);
	   }
	   
	unknowncircles($mac.",0");


   //logging($text,'plugwiseunknown.log' );

	}


/***************************************************************************//**
*	"000E" empfangen	- Ping
*  Antwort auf "000D"
*******************************************************************************/
function plugwise_000E_received($buf)
	{
   $mac = substr($buf,8,16);

   $hrssi1 = (substr($buf,24,2));
   $hrssi2 = (substr($buf,26,2));
   $hmsec  = (substr($buf,28,4));

	$rssi1 = hexdec($hrssi1);
	$rssi2 = hexdec($hrssi2);

	//$rssi1 = $rssi1 & 127;
	//$rssi2 = $rssi2 & 127;

	$msec = hexdec($hmsec);

	$text = ",".$mac.",".$rssi1.",".$rssi2.",".$msec ;
	logging($text,'plugwiseping.log',true );


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

	$totalpulse = substr($buf,32,8);
	$unknown1   = substr($buf,40,4);
	$unknown2   = substr($buf,44,4);
	$unknown3   = substr($buf,48,4);


	if ( !$myCat)
	   {
		$text = "Stromverbrauch von unbekannt empfangen [".$buf."]";
		logging($text,'plugwisepowerinformation.log' );
		return;
	   }

	// Circleantwortzeit berechnen
	//$t = time()  ;
	//SetValue(IPS_GetVariableIDByName ("LastMessage", $myCat),$t);

	$id = IPS_GetVariableIDByName("LastMessage", $myCat);
	$obj = IPS_GetObject(IPS_GetObjectIDByIdent("LastMessage",$myCat));
	$string = $obj['ObjectInfo'];
	$string_array = explode(";", $string);

	$start_timestamp = @$string_array[0];
	$ende_timestamp  = @$string_array[1];
	$ende_timestamp  = microtime(true);
	$string = $start_timestamp .";".$ende_timestamp;
	$diff = round(($ende_timestamp - $start_timestamp) * 1000);

	IPS_SetInfo($id,$string);

	$string1 = GetValue(IPS_GetVariableIDByName ("LastMessage", $myCat));
	$string_array = explode(';',$string1);
   $telegramm_counter = 0;
	if ( isset($string_array[1]) )
	   {
		$telegramm_counter = $string_array[1];
		$telegramm_counter = $telegramm_counter + 1;
		}
   SetValue(IPS_GetVariableIDByName ("LastMessage", $myCat),$diff.";".$telegramm_counter);
	$LaufzeitID = @IPS_GetVariableIDByName ("Laufzeit", $myCat);
	if ( $LaufzeitID )
   	SetValue($LaufzeitID,$diff);

	//IPS_LogMessage("Info","[".$string."]".$diff);
	//*********************************************








	If ($pulse == "FFFF")
		{	// Circle ausgeschaltet, meldet FFFF ( nicht immer ?? )
			// scheinbar nicht
		$id = IPS_GetVariableIDByName("Leistung",$myCat);
		if ( GetValueFloat($id) != 0 )
			SetValueFloat($id,0);
		
		// FFFF heisst nicht ausgeschaltet !!!
		//$id = IPS_GetVariableIDByName ("Status", $myCat);
		//IPS_LogMessage("....",$id);
		//if ( GetValue($id) <> false)
		//	SetValue($id,false);

		$text = $myCat . " Circle ausgeschaltet FFFF . " . $buf;
		logging($text,'plugwiseerror.log' );
		}
	else
		{
		$gainA	 = @GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
		$gainB	 = @GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
		$offTotal = @GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
		$offNoise = @GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));
      
		$kalib_str = @GetValueString(IPS_GetVariableIDByName("Kalibrierdaten", $myCat));
		
		if ( $kalib_str != false )
		   {
			$debug = false;
			
		   //IPS_LogMessage("Kalibrierdaten vorhanden",$kalib_str);
		   $kalib_array = explode(";", $kalib_str);
         $gainA_      = @floatval($kalib_array[0]);
         $gainB_      = @floatval($kalib_array[1]);
			$offTotal_   = @floatval($kalib_array[2]);
			$offNoise_   = @floatval($kalib_array[3]);

			$diff = $gainA - $gainA_;

			if ( $debug )
			   {
				if ( round($gainA,10) != round($gainA_,10) )
			   	IPS_LogMessage("Kalibrierdaten ungleich gainA","[".$gainA."][".$gainA_."]".$diff);
				else
					IPS_LogMessage("Kalibrierdaten gleich gainA","[".$gainA."][".$gainA_."]".$diff);
				if ( round($gainB,10) != round($gainB_,10) )
			   	IPS_LogMessage("Kalibrierdaten ungleich gainB","[".$gainB."][".$gainB_."]".$diff);
				else
					IPS_LogMessage("Kalibrierdaten gleich gainB","[".$gainB."][".$gainB_."]".$diff);
				if ( round($offTotal,10) != round($offTotal_,10) )
			   	IPS_LogMessage("Kalibrierdaten ungleich offTotal","[".$offTotal."][".$offTotal_."]".$diff);
				else
					IPS_LogMessage("Kalibrierdaten gleich offTotal","[".$offTotal."][".$offTotal_."]".$diff);
				if ( round($offNoise,10) != round($offNoise_,10) )
			   	IPS_LogMessage("Kalibrierdaten ungleich offNoise","[".$offNoise."][".$offNoise_."]".$diff);
				else
					IPS_LogMessage("Kalibrierdaten gleich offNoise","[".$offNoise."][".$offTotal_."]".$diff);
				}

			$gainA	 = $gainA_;
			$gainB	 = $gainB_;
			$offTotal = $offTotal_;
			$offNoise = $offNoise_;


	  
			$verbrauch = pulsesToKwh(hexdec($totalpulse), $offNoise, $offTotal, $gainA, $gainB);
			$type = "ZAEHLER" ;
			$parent = 0 ;
			$objectname = $mcID;
			$leistung = 0;

   		$akt_tk   = aktuelle_kosten($type,$myCat,$objectname,$leistung);  // aktuelle Kosten und Tarif
   		$kosten   = $akt_tk['KOSTEN'];
   		$akt_tarif= $akt_tk['TARIF'];
   		$kt_preis = $akt_tk['PREISKWH'];
			
		   $id_kosten = IPS_GetVariableIDByName("Kosten",$myCat);

			//$kt_str = $kt_preis . ";" . $verbrauch ;
			//IPS_SetInfo($id_kosten,$kt_str);  // Preisstring fuer die naechste Zeit merken
			$ttext = "";
			// letzten Stundenpreis holen
			$obj_info_kosten = IPS_GetObject($id_kosten);
			$alt_stundenpreis = floatval($obj_info_kosten['ObjectInfo']);
			// stunden_preis ist der aktuelle Veerbrauchspreis in dieser Stunde
			// wird bei Stundenbeginn neu gestartet.
			$stunden_preis = $verbrauch * $kt_preis;
			
			$stunden_preis = round($stunden_preis,6);
			
			$diff_stunden_preis = 0;

			if ( $stunden_preis > $alt_stundenpreis )
				{
			   $diff_stunden_preis = $stunden_preis - $alt_stundenpreis;
				$ttext = ">Alt:".$alt_stundenpreis."-Neu:".$stunden_preis;
				}
			if ( $stunden_preis < $alt_stundenpreis ) // Stundenbeginn
				{
			   $diff_stunden_preis = $stunden_preis;
				$ttext = "<Neu:".$stunden_preis;
				//IPS_logmessage("....",$stunden_preis."-".$alt_stundenpreis);
				}
//			if ( $stunden_preis == $alt_stundenpreis )
//			   {
//				IPS_logmessage("....",$stunden_preis."-".$myCat."-".$alt_stundenpreis);
//
//			   }

				
			zaehleKostenhoch($myCat,$diff_stunden_preis);
			
			$valstring = strval($stunden_preis);
			$valstring = str_replace(",",".",$valstring);

			IPS_SetInfo($id_kosten,$valstring);
			//$kt_preis = floatval($kt_preis);
			//$string_kt_preis = number_format($kt_preis,2,'.','');
         $string_kt_preis = str_replace(",", ".", $kt_preis);
			$text = time() . ";" . $mcID .";".$totalpulse.";".$unknown1.$unknown2.";".$unknown3.";".$verbrauch.";".$string_kt_preis.";".$stunden_preis.";".$diff_stunden_preis.";".$ttext;
			$log_type = "01";
			circle_data_loggen($log_type,$text,$mcID .'plugwise_data.log',$myCat );



		   }
		
		// keine Kalibrierung
		if ( $gainA == 0 and $gainB == 0 and $offTotal == 0 and $offNoise == 0 )
		   {IPS_LogMessage("keine Kalibrierdaten vorhanden",$kalib_str);
		   // Kalibrierungsdaten vom Circle abrufen
		   $id_info = IPS_GetObject($myCat);
			PW_SendCommand("0026".$id_info['ObjectIdent'],$id_info['ObjectIdent']);
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
		//if ( GetValueFloat($id) != $Leistung )
			SetValueFloat($id,$Leistung);

//		$id = IPS_GetVariableIDByName ("Status", $myCat);
//		if ( GetValue($id) <> true)
//			SetValue($id,True);

		$time = date('Y-m-d H:i:s');
		$group_name = find_group($mcID);
		if ( defined('MYSQL_ANBINDUNG') )
      	mysql_add(MYSQL_TABELLE_LEISTUNG,$time, IPS_GetName($myCat),$Leistung,$myCat,$group_name);
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

	$text = " Adresse empfangen [".$buf."] ";

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

	$autorestore = false;
   if ( defined('AUTORESTORESWITCH') )    // nach Stromausfall alten Zustand wiederherstellen
   	if ( AUTORESTORESWITCH == true )
         $autorestore = true;
         
   if ( $autorestore == false )
      {
	   

		$einaus    = substr($buf,41,1);
		$id        = IPS_GetVariableIDByName("Status",$myCat);
		$aktstatus = GetValue($id);
	
		if ( GetValue($id) != $einaus ){
			SetValue(IPS_GetVariableIDByName("Status",$myCat),$einaus);}
		}
	else
	   {

		$einaus     = substr($buf,41,1);
		$id         = IPS_GetVariableIDByName("Status",$myCat);
		$sollstatus = GetValue($id);

		if ( $einaus == "0" OR $einaus == "1" )
		   {
			if ( $einaus == "0" )
				$iststatus = false;
			if ( $einaus == "1" )
				$iststatus = true;

	   	//IPS_LogMessage("Plugwise",$myCat."-".$iststatus."-".$sollstatus);

		   if ( $iststatus != $sollstatus )
		      {
	   		IPS_LogMessage("Plugwise",$myCat."- Iststatus ungleich Sollstatus");
            circle_on_off($mcID,$sollstatus);
		      }
		   
		   }
	   
	   }


	
	$logadress = intval((hexdec(substr($buf,32,8))));
	$id = IPS_GetVariableIDByName("LogAddress",$myCat);
	if ( GetValue($id) != $logadress )
   	SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),$logadress);
   
	$text = IPS_GetName($myCat);
	$text = $text." Logadresse[".intval((hexdec(substr($buf,32,8)) - 278528) / 32)."][".hexdec(substr($buf,32,8))."]";


	$hw_version = substr($buf,44,4)."-".substr($buf,48,4)."-".substr($buf,52,4);
	$sw_version = date('d.m.Y h:i:s',hexdec(substr($buf,56,8)));
	$nodetype = substr($buf,64,2);      // nicht in Doku
	
	$text = $text. " Hardwareversion: ".$hw_version;
	$text = $text. " Softwareversion: ".$sw_version." ";
	$text = $text. " Nodetype: ".$nodetype." ";

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

	//$buf = substr($buf,0,24) . 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
	//IPS_LogMessage("Plugwise Kalibrierung : ",substr($buf,0,48));
	
	
	$gaina    = bintofloat(substr($buf,24,8));
	$gainb    = bintofloat(substr($buf,32,8));
	$offTotal = bintofloat(substr($buf,40,8));
	$offNoise = bintofloat(substr($buf,48,8));

	if (substr($buf,48,8)=="00000000")
	   {
      $offNoise = 0;
		}
	else
		{
      $offNoise = bintofloat(substr($buf,48,8));
		}

	if (substr($buf,24,8)=="FFFFFFFF")
		{
		$gaina = 1;
		IPS_LogMessage("Plugwise Kalibrierung GainA: ",substr($buf,24,8));
		}
	if (substr($buf,32,8)=="FFFFFFFF")
		{
		$gainb = 0;
		IPS_LogMessage("Plugwise Kalibrierung GainB: ",substr($buf,32,8));
		}
	if (substr($buf,40,8)=="FFFFFFFF")
		{
		$offTotal = 0;
		IPS_LogMessage("Plugwise Kalibrierung OffTotal: ",substr($buf,40,8));
		}
	if (substr($buf,48,8)=="FFFFFFFF")
		{
		$offNoise = 0;
		IPS_LogMessage("Plugwise Kalibrierung OffNoise: ",substr($buf,48,8));
		}

		
	$kal_str  = $gaina.";".$gainb.";".$offTotal.";".$offNoise;
	//IPS_LogMessage("kalib",$offNoise."-".substr($buf,48,8));

	SetValue(CreateVariable("Kalibrierdaten", 3,$myCat,0,"",0,0),$kal_str);


	$text = IPS_GetName($myCat) . "Kalibrierungsdaten empfangen [".$buf."]";
	
	logging($text,'plugwisecalibration.log' );
	
	}
	

/***************************************************************************//**
*	"003A" empfangen	-  aktuelle RTC empfangen
*  Antwort auf "0029" - RTC auslesen

      <command number="003a" vnumber="1.0" implementation="Plugwise.IO.Commands.V10.PWGetRTCReplyV1_0">
        <arguments>
          <argument name="macId" length="16"/>
          <argument name="seconds" length="2"/>
          <argument name="minutes" length="2"/>
          <argument name="hour" length="2"/>
          <argument name="dayOfWeek" length="2"/>
          <argument name="day" length="2"/>
          <argument name="month" length="2"/>
          <argument name="year" length="2"/>
        </arguments>
      </command>

*/
function plugwise_003A_received($buf)
	{
	GLOBAL $idCatCircles;

	$mcID = substr($buf,8,16);


	}
/***************************************************************************//**
*	"003F" empfangen	-  aktuelle Uhrzeit empfangen
*  Antwort auf "003E" - Uhrzeit auslesen
*
*	command number="003f"
*
*	vnumber="1.0" Plugwise.IO.Commands.V10.PWGetClockReplyV1_0
*  	name="macId" 					length="16"
*     name="hour" 					length="2"
*     name="minutes" 				length="2"
*     name="seconds" 				length="2"
*     name="dayOfWeek" 				length="2"
*     name="hoursNotMinutes" 		length="2"
*
*	vnumber="1.1" Plugwise.IO.Commands.V20.PWGetClockReplyV1_1
*		name="macId" 					length="16"
*   	name="hour" 					length="2"
*		name="minutes" 				length="2"
*     name="seconds" 				length="2"
*     name="dayOfWeek" 				length="2"
*     name="hoursNotMinutes" 		length="2"
*     name="scheduleCRC" 			length="4"
*
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
	$text = IPS_GetName($myCat).": ".date("d.m.Y H:i:s", $myTime)." Abweichung: " . $abweichung ." Sekunden. " .unixtime2pwtime();

	if ( $abweichung > 10  or $abweichung < -10 ) // Abweichung groesser 10 Sekunden
		{
		$text = $text . " Uhrzeit wird gestellt.";
 		//PW_SendCommand("0016".IPS_GetName($myCat).unixtime2pwtime(),IPS_GetName($myCat));

   	//$id_info = IPS_GetObject($item);
 		PW_SendCommand("0016".$mcID.unixtime2pwtime(),IPS_GetName($myCat));

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
	$mcID = substr($buf,8,16);

	$group_name = find_group($mcID);
	
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
	$gaina	 = @GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
	$gainb	 = @GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
	$offTotal = @GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
	$offNoise = @GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));

	$kalib_str = @GetValueString(IPS_GetVariableIDByName("Kalibrierdaten", $myCat));
	if ( $kalib_str != false )
		{
		//IPS_LogMessage("Kalibrierdaten vorhanden",$kalib_str);
		$kalib_array = explode(";", $kalib_str);
      $gainA_      = @floatval($kalib_array[0]);
      $gainB_      = @floatval($kalib_array[1]);
		$offTotal_   = @floatval($kalib_array[2]);
		$offNoise_   = @floatval($kalib_array[3]);

		$gaina	 = $gainA_;
		$gainb	 = $gainB_;
		$offTotal = $offTotal_;
		$offNoise = $offNoise_;

		}

	//IPS_logMessage("........",$gaina);
	//IPS_logMessage("........",$gainb);
	//IPS_logMessage("........",$offTotal);
	//IPS_logMessage("........",$offNoise);

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

		$stunde1 = intval(date('H',$usedlogdate));
		$stunde2 = intval(date('H',$ti1));

      //IPS_LogMessage("...",$stunde1."-".$stunde2);

		if ( $stunde1 == $stunde2 )
		   {
			$ti1 = date('Y-m-d H:i:s',$ti1);
			$ti2 = date('Y-m-d H:i:s',$usedlogdate);
			
			//$time = date('d.m.y H:i:s');
			$lad = hexdec($LogAddressRaw);
			$lad = $lad + ( $bufferstelle * 8 );
			$lad = dechex($lad);

			if ( defined('MYSQL_ANBINDUNG') )
      		mysql_add(MYSQL_TABELLE_GESAMT,$ti2, IPS_GetName($myCat),$verbrauch,$myCat,$group_name,$lad);
     		

			//IPS_LogMessage("Stunde bereits gezaehlt",$ti1."-".$ti2);
			}
		else
		   {
			$ti1 = date('Y-m-d H:i:s',$ti1);
			$ti2 = date('Y-m-d H:i:s',$usedlogdate);

			//IPS_LogMessage("Stunde wird gezaehlt",$ti1."-".$ti2);

			//$time = date('d.m.y H:i:s');
			$lad = hexdec($LogAddressRaw);
			$lad = $lad + ( $bufferstelle * 8 );
			$lad = dechex($lad);
			if ( defined('MYSQL_ANBINDUNG') )
      		mysql_add(MYSQL_TABELLE_GESAMT,$ti2, IPS_GetName($myCat),$verbrauch,$myCat,$group_name,$lad);


			$neuerverbrauch = $verbrauch + $oldVerbrauch;
         if (GetValue($varGesamtverbrauch) != $neuerverbrauch )
				SetValueFloat ($varGesamtverbrauch,$neuerverbrauch);

			// versuche den Stundenpreis zu finden
			$text = $usedlogdate . ";" . $mcID . ";" .$ti2 . ";" . $stunde1 . ";" . $verbrauch;
			$log_type = "60";
         circle_data_loggen($log_type,$text,$mcID .'plugwise_data.log',$myCat );

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
function plugwise_0061_received($buf)
	{
	GLOBAL $CircleGroups;

	$mac = substr($buf,8,16);

	$text = "Circle hinzugefuegt [0061] :" . $mac;
	
	
	// Uhrzeit stellen
   PW_SendCommand("0016".$mac.unixtime2pwtime(),$mac);
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
		$action = true;
		}
	else
		{
		$action = false;
		}
		
	$mac = $id_info['ObjectIdent'];
	circle_on_off($mac,$action);
	
//	$cmd = "0017".$id_info['ObjectIdent']."0".$action;
//	PW_SendCommand($cmd);

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
		//$id = IPS_GetObjectIDByIdent("LastMessage",$item);
		//$t = GetValue($id);
		
		if ( defined('REFRESH_TIME') )
			$refreshtime = REFRESH_TIME;
		else
			$refreshtime = 1 ;
		
		$timeoutcircle = $refreshtime * 3 ;
      if ( $t > $timeoutcircle )  // laenger als x Minuten keine Daten
      	{
      	$id = IPS_GetVariableIDByName("Error", $item);
      	
         if ( defined('RUNSCRIPT_CIRCLEFAILED') )
         	if ( GetValue($id ) == 0 )
					if ( IPS_ScriptExists(RUNSCRIPT_CIRCLEFAILED) )
				   	IPS_RunScriptEx(RUNSCRIPT_CIRCLEFAILED,array("CIRCLE" => IPS_GetName($item)));

      	
			if ( GetValue($id ) != 1 ) // Error setzen
				SetValue($id,1);
				
				
			// wenn Circle nicht erreichbar Leistung auf 0
			//IPS_LogMessage("Plugwise Circle ausgefallen",$t);
      	$id = IPS_GetVariableIDByName("Leistung", $item);
			if ( GetValue($id ) != 0 )
				SetValue($id,0);

			}
		else
		   {
      	$id = IPS_GetVariableIDByName("Error", $item);
			if ( GetValue($id ) != 0 )
				SetValue($id,0);
			}
			
		$id_info = IPS_GetObject($item);

		
		
		$id = IPS_GetVariableIDByName("LastMessage", $item);
		$obj = IPS_GetObject(IPS_GetObjectIDByIdent("LastMessage",$item));
		$string = $obj['ObjectInfo'];
		$string_array = explode(";", $string);

		$start_timestamp = @$string_array[0];
		$ende_timestamp  = @$string_array[1];
		$start_timestamp = microtime(true);
		$string = $start_timestamp .";".$ende_timestamp;

		//IPS_LogMessage(".......",$string);
		
		//$timestamp = microtime(true).";0";
		IPS_SetInfo(IPS_GetObjectIDByIdent("LastMessage",$item),$string);
		PW_SendCommand("0012".$id_info['ObjectIdent'],$id_info['ObjectIdent']);
		
		PW_SendCommand("0023".$id_info['ObjectIdent'],$id_info['ObjectIdent']);
		

		}
	}
  
  
/***************************************************************************//**
* Gesamtverbrauch holen
* 
* 
*******************************************************************************/
function hole_gesamtverbrauch()
	{
		
	GLOBAL $idCatOthers;
	GLOBAL $idCatCircles;
	GLOBAL $SystemStromzaehlerGroups;
	GLOBAL $CircleGroups;
	GLOBAL $ExterneStromzaehlerGroups;

	// Wo soll der Gesamtverbrauch hin
	$id1 = @IPS_GetObjectIDByIdent("SYSTEM_MAIN",$idCatOthers);
	if ( $id1 == false )
		$id1 = IPS_GetObjectIDByIdent("Gesamt",$idCatOthers);

	
	$id_gesamt   = 0;
	$id_leistung = 0;

   // IDs aus der Konfig lesen - alte Version
   if ( defined('ID_GESAMTVERBRAUCH') )
		if ( ID_GESAMTVERBRAUCH != 0 )
			$id_gesamt = ID_GESAMTVERBRAUCH;

   if ( defined('ID_LEISTUNG') )
		if ( ID_LEISTUNG != 0 )
			$id_leistung = ID_LEISTUNG;

	$id_math = "*";
	$id_faktor = 1;
   // IDs aus der Konfig lesen - neue Version
	if ( isset( $SystemStromzaehlerGroups[0][2] ) )
	   {
		$id_leistung_str = str_replace(" ", "", $SystemStromzaehlerGroups[0][2]); ;
      $id_leistung = intval($SystemStromzaehlerGroups[0][2]);  // erster Teil
		$id_math = substr($id_leistung_str,5,1);
		$id_faktor = substr($id_leistung_str,6);
		}

	if ( isset( $SystemStromzaehlerGroups[0][3] ) )
      $id_gesamt = intval($SystemStromzaehlerGroups[0][3]);


	// wenn id nicht 0 kopiere Gesamtverbrauch nach Plugwise - nicht optimal
	if ( $id_gesamt != 0 )
		if ( IPS_ObjectExists($id_gesamt) )
	   	{
      	$d = GetValue($id_gesamt);
			$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$id1);
			$oldvalue = GetValue($id);
			
			$diff = $d - $oldvalue;

			$kostenunterschied = 0;
			if ( $diff > 0 )
				{
				
				// aktuellen Strompreis holen
            $akt_kosten = aktuelle_kosten("GESAMT",0,"",1000);
				$akt_preis = $akt_kosten['PREISKWH'];
				$kostenunterschied = ($akt_preis * $diff)/100;  // in Euro

            $idkosten = IPS_GetObjectIDByIdent('Kosten',$id1);
				$lastkosten = GetValue($idkosten);
				$neukosten = $lastkosten + $kostenunterschied;
				SetValue($idkosten,$neukosten);
            //IPS_Logmessage($oldvalue,$diff."-".$akt_preis."-".$kostenunterschied."-".$neukosten);

				}
			
			if (GetValue($id) <> $d)
				SetValue($id,$d);
		   $text = "Gesamt Hauptzaehler:".$id_gesamt."-".$d;
		   logging($text,"Gesamtleistung.log",true);

	   	}
	 
	// wenn id nicht 0 kopiere Gesamtleistung nach Plugwise - nicht optimal
   if ( $id_leistung != 0 )
		if ( IPS_ObjectExists($id_leistung) )
	   	{
      	$d = GetValue($id_leistung);

			if ( $id_math == '*' )
			   $d = $d * $id_faktor;
			   
			$id = IPS_GetObjectIDByIdent('Leistung',$id1);
			if (GetValue($id) <> $d)
				SetValue($id,$d);
		   $text = "Leistung Hauptzaehler:".$id_gesamt."-".$d;
		   logging($text,"Gesamtleistung.log",true);

	   	}
	   	
	// wenn 0 dann alle Circles addieren die in Config mit TRUE
	// dann gibt es keinen Hauptzaehler
   if ( $id_leistung == 0 and $id_gesamt == 0)
      {
		$l = 0;
		$g = 0;
		$k = 0;

		foreach($CircleGroups as $item)
		   { // addiere Circles
		   $text = "Gesamt:".$item[0];
		   logging($text,"Gesamtleistung.log",true);
		   if ( (isset($item[7]) and $item[7] == true) or (isset($item[7]) and $item[7] == 1) or (isset($item[7]) and $item[7] == "1") )
		      {
		   	$id = @IPS_GetObjectIDByIdent($item[0],$idCatCircles);
		      if ( $id )
		         {
					$data1 = GetValueFloat(IPS_GetObjectIDByIdent("Leistung",$id));
					$l = $l + $data1;
					$data2 = GetValueFloat(IPS_GetObjectIDByIdent("Gesamtverbrauch",$id));
					$g = $g + $data2;
					$data3 = GetValueFloat(IPS_GetObjectIDByIdent("Kosten",$id));
					$k = $k + $data3;

					$text = "ID:".$id."-".$data1."-".$l."-".$data2."-".$g."-".$data3."-".$k;
		   		logging($text,"Gesamtleistung.log",true);
					}
				}

		   }
		   
		foreach($ExterneStromzaehlerGroups as $item)
		   { // addiere Externe
		   $text = "Gesamt:".$item[0];
		   logging($text,"Gesamtleistung.log",true);
		   if ( (isset($item[7]) and $item[7] == true) or (isset($item[7]) and $item[7] == 1) or (isset($item[7]) and $item[7] == "1") )
		      {
		      $id_leistung = intval($item[2]);
		      $id_gesamt   = intval($item[3]);

				$data1 = @GetValue($id_leistung);
				$l = $l + $data1;
				$data2 = @GetValue($id_gesamt);
				$g = $g + $data2;

				$text = "ID:".$id."-".$data1."-".$l."-".$data2."-".$g;
		   	logging($text,"Gesamtleistung.log",true);
				}

		   }


		$id = IPS_GetObjectIDByIdent('Leistung',$id1);
      if (GetValue($id) <> $l)
			SetValue($id,$l);
      
		$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$id1);
		if (GetValue($id) <> $g)
      	SetValue($id,$g);

		$id = IPS_GetObjectIDByIdent('Kosten',$id1);
		if (GetValue($id) <> $k)
      	SetValue($id,$k);

		}
		

	
	}

/***************************************************************************//**
* Restverbrauch berechnen
*******************************************************************************/
function berechne_restverbrauch()
	{
	GLOBAL $idCatOthers;
	GLOBAL $CircleGroups;
	GLOBAL $idCatCircles;
	GLOBAL $ExterneStromzaehlerGroups;

   $id = IPS_GetObjectIDByIdent("SYSTEM_MAIN",$idCatOthers);
   $so = IPS_GetObjectIDByIdent("SYSTEM_REST",$idCatOthers);

	$gesamt_leistung = GetValue(IPS_GetObjectIDByIdent('Leistung',$id));
	$gesamt_gesamt   = GetValue(IPS_GetObjectIDByIdent('Gesamtverbrauch',$id));

	$sonstid_leistung = IPS_GetObjectIDByIdent('Leistung',$so);
	$sonstid_gesamt   = IPS_GetObjectIDByIdent('Gesamtverbrauch',$so);

   $others = IPS_GetChildrenIDs($idCatOthers);


	$l = 0;
	$g = 0;

	$text = "Berechne Restverbrauch";
   logging($text,"Gesamtleistung.log",true);
	$text = "Start:".$gesamt_leistung."-".$gesamt_gesamt;
   logging($text,"Gesamtleistung.log",true);

	foreach($CircleGroups as $item)
		{  
		if ( (isset($item[7]) and $item[7] == true) or (isset($item[7]) and $item[7] == 1) or (isset($item[7]) and $item[7] == "1") )
		   {
		   $id = @IPS_GetObjectIDByIdent($item[0],$idCatCircles);

		   if ( $id )
		      {
				$datal = GetValueFloat(IPS_GetObjectIDByIdent("Leistung",$id));
				$l = $l + $datal;


				$datag = GetValueFloat(IPS_GetObjectIDByIdent("Gesamtverbrauch",$id));
				$g = $g + $datag;
				
				$text = $id.":".$datal."-".$datag;
   			logging($text,"Gesamtleistung.log",true);

				
				}
			}

		}

		if ( isset($ExterneStromzaehlerGroups) )
		foreach($ExterneStromzaehlerGroups as $item)
		   { // addiere Externe
		   if ( (isset($item[7]) and $item[7] == true) or (isset($item[7]) and $item[7] == 1) or (isset($item[7]) and $item[7] == "1") )
		      {
		      $id_leistung = intval($item[2]);
		      $id_gesamt   = intval($item[3]);

				$data1 = @GetValue($id_leistung);
				$l = $l + $data1;
				$data2 = @GetValue($id_gesamt);
				$g = $g + $data2;
				
				
				$text = $id_leistung."EX:".$data1."-".$data2;
   			logging($text,"Gesamtleistung.log",true);

				}

		   }


   $sonst_leistung = $gesamt_leistung - $l ;
   $sonst_gesamt   = $gesamt_gesamt - $g ;

	$text = "Ergebnis:".$sonst_leistung."|".$sonst_gesamt;
   logging($text,"Gesamtleistung.log",true);

	if ( $sonst_leistung < 0 )
		$sonst_leistung = 0;
	if ( $sonst_gesamt < 0 )
		$sonst_gesamt = 0;


	SetValue($sonstid_leistung,$sonst_leistung);
	SetValue($sonstid_gesamt  ,$sonst_gesamt);

	}

/***************************************************************************//**
* Gruppenverbrauch berechnen
*******************************************************************************/
function berechne_gruppenverbrauch()
	{
	GLOBAL $CircleGroups;
	GLOBAL $idCatCircles;
	GLOBAL $idCatOthers;
   GLOBAL $ExterneStromzaehlerGroups;

	$others = IPS_GetChildrenIDs($idCatOthers);
	$text = "Berechne Gruppenverbrauch";
   logging($text,"Gesamtleistung.log",true);
   
   // erstelle ein array mit den Gruppen aus den Circles
   foreach ( $CircleGroups as $group )
      if ( $group[0] != "" )
         {
			$array_leistung[$group[2]]  = 0;
			$array_verbrauch[$group[2]] = 0;
			$array_kosten[$group[2]] = 0;

			}

	// fuege die externen Gruppen hinzu
   foreach ( $ExterneStromzaehlerGroups as $group )
   	if ( $group[0] != "" )
         {
			$array_leistung[$group[1]]  = 0;
			$array_verbrauch[$group[1]] = 0;
			}

	// gehe alle Circles durch
	foreach ( $CircleGroups as $group )
		{
      if ( $group[0] != "" )
         {
			$gruppe    = $group[2];
			$mac 	     = $group[0];
			
			if ( isset($group[7]) )
				$in_gesamt = $group[7];
			else
			   $in_gesamt = true;

			if ( isset($group[8]) )
				$in_gesamt = $group[8];


			$gruppenid = IPS_GetObjectIDByIdent(Get_IdentByName($gruppe),$idCatOthers);
		
			$id = IPS_GetObjectIDByIdent($mac,$idCatCircles);
			if ( $id )
		   	{
		   	$leistung  = GetValue(IPS_GetObjectIDByIdent('Leistung',$id));
		   	$verbrauch = GetValue(IPS_GetObjectIDByIdent('Gesamtverbrauch',$id));
		   	$kosten    = GetValue(IPS_GetObjectIDByIdent('Kosten',$id));
				
				if ( $in_gesamt )
				   {
					$array_leistung[$gruppe]  = $array_leistung[$gruppe]  + $leistung;
					$array_verbrauch[$gruppe] = $array_verbrauch[$gruppe] + $verbrauch;
					$array_kosten[$gruppe]    = $array_kosten[$gruppe] + $kosten;
               //IPS_Logmessage($gruppe,$array_kosten[$gruppe]."-".$kosten."-".$id);
					}

				}
			}
		}


	// gehe alle externen durch
	foreach ( $ExterneStromzaehlerGroups as $group )
		{ 
      if ( $group[0] != "" )
         {
			$mac 	        = $group[0];
			$gruppe       = $group[1];
			$id_leistung  = intval($group[2]);
			$id_verbrauch = intval($group[3]);

			echo $id_leistung;
			if ( isset($group[7]) )
				$in_gesamt = $group[7];
			else
			   $in_gesamt = true;

			if ( isset($group[8]) )
				$in_gesamt = $group[8];


			if ( $in_gesamt )
			   {  // soll in Gruppe gezaehlt werden
				//echo $id_leistung;
		   	$leistung  = GetValue($id_leistung);
				//echo $leistung;
		   	$verbrauch = GetValue($id_verbrauch);
				$array_leistung[$gruppe]  = $array_leistung[$gruppe]  + $leistung;
				$array_verbrauch[$gruppe] = $array_verbrauch[$gruppe] + $verbrauch;
				$text = "Gruppe:".$gruppe."-".$verbrauch."SUMME:".$array_verbrauch[$gruppe]."-".$id_leistung."-".$id_verbrauch;
   			logging($text,"Gesamtleistung.log",true);

			   }

			}
		}
		

	// Werte in die Gruppen Variablen schreiben
	$keys = array_keys($array_leistung);
	
	foreach ( $keys as $gruppe )
	   {
		$gruppenid = IPS_GetObjectIDByIdent(Get_IdentByName($gruppe),$idCatOthers);

		if ( $gruppenid > 0 )
		   {
			$wert = $array_leistung[$gruppe];
			$id = IPS_GetObjectIDByIdent('Leistung',$gruppenid);
			if (GetValue($id) <> $wert)
				SetValue($id,$wert);

			$wert = $array_verbrauch[$gruppe];
			$id = IPS_GetObjectIDByIdent('Gesamtverbrauch',$gruppenid);
      	if (GetValue($id) <> $wert)
				SetValue($id,$wert);

			$wert = 0;
			$wert = @$array_kosten[$gruppe];
			$id = IPS_GetObjectIDByIdent('Kosten',$gruppenid);
      	if (GetValue($id) <> $wert)
				SetValue($id,$wert);

				
			}
	   }



	   
	}

/***************************************************************************//**
* @}
* @}
*******************************************************************************/
?>
