<?php
	// bei der Konfiguration unbedingt auf die Groß/Kleinschreibung achten
	// es wurde versucht folgende Namensgebeung in der Konfiguration zu verwenden
	// Parameter mit kleinen Anfangsbuchstaben = Parameter welche von Highcharts übnernommen wurden. Siehe dazu: http://www.highcharts.com/ref/
	// Parameter mit großen Anfangsbuchstaben = für das IPS-Highcharts-Script eingeführte Parameter

	IPSUtils_Include ("IPSInstaller.inc.php",          "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",   "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
	IPSUtils_Include ("Plugwise_Include.ips.php",      "IPSLibrary::app::hardware::Plugwise");

	$AppPath        = "Program.IPSLibrary.app.hardware.Plugwise";
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
	$ContentPath    = "Visualization.WebFront.Hardware.Plugwise.GRAPH.Highcharts";
	$CircleVisuPath = "Visualization.WebFront.Hardware.Plugwise.MENU.Circles";
  	$CircleIdCData  = get_ObjectIDByPath($CircleVisuPath);

  	$CircleIdData   = get_ObjectIDByPath($CircleDataPath);
	$CategoryIdApp  = get_ObjectIDByPath($AppPath);
	$ContentId      = get_ObjectIDByPath($ContentPath);


	Global $CfgDaten; // damit kann der Script auch von anderen Scripten aufgerufen werden und bereits mit CfgDaten vorkonfiguriert werden

	// IPS Variablen ID´s
	$CfgDaten['ContentVarableId']= $ContentId;  // ID der String Variable in welche die Daten geschrieben werden (-1 oder überhaupt nicht angeben wenn die Content Variable das übergordnete Element ist)
	$CfgDaten['HighChartScriptId']= IPS_GetScriptIDByName('Highcharts_V2.01', $CategoryIdApp );  				// ID des Highcharts Scripts
                                    

	// Highcharts oder Highstock (default = Highcharts
	$CfgDaten['Ips']['ChartType'] = 'Highcharts';
	
	// Zeitraum welcher dargestellt werden soll (kann durch die Zeitvorgaben in den Serien verändert werden)
	$CfgDaten['StartTime'] = mktime(0,0,0, date("m", time()), date("d",time())-4, date("Y",time())); // ab heute 00:00 Uhr
	$CfgDaten['EndTime'] = mktime(23,59,59, date("m", time()), date("d",time()), date("Y",time())); // ab heute 23:59 Uhr, oder //$CfgDaten['EndTime'] = time();   // = bis jetzt

	// damit wird die Art des Aufrufes festgelegt
	$CfgDaten['RunMode'] = "script"; 	// file, script oder popup


	// Serienübergreifende Einstellung für das Laden von Werten
	$CfgDaten['AggregatedValues']['HourValues'] = 5;      // ist der Zeitraum größer als X Tage werden Stundenwerte geladen
	$CfgDaten['AggregatedValues']['DayValues'] = -1;       // ist der Zeitraum größer als X Tage werden Tageswerte geladen
	$CfgDaten['AggregatedValues']['WeekValues'] = -1;      // ist der Zeitraum größer als X Tage werden Wochenwerte geladen
	$CfgDaten['AggregatedValues']['MonthValues'] = -1;      // ist der Zeitraum größer als X Tage werden Monatswerte geladen
	$CfgDaten['AggregatedValues']['YearValues'] = -1;      	// ist der Zeitraum größer als X Tage werden Jahreswerte geladen
	$CfgDaten['AggregatedValues']['NoLoggedValues'] = 1000; 	// ist der Zeitraum größer als X Tage werden keine Boolean Werte mehr geladen, diese werden zuvor immer als Einzelwerte geladen	$CfgDaten['AggregatedValues']['MixedMode'] = false;     // alle Zeitraumbedingungen werden kombiniert
	$CfgDaten['AggregatedValues']['MixedMode'] = false;
	// Systematik funktioniert jetzt additiv. D.h. die angegebenen Werte gehen ab dem letzten Wert
	//
	//            -5 Tage           -3 Tage    					EndTime
	// |           |              	|            				 |
	// |           |DayValue = 2     |HourValues = 3          |
	// |Tageswerte |Stundenwerte     |jeder geloggte Wert     |

	// **************************************************************************************
	// *** Highcharts Options ***
	// **************************************************************************************
	// Ab hier werden die Bereiche des Highchart-Objektes parametriert.
	// Dieser Bereich wurde (soweit möglich) identisch der Originalstruktur gehalten.
	// Informationen über die Parametrierung findet man unter http://www.highcharts.com/ref/
	
	// **************************************************************************************
	// *** chart *** http://www.highcharts.com/ref/#chart
	// **************************************************************************************
	// $CfgDaten['chart']['zoomType'] = "'x'";			//default: $CfgDaten['chart']['zoomType'] = "'xy'";
   $CfgDaten['chart']['backgroundColor'] = "#003366";
	// **************************************************************************************
	// *** credits *** siehe http://www.highcharts.com/ref/#credits
	// **************************************************************************************
	// $CfgDaten['credits']['text'] = "used by IPS";
	// $CfgDaten['credits']['href'] = "http://www.ip-symcon.de/forum/f53/highcharts-multigraph-v1-0-a-17625/#post120721";

	// **************************************************************************************
	// *** title *** siehe http://www.highcharts.com/ref/#title
	// **************************************************************************************
	// $CfgDaten['title']['text'] = "Chart-Überschrift";  // Überchrift des gesamten Charts
	$CfgDaten['title']['text'] = "Leistung";
	
	// **************************************************************************************
	// *** subtitle *** siehe http://www.highcharts.com/ref/#subtitle
	// **************************************************************************************
	// $CfgDaten['subtitle']['text'] = "Zeitraum: %STARTTIME% - %ENDTIME%" // Sub-Überschrift. Wenn nichts angegeben wird wird dieser String als Default verwendet
	//		-> veraltet: 'SubTitle' -> verwende ['subtitle']['text']
	// $CfgDaten['subtitle']['Ips']['DateTimeFormat'] = "(D) d.m.Y H:i"	// z.B.: "(D) d.m.Y H:i" (wird auch als Default herangezogen wenn nichts konfiguriert wurde)
	//		-> veraltet: 'SubTitleDateTimeFormat' -> verwende ['subtitle']['Ips']['DateTimeFormat']
	//    -> entfallen: 'SubTitleFormat' -> unnötiger Paramter, wird jetzt in ['subtitle']['text'] angegeben

	$CfgDaten['subtitle']['text'] = "Zeitraum: %STARTTIME% - %ENDTIME%"; 	
	$CfgDaten['subtitle']['Ips']['DateTimeFormat'] = "(D) d.m.Y H:i"; 			

	// **************************************************************************************
	// *** tooltip *** http://www.highcharts.com/ref/#tooltip
	// **************************************************************************************
	// $CfgDaten['tooltip']['enabled'] = false;
	// $CfgDaten['tooltip']['formatter'] = Null; // IPS erstellt selbständig einen Tooltip
	// $CfgDaten['tooltip']['formatter'] = ""; // Standard - Highcharts Tooltip
	
	// **************************************************************************************
	// *** exporting *** http://www.highcharts.com/ref/#exporting
	// **************************************************************************************
	// $CfgDaten['exporting']['enabled'] = true;

	// **************************************************************************************
	// *** lang *** http://www.highcharts.com/ref/#lang
	// **************************************************************************************
	// $CfgDaten['lang']['resetZoom'] = "Zoom zurücksetzten";

	// **************************************************************************************
	// *** legend *** http://www.highcharts.com/ref/#legend
	// **************************************************************************************
	// $CfgDaten['legend']['backgroundColor'] = '#FCFFC5';

	// **************************************************************************************
	// *** xAxis *** http://www.highcharts.com/ref/#xAxis
	// **************************************************************************************
	// $CfgDaten['xAxis']['lineColor'] = '#FF0000';
	// $CfgDaten['xAxis']['plotBands'][] = array("color"=>'#FCFFC5',"from"=> "@Date.UTC(2012, 3, 29)@","to"=> "@Date.UTC(2012, 3, 30)@");

	// **************************************************************************************
	// *** yAxis *** http://www.highcharts.com/ref/#yAxis
	// **************************************************************************************
	// $CfgDaten['yAxis'][0]['title']['text'] = "Temperaturen"; // Bezeichnung der Achse
	//		-> veraltet: 'Name' und 'TitleText' -> verwende ['title']['text']
	// $CfgDaten['yAxis'][0]['Unit'] = "°C";	// Einheit für die Beschriftung die Skalenwerte
	//	$CfgDaten['yAxis'][0]['min'] = 0; // Achse beginnt bei Min (wenn nichts angegeben wird wird der Min der Achse automatisch eingestellt)
	//	$CfgDaten['yAxis'][0]['max'] = 40; // Achse geht bis Max (wenn nichts angegeben wird wird der Max der Achse automatisch eingestellt)
	//		-> veraltet: 'Min' und 'Max'
	//	$CfgDaten['yAxis'][0]['opposite'] = false; // Achse wird auf der rechten (true) oder linken Seite (false) des Charts angezeigt (default = false)
	//		-> veraltet: 'Opposite'
	//	$CfgDaten['yAxis'][0]['tickInterval'] = 5; // Skalenwerte alle x (TickInterval)
	//		-> veraltet: 'TickInterval'
	//    -> entfallen: 'PlotBands' -> verwende ['yAxis'][0]['plotBands'],  (siehe Beispiel 'cfg - drehgriff und tf-kontakt')
	//    -> entfallen: 'YAxisColor' -> verwende ['yAxis'][0]['title']['style']
	//    -> entfallen: 'TitleStyle'-> verwende ['yAxis'][0]['title']['style']

	//$CfgDaten['yAxis'][0]['title']['text'] = "Leistung";
	$CfgDaten['yAxis'][0]['Unit'] = "Watt";
	$CfgDaten['yAxis'][0]['opposite'] = false;
	//$CfgDaten['yAxis'][0]['tickInterval'] = 5;
	$CfgDaten['yAxis'][0]['min'] = 0;
	//$CfgDaten['yAxis'][0]['max'] = 0;


	// **************************************************************************************
	// *** series *** http://www.highcharts.com/ref/#series
	// **************************************************************************************
	// $serie['name'] = "Temperatur; // Name der Kurve (Anzeige in Legende und Tooltip)
	//		-> veraltet: 'Name' -> verwende [series']['name']
	// $serie['Unit'] = "°C"; // Anzeige in automatisch erzeugtem Tooltip
	// 	wenn $serie['Unit'] = NULL; // oder Unit wird gar nicht definiert, wird versucht die Einheit aus dem Variablenprofil automatisch auszulesen
	// $serie['ReplaceValues'] = false; // Werte werden wie geloggt übernommen
	// 	$serie['ReplaceValues'] = array(0=>0.2,1=>10) // der Wert 0 wird in 0.2 geändert, der Wert 1 wird in 10 geändert
	//   	das macht für die Darstellung von Boolean Werte Sinn, oder für Drehgriffkontakte (Werte 0,1,2)
	// $serie['type'] = 'spline'; // Festlegung des Kuventypes (area, areaspline, line, spline, pie, Column)
	// $serie['yAxis'] = 0; // Nummer welche Y-Achse verwendet werden soll (ab 0)
	// 	-> veraltet: 'Param' -> verwende die Highcharts Parameter - sollte eigentlich noch so funktionieren wie in IPS-Highcharts V1.x
	// $serie['AggType'] = 0 // Festlegung wie die Werte gelesen werden soll (0=Hour, 1=Day, 2=Week, 3=Month, 4=Year), hat Vorrang gegenüber den Einstellungen in AggregatedValues
	//    wird kein AggType definiert werden alle gelogten Werte angezeigt
	// $serie['AggNameFormat'] = "d.m.Y H:i"; // (gilt nur bei den Pies, wenn eine Id verwendet wird), entspricht dem PHP-date("xxx") Format, welches das Format der Pie Namen festlegt, wenn keine Eingabe werden Default Werte genommen
	// $serie['Offset'] = 24*60*60; hiermit können Kurven unterschiedlicher Zeiträume in einem Chart dargestellt. Angabe ist in Minuten
	//	$serie['StartTime'] = mktime(0,0,0,1,1,2012); 	// wird für die entsprechende Serie eine Anfangs- und/oder Endzeitpunkt festgelegt wird dieser verwendet. Ansonsten wird
	// $serie['EndTime'] = mktime(0,0,0,2,1,2012);  		// der Zeitpunkt der Zeitpunkt aus den $CfgDaten genommen
	// $serie['ScaleFactor'] = 10; // Skalierungsfaktor mit welchem der ausgelesene Werte multipliziert wird
	// $serie['RoundValue'] = 1; // Anzahl der Nachkommastellen
	//	$serie['AggValue'] ='Min' // über AggValue kann Min/Max oder Avg vorgewählt werden (Default bei keiner Angabe ist Avg)
	//		ist sinnvoll wenn nicht Einzelwerte sondern Stundenwerte, Tageswerte, usw. ausgelesen werden
	// $serie['data'] = array('TimeStamp'=> time(),'Value'=12) // hier kann ein Array an eigenen Datenpunkten übergeben werden. In diesem Fall werden für diese Serie keine Daten aus der Variable gelesenen.

	$id = 0;
	$childs = IPS_GetChildrenIDs($CircleIdCData);
	foreach ( $childs as $child )
	   {
	   $object = IPS_GetObject($child);
	   
	   if ( GetValueInteger($child) == 1 )
	      {
	      $info = $object['ObjectInfo'];
	      $parent = IPS_GetObjectIDByName($info,$CircleIdData);
			$id = IPS_GetObjectIDByName('Leistung',$parent);
	      
	      break;
	      }
	   }
	if ( $id == 0 )
	   return;
	
	$serie = array();
	$serie['name'] = "Leistung " . $info;
	$serie['Id'] = $id;
	$serie['color'] = "#CC9966";
	$serie['Unit'] = "Watt";
	$serie['ReplaceValues'] = false;
	$serie['type'] = "area";
	$serie['yAxis'] = 0;
	$serie['marker']['enabled'] = false;
	$serie['AggType'] = 0;
	$serie['shadow'] = true;
	$serie['lineWidth'] = 2;
	$serie['RoundValue'] = 1;
	$serie['states']['hover']['lineWidth'] = 2;
	$serie['marker']['states']['hover']['enabled'] = true;
	$serie['marker']['states']['hover']['symbol'] = 'circle';
	$serie['marker']['states']['hover']['radius'] = 4;
	$serie['marker']['states']['hover']['lineWidth'] = 1;
	$serie['showInLegend'] =  false;
	$CfgDaten['series'][] = $serie;



	// Highcharts-Theme
	//	$CfgDaten['HighChart']['Theme']="grid.js";   // von Highcharts mitgeliefert: dark-green.js, dark-blue.js, gray.js, grid.js
	//$CfgDaten['HighChart']['Theme']="ips.js";   // IPS-Theme muss per Hand in in Themes kopiert werden....

	// Abmessungen des erzeugten Charts
	$CfgDaten['HighChart']['Width'] = 0; 			// in px,  0 = 100%
	$CfgDaten['HighChart']['Height'] = 300; 		// in px

	// -------------------------------------------------------------------------------------------------------------------------------------
	// und jetzt los ......
	$s = IPS_GetScript($CfgDaten['HighChartScriptId']); 	// Id des Highcharts-Scripts
	include($s['ScriptFile']);

  	// hier werden die CfgDaten geprüft und bei Bedarf vervollständigt
	$CfgDaten = CheckCfgDaten($CfgDaten);
	
	// abhängig von der Art des Aufrufs -> json String für Highcharts erzeugen
	if (isset($CfgDaten['RunMode'])
		&& ($CfgDaten['RunMode'] == "script" || $CfgDaten['RunMode'] == "popup"))
	{
		// Variante1: Übergabe der ScriptId. Daten werden beim Aufruf der PHP Seite erzeugt und direkt übergeben. Dadurch kann eine autom. Aktualisierung der Anzeige erfolgen
		if ($IPS_SENDER != "WebInterface")
		{
			WriteContentWithScriptId ($CfgDaten, $IPS_SELF);     		// und jetzt noch die ContentTextbox
			return;                                               	// Ende, weil durch die Zuweisung des Script sowieso nochmals aufgerufen wird
		}

		$sConfig = CreateConfigString($CfgDaten);             		// erzeugen und zurückgeben des Config Strings
	}
	else
	{
		//Variante2: Übergabe des Textfiles. Daten werden in tmp-File gespeichert. Eine automatische Aktualisierung beim Anzeigen der Content-Textbox erfolgt nicht
		$sConfig = CreateConfigString($CfgDaten);             		// erzeugen und zurückgeben des Config Strings
		
		$tmpFilename = CreateConfigFile($sConfig, $IPS_SELF);     	// und ab damit ins tmp-Files
		if ($IPS_SENDER != "WebInterface")
		{
			WriteContentWithFilename ($CfgDaten, $tmpFilename);   	// und jetzt noch die ContentTextbox
		}
	}



?>
