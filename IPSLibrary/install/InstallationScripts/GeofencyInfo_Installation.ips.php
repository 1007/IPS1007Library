<?
/***************************************************************************//**
* @ingroup geofencyinfo 
* @{   		
* @defgroup geofencyinfo_installation GeofencyInfo Installation
* @{
*
* @file       GeofencyInfo_Installation.ips.php
* @author     
* @version    Version 1.0.0
* @date       11.12.2013
* 
* @brief Installation fuer GeofencyInfo
* 
*******************************************************************************/
  GLOBAL $CircleGroups;
         
	if (!isset($moduleManager)) {
		IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');

		echo 'ModuleManager Variable not set --> Create "default" ModuleManager';
		$moduleManager = new IPSModuleManager('GeofencyInfo');
	}

  $moduleManager->VersionHandler()->CheckModuleVersion('IPS','2.50');
	$moduleManager->VersionHandler()->CheckModuleVersion('IPSModuleManager','2.50.1');
  
  IPSUtils_Include ("IPSInstaller.inc.php",                "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",         "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("GeofencyInfo_Configuration.inc.php",  "IPSLibrary::config::modules::Informationen::GeofencyInfo");
  
  
	$AppPath        = "Program.IPSLibrary.app.modules.Informationen.GeofencyInfo";
	$DataPath       = "Program.IPSLibrary.data.modules.Informationen.GeofencyInfo";
	$ConfigPath     = "Program.IPSLibrary.config.modules.Informationen.GeofencyInfo";
	$VisuPath       = "Visualization.WebFront.Informationen.GeofencyInfo";
  
  $CategoryIdData   = CreateCategoryPath($DataPath);
	$CategoryIdApp    = CreateCategoryPath($AppPath);
	$CategoryIdVisu   = CreateCategoryPath($VisuPath);
  
  EmptyCategory($CategoryIdVisu);
  
  ReloadAllWebFronts() ;
  
  $error = error_get_last() ;
  $error = false;
  if ( !$error )
    {
    echo "\n<br>********************************************************************";
    echo "\n<br>Installation beendet. Es ist kein Fehler aufgetreten.";
    echo "\n<br>*********************************************************************";
    echo "\n<br>";
    }
  else
    {
    echo "\n<br>********************************************************************";
    echo "\n<br>Installation beendet. Fehler aufgetreten:";
    echo "\n<br>" . $error['message'];
    echo "\n<br>" . $error['file'];
    echo "\n<br>" . $error['line'];
    echo "\n<br>*********************************************************************";
    echo "\n<br>";    
    }
?>