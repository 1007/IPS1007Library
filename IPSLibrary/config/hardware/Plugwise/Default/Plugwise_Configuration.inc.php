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
	define ( 'DEBUG_MODE' , FALSE );
	define ( 'LOG_MODE'   , FALSE );

//******************************************************************************
// Comport Plugwise-Stick
//******************************************************************************

	define ( 'COMPORT' , 'COM6' );       // COM-Port des Sticks	
	define ( 'REFRESH_TIME',1);         // Refreshzeit in Minuten
	define ( 'CALIBRATION_TIME',3);     // Uhrzeit fuer Recalibration + Uhrzeit checken
	define ( 'WAIT_TIME',200);          // Wartezeit in Millisekunden nachdem ein Telegramm gesendet wurde

    
	GLOBAL $CircleGroups;
	GLOBAL $Stromtarife;
      
	$CircleGroups = array(
	//*******************************************************************************************************************************
	//		    CircleID			   Name 		Gruppe       Ein/Aus   Watt     kWh  Tarifgruppe           in Gesamt
	//*******************************************************************************************************************************
	array("000D6F0000B81B6E","Plasma TV","Wohnzimmer"		,"1","500"	,"5" 	, "Tarifgruppe Tag/Nacht" , 1 ),
	array("000D6F0000C3B1DA","Server"	,"Arbeitszimmer"	,"0","200"	,"" 	, "Tarifgruppe Tag/Nacht" , 1 ), 

	// Standardtarifgruppe
	array(""						,""			,""					,"0",""		,""	, "Tarifgruppe Tag/Nacht"),
  );

	$ExterneGroups = array(
	//*************************************************************************************
	//		   Name 		       Gruppe    ID-Leistung ID-KWh     Watt     kWh  Tarifgruppe              in Gesamt
	//*************************************************************************************
	array("Hauptzaehler" ,"SYSTEM_MAIN"	,"28466"	, "28466"   ,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),
	array("Sonstige"		,"SYSTEM_REST"	, false	, false		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),

	array("Nebenzaehler1","Keller"		,"28466" ,"28466"		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),
	array("Nebenzaehler2","Keller"		,"28466" ,"28466"		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),

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
	// Archivehandling
  // AggregationType setzen ( 0 = Standard , 1 = Zaehler ) 
	//***************************************************************************
	define ( 'AGGTYPE' ,1 ) ;
  define ( 'ARCHIVLOGGING' , true);


  //***************************************************************************
	// Highcharts
	//***************************************************************************
	define ( 'HIGHCHARTS' , true ) ;
	define ( 'HIGHCHARTS_ZEITRAUM' , 24 ) ;   // Zeitraum fuer Graph in Stunden

  //***************************************************************************
	// Externe Stromdaten ( zB EKM )
	//***************************************************************************
  define ( 'ID_GESAMTVERBRAUCH',0); // VariablenID des Gesamtverbrauchs
  define ( 'ID_LEISTUNG',0);        // VariablenID der aktuellen Leistung

  	//***************************************************************************
	// MySql Anbindung
	//***************************************************************************
	define ( 'MYSQL_ANBINDUNG' , 			true );
	define ( 'MYSQL_SERVER' , 				'121.11.58.34' );
	define ( 'MYSQL_USER' , 				'root' );
	define ( 'MYSQL_PASSWORD' , 			'k7pmde' );
	define ( 'MYSQL_DATENBANK' , 			'Plugwise' );
	define ( 'MYSQL_TABELLE_LEISTUNG' , 'Leistung' );
	define ( 'MYSQL_TABELLE_GESAMT' , 	'Gesamtverbrauch' );


  define ( 'AUTOCREATECIRCLE',false);  
  
  

?>
  