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

	$imagepath  = "./user/BusBahnInfo/images/";
	$csspath    = "./user/BusBahnInfo/";

	$debug 	= DEBUG_MODE;      /**< Debugmode true/false */
	$log 		= LOG_MODE;
	$log = true;
	GLOBAL $stationen;

	check_unused($stationen);

	$sort_nummer = 10;

	foreach( $stationen as $line )
    {
		if ( $line[0] != "" and $line[1] != "" and $line[2] != "" )
		   {
	   	 if ( $debug ) echo "\n" . $line[0] ." - " . $line[1] ." - " .$line[2];

			 $bahn=new bahn($line[1],$line[2]);
			 $bahn->TypeICE($line[4]);
			 $bahn->TypeIC($line[5]);
			 $bahn->TypeIR($line[6]);
			 $bahn->TypeRE($line[7]);
			 $bahn->TypeSBAHN($line[8]);
			 $bahn->TypeBUS($line[9]);
			 $bahn->TypeFAEHRE($line[10]);
			 $bahn->TypeUBAHN($line[11]);
			 $bahn->TypeTRAM($line[12]);

			 if($bahn->fetch(PROXY_SERVER))
				{
    			anzeige($bahn,$line,$sort_nummer);
    			$sort_nummer = $sort_nummer + 10 ;
    			
				}
			 else
	   		if ( $debug ) echo "\nKeine Informationen vorhanden ";

	    }
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
* Checkt ob Bahnhof aus Data oder Visu geloescht werden kann
* und loescht diese dann
* @param $stationen alle Station
* @return none
* @todo   nothing
*******************************************************************************/
function check_unused($stationen)
	{
	GLOBAL   $CategoryData;
	GLOBAL   $VisuData;
	GLOBAL   $debug;

	// nicht mehr definierte Variablen in Datapath loeschen
   foreach(IPS_GetChildrenIDs($CategoryData) as $child)
      {
      $object = IPS_GetObject($child);
 		//print_r(IPS_GetObject($child));
      $name = IPS_GetName($child);
		//echo "\n" .$name;
		$gefunden = false;
		foreach($stationen as $station )
		   {
			$station_alias 	= $station[0];    // Name fuer Webtab
			$station_name  	= $station[1];    // Name Bahnhof
			$station_richtung = $station[2];    // Ankunft / Abfahrt
			$vardataname      = $station_richtung . " " . $station_name;
			$vardataname      = $station_alias;
			$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;

			$tabname = $station_name . " " . $station_richtung;
			if ( $name == $vardataname )
				$gefunden = true;
		   }
		if ( !$gefunden )
		   {
		   //echo "------nicht gefunden !";
		   if ( $object['ObjectType'] == 0 )
		      IPS_DeleteCategory($child);
		   if ( $object['ObjectType'] == 2 )
				{ IPS_DeleteVariable($child); if ($debug ) echo "\nData " . $child . "geloescht\n"; }

			}

      }


	// nicht mehr definierte Variablen in Visupath loeschen
   foreach(IPS_GetChildrenIDs($VisuData) as $child)
      {
      $object = IPS_GetObject($child);
		//print_r($object);
      $name = IPS_GetName($child);


		if ( $object['ObjectType'] != 2 )
		   {
			$gefunden = false;
			foreach($stationen as   $station)
		   	{
				$station_alias 	= $station[0];    // Name fuer Webtab
				$station_name  	= $station[1];    // Name Bahnhof
				$station_richtung = $station[2];    // Ankunft / Abfahrt
				$vardataname      = $station_richtung . " " . $station_name;
				$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;

				$tabname = $station_name . " " . $station_richtung;
				if ( $name == $vardataname )
					$gefunden = true;

		   	}

			if ( !$gefunden )
		   	{
				if ($debug ) echo "\nVisu " . $child . "geloescht";
   			foreach(IPS_GetChildrenIDs($child) as $subchild)
   			   {
					IPS_DeleteLink($subchild);

					}
				IPS_DeleteCategory($child);
				IPS_DeleteLink($child);
				}



			// teste ob mehr als ein child
			$anzahl_childs = count(IPS_GetChildrenIDs($child));
			// wenn mehr als 1 child alle loeschen
			if ( $anzahl_childs > 1 )
   			foreach(IPS_GetChildrenIDs($child) as $subchild)
					IPS_DeleteLink($subchild);
			}
      }




	}

/***************************************************************************//**
* Erstellt ein String und schreibt in Data und Visu.
* Variablen werden automatisch erstellt.
* @param  $bahn     class bahn
* @param  $station  aktuelle Station
* @return none
* @todo   nothing
*******************************************************************************/
function anzeige($bahn,$station,$sort_nummer)
	{
	GLOBAL   $CategoryData;
	GLOBAL   $CategoryPath;
	GLOBAL   $VisuPath;
	GLOBAL   $VisuData;
	GLOBAL 	 $debug;
	GLOBAL   $imagepath;
	GLOBAL   $csspath;


	$station_alias 	= $station[0];    // Name fuer Webtab
	$station_name  	= $station[1];    // Name Bahnhof
	$station_richtung = $station[2];    // Ankunft / Abfahrt
	$station_wegezeit = $station[3];    // Wegezeit zum Bahnhof
	$auswahl_string   = "";             // Ausgewaehlte Verkehrsmittel
	$vardataname      = $station_name . " - " . $station_alias . " - " .$station_richtung;
	//$vardataname      = $station_alias;
	//***************************************************************************
	// HTML Kopf
	//***************************************************************************
	$str = "<html><head>";
	$str .= "<link rel='stylesheet' type='text/css' href='".$csspath."BusBahnInfo.css'>";
	$str .= "</head>";

	//***************************************************************************
	// HTML Body
	//***************************************************************************
	$str .= "<body>";
	$str .= "<div class='body'></div>";
	$str .= "<table width='100%' align='center' border='0'>";
	//$str .= "<table div class='table' >";
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
	$str .= "<th><div class='titel_ankunft'>Ankunft</div></th>";
	$str .= "<th><div class='titel_plattform'>Plattform</div></th>";
	$str .= "<th><div class='titel_aktuelles'>Aktuelles</div></th>";
	$str .= "</tr>";

	if ( $debug )print_r($bahn->timetable);
	$eintrag = array("","","","","","","");
	$pos = 0;
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
      $timestampField = strtotime($bahn->timetable[$i]["time"]);
      $timestampNow = time();//+1*60*60;

      $diff = $timestampField - $timestampNow;

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
		
		$spalte4    = "<div onclick=\"{with(document.getElementById('".$div_id."').style){if(display=='none'){display='inline';}else{display='none';}}};\">";
		$spalte4   .= $zielstring_raw."</div>";
		$eintrag[4] = $spalte4;
		$eintrag[4]="<div class='richtung'>".$eintrag[4]."</div>";

      $eintrag[5] = substr($route[count($route)-1],0,5);
		$eintrag[5]="<div class='ankunft'>".$eintrag[5]."</div>";

		$platform_str = @$bahn->timetable[$i]["platform"];
		$platform_nr = intval($platform_str);

		if ( $platform_nr != 0 ) $platform_str = $platform_nr;

   	$platform_str = cosmetic_string($platform_str,6);
      $eintrag[6] = $platform_str;
      
		$eintrag[6]="<div class='plattform'>".$eintrag[6]."</div>";

      $spalte7    = @$bahn->timetable[$i]["ris"];
		$spalte7 = cosmetic_string($spalte7,7);
		
		$spalte7 .= "<div id='".$div_id."' style='display:none;'</div><br>";

		if ( is_array($route) )
	 	foreach ( $route as $halt )
		 		{
				//$spalte7 .= $halt."<br>";
   			$halt = cosmetic_string($halt,7);
				$spalte7 .="<div class='route'>".$halt."</div>";

				}

		//$spalte7 .= "</div>";
      $eintrag[7] = $spalte7;
      $eintrag[7]="<div class='aktuelles'>".$eintrag[7]."</div>";

      $str .= "<tr valign='top'>";

		//$eintrag[1] = $div_id;
		$str .= '<td align="center">'.$eintrag[0].'</td>';
		$str .= '<td align="left">'  .$eintrag[1].'</td>';
		$str .= '<td align="center">'.$eintrag[2].'</td>';
		$str .= '<td align="center">'.$eintrag[3].'</td>';
		$str .= '<td align="left">'  .$eintrag[4].'</td>';
		$str .= '<td align="center">'.$eintrag[5].'</td>';
		$str .= '<td align="center">'.$eintrag[6].'</td>';
		$str .= '<td align="left">'  .$eintrag[7].'</td>';

		$str .= "</tr>";
      $pos++;

      if($pos >= MAX_LINES)
            break;
    }

   $str .= "</table>";
	$str .= "</body>";
	$str .= "</html>";
	//***************************************************************************
	// HTML Ende
	//***************************************************************************

	// schreibt Daten in Datapath.Wenn Variable nicht vorhanden wird sie erstellt
   $id = CreateCategoryPath($CategoryPath);
	
   $varid = CreateVariable($vardataname,3,$id,$sort_nummer,"~HTMLBox",false,false,"");
   SetValue($varid, $str);

	// wenn Tab in Visu nicht vorhanden wird er erstellt
	$tabname = $station_alias;
  	$tabid = CreateCategoryPath($VisuPath);

	// wenn Link nicht vorhanden wird er erstellt
	$linkname = $vardataname ;

	$exist = IPS_GetLinkIDByName($linkname,$VisuData);
	if ( ! $exist )
	   {
		$id = CreateLink ($linkname, $varid, $tabid, 10);
		IPS_SetHidden($id, true);
		}

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
* @}
*******************************************************************************/

?>
