<?
/***************************************************************************//**
* @ingroup withingsinfo
* @{
* @defgroup withingsinfo_refresh WithingsInfo Refresh
* @{
*
* @file       withingsinforefresh.ips.php
* @author     1007
* @version    Version 1.0.0
* @date       04.03.2012
*
*
*  @brief   Refresh der Withingsdaten
*******************************************************************************/

	IPSUtils_Include ("WithingsInfo_Configuration.inc.php", "IPSLibrary::config::modules::Informationen::WithingsInfo");
	IPSUtils_Include ("withingsinfoapi.inc.php", "IPSLibrary::app::modules::Informationen::WithingsInfo");

   $CategoryPath = "Program.IPSLibrary.data.modules.Informationen.WithingsInfo";

   $CategoryWithingsData = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.WithingsInfo");
   
	$debug = DEBUG_MODE;
	$log 	 = LOG_MODE;

	// Teste ob Withings API OK
	echo WBSAPI_OnceProbe ();
	if (WBSAPI_OnceProbe ())
		{
		if ($debug) echo "\nTeste API : OK\n";
		if ($log) logging("Teste API : OK");
		}
	else
		{
		if ($debug) echo "\nTeste API : NOK\n";
		if ($log) logging("Teste API : NOK");
		exit(-1);
		}
	//***************************************************************************
	// Konfiguration ueberpruefen
	//***************************************************************************
	if ( MYMAIL == "" )
	   {
	   echo "\nMYMAIL in Konfigurationsfile nicht definiert";
	   exit(false);
		}
	if ( MYPASS == "" )
	   {
	   echo "\nMYPASS in Konfigurationsfile nicht definiert";
	   exit(false);
		}

	//***************************************************************************
	// Alle Userdaten bei Withings holen
	//***************************************************************************
	WBSAPI_AccountGetuserslist ( MYMAIL, MYPASS, $user );
	if ( !$user )
	   {
		if ($debug) echo "\nFehler beim Holen der User MYMAIL und MYPASS ueberpruefen";
		if ($log) logging("Fehler beim Holen der User  MYMAIL und MYPASS ueberpruefen");
		exit(-1);
	   }
	   
	
	foreach ( $user as $person )
	   {
	   $shortname 	= $person['shortname'];
		$gefunden = false;
		
		// nur die User holen die auch konfiguriert sind
		for ($x=1;$x<10;$x++)
		   {
		   $per = constant('USER'.$x.'_NAME');
			if ( $per == $shortname )
			   {
			   $gefunden = true;
			   if ($log) logging ("USER:".$per." wird geholt");
			   getwithingsdata($person,$x); break;
				}
			}
		if ( !$gefunden )
			if ($log) logging ("USER:".$shortname." nicht in Konfiguration");


	   }

  dummy();

/***************************************************************************//**
* Dummy Routine gegen DoxygenBug
* wenn foreach() als letzter Befehl im Kopf dann wird foreach
* als Routine/Variable dokumentiert
*******************************************************************************/
function dummy()
  {
  }

/***************************************************************************//**
* Withingsdaten fuer jeden User holen
*******************************************************************************/
function getwithingsdata($person,$usernummer)
	{
	GLOBAL $log;
	GLOBAL $debug;
	GLOBAL $CategoryWithingsData;
	GLOBAL $CategoryPath;

	$sonderzeichen=array( "Ã¶" => "ö", "Ã¼" => "ü", "ÃŸ" => "ß","Ã¤" => "ä",
								 "Ã„" => "Ä", "Ãœ" => "Ü", "Ã–" => "Ö", "Ã©" => "Ë",
								 "Ã©" => "é" );

	$personid 		= $person['id'];
	$firstname 		= $person['firstname'];
	$firstname     = strtr($firstname, $sonderzeichen);
	$lastname 		= strtr($person['lastname'], $sonderzeichen);
	$shortname 		= strtr($person['shortname'], $sonderzeichen);
	$gender 			= $person['gender'];
	$fatmethod 		= $person['fatmethod'];
	$birthdate 		= $person['birthdate'];
	$ispublic 		= $person['ispublic'];
	$publickey 		= $person['publickey'];

	$groessedatum	= false ;
	$groesse    	= false ;

	$gewichtdatum 	= false;
	$gewicht       = false;
	$fettfrei      = false;
	$fettanteil    = false;
	$fettprozent   = false;
	$diastolic     = false;
	$systolic      = false;
	$puls          = false;
	

	if ( $debug ) print_r($person);

	// wenn Daten nicht public dann raus
	// 1 	Body scale
	// 4 	Blood pressure monitor
	$publicOK = false;
	
	if ( $ispublic == 5 )
		$publicOK = true;
	if ( $ispublic == 1 )
		$publicOK = true;
	if ( $ispublic == 4 )
		$publicOK = true;
		
	if ( $publicOK == false)
	   {
		if ($log) logging ("USER:".$shortname." nicht public");
	   return false;
	   }
	
	
	$userpath = $CategoryPath .".".$shortname;
   $userwaagepath = $userpath . ".WAAGE";
   $userblutdruckpath = $userpath . ".BLUTDRUCK";

	$id = IPSUtil_ObjectIDByPath($userpath . ".Name",true);
	if ( !$id )
	   {
	   echo "\n User nicht in Data. Installroutine ausfuehren !";
	   return;
	   }
	   
	SetValueString($id,$firstname." ".$lastname);
	$id = IPSUtil_ObjectIDByPath($userpath . ".Pseudonym");
	SetValueString($id,$shortname);
	$id = IPSUtil_ObjectIDByPath($userpath . ".Geschlecht");
	if ( $gender == 0 )
		SetValueString($id,"maennlich");
	if ( $gender == 1 )
		SetValueString($id,"weiblich");

	$id = IPSUtil_ObjectIDByPath($userpath . ".Fettmassenanzeige");
	if ( $fatmethod == 0 )
		SetValueString($id,"Verhältnis in %");
	if ( $fatmethod == 1 )
		SetValueString($id,"Masseneinheitswert (kg,lb,stlb) ");

	$id = IPSUtil_ObjectIDByPath($userpath . ".Geburtstag");
	SetValueString($id,date('d.m.Y',$birthdate));

	$startdate 	= 0;     // Startdatum
	$enddate 	= 0;     // Endedatum
	$category 	= 1;  	// aktuelle Messung
	$limit      = 1;     // Anzahl

	//***************************************************************************
	// Groesse updaten
	//***************************************************************************
	$meastype 	= 4; 		// Groesse
	$devtype 	= 0;

	WBSAPI_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$devtype,$meastype,$category,$limit);
	
	if ( $data )
		{
		foreach ( $data[0]['measures'] as $messung )
	   	{
			$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );
			if ( $messung['type'] == 4 )  $groesse  		= round ($val,2)*100;
	      }
		$groessedatum = date('d.m.Y H:i:s',$data[0]['date']);
      $id = IPSUtil_ObjectIDByPath($userpath . ".Groessendatum");
		SetValueString($id,$groessedatum);

      $id = IPSUtil_ObjectIDByPath($userpath . ".Groesse");
		SetValueInteger($id,$groesse);

		}
	else
	   {
		if ($log) logging ("USER:" .$shortname." keine GroesseDaten ");
		if ($debug) echo ("\nUSER:".$shortname." keine GroesseDaten ");
		}
	
	//***************************************************************************
	// Gewicht updaten
	//***************************************************************************
	$meastype 	= false; 		// Alles
	$devtype 	= 1;
	$data = array();

	if ( constant('USER'.$usernummer.'_WAAGE') )
		WBSAPI_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$devtype,$meastype,$category,$limit);
	
	if ( $data )
		{
		foreach ( $data[0]['measures'] as $messung )
	   	{
			$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );

			if ( $messung['type'] == 1 )  $gewicht 		= round ($val,2);
			if ( $messung['type'] == 5 )  $fettfrei 		= round ($val,2);
			if ( $messung['type'] == 6 )  $fettprozent 	= round ($val,2);
			if ( $messung['type'] == 8 )  $fettanteil  	= round ($val,2);
			if ( $messung['type'] == 9 )  $diastolic 		= round ($val,2);
			if ( $messung['type'] == 10 ) $systolic 		= round ($val,2);
			if ( $messung['type'] == 11 ) $pulse 			= round ($val,2);
	      }

			$gewichtdatum = date('d.m.Y H:i:s',$data[0]['date']);
      	$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Uhrzeit");
			SetValueString($id,$gewichtdatum);

      	$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Gewicht");
			SetValueFloat($id,$gewicht);

      	$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Fettfrei");
			SetValueFloat($id,$fettfrei);

      	$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Fettanteil");
			SetValueFloat($id,$fettanteil);

      	$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Fettprozent");
			SetValueFloat($id,$fettprozent);

         $bmi = round($gewicht/(($groesse/100)*($groesse/100)),2);
      	$id = IPSUtil_ObjectIDByPath($userwaagepath . ".BMI");
			SetValueFloat($id,$bmi);


		}
	else
	   {
		if ($log) logging ("USER:".$shortname." keine GewichtsDaten ");
		if ($debug) echo ("\nUSER:".$shortname." keine GewichtsDaten ");
		}

	//***************************************************************************
	// Blutdruck updaten
	//***************************************************************************
	$meastype 	= false; 		// Alles
	$devtype 	= 4;
	
	$data = array();
	
	if ( constant('USER'.$usernummer.'_BLUTDRUCK') )
		WBSAPI_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$devtype,$meastype,$category,$limit);

	if ( $data )
		{
		foreach ( $data[0]['measures'] as $messung )
	   	{
			$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );
			if ( $messung['type'] == 9 )  $diastolic 		= round ($val,2);
			if ( $messung['type'] == 10 ) $systolic 		= round ($val,2);
			if ( $messung['type'] == 11 ) $pulse 			= round ($val,2);
	      }

			$blutdruckdatum = date('d.m.Y H:i:s',$data[0]['date']);
      	$id = IPSUtil_ObjectIDByPath($userblutdruckpath . ".Uhrzeit");
			SetValueString($id,$blutdruckdatum);

      	$id = IPSUtil_ObjectIDByPath($userblutdruckpath . ".Diastolic");
			SetValueInteger($id,$diastolic);

      	$id = IPSUtil_ObjectIDByPath($userblutdruckpath . ".Systolic");
			SetValueInteger($id,$systolic);

      	$id = IPSUtil_ObjectIDByPath($userblutdruckpath . ".Puls");
			SetValueInteger($id,$pulse);


		}
	else
	   {
		if ($log) logging ("USER:".$shortname." keine BlutdruckDaten ");
		if ($debug) echo ("\nUSER:".$shortname." keine BlutdruckDaten ");
		}

}

/***************************************************************************//**
* Dekodiert das englische Datum ins deutsche ( not in use )
*******************************************************************************/
function decode_datum($string)
	{
	
	return $string;
	}
	
/***************************************************************************//**
* Logging
*******************************************************************************/
function logging($text)
	{
	$datei = "withings.log";
	$logdatei = IPS_GetKernelDir() . "logs\\" . $datei;
	$datei = fopen($logdatei,"a+");
	fwrite($datei, date("d.m.Y H:i:s - "). $text . chr(13));
	fclose($datei);

	}
//******************************************************************************

/***************************************************************************//**
* @}
* @}
*******************************************************************************/
?>