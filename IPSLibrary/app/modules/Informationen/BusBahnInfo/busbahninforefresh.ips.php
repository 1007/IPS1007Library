<?
/***************************************************************************//**
* @addtogroup busbahninfo
* @{
* @file          busbahninforefresh.ips.php
* @author        1007
* @version       1.0.1
*
* @brief Script zur Anzeige von Abfahrtstafeln im Webfront
* @details Dieses Script liest das dazugehoerige Configurationfile und holt
* die Daten der Stationen von http://reiseauskunft.bahn.de/bin/bhftafel.exe/dn?
* Benutzt wird die "Bus und Bahn API" von Author: Frederik Granna (sysrun)
* Bei Aenderungen im Konfigurationsfile braucht kein Install ausgefuehrt werden
* Neue Stationen werden automatisch waehrend der Laufzeit angelegt.
* Jedoch kein Webfrontrefresh.
* Mit einem Klick auf den Zielbahnhof werden die Zwischenstationen angezeigt.
*
* Original Script von sysrun
* http://www.ip-symcon.de/forum/f53/class-abfahrtstafeln-bahn-de-auslesen-10416/
*
* @todo   Ausgabe der Verkehrsmittelbilder verbessern
* @bug
*
*******************************************************************************/

	IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");

	IPSUtils_Include ("BusBahnInfo_Configuration.inc.php", "IPSLibrary::config::modules::Informationen::BusBahnInfo");
	IPSUtils_Include ("busbahninfo.class.php", "IPSLibrary::app::modules::Informationen::BusBahnInfo");

  	$CategoryPath = "Program.IPSLibrary.data.modules.Informationen.BusBahnInfo";
	$VisuPath     = "Visualization.WebFront.Informationen.BusBahnInfo";

  	$CategoryData = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.BusBahnInfo");
  	$VisuData 	  = IPSUtil_ObjectIDByPath("Visualization.WebFront.Informationen.BusBahnInfo");

	$imagepath  = "/user/BusBahnInfo/images/";
	$csspath    = "/user/BusBahnInfo/";

	$debug 	= DEBUG_MODE;      /**< Debugmode true/false */
	$log 		= LOG_MODE;

	GLOBAL $stationen;


	stationen_manager($stationen);
	
	// Daten holen und in die Variablen in data schreiben
	$counter = 1 ;
	foreach( $stationen as $line )
    	{
		if ( $line[0] != "" and $line[1] != "" and $line[2] != "" )
		   {
	   	 if ( $debug ) echo "\n" . $line[0] ." - " . $line[1] ." - " .$line[2];
			 logging("",'busbahninfo.log',true); // Leerzeile
			 logging("[".$line[0] ."][" . $line[1] ."][" .$line[2] ."]");
			 
			 $startt = time();
			 
			 // Klasse einrichten
			 $bahn=new bahn($line[1]);

			 $bahn->Type($line[2]);    // Ankunft-Abfahrt

			 $bahn->TypeICE($line[4]);
			 $bahn->TypeIC($line[5]);
			 $bahn->TypeIR($line[6]);
			 $bahn->TypeRE($line[7]);
			 $bahn->TypeSBAHN($line[8]);
			 $bahn->TypeBUS($line[9]);
			 $bahn->TypeFAEHRE($line[10]);
			 $bahn->TypeUBAHN($line[11]);
			 $bahn->TypeTRAM($line[12]);
			 
			 $bahn->noresult = "";
			 
			 if ( ISSET($line[14]) )
				$bahn->zeit($line[14]);

			 if ( ISSET($line[15]) )
				$bahn->ziel($line[15]);

			 $html = html_head();
			 
			 if($bahn->fetch(PROXY_SERVER,$counter))
				{
    			//$html = anzeige($bahn,$line);
    			$html = $html . html_body($bahn,$line);
				}
			 else
			   {
	   		if ( $debug ) echo "\nKeine Informationen vorhanden ";
				logging("Keine Informationen vorhanden");
				$html = $html . "Keine Daten oder Station nicht eindeutig";
				$html = $html . $bahn->noresult;
				}

         $html = $html . html_end();

			if ( $debug )
			   {
			   $endt = time();
			   $difft = $endt - $startt ;
			   
			   echo "\n" .$line[0] ."[" .$difft."s]".count($bahn->timetable);
			   }

			
   		$id = CreateCategoryPath($CategoryPath);
         $station_alias 	= $line[0];    // Name fuer Webtab
			$station_name  	= $line[1];    // Name Bahnhof
			$station_richtung = $line[2];    // Ankunft / Abfahrt
			$vardataname      = $station_alias;
			$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;

			$id = IPS_GetObjectIDByName($vardataname,$id);
			if ( $id )
			   {
			   SetValueString($id,$html);
			   // create HTML-Dateien
			   $datei = IPS_GetKernelDir() . "webfront\\user\\BusBahnInfo\\".$counter.".html";
				$datei = fopen($datei,"w");
				fwrite($datei,$html );
				fclose($datei);

			   }
			   

	    }
		$counter = $counter + 1;
	  }

	make_info();
	
	// Ausgewaehlte Station nach Visu kopieren
	//copyhtml_visu();

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
* HTML nach Visu kopieren
*******************************************************************************/
function make_info()
	{
	GLOBAL $imagepath;
	IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');

	$moduleManager = new IPSModuleManager('BusBahnInfo');

	$html = html_head();
   $version = "<br><h2>Version : " .$moduleManager->VersionHandler()->GetVersion('BusBahnInfo') ."</h2>";
	$html = $html . "<div align='center'>";
	$html = $html . "<h2>Bus und Bahn Informationen</h2><br>";
	$html = $html . "<img src='".$imagepath."/BusBahnInfo.png' style='display: block;margin: 0px auto;' >" ;
	$html = $html . $version;
   $html = $html . "</div>";

   $html = $html . html_end();


	$datei = IPS_GetKernelDir() . "webfront\\user\\BusBahnInfo\\0.html";
	$datei = fopen($datei,"w");
	fwrite($datei,$html );
	fclose($datei);

	}

/***************************************************************************//**
* HTML nach Visu kopieren
*******************************************************************************/
function copyhtml_visu()
  {
	GLOBAL $CategoryData;
	GLOBAL $VisuData;
  
	$stid = @IPS_GetVariableIDByName('Station',$VisuData) ;
   
   if ( $stid )
      $st = GetValue($stid);
	else
		{
		IPS_LogMessage('BusBahnInfo','Fehler in Stationsnummer');
	   return;
		}
	$quelle = @IPS_GetObjectIDByIdent($st,$CategoryData);
	$ziel   = @IPS_GetObjectIDByName("Data",$VisuData);
	if ( $quelle and $ziel )
	   {
	   SetValueString($ziel,GetValueString($quelle));
	   
	   $datei = IPS_GetKernelDir() . "webfront\\user\\BusBahnInfo\\BusBahnInfo.html";
		$datei = fopen($datei,"w");
		fwrite($datei,GetValueString($quelle) );
		fclose($datei);

	   
	   
	   }
	else
	   {
	   if ( $ziel )
			{
	   	IPS_Logmessage('BusBahnInfo',"Fehler bei GetIdent ". $st);
         SetValue($stid,1);
			}
	   }

  }


/***************************************************************************//**
* Checkt ob Bahnhof aus Data geloescht werden kann
* legt neue Stationen an
* @param $stationen alle Station
* @return none
* @todo   nothing
*******************************************************************************/
function stationen_manager($stationen)
	{
	GLOBAL   $CategoryData;
	GLOBAL   $VisuData;
	GLOBAL   $CategoryPath;
	GLOBAL   $debug;

	$reset_data = false ;
	
   foreach(IPS_GetChildrenIDs($CategoryData) as $child)
      {
      $object = IPS_GetObject($child);
      $name   = IPS_GetName($child);

		$gefunden = false;
		
		
		foreach($stationen as $station )
		   {
			$station_alias 	= $station[0];    // Name fuer Webtab
			$station_name  	= $station[1];    // Name Bahnhof
			$station_richtung = $station[2];    // Ankunft / Abfahrt
			$vardataname      = $station_alias;
			$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;
			
         
         
			if ( $name == $vardataname )
				{
				$gefunden = true;
				//echo "\n" . $name ."|" . $vardataname;
				}
		   }
		   
		if ( !$gefunden )
		   {
			$reset_data = true;
			//echo "\nReset";
			}

      }

		
		
		foreach($stationen as $station )
		   {
			$station_alias 	= $station[0];    // Name fuer Webtab
			$station_name  	= $station[1];    // Name Bahnhof
			$station_richtung = $station[2];    // Ankunft / Abfahrt
			$vardataname      = $station_alias;
			$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;

   		$id = CreateCategoryPath($CategoryPath);

			if ( $station_alias != "" and $station_name != "" )
			   {
            
				$id = @IPS_GetObjectIDByName($vardataname,$id);
				if ( $id == false )
			   	$reset_data = true;
			   	
			   	
			   }
			
			
			}



	// Variablen neu anlegen bei Aenderung
	if ( $reset_data == true )
	   {
	   IPS_LogMessage("BusBahnInfo","Reset Data");
	   $id = CreateCategoryPath($CategoryPath);
		EmptyCategory($id);
	   
	   $counter = 1 ;
		foreach($stationen as $station )
		   {
			$station_alias 	= $station[0];    // Name fuer Webtab
			$station_name  	= $station[1];    // Name Bahnhof
			$station_richtung = $station[2];    // Ankunft / Abfahrt
			$vardataname      = $station_alias;
			$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;
         //$vardataname      = $counter . ".". $vardataname ;
			if ( $station_alias != "" and $station_name != "" )
			   {
   			$varid = CreateVariable($vardataname,3,$id,$counter,"~HTMLBox",false,false,"");
   			SetValue($varid, "Init");
				IPS_SetIdent($varid,$counter);
   			
				}
			$counter = $counter + 1;
			}
	   }
	else
	   {
	   //echo "\nKeine Aenderung";
	   }


	}



/***************************************************************************//**
* HTML Head
* @return HTML Head
*******************************************************************************/
function html_head()
	{
	GLOBAL   $csspath;

	$str  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
//	$str  = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';

	$str .= "<html><head>";
//	$str .= '<meta http-equiv="cache-control" content="no-cache">';
	$str .= '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1 ">';
// $str .= '<script src="'.$csspath.'jquery.min.js" type="text/javascript"></script>';
//	$str .= '<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>';

// $str .= "<script type='text/javascript'>";
//	$str .= 'function startAjax1() { $.ajax({ type: "POST", url:  "/user/BusBahnInfo/BusBahnInfoWebMenuController.php", data: "name=1" }); }';
// $str .= '</script>';
//	$str .= "<script type='text/javascript'>";
//	$str .= 'function startAjax()  {$.ajax({ url: "/user/BusBahnInfo/BusBahnInfoWebMenuController.php",cache: false , data: {"Button":"1"},success:function(data){ alert("successful"); }});}</script> ';
//	$str .= 'function startAjax()  $.get("/user/BusBahnInfo/BusBahnInfoWebMenuController.php",{func:"getNameAndTime"},function(data){alert(data.name);},"jsonp");</script> ';



	$str .= '<link rel="stylesheet" type="text/css" href="'.$csspath.'BusBahnInfo1920.css" media="only screen and (max-device-width: 1920px)" />';
	$str .= '<link rel="stylesheet" type="text/css" href="'.$csspath.'BusBahnInfo1680.css" media="only screen and (max-device-width: 1680px)" />';
	$str .= '<link rel="stylesheet" type="text/css" href="'.$csspath.'BusBahnInfo1024.css" media="only screen and (max-device-width: 1024px)" />';
	$str .= '<link rel="stylesheet" type="text/css" href="'.$csspath.'BusBahnInfo768.css"  media="only screen and (max-device-width: 768px)" />';


	$str .= "<link rel='stylesheet' type='text/css' href='".$csspath."BusBahnInfoCSS3.css'  />";
	$str .= '<meta http-equiv="refresh" content="60" >';
	$str .= "</head>";
	$str .= "<body>";
	$str .= create_css3_menu();

	return $str;
	}

/***************************************************************************//**
* HTML BODY
* @return HTML Body
*******************************************************************************/
function html_body($bahn,$station)
	{
	GLOBAL   $CategoryData;
	GLOBAL   $CategoryPath;
	GLOBAL   $VisuPath;
	GLOBAL   $VisuData;
	GLOBAL 	$debug;
	GLOBAL   $imagepath;
	GLOBAL   $csspath;
	

	$station_alias 	= $station[0];    // Name fuer Webtab
	$station_name  	= $station[1];    // Name Bahnhof
	$station_richtung = $station[2];    // Ankunft / Abfahrt
	$station_wegezeit = $station[3];    // Wegezeit zum Bahnhof
	$auswahl_string   = "";             // Ausgewaehlte Verkehrsmittel
	$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;

	$str  = '';
	$str .= "<div class='body' valign=middle style='font-size: 13pt'>$station_richtung - $station_alias - ";
	if ( $station[4] ) 	$str .=  "<img align=top src=".$imagepath."ice_24x24.gif>";  	// ICE
	if ( $station[5] ) 	$str .=  "<img align=top src=".$imagepath."ec_ic_24x24.gif>";  	// IC/EC
	if ( $station[6] ) 	$str .=  "<img align=top src=".$imagepath."ir_24x24.gif>";  		// Interregio/Schnellzuege
	if ( $station[7] ) 	$str .=  "<img align=top src=".$imagepath."re_24x24.gif>";  	// Nahverkehr/Sonstiges
	if ( $station[8] ) 	$str .=  "<img align=top src=".$imagepath."sbahn_24x24.gif>";  	// SBahn
	if ( $station[9] ) 	$str .=  "<img align=top src=".$imagepath."bus_24x24.gif>";  	// Bus
	if ( $station[10] ) 	$str .=  "<img align=top src=".$imagepath."faehre_24x24.gif>";  	// Faehren
	if ( $station[11] ) 	$str .=  "<img align=top src=".$imagepath."ubahn_24x24.gif>";  	// UBahn
	if ( $station[12] ) 	$str .=  "<img align=top src=".$imagepath."re_24x24.gif>";  // Tram


	$str .= "</div>";

	$str .= "<table width='100%' align='center' border='0'>";
	
   $str .= "<colgroup>";
	$str .= "<col width='10' >";
	$str .= "<col width='10' >";
	$str .= "<col width='10' >";
	$str .= "<col width='10' >";
	$str .= "<col width='10'>";
	$str .= "<col width='10' >";
	$str .= "<col width='10'>";
	$str .= "<col width='*'>";
	$str .= "</colgroup>";

   $str .= "<tr>";
	$str .= "<th><div class='titel_verkehrsmittel'></div></th>";
	$str .= "<th><div class='titel_train'></div></th>";
	$str .= "<th><div class='titel_srichtung'>".$station_richtung."</div></th>";
	$str .= "<th><div class='titel_diff'>Diff</div></th>";
	$str .= "<th><div class='titel_richtung'>Richtung</div></th>";

	If ( $station_richtung == "Abfahrt")
		$str .= "<th><div class='titel_ankunft'>Ankunft</div></th>";
	If ( $station_richtung == "Ankunft")
		$str .= "<th><div class='titel_ankunft'></div></th>";

	$str .= "<th><div class='titel_plattform'>Plattform</div></th>";
	$str .= "<th><div class='titel_aktuelles'>Aktuelles</div></th>";
	$str .= "</tr>";

	
	$eintrag = array("","","","","","","");
	$pos = 0;
	$heute = 0;
   for($i=0; $i<sizeof($bahn->timetable); $i++)
   	{
      $caller = $bahn->timetable[$i]["type"];

		switch($caller)
		  		{
           	case "UBAHN": 	$eintrag[0] = "<img src=".$imagepath."ubahn_24x24.gif>"	;break;
           	case "SBAHN": 	$eintrag[0] = "<img src=".$imagepath."sbahn_24x24.gif>"	;break;
           	case "BUS":		$eintrag[0] = "<img src=".$imagepath."bus_24x24.gif>"		;break;
           	case "RE":		$eintrag[0] = "<img src=".$imagepath."re_24x24.gif>"		;break;
           	case "IR":		$eintrag[0] = "<img src=".$imagepath."ir_24x24.gif>"		;break;
           	case "ICE":		$eintrag[0] = "<img src=".$imagepath."ice_24x24.gif>"		;break;
           	case "IC":		$eintrag[0] = "<img src=".$imagepath."ec_ic_24x24.gif>"	;break;
           	case "EC":		$eintrag[0] = "<img src=".$imagepath."ec_ic_24x24.gif>"	;break;
           	case "TRAM":	$eintrag[0] = "<img src=".$imagepath."tram_24x24.gif>"	;break;
           	case "SCHIFF":	$eintrag[0] = "<img src=".$imagepath."faehre_24x24.gif>"	;break;
            default:			$eintrag[0] = $caller												;break;
      		}


		$eintrag[0]="<div class='verkehrsmittel'>".$eintrag[0]."</div>";

      $eintrag[1] = $bahn->timetable[$i]["train"];
		$eintrag[1]="<div class='train'>".$eintrag[1]."</div>";

      $eintrag[2] = $bahn->timetable[$i]["time"];
		$eintrag[2]="<div class='abfahrt'>".$eintrag[2]."</div>";

      // differenz zur aktuellen zeit ausrechnen.
      $timestampField = strtotime($bahn->timetable[$i]["time"]) ;
      $timestampNow = time();//+1*60*60;
		
      $diff = $timestampField - $timestampNow;
		
		
		if ( $diff < -14400 )   // wird wohl morgen sein
		   $diff = $diff + 86400;
		
      if ($diff >0)
      	{
         $eintrag[3] = $uhrzeit = date("H:i",$diff-1*60*60);
         if ($diff > $station_wegezeit*60)
            {
            $eintrag[3]="<div class='diff_wird_erreicht'>".$eintrag[3]."</div>";
            }
         else
         	{
            $eintrag[3]="<div class='diff_wegezeit_zulang'>".$eintrag[3]."</div>";
            }
        	}
      else
      	{
         // nicht mehr zu schaffen da zeit abgelaufen
         $eintrag[3] = "--:--";
         if ($station_wegezeit <>0)
            {
            $eintrag[3]="<div class='diff_abgefahren'>".$eintrag[3]."</div>";
				}
        	}

		$route = array();
		$route = @$bahn->timetable[$i]["route"];
		$div_id = $station_name."_".$pos."_".rand(1,100000);

		$zielstring_raw = $bahn->timetable[$i]["route_ziel"];
		$zielstring_raw = cosmetic_string($zielstring_raw,4);
		$spalte4    = "<div ";
      $spalte4   .= "ontouchstart=\"{with(document.getElementById('".$div_id."').style){if(display=='none'){display='inline';}else{display='none';}}}; \" ";
		$spalte4   .= "onclick=\"{with(document.getElementById('".$div_id."').style){if(display=='none'){display='inline';}else{display='none';}}};\">";
		$spalte4   .= $zielstring_raw."</div>";
		$eintrag[4] = $spalte4;
		$eintrag[4]="<div class='richtung'>".$eintrag[4]."</div>";

      $eintrag[5] = "99:99";
		if ( $station_richtung == "Abfahrt" )
      	$eintrag[5] = substr($route[count($route)-1],0,5);
		if ( $station_richtung == "Ankunft" )
      	$eintrag[5] = "";

      
      if ( strlen($eintrag[5]) > 2 )
      	if ( $eintrag[5][2] != ':' )
         	$eintrag[5] = "Info";
         
		$eintrag[5]="<div class='ankunft'>".$eintrag[5]."</div>";

		$platform_str = @$bahn->timetable[$i]["platform"];
		$platform_nr = intval($platform_str);

		if ( $platform_nr != 0 ) $platform_str = $platform_nr;

   	$platform_str = cosmetic_string($platform_str,6);
      $eintrag[6] = $platform_str;

		$eintrag[6]="<div class='plattform'>".$eintrag[6]."</div>";

      $spalte7    = @$bahn->timetable[$i]["ris"];
		$spalte7 = cosmetic_string($spalte7,7) ;

		if ( strlen($spalte7) > 70 )
			$spalte7 = "<MARQUEE>". $spalte7 ."</MARQUEE>" ;


		$spalte7 .= "<div id='".$div_id."' style='display:none;'</div><br>";

		if ( is_array($route) )
	 	foreach ( $route as $halt )
		 		{
				//$spalte7 .= $halt."<br>";
   			$halt = cosmetic_string($halt,7);
				$spalte7 .="<div class='route'>".$halt."</div>";

				}

		
      $eintrag[7] = $spalte7;
      $eintrag[7]="<div class='aktuelles'>".$eintrag[7]."</div>";

      $str .= "<tr valign='top'>";

		
		$str .= '<td align="center">'.$eintrag[0].'</td>';
		$str .= '<td align="left">'  .$eintrag[1].'</td>';
		$str .= '<td align="center">'.$eintrag[2].'</td>';
		$str .= '<td align="center">'.$eintrag[3].'</td>';
		$str .= '<td align="left">'  .$eintrag[4].'</td>';
		$str .= '<td align="center">'.$eintrag[5].'</td>';
		$str .= '<td nowrap align="center">'.$eintrag[6].'</td>';
		$str .= '<td nowrap align="left">'  .$eintrag[7].'</td>';

		$str .= "</tr>";
      $pos++;

      if($pos >= MAX_LINES)
            break;
    }

   $str .= "</table>";

	return $str;



	}
/***************************************************************************//**
* HTML END
* @return HTML End
*******************************************************************************/
function html_end()
	{
	$str  = "";
	$str .= "</body>";
	$str .= "</html>";

	return $str;
	}


/***************************************************************************//**
* Texte aufhuebschen
* @param $string  - Text
* @return verbesserter Text
*******************************************************************************/
function cosmetic_string($string,$index)
	{

	$suchmuster = array();
	$suchmuster[0] = '/\(/';
	$suchmuster[1] = '/\)/';
	$suchmuster[2] = '//';

	$ersetzungen = array();
	$ersetzungen[2] = ' (';
	$ersetzungen[1] = ') ';
	$ersetzungen[0] = '';

	$string=  preg_replace($suchmuster, $ersetzungen, $string);
	
	return $string;
	}

/***************************************************************************//**
* Ausgewaehlte Verkehrsmittel als hmtl-string zuruekgeben
* @param $station  - aktuelle Station
* @return HTML-String fuer ausgewaehlte Verkehrsmittel
* @todo   nothing
*******************************************************************************/
function verkehrsmittel($station)
	{
	GLOBAL   $imagepath;

	$auswahl_string   = " - <font size='2'>Verkehrsmittel : ";

	if ( $station[4] )  $auswahl_string .= "<img src=".$imagepath."ice_24x24.gif>";
	if ( $station[5] )  $auswahl_string .= "<img src=".$imagepath."ec_ic_24x24.gif>";
	if ( $station[6] )  $auswahl_string .= "<img src=".$imagepath."ir_24x24.gif>";
	if ( $station[7] )  $auswahl_string .= "<img src=".$imagepath."re_24x24.gif>";
	if ( $station[8] )  $auswahl_string .= "<img src=".$imagepath."sbahn_24x24.gif>";
	if ( $station[9] )  $auswahl_string .= "<img src=".$imagepath."bus_24x24.gif>";
	if ( $station[10] ) $auswahl_string .= "<img src=".$imagepath."faehre_24x24.gif>";
	if ( $station[11] ) $auswahl_string .= "<img src=".$imagepath."ubahn_24x24.gif>";
	if ( $station[12] ) $auswahl_string .= "<img src=".$imagepath."tram_24x24.gif>";

	//$auswahl_string   .= "Verkehrsmittel";


	return $auswahl_string;
	}


/***************************************************************************//**
* Erstelle Menu , als hmtl-string zuruekgeben
* @return HTML-String 
* @todo   nothing
*******************************************************************************/
function create_css3_menu()
	{
	GLOBAL $stationen;
	
	$html = "";
	
	$html = $html . '<!-- Start css3menu.com BODY section -->';
	$html = $html . '<ul id="css3menu1" class="topmenu">';
	
	// Menu fuer Abfahrt
	$html = $html . '<li class="topfirst"><a href="/user/BusBahnInfo/0.html" target="_self" style="height:15px;line-height:15px;">Bus/Bahn Informationen</a></li>';
	$html = $html . '<li class="topmenu" ><a href="#" target="_self" style="height:15px;line-height:15px;"><span>Abfahrt</span></a>';
	$html = $html . '<ul>' ;

	$counter = 1 ;
	foreach ( $stationen as $station )
	   {
	   if ( $station[1] != '' And $station[2] == 'Abfahrt' )
			{
			$html = $html . '<li><a href="/user/BusBahnInfo/'.$counter.'.html">  ';
			$html = $html .$station[0].'</span></a>';
			$html = $html . '</li>';
			}
			
		$counter = $counter + 1;
	   }
	$html = $html . '</ul>' ;
	$html = $html . '</li>';


	// Menu fuer Ankunft
	$html = $html . '<li class="topmenu" ><a href="#" style="height:15px;line-height:15px;"><span>Ankunft</span></a>';
	$html = $html . '<ul>' ;
	$counter = 1 ;
	foreach ( $stationen as $station )
	   {
	   if ( $station[1] != '' And $station[2] == 'Ankunft' )
	      {
			$html = $html . '<li><a href="/user/BusBahnInfo/'.$counter.'.html">';

			$html = $html . $station[0].'</a>';
			
			$html = $html . '</li>';
			}
			
		$counter = $counter + 1;
	   }
	$html = $html . '</ul>' ;
	$html = $html . '</li>';


	// Info
	$html = $html . '<li class="topmenu" ><a ';

	$html = $html . 'href="/user/BusBahnInfo/0.html" style="height:15px;line-height:15px;"><span>Info</span></a></li>';

	$html = $html . '</ul>';
	return $html;
	}

/***************************************************************************//**
* @}
*******************************************************************************/

?>
