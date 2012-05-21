<?php
/**@defgroup plugwise_configuration Plugwise Konfiguration
* @ingroup plugwise
* @{
*
* Konfigurations File fuer Plugwise.
*
* @file Plugwise_Configuration.inc.php
* @author 
* @version
* Version 0.6, 5.05.2012<br/>
*
*/

//******************************************************************************
// Debug und Logging
//******************************************************************************
	define ( 'DEBUG_MODE' , TRUE );
	define ( 'LOG_MODE'   , TRUE );

//******************************************************************************
// Comport Plugwise-Stick
//******************************************************************************

	define ( 'COMPORT' , 'COM6' );       // COM-Port des Sticks	
	define ( 'REFRESH_TIME',1);         // Refreshzeit in Minuten
	define ( 'CALIBRATION_TIME',3);     // Uhrzeit fuer Recalibration + Uhrzeit checken
  
	GLOBAL $CircleGroups;
	GLOBAL $Stromtarife;
      
	$CircleGroups = array(
	//*************************************************************************************
	//		    CircleID			   Name 		Gruppe       Ein/Aus   Watt     kWh  Tarifgruppe
	//*************************************************************************************
	array("000D6F0000B81B6E","Plasma TV","Wohnzimmer"		,"1","500"	,"5" 	, "Tarifgruppe Tag/Nacht"),
	array("000D6F0000C3B1DA","Server"	,"Arbeitszimmer"	,"0","200"	,"" 	, "Tarifgruppe Tag/Nacht"),

	// Standardtarifgruppe
	array(""						,""			,""					,"0",""		,""	, "Tarifgruppe Tag/Nacht"),
  );


	//***************************************************************************
	// Stromtarife und Gruppen immer beginnen um 00:00 Uhr
	//***************************************************************************
	$Stromtarife = array(
		array("01.06.2011","31.05.2012","Tarifgruppe Normal"		,"Normaltarif"	,"00:00","23:59","26,05"),
		array("01.06.2011","31.05.2012","Tarifgruppe Tag/Nacht"	,"Nachttarif"	,"00:00","06:29","18,78"),
		array("01.06.2011","31.05.2012","Tarifgruppe Tag/Nacht"	,"Tagtarif"  	,"06:30","22:29","26,05"),
		array("01.06.2011","31.05.2012","Tarifgruppe Tag/Nacht"	,"Nachttarif"	,"22:30","23:59","18,78"),

		array("01.06.2012","31.05.2013","Tarifgruppe Normal"		,"Normaltarif"	,"00:00","23:59","26,05"),
		array("01.06.2012","31.05.2013","Tarifgruppe Tag/Nacht"	,"Nachttarif"	,"00:00","06:29","18,78"),
		array("01.06.2012","31.05.2013","Tarifgruppe Tag/Nacht"	,"Tagtarif"  	,"06:30","22:29","26,05"),
		array("01.06.2012","31.05.2013","Tarifgruppe Tag/Nacht"	,"Nachttarif"	,"22:30","23:59","18,78"),

				);



  	//***************************************************************************
	// Highcharts
	//***************************************************************************
	define ( 'HIGHCHARTS' , true ) ;

  //***************************************************************************
	// Externe Stromdaten ( zB EKM )
	//***************************************************************************
  define ( 'ID_GESAMTVERBRAUCH',20244); // VariablenID des Gesamtverbrauchs
  define ( 'ID_LEISTUNG',45750);        // VariablenID der aktuellen Leistung

  define ( 'AUTOCREATECIRCLE',false);  

?>
  