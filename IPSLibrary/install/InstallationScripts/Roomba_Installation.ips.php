<?
	/**@defgroup roomba_installation Roomba Installation
	 * @ingroup roomba
	 * @{
	 *
	 * Installations Script fuer Roomba
	 *
	 * @file          Roomba_Installation.ips.php
	 * @author        1007
	 * @version
	 *  Version 1.0.1, 27.02.2012<br/>
	 *
	 * @section requirements_component Installations Voraussetzungen Roomba
	 * - IPS Kernel >= 2.50
	 * - IPSModuleManager >= 2.50.1
	 *
	 * @page install_component Installations Schritte
	 * Folgende Schritte sind zur Installation von Roomba noetig:
	 * - Laden des Modules (siehe IPSModuleManager)
	 * - Installation (siehe IPSModuleManager)
	 */

  GLOBAL $roombas;
	
	
	if (!isset($moduleManager)) {
		IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');

		echo 'ModuleManager Variable not set --> Create "default" ModuleManager';
		$moduleManager = new IPSModuleManager('Roomba');
	}

  $moduleManager->VersionHandler()->CheckModuleVersion('IPS','2.50');
	$moduleManager->VersionHandler()->CheckModuleVersion('IPSModuleManager','2.50.1');


  IPSUtils_Include ("IPSInstaller.inc.php",            "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("IPSMessageHandler.class.php",     "IPSLibrary::app::core::IPSMessageHandler");
	IPSUtils_Include ("Roomba_Configuration.inc.php",    "IPSLibrary::config::hardware::Roomba");

	$AppPath       = "Program.IPSLibrary.app.hardware.Roomba";
	$DataPath      = "Program.IPSLibrary.data.hardware.Roomba";
	$ConfigPath    = "Program.IPSLibrary.config.hardware.Roomba";
	$HardwarePath  = "Hardware.Roomba";
	$VisuPath      = "Visualization.WebFront.Hardware.Roomba";

  echo "\n--- Create Roomba -------------------------------------------------------------------\n";
	$CategoryIdData = CreateCategoryPath($DataPath);
	$CategoryIdApp  = CreateCategoryPath($AppPath);
	$CategoryIdHw   = CreateCategoryPath($HardwarePath);
	$CategoryIdVisu = CreateCategoryPath($VisuPath);


  $roomba_array = array();
  // Gateway finden
  $xbee_gateway = 0;
  
  if ( AUTOSEARCH == true)
    {   
    foreach ( IPS_GetInstanceListByModuleType(2) as $modul )
        {
		    $instance = IPS_GetInstance($modul);
		    print_r($instance);
        if ( $instance['ModuleInfo']['ModuleName'] == "XBee Gateway" ) 
          { 
		      $xbee_gateway = $instance['InstanceID'];          
          }  
        }
    }
          
  // Splitter finden
  if ( AUTOSEARCH == true)
    {   
    foreach ( IPS_GetInstanceListByModuleType(2) as $modul )
        {
        
		    $instance = IPS_GetInstance($modul);
        if ( $instance['ModuleInfo']['ModuleName'] == "XBee Splitter" ) 
          { 
          $xbeesplitter_id = $modul; 
          $object = IPS_GetObject($xbeesplitter_id);
          $name = $object['ObjectName'];
          if ( stripos($name,"Roomba") )
            array_push($roomba_array,array($name,$xbeesplitter_id,true,0));
          }

        }
    }
  else
    {
   foreach ( $roombas as $roomba)
      {  
      $name             = $roomba[0];
      $xbeesplitter_id  = $roomba[1];
      $aktiv            = $roomba[2];
      $xbeegateway_id   = $roomba[3];
      
      if ( $aktiv )
        array_push($roomba_array,array($name,$xbeesplitter_id,$aktiv,$xbeegateway_id));
      }     
    }


  if ( !$roomba_array )
    {
    echo "\n--- Keine Roombas gefunden ----------------------------------------------------------\n";
    return;
    }

  $pollingScriptId = IPS_GetScriptIDByName('RoombaPolling', $CategoryIdApp );
  CreateTimer_CyclicBySeconds ("ScriptTimer",$pollingScriptId,POLLING_DEFAULT,true);
  IPS_SetScriptTimer($pollingScriptId,POLLING_DEFAULT);


  $actionScriptId         = IPS_GetScriptIDByName('RoombaInput', $CategoryIdApp );

  foreach ( $roomba_array as $roomba )
      {
      $roomba_name     = $roomba[0];
      $roomba_splitter = $roomba[1];
      $roomba_gateway  = $roomba[3];
      
      if ( $roomba_gateway == 0 )
          if ( AUTOSEARCH == true)
            $roomba_gateway = $xbee_gateway;
      
      $roomba_rv_Id = CreateRegisterVariable($roomba_name, $CategoryIdHw, $actionScriptId,$roomba_splitter, 0);
      
      $CategoryRoombaData     = CreateCategoryPath($DataPath.".$roomba_name.RoombaData");

      $Id  = CreateVariable("ANGLE"                                       , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BATTERY_CHARGE"                              , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BATTERY_CAPACITY"                            , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);  
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS"                        , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_BUMP_RIGHT"             , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_BUMP_LEFT"              , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_WHEEL_DROP_RIGHT"       , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_WHEEL_DROP_LEFT"        , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_CLEAN"                               , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_SPOT"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_DOCK"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_MINUTE"                              , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_HOUR"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_DAY"                                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_SCHEDULE"                            , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_CLOCK"                               , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_SOURCES_AVAILABLE"                  , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_SOURCES_AVAILABLE_HOME_BASE"        , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_SOURCES_AVAILABLE_INTERNAL_CHARGER" , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_STATE"                              , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_NOT_CHARGING"                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_RECONDITIONING_CHARGING"      , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_FULL_CHARGING"                , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_TRICKLE_CHARGING"             , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_WAITING"                      , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_CHARGING_FAULT_CONDITION"     , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_LEFT"                                  , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_LEFT"                            , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_RIGHT"                           , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_RIGHT"                                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_LEFT_SIGNAL"                           , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_LEFT_SIGNAL"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_RIGHT_SIGNAL"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_RIGHT_SIGNAL"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CURRENT"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("DIRT_DETECT"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("DISTANCE"                                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("INFRARED_CHARACTER_LEFT"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("INFRARED_CHARACTER_RIGHT"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("INFRARED_CHARACTER_OMNI"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LEFT_ENCODER_COUNTS"                         , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LEFT_MOTOR_CURRENT"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER"                                , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_FRONT_RIGHT"                    , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_RIGHT"                          , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_CENTER_RIGHT"                   , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_CENTER_LEFT"                    , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_FRONT_LEFT"                     , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_LEFT"                           , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_LEFT_SIGNAL"                      , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_FRONT_LEFT_SIGNAL"                , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_CENTER_LEFT_SIGNAL"               , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_CENTER_RIGHT_SIGNAL"              , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_FRONT_RIGHT_SIGNAL"               , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_RIGHT_SIGNAL"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("MAIN_BRUSH_MOTOR_CURRENT"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("NUMBER_OF_STREAM_PACKETS"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("OI_MODE"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("OI_MODE_OFF"                                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("OI_MODE_PASSIVE"                             , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("OI_MODE_SAFE"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("OI_MODE_FULL"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("REQUESTED_VELOCITY"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("REQUESTED_RIGHT_VELOCITY"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("REQUESTED_LEFT_VELOCITY"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("REQUESTED_RADIUS"                            , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("RIGHT_ENCODER_COUNTS"                        , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("RIGHT_MOTOR_CURRENT"                         , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("SIDE_BRUSH_MOTOR_CURRENT"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("STASIS"                                      , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("SONG_NUMBER"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("SONG_PLAYING"                                , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("TEMPERATURE"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("VIRTUAL_WALL"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("VOLTAGE"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WALL"                                        , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WALL_SIGNAL"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_SIDE_BRUSH"               , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_MAIN_BRUSH"               , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_RIGHT_WHEEL"              , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_LEFT_WHEEL"               , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);                       

      $CategorySystemData     = CreateCategoryPath($DataPath.".$roomba_name.SystemData");

      $Id  = CreateVariable("PACKET_REQUESTED"                            , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("PACKET_COUNTER"                              , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("WEGMESSUNG"                                  , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("STARTZEIT"                                   , 3 /*String */,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("ENDZEIT"                                     , 3 /*String */,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("LAUFZEIT"                                    , 3 /*String */,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("BATTERIE"                                    , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("STARTTIMESTAMP"                              , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_CHARGING"                      , 0 /*Boolean*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_MOVING"                        , 0 /*Boolean*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_ONLINE"                        , 0 /*Boolean*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_UNKNOWN"                       , 0 /*Boolean*/,  $CategorySystemData,100,'', null, true);
      $Id  = CreateVariable("POLLING_STATUS"                              , 0 /*Boolean*/,  $CategorySystemData,100,'', null, true);
      $Id  = CreateVariable("ROOMBA_DISTANCE"                             , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("ROOMBA_CHARGING"                             , 0 /*Boolean*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("TIMER"                                       , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("AKTIV"                                       , 0 /*Boolean*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("POLLING"                                     , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("XBEE_GATEWAY"                                , 1 /*Integer*/,  $CategorySystemData,100,'', null, $roomba_gateway);
      SetValueInteger($Id,$roomba_gateway);
      $Id  = CreateVariable("XBEE_SPLITTER"                               , 1 /*Integer*/,  $CategorySystemData,100,'', null, $roomba_splitter);
      SetValueInteger($Id,$roomba_splitter);
      $Id  = CreateVariable("XBEE_REGISTERVARIABLE"                       , 1 /*Integer*/,  $CategorySystemData,100,'', null, $roomba_rv_Id);
      SetValueInteger($Id,$roomba_rv_Id);

               
      $CategoryLighthouseData = CreateCategoryPath($DataPath.".$roomba_name.LighthouseData");

      $Id  = CreateVariable("LH_CHARGER_160"                              , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);
      $Id  = CreateVariable("LH_CHARGER_FORCE_FIELD"                      , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);
      $Id  = CreateVariable("LH_CHARGER_GREEN_BUOY"                       , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);
      $Id  = CreateVariable("LH_CHARGER_RED_BUOY"                         , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);

    
      

      for ( $x=0;$x<LIGHTHOUSES_ANZAHL;$x++)
          {
          $CategoryLighthouseData = CreateCategoryPath($DataPath.".$roomba_name.LighthouseData.LH_0$x");

          $Id  = CreateVariable("LH_0".$x."_ID"                           , 1 /*Integer*/,  $CategoryLighthouseData,100,'', null, 0);
          $Id  = CreateVariable("LH_0".$x."_FENCE"                        , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);
          $Id  = CreateVariable("LH_0".$x."_FORCE_FIELD"                  , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);
          $Id  = CreateVariable("LH_0".$x."_GREEN_BUOY"                   , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);
          $Id  = CreateVariable("LH_0".$x."_RED_BUOY"                     , 0 /*Boolean*/,  $CategoryLighthouseData,100,'', null, 0);
      }

      }

  //****************************************************************************
  //  Visu
  //****************************************************************************
  $CategoryIDScripts = CreateCategoryPath($VisuPath.".Scripts");

  $actionScriptId = IPS_GetScriptIDByName('RoombaCmdClean', $CategoryIdApp );
  CreateLink ("Roomba Start", $actionScriptId, $CategoryIDScripts, 10);
  $actionScriptId = IPS_GetScriptIDByName('RoombaCmdHome', $CategoryIdApp );
  CreateLink ("Roomba Home", $actionScriptId, $CategoryIDScripts, 20);
  $actionScriptId = IPS_GetScriptIDByName('RoombaCmdWartung', $CategoryIdApp );
  CreateLink ("Roomba Wartung", $actionScriptId, $CategoryIDScripts, 30);
  $actionScriptId = IPS_GetScriptIDByName('RoombaCmdSpot', $CategoryIdApp );
  CreateLink ("Roomba Spot", $actionScriptId, $CategoryIDScripts, 30);
  $actionScriptId = IPS_GetScriptIDByName('RoombaCmdMax', $CategoryIdApp );
  CreateLink ("Roomba Max", $actionScriptId, $CategoryIDScripts, 30);
  $actionScriptId = IPS_GetScriptIDByName('RoombaCmdInit', $CategoryIdApp );
  CreateLink ("Roomba Init", $actionScriptId, $CategoryIDScripts, 30);
   

	/** @}*/


?>