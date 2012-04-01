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
	IPSUtils_Include ("BusBahnInfo_Configuration.inc.php","IPSLibrary::config::modules::Informationen::BusBahnInfo");
  	$VisuData 	  = IPSUtil_ObjectIDByPath("Visualization.WebFront.Informationen.BusBahnInfo");

	If ( $_IPS['SENDER'] == "WebFront" ) 
		{
		$value = $_IPS['VALUE'];
	
		SetValueInteger($_IPS['VARIABLE'],$value);

		$object = IPS_GetObject($_IPS['VARIABLE']);
		}
	else
	   {
	   $name_station = "";
	   }

	if ( isset($object) )
	if ( $object['ObjectName'] == 'Bahnhof/Station' )
	   {
		$stations_valve = $value;
   	$profil = (IPS_GetVariableProfile("BusBahnInfo_Stationen"));
		$profil = $profil['Associations'];
	
	   $name = "";
		foreach( $profil as $profileintrag )
	   	{

	   	$val  = $profileintrag['Value'];
			if ( $val == $value )
	   		$name = $profileintrag['Name'];

	   	}
		$anzeigetafeln = array();
		// Tafeln aus Konfiguration holen
		foreach( $stationen as $station )
		   {
		   if ( $station[1] == $name )
		   	array_push($anzeigetafeln,$station[0]." - ".$station[2]);
		   }

   	$profil = (IPS_GetVariableProfile("BusBahnInfo_Anzeigetafeln"));
		$profil = $profil['Associations'];
		$anzahl = count($profil);

		for($x=0;$x<$anzahl;$x++) // Alle Tafeln erstmal loeschen
  	   	IPS_SetVariableProfileAssociation("BusBahnInfo_Anzeigetafeln", $x,"", "", 0xaaaaaa);

		$id = 0;
		foreach( $anzeigetafeln as $tafel )
  	   	{
  	   	IPS_SetVariableProfileAssociation("BusBahnInfo_Anzeigetafeln", $id,$tafel, "", 0xaaaaaa);
  			$id++;
			}
			
  		SetValueInteger(IPS_GetVariableIDByName('Anzeigetafel',$VisuData),0);
		}


	$profil = (IPS_GetVariableProfile("BusBahnInfo_Stationen"));
	$profil = $profil['Associations'];

	$name_station = "";
	foreach( $profil as $profileintrag )
			{
	   	$val  = $profileintrag['Value'];
			if ( $val == GetValueInteger(IPS_GetVariableIDByName('Bahnhof/Station',$VisuData)) )
	   		$name_station = $profileintrag['Name'];
	   	}
	   	
	$profil = (IPS_GetVariableProfile("BusBahnInfo_Anzeigetafeln"));
	$profil = $profil['Associations'];

	$name_anzeigetafel = "";
	foreach( $profil as $profileintrag )
			{
	   	$val  = $profileintrag['Value'];
			if ( $val == GetValueInteger(IPS_GetVariableIDByName('Anzeigetafel',$VisuData)) )
	   		$name_anzeigetafel = $profileintrag['Name'];
	   	}

   $ident = $name_station." - ".$name_anzeigetafel;
   
	//echo "----".$ident."----";


	
  	foreach(IPS_GetChildrenIDs($VisuData) as $objectID)
		{
    	$info = IPS_GetObject($objectID);

    	if ($info['ObjectType'] == 6)
			{
      	$name = IPS_GetName($objectID);
      	//echo "--" .$name;
      	if ($name == $ident)
				{
        		//$flag = "ANZEIGEN";
        		IPS_SetHidden($objectID, false);
      		}
			else
				{
        		//$flag = "AUSBLENDEN";
        		IPS_SetHidden($objectID, true);
      		}
    		}
		}

/***************************************************************************//**
* @}
*******************************************************************************/

?>
