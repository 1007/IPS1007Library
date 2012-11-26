<?
/***************************************************************************//**
* @ingroup withingsinfo 
* @{   		
* @defgroup withingsinfo_installation WithingsInfo Installation
* @{
*
* @file       WithingsInfo_Installation.ips.php
* @author     1007
* @version    Version 1.0.0
* @date       01.03.2012
* 
* @brief Installation fuer WithingsInfo
* 
*******************************************************************************/


	
	if (!isset($moduleManager)) {
		IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');

		echo 'ModuleManager Variable not set --> Create "default" ModuleManager';
		$moduleManager = new IPSModuleManager('WithingsInfo');
	}

  $moduleManager->VersionHandler()->CheckModuleVersion('IPS','2.50');
	$moduleManager->VersionHandler()->CheckModuleVersion('IPSModuleManager','2.50.1');
  //$moduleManager->VersionHandler()->SetModuleVersion("1.0.0");
  
  IPSUtils_Include ("IPSInstaller.inc.php",                "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",         "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("WithingsInfo_Configuration.inc.php",  "IPSLibrary::config::modules::Informationen::WithingsInfo");

	$AppPath       = "Program.IPSLibrary.app.modules.Informationen.WithingsInfo";
	$DataPath      = "Program.IPSLibrary.data.modules.Informationen.WithingsInfo";
	$ConfigPath    = "Program.IPSLibrary.config.modules.Informationen.WithingsInfo";
	$VisuPath      = "Visualization.WebFront.Informationen.WithingsInfo";
	$MobilePath    = "Visualization.Mobile.Informationen.WithingsInfo";

  echo "--- Create WithingsInfo -------------------------------------------------------------------\n";
	
  
   $CategoryIdData   = CreateCategoryPath($DataPath);
	$CategoryIdApp    = CreateCategoryPath($AppPath);
	$CategoryIdVisu   = CreateCategoryPath($VisuPath);
	$CategoryIdMobile = CreateCategoryPath($MobilePath,100);

  $refreshScriptId = IPS_GetScriptIDByName('withingsinforefresh', $CategoryIdApp );
  CreateTimer_CyclicByMinutes ("REFRESH",$refreshScriptId,REFRESH_TIME,true);

  create_profile("WithingsInfo_cm"			," cm",2);
  create_profile("WithingsInfo_kg"			," kg",2,1);
  create_profile("WithingsInfo_prozent" 	," %" ,2,1);
  create_profile("WithingsInfo_bmi" 		," "  ,2,1);
  create_profile("WithingsInfo_mmhg" 		," mmHg"  ,2);

  for($x=1;$x<10;$x++)
    {
    $userdata     = constant("USER".$x."_NAME");
    $uservisu     = constant("USER".$x."_ANZEIGE");

    if( strlen($userdata) > 0 )
      { 
      $id     = CreateCategoryPath($DataPath.".$userdata",$x+30);
      $visuid = CreateCategoryPath($VisuPath.".$uservisu",50+$x);
      $mobid  = CreateCategoryPath($MobilePath.".$uservisu",50+$x);


      if ( $id)
        { 
        $idd = CreateVariable("Name"               , 3 /*String*/,  $id,110 ,'~String', null, 0);
        CreateLink ("Name", $idd, $visuid, 10);
        CreateLink ("Name", $idd, $mobid, 10);

        CreateVariable("Pseudonym"          , 3 /*String*/,  $id,120 ,'~String', null, 0);
        $idd = CreateVariable("Geschlecht"         , 3 /*String*/,  $id,130 ,'~String', null, 0);
        CreateLink ("Geschlecht", $idd, $visuid, 20);
        CreateLink ("Geschlecht", $idd, $mobid, 20);

        $idd = CreateVariable("Fettmassenanzeige"  , 3 /*String*/,  $id,150 ,'~String', null, 0);
        CreateLink ("Fettmassenanzeige", $idd, $visuid, 30);
        CreateLink ("Fettmassenanzeige", $idd, $mobid, 30);

        $idd = CreateVariable("Geburtstag"         , 3 /*String*/,  $id,160 ,'~String', null, 0);
        CreateLink ("Geburtstag", $idd, $visuid, 40);
        CreateLink ("Geburtstag", $idd, $mobid, 40);

        $idd = CreateVariable("Groessendatum"      , 3 /*String*/,  $id,170 ,'~String', null, 0);
        CreateLink ("Groessendatum", $idd, $visuid, 50);
        CreateLink ("Groessendatum", $idd, $mobid, 50);

        $idd = CreateVariable("Groesse"            , 1 /*Integer*/,$id,180 ,'WithingsInfo_cm', null , 0);
        CreateLink ("Groesse", $idd, $visuid, 60);
        CreateLink ("Groesse", $idd, $mobid, 60);

        
        if ( constant("USER".$x."_WAAGE") == true )
          {
          $subid      = CreateCategory("WAAGE",$id,$x+30);
          $visusubid  = CreateCategory("WAAGE",$visuid,$x+130);
          $mobsubid  = CreateCategory("WAAGE",$mobid,$x+130);

          $idd = CreateVariable("Uhrzeit"        , 3 /*String*/,  $subid,10 ,'~String', null, 0);
          CreateLink ("Uhrzeit", $idd, $visusubid, 10);
          CreateLink ("Uhrzeit", $idd, $mobsubid, 10);

          $idd = CreateVariable("Gewicht"        , 2 /*Float*/  ,  $subid,102,'WithingsInfo_kg', null, 0);
          CreateLink ("Gewicht", $idd, $visusubid, 20);
          CreateLink ("Gewicht", $idd, $mobsubid, 20);

          $idd = CreateVariable("BMI"            , 2 /*Float*/  ,  $subid,103,'WithingsInfo_bmi', null, 0);
          CreateLink ("BMI", $idd, $visusubid, 30);
          CreateLink ("BMI", $idd, $mobsubid, 30);

          $idd = CreateVariable("Fettfrei"       , 2 /*Float*/  ,  $subid,104,'WithingsInfo_kg', null, 0);
          CreateLink ("Fettfrei", $idd, $visusubid, 40);
          CreateLink ("Fettfrei", $idd, $mobsubid, 40);

          $idd = CreateVariable("Fettanteil"     , 2 /*Float*/  ,  $subid,105,'WithingsInfo_kg', null, 0);
          CreateLink ("Fettanteil", $idd, $visusubid, 50);
          CreateLink ("Fettanteil", $idd, $mobsubid, 50);

          $idd = CreateVariable("Fettprozent"    , 2 /*Float*/  ,  $subid,106,'WithingsInfo_prozent', null, 0);
          CreateLink ("Fettprozent", $idd, $visusubid, 60);
          CreateLink ("Fettprozent", $idd, $mobsubid, 60);

          }
        if ( constant("USER".$x."_BLUTDRUCK") == true )
          {
          $subid      = CreateCategory("BLUTDRUCK",$id,$x+30);
          $visusubid  = CreateCategory("BLUTDRUCK",$visuid,$x+130);
          $mobsubid   = CreateCategory("BLUTDRUCK",$mobid,$x+130);

          $idd = CreateVariable("Uhrzeit"        , 3 /*String*/,  $subid,10 ,'~String', null, 0);
          CreateLink ("Uhrzeit", $idd, $visusubid, 10);
          CreateLink ("Uhrzeit", $idd, $mobsubid, 10);

          $idd = CreateVariable("Diastolic"      , 1 /*Integer*/,  $subid,101 ,'WithingsInfo_mmhg', null, 0);
          CreateLink ("Diastolic", $idd, $visusubid, 20);
          CreateLink ("Diastolic", $idd, $mobsubid, 20);

          $idd = CreateVariable("Systolic"       , 1 /*Integer*/,  $subid,102 ,'WithingsInfo_mmhg', null, 0);
          CreateLink ("Systolic", $idd, $visusubid, 30);
          CreateLink ("Systolic", $idd, $mobsubid, 30);

          $idd = CreateVariable("Puls"           , 1 /*Integer*/,  $subid,103 ,'~String', null, 0);
          CreateLink ("Puls", $idd, $visusubid, 40);
          CreateLink ("Puls", $idd, $mobsubid, 40);
          }

        } 
                 
          
        }
    }
  
  IPS_RunScript($refreshScriptId);
  
  ReloadAllWebFronts() ;

/***************************************************************************//**
* Ein Profil erstellen
*******************************************************************************/
function create_profile($name,$suffix,$typ,$digits=0)
  {
  @IPS_DeleteVariableProfile($name);
	IPS_CreateVariableProfile($name, $typ);       
	IPS_SetVariableProfileText($name, "",$suffix);
 
   if ( $digits > 0 )
   	IPS_SetVariableProfileDigits($name, $digits);
  }

/***************************************************************************//**
* @}
* @}
*******************************************************************************/
?>