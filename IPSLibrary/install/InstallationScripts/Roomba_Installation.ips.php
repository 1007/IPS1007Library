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
		    //print_r($instance);
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
  $webfrontScriptId       = IPS_GetScriptIDByName('RoombaWebfront', $CategoryIdApp );

  create_profile("Roomba_ChargingState"	,"",1);
  IPS_SetVariableProfileAssociation("Roomba_ChargingState", 0, "Not charging",            "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ChargingState", 1, "Reconditioning Charging", "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ChargingState", 2, "Full Charging",           "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ChargingState", 3, "Trickle Charging",        "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ChargingState", 4, "Waiting",                 "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ChargingState", 5, "Charging Fault Condition","", 0xaaaaaa);

  create_profile("Roomba_mV"	," mV",1);
  create_profile("Roomba_mA"	," mA",1);
  create_profile("Roomba_mAh"	," mAh",1);
  create_profile("Roomba_mm"	," mm",1);
  create_profile("Roomba_mms"	," mm/s",1);
  create_profile("Roomba_Winkel"	," °",1);
  create_profile("Roomba_Temperatur"	," °",1);

  create_profile("Roomba_ON_OFF"	,"",1);
  IPS_SetVariableProfileAssociation("Roomba_ON_OFF", 0, "Off",  "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ON_OFF", 1, "On",   "", 0xaaaaaa);

  create_profile("Roomba_ChargingSource"	,"",1);
  IPS_SetVariableProfileAssociation("Roomba_ChargingSource", 0, "No source",        "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ChargingSource", 1, "Internal Charger", "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_ChargingSource", 2, "Home Base",        "", 0xaaaaaa);

  create_profile("Roomba_OIMode"	,"",1);
  IPS_SetVariableProfileAssociation("Roomba_OIMode", 0, "Off",     "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_OIMode", 1, "Passive", "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_OIMode", 2, "Safe",    "", 0xaaaaaa);
  IPS_SetVariableProfileAssociation("Roomba_OIMode", 3, "Full",    "", 0xaaaaaa);

  create_profile("Roomba_Overcurrents"	,"",0);
  IPS_SetVariableProfileAssociation("Roomba_Overcurrents", 0, "OK",          "", 0x000000);
  IPS_SetVariableProfileAssociation("Roomba_Overcurrents", 1, "Overcurrent", "", 0xaaaaaa);

  create_profile("Roomba_cmdbutton"	,"",1);
  IPS_SetVariableProfileAssociation("Roomba_cmdbutton", 0, "START", "", 0x66CC66);
  IPS_SetVariableProfileAssociation("Roomba_cmdbutton", 1, " " ,      "", -1);


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

      $Id  = CreateVariable("ANGLE"                                       , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_Winkel', null, 0);
      $Id  = CreateVariable("BATTERY_CHARGE"                              , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mAh', null, 0);
      $Id  = CreateVariable("BATTERY_CAPACITY"                            , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mAh', null, 0);  
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS"                        , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_BUMP_RIGHT"             , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_BUMP_LEFT"              , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_WHEEL_DROP_RIGHT"       , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_WHEEL_DROP_LEFT"        , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUMP_AND_WHEEL_DROPS_WHEEL_DROP_CASTER"      , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("BUTTONS_CLEAN"                               , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS_SPOT"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS_DOCK"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS_MINUTE"                              , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS_HOUR"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS_DAY"                                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS_SCHEDULE"                            , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("BUTTONS_CLOCK"                               , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_SOURCES_AVAILABLE"                  , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_ChargingSource', null, 0);
      $Id  = CreateVariable("CHARGING_SOURCES_AVAILABLE_HOME_BASE"        , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_SOURCES_AVAILABLE_INTERNAL_CHARGER" , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_STATE"                              , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_ChargingState', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_NOT_CHARGING"                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_RECONDITIONING_CHARGING"      , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_FULL_CHARGING"                , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_TRICKLE_CHARGING"             , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_WAITING"                      , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CHARGING_STATE_CHARGING_FAULT_CONDITION"     , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CLIFF_LEFT"                                  , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_LEFT"                            , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_RIGHT"                           , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CLIFF_RIGHT"                                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("CLIFF_LEFT_SIGNAL"                           , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_LEFT_SIGNAL"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_FRONT_RIGHT_SIGNAL"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CLIFF_RIGHT_SIGNAL"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("CURRENT"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mA', null, 0);
      $Id  = CreateVariable("DIRT_DETECT"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("DISTANCE"                                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("INFRARED_CHARACTER_LEFT"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("INFRARED_CHARACTER_RIGHT"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("INFRARED_CHARACTER_OMNI"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LEFT_ENCODER_COUNTS"                         , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LEFT_MOTOR_CURRENT"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mA', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER"                                , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_FRONT_RIGHT"                    , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_RIGHT"                          , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_CENTER_RIGHT"                   , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_CENTER_LEFT"                    , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_FRONT_LEFT"                     , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("LIGHT_BUMPER_LEFT"                           , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_LEFT_SIGNAL"                      , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_FRONT_LEFT_SIGNAL"                , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_CENTER_LEFT_SIGNAL"               , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_CENTER_RIGHT_SIGNAL"              , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_FRONT_RIGHT_SIGNAL"               , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("LIGHT_BUMP_RIGHT_SIGNAL"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("MAIN_BRUSH_MOTOR_CURRENT"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mA', null, 0);
      $Id  = CreateVariable("NUMBER_OF_STREAM_PACKETS"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("OI_MODE"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_OIMode', null, 0);
      $Id  = CreateVariable("OI_MODE_OFF"                                 , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("OI_MODE_PASSIVE"                             , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("OI_MODE_SAFE"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("OI_MODE_FULL"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("REQUESTED_VELOCITY"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mms', null, 0);
      $Id  = CreateVariable("REQUESTED_RIGHT_VELOCITY"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mms', null, 0);
      $Id  = CreateVariable("REQUESTED_LEFT_VELOCITY"                     , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mms', null, 0);
      $Id  = CreateVariable("REQUESTED_RADIUS"                            , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mm', null, 0);
      $Id  = CreateVariable("RIGHT_ENCODER_COUNTS"                        , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("RIGHT_MOTOR_CURRENT"                         , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mA', null, 0);
      $Id  = CreateVariable("SIDE_BRUSH_MOTOR_CURRENT"                    , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mA', null, 0);
      $Id  = CreateVariable("STASIS"                                      , 0 /*Boolean*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("SONG_NUMBER"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("SONG_PLAYING"                                , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("TEMPERATURE"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_Temperatur', null, 0);
      $Id  = CreateVariable("VIRTUAL_WALL"                                , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("VOLTAGE"                                     , 1 /*Integer*/,  $CategoryRoombaData,100,'Roomba_mV', null, 0);
      $Id  = CreateVariable("WALL"                                        , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_ON_OFF', null, 0);
      $Id  = CreateVariable("WALL_SIGNAL"                                 , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS"                          , 1 /*Integer*/,  $CategoryRoombaData,100,'', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_SIDE_BRUSH"               , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_Overcurrents', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_MAIN_BRUSH"               , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_Overcurrents', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_RIGHT_WHEEL"              , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_Overcurrents', null, 0);
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_LEFT_WHEEL"               , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_Overcurrents', null, 0);                       
      $Id  = CreateVariable("WHEEL_OVERCURRENTS_VACCUM"                   , 0 /*Boolean*/,  $CategoryRoombaData,100,'Roomba_Overcurrents', null, 0);                       

      $CategorySystemData     = CreateCategoryPath($DataPath.".$roomba_name.SystemData");

      $Id  = CreateVariable("PACKET_REQUESTED"                            , 1 /*Integer*/,  $CategorySystemData,103,'', null, 0);
      $Id  = CreateVariable("PACKET_COUNTER"                              , 1 /*Integer*/,  $CategorySystemData,102,'', null, 0);
      $Id  = CreateVariable("WEGMESSUNG"                                  , 1 /*Integer*/,  $CategorySystemData,510,'', null, 0);
      $Id  = CreateVariable("STARTZEIT"                                   , 3 /*String */,  $CategorySystemData,502,'', null, 0);
      $Id  = CreateVariable("ENDZEIT"                                     , 3 /*String */,  $CategorySystemData,504,'', null, 0);
      $Id  = CreateVariable("LAUFZEIT"                                    , 3 /*String */,  $CategorySystemData,503,'', null, 0);
      $Id  = CreateVariable("BATTERIE"                                    , 1 /*Integer*/,  $CategorySystemData,410,'~Battery.100', null, 0);
      $Id  = CreateVariable("STARTTIMESTAMP"                              , 1 /*Integer*/,  $CategorySystemData,500,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_CHARGING"                      , 0 /*Boolean*/,  $CategorySystemData,401,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_MOVING"                        , 0 /*Boolean*/,  $CategorySystemData,402,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_ONLINE"                        , 0 /*Boolean*/,  $CategorySystemData,403,'', null, 0);
      $Id  = CreateVariable("ROOMBA_STATUS_UNKNOWN"                       , 0 /*Boolean*/,  $CategorySystemData,404,'', null, true);
      $Id  = CreateVariable("POLLING_STATUS"                              , 0 /*Boolean*/,  $CategorySystemData,100,'', null, true);
      $Id  = CreateVariable("ROOMBA_DISTANCE"                             , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("ROOMBA_CHARGING"                             , 0 /*Boolean*/,  $CategorySystemData,400,'', null, 0);
      $Id  = CreateVariable("TIMER"                                       , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("AKTIV"                                       , 0 /*Boolean*/,  $CategorySystemData,10 ,'', null, 0);
      $Id  = CreateVariable("POLLING"                                     , 1 /*Integer*/,  $CategorySystemData,100,'', null, 0);
      $Id  = CreateVariable("XBEE_GATEWAY"                                , 1 /*Integer*/,  $CategorySystemData,900,'', null, $roomba_gateway);
      SetValueInteger($Id,$roomba_gateway);
      $Id  = CreateVariable("XBEE_SPLITTER"                               , 1 /*Integer*/,  $CategorySystemData,901,'', null, $roomba_splitter);
      SetValueInteger($Id,$roomba_splitter);
      $Id  = CreateVariable("XBEE_REGISTERVARIABLE"                       , 1 /*Integer*/,  $CategorySystemData,902,'', null, $roomba_rv_Id);
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


      $CategoryWebFrontData = CreateCategoryPath($DataPath.".$roomba_name.WebFrontData");

      //****************************************************************************
      //  Visu
      //****************************************************************************
      $cmd_init_Id    = CreateVariable("CMD_INIT"         , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_home_Id    = CreateVariable("CMD_HOME"         , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_clean_Id   = CreateVariable("CMD_CLEAN"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_spot_Id    = CreateVariable("CMD_SPOT"         , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_max_Id     = CreateVariable("CMD_MAX"          , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_power_Id   = CreateVariable("CMD_POWER"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_wartung_Id = CreateVariable("CMD_WARTUNG"      , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song1      = CreateVariable("CMD_SONG1"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song2      = CreateVariable("CMD_SONG2"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song3      = CreateVariable("CMD_SONG3"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song4      = CreateVariable("CMD_SONG4"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song5      = CreateVariable("CMD_SONG5"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song6      = CreateVariable("CMD_SONG6"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song7      = CreateVariable("CMD_SONG7"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);
      $cmd_song8      = CreateVariable("CMD_SONG8"        , 1 /*Integer*/,  $CategoryWebFrontData,950,'Roomba_cmdbutton', $webfrontScriptId, 0);

      $varid          = CreateVariable("MOTORS_MAINBRUSH" , 3 /*String */,  $CategoryWebFrontData,100,"~HTMLBox",false,false,"");



      $CategoryIDScriptsSystem = CreateCategoryPath($VisuPath.".$roomba_name.Scripts.System");
      $CategoryIDScriptsRoomba = CreateCategoryPath($VisuPath.".$roomba_name.Scripts.Roomba");
      $CategoryIDScriptsSongs  = CreateCategoryPath($VisuPath.".$roomba_name.Scripts.Songs");
      CreateLink ("System Init" ,   $cmd_init_Id    , $CategoryIDScriptsSystem, 10);      
      CreateLink ("Roomba Clean",   $cmd_clean_Id   , $CategoryIDScriptsRoomba, 20);
      CreateLink ("Roomba Max",     $cmd_max_Id     , $CategoryIDScriptsRoomba, 30);
      CreateLink ("Roomba Spot",    $cmd_spot_Id    , $CategoryIDScriptsRoomba, 40);
      CreateLink ("Roomba Home",    $cmd_home_Id    , $CategoryIDScriptsRoomba, 50);
      CreateLink ("Roomba Wartung", $cmd_wartung_Id , $CategoryIDScriptsRoomba, 60);
      CreateLink ("Roomba Power",   $cmd_power_Id   , $CategoryIDScriptsRoomba, 70);

      CreateLink ("Song 1", $cmd_song1 , $CategoryIDScriptsSongs, 10);
      CreateLink ("Song 2", $cmd_song2 , $CategoryIDScriptsSongs, 20);
      CreateLink ("Song 3", $cmd_song3 , $CategoryIDScriptsSongs, 30);
      CreateLink ("Song 4", $cmd_song4 , $CategoryIDScriptsSongs, 40);
      CreateLink ("Song 5", $cmd_song5 , $CategoryIDScriptsSongs, 50);
      CreateLink ("Song 6", $cmd_song6 , $CategoryIDScriptsSongs, 60);
      CreateLink ("Song 7", $cmd_song7 , $CategoryIDScriptsSongs, 70);
      CreateLink ("Song 8", $cmd_song8 , $CategoryIDScriptsSongs, 80);


      $CategoryIDMotorenSystem = CreateCategoryPath($VisuPath.".$roomba_name.Motoren.Left");
      $CategoryIDMotorenSystem = CreateCategoryPath($VisuPath.".$roomba_name.Motoren.Right");
      $CategoryIDMotorenSystem = CreateCategoryPath($VisuPath.".$roomba_name.Motoren.MainBrush");
      $CategoryIDMotorenSystem = CreateCategoryPath($VisuPath.".$roomba_name.Motoren.SideBrush");
      $CategoryIDMotorenSystem = CreateCategoryPath($VisuPath.".$roomba_name.Motoren.Vacuum");

      $CategoryIDSensorenSystem      = CreateCategoryPath($VisuPath.".$roomba_name.Sensoren");
      $CategoryIDAkkuSystem          = CreateCategoryPath($VisuPath.".$roomba_name.Akku");
      $CategoryIDOverviewSystem      = CreateCategoryPath($VisuPath.".$roomba_name.Uebersicht");
      $CategoryIDInfraredSystem      = CreateCategoryPath($VisuPath.".$roomba_name.Infrarot");
      $CategoryIDCommunicationSystem = CreateCategoryPath($VisuPath.".$roomba_name.Kommunikation");


      }

	echo "\n--- Create Webfront -----------------------------------------------\n";
  $WFC10_Enabled        = $moduleManager->GetConfigValue('Enabled', 		 'WFC10');
  $WFC10_Path           = $moduleManager->GetConfigValue('Path', 			   'WFC10');
  $WFC10_WebFrontID     = $moduleManager->GetConfigValueInt('WebFrontID','WFC10');
  $WFC10_TabPaneParent  = $moduleManager->GetConfigValue('TabParent', 	 'WFC10');
  $WFC10_TabPaneName    = $moduleManager->GetConfigValue('TabName', 		 'WFC10');
  $WFC10_TabPaneItem    = $moduleManager->GetConfigValue('TabItem', 		 'WFC10');
  $WFC10_TabPaneIcon    = $moduleManager->GetConfigValue('TabIcon', 		 'WFC10');
  $WFC10_TabPaneOrder   = $moduleManager->GetConfigValueInt('TabOrder',  'WFC10');
  $WFC10_ConfigId       = $moduleManager->GetConfigValueIntDef('ID', 	   'WFC10', GetWFCIdDefault());
	if ( $WFC10_WebFrontID > 0 )
      $WFC10_ConfigId = $WFC10_WebFrontID;

  DeleteWFCItems($WFC10_ConfigId, $WFC10_TabPaneItem);
  
  if ($WFC10_Enabled) 
    {
    $src  = IPS_GetKernelDir() ."\\webfront\\user\\Roomba\\images\\Roomba.png";
    $dest = IPS_GetKernelDir() ."\\webfront\\img\\icons\\Roomba.png";
      
    copy($src,$dest);
    $categoryId_WebFront = CreateCategoryPath($WFC10_Path);

    

    CreateWFCItemTabPane   ($WFC10_ConfigId, $WFC10_TabPaneItem, $WFC10_TabPaneParent,  $WFC10_TabPaneOrder, $WFC10_TabPaneName, $WFC10_TabPaneIcon);

    $order = 10 ;
    foreach ( $roomba_array as $roomba )
        {
        $roomba_name     = $roomba[0];
        
        CreateWFCItemTabPane   ($WFC10_ConfigId, 'Roomba1007'.$roomba_name, $WFC10_TabPaneItem ,  $order , $roomba_name, $WFC10_TabPaneIcon);
        $order = $order + 10 ;

        $CategoryIDScriptsSystem = get_ObjectIDByPath($VisuPath.".$roomba_name.Scripts.System");
        $CategoryIDScriptsRoomba = get_ObjectIDByPath($VisuPath.".$roomba_name.Scripts.Roomba");
        $CategoryIDScriptsSongs  = get_ObjectIDByPath($VisuPath.".$roomba_name.Scripts.Songs");


        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'overview'       , 'Roomba1007'.$roomba_name , 10 , 'Übersicht'      , '', 1 /*Vertical*/, 33 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');

        // Webfront Motoren
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'motors'                       , 'Roomba1007'.$roomba_name             , 20 , 'Antriebe'   , ''  , 0 /*Vertical*/, 50 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'motors_top'                   , 'Roomba1007'.$roomba_name.'motors'    , 10 , ''           , ''  , 1 /*Vertical*/, 50 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'motors_bottom_right'          , 'Roomba1007'.$roomba_name.'motors'    , 20 , ''           , ''  , 1 /*Vertical*/, 66 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'motors_bottom_left_right'     , 'Roomba1007'.$roomba_name.'motors_bottom_right'    , 1 , ''           , ''  , 1 /*Vertical*/, 50 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');


        // Webfront Sensoren
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'sensoren'       , 'Roomba1007'.$roomba_name , 30 , 'Sensoren'       , '', 1 /*Vertical*/, 33 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'akku'           , 'Roomba1007'.$roomba_name , 40 , 'Akku'           , '', 1 /*Vertical*/, 33 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'infrared'       , 'Roomba1007'.$roomba_name , 50 , 'Infrarot'       , '', 1 /*Vertical*/, 33 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'communication'  , 'Roomba1007'.$roomba_name , 60 , 'Kommunikation'  , '', 1 /*Vertical*/, 33 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');

        // Webfront Kommandos
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'commands'                     , 'Roomba1007'.$roomba_name             , 70 , 'Kommandos'  , '' , 1 /*Vertical*/, 33 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
        CreateWFCItemSplitPane ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'commands_scripts_right'       , 'Roomba1007'.$roomba_name.'commands'  , 10 , ''           , '' , 1 /*Vertical*/, 50 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');

        CreateWFCItemCategory  ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'commands_scripts_left'        , 'Roomba1007'.$roomba_name.'commands'                , 1, '', '', $CategoryIDScriptsSystem /*BaseId*/, 'false' /*BarBottomVisible*/);
        CreateWFCItemCategory  ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'commands_scripts_right_right' , 'Roomba1007'.$roomba_name.'commands_scripts_right'  , 1, '', '', $CategoryIDScriptsRoomba /*BaseId*/, 'false' /*BarBottomVisible*/);
        CreateWFCItemCategory  ($WFC10_ConfigId, 'Roomba1007'.$roomba_name.'commands_scripts_right_left'  , 'Roomba1007'.$roomba_name.'commands_scripts_right'  , 2, '', '', $CategoryIDScriptsSongs  /*BaseId*/, 'false' /*BarBottomVisible*/);
 


        }

//    $categoryId_WebFront         = CreateCategoryPath($WFC10_Path);
//    $categoryId_WebFrontTopLeft  = CreateCategory(  'TopLeft',  $categoryId_WebFront, 10);
//    $categoryId_WebFrontTopRight = CreateCategory(  'TopRight', $categoryId_WebFront, 20);
//    $categoryId_WebFrontBottom   = CreateCategory(  'Bottom',   $categoryId_WebFront, 30);
//    $categoryId_WebFrontRight    = CreateCategory(  'Right',    $categoryId_WebFront, 40);

//    $tabItem = $WFC10_TabPaneItem.$WFC10_TabItem;
//    DeleteWFCItems($WFC10_ConfigId, $tabItem);
//    CreateWFCItemTabPane   ($WFC10_ConfigId, $WFC10_TabPaneItem, $WFC10_TabPaneParent,  $WFC10_TabPaneOrder, $WFC10_TabPaneName, $WFC10_TabPaneIcon);
//    CreateWFCItemSplitPane ($WFC10_ConfigId, $tabItem,           $WFC10_TabPaneItem,    $WFC10_TabOrder,     $WFC10_TabName,     $WFC10_TabIcon, 1 /*Vertical*/, 300 /*Width*/, 1 /*Target=Pane2*/, 1/*UsePixel*/, 'true');
//    CreateWFCItemSplitPane ($WFC10_ConfigId,   $tabItem.'_Left',        $tabItem,          10, '', '', 0 /*Horicontal*/, 205 /*Height*/, 0 /*Target=Pane1*/, 1 /*UsePixel*/, 'true');
//    CreateWFCItemCategory  ($WFC10_ConfigId,   $tabItem.'_Right',        $tabItem,         20, '', '', $categoryId_WebFrontRight    /*BaseId*/, 'false' /*BarBottomVisible*/);
//    CreateWFCItemSplitPane ($WFC10_ConfigId,     $tabItem.'_Top',        $tabItem.'_Left', 10, '', '', 1 /*Vertical*/, 50 /*Width*/, 0 /*Target=Pane1*/, 0 /*UsePercentage*/, 'true');
//    CreateWFCItemCategory  ($WFC10_ConfigId,     $tabItem.'_Bottom',     $tabItem.'_Left', 20, '', '', $categoryId_WebFrontBottom   /*BaseId*/, 'false' /*BarBottomVisible*/);
//    CreateWFCItemCategory  ($WFC10_ConfigId,       $tabItem.'_TopLeft',  $tabItem.'_Top',  10, '', '', $categoryId_WebFrontTopLeft  /*BaseId*/, 'false' /*BarBottomVisible*/);
//    CreateWFCItemCategory  ($WFC10_ConfigId,       $tabItem.'_TopRight', $tabItem.'_Top',  20, '', '', $categoryId_WebFrontTopRight /*BaseId*/, 'false' /*BarBottomVisible*/);



    }

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



?>