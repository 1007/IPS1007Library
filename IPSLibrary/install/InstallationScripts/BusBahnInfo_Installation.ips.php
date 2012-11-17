<?
/***************************************************************************//**
* @defgroup informationen Informations-Module
* @brief Uebersicht ueber alle Informationsmodule
* @defgroup busbahninfo BusBahnInformationen
* @ingroup informationen
* @{
* @defgroup busbahninfo_installation BusBahnInfo Installation
* @ingroup busbahninfo
* @{
*
* Installations Script fuer BusBahnInfo
*
* @file       BusBahnInfo_Installation.ips.php
* @author     1007
* @version    Version 1.0.1
* @date       04.03.2012
*
* @section requirements_component Installations Voraussetzungen
* - IPS Kernel >= 2.50
* - IPSModuleManager >= 2.50.1
*
*******************************************************************************/
  
	if (!isset($moduleManager))
    {
		IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');

    echo 'ModuleManager Variable not set --> Create "default" ModuleManager';
		$moduleManager = new IPSModuleManager('BusBahnInfo');
    }

  	
  	$moduleManager->VersionHandler()->CheckModuleVersion('IPS','2.50');
	$moduleManager->VersionHandler()->CheckModuleVersion('IPSModuleManager','2.50.1');
  	//$moduleManager->VersionHandler()->SetVersion($version);

  	IPSUtils_Include ("IPSInstaller.inc.php",            "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",     "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("BusBahnInfo_Configuration.inc.php","IPSLibrary::config::modules::Informationen::BusBahnInfo");

  	GLOBAL $stationen;
  
	$AppPath       = "Program.IPSLibrary.app.modules.Informationen.BusBahnInfo";
	$DataPath      = "Program.IPSLibrary.data.modules.Informationen.BusBahnInfo";
	$ConfigPath    = "Program.IPSLibrary.config.modules.Informationen.BusBahnInfo";
	$VisuPath      = "Visualization.WebFront.Informationen.BusBahnInfo";
	$cssDefault    = IPS_GetKernelDir()."webfront\user\BusBahnInfo\Default\BusBahnInfo.css";
	$cssFile       = IPS_GetKernelDir()."webfront\user\BusBahnInfo\BusBahnInfo.css";

	echo "--- Create BusBahnInfo --------------------------------------------\n";

  	$CategoryIdData = CreateCategoryPath($DataPath);
	$CategoryIdApp  = CreateCategoryPath($AppPath);

  $webfrontScriptId = IPS_GetScriptIDByName('busbahninfowebfront', $CategoryIdApp );

	$CategoryIdVisu = CreateCategoryPath($VisuPath);

  $refreshScriptId = IPS_GetScriptIDByName('busbahninforefresh', $CategoryIdApp );
  CreateTimer_CyclicBySeconds ("ScriptTimer",$refreshScriptId,REFRESH_TIME,true);
  IPS_SetScriptTimer($refreshScriptId,REFRESH_TIME);

  	create_profile("BusBahnInfo_Stationen"	,"",1);
  	create_profile("BusBahnInfo_Anzeigetafeln","",1);

	$id = 0;
	
	$bahnhofsliste = array();
	foreach( $stationen as $station )
	   {
	   if ( $station[1] != '' )
	   	array_push($bahnhofsliste,$station[1]);
	   }
	   
	$bahnhofsliste = array_unique($bahnhofsliste);
	
  	foreach( $bahnhofsliste as $bahnhof )
  	   {
  	   IPS_SetVariableProfileAssociation("BusBahnInfo_Stationen", $id,$bahnhof, "", 0xaaaaaa);
  		$id++;
		}


		
   $Id  = CreateVariable("Bahnhof/Station", 1 /* */,  $CategoryIdVisu,1,"BusBahnInfo_Stationen", $webfrontScriptId, 0);

   $Id  = CreateVariable("Anzeigetafel", 1 /* */,  $CategoryIdVisu,2,"BusBahnInfo_Anzeigetafeln", $webfrontScriptId, 1);
    


  	if ( file_exists($cssFile))
	   {
	   echo "\nUser-CSS-File existiert. Wird nicht ueberschrieben";
		}
	else
	   {
	   echo "\nUser-CSS-File existiert nicht . Default wird kopiert";
	   copy($cssDefault,$cssFile);
		}

	echo "\n--- Create Webfront -----------------------------------------------\n";
  	$WFC10_Enabled    = $moduleManager->GetConfigValue('Enabled', 		 'WFC10');
  	$WFC10_Path       = $moduleManager->GetConfigValue('Path', 			 'WFC10');
  	$WFC10_WebFrontID = $moduleManager->GetConfigValueInt('WebFrontID','WFC10');
  	$WFC10_TabParent  = $moduleManager->GetConfigValue('TabParent', 	 'WFC10');
  	$WFC10_TabName    = $moduleManager->GetConfigValue('TabName', 		 'WFC10');
  	$WFC10_TabItem    = $moduleManager->GetConfigValue('TabItem', 		 'WFC10');
  	$WFC10_TabIcon    = $moduleManager->GetConfigValue('TabIcon', 		 'WFC10');
  	$WFC10_TabOrder   = $moduleManager->GetConfigValueInt('TabOrder',  'WFC10');
  	$WFC10_ConfigId   = $moduleManager->GetConfigValueIntDef('ID', 	 'WFC10', GetWFCIdDefault());


	if ( $WFC10_WebFrontID > 0 )
      $WFC10_ConfigId = $WFC10_WebFrontID;

 	if ($WFC10_Enabled)
    	{
    	$categoryId_WebFront = CreateCategoryPath($WFC10_Path);

    	DeleteWFCItems($WFC10_ConfigId, $WFC10_TabItem);
      
		  CreateWFCItemCategory ($WFC10_ConfigId, $WFC10_TabItem, $WFC10_TabParent,$WFC10_TabOrder, $WFC10_TabName, $WFC10_TabIcon, $CategoryIdVisu ) ;

		}


	IPS_RunScript($refreshScriptId);
  //IPS_RunScript($webfrontScriptId);

  
  
	ReloadAllWebFronts();
  
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
*******************************************************************************/

?>