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
	GLOBAL $SystemStromzaehlerGroups; 
  GLOBAL $ExterneStromzaehlerGroups;
         
	if (!isset($moduleManager)) {
		IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');

		echo 'ModuleManager Variable not set --> Create "default" ModuleManager';
		$moduleManager = new IPSModuleManager('Plugwise');
	}

  $moduleManager->VersionHandler()->CheckModuleVersion('IPS','2.50');
	$moduleManager->VersionHandler()->CheckModuleVersion('IPSModuleManager','2.50.1');
  $moduleManager->VersionHandler()->SetModuleVersion("1.0.1008");
  
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
	$OtherDataPath  = "Program.IPSLibrary.data.hardware.Plugwise.Others";
	$GroupDataPath  = "Program.IPSLibrary.data.hardware.Plugwise.Groups";
	$ExternDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Extern";

	$cssDefault    = IPS_GetKernelDir()."webfront\user\Plugwise\Default\Plugwise.css";
	$cssFile       = IPS_GetKernelDir()."webfront\user\Plugwise\Plugwise.css";

  
  echo "--- Create Plugwise -------------------------------------------------------------------\n";
  
  $CategoryIdData   = CreateCategoryPath($DataPath);
	$CategoryIdApp    = CreateCategoryPath($AppPath);
	$CategoryIdVisu   = CreateCategoryPath($VisuPath);
	$CategoryIdMobile = CreateCategoryPath($MobilePath,100);
	$CategoryIdHw     = CreateCategoryPath($HardwarePath);
  $CategoryIdCData  = CreateCategoryPath($CircleDataPath);
  $CategoryIdOData  = CreateCategoryPath($OtherDataPath);
  
  EmptyCategory($CategoryIdVisu);
  EmptyCategory($CategoryIdMobile);

  //****************************************************************************
  // Serial Port erstellen
  //****************************************************************************
  $comid = CreateSerialPort('PlugwiseCOM', COMPORT , 115200, 1, 8, 'None');

  if ( !$comid )
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


  $ScriptId = IPS_GetScriptIDByName('Plugwise_Controller', $CategoryIdApp );
  //****************************************************************************
  // alle Kinder loeschen
  //****************************************************************************  
  $childs = IPS_GetChildrenIDs($ScriptId);
  foreach( $childs as $child )
    IPS_DeleteEvent($child);
    

  //****************************************************************************
  // Registervariable erstellen
  //****************************************************************************  

  $Name     = "PlugwiseRegisterVariable";
  
  $id = CreateRegisterVariable($Name, $CategoryIdHw , $ScriptId, $cutterid );

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
  IPS_SetEventCyclicTimeBounds($id,mktime(0,05,00),0);

  //****************************************************************************
  // Timer fuer Plugwise_Recalibrate
  //****************************************************************************  
  $ScriptId = IPS_GetScriptIDByName('Plugwise_Recalibrate', $CategoryIdApp );
  $id = CreateTimer_OnceADay ("REFRESH",$ScriptId,intval(CALIBRATION_TIME),15);

  //****************************************************************************
  // Timer fuer Plugwise_CheckUpdate
  //****************************************************************************  
  if ( defined('CHECK_VERSION') and defined('CHECK_VERSION_TIME') )                                           
    if (CHECK_VERSION != FALSE )
      {
      $ScriptId = IPS_GetScriptIDByName('Plugwise_CheckUpdate', $CategoryIdApp );
      $id = CreateTimer_OnceADay ("REFRESH",$ScriptId,intval(CHECK_VERSION_TIME),15);
      }


  
  //****************************************************************************
  //  CircleGroups in Data erstellen anhand der Liste im Configurationsfile
  //****************************************************************************
  foreach ( $CircleGroups as $cycle )
      {
      if ( $cycle[0] != "" )
        { 
        echo "\nCreate Circle". $cycle[0];
        createCircle($cycle[0],$CategoryIdCData);        
        }
      } 
 
  //****************************************************************************
  //  Archive Control finden
  //****************************************************************************
  foreach ( IPS_GetInstanceListByModuleType(0) as $modul )
    {
    $archive_id = false;
		$instance = IPS_GetInstance($modul);
		if ( $instance['ModuleInfo']['ModuleName'] == "Archive Control" ) 
      { $archive_id = $modul; break; }
	  }
   
  //****************************************************************************
  //  Gesamt  Sonstiges(Gesamt-Rest)
  //****************************************************************************
  if ( isset($SystemStromzaehlerGroups) )       
  foreach( $SystemStromzaehlerGroups as $systemzaehler )
    {
    $name  = $systemzaehler[0];
    $ident = $systemzaehler[1];
   
    $item = CreateDummyInstance ($name, $CategoryIdOData , 0);
    $id1  = CreateVariable("Leistung", 2, $item, 0, "~Watt.14490", 0, 0);
    $id2  = CreateVariable("Gesamtverbrauch", 2, $item, 0, "~Electricity", 0, 0); 
    IPS_SetIdent($item,$ident);
    IPS_SetIdent($id1,"Leistung");
    IPS_SetIdent($id2,"Gesamtverbrauch");
    
    
    if ( $archive_id )
      {
      $aggtype = 1;   // Zaehler
      if ( defined('AGGTYPE') )
        $aggtype = AGGTYPE;

      $archivlogging = true;
      if ( defined('ARCHIVLOGGING') )
        $archivlogging = ARCHIVLOGGING;
    
      if ($archivlogging == true)
        {        
        AC_SetLoggingStatus($archive_id,  $id1, True); // Logging einschalten
        AC_SetAggregationType($archive_id,$id1,$aggtype); // Logging auf  setzen
        IPS_ApplyChanges($archive_id);
        AC_SetLoggingStatus($archive_id,  $id2, True); // Logging einschalten
        AC_SetAggregationType($archive_id,$id2, $aggtype); // Logging auf  setzen
        IPS_ApplyChanges($archive_id);
        }

      }
    }


  //****************************************************************************
  //  Externe Zaehler anlegen
  //****************************************************************************
  if ( isset($ExterneStromzaehlerGroups) )
  if ( TRUE == FALSE )       
  foreach( $ExterneStromzaehlerGroups as $externzaehler )
    {
    $name  = $externzaehler[0];
    $ident = $externzaehler[0];
   
    $item = CreateDummyInstance ($name, $CategoryIdEData , 0);
    $id1  = CreateVariable("Leistung", 2, $item, 0, "~Watt.14490", 0, 0);
    $id2  = CreateVariable("Gesamtverbrauch", 2, $item, 0, "~Electricity", 0, 0); 
    IPS_SetIdent($item,$ident);
    IPS_SetIdent($id1,"Leistung");
    IPS_SetIdent($id2,"Gesamtverbrauch");
    
    
    if ( $archive_id )
      {
      $aggtype = 1;   // Zaehler
      if ( defined('AGGTYPE') )
        $aggtype = AGGTYPE;

      $archivlogging = true;
      if ( defined('ARCHIVLOGGING') )
        $archivlogging = ARCHIVLOGGING;
    
      if ($archivlogging == true)
        {        
        AC_SetLoggingStatus($archive_id,  $id1, True); // Logging einschalten
        AC_SetAggregationType($archive_id,$id1,$aggtype); // Logging auf  setzen
        AC_SetLoggingStatus($archive_id,  $id2, True); // Logging einschalten
        AC_SetAggregationType($archive_id,$id2, $aggtype); // Logging auf  setzen
        }

      }
    }


  //***************************************************************************
	// Gruppe in DATA erstellen fuer Gesamtuebersicht einer Gruppe
	//***************************************************************************
   $array = array();
   foreach ( $CircleGroups as $group ) array_push($array,$group[2]);
   if ( isset($ExterneStromzaehlerGroups) ) 
   foreach ( $ExterneStromzaehlerGroups as $group ) array_push($array,$group[1]);  // Externe

   $groups = array_unique($array);
	 $x = 10;
   foreach ( $groups as $group )
   	{
      if ( $group != "" )
      	{
        $item = CreateDummyInstance ($group, $CategoryIdOData , $x);
        $id2  = CreateVariable("Leistung", 2, $item, 0, "~Watt.14490", 0, 0);
        $id3  = CreateVariable("Gesamtverbrauch", 2, $item, 0, "~Electricity", 0, 0); 

        if ( $archive_id )
          { 
          $archivlogging = true;
          if ( defined('ARCHIVLOGGING') )
            $archivlogging = ARCHIVLOGGING;

          if ($archivlogging == true)
            {        
            AC_SetLoggingStatus($archive_id, $id2, True); // Logging einschalten
            AC_SetAggregationType($archive_id, $id2, 1); // Logging auf Zähler setzen
            IPS_ApplyChanges($archive_id);
            AC_SetLoggingStatus($archive_id, $id3, True); // Logging einschalten
            AC_SetAggregationType($archive_id, $id3, 1); // Logging auf Zähler setzen
            IPS_ApplyChanges($archive_id);
            }
          }

        $x = $x + 10;
        }
      }




  //****************************************************************************
  // Jetzt Circles suchen  / erst mal deaktiviert
  //****************************************************************************
	IPSUtils_Include ("Plugwise_Include.ips.php",      "IPSLibrary::app::hardware::Plugwise");
  //PW_SendCommand("0008");   // deaktiviert
  


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
	CreateProfile_Associations ("Plugwise_MenuItem", array(
												0	=> "",
												1 	=> "———"
												),
												'', array(
												0  =>	0xFFCC00,
												1  =>	0x00FFCC
												));
	CreateProfile_Associations ("Plugwise_MenuScripte", array(
												0	=> "Starten"											
												),
												'', array(
												0  =>	0xFFCC00
												));
	CreateProfile_Associations ("Plugwise_MenuUebersicht", array(
												0	=> "On/Offline",
												1 => "Ein / Aus ",
												2 => "HW-Version",
												3 => "SW-Version",
												4 => "- Timing -",
												5 => " Not used "
												
												),
												'', array(
												0  =>	0xFFCC00,
												1  =>	0xFFCC00,
												2  =>	0xFFCC00,
												3  =>	0xFFCC00,
												4  =>	0xFFCC00,
												5  =>	0xFFCC00
												));

    
   $VisuID_menu  = CreateCategory("MENU",$CategoryIdVisu,10);
   $VisuID_data1 = CreateCategory("DATA1",$CategoryIdVisu,10);
   $VisuID_data2 = CreateCategory("DATA2",$CategoryIdVisu,10);
   $VisuID_graph = CreateCategory("GRAPH",$CategoryIdVisu,10);

   $ActionScriptId = IPS_GetScriptIDByName('Plugwise_Webfront', $CategoryIdApp );
   
  $graphid  = CreateVariable("Uebersicht", 3, $VisuID_graph, 0, "~HTMLBox", false, false);
  $graphid1 = CreateVariable("Auswahl", 1, $VisuID_graph, 0, "Plugwise_MenuUebersicht", $ActionScriptId, false);
  IPS_SetHidden($graphid1,true);
  
  $IDAllgemein = CreateDummyInstance("Allgemeines",$VisuID_menu,10);
  $IDGroups    = CreateDummyInstance("Gruppen",$VisuID_menu,10);
	$IDCircles   = CreateDummyInstance("Stromzähler",$VisuID_menu,20);
  IPS_SetHidden($IDCircles,true);
	$IDSystemst  = CreateDummyInstance("Systemsteuerung",$VisuID_menu,30);
  IPS_SetHidden($IDSystemst,true);
	$IDAuswert   = CreateDummyInstance("Auswertungen",$VisuID_menu,30);
  IPS_SetHidden($IDAuswert,true);
  
  // alternativer button ?
  if ( defined('ALT_BUTTON_NORMAL') )
    if (ALT_BUTTON_NORMAL!= FALSE )
      {
      $normal_file  = IPS_GetKernelDir() ."webfront\\user\\Plugwise\\".ALT_BUTTON_NORMAL;
      $dest_file    = IPS_GetKernelDir() ."webfront\\user\\Plugwise\\tabPane.png";
      copy ( $normal_file,$dest_file);
      $tabbutton  = "user/Plugwise/tabPane.png";
      $WFC_TabPaneName = "<img src='".$tabbutton."' height=32  width=150 align='top' alt='".$WFC_TabPaneName."'>";

      }
  
	CreateWFCItemSplitPane ($WFC_ConfigId, $WFC_TabPaneItem, $WFC_TabPaneParent , 20 , $WFC_TabPaneName   , ''  , 1 /*Horizontal*/, 30 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
	CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-MENU", $WFC_TabPaneItem, 10, "Titel", $Icon="", $VisuID_menu, $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');

	//CreateWFCItemSplitPane ($WFC_ConfigId, $WFC_TabPaneItem."-SPLITDATA",  $WFC_TabPaneItem              , 20 , $WFC_TabPaneName   , ''  , 0 , 40 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
	//CreateWFCItemSplitPane ($WFC_ConfigId, $WFC_TabPaneItem."-SPLITDATA1", $WFC_TabPaneItem."-SPLITDATA" , 20 , $WFC_TabPaneName   , ''  , 1 , 50 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
	//CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-DATA1",      $WFC_TabPaneItem."-SPLITDATA1", 30, "Titel", $Icon="", $VisuID_data1, $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');
	//CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-DATA2",      $WFC_TabPaneItem."-SPLITDATA1", 40, "Titel", $Icon="", $VisuID_data2, $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');

  //CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-GRAPH", $WFC_TabPaneItem."-SPLITDATA", 40, "Titel", $Icon="", $VisuID_graph , $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');
  CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-GRAPH", $WFC_TabPaneItem, 40, "Titel", $Icon="", $VisuID_graph , $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');

  
	//***************************************************************************
	// Systemsteuerung und Auswertung erstellen
	//***************************************************************************
  $id = CreateVariable("Systemsteuerung", 1, $IDAllgemein, 0, "Plugwise_MenuItem", $ActionScriptId, false);
  $id = CreateVariable("Auswertungen"   , 1, $IDAllgemein, 0, "Plugwise_MenuItem", $ActionScriptId, false);
  IPS_SetHidden($id,true);
	//***************************************************************************
	// Gruppenmenu erstellen fuer Circles und Externe
	//***************************************************************************
   $array = array();
   foreach ( $CircleGroups as $group )              array_push($array,$group[2]);  // Circles
   if ( isset($ExterneStromzaehlerGroups) ) 
   foreach ( $ExterneStromzaehlerGroups as $group ) array_push($array,$group[1]);  // Externe
   
   $groups = array_unique($array);
	 $x = 10;
   foreach ( $groups as $group )
   	{
      if ( $group != "" )
      	{
         $id = CreateVariable($group, 1, $IDGroups, 0, "Plugwise_MenuItem", $ActionScriptId, false);
         IPS_SetInfo($id,$group);
         $x = $x + 10;
         }
      }
      
    // Sonstiges / Rest  
    $id = CreateVariable("Sonstige", 1, $IDGroups, 9999, "Plugwise_MenuItem", $ActionScriptId, false);
    IPS_SetInfo($id,"SYSTEM_REST");
    IPS_SetIdent($id,"SYSTEM_REST");
    $x = $x + 10;


	//***************************************************************************
	// Externsmenu erstellen
	//***************************************************************************
	$x = 10;
	if ( isset($ExterneStromzaehlerGroups) ) 
	foreach ( $ExterneStromzaehlerGroups as $extern )
		{
		if ( $extern[0] != "" )
		   {
         $id = CreateVariable($extern[0], 1, $IDCircles, 0, "Plugwise_MenuItem", $ActionScriptId, false);
         IPS_SetInfo($id,$extern[0]);
			   IPS_SetHidden($id,true);
         $x = $x + 10;
		   }
		}
	//***************************************************************************
	//  Circlesmenu erstellen
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

    $id = CreateVariable("Kalibrierung"     , 1, $IDSystemst, 50, "Plugwise_MenuScripte", $ActionScriptId, false);
    IPS_SetInfo($id,"Script");
    $id = CreateVariable("Circles suchen"   , 1, $IDSystemst, 60, "Plugwise_MenuScripte", $ActionScriptId, false);
    IPS_SetInfo($id,"Script");
//    $id = CreateVariable("Circlezeit lesen" , 1, $IDSystemst, 30, "Plugwise_MenuScripte", $ActionScriptId, false);
//    IPS_SetInfo($id,"Script");
    $id = CreateVariable("Circlezeit setzen", 1, $IDSystemst, 70, "Plugwise_MenuScripte", $ActionScriptId, false);
    IPS_SetInfo($id,"Script");

    $id = CreateVariable("OnlineUpdate"     , 1, $IDSystemst, 30, "Plugwise_MenuScripte", $ActionScriptId, false);
    IPS_SetInfo($id,"Script");
    $id = CreateVariable("Versionsinfo"     , 1, $IDSystemst, 10, "Plugwise_MenuScripte", $ActionScriptId, false);
    IPS_SetInfo($id,"Script");
    $id = CreateVariable("Update vorhanden?", 1, $IDSystemst, 20, "Plugwise_MenuScripte", $ActionScriptId, false);
    IPS_SetInfo($id,"Script");
    

	//***************************************************************************
	// Webdata1 und Webdata2 erstellen
	//***************************************************************************
  $id = CreateVariable("WEBDATA1", 3, $VisuID_data1, 1, "~HTMLBox", false, "");
  $id = CreateVariable("WEBDATA2", 3, $VisuID_data2, 1, "~HTMLBox", false, "");
  
    
	//***************************************************************************
	// HTML Daten linken
	//***************************************************************************
	$x = 100;
	foreach ( $CircleGroups as $circle )
		{
		if ( $circle[1] != "" )
		   {
        echo "\n". $circle[0]."-".$CategoryIdCData;
        $parent = @IPS_GetObjectIDByIdent($circle[0],$CategoryIdCData);
			if ( $parent )
			   {
			   $id = IPS_GetObjectIDByName("Status",$parent);
			   if ( $id )
			      {                                                      
      			//$id = CreateLink($circle[1]." Status",$id,$VisuID_data1,$x+200);
      			$id = CreateLink($circle[1],$id,$VisuID_menu,$x+200);
					  IPS_SetHidden($id , true );
					  IPS_SetInfo($id,$circle[0]);
			      }
			   }
			}
		}


    }

  if ( file_exists($cssFile))
	   {
	   echo "\nUser-CSS-File existiert. Wird nicht ueberschrieben";
		}
	else
	   {
	   echo "\nUser-CSS-File existiert nicht . Default wird kopiert";
	   copy($cssDefault,$cssFile);
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