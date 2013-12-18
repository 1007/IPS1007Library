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
function DoGoogleMaps($HTMLBoxID,$latitude,$longitude,$hoehe=300,$breite=600,$zoomlevel=14)
    {

    $s  = "<iframe width='".$breite."' height='".$hoehe."' ";
    $s .= "src='http://maps.google.de/maps?hl=de";
    $s .= "&q=".$latitude.",".$longitude."&ie=UTF8&t=&z=".$zoomlevel;
    $s .= "&output=embed' frameborder='0' scrolling='no' ></iframe>";

    SetValue($HTMLBoxID,$s);

    }
    
/***************************************************************************//**
*	Erstellen einen OSMMap
*******************************************************************************/
function DoOSMMap($HTMLBoxID,$hoehe=300,$breite=600)
    {

    $s  = "<iframe width='".$breite."' height='".$hoehe."' ";
    $s .= "src='./User/Geofency/openstreetmap.php'";
    $s .= " frameborder='0' scrolling='no' ></iframe>";

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

	$htmlText = "<table border = '0' width='100%' >";
	$htmlText = $htmlText . "<colgroup>";

	$htmlText = $htmlText . "<col width='25'><col width='20%'><col width='25'><col width='15%'>";
	$htmlText = $htmlText . "<col width='25'><col width='20%'><col width='25'><col width='20%'>";
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
   $array = array_slice($array,0,18);
   $s = '';
   foreach($array as $zeile )
   	{
      $s = $s . '</table>'. $zeile  ;
      }

   $s = $htmlText . $s ;

   SetValue($IDlog,$s);
	
	}
	

?>
