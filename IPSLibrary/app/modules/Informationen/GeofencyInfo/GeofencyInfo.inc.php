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
*	Ausfuehren von Aktion bei Erreichen oder Verlassen
*  Returnwert
*              false -  Fehler
*                 1  -  keine Action
*                 2  -  Scriptausfuehrung OK
*                 3  -  Fehler bei Scriptausfuehrung
*******************************************************************************/
function GEOActions($GEOentry,$IPSName,$GEOname)
	{
	GLOBAL $ActionConfig;

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

			   $ok = IPS_RunScript($ActionScriptID);
			   
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
    $s .= "src='http://maps.google.de/maps?hl=de";
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
	$datei = fopen($logdatei,"a+");
	fwrite($datei, $time .": ". $text . chr(13));
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
*	HTML Logging
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

	IPSLogger_Dbg(__FILE__,$Id);
	$geoAnkunft = GetValue(@IPS_GetVariableIDByName('GEOAnkunft',$Id));
	$geoAbfahrt = GetValue(@IPS_GetVariableIDByName('GEOAbfahrt',$Id));
	$latitude   = GetValue(@IPS_GetVariableIDByName('Latitude',$Id));
	$longitude  = GetValue(@IPS_GetVariableIDByName('Longitude',$Id));
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

	
	// Create MAP
	
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


	$htmlText = "<head><link rel='stylesheet' type='text/css' href='/user/GeofencyInfo/css/Geofency.css'>";
	$htmlText = $htmlText . "<style>overflow-x:auto</style>";
	$htmlText = $htmlText . "</head><body>";

	$htmlText = $htmlText . "<table border = '0' width='100%' scrolling='No'>";

	$htmlText = $htmlText . "<tr>";

	$htmlText = $htmlText . "<td class='tdStyle' width='40%'>";
	$htmlText = $htmlText . "<div class='divImgGeofency'>";
	$htmlText = $htmlText . "<img class='imgGeofency' src='".$img_geofency."'></div>";

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

?>