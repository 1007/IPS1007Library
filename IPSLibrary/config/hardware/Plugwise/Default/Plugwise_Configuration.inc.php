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
  GLOBAL $Zaehleractions;
  
/***************************************************************************//**
* Debug und Logging
*   DEBUG_MODE -  TRUE/FALSE ( Standard FALSE )
*                 wenn auf TRUE werden bei Bedarf Informationen im Meldungsfenster
*                 angezeigt
*   LOG_MODE   -  TRUE/FALSE ( Standard FALSE )
*                 wenn TRUE werden jede Menge Logfiles in Log/Plugwise Ordner
*                 geschrieben 
*******************************************************************************/
	define ( 'DEBUG_MODE' , FALSE );
	define ( 'LOG_MODE'   , FALSE );


/***************************************************************************//**
*   COMPORT           - Comport des Plugwise Sticks 
*                       bei Aenderung muss ein Update ausgefuehrt werden 
*   REFRESH_TIME      - Standard 1 Minute
*                       Abfragezeit der Circles in Minuten
*   CALIBRATION_TIME  - Standard 3 = 03:00 Uhr
*                       Zu dieser Zeit werden die Kalibrierungsdaten der Circles
*                       neu gelesen und Uhrzeiten gecheckt
*   WAIT_TIME         - Standard 300 ms 
*                       Wartezeit in Millisekunden nachdem ein Telegramm 
*                       gesendet wurde
*   AUTOCREATECIRCLE  - Standard FALSE
*                       Bei TRUE werden neu gefundene Circles automatisch angelegt
*                       ( Vorsicht bei vielen Circles ) im Moment nicht sinnvoll
*                       Daten der Circles muessen trotzdem unten eingetragen werden                                        
*   CHECK_VERSION     - suche nach neuer Version ( Standard FALSE )
*   CHECK_VERSION_TIME- Uhrzeit zu der nach einem Update gesucht wird.
*                       Nur wenn CHECK_VERSION auf TRUE   ( Stunde )                 
*   ALT_BUTTON_NORMAL - benutze eigenen Button fuer Tab im Webfront
*                       ( Standard FALSE )
*                       zum aktivieren Filenamen eingeben.
*                       Beispiel "meineButton.png" 
*   ALT_BUTTON_RED    - benutze eigenen Button fuer Tab im Webfront
*                       ( Standard FALSE )
*                       zum aktivieren Filenamen eingeben.
*                       Beispiel "meineButtonred.png"
*                       Dieser Button wird angezeigt wenn Update verfuegbar 
*******************************************************************************/
	define ( 'COMPORT'           , 'COM4' );       	
	define ( 'REFRESH_TIME'      , 1      );         
	define ( 'CALIBRATION_TIME'  , 3      );     
	define ( 'WAIT_TIME'         , 300    );          
  define ( 'AUTOCREATECIRCLE'  , false  );    
	define ( 'CHECK_VERSION'     , false  ); 	
	define ( 'CHECK_VERSION_TIME', 4      ); 	
  define ( 'ALT_BUTTON_NORMAL' , false  ); 	
  define ( 'ALT_BUTTON_RED'    , false  ); 	


/***************************************************************************//**
* Circle Gruppen ( bei Aenderungen Update ausfuehren )
*                 letzten Eintrag ( Standardtarifgruppe ) nicht loeschen 
*   Feld 01   -   CircleID      - ID-Nummer des Circles
*   Feld 02   -   Name          - Anzeigename
*   Feld 03   -   Gruppe        - in welcher Gruppe ist dieser Circle
*   Feld 04   -   Ein/Aus       - Im Webfront schaltbar ( 0/1 oder true/false )
*                                 hier kann auch eine VariablenID angegeben 
*                                 werden die den Circle entsprechend schaltet                               
*   Feld 05   -   Watt          - max Watt Anzeige im Graph ( rot ) ( 0 = disabled )
*   Feld 06   -   kWh           - zur Zeit ohne Funktion
*   Feld 07   -   Tarifgruppe   - Tarifgruppe des Circles (siehe Tarife )
*   Feld 08   -   in Gesamt     - in Gesamtanzeige enthalten
*   Feld 09   -   in Gruppe     - in Gesamtanzeige der Gruppe enthalten
*******************************************************************************/      
  $CircleGroups = array(
	   array("000D6F0000B81B6E","Verbraucher1","Raum1"	,"1","500"	,"5" 	, "Tarifgruppe Tag/Nacht" , true, true ),
	   array("000D6F0000C3B1DA","Verbraucher2","Raum1"	,"0","200"	,"" 	, "Tarifgruppe Tag/Nacht" , true, true ), 
	   array("000D6F0000B81B7E","Verbraucher3","Raum2"	,"1","500"	,"5" 	, "Tarifgruppe Tag/Nacht" , true, true ),
	   array("000D6F0000C3B18A","Verbraucher4","Raum2"	,"0","200"	,"" 	, "Tarifgruppe Tag/Nacht" , true, true ), 

	// Standardtarifgruppe
	   array(""						     ,""			      ,""				,"0",""		  ,""	  , "Tarifgruppe Tag/Nacht" , true ),
  );


/***************************************************************************//**
* Externe Stromzaehler Gruppen . Hier koennen "externe" Stromzaehler ( zB EKM )
* definiert werden. Eingetragen wird die VariablenID der akt Leistung (Watt)und 
* die VariablenID des Verbrauchs (kWh)
*                      
*   Feld 01   -   Name  dieser muss eindeutig sein - keine doppelten
*   Feld 02   -   Gruppe
*   Feld 03   -   VariablenID-Leistung
*   Feld 04   -   VariablenID-Verbrauch
*   Feld 05   -   Watt
*   Feld 06   -   kWh   wird nicht verwendet
*   Feld 07   -   Tarifgruppe
*   Feld 08   -   in Gesamt der Gruppe
*   Feld 09   -   in Gesamtanzeige der Gruppe enthalten
*******************************************************************************/
	$ExterneStromzaehlerGroups = array(
	   array("Nebenzaehler1","ExKeller"		,"28466" ,"28466"		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true,true),
	   array("Nebenzaehler2","ExKeller"		,"28466" ,"28466"		,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true,true),
 
  			);

/***************************************************************************//**
* System Stromzaehler Gruppen 
*           Hier sind nur 2 Stromzaehler definiert
*           Hauptstromzaehler und der Reststromzaehler
*           Sollte beim Gesamtzaehler die bieden IDs auf 0/false sein gibt es keinen
*           Gesamtstromzaehler und es werden alle oben definierten Stromzaehler
*           die markiert sind als ( in Gesamt ) addiert.
*           Sonstige ist ein Platzhalter fuer den hier nicht erfassten Teil des 
*           Stromverbrauches.
*           Bitte an den Text ( aenderbar! ) halten
*   Gesamt
*     Feld 01   -   Name
*     Feld 02   -   Ident
*     Feld 03   -   ID-Leistung des "externen" Stromzaehlers ( zB EKM )  aenderbar!
*     Feld 04   -   ID-Gesamt   des "externen" Stromzaehlers ( zB EKM )  aenderbar!
*     Feld 05   -   Watt                                                 aenderbar!
*     Feld 06   -   nicht verwendet
*     Feld 07   -   Tarifgruppe ( siehe Stromtarife )                    aenderbar!
*     Feld 08   -   in Gesamt 
*   Sonstige
*     Feld 01   -   Name
*     Feld 02   -   Ident
*     Feld 03   -   
*     Feld 04   -   
*     Feld 05   -   Watt                                                 aenderbar!
*     Feld 06   -   nicht verwendet
*     Feld 07   -   Tarifgruppe ( siehe Stromtarife )                    aenderbar!
*     Feld 08   -   in Gesamt 
* 
*******************************************************************************/
	$SystemStromzaehlerGroups = array(
      array("Gesamt" 		,"SYSTEM_MAIN"	,"49998" , "35097"   ,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),
      array("Sonstige"	,"SYSTEM_REST"	, false	 , false		 ,"500"	,"5" 	, "Tarifgruppe Tag/Nacht",true),

  			);


/***************************************************************************//**
* Stromtarife 
*             Stromtarife und Gruppen immer beginnen um 00:00 Uhr
*   Feld 01   -   Anfangsdatum Tarifgruppe
*   Feld 02   -   Anfangszeit Tarifgruppe
*   Feld 03   -   Tarifgruppenname ( frei waehlbar )
*   Feld 04   -   Tarifname ( frei waehlbar )
*   Feld 05   -   Anfangszeit dieses Tarifes
*   Feld 06   -   Endzeit dieses Tarifes
*   Feld 07   -   Tarifpreis in Cent             
* 
*******************************************************************************/
  $Stromtarife = array(
	   array("01.06.2011","31.05.2012","Tarifgruppe Normal"		  ,"Normaltarif"  ,"00:00","23:59","26,05"),
	   array("01.06.2011","31.05.2012","Tarifgruppe Tag/Nacht"	,"Nachttarif"	  ,"00:00","06:29","18,78"),
		 array("01.06.2011","31.05.2012","Tarifgruppe Tag/Nacht"	,"Tagtarif"  	  ,"06:30","22:29","26,05"),
		 array("01.06.2011","31.05.2012","Tarifgruppe Tag/Nacht"	,"Nachttarif"	  ,"22:30","23:59","18,78"),

		 array("01.06.2012","31.05.2013","Tarifgruppe Normal"		  ,"Normaltarif"	,"00:00","23:59","26,05"),
		 array("01.06.2012","31.05.2013","Tarifgruppe Tag/Nacht"	,"Nachttarif"	  ,"00:00","06:29","18,78"),
		 array("01.06.2012","31.05.2013","Tarifgruppe Tag/Nacht"	,"Tagtarif"  	  ,"06:30","22:29","26,05"),
		 array("01.06.2012","31.05.2013","Tarifgruppe Tag/Nacht"	,"Nachttarif"	  ,"22:30","23:59","18,78"),

				);

/***************************************************************************//**
* Zaehleractions
* mache etwas bei unterschreiten oder ueberschreiten eines Wertes
* fuer eine bestimmte Zeit
*
*   Feld 01   -   CircleID oder Externer Name
*   Feld 02   -   < Wert kleiner , > Wert groesser
*   Feld 03   -   Leistungswert1 in Watt
*   Feld 04   -   Leistungswert2 in Watt
*   Feld 05   -   Zeitraum
*   Feld 06   -   VariablenID oder ScriptID
*   Feld 07   -   Wert auf welchen die Variable gesetzt wird
*   Feld 08   -   Reserve
*
*  Beispiel
* 	array("000D6F0000D3412E"	,"<"	,4	,false,5   ,xxxxx 	,false	,false),
*  fuehre Script 12345 aus / bzw setze Variable 12345 auf true
*  wenn Circle 5 Minuten lang unter 4 Watt hat.
*******************************************************************************/
  $Zaehleractions = array(
	    //array("000D6F0000D3412E"	,"<"	,4	,false ,5   ,12345 	,0	,false),
	    //array("000D6F0000D3412E"	,">"	,400,false ,5   ,12345 	,1	,false),
			);

/***************************************************************************//**
* Archivehandling 
*   AGGTYPE       - AggregationType 0/1 ( Standard 0 )( 0=Standard,1=Zaehler )
*   ARCHIVLOGGING - Archivlogging TRUE/FALSE ( Standard TRUE )
*******************************************************************************/
	define ( 'AGGTYPE'       , 1 ) ;
  define ( 'ARCHIVLOGGING' , true);


/***************************************************************************//**
* HIGHCHARTS
*   HIGHCHARTS          - Highchartsgraph anzeigen ( Standard = TRUE )
*   HIGHCHARTS_ZEITRAUM - Anzeigezeitraum in Stunden ( Standard = 24 )
*   HIGHCHARTS_THEME    - Highchart Theme ( Standard = "" )
*******************************************************************************/
	define ( 'HIGHCHARTS'          , true ) ;
	define ( 'HIGHCHARTS_ZEITRAUM' , 24 ) ;   
	define ( 'HIGHCHARTS_THEME'    , '' ) ;  


/***************************************************************************//**
* Hauptstromzaehler wenn Daten in IPS vorhanden
* veraltet , IDs bei SystemStromzaehlerGroups eintragen
* bitte nicht loeschen
*   ID_GESAMTVERBRAUCH  - ID der Variablen Gesamtverbrauch ( Standard = 0 ) 
*   ID_LEISTUNG         - ID der Variablen Leistung ( Standard = 0 )
*******************************************************************************/
  define ( 'ID_GESAMTVERBRAUCH' ,0); 
  define ( 'ID_LEISTUNG'        ,0);        


/***************************************************************************//**
* MySQL - Anbindung 
*   MYSQL_ANBINDUNG         - MySQL Anbindung aktiv ( Standard = FALSE )
*   MYSQL_SERVER            - MySQL-Server IP-Adresse/Name
*   MYSQL_USER              - MySQL-User
*   MYSQL_PASSWORD          - MySQL-Password
*   MYSQL_DATENBANK         - MySQL-Datenbankname (Standard = Plugwise)
*   MYSQL_TABELLE_LEISTUNG  - MySQL-Tab Leistung  (Standard = Leistung)
*   MYSQL_TABELLE_GESAMT    - MySQL-Tab Verbrauch (Standard = Gesamtverbrauch)
* wird eine Variable angegeben zB aus der ____autoload.php muss diese als
* GLOBAL definiert werden. ( GLOBAL $MYSQL_SERVER; )
*******************************************************************************/
	define ( 'MYSQL_ANBINDUNG'         , false );
	define ( 'MYSQL_SERVER'            , '192.168.10.1' );
	define ( 'MYSQL_USER'              , 'xxxx' );
	define ( 'MYSQL_PASSWORD'          , 'xxxx' );
	define ( 'MYSQL_DATENBANK'         , 'Plugwise' );
	define ( 'MYSQL_TABELLE_LEISTUNG'  , 'Leistung' );
	define ( 'MYSQL_TABELLE_GESAMT'    , 'Gesamtverbrauch' );



  
  

?>