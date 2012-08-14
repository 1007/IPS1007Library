<?php
/**@defgroup plugwise_profile Plugwise Profile
* @ingroup plugwise
* @{
*
* Konfigurations File fuer Plugwise.
*
* @file Plugwise_Profile.inc.php
* @author 
* @version
* Version 0.6, 5.08.2012<br/>
*
*/
  //****************************************************************************
  //  Profile :
  //          Parameter0 = Name
  //          Parameter1 = Associations
  //          Parameter2 = Icons
  //          Parameter3 = Colors
  //****************************************************************************

  GLOBAL $Profil_Plugwise_Leistung;
  GLOBAL $Profil_Plugwise_Verbrauch;
  GLOBAL $Profil_Plugwise_Switch;
  GLOBAL $Profil_Plugwise_MenuItem;
  GLOBAL $Profil_Plugwise_MenuScripte;
  GLOBAL $Profil_Plugwise_MenuUebersicht;
 
 
  $Profil_Plugwise_Leistung = array(
                "~Watt.14490" );
  
  $Profil_Plugwise_Verbrauch = array(
                "~Electricity" );
                
  $Profil_Plugwise_Switch = array(
                "~Switch" );
                
 
  $Profil_Plugwise_MenuItem = array( 
                "Plugwise_MenuItem",                    // Parameter0
                array( 0	=> "",                        // Parameter1
                       1 => "   " ),
								'',                                     // Parameter2
                array( 0  =>	0xFFCC00,                 // Parameter3
                       1  =>	0x00FFCC) );

	$Profil_Plugwise_MenuScripte = array( 
                "Plugwise_MenuScripte",                 // Parameter0
                array( 0	=> "   " ),                   // Parameter1
								'',                                     // Parameter2
                array( 0 =>	0xFFCC00 ));                // Parameter3


	$Profil_Plugwise_MenuUebersicht = array(
                "Plugwise_MenuUebersicht",              // Parameter0
                array( 0	=> "On/Offline",              // Parameter1
											 1 => "Ein / Aus ",
											 2 => "HW-Version",
											 3 => "SW-Version",
											 4 => "- Timing -",
											 5 => " Not used " ),
								'',                                     // Parameter2
                array( 0  =>	0xFFCC00,                 // Parameter3
											 1  =>	0xFFCC00,
											 2  =>	0xFFCC00,
											 3  =>	0xFFCC00,
											 4  =>	0xFFCC00,
											 5  =>	0xFFCC00 ));
  

?>