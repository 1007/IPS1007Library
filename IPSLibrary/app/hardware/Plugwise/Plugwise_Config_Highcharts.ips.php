<?php

	IPSUtils_Include ("Plugwise_Include.ips.php",      "IPSLibrary::app::hardware::Plugwise");

	$AppPath        = "Program.IPSLibrary.app.hardware.Plugwise";
	$CategoryIdApp  = get_ObjectIDByPath($AppPath);

	$ContentPath    = "Visualization.WebFront.Hardware.Plugwise.GRAPH.Uebersicht";
	$ContentId      = get_ObjectIDByPath($ContentPath);

	$HighchartsPath    = "Visualization.WebFront.Hardware.Plugwise.Highcharts";
	$HighchartsId      = get_ObjectIDByPath($HighchartsPath);

	
	
	// damit kann der Script auch von anderen Scripten aufgerufen werden
	// und bereits mit CfgDaten vorkonfiguriert werden
	Global $CfgDaten; 

	$result = find_id_toshow();
	$id 			 = $result['IDLEISTUNG'];
	$maxleistung = $result['MAXLEISTUNG'];
	$objectname  = $result['OBJECTNAME'];
	$info        = $result['INFO'];

	
   // Id des ArchiveHandler auslesen
	$instances = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}');
	$cfg['ArchiveHandlerId'] = $instances[0];
	
	$wird_geloggt = AC_GetLoggingStatus(intval($cfg['ArchiveHandlerId']),intval($id));
	if ( !$wird_geloggt )
	   {
	   $text = "Variable ". $id . " wird nicht geloggt";
	   
	   SetValue($ContentId,$text);
	   return;
	   }
	
	// ID der String Variable in welche die Daten geschrieben werdern
	$CfgDaten['ContentVarableId']= $ContentId;
   // ID des Highcharts Scripts
	$CfgDaten['HighChartScriptId']= IPS_GetScriptIDByName('Highcharts', $CategoryIdApp );  				
	// Highcharts
	$CfgDaten['Ips']['ChartType'] = 'Highcharts';

	//***************************************************************************
	// Zeitraum welcher dargestellt werden soll
	//***************************************************************************
	$startid = IPS_GetVariableIDByName('StartTime',$HighchartsId);
	$endeid  = IPS_GetVariableIDByName('EndTime',$HighchartsId);

	$start = getValue(IPS_GetVariableIDByName('StartTime',$HighchartsId));
	$ende  = getValue(IPS_GetVariableIDByName('EndTime',$HighchartsId));

	if ( $start == 0 )
	   {
		if ( defined('HIGHCHARTS_ZEITRAUM') )
			$zeitrum_stunden = HIGHCHARTS_ZEITRAUM;
		else
			$zeitrum_stunden = 24;

		$starttime = time() - (60*60*$zeitrum_stunden );

		SetValue($startid,$starttime);
		
		}
	else
	   $starttime = $start;

	if ( $ende == 0 )
	   {
		$endetime = time() ;

		SetValue($endeid,$endetime);

		}
	else
	   $endetime = $ende;

	// Highcharts-Theme
	$CfgDaten['HighChart']['Theme']="ips1007.js";

	$CfgDaten['StartTime'] = $starttime;
	$CfgDaten['EndTime']   = $endetime;
	
	// damit wird die Art des Aufrufes festgelegt
	$CfgDaten['RunMode'] = "script";
	
	// **************************************************************************************
	// *** Highcharts Options ***
	// **************************************************************************************
	// Ab hier werden die Bereiche des Highchart-Objektes parametriert.
	// Dieser Bereich wurde (soweit möglich) identisch der Originalstruktur gehalten.
	// Informationen über die Parametrierung findet man unter http://www.highcharts.com/ref/
	
   $CfgDaten['chart']['animation'] = false;
//	$CfgDaten['title']['text'] = "Leistung " .$objectname ;
	$CfgDaten['title']['text'] = " ";
//	$CfgDaten['title']['style']['color'] = "#FFFFFF";

	//$CfgDaten['title']['text'] = date("d.m.y H:i",$starttime) ." - ".date("d.m.y H:i",$endetime);

	$CfgDaten['subtitle']['text'] = " ";
//	$CfgDaten['subtitle']['text'] = "Zeitraum: %STARTTIME% - %ENDTIME%";
//	$CfgDaten['subtitle']['Ips']['DateTimeFormat'] = "(D) d.m.Y H:i";
//   $CfgDaten['subtitle']['text'] = date("d.m.y H:i",$starttime) ." - ".date("d.m.y H:i",$endetime);

	$CfgDaten['yAxis'][0]['title']['text'] = "Watt";
	$CfgDaten['yAxis'][0]['Unit'] = "Watt";
	$CfgDaten['yAxis'][0]['opposite'] = false;
	$CfgDaten['yAxis'][0]['min'] = 0;

	$CfgDaten['yAxis'][1]['title']['text'] = date("d.m.y H:i",$starttime) ." - ".date("d.m.y H:i",$endetime);
	
	if ( $maxleistung > 0 )
	   {
    	$pb['from'] = $maxleistung;
    	$pb['to'] = 1000000;
    	$pb['color'] = 'rgba(255, 0, 0, 0.2)';
    	$CfgDaten["yAxis"][0]['plotBands'][] = $pb;
		}

	//***************************************************************************
	// Serienübergreifende Einstellung für das Laden von Werten
	// Systematik funktioniert jetzt additiv.
	// D.h. die angegebenen Werte gehen ab dem letzten Wert
	//
	//            -5 Tage           -3 Tage    					EndTime
	// |           |              	|            				 |
	// |           |DayValue = 2     |HourValues = 3          |
	// |Tageswerte |Stundenwerte     |jeder geloggte Wert     |
	//***************************************************************************
	$CfgDaten['AggregatedValues']['HourValues'] 		= 2;      	// ist der Zeitraum größer als X Tage werden Stundenwerte geladen
	$CfgDaten['AggregatedValues']['DayValues'] 		= 35;       // ist der Zeitraum größer als X Tage werden Tageswerte geladen
	$CfgDaten['AggregatedValues']['WeekValues'] 		= -1;      	// ist der Zeitraum größer als X Tage werden Wochenwerte geladen
	$CfgDaten['AggregatedValues']['MonthValues'] 	= -1;      	// ist der Zeitraum größer als X Tage werden Monatswerte geladen
	$CfgDaten['AggregatedValues']['YearValues'] 		= -1;     	// ist der Zeitraum größer als X Tage werden Jahreswerte geladen
	$CfgDaten['AggregatedValues']['NoLoggedValues'] = 1000; 		// ist der Zeitraum größer als X Tage werden keine Boolean Werte mehr geladen,
																					//	diese werden zuvor immer als Einzelwerte geladen
	$CfgDaten['AggregatedValues']['MixedMode'] 		= true;    // alle Zeitraumbedingungen werden kombiniert
//	$CfgDaten['AggregatedValues']['MixedMode'] 		= false;    // alle Zeitraumbedingungen werden kombiniert



	//***************************************************************************
	// Buttons fuer Zeitraumauswahl erstellen
	//***************************************************************************
	$PosYOffset = 5;
	$PosXOffset = 5;
	$PosY = 0;
	$PosX = 0;
	$ImageSizeHeight = 30;
	$ImageSizeWidth  = 55;
	$StartPosLeft = 5 ;

   $IPS_SELF = IPS_GetScriptIDByName('Plugwise_Config_Highcharts', $CategoryIdApp );

	$CfgDaten['exporting']['buttons']['Back']['x']       		= ($StartPosLeft-15) - ( 2 * $ImageSizeWidth ) - 14 ;
	$CfgDaten['exporting']['buttons']['Back']['y'] 				= $PosY;
	$CfgDaten['exporting']['buttons']['Back']['symbol']  		= "url(/user/Plugwise/images/HighchartsRueckwaerts.png)";
	$CfgDaten['exporting']['buttons']['Back']['symbolX'] 		= $PosXOffset;
	$CfgDaten['exporting']['buttons']['Back']['symbolY'] 		= $PosYOffset;
	$CfgDaten['exporting']['buttons']['Back']['height']  		= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['Back']['width']   		= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['Back']['_titleKey'] 	= 'myBackButton';
	$CfgDaten['exporting']['buttons']['Back']['onclick'] 		= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Backward&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	$CfgDaten['exporting']['buttons']['Home']['x']       		= ($StartPosLeft-15) - ( 1 * $ImageSizeWidth ) - 7;
	$CfgDaten['exporting']['buttons']['Home']['y'] 				= $PosY;
	$CfgDaten['exporting']['buttons']['Home']['symbol']  		= "url(/user/Plugwise/images/HighchartsHome.png)";
	$CfgDaten['exporting']['buttons']['Home']['symbolX'] 		= $PosXOffset;
	$CfgDaten['exporting']['buttons']['Home']['symbolY'] 		= $PosYOffset;
	$CfgDaten['exporting']['buttons']['Home']['height']  		= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['Home']['width']   		= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['Home']['_titleKey'] 	= 'myHomeButton';
	$CfgDaten['exporting']['buttons']['Home']['onclick'] 		= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Home&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	$CfgDaten['exporting']['buttons']['For']['x']       		= $StartPosLeft-15;
	$CfgDaten['exporting']['buttons']['For']['y'] 				= $PosY;
	$CfgDaten['exporting']['buttons']['For']['symbol']  		= "url(/user/Plugwise/images/HighchartsVorwaerts.png)";
	$CfgDaten['exporting']['buttons']['For']['symbolX'] 		= $PosXOffset;
	$CfgDaten['exporting']['buttons']['For']['symbolY'] 		= $PosYOffset;
	$CfgDaten['exporting']['buttons']['For']['height']  		= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['For']['width']   		= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['For']['_titleKey'] 	= 'myForButton';
	$CfgDaten['exporting']['buttons']['For']['onclick'] 		= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Forward&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	$CfgDaten['exporting']['buttons']['Stunde']['x']       	= $StartPosLeft ;
	$CfgDaten['exporting']['buttons']['Stunde']['y'] 			= $PosY;
	$CfgDaten['exporting']['buttons']['Stunde']['symbol']  	= "url(/user/Plugwise/images/HighchartsStunde.png)";
	$CfgDaten['exporting']['buttons']['Stunde']['symbolX'] 	= $PosXOffset;
	$CfgDaten['exporting']['buttons']['Stunde']['symbolY'] 	= $PosYOffset;
	$CfgDaten['exporting']['buttons']['Stunde']['height']  	= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['Stunde']['width']   	= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['Stunde']['align']     = "left" ;
	$CfgDaten['exporting']['buttons']['Stunde']['_titleKey'] = 'myHourButton';
	$CfgDaten['exporting']['buttons']['Stunde']['onclick'] 	= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Hour&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	$CfgDaten['exporting']['buttons']['Tag']['x']       		= $StartPosLeft + ( 1 * $ImageSizeWidth ) + 7;
	$CfgDaten['exporting']['buttons']['Tag']['y'] 				= $PosY;
	$CfgDaten['exporting']['buttons']['Tag']['symbol']  		= "url(/user/Plugwise/images/HighchartsTag.png)";
	$CfgDaten['exporting']['buttons']['Tag']['symbolX'] 		= $PosXOffset;
	$CfgDaten['exporting']['buttons']['Tag']['symbolY'] 		= $PosYOffset;
	$CfgDaten['exporting']['buttons']['Tag']['height']  		= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['Tag']['width']   		= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['Tag']['align']       	= "left" ;
	$CfgDaten['exporting']['buttons']['Tag']['_titleKey'] 	= 'myDayButton';
	$CfgDaten['exporting']['buttons']['Tag']['onclick'] 		= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Day&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	$CfgDaten['exporting']['buttons']['Woche']['x']       	= $StartPosLeft + ( 2 * $ImageSizeWidth ) + 14;
	$CfgDaten['exporting']['buttons']['Woche']['y'] 			= $PosY;
	$CfgDaten['exporting']['buttons']['Woche']['symbol']  	= "url(/user/Plugwise/images/HighchartsWoche.png)";
	$CfgDaten['exporting']['buttons']['Woche']['symbolX'] 	= $PosXOffset;
	$CfgDaten['exporting']['buttons']['Woche']['symbolY'] 	= $PosYOffset;
	$CfgDaten['exporting']['buttons']['Woche']['height']  	= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['Woche']['width']   	= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['Woche']['align']      = "left" ;
	$CfgDaten['exporting']['buttons']['Woche']['_titleKey'] 	= 'myWeekButton';
	$CfgDaten['exporting']['buttons']['Woche']['onclick'] 	= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Week&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	$CfgDaten['exporting']['buttons']['Monat']['x']       	= $StartPosLeft + ( 3 * $ImageSizeWidth ) + 21;
	$CfgDaten['exporting']['buttons']['Monat']['y'] 			= $PosY;
	$CfgDaten['exporting']['buttons']['Monat']['symbol']  	= "url(/user/Plugwise/images/HighchartsMonat.png)";
	$CfgDaten['exporting']['buttons']['Monat']['symbolX'] 	= $PosXOffset;
	$CfgDaten['exporting']['buttons']['Monat']['symbolY'] 	= $PosYOffset;
	$CfgDaten['exporting']['buttons']['Monat']['height']  	= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['Monat']['width']   	= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['Monat']['align']      = "left" ;
	$CfgDaten['exporting']['buttons']['Monat']['_titleKey'] 	= 'myMonthButton';
	$CfgDaten['exporting']['buttons']['Monat']['onclick'] 	= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Month&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	$CfgDaten['exporting']['buttons']['Jahr']['x']       		= $StartPosLeft + ( 4 * $ImageSizeWidth ) + 28;
	$CfgDaten['exporting']['buttons']['Jahr']['y'] 				= $PosY;
	$CfgDaten['exporting']['buttons']['Jahr']['symbol']  		= "url(/user/Plugwise/images/HighchartsJahr.png)";
	$CfgDaten['exporting']['buttons']['Jahr']['symbolX'] 		= $PosXOffset;
	$CfgDaten['exporting']['buttons']['Jahr']['symbolY'] 		= $PosYOffset;
	$CfgDaten['exporting']['buttons']['Jahr']['height']  		= $ImageSizeHeight;
	$CfgDaten['exporting']['buttons']['Jahr']['width']   		= $ImageSizeWidth;
	$CfgDaten['exporting']['buttons']['Jahr']['align']       = "left" ;
	$CfgDaten['exporting']['buttons']['Jahr']['_titleKey'] 	= 'myYearButton';
	$CfgDaten['exporting']['buttons']['Jahr']['onclick'] 		= "@function() { new Image().src = '/user/Plugwise/HighchartsCommand.php?VarID=".$IPS_SELF."&Time=Year&Start=".$CfgDaten['StartTime']."&End=".$CfgDaten['EndTime']." '; }@";

	// **************************************************************************************
	// *** series *** 
	// **************************************************************************************
	$serie = array();
	$serie['name'] = "Leistung ";
	$serie['Id'] = $id;
	//$serie['color'] = "#CC9933";
	$serie['Unit'] = "Watt";
	//$serie['ReplaceValues'] = true;
	
	$serie['step'] = true;
	$serie['type'] = "areaspline";
	$serie['type'] = "area";
	$serie['yAxis'] = 0;
	$serie['marker']['enabled'] = false;
	//$serie['AggType'] = 0;
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

	$CfgDaten['plotOptions']['areaspline']['fillColor']['linearGradient'] = array(0, 0, 0, 300); // Winkel,,,,
	$CfgDaten['plotOptions']['areaspline']['fillColor']['stops'] = array(array(0,'rgba(255,0,0,0.5)'),array(1,'rgba(0,255,0,1)'));

	$CfgDaten['plotOptions']['area']['fillColor']['linearGradient'] = array(0, 0, 0, 300); // Winkel,,,,
	$CfgDaten['plotOptions']['area']['fillColor']['stops'] = array(array(0,'rgba(255,0,0,0.5)'),array(1,'rgba(0,255,0,1)'));

	$CfgDaten['series'][] = $serie;

	// Abmessungen des erzeugten Charts
	$CfgDaten['HighChart']['Width'] = 0; 			// in px,  0 = 100%
	$CfgDaten['HighChart']['Height'] = 450; 		// in px

	//***************************************************************************
	// und jetzt los ......
	//***************************************************************************

	IPSUtils_Include ("Highcharts.ips.php",      "IPSLibrary::app::hardware::Plugwise");
	//IPSUtils_Include ("IPSHighcharts.inc.php","IPSLibrary::app::modules::Charts::IPSHighcharts");

	$TimeControl = false;
   if ( isset($CfgDaten['RefreshID'] ) )
	 	$TimeControl = $CfgDaten['RefreshID'];

  	// hier werden die CfgDaten geprüft und bei Bedarf vervollständigt
	$CfgDaten = CheckCfgDaten($CfgDaten);								// hier werden die CfgDaten geprüft und bei Bedarf vervollständigt
	//IPSLogger_Dbg(__FILE__,date('d.m.Y h:i:s',$CfgDaten['StartTime'])."+++++++++++".date('d.m.Y h:i:s',$CfgDaten['EndTime']));


		if ($_IPS['SENDER'] != "WebInterface" or $TimeControl == true )
			{
			$CfgDaten = CheckCfgDaten($CfgDaten);

			WriteContentWithScriptId ($CfgDaten, $IPS_SELF);     		// und jetzt noch die ContentTextbox
			return;                                               	// Ende, weil durch die Zuweisung des Script sowieso nochmals aufgerufen wird
			}

		$sConfig = CreateConfigString($CfgDaten);             		// erzeugen und zurückgeben des Config Strings



?>
