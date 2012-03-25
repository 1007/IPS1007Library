<?
/***************************************************************************//**
* @ingroup informationen
* @{
* @defgroup withingsinfo WithingsInformationen
* @{
* @defgroup withingsinfo_configuration WithingsInfo Konfiguration
* @{
* 
* @file       WithingsInfo_Configuration.inc.php
* @author     1007
* @version    Version 1.0.0
* @date       01.03.2012
* 
* @brief Configuration fuer WithingsInfo
* @details  Hier werden folgende Komponenten definiert:
* - MYMAIL = Loginname
* - MYPASS = Passwort
* - DEBUG_MODE ( true / false )
* - LOG_MODE ( true / false )
* - USER1_NAME - Pseudonym wie in Withings definiert
* - USER1_ANZEIGE - Text fuer Anzeige im Webfront
* - USER1_WAAGE - ( true , false )
* - USER1_BLUTDRUCK ( true , false )
* - etc
* - REFRESH_TIME - wie oft sollen Daten geholt werden
* 
*******************************************************************************/


  define  ( 'DEBUG_MODE'  , TRUE );
  define  ( 'LOG_MODE'    , TRUE );
   
	define ('MYMAIL','');
	define ('MYPASS','');

	define ('MYURL','');
	define ('MYWBSAPIURL','wbsapi.withings.net/');
	define ('MYAPIURL','scalews.withings.net/cgi-bin/');

  define ('USER1_NAME',''); define('USER1_ANZEIGE',''); define('USER1_WAAGE',false); define('USER1_BLUTDRUCK',false);  
  define ('USER2_NAME',''); define('USER2_ANZEIGE',''); define('USER2_WAAGE',false); define('USER2_BLUTDRUCK',false);    
  define ('USER3_NAME',''); define('USER3_ANZEIGE',''); define('USER3_WAAGE',false); define('USER3_BLUTDRUCK',false);     
  define ('USER4_NAME',''); define('USER4_ANZEIGE',''); define('USER4_WAAGE',false); define('USER4_BLUTDRUCK',false);    
  define ('USER5_NAME',''); define('USER5_ANZEIGE',''); define('USER5_WAAGE',false); define('USER5_BLUTDRUCK',false);     
  define ('USER6_NAME',''); define('USER6_ANZEIGE',''); define('USER6_WAAGE',false); define('USER6_BLUTDRUCK',false);      
  define ('USER7_NAME',''); define('USER7_ANZEIGE',''); define('USER7_WAAGE',false); define('USER7_BLUTDRUCK',false);      
  define ('USER8_NAME',''); define('USER8_ANZEIGE',''); define('USER8_WAAGE',false); define('USER8_BLUTDRUCK',false);      
  define ('USER9_NAME',''); define('USER9_ANZEIGE',''); define('USER9_WAAGE',false); define('USER9_BLUTDRUCK',false);      
  
  define  ('REFRESH_TIME',180);   // Werte alle 3 Stunden holen
  define  ('PROXY_SERVER','');    // Proxyserver   
   
/***************************************************************************//**
* @}
* @}
* @}
*******************************************************************************/
?>