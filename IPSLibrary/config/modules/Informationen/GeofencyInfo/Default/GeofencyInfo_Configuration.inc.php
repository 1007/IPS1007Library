<?
/***************************************************************************//**
* @ingroup informationen
* @{
* @defgroup geofencyinfo GeofencyInformationen
* @{
* @defgroup geofencyinfo_configuration GeofencyInfo Konfiguration
* @{
* 
* @file       GeofencyInfo_Configuration.inc.php
* @author     1007
* @version    Version 1.0.0
* @date       11.12.2013
* 
* @brief Configuration fuer GeofencyInfo
* @details  Hier werden folgende Komponenten definiert:
* - DEBUG_MODE ( true / false )
* - LOG_MODE ( true / false )
* 
*******************************************************************************/


/***************************************************************************//**
* Debug und Logging
*   DEBUG_MODE -  TRUE/FALSE ( Standard FALSE )
*                 wenn auf TRUE werden Informationen im Meldungsfenster
*                 angezeigt
*   LOG_MODE   -  TRUE/FALSE ( Standard FALSE )
*                 wenn TRUE werden Logfiles in Log/GeofencyInfo Ordner
*                 geschrieben 
*******************************************************************************/
  define  ( 'DEBUG_MODE'  , FALSE );
  define  ( 'LOG_MODE'    , FALSE );
 
/***************************************************************************//**
* Geraetekonfiguration 
*             
*   Feld 01   -   laufende Nummer
*   Feld 02   -   Geraet aktiv (true/false)
*   Feld 03   -   Geraetenamen ( identisch mit dem Namen in der URL (IPSName=xx)
*   Feld 04   -   
*   Feld 05   -   
*   Feld 06   -   
*   Feld 07   -                
* 
*   Beispiel  - array(1,true,"iPhone",false,false,false,false),
*******************************************************************************/
  $DeviceConfig = array(
	   array(1, false,"",false,false,false,false),
	   array(2, false,"",false,false,false,false),
	   array(3, false,"",false,false,false,false),
	   array(4, false,"",false,false,false,false),
	   array(5, false,"",false,false,false,false),
	   array(6, false,"",false,false,false,false),
	   array(7, false,"",false,false,false,false),
	   array(8, false,"",false,false,false,false),
	   array(9, false,"",false,false,false,false),
	   array(10,false,"",false,false,false,false),
				);   

/***************************************************************************//**
* Geraete Aktionen 
*             
*   Feld 01   -   laufende Nummer
*   Feld 02   -   Geraet aktiv (true/false)
*   Feld 03   -   Geraetenamen ( identisch mit dem Namen in der URL (IPSName=xx)
*   Feld 04   -   Location Name   
*   Feld 05   -   Coming ( SkriptID welches beim Kommen ausgefuehrt werden soll)
*   Feld 06   -   Going  ( SkriptID welches beim Gehen ausgefuehrt werden soll)
*   Feld 07   -   Reserve             
* 
*   Beispiel  - array(1,true,"iPhone","Home",12345,12345,false),
*******************************************************************************/
  $ActionConfig = array(
	   array(1, false,"",false,false,false,false),
	   array(2, false,"",false,false,false,false),
	   array(3, false,"",false,false,false,false),
	   array(4, false,"",false,false,false,false),
	   array(5, false,"",false,false,false,false),
	   array(6, false,"",false,false,false,false),
	   array(7, false,"",false,false,false,false),
	   array(8, false,"",false,false,false,false),
	   array(9, false,"",false,false,false,false),
	   array(10,false,"",false,false,false,false),
				);
				
				
/***************************************************************************//**
* MySQL - Anbindung 
*   MYSQL_ANBINDUNG         - MySQL Anbindung aktiv ( Standard = FALSE )
*   MYSQL_SERVER            - MySQL-Server IP-Adresse/Name
*   MYSQL_USER              - MySQL-User
*   MYSQL_PASSWORD          - MySQL-Password
*   MYSQL_DATENBANK         - MySQL-Datenbankname (Standard = Geofency)
*   MYSQL_TABELLE_INOUT     - MySQL-Tab InOut (Standard = InOut)
*   MYSQL_TABELLE_RESERVE1  - MySQL-Tab Reserve1
*   MYSQL_TABELLE_RESERVE2  - MySQL-Tab Reserve2
* wird eine Variable angegeben zB aus der ____autoload.php muss diese als
* GLOBAL definiert werden. ( GLOBAL $MYSQL_SERVER; )
*******************************************************************************/
	define ( 'MYSQL_ANBINDUNG'         , false );
	define ( 'MYSQL_SERVER'            , '192.168.10.1' );
	define ( 'MYSQL_USER'              , 'xxxx' );
	define ( 'MYSQL_PASSWORD'          , 'xxxx' );
	define ( 'MYSQL_DATENBANK'         , 'Geofency' );
	define ( 'MYSQL_TABELLE_INOUT'     , 'InOut' );
	define ( 'MYSQL_TABELLE_RESERVE1'  , 'Reserve1' );
	define ( 'MYSQL_TABELLE_RESERVE2'  , 'Reserve2' );
	
/***************************************************************************//**
* Interne Einstellungen
*******************************************************************************/
  define  ( 'CREATE_IPS_ZEITSTEMPEL'  , FALSE );
  define  ( 'AUTO_LEAVING_LOCATION'   , FALSE );
  define  ( 'MAPS_GOOGLE_CREATE'      , FALSE );
  define  ( 'MAPS_OSM_CREATE'         , TRUE  );
  define  ( 'TESTLOCALWEBSERVER'      , 'http://localhost:82/' );
  

      
/***************************************************************************//**
* @}
* @}
* @}
*******************************************************************************/
?>