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

  
  GLOBAL $CircleGroups;
	GLOBAL $Stromtarife;
  GLOBAL $SystemStromzaehlerGroups;
  GLOBAL $ExterneStromzaehlerGroups;

//******************************************************************************
// Debug und Logging
//******************************************************************************
	define ( 'DEBUG_MODE' , FALSE );
	define ( 'LOG_MODE'   , FALSE );

//******************************************************************************
// Comport Plugwise-Stick
//******************************************************************************

	define ( 'COMPORT' , 'COM4' );       // COM-Port des Sticks	
	define ( 'REFRESH_TIME',1);         // Refreshzeit in Minuten
	define ( 'CALIBRATION_TIME',3);     // Uhrzeit fuer Recalibration + Uhrzeit checken
	define ( 'WAIT_TIME',200);          // Wartezeit in Millisekunden nachdem ein Telegramm gesendet wurde

  define ( 'AUTOCREATECIRCLE',false);    
	

      
	$CircleGroups = array(
	//*******************************************************************************************************************************
	//		    CircleID			   Name 		Gruppe       Ein/Aus   Watt     kWh  Tarifgruppe           in Gesamt
	//*******************************************************************************************************************************
	array("000D6F0000B81B6E","Verbraucher1","Raum1"	,"1","500"	,"5" 	, "Tarifgruppe Tag/Nacht" , true ),
	array("000D6F0000C3B1DA","Verbraucher2","Raum1"	,"0","200"	,"" 	, "Tarifgruppe Tag/Nacht" , true ), 
	array("000D6F0000B81B7E","Verbraucher3","Raum2"	,"1","500"	,"5" 	, "Tarifgruppe Tag/Nacht" , true ),
	array("000D6F0000C3B18A","Verbraucher4","Raum2"	,"0","200"	,"" 	, "Tarifgruppe Tag/Nacht" , true ), 

	// Standardtarifgruppe
	array(""						,""			,""					,"0",""		,""	, "Tarifgruppe Tag/Nacht", true ),
  );

	$ExterneStromzaehlerGroups = array(
	//*************************************************************************************
	// Hier koennen "externe" Stromzaehler (IDs) eingetragen werden
	// Noch nicht ganz fertig
	//		   Name 		       Gruppe    ID-Leistung ID-KWh     Watt     kWh  Tarifgruppe              in Gesamt
	//*************************************************************************************
	array("Nebenzaehler1","ExKeller"		,"28466" ,"28466"		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),
	array("Nebenzaehler2","ExKeller"		,"28466" ,"28466"		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),

  			);

	$SystemStromzaehlerGroups = array(
	//*************************************************************************************
	//		   Name 		       Gruppe    ID-Leistung ID-KWh     Watt     kWh  Tarifgruppe              in Gesamt
  // Hier ist der Haupstromzaehler und der nicht erfasste Teil definiert
  // Sind bei Gesamt die beiden IDs auf 0 , gibt es keinen Hauptstromzaehler und es wird
  // der Gesamtstrom aus der Summe aller erfassten Daten gebildet.
  // Sonstige ist zur Zeit ein Platzhalter fuer den nicht erfassten Teil
	//*************************************************************************************
	array("Gesamt" 		,"SYSTEM_MAIN"	,"49998"	, "35097"   ,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),
	array("Sonstige"	,"SYSTEM_REST"	, false	, false		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),
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
	define ( 'HIGHCHARTS_THEME' , 'ips.js' ) ;   // Highchart Theme

  //***************************************************************************
	// Externe Stromdaten ( zB EKM )
	// Dies ist alte Version - Bitte nicht mehr verwenden
	// IDs oben bei $SystemStromzaehlerGroups in SYSTEM_MAIN eintragen
	//***************************************************************************
  define ( 'ID_GESAMTVERBRAUCH',0); // VariablenID des Gesamtverbrauchs
  define ( 'ID_LEISTUNG',0);        // VariablenID der aktuellen Leistung

  //***************************************************************************
	// MySql Anbindung
	//***************************************************************************
	define ( 'MYSQL_ANBINDUNG' , 			false );
	define ( 'MYSQL_SERVER' , 				'121.11.58.34' );
	define ( 'MYSQL_USER' , 				'xxxx' );
	define ( 'MYSQL_PASSWORD' , 			'xxxx' );
	define ( 'MYSQL_DATENBANK' , 			'Plugwise' );
	define ( 'MYSQL_TABELLE_LEISTUNG' , 'Leistung' );
	define ( 'MYSQL_TABELLE_GESAMT' , 	'Gesamtverbrauch' );



  
  

?>
  