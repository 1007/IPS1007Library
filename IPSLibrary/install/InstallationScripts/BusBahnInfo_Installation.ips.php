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
* @version    Version 1.0.0
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

  $version = "1.0.0";
  $moduleManager->VersionHandler()->CheckModuleVersion('IPS','2.50');
	$moduleManager->VersionHandler()->CheckModuleVersion('IPSModuleManager','2.50.1');
  $moduleManager->VersionHandler()->SetModuleVersion($version);

  IPSUtils_Include ("IPSInstaller.inc.php",            "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",     "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("BusBahnInfo_Configuration.inc.php",   "IPSLibrary::config::modules::Informationen::BusBahnInfo");

	$AppPath       = "Program.IPSLibrary.app.modules.Informationen.BusBahnInfo";
	$DataPath      = "Program.IPSLibrary.data.modules.Informationen.BusBahnInfo";
	$ConfigPath    = "Program.IPSLibrary.config.modules.Informationen.BusBahnInfo";
	$VisuPath      = "Visualization.WebFront.Informationen.BusBahnInfo";
	$cssDefault    = IPS_GetKernelDir()."webfront\user\BusBahnInfo\Default\BusBahnInfo.css";
	$cssFile       = IPS_GetKernelDir()."webfront\user\BusBahnInfo\BusBahnInfo.css";

	echo "--- Create BusBahnInfo -------------------------------------------------------------------\n";

  $CategoryIdData = CreateCategoryPath($DataPath);
	$CategoryIdApp  = CreateCategoryPath($AppPath);
	$CategoryIdVisu = CreateCategoryPath($VisuPath);

  $refreshScriptId = IPS_GetScriptIDByName('busbahninforefresh', $CategoryIdApp );
  CreateTimer_CyclicBySeconds ("ScriptTimer",$refreshScriptId,REFRESH_TIME,true);
  IPS_SetScriptTimer($refreshScriptId,REFRESH_TIME);

  	if ( file_exists($cssFile))
	   {
	   echo "\nUser-CSS-File existiert. Wird nicht ueberschrieben";
		}
	else
	   {
	   echo "\nUser-CSS-File existiert nicht . Default wird kopiert";
	   copy($cssDefault,$cssFile);
		}

  IPS_RunScript($refreshScriptId);

/***************************************************************************//**
* @}
*******************************************************************************/

?>