<?php


/***************************************************************************//**
*	Ausfuehren von Aktion bei Erreichen oder Verlassen
*******************************************************************************/
function GEOActions($GEOentry,$IPSName,$GEOname)
	{
	GLOBAL $ActionConfig;
	
	if ( $GEOentry == true )
	   $str = "Ankunft";
	else
	   $str = "Abfahrt";

	 $str = "GEOActions:" . $str . " - " . $IPSName . " - " . $GEOname;
    if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,$str);

	if ( !isset($ActionConfig) )
	   {
	   IPSLogger_Dbg(__FILE__,"Keine Actionconfig in der Konfiguration !!");
	   return false;
	   }

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

			   IPS_RunScript($ActionScriptID);
			   }
				
	      }
	      
	   }
	
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
function logging($Parent,$text,$file = 'geofency.log')
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

  if ( $Parent )
    {
    $htmlText = $text ."<br>";
    $IDlog  = CreateVariable('Log'  ,3,$Parent,99);

    $s = GetValue($IDlog);
    $s = $s . $htmlText;
  
    SetValue($IDlog,$s);
    }
      
	}

?>
