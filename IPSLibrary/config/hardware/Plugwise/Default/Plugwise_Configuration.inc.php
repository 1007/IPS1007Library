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

	define ( 'COMPORT' , 'COM4' );       // COM-Port des Sticks	
  define ( 'REFRESH_TIME',1);         // Refreshzeit in Minuten
  define ( 'CALIBRATION_TIME',3);     // Uhrzeit fuer Recalibration 
  
  GLOBAL $CircleGroups;
    
  $CircleGroups = array(
	//***************************************************************************
	//		CircleID			    Name 				   Gruppe   Res Res Res
	//***************************************************************************

	array("000D6F0000B81B6E","Circle+",	"Keller","","",""),
	array("000D6F0000C3B1DA","Circle1",	"Keller","","",""),
	array("000D6F0000B81B6B","Circle2",	"Wohnzimmer","","",""),
	array("000D6F0000C3B1DC","Circle3",	"Wohnzimmer","","",""),
	array("000D6F0000B81B6D","Circle4",	"Wohnzimmer","","",""),
	array("000D6F0000C3B1DE","Circle5",	"Buero","","",""),
	array("000D6F0000B81B6F","Circle6",	"Buero","","",""),
	array("000D6F0000C3B10A","Circle7",	"Buero","","",""),
	array("000D6F0000B81B1E","Circle8",	"Kueche","","",""),
	array("000D6F0000C3B12A","Circle9",	"Kueche","","",""),


	//***************************************************************************
	array("",			           "",				"",      "","",""));
	//***************************************************************************
  

?>
  