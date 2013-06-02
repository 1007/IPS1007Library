<?php
/**@defgroup roomba_configuration Roomba Konfiguration
* @ingroup roomba
* @{
*
* Konfigurations File fuer Roomba.
*
* Hier werden folgende Komponenten definiert:
* -
* -
* -
* -
*
* @file Roomba_Configuration.inc.php
* @author 1007
* @version
* Version 1.0.1, 27.02.2012<br/>
*
*/
	GLOBAL $roombas;

  //****************************************************************************
  // Debug und Logging
  //****************************************************************************
  define ( 'DEBUG_MODE' , TRUE );
  define ( 'LOG_MODE'   , TRUE );

 //****************************************************************************
	$roombas = array (
                    array (
                            'Name' =>           "Roomba2",    
                            'Aktiv' =>          true, 
                            'XBeeSplitter' =>   46435,     
                            'XBeeGateway' =>    53848,
                            ),
                    array (
                            'Name' =>           "Roomba3",    
                            'Aktiv' =>          false, 
                            'XBeeSplitter' =>   32706,     
                            'XBeeGateway' =>    53848,
                            ),
	
	                 );
	//***************************************************************************

   
  //****************************************************************************
  // Anzahl der Lighthouses pro Roomba
  //****************************************************************************
  define ( 'LIGHTHOUSES_ANZAHL' , 5 );

  //****************************************************************************
  // Polling-Zeiten in Sekunden
  //****************************************************************************
  define ( 'POLLING_OFFLINE'  ,30);
  define ( 'POLLING_UNKNOWN'  ,30);
  define ( 'POLLING_MOVING'   ,2 );
  define ( 'POLLING_CHARGING' ,30);
  define ( 'POLLING_DEFAULT'  ,30);

  //****************************************************************************
  // SONGS
  //****************************************************************************
  define ( 'SONG0' ,"");
  define ( 'SONG1' ,"Muppet:d=4,o=5,b=250:c6,c6,a,b,8a,b,g,p,c6,c6,a,8b,8a,8p,g.,p,e,e,g,f,8e,f,8c6,8c,8d,e,8e,8e,8p,8e,g,2p,c6,c6,a,b,8a,b,g,p,c6,c6,a,8b,a,g.,p,e,e,g,f,8e,f,8c6,8c,8d,e,8e,d,8d,c");
  define ( 'SONG2' ,"DasBoot:d=4,o=5,b=100:d#.4,8d4,8c4,8d4,8d#4,8g4,a#.4,8a4,8g4,8a4,8a#4,8d,2f.,p,f.4,8e4,8d4,8e4,8f4,8a4,c.,8b4,8a4,8b4,8c,8e,2g.,2p");
  define ( 'SONG3' ,"Flntstn:d=4,o=5,b=200:g#,c#,8p,c#6,8a#,g#,c#,8p,g#,8f#,8f,8f,8f#,8g#,c#,d#,2f,2p,g#,c#,8p,c#6,8a#,g#,c#,8p,g#,8f#,8f,8f,8f#,8g#,c#,d#,2c#");
  define ( 'SONG4' ,"YMCA:d=4,o=5,b=160:8c#6,8a#,2p,8a#,8g#,8f#,8g#,8a#,c#6,8a#,c#6,8d#6,8a#,2p,8a#,8g#,8f#,8g#,8a#,c#6,8a#,c#6,8d#6,8b,2p,8b,8a#,8g#,8a#,8b,d#6,8f#6,d#6,f.6,d#.6,c#.6,b.,a#,g#");
  define ( 'SONG5' ,"BarbieGirl:d=4,o=5,b=125:8g#,8e,8g#,8c#6,a,p,8f#,8d#,8f#,8b,g#,8f#,8e,p,8e,8c#,f#,c#,p,8f#,8e,g#,f#");
  define ( 'SONG6' ,"Popcorn:d=4,o=5,b=112:8c6,8a#,8c6,8g,8d#,8g,c,8c6,8a#,8c6,8g,8d#,8g,c,8c6,8d6,8d#6,16c6,8d#6,16c6,8d#6,8d6,16a#,8d6,16a#,8d6,8c6,8a#,8g,8a#,c6");
  define ( 'SONG7' ,"Entertainer:d=4,o=5,b=140:8d,8d#,8e,c6,8e,c6,8e,2c.6,8c6,8d6,8d#6,8e6,8c6,8d6,e6,8b,d6,2c6,p,8d,8d#,8e,c6,8e,c6,8e,2c.6,8p,8a,8g,8f#,8a,8c6,e6,8d6,8c6,8a,2d6");
  define ( 'SONG8' ,"aadams:d=4,o=5,b=160:8c,f,8a,f,8c,b4,2g,8f,e,8g,e,8e4,a4,2f,8c,f,8a,f,8c,b4,2g,8f,e,8c,d,8e,1f,8c,8d,8e,8f,1p,8d,8e,8f#,8g,1p,8d,8e,8f#,8g,p,8d,8e,8f#,8g,p,8c,8d,8e,8f");

  define ( 'EVENT_CLEAN'    , 'SONG1');
  define ( 'EVENT_SPOT'     , 'SONG2');
  define ( 'EVENT_MAX'      , 'SONG3');
  define ( 'EVENT_HOME'     , 'SONG4');
  define ( 'EVENT_INIT'     , 'SONG5');
  define ( 'EVENT_POWER'    , 'SONG6');
  define ( 'EVENT_WARTUNG1' , 'SONG7');
  define ( 'EVENT_WARTUNG2' , 'SONG8');

?>