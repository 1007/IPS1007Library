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
	// Highcharts
	//***************************************************************************
  define ( 'HIGHCHARTS' , true ) ;
  define ( 'WEBSERVER' ,'http://192.168.10.8:82/' ) ;
  

?>
  