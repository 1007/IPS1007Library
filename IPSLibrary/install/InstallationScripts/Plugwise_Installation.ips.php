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
	IPSUtils_Include ("Plugwise_Include.ips.php",      "IPSLibrary::app::hardware::Plugwise");

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

  //****************************************************************************
  // Serial Port erstellen
  //****************************************************************************
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
      
      @IPS_ApplyChanges($comid);
     }
  else
    echo "\nCOM-Port konnte nicht angelegt werden ";

  //****************************************************************************
  // Cutter erstellen
  //****************************************************************************  
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

  //****************************************************************************
  // Registervariable erstellen
  //****************************************************************************  
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
  
  //****************************************************************************
  // Registervariable Script zuordnen
  //****************************************************************************      
  $ScriptId = IPS_GetScriptIDByName('Plugwise_Controller', $CategoryIdApp );
  RegVar_SetRXObjectID($id, $ScriptId);
  IPS_ApplyChanges($id);
  
  
  //****************************************************************************
  // Timer fuer Plugwise_Controller setzten
  //****************************************************************************  
  $id = CreateTimer_CyclicByMinutes ("REFRESH",$ScriptId,REFRESH_TIME,true);
  IPS_SetEventCyclic($id, 2 /*Daily*/, 1 /*Unused*/,0 /*Unused*/,0/*Unused*/,2/*TimeType Minutes*/,REFRESH_TIME/*Minutes*/);
  
  //****************************************************************************
  // Timer fuer Plugwise_ReadBuffer
  //****************************************************************************  
  $ScriptId = IPS_GetScriptIDByName('Plugwise_ReadBuffer', $CategoryIdApp );
  $id = CreateTimer_CyclicByMinutes ("REFRESH",$ScriptId,60,true);
  IPS_SetEventCyclic($id, 2 /*Daily*/, 1 /*Unused*/,0 /*Unused*/,0/*Unused*/,2/*TimeType Minutes*/,60/*Minutes*/);
  IPS_SetEventCyclicTimeBounds($id,mktime(0,59,30),0);

  //****************************************************************************
  // Timer fuer Plugwise_Recalibrate
  //****************************************************************************  
  $ScriptId = IPS_GetScriptIDByName('Plugwise_Recalibrate', $CategoryIdApp );
  $id = CreateTimer_OnceADay ("REFRESH",$ScriptId,3,0);

  //****************************************************************************
  //  CycleGroups in Data erstellen anhand der Liste im Configurationsfile
  //****************************************************************************
  foreach ( $CircleGroups as $cycle )
      {
      if ( $cycle[0] != "" )
        { echo "\nCreate Circle". $cycle[0];
        createCircle($cycle[0],$CategoryIdCData);        
        }
      } 
 
   
  //****************************************************************************
  // Jetzt Cycles suchen
  //****************************************************************************
	IPSUtils_Include ("Plugwise_Include.ips.php",      "IPSLibrary::app::hardware::Plugwise");
  PW_SendCommand("0008");
  


  //****************************************************************************
  // Webfront bauen
  //****************************************************************************
  $WFC_Enabled        = $moduleManager->GetConfigValue('Enabled', 		 'WFC10');
  $WFC_Path           = $moduleManager->GetConfigValue('Path', 			   'WFC10');
  $WFC_WebFrontID     = $moduleManager->GetConfigValueInt('WebFrontID','WFC10');
  $WFC_TabPaneParent  = $moduleManager->GetConfigValue('TabParent', 	 'WFC10');
  $WFC_TabPaneName    = $moduleManager->GetConfigValue('TabName', 		 'WFC10');
  $WFC_TabPaneItem    = $moduleManager->GetConfigValue('TabItem', 		 'WFC10');
  $WFC_TabPaneIcon    = $moduleManager->GetConfigValue('TabIcon', 		 'WFC10');
  $WFC_TabPaneOrder   = $moduleManager->GetConfigValueInt('TabOrder',  'WFC10');
  $WFC_ConfigId       = $moduleManager->GetConfigValueIntDef('ID', 	   'WFC10', GetWFCIdDefault());
	if ( $WFC_WebFrontID > 0 )
      $WFC_ConfigId = $WFC_WebFrontID;

  $ItemList = WFC_GetItems($WFC_ConfigId);
	foreach ($ItemList as $Item)
    {
    $pos = strpos($Item['ID'], $WFC_TabPaneItem);
    if ($pos === false)
	 	 {//echo "\nNicht gefunden".$Item['ID'];
		  }
	 else
	 	{	DeleteWFCItem($WFC_ConfigId, $Item['ID']);
		}
	 }

	if ($WFC_Enabled)
	{
    
   $VisuID_menu  = CreateCategory("MENU",$CategoryIdVisu,10);
   $VisuID_data1 = CreateCategory("DATA1",$CategoryIdVisu,10);
   $VisuID_data2 = CreateCategory("DATA2",$CategoryIdVisu,10);
   $VisuID_graph = CreateCategory("GRAPH",$CategoryIdVisu,10);

  $graphid = CreateVariable("Highcharts", 3, $VisuID_graph, 0, "~HTMLBox", false, false);

  $IDGroups    = CreateDummyInstance("Gruppen",$VisuID_menu,10);
	$IDCircles   = CreateDummyInstance("Circles",$VisuID_menu,20);
  IPS_SetHidden($IDCircles,true);
  
	CreateWFCItemSplitPane ($WFC_ConfigId, $WFC_TabPaneItem, $WFC_TabPaneParent , 20 , $WFC_TabPaneName   , ''  , 1 /*Horizontal*/, 30 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
	CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-MENU", $WFC_TabPaneItem, 10, "Titel", $Icon="", $VisuID_menu, $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');

	CreateWFCItemSplitPane ($WFC_ConfigId, $WFC_TabPaneItem."-SPLITDATA", $WFC_TabPaneItem , 20 , $WFC_TabPaneName   , ''  , 0 , 48 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
	CreateWFCItemSplitPane ($WFC_ConfigId, $WFC_TabPaneItem."-SPLITDATA1", $WFC_TabPaneItem."-SPLITDATA" , 20 , $WFC_TabPaneName   , ''  , 1 , 50 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
	CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-DATA1", $WFC_TabPaneItem."-SPLITDATA1", 30, "Titel", $Icon="", $VisuID_data1, $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');
	CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-DATA2", $WFC_TabPaneItem."-SPLITDATA1", 40, "Titel", $Icon="", $VisuID_data2, $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');

  //$PageUri = WEBSERVER . "user/Plugwise/Plugwise_Highcharts_index.htm";
  CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-GRAPH", $WFC_TabPaneItem."-SPLITDATA", 40, "Titel", $Icon="", $graphid , $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');
  //CreateWFCItemExternalPage ($WFC_ConfigId, $WFC_TabPaneItem."-GRAPH", $WFC_TabPaneItem."-SPLITDATA", 50, "Titel", $Icon="", $PageUri, $BarBottomVisible='true' /*'true' or 'false'*/);


	CreateProfile_Associations ("Plugwise_MenuItem", array(
												0	=> "",
												1 	=> "-----"
												),
												'', array(
												0  =>	0x666666,
												1  =>	0x00FFCC
												));


  $ActionScriptId = IPS_GetScriptIDByName('Plugwise_Webfront', $CategoryIdApp );

	//***************************************************************************
	// Gruppenmenu erstellen
	//***************************************************************************
	$groups = $CircleGroups[2];
	$groups = array_unique($groups);
   $array = array();
   foreach ( $CircleGroups as $group ) array_push($array,$group[2]);
   $groups = array_unique($array);
	$x = 10;
   foreach ( $groups as $group )
   	{
      if ( $group != "" )
      	{
         $id = CreateVariable($group, 1, $IDGroups, 0, "Plugwise_MenuItem", $ActionScriptId, false);
         $x = $x + 10;
         }
      }

	//***************************************************************************
	// Circlesmenu erstellen
	//***************************************************************************
	$x = 10;
	foreach ( $CircleGroups as $circle )
		{
		if ( $circle[1] != "" )
		   {
         $id = CreateVariable($circle[1], 1, $IDCircles, 0, "Plugwise_MenuItem", $ActionScriptId, false);
         IPS_SetInfo($id,$circle[0]);
			IPS_SetHidden($id,true);
         $x = $x + 10;
		   }
		}

	//***************************************************************************
	// Scriptlinks erstellen
	//***************************************************************************
		$ScriptId = IPS_GetScriptIDByName('Plugwise_Recalibrate', $CategoryIdApp );
      $id = CreateLink("Kalibrierung starten",$ScriptId,$VisuID_data1,10);
      IPS_SetInfo($id,"Script");
		$ScriptId = IPS_GetScriptIDByName('Plugwise_Circlesearch', $CategoryIdApp );
      $id = CreateLink("Circlesuche starten",$ScriptId,$VisuID_data1,20);
      IPS_SetInfo($id,"Script");
		$ScriptId = IPS_GetScriptIDByName('Plugwise_ReadTime', $CategoryIdApp );
      $id = CreateLink("Circlezeit auslesen",$ScriptId,$VisuID_data1,30);
      IPS_SetInfo($id,"Script");
		$ScriptId = IPS_GetScriptIDByName('Plugwise_SetTime', $CategoryIdApp );
      $id = CreateLink("Circlezeit setzen",$ScriptId,$VisuID_data1,40);
      IPS_SetInfo($id,"Script");
		$ScriptId = IPS_GetScriptIDByName('Plugwise_ReadBuffer', $CategoryIdApp );
      $id = CreateLink("Circlebuffer lesen",$ScriptId,$VisuID_data1,50);
      IPS_SetInfo($id,"Script");

		$ScriptId = IPS_GetScriptIDByName('Plugwise_IPSModulupdaten', $CategoryIdApp );
      $id = CreateLink("Plugwise Onlineupdate",$ScriptId,$VisuID_data2,50);
      IPS_SetInfo($id,"Script");

	//***************************************************************************
	// HTMLCircledaten linken
	//***************************************************************************
	$x = 100;
	foreach ( $CircleGroups as $circle )
		{
		if ( $circle[1] != "" )
		   {
			echo "\n". $circle[0]."-".$CategoryIdCData;
         $parent = @IPS_GetObjectIDByName($circle[0],$CategoryIdCData);
			if ( $parent )
			   {
			   $id = @IPS_GetObjectIDByName("WebData1",$parent);
			   if ( $id )
			      {
      			$id = CreateLink($circle[1],$id,$VisuID_data1,$x);
					IPS_SetHidden($id , true );
					IPS_SetInfo($id,$circle[0]);
      			$x = $x + 10;
			      }
			   $id = IPS_GetObjectIDByName("Status",$parent);
			   if ( $id )
			      {
      			$id = CreateLink($circle[1]." Status",$id,$VisuID_data1,$x+200);
					IPS_SetHidden($id , true );
					IPS_SetInfo($id,$circle[0]);
			      }

			   $id = @IPS_GetObjectIDByName("WebData2",$parent);
			   if ( $id )
			      {
      			$id = CreateLink($circle[1],$id,$VisuID_data2,$x);
					IPS_SetHidden($id , true );
					IPS_SetInfo($id,$circle[0]);
      			$x = $x + 10;
			      }

			   }
			}
		}




    }


  
  ReloadAllWebFronts() ;


?>