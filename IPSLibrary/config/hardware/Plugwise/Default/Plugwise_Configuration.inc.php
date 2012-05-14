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
	define ( 'CALIBRATION_TIME',3);     // Uhrzeit fuer Recalibration
  
	GLOBAL $CircleGroups;
	GLOBAL $Stromtarife;
      
	$CircleGroups = array(
	//***************************************************************************
	//		    CircleID			   Name 		Gruppe       Ein/Aus   Watt     kWh
	//***************************************************************************
	array("000D6F0000B81B6E","Circle+",	"Keller",      "1",    "100",  "5"),
	array("000D6F0000C3B1DA","Circle1",	"Keller",      "0",    "",     ""),
	array("000D6F0000B81B6B","Circle2",	"Wohnzimmer",  "0",    "",     ""),
	array("000D6F0000C3B1DC","Circle3",	"Wohnzimmer",  "0",    "",     ""),
	array("000D6F0000B81B6D","Circle4",	"Wohnzimmer",  "0",    "",     ""),
	array("000D6F0000C3B1DE","Circle5",	"Buero",       "0",    "",     ""),
	array("000D6F0000B81B6F","Circle6",	"Buero",       "0",    "",     ""),
	array("000D6F0000C3B10A","Circle7",	"Buero",       "0",    "",     ""),
	array("000D6F0000B81B1E","Circle8",	"Kueche",      "0",    "",     ""),
	array("000D6F0000C3B12A","Circle9",	"Kueche",      "0",    "",     ""),
  );

	//***************************************************************************
	//array("",			           "",				"",      "","",""));
	//***************************************************************************

	//***************************************************************************
	// Stromtarife
	//***************************************************************************
  	$Stromtarife = array(array("01.01.2011","31.12.2011",array(
                                          array("Tarif1","22:00","06:00","0,22"),
														array("Tarif2","22:00","06:00","0,22"),
														array("Tarif3","","",""),
                     							array("Tarif4","","",""))),
							   array("01.01.2012","31.12.2012",array(
                                          array("Tarif1","22:00","06:00","0,22"),
														array("Tarif2","22:00","06:00","0,22"),
														array("Tarif3","","",""),
                     							array("Tarif4","","",""))),
							   array("01.01.2013","31.12.2013",array(
                                          array("Tarif1","22:00","06:00","0,22"),
														array("Tarif2","22:00","06:00","0,22"),
														array("Tarif3","","",""),
                     							array("Tarif4","","",""))),
							   array("01.01.2014","31.12.2014",array(
                                          array("Tarif1","22:00","06:00","0,22"),
														array("Tarif2","22:00","06:00","0,22"),
														array("Tarif3","","",""),
                     							array("Tarif4","","","")))
														);



  	//***************************************************************************
	// Highcharts
	//***************************************************************************
	define ( 'HIGHCHARTS' , true ) ;


  

?>
  