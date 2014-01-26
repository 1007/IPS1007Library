<?php
	/*
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

	/**@defgroup geofencyinfo GeofencyInfo
	 * @ingroup modules
	 * @{
	 *
	 * @file          GeofencyInfo.inc.php
	 * @author        Juergen Gerharz
	 * @version
	 *  Version 1.0.0, 13.12.2013<br/>
	 *
	 * GeofencyInfo Include
	 *
	 */

	IPSUtils_Include("GeofencyInfo_Configuration.inc.php","IPSLibrary::config::modules::Informationen::GeofencyInfo");
 	IPSUtils_Include ("IPSLogger.inc.php","IPSLibrary::app::core::IPSLogger");

/***************************************************************************//**
*	Auto Leaving/Entering
*
*******************************************************************************/
function AutoLeaving($GEOentry,$IPSName,$GEOname,$GeofencyPOST)
	{

   $DeviceID = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.GeofencyInfo.".$IPSName, true );
	if ( !$DeviceID )
		return;
		
   if ( AUTO_LEAVING_LOCATION )
      {
		if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Autoleaving ON - ".$IPSName."-".$GEOname."-".$DeviceID);
		}
	else
	   {
		if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Autoleaving OFF - ".$IPSName."-".$GEOname."-".$DeviceID);
		return;
		}
		
	if ( $GEOentry  == true )  	// Location betreten
		$sollStatus = false;
	else
		$sollStatus = true;

	$array = IPS_GetChildrenIDs($DeviceID);
	foreach($array as $location)
		{
			
		$name = IPS_GetName($location);
		if ( $name != $GEOname)
			{
			$entryID = @IPS_GetVariableIDByName('Entry',$location);
			$geoAbID = @IPS_GetVariableIDByName('GEOAbfahrt',$location);
			$geoAnID = @IPS_GetVariableIDByName('GEOAnkunft',$location);

			if ( $entryID )
			   {
			   if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,$name);
			   
			   $istStatus = GetValueBoolean($entryID);
			   if ( $istStatus != $sollStatus )
			      {
			      SetValueBoolean($entryID,$sollStatus);
			      
			      if ( !$sollStatus )   // Ankunft an einem anderen Ort
			         {
			      	SetValue($geoAbID,false);

			         }
					else
			         {
			      	//SetValue($geoAbID,false);

			         }

			      
			      }
			   }
			}
		}
	   

	}
	
/***************************************************************************//**
*	Ausfuehren von Aktion bei Erreichen oder Verlassen
*  Returnwert
*              false -  Fehler
*                 1  -  keine Action
*                 2  -  Scriptausfuehrung OK
*                 3  -  Fehler bei Scriptausfuehrung
*******************************************************************************/
function GEOActions($GEOentry,$IPSName,$GEOname,$GeofencyPOST)
	{
	GLOBAL $ActionConfig;

	AutoLeaving($GEOentry,$IPSName,$GEOname,$GeofencyPOST);

	//***************************************************************************
	// POST Keys umbenennen
	//***************************************************************************
	$RunScriptArray = array();
	$keys = array_keys($GeofencyPOST);
	foreach ( $keys as $key )
			{
			$RunScriptArray['Geofency_' . $key] = $GeofencyPOST[$key];
			}
			

	$return = false;
	
	if ( $GEOentry == true )
	   $str = "Ankunft";
	else
	   $str = "Abfahrt";

	 $str = "GEOActions:" . $str . " - " . $IPSName . " - " . $GEOname;
    if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,$str);

	if ( !isset($ActionConfig) )
	   {
	   IPSLogger_Dbg(__FILE__,"Keine Actionconfig in der Konfiguration !!");
	   return $return;
	   }

	$return = 1;
	
	foreach ( $ActionConfig as $Action )
	   {
	   if ( ($Action[2] == $IPSName ) AND ( $Action[3] == $GEOname ) AND ( $Action[1] == true ) )
	      {
	   	if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Action gefunden Eintrag : " . $Action[0]);

			if ( $GEOentry == true )
				$ActionScriptID = $Action[4];
			else
				$ActionScriptID = $Action[5];
				
			if (IPS_ScriptExists($ActionScriptID))
			   {
	   		if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Run Actionscript : " . $ActionScriptID);

			   $ok = IPS_RunScriptEx($ActionScriptID,$RunScriptArray);
			   
			   if ( $ok )
					$return = 2;
				else
				   $return = 3;
			   }
			else
			   {
	   		if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Actionscript : " . $ActionScriptID . " nicht gefunden");
	   		$return = 3;
			   }

				
	      }
	      
	   }

	return $return;

	}
	

/***************************************************************************//**
*	Erstellen einer GoogleMap
*******************************************************************************/
function DoGoogleMaps($HTMLBoxID,$latitude,$longitude,$hoehe='100%',$breite='100%',$zoomlevel=14)
    {
    if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Create New Googlemap");

    $zoomlevel = GOOGLEMAPS_ZOOM ;

    $s  = "<iframe width='".$breite."' height='".$hoehe."' ";
    $s .= "src='https://maps.google.de/maps?hl=de";
    $s .= "&q=".$latitude.",".$longitude."&ie=UTF8&t=&z=".$zoomlevel;
    $s .= "&output=embed' frameborder='0' scrolling='no' ></iframe>";

    SetValue($HTMLBoxID,$s);

    }
    
/***************************************************************************//**
*	Erstellen einen OSMMap
*******************************************************************************/
function DoOSMMap($HTMLBoxID,$latitude,$longitude,$entry,$hoehe='100%',$breite='100%',$zoomlevel=14)
	{

	$zoomlevel = OSM_ZOOM ;

	if ($entry)
	   $entry = "1";
	else
	   $entry = "0";
	   
	$s  = "<iframe width='".$breite."' height='".$hoehe."' ";
	$s .= "src='./User/GeofencyInfo/osmTemplate.php";
	$s .= "?zoom=".$zoomlevel;
	$s .= "&lat=".$latitude."&lon=".$longitude."&entry=".$entry;
	$s .= "' frameborder='0' scrolling='no' ></iframe>";

	SetValue($HTMLBoxID,$s);

	}


/***************************************************************************//**
*	Logging
*******************************************************************************/
function Logging($Parent,$text,$file = 'geofency.log')
	{

	$ordner = IPS_GetKernelDir() . "logs\\Geofency";
   if ( !is_dir ( $ordner ) )
		mkdir($ordner);
	
   if ( !is_dir ( $ordner ) )
	   return;

	$time = date("d.m.Y H:i:s");
	$logdatei = IPS_GetKernelDir() . "logs\\Geofency\\" . $file;

	$zeilen = @file ($logdatei);

	$max_anzahl = 50;
	
	$anzahl = count($zeilen);

	if ( $anzahl > $max_anzahl )
		{
	   $zeilen = array_slice($zeilen,$anzahl-$max_anzahl);
	   }

	$datei = fopen($logdatei,"w");

	if ( $zeilen )
	foreach ($zeilen as $zeile)
		{
		fwrite($datei, $zeile );
		}

	fwrite($datei, $time ." ". trim($text)  ."\n" );

	fclose($datei);

	}


/***************************************************************************//**
*	HTML Logging
*******************************************************************************/
function HTMLlogging($Parent,$GEOentry,$GEOdevice,$GEOname,$GEOdate,$IPSName,$ActionOK)
	{

	$IconSize 	  = 30;
	$img_clock    = "/user/GeofencyInfo/images/uhr.png";
	$img_red      = "/user/GeofencyInfo/images/arrowred.png";
	$img_green    = "/user/GeofencyInfo/images/arrowgreen.png";
	$img_geofency = "/user/GeofencyInfo/images/geofency.png";
	$img_geoloc   = "/user/GeofencyInfo/images/geoloc.png";
	$img_empty    = "/user/GeofencyInfo/images/leer.png";
	$img_scriptok = "/user/GeofencyInfo/images/scriptok.png";
	$img_scriptnok= "/user/GeofencyInfo/images/scriptnok.png";

	$htmlText = "<table border = '0' width='100%' scrolling='no'>";
	$htmlText = $htmlText . "<colgroup>";

	$htmlText = $htmlText . "<col width='25'><col width='20%'><col width='25'><col width='10%'>";
	$htmlText = $htmlText . "<col width='25'><col width='17%'><col width='25'><col width='33%'>";
	$htmlText = $htmlText . "<col width='25'>";

	$htmlText = $htmlText . "</colgroup>";

	$htmlText = $htmlText . "<td><center><img src='".$img_clock."'  height='$IconSize' width='$IconSize' ></td>";

	$htmlText = $htmlText . "<td> " . date("d.m.Y H:i:s") . "</td>" ;

	if ( $GEOentry )
		$htmlText = $htmlText . "<td><center>" . "<img src='".$img_green."'  height='$IconSize' width='$IconSize' ></td><td> Ankunft</td>" ;
	else
		$htmlText = $htmlText . "<td><center>" . "<img src='".$img_red."'  height='$IconSize' width='$IconSize' ></td><td> Abfahrt</td>";

	$htmlText = $htmlText . "<td><center>" . "<img src='".$img_geofency."'  height='$IconSize' width='$IconSize' ></td><td> " . $IPSName. "</td>";

	$htmlText = $htmlText . "<td><center>" . "<img src='".$img_geoloc."'  height='$IconSize' width='$IconSize' ></td><td> " . $GEOname. "</td>";

	if( !$ActionOK OR $ActionOK == 1 )
	   $htmlText = $htmlText . "<td><center>" . "<img src='".$img_empty."'  height='$IconSize' width='$IconSize' ></td>";

	if( $ActionOK == 2 )
	   $htmlText = $htmlText . "<td><center>" . "<img src='".$img_scriptok."'  height='$IconSize' width='$IconSize' ></td>";
	if( $ActionOK == 3 )
	   $htmlText = $htmlText . "<td><center>" . "<img src='".$img_scriptnok."'  height='$IconSize' width='$IconSize' ></td>";


	$htmlText = $htmlText . "</table>";

	$IDlog  = CreateVariable('Log'  ,3,$Parent,99,"~HTMLBox");

	$s = GetValue($IDlog);
   $array = explode('</table>',$s);
   $max = (HTMLLOGLINES * 2)-2 ;
	
   $array = array_slice($array,0,$max);
   $s = '';
   foreach($array as $zeile )
   	{
      $s = $s . '</table>'. $zeile  ;
      }

   $s = $htmlText . $s ;

//	$s = "<table border = '0' width='100%' ><tr><td>" . $s;

//	$s = $s . "</td></tr></table>";


   SetValue($IDlog,$s);
	
	}
	

/***************************************************************************//**
*	HTMLwithMap
*******************************************************************************/
function CreateHTMLBoxWithMap($Parent,$IPSName,$ActionResult)
	{
	GLOBAL $debug;

	$img_geofency = "/user/GeofencyInfo/images/geofency.png";
	$img_red      = "/user/GeofencyInfo/images/arrowred.png";
	$img_green    = "/user/GeofencyInfo/images/arrowgreen.png";
	$img_empty    = "/user/GeofencyInfo/images/leer.png";
	$img_scriptok = "/user/GeofencyInfo/images/scriptok.png";
	$img_scriptnok= "/user/GeofencyInfo/images/scriptnok.png";

	$str = "CreateHTMLBoxWithMap" . $Parent . " - " . $IPSName;
   if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,$str);

	// suche Contentvariable
	$ContentId = @IPS_GetVariableIDByName($IPSName."Content",$Parent);

	if ( !$ContentId )
	   {
		IPSLogger_Dbg(__FILE__,'Keine ContentID gefunden');
		return;
	   }


	// suche letzte Ankunft
	$CategoryId = @IPS_GetCategoryIDByName($IPSName,$Parent);
	if ( !$CategoryId )
	   {
		IPSLogger_Dbg(__FILE__,'Keine $CategoryId gefunden');
		return;
	   }
	$array = IPS_GetChildrenIDs($CategoryId);

	$LastTimeAnwesend = 0 ;   // neuester Eintrag
	$IdAnwesend = false;
	$LastTimeAbwesend = 0 ;   // neuester Eintrag
	$IdAbwesend = false;
	foreach($array as $kategorie)
	   { 
		$childs = IPS_GetChildrenIDs($kategorie);
		if ( $childs )
			{ 
			$entry      = GetValue(IPS_GetVariableIDByName('Entry',$kategorie));
			if ( $entry )
			   {
				$geoAnkunft = GetValue(IPS_GetVariableIDByName('GEOAnkunft',$kategorie));

				if ( $geoAnkunft > $LastTimeAnwesend )
			   	{
					$LastTimeAnwesend = $geoAnkunft;
			   	$IdAnwesend = $kategorie;
			   	}
				}
			else
				{
				$geoAbfahrt = GetValue(IPS_GetVariableIDByName('GEOAbfahrt',$kategorie));

				if ( $geoAbfahrt > $LastTimeAbwesend )
			   	{
					$LastTimeAbwesend = $geoAbfahrt;
			   	$IdAbwesend = $kategorie;
			   	}
				   
				}


			}
	   }

	if ( $IdAnwesend == false )
		$Id = $IdAbwesend;
	else
		$Id = $IdAnwesend;


	$geoAnkunft = @GetValue(@IPS_GetVariableIDByName('GEOAnkunft',$Id));
	$geoAbfahrt = @GetValue(@IPS_GetVariableIDByName('GEOAbfahrt',$Id));
	$latitude   = @GetValue(@IPS_GetVariableIDByName('Latitude',$Id));
	$longitude  = @GetValue(@IPS_GetVariableIDByName('Longitude',$Id));
	$objectinfo = IPS_GetObject($Id);
	$IPSName = $objectinfo['ObjectName'];

	//***************************************************************************

	if ( $geoAnkunft )
   	$geoAnkunft = date("d.m.y H:i:s",$geoAnkunft);

	if ( $geoAbfahrt )
   	$geoAbfahrt = date("d.m.y H:i:s",$geoAbfahrt);

   $latitude   = round(floatval($latitude) ,5);
	$longitude  = round(floatval($longitude),5);

	// Create History-Array
	foreach($array as $kategorie)
	   {
		$childs = IPS_GetChildrenIDs($kategorie); 
		if ( $childs )
			{
			$entry      = GetValue(@IPS_GetVariableIDByName('Entry',$kategorie));
			if ( !$entry );
			   { 
				$geoAnkunftH = GetValue(@IPS_GetVariableIDByName('GEOAnkunft',$kategorie));
				$geoAbfahrtH = GetValue(@IPS_GetVariableIDByName('GEOAbfahrt',$kategorie));

				$objectinfo = IPS_GetObject($kategorie);
				$IPSNameH = $objectinfo['ObjectName'];
				
				if ( $geoAbfahrtH )
					$index = $geoAbfahrtH;
				else
					$index = $geoAnkunftH;
            
            $HistoryArray[$index] = array($IPSNameH,$geoAnkunftH,$geoAbfahrtH,);

				}
			}
		}

	$HistoryAnzahl = 6;
	if ( @defined ( 'HISTORYLINES' ) )
	   {
		$HistoryAnzahl = HISTORYLINES;
		}

	ksort($HistoryArray);
	$HistoryArray = array_reverse($HistoryArray);
	$HistoryArray = array_slice($HistoryArray,1,$HistoryAnzahl);
		
	// Create History-HTML
	$htmlHistory  = "";
	foreach ( $HistoryArray as $Location )
	   {	
		$htmlHistory .= "<p class='tdStyleHistory'>" . $Location[0] ."</p>" ;

		if ( $Location[1] )
			$Location[1] = date("d.m.y H:i:s",$Location[1]);
		else
		   $Location[1] = "";

		if ( $Location[2] )
			$Location[2] = date("d.m.y H:i:s",$Location[2]);
		else
		   $Location[2] = "";
		
		
		$htmlHistory = $htmlHistory . "<img class='imgGreenArrow' src='".$img_green."' height='20px' width='20px' align='ABSMIDDLE'>".$Location[1]."</br>";
		$htmlHistory = $htmlHistory . "<img class='imgRedArrow'   src='".$img_red."'   height='20px' width='20px' align='ABSMIDDLE'>".$Location[2]."";
		}

	// checke ob Script ausgefuehrt
	$img_script = $img_empty;
   if ( $ActionResult == false OR $ActionResult == 3 )
		$img_script = $img_scriptnok;
   if ( $ActionResult == 1 )
		$img_script = $img_empty;
   if ( $ActionResult == 2 )
		$img_script = $img_scriptok;

	
	//***************************************************************************
	// Get MAP
	//***************************************************************************
	$map = "Keine Karte in der Konfiguration definiert";
	
	if ( @defined ( 'GEOFENCYIPSMAP' ) )
	   {
		if ( GEOFENCYIPSMAP == 'GOOGLE' )
			$mapName = "GoogleMap";
		if ( GEOFENCYIPSMAP == 'OSM' )
			$mapName = "OSMMap";

		$map = GetValue(IPS_GetVariableIDByName($mapName,$CategoryId));

		}
		
	$MapHeight = 500;
	if ( @defined ( 'MAPHEIGHT' ) )
	   {
		$MapHeight = MAPHEIGHT;
		}

	//***************************************************************************
	// Create HTMLBox
	//***************************************************************************
	$htmlText = "<head><link rel='stylesheet' type='text/css' href='/user/GeofencyInfo/css/Geofency.css'>";
	$htmlText = $htmlText . "<style>overflow-x:auto</style>";
	$htmlText = $htmlText . "</head><body>";

	$htmlText = $htmlText . "<table border = '0' width='100%' scrolling='No'>";

	$htmlText = $htmlText . "<tr>";

	$htmlText = $htmlText . "<td class='tdStyle' width='40%'>";
	$htmlText = $htmlText . "<div class='divImgGeofency'>";
	$htmlText = $htmlText . "<img class='imgGeofency' src='".$img_geofency ."'";

	//$menu = $menu . "<img src='$file' ". $imggroesse ." onmouseover=\"this.style.cursor = 'pointer'\" ";
	//$menu = $menu . "onclick=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\" ";
	//$menu = $menu . "ontouchstart=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\">";

	$htmlText = $htmlText . " onmouseover=\"this.style.cursor = 'pointer'\" ";
	$htmlText = $htmlText . "onclick=\"new Image().src = '/user/GeofencyInfo/GeofencyWebCommand.php?Device=".$Parent."'; return false;\" ";
	$htmlText = $htmlText . "ontouchstart=\"new Image().src = '/user/GeofencyInfo/GeofencyWebCommand.php?Device=".$Parent."'; return false;\">";

	$htmlText = $htmlText . "</div>";


   if ( $geoAnkunft )
		{
		$htmlText = $htmlText . "<div class='divImgGreenArrow'>";
		$htmlText = $htmlText . "<img src='".$img_green. "' hspace=10 align='ABSMIDDLE'>";
		$htmlText = $htmlText . "".$geoAnkunft."";
		$htmlText = $htmlText . "<img  class='floatRight' src='".$img_script."' hspace=5 >";
		$htmlText = $htmlText . "</div>";
		}
	if ( $geoAbfahrt )
	   {
		$htmlText = $htmlText . "<div class='divImgRedArrow'>";
		$htmlText = $htmlText . "<img src='".$img_red   ."' hspace=10 align='ABSMIDDLE'>";
		$htmlText = $htmlText . "".$geoAbfahrt."";
		$htmlText = $htmlText . "<img  class='floatRight' src='".$img_script."' hspace=5 >";
		$htmlText = $htmlText . "</div></td>";
		}
		
	$htmlText = $htmlText . "<td class='tdStyleLocationInfo' width='60%' ><center>" . $IPSName . "" ;
	$htmlText = $htmlText . "<p  class='txtLocationInfo'>Latitude:" . $latitude . "  Longitude:".$longitude."</p></td>" ;

	$htmlText = $htmlText . "</tr>";
	$htmlText = $htmlText . "</table>";

	$htmlText = $htmlText . "<table border = '0' width='100%' scrolling='No'>";
	
	$htmlText = $htmlText . "<tr>";
	$htmlText = $htmlText . "<td class='tdStyle' valign='top'  width='200' height='400'> " ;

	$htmlText = $htmlText . "" . $htmlHistory . "</td>" ;

	$htmlText = $htmlText . "<td class='tdStyle' height='".$MapHeight."'> " . $map . "</td>" ;

	$htmlText = $htmlText . "</tr>";
	$htmlText = $htmlText . "</table>";
	$htmlText = $htmlText . "</body>";

   //$htmlText = "<iframe>".$htmlText ."</iframe>";
	SetValueString($ContentId,$htmlText);

	}


/***************************************************************************//**
*	Refresh HTMLwithMap
*******************************************************************************/
function RefreshHTMLBoxWithMap($Device,$Switch=false)
	{

	$img_geofency = "/user/GeofencyInfo/images/geofency.png";
	$img_red      = "/user/GeofencyInfo/images/arrowred.png";
	$img_green    = "/user/GeofencyInfo/images/arrowgreen.png";
	$img_empty    = "/user/GeofencyInfo/images/leer.png";
	$img_scriptok = "/user/GeofencyInfo/images/scriptok.png";
	$img_scriptnok= "/user/GeofencyInfo/images/scriptnok.png";


	$str = "RefreshHTMLBoxWithMap : " . $Device;
   if ( DEBUG_MODE ); IPSLogger_Dbg(__FILE__,$str);

	// suche Contentvariable
   $ParentID = @IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.GeofencyInfo", true );
	$ContentId = @IPS_GetVariableIDByName($Device."Content",$ParentID);

	if ( !$ContentId )
	   {
		IPSLogger_Dbg(__FILE__,'Keine ContentID gefunden');
		return;
	   }

	$CategoryId = @IPS_GetCategoryIDByName($Device,$ParentID);
	if ( !$CategoryId )
	   {
		IPSLogger_Dbg(__FILE__,'Keine $CategoryId gefunden');
		return;
	   }


	//***************************************************************************
	// Menu Umschaltung durch Webfront
	//***************************************************************************
  	$IDMenuMode = CreateVariable('Mode',1,$CategoryId,3,'',false,1);
	$Mode = GetValue($IDMenuMode);
	if ( $Switch )
	   {
		IPSLogger_Dbg(__FILE__,'Switch');

		$MaxMode = 2;
		$Mode = $Mode + 1 ;

		if ( $Mode < 1 )
		   $Mode = 1;
		if ( $Mode > $MaxMode )
		   $Mode = 1;

		SetValue($IDMenuMode,$Mode);
		}
	$htmlHistory = "Wrong MenuMode";

   if ( $Mode == 1 )
		$htmlHistory = HtmlHistoryLeft($Device,$CategoryId);
   if ( $Mode == 2 )
		$htmlHistory = HtmlLogLeft($Device);





	$AnkunftID = false;
	//***************************************************************************
	// suche letzte Ankunft / Abfahrt
	//***************************************************************************
	$LastTimeAnwesend = 0 ;   
	$IdAnwesend = false;
	$LastTimeAbwesend = 0 ;   
	$IdAbwesend = false;

	$array = IPS_GetChildrenIDs($CategoryId);
	foreach ( $array as $Location )
	   {
		//IPSLogger_Dbg(__FILE__,$Location);
      $childs = IPS_GetChildrenIDs($Location);
		if ( $childs )
			{
			$entry = GetValue(IPS_GetVariableIDByName('Entry',$Location));
			// Anwesend ?
			if ( $entry )
			   {
				$geoAnkunft = GetValue(IPS_GetVariableIDByName('GEOAnkunft',$Location));
				if ( $geoAnkunft > $LastTimeAnwesend )
			   	{
					$LastTimeAnwesend = $geoAnkunft;
			   	$IdAnwesend = $Location;
			   	}
			   }
			// Abwesend
			if ( !$entry )
			   {
				$geoAbfahrt = GetValue(IPS_GetVariableIDByName('GEOAbfahrt',$Location));
				if ( $geoAbfahrt > $LastTimeAbwesend )
			   	{
					$LastTimeAbwesend = $geoAbfahrt;
			   	$IdAbwesend = $Location;
			   	}

			   }

			}
	   
	   }


	$geoAnkunft  = "???";
	$geoAbfahrt  = "???";
	$latitude    = "???";
	$longitude   = "???";
	$LocName 	 = "???";
	$GEOentry   = false;
	
	$img_script_ankunft = $img_empty;
	$img_script_abfahrt = $img_empty;

	// Bin ich irgendwo ? Neueste Ankunft
	if ( $IdAnwesend == true )
	   {
	   $ID = $IdAnwesend;
		IPSLogger_Dbg(__FILE__,$ID);
		$geoAnkunft = GetValue(IPS_GetVariableIDByName('GEOAnkunft',$ID));
		$geoAnkunft = date("d.m.y H:i:s",$geoAnkunft);
		$geoAbfahrt = GetValue(IPS_GetVariableIDByName('GEOAbfahrt',$ID));
		$geoAbfahrt = date("d.m.y H:i:s",$geoAbfahrt);
		$objectinfo = IPS_GetObject($ID);
		$LocName    = $objectinfo['ObjectName'];
		$latitude   = GetValue(IPS_GetVariableIDByName('Latitude',$ID));
		$longitude  = GetValue(IPS_GetVariableIDByName('Longitude',$ID));
		$latitude   = round(floatval($latitude),5);
		$longitude  = round(floatval($longitude),5);
		$GEOentry = true;

		$action     = @GetValue(@IPS_GetVariableIDByName('Action',$ID));
		if ( $action )
		   {
		   $array = explode(",",$action);
		   if ( isset($array[0]) )
		      {
		      if ( $array[0] == 2 )
					$img_script_ankunft = "/user/GeofencyInfo/images/scriptok.png";
		      if ( $array[0] == 3 )
					$img_script_ankunft = "/user/GeofencyInfo/images/scriptnok.png";
				}

		   }
		   
		$geoAbfahrt = " ";
	   }

	// Bin ich nirgendwo ? Neueste Abfahrt
	if ( $IdAnwesend == false and $IdAbwesend == true )
	   {
	   $ID = $IdAbwesend;
		IPSLogger_Dbg(__FILE__,$ID);
		$geoAnkunft = GetValue(IPS_GetVariableIDByName('GEOAnkunft',$ID));
		$geoAnkunft = date("d.m.y H:i:s",$geoAnkunft);
		$geoAbfahrt = GetValue(IPS_GetVariableIDByName('GEOAbfahrt',$ID));
		$geoAbfahrt = date("d.m.y H:i:s",$geoAbfahrt);
		$objectinfo = IPS_GetObject($ID);
		$LocName    = $objectinfo['ObjectName'];
		$latitude   = GetValue(IPS_GetVariableIDByName('Latitude',$ID));
		$longitude  = GetValue(IPS_GetVariableIDByName('Longitude',$ID));
		$latitude   = round(floatval($latitude),5);
		$longitude  = round(floatval($longitude),5);

		$action     = @GetValue(@IPS_GetVariableIDByName('Action',$ID));
		if ( $action )
		   {
		   $array = explode(",",$action);
		   if ( isset($array[0]) )
		      {
		      if ( $array[0] == 2 )
					$img_script_ankunft = "/user/GeofencyInfo/images/scriptok.png";
		      if ( $array[0] == 3 )
					$img_script_ankunft = "/user/GeofencyInfo/images/scriptnok.png";
				}
		   if ( isset($array[1]) )
		      {
		      if ( $array[1] == 2 )
					$img_script_abfahrt = "/user/GeofencyInfo/images/scriptok.png";
		      if ( $array[1] == 3 )
					$img_script_abfahrt = "/user/GeofencyInfo/images/scriptnok.png";
				}

		   }


	   }


	//***************************************************************************
	// Create MAPs
	//***************************************************************************
	$DeviceID = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.GeofencyInfo.".$Device);
  	$HTMLBoxID = CreateVariable('GoogleMap'  ,3,$DeviceID,99,'~HTMLBox');
  	DoGoogleMaps($HTMLBoxID,trim($latitude),trim($longitude));

	$HTMLBoxID = CreateVariable('OSMMap'  ,3,$DeviceID,99,'~HTMLBox');
  	DoOSMMap($HTMLBoxID,trim($latitude),trim($longitude),$GEOentry);



	//***************************************************************************
	// Get MAP
	//***************************************************************************
	$map = "Keine Karte in der Konfiguration definiert";

	if ( @defined ( 'GEOFENCYIPSMAP' ) )
	   {
		if ( GEOFENCYIPSMAP == 'GOOGLE' )
			$mapName = "GoogleMap";
		if ( GEOFENCYIPSMAP == 'OSM' )
			$mapName = "OSMMap";

		$map = GetValue(IPS_GetVariableIDByName($mapName,$CategoryId));

		}

	$MapHeight = 500;
	if ( @defined ( 'MAPHEIGHT' ) )
	   {
		$MapHeight = MAPHEIGHT;
		}

	//***************************************************************************
	// Create HTMLBox
	//***************************************************************************
	$htmlText = "<head><link rel='stylesheet' type='text/css' href='/user/GeofencyInfo/css/Geofency.css'>";
	$htmlText = $htmlText . "<style>overflow-x:auto</style>";
	$htmlText = $htmlText . "</head><body>";

	$htmlText = $htmlText . "<table border = '0' width='100%' scrolling='No'>";

	$htmlText = $htmlText . "<tr>";

	$htmlText = $htmlText . "<td class='tdStyle' width='40%'>";
	$htmlText = $htmlText . "<div class='divImgGeofency'>";
	$htmlText = $htmlText . "<img class='imgGeofency' src='".$img_geofency ."'";

	//$menu = $menu . "<img src='$file' ". $imggroesse ." onmouseover=\"this.style.cursor = 'pointer'\" ";
	//$menu = $menu . "onclick=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\" ";
	//$menu = $menu . "ontouchstart=\"new Image().src = '/user/Plugwise/PlugwiseWebMenuController.php?Button=$menuitem[3] '; return false;\">";

	$htmlText = $htmlText . " onmouseover=\"this.style.cursor = 'pointer'\" ";
	$htmlText = $htmlText . "onclick=\"new Image().src = '/user/GeofencyInfo/GeofencyWebCommand.php?Device=".$Device."'; return false;\" ";
	$htmlText = $htmlText . "ontouchstart=\"new Image().src = '/user/GeofencyInfo/GeofencyWebCommand.php?Device=".$Device."'; return false;\">";

	$htmlText = $htmlText . "</div>";


	$htmlText = $htmlText . "<div class='divImgGreenArrow'>";
	$htmlText = $htmlText . "<img src='".$img_green. "' hspace=10 align='ABSMIDDLE'>";
	$htmlText = $htmlText . "".$geoAnkunft."";
	$htmlText = $htmlText . "<img  class='floatRight' src='".$img_script_ankunft."' hspace=5 >";
	$htmlText = $htmlText . "</div>";

	$htmlText = $htmlText . "<div class='divImgRedArrow'>";
	$htmlText = $htmlText . "<img src='".$img_red   ."' hspace=10 align='ABSMIDDLE'>";
	$htmlText = $htmlText . "".$geoAbfahrt."";
	$htmlText = $htmlText . "<img  class='floatRight' src='".$img_script_abfahrt."' hspace=5 >";
	$htmlText = $htmlText . "</div></td>";

	$htmlText = $htmlText . "<td class='tdStyleLocationInfo' width='60%' ><center>" . $LocName . "" ;
	$htmlText = $htmlText . "<p  class='txtLocationInfo'>Latitude:" . $latitude . "  Longitude:".$longitude."</p></td>" ;

	$htmlText = $htmlText . "</tr>";
	$htmlText = $htmlText . "</table>";

	$htmlText = $htmlText . "<table border = '0' width='100%' scrolling='No'>";

	$htmlText = $htmlText . "<tr>";
	$htmlText = $htmlText . "<td class='tdStyle' valign='top'  width='200' height='400'> " ;

	$htmlText = $htmlText . "" . $htmlHistory . "</td>" ;

	$htmlText = $htmlText . "<td class='tdStyle' height='".$MapHeight."'> " . $map . "</td>" ;

	$htmlText = $htmlText . "</tr>";
	$htmlText = $htmlText . "</table>";
	$htmlText = $htmlText . "</body>";

	SetValueString($ContentId,$htmlText);


	}

/***************************************************************************//**
*	LocationHistory Left
*******************************************************************************/
function HtmlHistoryLeft($Device,$CategoryId)
	{
	$img_red      = "/user/GeofencyInfo/images/arrowred.png";
	$img_green    = "/user/GeofencyInfo/images/arrowgreen.png";
	$img_empty    = "/user/GeofencyInfo/images/leer.png";
	$img_scriptok = "/user/GeofencyInfo/images/scriptok.png";
	$img_scriptnok= "/user/GeofencyInfo/images/scriptnok.png";

	$html = "";
	
	$img_script = $img_empty;
	$img_scriptankunft = $img_empty;
	$img_scriptabfahrt = $img_empty;

	$array = IPS_GetChildrenIDs($CategoryId);



	// Create History-Array
	foreach($array as $kategorie)
	   {
		$childs = IPS_GetChildrenIDs($kategorie);
		if ( $childs )
			{
			$entry      = GetValue(@IPS_GetVariableIDByName('Entry',$kategorie));
			if ( !$entry );
			   {
				$geoAnkunftH = GetValue(@IPS_GetVariableIDByName('GEOAnkunft',$kategorie));
				$geoAbfahrtH = GetValue(@IPS_GetVariableIDByName('GEOAbfahrt',$kategorie));

				$objectinfo = IPS_GetObject($kategorie);
				$IPSNameH = $objectinfo['ObjectName'];

				if ( $geoAbfahrtH )
					$index = $geoAbfahrtH;
				else
					$index = $geoAnkunftH;

            $HistoryArray[$index] = array($IPSNameH,$geoAnkunftH,$geoAbfahrtH,);

				}
			}
		}

	$HistoryAnzahl = 6;
	if ( @defined ( 'HISTORYLINES' ) )
	   {
		$HistoryAnzahl = HISTORYLINES;
		}

	ksort($HistoryArray);
	$HistoryArray = array_reverse($HistoryArray);
	$HistoryArray = array_slice($HistoryArray,1,$HistoryAnzahl);

	// Create History-HTML
	$htmlHistory  = "";
	foreach ( $HistoryArray as $Location )
	   {
		$htmlHistory .= "<p class='tdStyleHistory'>" . $Location[0] ."</p>" ;

		if ( $Location[1] )
			$Location[1] = date("d.m.y H:i:s",$Location[1]);
		else
		   $Location[1] = "";

		if ( $Location[2] )
			$Location[2] = date("d.m.y H:i:s",$Location[2]);
		else
		   $Location[2] = "";


		$htmlHistory = $htmlHistory . "<img class='imgGreenArrow' src='".$img_green."' height='20px' width='20px' align='ABSMIDDLE'> ".$Location[1];
		if ( $Location[1] )
			$htmlHistory = $htmlHistory . " <img class='imgGreenArrow' src='".$img_scriptankunft."' height='20px' width='20px' align='ABSMIDDLE'> ";

		$htmlHistory = $htmlHistory . "</br>";

		$htmlHistory = $htmlHistory . "<img class='imgRedArrow'   src='".$img_red."'   height='20px' width='20px' align='ABSMIDDLE'> ".$Location[2]."";
		if ( $Location[2] )
			$htmlHistory = $htmlHistory . " <img class='imgGreenArrow' src='".$img_scriptabfahrt."' height='20px' width='20px' align='ABSMIDDLE'> ";

		}








	return $htmlHistory;
	}

/***************************************************************************//**
*	LogHistory Left
*******************************************************************************/
function HtmlLogLeft($Device)
	{
	$img_red      = "/user/GeofencyInfo/images/arrowred.png";
	$img_green    = "/user/GeofencyInfo/images/arrowgreen.png";
	$img_empty    = "/user/GeofencyInfo/images/leer.png";
	$img_scriptok = "/user/GeofencyInfo/images/scriptok.png";
	$img_scriptnok= "/user/GeofencyInfo/images/scriptnok.png";

	$img_script = $img_empty;

	$html = "";
	
   $ordner = IPS_GetKernelDir() . "logs\\Geofency";

   if ( !is_dir ( $ordner ) )
	   return;

	$logdatei = IPS_GetKernelDir() . "logs\\Geofency\\Device_" . $Device.".log";

	$zeilen = @file ($logdatei);
	$zeilen = @array_reverse($zeilen);
	
	$anzahl = count($zeilen);

	$HistoryAnzahl = 6;
	if ( @defined ( 'HISTORYLINES' ) )
	   {
		$HistoryAnzahl = HISTORYLINES;
		}
   $HistoryAnzahl = $HistoryAnzahl + ( $HistoryAnzahl / 2 );


   $HistoryArray = array();
	for ( $x=0;$x<=$HistoryAnzahl;$x++)
	   {
	   $text = "";
		if ( isset($zeilen[$x]) )
		   {
		   $text = "";
			$array = explode(";",$zeilen[$x]);

			if ( isset($array[2] ))
			   $loc = trim($array[2]);

			$text .= "<p class='tdStyleHistory'>" . $loc ."</p>" ;

			
			if ( isset($array[3] ))
			   {
			   if ( trim($array[3] == "Abfahrt") )
					$text = $text . "<img class='imgGreenArrow' src='".$img_red."' height='20px' width='20px' align='ABSMIDDLE'> ";
			   if ( trim($array[3] == "Ankunft") )
					$text = $text . "<img class='imgGreenArrow' src='".$img_green."' height='20px' width='20px' align='ABSMIDDLE'> ";

				}
				
			if ( isset($array[0] ))
			   $text = $text . trim($array[0]);

			if ( isset($array[4] ))
			   {
			   $ActionResult = trim($array[4]);
				if ( $ActionResult == 2 )
					$img_script = $img_scriptok;
				if ( $ActionResult == 3 )
					$img_script = $img_scriptnok;

				$text = $text . " <img class='imgGreenArrow' src='".$img_script."' height='20px' width='20px' align='ABSMIDDLE'>";

				}
				
		   }

		$html = $html . $text;
		}
		
	return $html;
	}

	
?>
