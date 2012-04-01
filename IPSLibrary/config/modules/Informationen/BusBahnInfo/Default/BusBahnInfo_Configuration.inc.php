<?
/***************************************************************************//**
* @ingroup informationen
* @{ 
* @defgroup busbahninfo BusBahnInformationen
* @{ 
* @defgroup busbahninfo_configuration BusBahnInfo Konfiguration
* @{
*
* Configuration fuer BusBahnInfo
*
* @file       BusBahnInfo_Configuration.inc.php
* @author     1007
* @version    Version 1.0.1
* @date       04.03.2012
*
* @brief Stationen definieren  
* 
* <br>Um den genauen Namen der Haltestelle zu finden 
* <br>auf die Seite http://reiseauskunft.bahn.de/bin/bhftafel.exe 
* <br>gehen und dort den Namen suchen.
* <br>Parameter 1 ist der Name ( Alias ) fuer die Station
* <br>Parameter 2 ist der Bahnhof oder die Haltestelle
* <br>Parameter 3 ist die Art der Tafel: "Abfahrt" oder "Ankunft"
* <br>Parameter 4 ist die Wegezeit zur Station in Minuten ( farbliche Anzeige )
* <br>Parameter 5 - 13 . Hier koennen bestimmte Verkehrsmittel ausgeschlossen werden
* <br>Parameter 5  Verkehrsmittel ICE
* <br>Parameter 6  Verkehrsmittel IC/EC
* <br>Parameter 7  Verkehrsmittel Interregie/Schnellzuege
* <br>Parameter 8  Verkehrsmittel Nahverkehr/Sonstiges
* <br>Parameter 9  Verkehrsmittel SBahn
* <br>Parameter 10 Verkehrsmittel Bus
* <br>Parameter 11 Verkehrsmittel Faehren
* <br>Parameter 12 Verkehrsmittel UBahn
* <br>Parameter 13 Verkehrsmittel Tram
*
*******************************************************************************/

  define  ( 'DEBUG_MODE'  , FALSE );
  define  ( 'LOG_MODE'    , FALSE );
      
	GLOBAL $stationen;
	$stationen = array (
	//***************************************************************************
	//		Name			    Station 				    RICHTUNG ,Weg,ICE,IC/EC,IR,NV,SBAHN,BUS,FAEHRE,UBAHN,TRAM
	//***************************************************************************

	array("NameTab1",		"Frankfurt(M) Hbf",	"Abfahrt",10 ,true,true,true,true,true,true,true,true,true),
	array("NameTab2",		"Frankfurt(M) Hbf",	"Ankunft",10 ,true,true,true,true,true,true,true,true,true),
	array("NameTab3",		"",	                "Abfahrt",0  ,true,true,true,true,true,true,true,true,true),
	array("NameTab4",		"",	                "Ankunft",0  ,true,true,true,true,true,true,true,true,true),
	array("NameTab5",		"",	                "Abfahrt",0  ,true,true,true,true,true,true,true,true,true),
	array("NameTab6",		"",	                "Ankunft",0  ,true,true,true,true,true,true,true,true,true),


	//***************************************************************************
	array("",			"",						""			,0  ,false,false,false,false,false,false,false,false,false));
	//***************************************************************************
	
  //****************************************************************************
  //  Sonstiges
  //****************************************************************************
  define  ( 'MAX_LINES'    , 12 );    // maximale Eintraege pro Seite
  define  ( 'REFRESH_TIME' , 300 );   // wie oft sollen Daten aktualisiert werden ( Minuten )
  //****************************************************************************
  //  PROXY SERVER
  //****************************************************************************
  define  ( 'PROXY_SERVER'    , "" ); 

  define  ( 'REPOSITRY' ,  'https://raw.github.com/1007/IPSLibrary/BusBahnInfo/') ;
  
/***************************************************************************//**
* @}
* @}
* @}
*******************************************************************************/
?>