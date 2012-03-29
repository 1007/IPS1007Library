<?

	IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");

	IPSUtils_Include ("RoombaFuncpool.inc.php","IPSLibrary::app::hardware::Roomba");

	IPSUtils_Include ("Roomba_Configuration.inc.php","IPSLibrary::config::hardware::Roomba");

	$HardwarePathId  = get_ObjectIDByPath("Hardware.Roomba");

	$register_array = IPS_GetChildrenIDs($HardwarePathId);


	foreach ( $register_array as $register_variable )
	   {
	   $object = IPS_GetObject($register_variable);
	   
	   $device_id = XBee_GetDeviceID(IPS_GetInstanceParentID($register_variable));

		$name = $object['ObjectName'];
		$object_id = $object['ObjectID'];

		$DataPathId = get_ObjectIDByPath("Program.IPSLibrary.data.hardware.Roomba.$name.SystemData");


		$objecttyp = $object['ObjectType'];
		if ( $objecttyp == 1 )
		   {
			$polling_id = IPS_GetVariableIDByName('POLLING_STATUS',$DataPathId);
			if ( GetValueBoolean($polling_id))
			   {
			   command(QUERY_LIST,array(1,100),$device_id,$DataPathId);
				script_timing($DataPathId);
			   }
	      }
	   }


function script_timing($DataPathId)
	{
   
	$debug = false;

	$poll 		= 0;
	$online 		= false;
	$charging 	= false;
	$moving 		= false;
	$unknown    = true;

   $t1 = time() ;

	$array = IPS_GetVariable(IPS_GetVariableIDByName('PACKET_COUNTER',$DataPathId));

   $t2 = $array["VariableUpdated"];
	$diff = $t1 - $t2;
	$diff = 10;
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
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_UNKNOWN'	,$DataPathId),$unknown);

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