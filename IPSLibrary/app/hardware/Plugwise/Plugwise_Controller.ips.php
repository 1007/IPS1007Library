<?

/*
Plugwise Skript Version 0.5
Von: Axel Philippson
Datum: 22.04.2012
Basiert auf der Vorarbeit von
1) Maarten Damen (http://www.maartendamen.com/wp-content/uploads/downloads/2010/08/Plugwise-unleashed-0.1.pdf) und
2) Jannis (http://www.ip-symcon.de/forum/f53/plugwise-ohne-server-direkt-auslesen-schalten-17348/)
3) Brownsons IPSInstaller (http://www.ip-symcon.de/forum/f74/ipsinstaller-einige-hilfreiche-scripts-autom-installation-13228/)
*/

/*
Noch zu lösende Probleme der Plugwise-Skripte:
- Befehl 0018 meldet mir immer nur genau 11 Circles - ich habe aber 18. In Zeile 104 habe ich die Abfrage auf 20 begrenzt, sollte 100 sein. Wie sieht das bei euch aus?
- Gesamtverbrauch fragt nicht den Buffer ab, sondern ist darauf angewiesen, dass das Skript minütlich ausgeführt wird. Ergebnisse stimmen 95% mit den Werten der Source überein. Besser wäre jedoch den Buffer abzufragen, dann können die Werte aber nicht in die IPS Datenbank, sondern müssten in eine separate Datenbank abgelegt werden.
- Anlegen des Netzwerks immer noch mit der 'Source' (hab keine Zeit zum testen)
- Einbindung in IPSLibrary
*/

	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	IPSUtils_Include ("Plugwise_Configuration.inc.php",      "IPSLibrary::config::hardware::Plugwise");

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles   = get_ObjectIDByPath($CircleDataPath);
	$OtherDataPath  = "Program.IPSLibrary.data.hardware.Plugwise.Others";
   $idCatOthers    = get_ObjectIDByPath($OtherDataPath);


   $now = time();
	

/*
foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
												{  // alle Unterobjekte durchlaufen
		    									$id_info = IPS_GetObject($item);
		    									$obj = IPS_GetVariable(IPS_GetObjectIDByIdent("Leistung",$item));
		    									$t = $now - ($obj["VariableUpdated"]);
  												}
  												
*/
	switch ($_IPS['SENDER'])
			{

			Case "RunScript":    break;
			Case "Execute":      break;
			Case "TimerEvent":   //********************************************************************************
										// Alle Circles durchlaufen, Status und Verbrauch lesen
										//********************************************************************************
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
										break;

			Case "Variable":		break;
			Case "WebFront":     //***********************************************************************************
										// Zum schalten im Webfront
										//***********************************************************************************
	   								$id = IPS_GetParent($_IPS['VARIABLE']);
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

										break;

			Case "RegisterVariable": // Daten vom COMPort empfangen
										$buf = $IPS_VALUE;
										logging( "R - ".$buf . "--------------------------------------------------------");
										switch ((substr($buf,0,4)))
											{
											case "0000":	//Befehl vom Stick empfangen
																switch ((substr($buf,8,4)))
																	{
																	case "00C1":	//Schauen ob alles empfangen wurde
		 																				// print "Befehl von Stick empfangen";
		 																				logging( "R - ".$buf ."[".substr($buf,4,4)."] 00C1 empfangen Befehl bestaetigt");
																						break;
						
																	case "00D8":  	//eingeschaltet
		 																				// print "Eingeschaltet MAC".substr($buf,12,16);
		 																				logging( "R - ".$buf ." Circle eingeschaltet");
																						$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
																						SetValue(IPS_GetVariableIDByName ("Status", $myCat),True);
																						break;
						
																	case "00DE":  	//ausgeschaltet
																						// print "Ausgeschaltet MAC".substr($buf,12,16);
																						logging( "R - ".$buf ." Circle ausgeschaltet");
																						$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
																						SetValue(IPS_GetVariableIDByName ("Status", $myCat),False);
																						break;
						
																	case "00E1":   // Ein Circle nicht erreichbar
																						//print "Achtung: ein Circle ist nicht erreichbar: ".$buf;
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
																						//print "Fehler von Stick: ".$buf;  //bei allem anderen
																						logging( "R - ".$buf ." Fehler von Stick - unbekannte Antwort");
																						break;
																}
													break;   // ???

											case "0011":  	// Init
																// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
																// print "MC Adresse: ".substr($buf,8,16);
																// MC Adresse: 000D6F0000C3CA8E
																// Stick: C3CE5C

																// Kalibrierungsdaten aller Circles abrufen
																foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
																	{
																	// alle Unterobjekte
				    												$id_info = IPS_GetObject($item);
				    												
				    												PW_SendCommand("0026".$id_info['ObjectIdent']);
																	}

																break;

											case "0013":	// Aktueller Verbrauch
			   												$mcID = substr($buf,8,16);
																$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
																$pulse = substr($buf,28,4);

																SetValue(IPS_GetVariableIDByName("Error", $myCat),0);
                                                logging( "R - ".$buf ." aktueller Verbrauch");
																If ($pulse == "FFFF")
																	{
				   												// Circle ausgeschaltet, meldet FFFF
																	SetValue(CreateVariable("Leistung", 2, $myCat, 0, "~Watt.3680", 0), 0);
																	}
																else
																	{
																	// print IPS_GetName ($myCat)."\r\n";
																	// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
																	// print "MC Adresse: ".substr($buf,8,16)."\r\n";
																	// print "Pulse/s : ".substr($buf,24,4)."\r\n";
																	// print "Pulse/8s: ".substr($buf,28,4)."\r\n";
																	// print "Pulse gesamt: ".substr($buf,32,8)."\r\n";

																	$gainA	 = GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
																	$gainB	 = GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
																	$offTotal = GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
																	$offNoise = GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));

																	// Aktueller Verbrauch in Watt ermitteln
																	$value 	 = hexdec($pulse)/8;
																	$out 		 = (pow(($value+$offNoise),2)*$gainB)+(($value+$offNoise)*$gainA)+$offTotal;
																	$Leistung = (($out ) / 468.9385193)*1000;
																	$Leistung = round($Leistung,0);

																	SetValueFloat(CreateVariable("Leistung", 2, $myCat, 0, "~Watt.3680", 0), $Leistung);

																	/*
																	// Gesamtverbrauch in kWh
																	$oldPulses = GetValue(IPS_GetVariableIDByName("Pulses Stunde", $myCat));
																	$newPulses	= hexdec(substr($buf,32,8));

	 																// PRINT IPS_GetName($myCat)." - Pulses Neu: ".$newPulses.", Alt: ".$oldPulses."\n";

																	if ($newPulses > $oldPulses)
																	$delta = $newPulses - $oldPulses;
																	else if ($newPulses == $oldPulses)
																	$delta = 0;
																	else if ($newPulses < $oldPulses)
																	$delta = $newPulses;

																	// $a = IPS_GetVariable(IPS_GetVariableIDByName("Pulses Stunde", $myCat));
	               											// $seconds = time() - $a["VariableUpdated"];
																	$kWh = pulsesToKwh($delta, $offNoise, $offTotal, $gainA, $gainB);

	 																// PRINT IPS_GetName($myCat)." - Pulses Neu: ".$newPulses.", Alt: ".$oldPulses.", Delta: ".$delta.", kWh: ".$kWh."\n";

																	SetValueFloat(CreateVariable("Pulses Stunde", 2, $myCat, 0, "", 0), $newPulses);
																	$oldGesamtverbrauch = GetValue(IPS_GetVariableIDByName("Gesamtverbrauch", $myCat));
	               											SetValueFloat(CreateVariable("Gesamtverbrauch", 2, $myCat, 0, "~Electricity", 0), $oldGesamtverbrauch + $kWh);
	 																// PRINT IPS_GetName($myCat)." - Gesamtverbrauch Neu: ".$oldGesamtverbrauch + $delta.", Alt: ".$oldGesamtverbrauch.", Delta: ".$delta;
																	*/
																	}
																break;

											case "0019":   //Adressen abfragen
                                                logging( "R - ".$buf . " Adresse ");
																// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
																// print "MC Circle+: ".substr($buf,8,16)."\r\n";
			   												$mac = substr($buf,24,16);
			   												if (!($mac == "FFFFFFFFFFFFFFFF"))
																	{
																	$myCat = @IPS_GetObjectIDByIdent($mac, $idCatCircles);
																	if ($myCat == false)
																		{
					   												createCircle($mac, $idCatCircles);
																		}
																	else
																		{
																		// print "PW Node ".substr($buf,40,2).": ".IPS_GetName($myCat).", MAC:".$mac;
																		}
																	}
																else  // Circle meldet sich nicht
																   {
																   
																   }
																break;
				
											case "0024":   //Status
			   												$mcID = substr($buf,8,16);
																$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
																SetValue(IPS_GetVariableIDByName("Status",$myCat),substr($buf,41,1));
																
																SetValue(IPS_GetVariableIDByName("LogAddress",$myCat),intval((hexdec(substr($buf,32,8)) - 278528) / 32));
																$s = "";
																$s = $s."[".substr($buf,4,4)."] ";
																$s = $s."[".substr($buf,8,16)."] ";
																$s = $s."[".substr($buf,24,2)." ".substr($buf,26,2)." ".substr($buf,28,4)."] ";

																// PRINT "LogAddress ".IPS_GetName($myCat).": ".intval((hexdec(substr($buf,32,8)) - 278528) / 32);
																// PRINT "LogAddress ".IPS_GetName($myCat).": Hex ".substr($buf,32,8).", Dez ".((hexdec(substr($buf,32,8)) - 278528) / 32);

																// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
																// print "MC Adresse: ".substr($buf,8,16)."\r\n";
																// print "Jahr : ".substr($buf,24,2)."\r\n";
																// print "Monat: ".substr($buf,26,2)."\r\n";
																// print "Minuten : ".substr($buf,28,4)."\r\n";
																 //PRINT "Zeit ".IPS_GetName($myCat).": ".date('c',pwtime2unixtime(substr($buf,24,8)));
																// PRINT "Zeit ".IPS_GetName($myCat)." - Hex: ".substr($buf,24,8);

																// print "log Adresse: ".substr($buf,32,8)."\r\n";
																// print "An/aus: ".substr($buf,40,2)."\r\n";
																// print "Herz (85=50H): ".substr($buf,42,2)."\r\n";
																$s = $s. "Hardwareversion: ".substr($buf,44,12)." ";
																$s = $s. "Softwareversion: ".date('l jS F Y h:i:s',hexdec(substr($buf,56,8)))." ";
																// print "Stick 00,Cir+ 01 Cir 02: ".substr($buf,64,2)."\r\n";
                                                logging( "R - ".$buf . " Status - ".$s);
																break;

											case "0027":   //*******************************************************************
																// Kalibrierungsdaten empfangen
											               //*******************************************************************
																SetValueFloat(CreateVariable("gaina",2,$myCat,0,""),bintofloat(substr($buf,24,8)));
																SetValueFloat(CreateVariable("gainb",2,$myCat,0,""),bintofloat(substr($buf,32,8)));
																SetValueFloat(CreateVariable("offTotal",2,$myCat,0,""),bintofloat(substr($buf,40,8)));
																if (substr($buf,48,8)=="00000000")
																	SetValueFloat(CreateVariable("offNoise",2,$myCat,0,""),0);
																else
																	SetValueFloat(CreateVariable("offNoise",2,$myCat,0,""),bintofloat(substr($buf,48,8)));

																$text = "Kalibrierungsdaten empfangen [".$buf."]";
											               //logging( "R - ".$buf . " Kalibrierungsdaten empfangen");
											               logging($text,'plugwisecalibration.log' );
																$myCat = IPS_GetObjectIDByIdent(substr($buf,8,16), $idCatCircles);

																break;

											case "0049": 	// Antwort auf 0048 - historischen Verbrauch lesen (Buffer)
			   												// PRINT "Buffer: ".$buf."\n\n";
																// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
																// print "MC Adresse: ".substr($buf,8,16)."\r\n";

			   												$mcID = substr($buf,8,16);
																$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);

																// Kalibrierungsdaten laden
																$gaina	 = GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
																$gainb	 = GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
																$offTotal = GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
																$offNoise = GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));


																// Korrecktes Logdate aus den Vier Werten im Buffer herausfinden
																$usedlogdate = 0;
																$logdate = pwtime2unixtime(substr($buf,24,8));
																if ($logdate > time())
																	{
				   												$usedlogdate = $logdate;
																	$verbrauch = pulsesToKwh(hexdec(substr($buf,32,8)), $offNoise, $offTotal, $gaina, $gainb);
																	}

																$logdate = pwtime2unixtime(substr($buf,40,8));

																if ($logdate > time())
																	{
				   												$usedlogdate = $logdate;
																	$verbrauch = pulsesToKwh(hexdec(substr($buf,48,8)), $offNoise, $offTotal, $gaina, $gainb);
																	}

																$logdate = pwtime2unixtime(substr($buf,56,8));

																if ($logdate > time())
																	{
				   												$usedlogdate = $logdate;
																	$verbrauch = pulsesToKwh(hexdec(substr($buf,64,8)), $offNoise, $offTotal, $gaina, $gainb);
																	}

																$logdate = pwtime2unixtime(substr($buf,72,8));

																if ($logdate > time())
																	{
				   												$usedlogdate = $logdate;
																	$verbrauch = pulsesToKwh(hexdec(substr($buf,80,8)), $offNoise, $offTotal, $gaina, $gainb);
																	}

																// echo "Letztes Logdate: ".date("c",$logdate)."\n";

																if ($usedlogdate == 0)
																	{
																	$id_log = IPS_GetVariable(IPS_GetVariableIDByName ("LogAddress", $myCat));
																	if ($id_log["VariableChanged"] > (time()-10*60))
																		{
																		$id_info = IPS_GetObject($myCat);
																		$LogAddress = GetValue(IPS_GetVariableIDByName ("LogAddress", $myCat));
																		print "PW0048 - ".IPS_GetName($myCat)." - ";
					   												print "Buffer mit akt. LogAddress ".$LogAddress." enthält keine aktuellen Werte für die Zeit ".date("c",time()).", es wird versucht den Buffer mit LogAdress ".($LogAddress-1)." zu lesen";
																		$LogAddress = 278528 + (32 * ($LogAddress-1));
																		$LogAddress = str_pad(strtoupper(dechex($LogAddress)), 8 ,'0', STR_PAD_LEFT);
																		PW_SendCommand("0048".$id_info['ObjectIdent'].$LogAddress);
						
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

																	PRINT "PW0048 - ".IPS_GetName($myCat).":\n";
																	print "Logdate: ".date("c",$usedlogdate)."\n";
				   												print "Verbrauch/Stunde: ".$verbrauch."\n";
				   												print "Alter Gesamtverbrauch: ".$oldVerbrauch."\n";
				   												print "Neuer Gesamtverbrauch: ".($verbrauch + $oldVerbrauch)."\n";
				   												SetValueFloat ($varGesamtverbrauch,$verbrauch + $oldVerbrauch);
																	};


																

																$text =  "\nPW0049 Buffer - ".IPS_GetName($myCat) . "[".$buf."]";
																$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,24,8))).": ";
																$text = $text . pulsesToKwh(hexdec(substr($buf,32,8)), $offNoise, $offTotal, $gaina, $gainb);
																$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,40,8))).": ";
																$text = $text . pulsesToKwh(hexdec(substr($buf,48,8)), $offNoise, $offTotal, $gaina, $gainb);
																$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,56,8))).": ";
																$text = $text . pulsesToKwh(hexdec(substr($buf,64,8)), $offNoise, $offTotal, $gaina, $gainb);
																$text = $text . date("\nd.m.Y H:i:s ", pwtime2unixtime(substr($buf,72,8))).": ";
																$text = $text . pulsesToKwh(hexdec(substr($buf,80,8)), $offNoise, $offTotal, $gaina, $gainb);
																logging($text,'plugwisebuffer.log' );
																
																

																break;

											case "003F":   //*************************************************************************
																// Antwort auf 003E - Uhrzeit auslesen
																//*************************************************************************
			   												$mcID = substr($buf,8,16);
																$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
																
																$myTime = mktime(hexdec(substr($buf,24,2)) + (date("Z")/3600), hexdec(substr($buf,26,2)), hexdec(substr($buf,28,2)), 0,0,0);
      														$std = date("H", $myTime);
																$min = date("i", $myTime);
																$sek = date("s", $myTime);
																$m1  = ($std*60*60) + ($min*60) + $sek;
																$std = date("H", time());
																$min = date("i", time());
																$sek = date("s", time());
																$m2  = ($std*60*60) + ($min*60) + $sek;

 																$abweichung = $m1 - $m2;
																$text = " R - ".IPS_GetName($myCat).": ".date("H:i:s", $myTime)." Abweichung: " . $abweichung ." Sekunden. " .unixtime2pwtime();
																
																if ( $abweichung > 120 ) // Abweichung groesser 120 Sekunden
																   {
																   $text = $text . " Uhrzeit wird gestellt.";
																   PW_SendCommand("0016".IPS_GetName($myCat).unixtime2pwtime());
																   }
																
																logging($text,'plugwisetime.log' );
																break;
										 }

									Default:
	   										//PRINT "PW: ".$_IPS['SENDER'];
	   										
	   										break;

	break;
	}



   berechne_gruppenverbrauch();

	hole_gesamtverbrauch();
	

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
		
		$id = IPS_GetObjectIDByIdent($mac,$idCatCircles);
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


?>