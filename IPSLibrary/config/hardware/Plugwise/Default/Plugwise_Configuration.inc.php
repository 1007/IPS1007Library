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
  
  GLOBAL $CircleGroups;
    
  $CircleGroups = array(
	//***************************************************************************
	//		CircleID			    Name 				   Gruppe   Res Res Res
	//***************************************************************************

	array("000D6F0000B81B6E","Circle+",	"System","","",""),
	array("000D6F0000C3B1DA","Circle1",	"System","","",""),


	//***************************************************************************
	array("",			           "",				"",      "","",""));
	//***************************************************************************
  

?>
  