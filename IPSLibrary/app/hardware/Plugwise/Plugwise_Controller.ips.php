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

	//$idCatCircles = CreateCategory("Circles",IPS_GetParent($IPS_SELF),0);
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);



   
Switch ($_IPS['SENDER'])
	{
	Default:
	   // PRINT "PW: ".$_IPS['SENDER'];
	   break;

	Case "RunScript":
	Case "Execute":
	Case "TimerEvent":

		// Alle Circles durchlaufen, Status und Verbrauch lesen
		foreach(IPS_GetChildrenIDs($idCatCircles) as $item){   // alle Unterobjekte durchlaufen
		    $id_info = IPS_GetObject($item);
		    PW_SendCommand("0012".$id_info['ObjectIdent']);
		    PW_SendCommand("0023".$id_info['ObjectIdent']);
		}

		break;

	Case "Variable":
		// PRINT "PW Variable: ".$_IPS['VARIABLE'].", Value: ".$_IPS['VALUE'];
		// break;
	Case "WebFront": 	   // Zum schalten im Webfront
		// PRINT "PW Schalten: ".$_IPS['VARIABLE'].", Value: ".$_IPS['VALUE'];
	   $id = IPS_GetParent($_IPS['VARIABLE']);
		$id_info = IPS_GetObject($id);
		if ($_IPS['VALUE'] == 1) {$action = 1;} else {$action = 0;}
		$cmd = "0017".$id_info['ObjectIdent']."0".$action;
	   PW_SendCommand($cmd);

		// Sofort Feedback geben - aber besser auskommentieren und auf Rückmeldung des Circles warten
	   // SetValueBoolean($_IPS['VARIABLE'], $_IPS['VALUE']);

		break;

	Case "RegisterVariable": //
		$buf = $IPS_VALUE;
		switch ((substr($buf,0,4)))
			{
			case "0000":         //Befehl vom Stick empfangen
				switch ((substr($buf,8,4)))
				{
					case "00C1":  //Schauen ob alles empfangen wurde
		 				// print "Befehl von Stick empfangen";
						break;
						
					case "00D8":  //eingeschaltet
		 				// print "Eingeschaltet MAC".substr($buf,12,16);
						$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
						SetValue(IPS_GetVariableIDByName ("Status", $myCat),True);

						// Sofort Verbrauch abfragen
						// PW_SendCommand(substr($buf,12,16));

						break;
						
					case "00DE":  //ausgeschaltet
						// print "Ausgeschaltet MAC".substr($buf,12,16);
						$myCat = IPS_GetObjectIDByIdent(substr($buf,12,16), $idCatCircles);
						SetValue(IPS_GetVariableIDByName ("Status", $myCat),False);

						// Sofort Verbrauch abfragen
						// PW_SendCommand(substr($buf,12,16));

						break;
						
					case "00E1":
						print "Achtung: ein Circle ist nicht erreichbar: ".$buf;
						break;
						
					case "00D7": // Antwort auf 0016 - Uhrzeit stellen
					   $mac = substr($buf,12,16);
						$myCat = IPS_GetObjectIDByIdent($mac, $idCatCircles);
					   print "Uhrzeit gestellt auf ".IPS_GetName($myCat).": ".$buf;
					   // print "Uhrzeit gestellt auf  ".IPS_GetName($myCat);
						break;

					case "00DD":  // Antwort auf 0008 - Anfrage nach Circle+
					   $macplus = substr($buf,12,16);
					   
						// Dummy Instanz für Circle+ anlegen
						$myCat = @IPS_GetObjectIDByIdent($macplus, $idCatCircles);
						if ($myCat == false) createCircle($macplus, $idCatCircles);
					   
					   PRINT "PW MC+:".$macplus.", Now searching for Circles...";
						for ($i = 0; $i < 40; $i++) {
						   PW_SendCommand("0018".$macplus.str_pad($i, 2 ,'0', STR_PAD_LEFT));
						}
						break;

					default:
						print "Fehler von Stick: ".$buf;  //bei allem anderen
						break;
				}
				break;

			case "0011":  // Init
				// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
				// print "MC Adresse: ".substr($buf,8,16);
				// MC Adresse: 000D6F0000C3CA8E
				// Stick: C3CE5C

				// Kalibrierungsdaten aller Circles abrufen
				foreach(IPS_GetChildrenIDs($idCatCircles) as $item){   // alle Unterobjekte
				    $id_info = IPS_GetObject($item);
				    PW_SendCommand("0026".$id_info['ObjectIdent']);
				}

				break;

			case "0013":   // Aktueller Verbrauch
			   $mcID = substr($buf,8,16);
				$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
				$pulse = substr($buf,28,4);

				If ($pulse == "FFFF") {
				   // Circle ausgeschaltet, meldet FFFF
					SetValue(CreateVariable("Leistung", 2, $myCat, 0, "~Watt.3680", 0), 0);
				} else {

					// print IPS_GetName ($myCat)."\r\n";
					// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
					// print "MC Adresse: ".substr($buf,8,16)."\r\n";
					// print "Pulse/s : ".substr($buf,24,4)."\r\n";
					// print "Pulse/8s: ".substr($buf,28,4)."\r\n";
					// print "Pulse gesamt: ".substr($buf,32,8)."\r\n";

					$gainA	= GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
					$gainB	= GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
					$offTotal	= GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
					$offNoise	= GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));

					// Aktueller Verbrauch in Watt ermitteln
					$value 	= hexdec($pulse)/8;
					$out 		= (pow(($value+$offNoise),2)*$gainB)+(($value+$offNoise)*$gainA)+$offTotal;
					$Leistung 	= (($out ) / 468.9385193)*1000;
					$Leistung 	= round($Leistung,0);

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

				// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
				// print "MC Circle+: ".substr($buf,8,16)."\r\n";
			   $mac = substr($buf,24,16);
			   if (!($mac == "FFFFFFFFFFFFFFFF")) {
					$myCat = @IPS_GetObjectIDByIdent($mac, $idCatCircles);
					if ($myCat == false){
					   createCircle($mac, $idCatCircles);
					} else {
						// print "PW Node ".substr($buf,40,2).": ".IPS_GetName($myCat).", MAC:".$mac;
					}
				};
				break;
				
			case "0024":   //Status
			   $mcID = substr($buf,8,16);
				$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
				// echo IPS_GetName ($myCat).": ".substr($buf,41,1);
				SetValue(CreateVariable("Status", 0, $myCat, 0, "~Switch", $IPS_SELF), substr($buf,41,1));

				SetValueInteger(CreateVariable("LogAddress", 1, $myCat,0,"",0,0),intval((hexdec(substr($buf,32,8)) - 278528) / 32));
				
				// PRINT "LogAddress ".IPS_GetName($myCat).": ".intval((hexdec(substr($buf,32,8)) - 278528) / 32);
				// PRINT "LogAddress ".IPS_GetName($myCat).": Hex ".substr($buf,32,8).", Dez ".((hexdec(substr($buf,32,8)) - 278528) / 32);

				// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
				// print "MC Adresse: ".substr($buf,8,16)."\r\n";
				// print "Jahr : ".substr($buf,24,2)."\r\n";
				// print "Monat: ".substr($buf,26,2)."\r\n";
				// print "Minuten : ".substr($buf,28,4)."\r\n";
				// PRINT "Zeit ".IPS_GetName($myCat).": ".date('c',pwtime2unixtime(substr($buf,24,8)));
				// PRINT "Zeit ".IPS_GetName($myCat)." - Hex: ".substr($buf,24,8);

				// print "log Adresse: ".substr($buf,32,8)."\r\n";
				// print "An/aus: ".substr($buf,40,2)."\r\n";
				// print "Herz (85=50H): ".substr($buf,42,2)."\r\n";
				// print "Hardwareversion: ".substr($buf,44,12)."\r\n";
				// print "Softwareversion: ".substr($buf,56,8)."\r\n";
				// print "Stick 00,Cir+ 01 Cir 02: ".substr($buf,64,2)."\r\n";

				break;

			case "0027":   //Kalibrierung
				$myCat = IPS_GetObjectIDByIdent(substr($buf,8,16), $idCatCircles);

				SetValueFloat(CreateVariable("gaina",2,$myCat,0,""),bintofloat(substr($buf,24,8)));
				SetValueFloat(CreateVariable("gainb",2,$myCat,0,""),bintofloat(substr($buf,32,8)));
				SetValueFloat(CreateVariable("offTotal",2,$myCat,0,""),bintofloat(substr($buf,40,8)));
				if (substr($buf,48,8)=="00000000")
					SetValueFloat(CreateVariable("offNoise",2,$myCat,0,""),0);
				else
					SetValueFloat(CreateVariable("offNoise",2,$myCat,0,""),bintofloat(substr($buf,48,8)));
				break;

			case "0049": // Antwort auf 0048 - historischen Verbrauch lesen (Buffer)
			   // PRINT "Buffer: ".$buf."\n\n";
				// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
				// print "MC Adresse: ".substr($buf,8,16)."\r\n";

			   $mcID = substr($buf,8,16);
				$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);

				// Kalibrierungsdaten laden
				$gaina	= GetValueFloat(IPS_GetVariableIDByName("gaina", $myCat));
				$gainb	= GetValueFloat(IPS_GetVariableIDByName("gainb", $myCat));
				$offTotal	= GetValueFloat(IPS_GetVariableIDByName("offTotal", $myCat));
				$offNoise	= GetValueFloat(IPS_GetVariableIDByName("offNoise", $myCat));


				// Korrecktes Logdate aus den Vier Werten im Buffer herausfinden
				$usedlogdate = 0;
				$logdate = pwtime2unixtime(substr($buf,24,8));
				if ($logdate > time()) {
				   $usedlogdate = $logdate;
					$verbrauch = pulsesToKwh(hexdec(substr($buf,32,8)), $offNoise, $offTotal, $gaina, $gainb);
					}

				$logdate = pwtime2unixtime(substr($buf,40,8));
				if ($logdate > time()) {
				   $usedlogdate = $logdate;
					$verbrauch = pulsesToKwh(hexdec(substr($buf,48,8)), $offNoise, $offTotal, $gaina, $gainb);
					}

				$logdate = pwtime2unixtime(substr($buf,56,8));
				if ($logdate > time()) {
				   $usedlogdate = $logdate;
					$verbrauch = pulsesToKwh(hexdec(substr($buf,64,8)), $offNoise, $offTotal, $gaina, $gainb);
					}

				$logdate = pwtime2unixtime(substr($buf,72,8));
				if ($logdate > time()) {
				   $usedlogdate = $logdate;
					$verbrauch = pulsesToKwh(hexdec(substr($buf,80,8)), $offNoise, $offTotal, $gaina, $gainb);
					}

				// echo "Letztes Logdate: ".date("c",$logdate)."\n";

				if ($usedlogdate == 0) {
					$id_log = IPS_GetVariable(IPS_GetVariableIDByName ("LogAddress", $myCat));
					if ($id_log["VariableChanged"] > (time()-10*60)){
						$id_info = IPS_GetObject($myCat);
						$LogAddress = GetValue(IPS_GetVariableIDByName ("LogAddress", $myCat));
						print "PW0048 - ".IPS_GetName($myCat)." - ";
					   print "Buffer mit akt. LogAddress ".$LogAddress." enthält keine aktuellen Werte für die Zeit ".date("c",time()).", es wird versucht den Buffer mit LogAdress ".($LogAddress-1)." zu lesen";
						$LogAddress = 278528 + (32 * ($LogAddress-1));
						$LogAddress = str_pad(strtoupper(dechex($LogAddress)), 8 ,'0', STR_PAD_LEFT);
						PW_SendCommand("0048".$id_info['ObjectIdent'].$LogAddress);
						
					} else {
						$LogAddress = GetValue(IPS_GetVariableIDByName ("LogAddress", $myCat));
						print "PW0048 - ".IPS_GetName($myCat)." - ";
					   print "Buffer mit akt. LogAddress ".$LogAddress." enthält keine aktuellen Werte für die Zeit ".date("c",time()).", Timing-Problem?";
					};
				} else {
				
					$varGesamtverbrauch = IPS_GetVariableIDByName("Gesamtverbrauch",$myCat);
				   $oldVerbrauch = GetValueFloat($varGesamtverbrauch);

					PRINT "PW0048 - ".IPS_GetName($myCat).":\n";
					print "Logdate: ".date("c",$usedlogdate)."\n";
				   print "Verbrauch/Stunde: ".$verbrauch."\n";
				   print "Alter Gesamtverbrauch: ".$oldVerbrauch."\n";
				   print "Neuer Gesamtverbrauch: ".($verbrauch + $oldVerbrauch)."\n";
				   SetValueFloat ($varGesamtverbrauch,$verbrauch + $oldVerbrauch);
				};


/*

				PRINT "PW0048 Buffer - ".IPS_GetName($myCat).":\r\n";
				$log[0]["Zeit"]=date("d.m.Y H:i", pwtime2unixtime(substr($buf,24,8)));
				$log[0]["Verbrauch"]=pulsesToKwh(hexdec(substr($buf,32,8)), $offNoise, $offTotal, $gaina, $gainb);
				$log[1]["Zeit"]=date("d.m.Y H:i", pwtime2unixtime(substr($buf,40,8)));
				$log[1]["Verbrauch"]=pulsesToKwh(hexdec(substr($buf,48,8)), $offNoise, $offTotal, $gaina, $gainb);
				$log[2]["Zeit"]=date("d.m.Y H:i", pwtime2unixtime(substr($buf,56,8)));
				$log[2]["Verbrauch"]=pulsesToKwh(hexdec(substr($buf,64,8)), $offNoise, $offTotal, $gaina, $gainb);
				$log[3]["Zeit"]=date("d.m.Y H:i", pwtime2unixtime(substr($buf,72,8)));
				$log[3]["Verbrauch"]=pulsesToKwh(hexdec(substr($buf,80,8)), $offNoise, $offTotal, $gaina, $gainb);
				print_r($log);
*/

				break;

			case "003F": // Antwort auf 003E - Uhrzeit auslesen
			   $mcID = substr($buf,8,16);
				$myCat = IPS_GetObjectIDByIdent($mcID, $idCatCircles);
				//date_default_timezone_set('UTC');
				$myTime = mktime(hexdec(substr($buf,24,2)) + (date("Z")/3600), hexdec(substr($buf,26,2)), hexdec(substr($buf,28,2)), 0,0,0);
				//date_default_timezone_set('Europe/Berlin');
				print "PW Uhrzeit (lokal) - ".IPS_GetName($myCat).": ".date("H:i", $myTime)."\r\n";
				// print $buf."\r\n";
				// print "Die Sequenznr: ".substr($buf,4,4)."\r\n";
				// print "MC Adresse: ".substr($buf,8,16)."\r\n";
				// print "Stunde : ".hexdec(substr($buf,24,2))."\r\n";
				// print "Min: ".hexdec(substr($buf,26,2))."\r\n";
				// print "Sek : ".hexdec(substr($buf,28,2))."\r\n";
				// print "Tag der Woche: ".substr($buf,30,2)."\r\n";
				// print "Rest : ".substr($buf,32,2)."\r\n";
				// print "Rest2: ".substr($buf,34,10)."\r\n";
				break;
			}
	break;
	}

function createCircle($mac, $parentID){
	GLOBAL $IPS_SELF;
	
	print "PW Create Circle: ".$mac;
	$item = CreateInstance($mac, $parentID, "{485D0419-BE97-4548-AA9C-C083EB82E61E}", $Position=0);
	$id_info = IPS_GetObject($item);
	IPS_SetIdent ($item, $mac);
	
	
	// CreateVariable ($Name, $Type, $Parent, $Position, $Profile, $Action=0, $ValueDefault='', $Icon="")

	CreateVariable("Status", 0, $item, 0, "~Switch", $IPS_SELF, false);
	CreateVariable("Leistung", 2, $item, 0, "~Watt.3680", 0, 0);
	CreateVariable("Gesamtverbrauch", 2, $item, 0, "~Electricity", 0, 0); //~Electricity
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

	// Kalibrierungsdaten vom Circle abrufen
	PW_SendCommand("0026".$mac);

	// Zeit stellen
 	PW_SendCommand("0016".$mac.unixtime2pwtime());

	// Status abfragen
	PW_SendCommand("0012".$mac);
	PW_SendCommand("0023".$mac);

}

?>