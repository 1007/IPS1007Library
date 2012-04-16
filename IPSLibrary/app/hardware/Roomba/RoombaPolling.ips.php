<?

	IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");

	IPSUtils_Include ("RoombaFuncpool.inc.php","IPSLibrary::app::hardware::Roomba");

	IPSUtils_Include ("Roomba_Configuration.inc.php","IPSLibrary::config::hardware::Roomba");

	$HardwarePathId  = get_ObjectIDByPath("Hardware.Roomba");

	$register_array = IPS_GetChildrenIDs($HardwarePathId);

	$one_moving   = false;
	$one_charging = false;
	$poll = POLLING_DEFAULT;
	
	foreach ( $register_array as $register_variable )
	   {
	   $object = IPS_GetObject($register_variable);
	   
	   $device_id = XBee_GetDeviceID(IPS_GetInstanceParentID($register_variable));

		$name      = $object['ObjectName'];
		$object_id = $object['ObjectID'];

		$DataPathId = get_ObjectIDByPath("Program.IPSLibrary.data.hardware.Roomba.$name.SystemData");

		if ( GetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_CHARGING',$DataPathId))  )
			$one_charging = true;
		if ( GetValueInteger(IPS_GetVariableIDByName('ROOMBA_DISTANCE',$DataPathId)) != 0 )
			$one_moving = true;

		if ( GetValueBoolean(IPS_GetVariableIDByName('POLLING_AKTIV',$DataPathId)))   // ist Polling aktiviert ?
		if ( GetValueBoolean(IPS_GetVariableIDByName('POLLING_STATUS',$DataPathId)))  // ist Polling noch aktiv ?

		if ( polling_timing($DataPathId) )
			   {
			   echo "\nPolling";
			   command(QUERY_LIST,array(1,100),$device_id,$DataPathId);
			   }
			   
		if ( !GetValueBoolean(IPS_GetVariableIDByName('POLLING_AKTIV',$DataPathId)))   // ist Polling aktiviert ?
   			SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_ONLINE'		,$DataPathId),false);


		}


	if ( $one_charging )
		$poll = POLLING_CHARGING;
	if ( $one_moving )
		$poll = POLLING_MOVING;

	$timer = IPS_GetScriptTimer($_IPS['SELF']);
	if ( $poll != $timer )
		IPS_SetScriptTimer($_IPS['SELF'],$poll);


function polling_timing($DataPathId)
	{
	$status 	 = false;
	$online 	 = false;
	$charging = false;
	$moving 	 = false;

   $t1 = time() ;

	$array = IPS_GetVariable(IPS_GetVariableIDByName('PACKET_COUNTER',$DataPathId));

   $t2 = $array["VariableUpdated"];
	$diff = $t1 - $t2;
	
	if ( $diff < POLLING_OFFLINE )
	   {
		$online = true;
		}


   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_CHARGING'	,$DataPathId),$charging);
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_ONLINE'		,$DataPathId),$online);
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_MOVING'		,$DataPathId),$moving);


	$status = true;
	return $status;
	
	}

function script_timing($DataPathId)
	{
   
	$debug = false;


	$online 		= false;
	$charging 	= false;
	$moving 		= false;
	$unknown    = true;

   $t1 = time() ;

	$array = IPS_GetVariable(IPS_GetVariableIDByName('PACKET_COUNTER',$DataPathId));

   $t2 = $array["VariableUpdated"];
	$diff = $t1 - $t2;
	//$diff = 10;
	if ( $diff < POLLING_OFFLINE )
	   {
		$online = true;
		$unknown = false;
		}
		
	if ( $online )
	   {
		if ( GetValueBoolean(IPS_GetVariableIDByName('ROOMBA_CHARGING',$DataPathId))  )
			$charging = true;
		if ( GetValueInteger(IPS_GetVariableIDByName('ROOMBA_DISTANCE',$DataPathId)) != 0 )
			$moving = true;
		if ( $charging == false and $moving == false )
	   	$unknown = true;
	   }

	
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_CHARGING'	,$DataPathId),$charging);
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_ONLINE'		,$DataPathId),$online);
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_MOVING'		,$DataPathId),$moving);

	$poll = POLLING_OFFLINE;
	if ( $online == true and $charging == true and $moving == false )
		{
	   $poll = POLLING_CHARGING;

		}
	if ( $online == true and $charging == false and $moving == true )
	   {
	   $poll = POLLING_MOVING;

		}
	if ( $online == true and $charging == false and $moving == false )
	   {
	   $poll = POLLING_UNKNOWN;

		}

	$timer = IPS_GetScriptTimer($_IPS['SELF']);
	if ( $poll != $timer )
		IPS_SetScriptTimer($_IPS['SELF'],$poll);

	}


?>