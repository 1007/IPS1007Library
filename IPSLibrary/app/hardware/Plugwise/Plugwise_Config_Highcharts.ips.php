<?php

	IPSUtils_Include ("IPSInstaller.inc.php",          "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",   "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
	IPSUtils_Include ("Plugwise_Include.ips.php",      "IPSLibrary::app::hardware::Plugwise");

	$AppPath        = "Program.IPSLibrary.app.hardware.Plugwise";
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
	$OthersDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Others";

	$ContentPath    = "Visualization.WebFront.Hardware.Plugwise.GRAPH.Uebersicht";
	$CircleVisuPath = "Visualization.WebFront.Hardware.Plugwise.MENU.Stromzähler";
  	$CircleIdCData  = get_ObjectIDByPath($CircleVisuPath);

	$GruppenVisuPath= "Visualization.WebFront.Hardware.Plugwise.MENU.Gruppen";
  	$GroupsIdData   = get_ObjectIDByPath($GruppenVisuPath);
  	$GroupsIdOData  = get_ObjectIDByPath($OthersDataPath);


  	$CircleIdData   = get_ObjectIDByPath($CircleDataPath);
	$CategoryIdApp  = get_ObjectIDByPath($AppPath);
	$ContentId      = get_ObjectIDByPath($ContentPath);

	// damit kann der Script auch von anderen Scripten aufgerufen werden
	// und bereits mit CfgDaten vorkonfiguriert werden
	Global $CfgDaten; 

	$id = 0;
	$maxleistung = 0;
	//***************************************************************************
	// Welcher Circle soll dargestellt werden
	//***************************************************************************

	$childs = IPS_GetChildrenIDs($CircleIdCData);
	foreach ( $childs as $child )
	   {
	   $object = IPS_GetObject($child);

	   if ( GetValueInteger($child) == 1 )
	      {
	      $info = $object['ObjectInfo'];
	      $objectname = $object['ObjectName'];
	      $parent = IPS_GetObjectIDByIdent($info,$CircleIdData);
			$id = IPS_GetObjectIDByName('Leistung',$parent);
			foreach ($CircleGroups as $circle )
	   		{	// Maxwert fuer Circle aus Config
	    		if ( $info == $circle[0] )
					{
					$maxleistung = $circle[4];
	      		break;
	      		}
				}

	      break;
	      }
	   }
	   
	   

	   
	if ( $id == 0 )   // Kein Circle dann nach Gruppe suchen
		{
		$childs = IPS_GetChildrenIDs($GroupsIdData);
		foreach ( $childs as $child )
	   	{
	   	$object = IPS_GetObject($child);
			
	   	if ( GetValueInteger($child) == 1 )
	      	{
	      	$ident = $object['ObjectIdent'];
	      	
	      	$objectname = $object['ObjectIdent'];
	      	$parent1 = IPS_GetObjectIDByIdent($ident,$GroupsIdOData);
	      	
				$id = IPS_GetObjectIDByIdent('Leistung',$parent1);
				$maxleistung = 0;
				
				}

	      
	      }
	   }

	
	if ( $id == 0 )   // Kein Circle keine Gruppe dann Gesamt
		{

		$idgesamt = IPS_GetObjectIDByIdent('Gesamt',$GroupsIdOData);
		$id = IPS_GetObjectIDByName('Leistung',$idgesamt);
		$objectname = "Gesamt";
		}

   // Id des ArchiveHandler auslesen
	$instances = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
	$cfg['ArchiveHandlerId'] = $instances[0];
		
	$wird_geloggt = AC_GetLoggingStatus($cfg['ArchiveHandlerId'],$id);
	if ( !$wird_geloggt )
	   return;
	
	// ID der String Variable in welche die Daten geschrieben werdern
	$CfgDaten['ContentVarableId']= $ContentId;
   // ID des Highcharts Scripts
	$CfgDaten['HighChartScriptId']= IPS_GetScriptIDByName('Highcharts', $CategoryIdApp );  				
                                    
	// Highcharts oder Highstock (default = Highcharts
	$CfgDaten['Ips']['ChartType'] = 'Highcharts';
	
	// Zeitraum welcher dargestellt werden soll
	// (kann durch die Zeitvorgaben in den Serien verändert werden)
	if ( defined('HIGHCHARTS_ZEITRAUM') )
		$zeitrum_stunden = HIGHCHARTS_ZEITRAUM;
	else
		$zeitrum_stunden = 24;
	   
	
	$CfgDaten['StartTime'] = time() - (60*60*$zeitrum_stunden );   // letzten 2 Tage
   $CfgDaten['EndTime']   = time();

	// damit wird die Art des Aufrufes festgelegt
	$CfgDaten['RunMode'] = "script"; 	// file, script oder popup
	//$CfgDaten['RunMode'] = "file"; 	// file, script oder popup

	// **************************************************************************************
	// *** Highcharts Options ***
	// **************************************************************************************
	// Ab hier werden die Bereiche des Highchart-Objektes parametriert.
	// Dieser Bereich wurde (soweit möglich) identisch der Originalstruktur gehalten.
	// Informationen über die Parametrierung findet man unter http://www.highcharts.com/ref/
	
   $CfgDaten['chart']['backgroundColor'] = "#003366";
	$CfgDaten['title']['text'] = "Leistung " .$objectname ;
	$CfgDaten['title']['style']['color'] = "#FFFFFF";

	$CfgDaten['subtitle']['text'] = "Zeitraum: %STARTTIME% - %ENDTIME%"; 	
	$CfgDaten['subtitle']['Ips']['DateTimeFormat'] = "(D) d.m.Y H:i"; 			
   

	$CfgDaten['yAxis'][0]['title']['text'] = "Watt";
	$CfgDaten['yAxis'][0]['Unit'] = "Watt";
	$CfgDaten['yAxis'][0]['opposite'] = false;
	$CfgDaten['yAxis'][0]['min'] = 0;

	if ( $maxleistung > 0 )
	   {
    	$pb['from'] = $maxleistung;
    	$pb['to'] = 1000000;
    	$pb['color'] = 'rgba(255, 0, 0, 0.2)';
    	$CfgDaten["yAxis"][0]['plotBands'][] = $pb;
		}

	// **************************************************************************************
	// *** series *** 
	// **************************************************************************************
	$serie = array();
	$serie['name'] = "Leistung ";
	$serie['Id'] = $id;
	//$serie['color'] = "#CC9933";
	$serie['Unit'] = "Watt";
	$serie['ReplaceValues'] = true;
	
	$serie['step'] = true;
	$serie['type'] = "areaspline";
	$serie['type'] = "area";
	$serie['yAxis'] = 0;
	$serie['marker']['enabled'] = false;
	$serie['AggType'] = 0;
	$serie['shadow'] = false;
	$serie['lineWidth'] = 0;
	$serie['RoundValue'] = 1;
	$serie['states']['hover']['lineWidth'] = 1;
	$serie['marker']['states']['hover']['enabled'] = true;
	$serie['marker']['states']['hover']['symbol'] = 'circle';
	$serie['marker']['states']['hover']['radius'] = 1;
	$serie['marker']['states']['hover']['lineWidth'] = 1;
	$serie['showInLegend'] =  false;
	
	$CfgDaten['plotOptions']['areaspline']['fillColor']['linearGradient'] = array(0, 0, 0, 300); // Winkel,,,,
	$CfgDaten['plotOptions']['areaspline']['fillColor']['stops'] = array(array(0,'rgba(255,0,0,0.5)'),array(1,'rgba(0,255,0,1)'));

	$CfgDaten['plotOptions']['area']['fillColor']['linearGradient'] = array(0, 0, 0, 300); // Winkel,,,,
	$CfgDaten['plotOptions']['area']['fillColor']['stops'] = array(array(0,'rgba(255,0,0,0.5)'),array(1,'rgba(0,255,0,1)'));

	$CfgDaten['series'][] = $serie;

	// Abmessungen des erzeugten Charts
	$CfgDaten['HighChart']['Width'] = 0; 			// in px,  0 = 100%
	$CfgDaten['HighChart']['Height'] = 320; 		// in px

	//***************************************************************************
	// und jetzt los ......
	//***************************************************************************
	//$s = IPS_GetScript($CfgDaten['HighChartScriptId']);
	//include($s['ScriptFile']);

	IPSUtils_Include ("Highcharts.ips.php",      "IPSLibrary::app::hardware::Plugwise");


  	// hier werden die CfgDaten geprüft und bei Bedarf vervollständigt
	$CfgDaten = CheckCfgDaten($CfgDaten);


	// abhängig von der Art des Aufrufs -> json String für Highcharts erzeugen
	if (isset($CfgDaten['RunMode'])
		&& ($CfgDaten['RunMode'] == "script" || $CfgDaten['RunMode'] == "popup"))
	{
		// Variante1: Übergabe der ScriptId.
		// Daten werden beim Aufruf der PHP Seite erzeugt und direkt übergeben.
		// Dadurch kann eine autom. Aktualisierung der Anzeige erfolgen
		if ($IPS_SENDER != "WebInterface")
		{
			WriteContentWithScriptId ($CfgDaten, $IPS_SELF);     		// und jetzt noch die ContentTextbox
			return;                                               	// Ende, weil durch die Zuweisung des Script sowieso nochmals aufgerufen wird
		}

		$sConfig = CreateConfigString($CfgDaten);             		// erzeugen und zurückgeben des Config Strings
		
	}
	else
	{
		// Variante2: Übergabe des Textfiles.
		// Daten werden in tmp-File gespeichert.
		// Eine automatische Aktualisierung beim Anzeigen der Content-Textbox erfolgt nicht
		$sConfig = CreateConfigString($CfgDaten);             		// erzeugen und zurückgeben des Config Strings
		
		$tmpFilename = CreateConfigFile($sConfig, $IPS_SELF);     	// und ab damit ins tmp-Files
		if ($IPS_SENDER != "WebInterface")
		{
			WriteContentWithFilename ($CfgDaten, $tmpFilename);   	// und jetzt noch die ContentTextbox
		}
	}



?>
