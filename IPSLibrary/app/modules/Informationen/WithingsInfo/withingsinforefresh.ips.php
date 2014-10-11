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
 	IPSUtils_Include ("IPSLogger.inc.php",       "IPSLibrary::app::core::IPSLogger");

   $CategoryPath = "Program.IPSLibrary.data.modules.Informationen.WithingsInfo";

   $CategoryWithingsData = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.WithingsInfo");
   
	$debug = DEBUG_MODE;
	$log 	 = LOG_MODE;

	// Teste ob Withings API OK
	//echo WBSAPI_OnceProbe ();
	if (WBSAPI_OnceProbe ())
		{
		if ($debug) IPSLogger_Dbg(__FILE__, "Teste API : OK");
		if ($log) withingsinfologging ("Teste API : OK");
		}
	else
		{
		if ($debug) IPSLogger_Dbg(__FILE__, "Teste API : NOK");
		if ($log) withingsinfologging ("Teste API : NOK");
		exit(-1);
		}
	//***************************************************************************
	// Konfiguration ueberpruefen
	//***************************************************************************
	if ( MYMAIL == "" )
	   {
	   IPSLogger_Dbg(__FILE__, "MYMAIL in Konfigurationsfile nicht definiert");
	   exit(false);
		}
	if ( MYPASS == "" )
	   {
	   IPSLogger_Dbg(__FILE__, "MYPASS in Konfigurationsfile nicht definiert");
	   exit(false);
		}

	//***************************************************************************
	// Alle Userdaten bei Withings holen
	//***************************************************************************
	WBSAPI_AccountGetuserslist ( MYMAIL, MYPASS, $user );
	if ( !$user )
	   {
	   IPSLogger_Dbg(__FILE__, "Fehler beim Holen der User MYMAIL und MYPASS ueberpruefen");
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
			   if ( $debug ) IPSLogger_Dbg(__FILE__, "USER:".$per." wird geholt");

			   if ($log) withingsinfologging ("USER:".$per." wird geholt");
			   getwithingsdata($person,$x); break;
				}
			}
		if ( !$gefunden )
		   {
		   if ( $debug ) IPSLogger_Dbg(__FILE__, "USER:".$shortname." nicht in Konfiguration");
			if ($log) withingsinfologging ("USER:".$shortname." nicht in Konfiguration");
			}

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
	$gewicht       = 0;
	$fettfrei      = 0;
	$fettanteil    = 0;
	$fettprozent   = 0;
	$diastolic     = 0;
	$systolic      = 0;
	$puls          = 0;
	

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
		if ($log) withingsinfologging ("USER:".$shortname." nicht public");
		IPSLogger_Dbg(__FILE__, "USER:".$shortname." nicht public");
	   return false;
	   }
	
	
	$userpath = $CategoryPath .".".$shortname;
   $userwaagepath = $userpath . ".WAAGE";
   $userblutdruckpath = $userpath . ".BLUTDRUCK";

	$id = IPSUtil_ObjectIDByPath($userpath . ".Name",true);
	if ( !$id )
	   {
	   IPSLogger_Dbg(__FILE__, "User nicht in Data. Installroutine ausfuehren !");

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

	$limit      = 1;     // Anzahl
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

		if ($log) withingsinfologging ("Groessedatum:".$groessedatum);
		if ($log) withingsinfologging ("Groesse:".$groesse);

		}
	else
	   {
		if ($log) withingsinfologging ("USER:" .$shortname." keine GroesseDaten ");
		IPSLogger_Dbg(__FILE__, "USER:".$shortname." keine GroesseDaten ");
		}
	
	//***************************************************************************
	// Gewicht updaten
	//***************************************************************************
	$meastype 	= false; 		// Alles
	$devtype 	= 1;
	$data = array();
	
	if ( constant('USER'.$usernummer.'_WAAGE') )
	   {
		$limit = 4;
		WBSAPI_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$devtype,$meastype,$category,$limit);
	
	   }
	if ( $data )
		{ 
		if ( $debug ) IPSLogger_Dbg(__FILE__, "USER:".$shortname." Gewichtsdaten vorhanden ");
		if ($log) withingsinfologging ("USER:".$shortname." Gewichtsdaten vorhanden ");

		foreach ( $data[0]['measures'] as $messung )
	   	{
	   	
			$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );

			if ($log) withingsinfologging ("Type:".$messung['type']." Wert:".$val);

			if ( $messung['type'] == 1 )  $gewicht 		= round ($val,2);
			if ( $messung['type'] == 5 )  $fettfrei 		= round ($val,2);
			if ( $messung['type'] == 6 )  $fettprozent 	= round ($val,2);
			if ( $messung['type'] == 8 )  $fettanteil  	= round ($val,2);
			if ( $messung['type'] == 9 )  $diastolic 		= round ($val,2);
			if ( $messung['type'] == 10 ) $systolic 		= round ($val,2);
			if ( $messung['type'] == 11 ) $pulse 			= round ($val,2);
	      }

			if ( $gewicht > 0  )
			   {
				$gewichtdatum = date('d.m.Y H:i:s',$data[0]['date']);
      		$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Uhrzeit");
				SetValueString($id,$gewichtdatum);

      		$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Gewicht");
				SetValueFloat($id,$gewicht);

         	$bmi = round($gewicht/(($groesse/100)*($groesse/100)),2);
      		$id = IPSUtil_ObjectIDByPath($userwaagepath . ".BMI");
				SetValueFloat($id,$bmi);
				}

			if ( $fettfrei > 0  )
			   {
      		$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Fettfrei");
				SetValueFloat($id,$fettfrei);
				}

			if ( $fettprozent > 0  )
			   {
      		$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Fettprozent");
				SetValueFloat($id,$fettprozent);
				}

			if ( $fettanteil > 0 )
			   {
      		$id = IPSUtil_ObjectIDByPath($userwaagepath . ".Fettanteil");
				SetValueFloat($id,$fettanteil);
				}




		}
	else
	   {
		if ($log) withingsinfologging ("USER:".$shortname." keine GewichtsDaten ");
		IPSLogger_Dbg(__FILE__, "USER:".$shortname." keine GewichtsDaten ");
		}

	//***************************************************************************
	// Blutdruck updaten
	//***************************************************************************
	$meastype 	= false; 		// Alles
	$devtype 	= 4;
	
	$data = array();
	
	if ( constant('USER'.$usernummer.'_BLUTDRUCK') )
	   {
	   $limit      = 3;
		WBSAPI_MeasureGetmeas ( $personid, $publickey, $data, $startdate,$enddate,$devtype,$meastype,$category,$limit);
		}

	if ( $data )
		{
		if ( $debug ) IPSLogger_Dbg(__FILE__, "USER:".$shortname." Blutdruckdatendaten vorhanden ");
		if ($log) withingsinfologging ("USER:".$shortname." Blutdruckdatendaten vorhanden ");

		foreach ( $data[0]['measures'] as $messung )
	   	{
			$val = floatval ( $messung['value'] ) * floatval ( "1e".$messung['unit'] );
			
			if ($log) withingsinfologging ("Type:".$messung['type']." Wert:".$val);

			if ( $messung['type'] == 9  ) $diastolic 		= round ($val,2);
			if ( $messung['type'] == 10 ) $systolic 		= round ($val,2);
			if ( $messung['type'] == 11 ) $pulse 			= round ($val,2);
	      }

			if ( $diastolic > 0 and $systolic > 0 and $pulse > 0 )
			   {
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
			
		}
	else
	   {
		if ($log) withingsinfologging ("USER:".$shortname." keine BlutdruckDaten ");
		IPSLogger_Dbg(__FILE__, "USER:".$shortname." keine BlutdruckDaten");
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
function withingsinfologging($text)
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
