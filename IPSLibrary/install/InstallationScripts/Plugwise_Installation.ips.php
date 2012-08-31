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

  GLOBAL $Profil_Plugwise_Leistung;
  GLOBAL $Profil_Plugwise_Verbrauch;
  GLOBAL $Profil_Plugwise_Switch;
  GLOBAL $Profil_Plugwise_MenuItem;
  GLOBAL $Profil_Plugwise_MenuScripte;
  GLOBAL $Profil_Plugwise_MenuUebersicht;
 
  set_time_limit(180);
         
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
	IPSUtils_Include ("Plugwise_Profile.inc.php",      "IPSLibrary::config::hardware::Plugwise");


  
  
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
  echo "[". $Profil_Plugwise_Leistung[0] ."]";
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
  //  Profile erstellen aus ProfileConfigurationsfile
  //****************************************************************************
  if ( substr($Profil_Plugwise_Leistung[0],0,1) != "~" )
    CreateProfile_Associations ($Profil_Plugwise_Leistung[0],
                                $Profil_Plugwise_Leistung[1],
                                $Profil_Plugwise_Leistung[2],
                                $Profil_Plugwise_Leistung[3]);

  if ( substr($Profil_Plugwise_Verbrauch[0],0,1) != "~" )
    CreateProfile_Associations ($Profil_Plugwise_Verbrauch[0],
                                $Profil_Plugwise_Verbrauch[1],
                                $Profil_Plugwise_Verbrauch[2],
                                $Profil_Plugwise_Verbrauch[3]);
                                
  if ( substr($Profil_Plugwise_Switch[0],0,1) != "~" )
    CreateProfile_Switch ($Profil_Plugwise_Switch[0],
                                $Profil_Plugwise_Switch[1],
                                $Profil_Plugwise_Switch[2],
                                $Profil_Plugwise_Switch[3],
                                $Profil_Plugwise_Switch[4],
                                $Profil_Plugwise_Switch[5]
                                );

  if ( substr($Profil_Plugwise_MenuItem[0],0,1) != "~" )
    CreateProfile_Associations ($Profil_Plugwise_MenuItem[0],
                                $Profil_Plugwise_MenuItem[1],
                                $Profil_Plugwise_MenuItem[2],
                                $Profil_Plugwise_MenuItem[3]);
 
  if ( substr($Profil_Plugwise_MenuScripte[0],0,1) != "~" )
    CreateProfile_Associations ($Profil_Plugwise_MenuScripte[0],
                                $Profil_Plugwise_MenuScripte[1],
                                $Profil_Plugwise_MenuScripte[2],
                                $Profil_Plugwise_MenuScripte[3]);
  
  if ( substr($Profil_Plugwise_MenuUebersicht[0],0,1) != "~" )
    CreateProfile_Associations ($Profil_Plugwise_MenuUebersicht[0],
                                $Profil_Plugwise_MenuUebersicht[1],
                                $Profil_Plugwise_MenuUebersicht[2],
                                $Profil_Plugwise_MenuUebersicht[3]);
 
 
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

    $id1 = @IPS_GetVariableIDByName("Leistung",$item) ;
    if ( $id1 == false )
      $id1  = CreateVariable("Leistung", 2, $item, 0, $Profil_Plugwise_Leistung[0], 0, 0);

    $id2 = @IPS_GetVariableIDByName("Gesamtverbrauch",$item) ;
    if ( $id2 == false )
      $id2  = CreateVariable("Gesamtverbrauch", 2, $item, 0, $Profil_Plugwise_Verbrauch[0], 0, 0); 


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
        if ( defined('AGGTYPELEISTUNG') )
      	   $aggtype = AGGTYPELEISTUNG;
   
        AC_SetLoggingStatus($archive_id,  $id1, True); // Logging einschalten
        AC_SetAggregationType($archive_id,$id1,$aggtype); // Logging auf  setzen
        IPS_ApplyChanges($archive_id);
        
        if ( defined('AGGTYPEVERBRAUCH') )
      	   $aggtype = AGGTYPEVERBRAUCH;

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

    $id1 = @IPS_GetVariableIDByName("Leistung",$item) ;
    if ( $id1 == false )
      $id1  = CreateVariable("Leistung", 2, $item, 0, $Profil_Plugwise_Leistung[0], 0, 0);

    $id2 = @IPS_GetVariableIDByName("Gesamtverbrauch",$item) ;
    if ( $id2 == false )
      $id2  = CreateVariable("Gesamtverbrauch", 2, $item, 0,  $Profil_Plugwise_Verbrauch[0], 0, 0); 

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
        if ( defined('AGGTYPELEISTUNG') )
      	   $aggtype = AGGTYPELEISTUNG;
      
        AC_SetLoggingStatus($archive_id,  $id1, True); // Logging einschalten
        AC_SetAggregationType($archive_id,$id1,$aggtype); // Logging auf  setzen
        IPS_ApplyChanges($archive_id);

        if ( defined('AGGTYPEVERBRAUCH') )
      	   $aggtype = AGGTYPEVERBRAUCH;

        AC_SetLoggingStatus($archive_id,  $id2, True); // Logging einschalten
        AC_SetAggregationType($archive_id,$id2, $aggtype); // Logging auf  setzen
        IPS_ApplyChanges($archive_id);
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

        $id2 = @IPS_GetVariableIDByName("Leistung",$item) ;
        if ( $id2 == false )
          $id2  = CreateVariable("Leistung", 2, $item, 0, $Profil_Plugwise_Leistung[0], 0, 0);

        $id3 = @IPS_GetVariableIDByName("Gesamtverbrauch",$item) ;
        if ( $id3 == false )
          $id3  = CreateVariable("Gesamtverbrauch", 2, $item, 0,  $Profil_Plugwise_Verbrauch[0], 0, 0); 

        if ( $archive_id )
          { 
          $archivlogging = true;
          if ( defined('ARCHIVLOGGING') )
            $archivlogging = ARCHIVLOGGING;

          if ($archivlogging == true)
            {        
            if ( defined('AGGTYPELEISTUNG') )
      	       $aggtype = AGGTYPELEISTUNG;

            AC_SetLoggingStatus($archive_id, $id2, True); // Logging einschalten
            AC_SetAggregationType($archive_id, $id2, 1); // Logging auf Zähler setzen
            IPS_ApplyChanges($archive_id);

            if ( defined('AGGTYPEVERBRAUCH') )
      	       $aggtype = AGGTYPEVERBRAUCH;

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
 
  $htmlboxid  = CreateVariable("Webfront", 3, $CategoryIdVisu, 0, "~HTMLBox", false, false);
  
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

  $cssmenu = false;
	if (defined('CSS3MENU'))
      $cssmenu = CSS3MENU ;

  if ( $cssmenu == false )
    {  
	   CreateWFCItemSplitPane ($WFC_ConfigId, $WFC_TabPaneItem, $WFC_TabPaneParent , $WFC_TabPaneOrder , $WFC_TabPaneName   , ''  , 1 /*Horizontal*/, 30 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
	   CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-MENU", $WFC_TabPaneItem, 10, "Titel", $Icon="", $VisuID_menu, $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');
     CreateWFCItemCategory  ($WFC_ConfigId, $WFC_TabPaneItem."-GRAPH", $WFC_TabPaneItem, 40, "Titel", $Icon="", $VisuID_graph , $BarBottomVisible='true' , $BarColums=9, $BarSteps=5, $PercentageSlider='true');
    }
  else
    {
    $WebfrontTitle  = "Plugwise";
    $WebfrontItemId = $htmlboxid; 
    $WebfrontIcon = '';                                                      
    $Configuration = "{\"title\":\"$WebfrontTitle\",\"name\":\"$WebfrontItemId\",\"baseID\":\"$htmlboxid\"}";
    CreateWFCItem($WFC_ConfigId,$WFC_TabPaneItem."-WEBFRONT", $WFC_TabPaneParent, $WFC_TabPaneOrder , "Plugwise CSS", '' , 'ContentChanger' ,$Configuration );
    IPS_ApplyChanges($WFC_ConfigId);
    }
    
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