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
	array("000D6F0000B81B6E","Plasma TV",	"Wohnzimmer",    "1",    "500",  "5"),
	array("000D6F0000C3B1DA","Server",	  "Arbeitszimmer", "0",    "200",  ""),
//	array("000D6F0000B81B6B","Circle2",	  "Wohnzimmer",    "0",    "",     ""),
  );

	//***************************************************************************
	//array("",			           "",				"",      "","",""));
	//***************************************************************************

	//***************************************************************************
	// Stromtarife
	//***************************************************************************
  	$Stromtarife = array(array("01.06.2011","31.05.2012",array(
                                array("Nachttarif","22:30","06:30","18,78"),
                                array("Tagtarif"  ,"06:30","22:30","26,05"),
                                array("","","",""),
                                array("","","",""))),
                        array("01.06.2012","31.05.2013",array(
                                array("Nachttarif","22:30","06:30","18,78"),
                                array("Tagtarif"  ,"06:30","22:30","26,05"),
                                array("","","",""),
                                array("","","",""))),
                        array("01.06.2013","31.05.2013",array(
                                array("Nachttarif","22:30","06:30","18,78"),
                                array("Tagtarif"  ,"06:30","22:30","26,05"),
                                array("","","",""),
                                array("","","",""))),
                        array("01.06.2014","31.05.2014",array(
                                array("Nachttarif","22:30","06:30","18,78"),
                                array("Tagtarif"  ,"06:30","22:30","26,05"),
                                array("","","",""),
                                array("","","","")))
														    );



  	//***************************************************************************
	// Highcharts
	//***************************************************************************
	define ( 'HIGHCHARTS' , true ) ;


  

?>
  