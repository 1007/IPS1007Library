<?
/***************************************************************************//**
* @ingroup plugwise 
* @{   		
* @defgroup plugwise_installation Plugwise Installation
* @{
*
* @file       Plugwise_Installation.ips.php
* @author     
* @version    Version 1.0.0
* @date       05.05.2012
* 
* @brief Installation fuer Plugwise
* 
*******************************************************************************/
  GLOBAL $CircleGroups;

	
	if (!isset($moduleManager)) {
		IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');

		echo 'ModuleManager Variable not set --> Create "default" ModuleManager';
		$moduleManager = new IPSModuleManager('Plugwise');
	}

  $moduleManager->VersionHandler()->CheckModuleVersion('IPS','2.50');
	$moduleManager->VersionHandler()->CheckModuleVersion('IPSModuleManager','2.50.1');
  $moduleManager->VersionHandler()->SetModuleVersion("1.0.0");
  
  IPSUtils_Include ("IPSInstaller.inc.php",                "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",         "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("Plugwise_Configuration.inc.php",      "IPSLibrary::config::hardware::Plugwise");

	$AppPath        = "Program.IPSLibrary.app.hardware.Plugwise";
	$DataPath       = "Program.IPSLibrary.data.hardware.Plugwise";
	$ConfigPath     = "Program.IPSLibrary.config.hardware.Plugwise";
	$VisuPath       = "Visualization.WebFront.Hardware.Plugwise";
	$MobilePath     = "Visualization.Mobile.Hardware.Plugwise";
  $HardwarePath   = "Hardware.Plugwise";
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
  
  echo "--- Create Plugwise -------------------------------------------------------------------\n";
	
  
  $CategoryIdData   = CreateCategoryPath($DataPath);
	$CategoryIdApp    = CreateCategoryPath($AppPath);
	$CategoryIdVisu   = CreateCategoryPath($VisuPath);
	$CategoryIdMobile = CreateCategoryPath($MobilePath,100);
	$CategoryIdHw     = CreateCategoryPath($HardwarePath);
  $CategoryIdCData  = CreateCategoryPath($CircleDataPath);

  EmptyCategory($CategoryIdVisu);
  EmptyCategory($CategoryIdMobile);

  // Serial Port erstellen
  $comid = @IPS_GetInstanceIDByName('PlugwiseCOM',0);
  if ( !$comid )
    $comid = IPS_CreateInstance ('{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}');
	if ( $comid )
	   {
	    IPS_SetName($comid,'PlugwiseCOM');
	    COMPort_SetBaudRate($comid,'115200');
	    COMPort_SetStopBits($comid,'1');
	    COMPort_SetDataBits($comid,'8');
	    COMPort_SetParity($comid,'N');      
      COMPort_SetPort($comid,COMPORT);
      COMPort_SetOpen($comid,true);
      
      IPS_ApplyChanges($comid);
     }
  else
    echo "\nCOM-Port konnte nicht angelegt werden ";

  // Cutter erstellen
  $cutterid = @IPS_GetInstanceIDByName('PlugwiseCUTTER',0);
  if ( !$cutterid )
    $cutterid = IPS_CreateInstance ('{AC6C6E74-C797-40B3-BA82-F135D941D1A2}');
	if ( $cutterid )
	   {
	    IPS_SetName($cutterid,'PlugwiseCUTTER');
      
      Cutter_SetParseType($cutterid,0);
      Cutter_SetLeftCutChar($cutterid,"\x05\x05\x03\x03");
      Cutter_SetRightCutChar($cutterid,"\x0D\x0A");
      Cutter_SetTimeout($cutterid,0);
    
      IPS_ApplyChanges($cutterid);
      if ( $comid )
        IPS_ConnectInstance($cutterid,$comid);
     }
  else
    echo "\nCutter konnte nicht angelegt werden ";


  $id = @IPS_GetInstanceIDByName('PlugwiseRegisterVariable',$CategoryIdHw);
  if ( !$id )
    $id = IPS_CreateInstance ('{F3855B3C-7CD6-47CA-97AB-E66D346C037F}');
	if ( $id )
    {
    IPS_SetName($id,'PlugwiseRegisterVariable');
    IPS_SetParent($id,$CategoryIdHw);
    if ( $cutterid )
      IPS_ConnectInstance($id,$cutterid);

    }
    
  $ScriptId = IPS_GetScriptIDByName('Plugwise_Controller', $CategoryIdApp );
  RegVar_SetRXObjectID($id, $ScriptId);
  IPS_ApplyChanges($id);
  
  $id = CreateTimer_CyclicByMinutes ("REFRESH",$ScriptId,REFRESH_TIME,true);
  IPS_SetEventCyclic($id, 2 /*Daily*/, 1 /*Unused*/,0 /*Unused*/,0/*Unused*/,2/*TimeType Minutes*/,REFRESH_TIME/*Minutes*/);
  

  $ScriptId = IPS_GetScriptIDByName('Plugwise_ReadBuffer', $CategoryIdApp );
  $id = CreateTimer_CyclicByMinutes ("REFRESH",$ScriptId,60,true);
  IPS_SetEventCyclic($id, 2 /*Daily*/, 1 /*Unused*/,0 /*Unused*/,0/*Unused*/,2/*TimeType Minutes*/,60/*Minutes*/);
  IPS_SetEventCyclicTimeBounds($id,mktime(0,59,30),0);

  //****************************************************************************
  //  CycleGroups im Webfront erstellen
  //****************************************************************************
  foreach ( $CircleGroups as $cycle )
      {
      if ( $cycle[0] != "" )
        {
        
        $id   = CreateCategory($cycle[2],$CategoryIdVisu,0);
        $id   = CreateCategory($cycle[1],$id,0);

        $id   = CreateCategory($cycle[2],$CategoryIdMobile,0);
        $id   = CreateCategory($cycle[1],$id,0);
        
        }
      } 
  
  //****************************************************************************
  // Jetzt Cycles suchen
  //****************************************************************************
	IPSUtils_Include ("Plugwise_Include.ips.php",      "IPSLibrary::app::hardware::Plugwise");
  PW_SendCommand("0008");
  
  
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