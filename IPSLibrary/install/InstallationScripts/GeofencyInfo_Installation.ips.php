<?
	/*
	 * This file is part of the IPSLibrary.
	 *
	 * The IPSLibrary is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published
	 * by the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * The IPSLibrary is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with the IPSLibrary. If not, see http://www.gnu.org/licenses/gpl.txt.
	 */


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
  
  GLOBAL $DeviceConfig;
  GLOBAL $ActionConfig;
         
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

  $ImgRedArrowDefault   = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/arrowred.png";
	$ImgRedArrow          = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/arrowred.png";
  $ImgGreenArrowDefault = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/arrowgreen.png";
	$ImgGreenArrow        = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/arrowgreen.png";
  $ImgGeofencyDefault   = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/geofency.png";
	$ImgGeofency          = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/geofency.png";
  $ImgGeoLocDefault     = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/geoloc.png";
	$ImgGeoLoc            = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/geoloc.png";
  $ImgClockDefault      = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/uhr.png";
	$ImgClock             = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/uhr.png";
  $ImgEmptyDefault      = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/leer.png";
	$ImgEmpty             = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/leer.png";
  $ImgScriptOKDefault   = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/scriptok.png";
	$ImgScriptOK          = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/scriptok.png";
  $ImgScriptNOKDefault  = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/default/scriptnok.png";
	$ImgScriptNOK         = IPS_GetKernelDir()."webfront/user/GeofencyInfo/Images/scriptnok.png";

  $cssFileDefault       = IPS_GetKernelDir()."webfront/user/GeofencyInfo/css/default/Geofency.css";
  $cssFile              = IPS_GetKernelDir()."webfront/user/GeofencyInfo/css/Geofency.css";


  //****************************************************************************
  // Variablen bauen
  //****************************************************************************
  $LogId  = CreateVariable("Log", 3,$CategoryIdData, 0, '~HTMLBox',false);  
  $LogIdLink = CreateLink("Log",$LogId,$CategoryIdVisu,0);
  
  foreach($DeviceConfig as $Device)
    {
    $Number = $Device[0];
    $aktiv  = $Device[1];
    $Name   = $Device[2];      
    $Device[3] = false;
    
    if ( $aktiv)
      {
      $Id     = CreateVariable($Name."Content", 3,$CategoryIdData, 0, '~HTMLBox',false);  

      $IdLink = CreateLink($Name,$Id,$CategoryIdVisu,0);
      $Device[3] = $IdLink;
      }
    
    }
      

  //****************************************************************************
  // Webfront bauen
  //****************************************************************************
  $WFC_Enabled        = $moduleManager->GetConfigValue('Enabled', 		 'WFC10');
  $WFC_Path           = $moduleManager->GetConfigValue('Path', 			   'WFC10');
  //$WFC_WebFrontID     = $moduleManager->GetConfigValueInt('WebFrontID','WFC10');
  $WFC_TabPaneParent  = $moduleManager->GetConfigValue('TabPaneParent', 	 'WFC10');
  $WFC_TabPaneName    = $moduleManager->GetConfigValue('TabPaneName', 		 'WFC10');
  $WFC_TabPaneItem    = $moduleManager->GetConfigValue('TabPaneItem', 		 'WFC10');
  $WFC_TabPaneIcon    = $moduleManager->GetConfigValue('TabPaneIcon', 		 'WFC10');
  $WFC_TabPaneOrder   = $moduleManager->GetConfigValueInt('TabPaneOrder',  'WFC10');
  $WFC_ConfigId       = $moduleManager->GetConfigValueIntDef('ID', 	   'WFC10', GetWFCIdDefault());

   if ( $WFC_TabPaneItem == "" )
    $WFC_TabPaneItem = "Geofency";

  if ( $WFC_TabPaneParent == "" )
    $WFC_TabPaneItem = "roottp";

  if ( $WFC_TabPaneName == "" )
    $WFC_TabPaneName = "Geofency";


	if ( $WFC_ConfigId == 0 )
      $WFC_ConfigId = GetWFCIdDefault();




  $ItemList = WFC_GetItems($WFC_ConfigId);

  	
  foreach ($ItemList as $Item)
    {
    $pos = strpos($Item['ID'], $WFC_TabPaneItem);
    if ($pos === false)
	 	 {//echo "\nNicht gefunden".$Item['ID'];
		  }
	 else
	 	{	DeleteWFCItem($WFC_ConfigId, $Item['ID']);
     IPS_ApplyChanges($WFC_ConfigId);
		}
	 }
  
	if ($WFC_Enabled)
	 {  
      
    //CreateWFCItemCategory ($WFC_ConfigId, $WFC_TabPaneItem, $WFC_TabPaneParent, $WFC_TabPaneOrder, $WFC_TabPaneName, $WFC_TabPaneIcon, $CategoryIdVisu);
    CreateWFCItemTabPane ($WFC_ConfigId, $WFC_TabPaneItem, $WFC_TabPaneParent, $WFC_TabPaneOrder, $WFC_TabPaneName, $WFC_TabPaneIcon); 


    $Configuration = '{"title":"Info","name":"'.$WFC_TabPaneItem.'Info","baseID":'.$LogIdLink.',"icon":"Information"}';
    CreateWFCItem($WFC_ConfigId,$WFC_TabPaneItem.'Info', $WFC_TabPaneItem, 99 , "", 'Information' , 'ContentChanger' ,$Configuration );

    foreach($DeviceConfig as $Device)
      {
      $Number = $Device[0];
      $aktiv  = $Device[1];
      $Name   = $Device[2];      
    
      if ( $aktiv)
        {        
        $id = @IPS_GetLinkIDByName($Name,$CategoryIdVisu);
        if ( $id )
          {
          //$Configuration = '{"title":"'.$Name.'","name":"'.$WFC_TabPaneItem.$Name.',"baseID":'.$id.'}';
          //CreateWFCItem($WFC_ConfigId,$WFC_TabPaneItem.$Name, $WFC_TabPaneItem, $Number , "", '' , 'ContentChanger' ,$Configuration );
          $Configuration = '{"title":"'.$Name.'","name":"'.$WFC_TabPaneItem.'Info1","baseID":'.$id.',"icon":"Notebook"}';
          CreateWFCItem($WFC_ConfigId,$WFC_TabPaneItem.$Name, $WFC_TabPaneItem, $Number , "Shuffle", '' , 'ContentChanger' ,$Configuration );

          }
        }
    
      }

    IPS_ApplyChanges($WFC_ConfigId);

  }
  
  
  
    if ( !file_exists($ImgRedArrow))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgRedArrowDefault,$ImgRedArrow);
		 }
    if ( !file_exists($ImgGreenArrow))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgGreenArrowDefault,$ImgGreenArrow);
		 }
    if ( !file_exists($ImgGeofency))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgGeofencyDefault,$ImgGeofency);
		 }
    if ( !file_exists($ImgClock))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgClockDefault,$ImgClock);
		 }
    if ( !file_exists($ImgGeoLoc))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgGeoLocDefault,$ImgGeoLoc);
		 }
    if ( !file_exists($ImgScriptOK))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgScriptOKDefault,$ImgScriptOK);
		 }
    if ( !file_exists($ImgScriptNOK))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgScriptNOKDefault,$ImgScriptNOK);
		 }
    if ( !file_exists($ImgEmpty))
	   {
	   echo "\nImageFile existiert nicht . Default wird kopiert";
	   copy($ImgEmptyDefault,$ImgEmpty);
		 }		 
    if ( !file_exists($cssFile))
	   {
	   echo "\nCSSFile existiert nicht . Default wird kopiert";
	   copy($cssFileDefault,$cssFile);
		 }		 
   
  
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