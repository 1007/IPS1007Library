<?
	IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");

	IPSUtils_Include ("Roomba_Configuration.inc.php", 	"IPSLibrary::config::hardware::Roomba");
	IPSUtils_Include ("RoombaFuncpool.inc.php",    		"IPSLibrary::app::hardware::Roomba");

	$instr = "";
	
	if ($_IPS['SENDER'] == "RegisterVariable")
	   {
		$absender = $_IPS['INSTANCE'];
		$instr = $IPS_VALUE;
		}

	$debug = false;
	
	$object = IPS_GetObject($absender);
	$name = $object['ObjectName'];
	$SystemDataPathId 		= get_ObjectIDByPath("Program.IPSLibrary.data.hardware.Roomba.$name.SystemData");
	$RoombaDataPathId 		= get_ObjectIDByPath("Program.IPSLibrary.data.hardware.Roomba.$name.RoombaData");
	$LighthouseDataPathId 	= get_ObjectIDByPath("Program.IPSLibrary.data.hardware.Roomba.$name.LighthouseData");

	$laenge = strlen($instr);

	$packet = GetValueInteger(IPS_GetVariableIDByName('PACKET_REQUESTED',$SystemDataPathId));
	
	$counter = GetValueInteger(IPS_GetVariableIDByName('PACKET_COUNTER',$SystemDataPathId));
	$counter++;
	SetValueInteger(IPS_GetVariableIDByName('PACKET_COUNTER',$SystemDataPathId),$counter);

	if ( $debug ) debug_packet($instr);

	if ( $laenge == 26 and $packet == 0   )
	   packet_group_0($instr);
	if ( $laenge == 10 and $packet == 1   )
	   packet_group_1($instr);
	if ( $laenge == 6  and $packet == 2   )
	   packet_group_2($instr);
	if ( $laenge == 10 and $packet == 3   )
	   packet_group_3($instr);
	if ( $laenge == 14 and $packet == 4   )
	   packet_group_4($instr);
	if ( $laenge == 12 and $packet == 5   )
	   packet_group_5($instr);
	if ( $laenge == 52 and $packet == 6   )
	   packet_group_6($instr);
	if ( $laenge == 80 and $packet == 100 )
	   packet_group_100($instr);
	if ( $laenge == 28 and $packet == 101 )
	   packet_group_101($instr);
	if ( $laenge == 12 and $packet == 106 )
	   packet_group_106($instr);
	if ( $laenge == 9  and $packet == 107 )
	   packet_group_107($instr);

	// Ladezustand berechnen
	$akt_mAh = GetValueInteger(IPS_GetVariableIDByName('BATTERY_CHARGE',$RoombaDataPathId));
	$max_mAh = GetValueInteger(IPS_GetVariableIDByName('BATTERY_CAPACITY',$RoombaDataPathId));
	$prozent = round(strval(($akt_mAh/$max_mAh)*100));
	if ( $prozent > 100 ) $prozent = 100;
	if ( $prozent < 0   ) $prozent = 0;

	SetValueInteger(IPS_GetVariableIDByName('BATTERIE',$SystemDataPathId),$prozent);

	//SetValueInteger(BATTERIE,$bat);

	return;

	$distance = GetValueInteger(DISTANCE);
	$angle 	 = GetValueInteger(ANGLE);
	$left     = GetValueInteger(LEFT_ENCODER_COUNTS);
	$right    = GetValueInteger(RIGHT_ENCODER_COUNTS);

	$text = "," . $distance . "," . $angle . "," . $left . "," . $right;
	if ( GetValueBoolean(ROOMBA_STATUS_MOVING) )
   	funcpool_logging("roomba.log",$text);

//***********************************************************************
function packet_7($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 7	Bumps and Wheel Drops
	$b1 = u_0bis255(7,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS',$RoombaDataPathId));
	
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS',$RoombaDataPathId),$b1);else return;

	if ( ($b1 & 1) != ($b2 & 1))
		if ( ($b1 & 1) == 1 )
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_BUMP_RIGHT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_BUMP_RIGHT',$RoombaDataPathId),false);
	if ( ($b1 & 2) != ($b2 & 2))
		if ( ($b1 & 2) == 2 )
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_BUMP_LEFT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_BUMP_LEFT',$RoombaDataPathId),false);
	if ( ($b1 & 4) != ($b2 & 4))
		if ( ($b1 & 4) == 4 )
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_WHEEL_DROP_RIGHT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_WHEEL_DROP_RIGHT',$RoombaDataPathId),false);
	if ( ($b1 & 8) != ($b2 & 8))
		if ( ($b1 & 8) == 8 )
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_WHEEL_DROP_LEFT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUMP_AND_WHEEL_DROPS_WHEEL_DROP_LEFT',$RoombaDataPathId),false);

	}
	
function packet_8($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 8	Wall
	$b1 = u_0bis1(8,$Byte[0]);
	$b2 = GetValueBoolean(IPS_GetVariableIDByName('WALL',$RoombaDataPathId));

	if ( $b1 != $b2 )
			SetValueBoolean(IPS_GetVariableIDByName('WALL',$RoombaDataPathId),$b1);

	}

function packet_9($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 9	Cliff Left
	$b1 = u_0bis1(9,$Byte[0]);
	$b2 = GetValueBoolean(IPS_GetVariableIDByName('CLIFF_LEFT',$RoombaDataPathId));

	if ( $b1 != $b2 )
			SetValueBoolean(IPS_GetVariableIDByName('CLIFF_LEFT',$RoombaDataPathId),$b1);

	}
	
function packet_10($Byte)
	{
	GLOBAL $RoombaDataPathId; 
	//Packet ID: 10	Cliff Front Left
	$b1 = u_0bis1(10,$Byte[0]);
	$b2 = GetValueBoolean(IPS_GetVariableIDByName('CLIFF_FRONT_LEFT',$RoombaDataPathId));

	if ( $b1 != $b2 )
			SetValueBoolean(IPS_GetVariableIDByName('CLIFF_FRONT_LEFT',$RoombaDataPathId),$b1);

	}
	
function packet_11($Byte)
	{
	GLOBAL $RoombaDataPathId; 
	//Packet ID: 11	Cliff Front Right
	$b1 = u_0bis1(11,$Byte[0]);
	$b2 = GetValueBoolean(IPS_GetVariableIDByName('CLIFF_FRONT_RIGHT',$RoombaDataPathId));
	
	if ( $b1 != $b2 )
			SetValueBoolean(IPS_GetVariableIDByName('CLIFF_FRONT_RIGHT',$RoombaDataPathId),$b1);

	}
	
function packet_12($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 12	Cliff Right
	$b1 = u_0bis1(12,$Byte[0]);
	$b2 = GetValueBoolean(IPS_GetVariableIDByName('CLIFF_RIGHT',$RoombaDataPathId));

	if ( $b1 != $b2 )
			SetValueBoolean(IPS_GetVariableIDByName('CLIFF_RIGHT',$RoombaDataPathId),$b1);

	}

function packet_13($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 13	Virtual Wall
	$b1 = u_0bis1(13,$Byte[0]);
	$b2 = GetValueBoolean(IPS_GetVariableIDByName('VIRTUAL_WALL',$RoombaDataPathId));

	if ( $b1 != $b2 )
			SetValueBoolean(IPS_GetVariableIDByName('VIRTUAL_WALL',$RoombaDataPathId),$b1);

	}

function packet_14($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 14   Wheel Overcurrents
	$b1 = u_0bis255(14,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS',$RoombaDataPathId));

	
	if ( $b1 != $b2 )
			SetValueInteger(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS',$RoombaDataPathId),$b1);else return;

	if ( ($b1 & 1) != ($b2 & 1))
		if ( ($b1 & 1) == 1 )
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_SIDE_BRUSH',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_SIDE_BRUSH',$RoombaDataPathId),false);
	if ( ($b1 & 4) != ($b2 & 4))
		if ( ($b1 & 4) == 4 )
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_MAIN_BRUSH',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_MAIN_BRUSH',$RoombaDataPathId),false);
	if ( ($b1 & 8) != ($b2 & 8))
		if ( ($b1 & 8) == 8 )
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_RIGHT_WHEEL',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_RIGHT_WHEEL',$RoombaDataPathId),false);
	if ( ($b1 &16) != ($b2 &16))
		if ( ($b1 &16) ==16 )
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_LEFT_WHEEL',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('WHEEL_OVERCURRENTS_LEFT_WHEEL',$RoombaDataPathId),false);

	}

function packet_15($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 15   Dirt Detect
	$b1 = u_0bis255(15,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('DIRT_DETECT',$RoombaDataPathId));

	if ( $b1 != $b2 )
			SetValueInteger(IPS_GetVariableIDByName('DIRT_DETECT',$RoombaDataPathId),$b1);

	}

function packet_16($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 16   Unused
	}
function packet_17($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 17	Infrared Character Omni
	$b1 = u_0bis255(17,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('INFRARED_CHARACTER_OMNI',$RoombaDataPathId));
	if ( $b1 == $b2 ) return;
	SetValueInteger(IPS_GetVariableIDByName('INFRARED_CHARACTER_OMNI',$RoombaDataPathId),$b1);
	//lighthouse(INFRARED_CHARACTER_OMNI,$b1);
	}
function packet_18($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 18   Buttons
	$b1 = u_0bis255(18,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('BUTTONS',$RoombaDataPathId));
	if ( $b1 != $b2 )SetValueInteger(IPS_GetVariableIDByName('BUTTONS',$RoombaDataPathId),$b1);else return;
	
	if ( ($b1 & 1) != ($b2 & 1))
		if ( ($b1 & 1) == 1 )
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_CLEAN',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_CLEAN',$RoombaDataPathId),false);
	if ( ($b1 & 2) != ($b2 & 2))
		if ( ($b1 & 2) == 2 )
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_SPOT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_SPOT',$RoombaDataPathId),false);
	if ( ($b1 & 4) != ($b2 & 4))
		if ( ($b1 & 4) == 4 )
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_DOCK',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_DOCK',$RoombaDataPathId),false);
	if ( ($b1 & 8) != ($b2 & 8))
		if ( ($b1 & 8) == 8 )
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_MINUTE',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_MINUTE',$RoombaDataPathId),false);
	if ( ($b1 &16) != ($b2 &16))
		if ( ($b1 &16) ==16 )
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_HOUR',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_HOUR',$RoombaDataPathId),false);
	if ( ($b1 &32) != ($b2 &32))
		if ( ($b1 &32) ==32 )
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_DAY',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_DAY',$RoombaDataPathId),false);
	if ( ($b1 &64) != ($b2 &64))
		if ( ($b1 &64) ==64 )
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_SCHEDULE',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_SCHEDULE',$RoombaDataPathId),false);
	if ( ($b1 &128)!= ($b2&128))
		if ( ($b1&128) ==128)
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_CLOCK',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('BUTTONS_CLOCK',$RoombaDataPathId),false);
	}

function packet_19($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 19   DISTANCE
	$b1 = u_32768bis32767(19,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('DISTANCE',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('DISTANCE',$RoombaDataPathId),$b1);

	//$km = GetValueInteger(KILOMETERZAEHLER);
	//$km = $km + abs($b1);

	//SetValueInteger(KILOMETERZAEHLER,$km);

	}
function packet_20($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 20   ANGLE
	$b1 = u_32768bis32767(20,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('ANGLE',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('ANGLE',$RoombaDataPathId),$b1);
	}
	
function packet_21($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 21	Ladestatus
	$b1 = u_0bis255(21,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('CHARGING_STATE',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('CHARGING_STATE',$RoombaDataPathId),$b1);else return;

	if ( $b1	== 0 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_NOT_CHARGING',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_NOT_CHARGING',$RoombaDataPathId),false);
	if ( $b1 == 1 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_RECONDITIONING_CHARGING',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_RECONDITIONING_CHARGING',$RoombaDataPathId),false);
	if ( $b1 == 2 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_FULL_CHARGING',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_FULL_CHARGING',$RoombaDataPathId),false);
	if ( $b1 == 3 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_TRICKLE_CHARGING',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_TRICKLE_CHARGING',$RoombaDataPathId),false);
	if ( $b1 == 4 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_WAITING',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_WAITING',$RoombaDataPathId),false);
	if ( $b1 == 5 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_CHARGING_FAULT_CONDITION',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_STATE_CHARGING_FAULT_CONDITION',$RoombaDataPathId),false);

	}

function packet_22($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 22	Batteriespannung
	$b1 = u_0bis65535(23,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('VOLTAGE',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('VOLTAGE',$RoombaDataPathId),$b1);

	}

function packet_23($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 23	Batteriestrom
	$b1 = u_32768bis32767(23,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('CURRENT',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('CURRENT',$RoombaDataPathId),$b1);
	}

function packet_24($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 24	Batterietemperatur
	$b1 = u_128bis127(24,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('TEMPERATURE',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('TEMPERATURE',$RoombaDataPathId),$b1);
	}
	
function packet_25($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 25	Batterieaufladung
	$b1 = u_0bis65535(25,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('BATTERY_CHARGE',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('BATTERY_CHARGE',$RoombaDataPathId),$b1);
	}

function packet_26($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 26	Batteriekapazitaet
	$b1 = u_0bis65535(26,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('BATTERY_CAPACITY',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('BATTERY_CAPACITY',$RoombaDataPathId),$b1);
	}

function packet_27($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 27	Wall Signal
	$b1 = u_0bis65535(27,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('WALL_SIGNAL',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('WALL_SIGNAL',$RoombaDataPathId),$b1);
	}

function packet_28($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 28	Cliff Left Signal
	$b1 = u_0bis65535(28,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('CLIFF_LEFT_SIGNAL',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('CLIFF_LEFT_SIGNAL',$RoombaDataPathId),$b1);
	}

function packet_29($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 29	Cliff Front Left Signal
	$b1 = u_0bis65535(29,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('CLIFF_FRONT_LEFT_SIGNAL',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('CLIFF_FRONT_LEFT_SIGNAL',$RoombaDataPathId),$b1);
	}

function packet_30($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 30	Cliff Front Right Signal
	$b1 = u_0bis65535(30,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('CLIFF_FRONT_RIGHT_SIGNAL',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('CLIFF_FRONT_RIGHT_SIGNAL',$RoombaDataPathId),$b1);
	}

function packet_31($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 31	Cliff Right Signal
	$b1 = u_0bis65535(31,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('CLIFF_RIGHT_SIGNAL',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('CLIFF_RIGHT_SIGNAL',$RoombaDataPathId),$b1);
	}

function packet_32($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 32	Unused

	}
function packet_33($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 33	Unused

	}
function packet_34($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 34	Ladequelle
	echo "\nCharging sourve" ;
	print_r( $Byte);
	$b1 = u_0bis255(34,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('CHARGING_SOURCES_AVAILABLE',$RoombaDataPathId));
	echo $RoombaDataPathId."--".$b1,$b2;
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('CHARGING_SOURCES_AVAILABLE',$RoombaDataPathId),$b1);else return;
		
	if ( ($b1 & 1) == 1 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_SOURCES_AVAILABLE_INTERNAL_CHARGER',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_SOURCES_AVAILABLE_INTERNAL_CHARGER',$RoombaDataPathId),false);
	if ( ($b1 & 2) == 2 )
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_SOURCES_AVAILABLE_HOME_BASE',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('CHARGING_SOURCES_AVAILABLE_HOME_BASE',$RoombaDataPathId),false);

	}

function packet_35($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 35	OI Mode

	$b1 = u_0bis255(35,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('OI_MODE',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('OI_MODE',$RoombaDataPathId),$b1);else return;
	
	if ( $b1 == 0 )
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_OFF',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_OFF',$RoombaDataPathId),false);
	if ( $b1 == 1 )
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_PASSIVE',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_PASSIVE',$RoombaDataPathId),false);
	if ( $b1 == 2 )
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_SAFE',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_SAFE',$RoombaDataPathId),false);
	if ( $b1 == 3 )
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_FULL',$RoombaDataPathId),true);
	else
		SetValueBoolean(IPS_GetVariableIDByName('OI_MODE_FULL',$RoombaDataPathId),false);

	}
	
function packet_36($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 36	Lied Nummer
	$b1 = u_0bis255(36,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('SONG_NUMBER',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('SONG_NUMBER',$RoombaDataPathId),$b1);
	}
	
function packet_37($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 37	Lied
	$b1 = u_0bis255(37,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('SONG_PLAYING',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('SONG_PLAYING',$RoombaDataPathId),$b1);

	}
	
function packet_38($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 38	Anzahl Stream Pakete
	$b1 = u_0bis255(38,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('NUMBER_OF_STREAM_PACKETS',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('NUMBER_OF_STREAM_PACKETS',$RoombaDataPathId),$b1);
	}
	
function packet_39($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 39	Request Velocity
	$b1 = u_32768bis32767(39,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('REQUESTED_VELOCITY',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('REQUESTED_VELOCITY',$RoombaDataPathId),$b1);

	}

function packet_40($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 40	Request Radius
	$b1 = u_32768bis32767(40,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('REQUESTED_RADIUS',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('REQUESTED_RADIUS',$RoombaDataPathId),$b1);

	}
	
function packet_41($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 41	Request Right Velocity
	$b1 = u_32768bis32767(41,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('REQUESTED_RIGHT_VELOCITY',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('REQUESTED_RIGHT_VELOCITY',$RoombaDataPathId),$b1);

	}

function packet_42($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 42	Request Left Velocity
	$b1 = u_32768bis32767(42,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('REQUESTED_LEFT_VELOCITY',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('REQUESTED_LEFT_VELOCITY',$RoombaDataPathId),$b1);

	}

function packet_43($Byte)
	{
   GLOBAL $RoombaDataPathId;
	//Packet ID: 43	Right Encoder Counts
	$b1 = u_0bis65535(43,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('RIGHT_ENCODER_COUNTS',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('RIGHT_ENCODER_COUNTS',$RoombaDataPathId),$b1);


	}
function packet_44($Byte)
	{
	GLOBAL $RoombaDataPathId;
   //Packet ID: 44	Left Encoder Counts
	$b1 = u_0bis65535(44,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LEFT_ENCODER_COUNTS',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LEFT_ENCODER_COUNTS',$RoombaDataPathId),$b1);

	}

function packet_45($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 45	Light Bumper
	$b1 = u_0bis255(45,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMPER',$RoombaDataPathId));
	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMPER',$RoombaDataPathId),$b1);else return;

	if ( ($b1 & 1) != ($b2 & 1))
		if ( ($b1 & 1) == 1 )
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_LEFT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_LEFT',$RoombaDataPathId),false);
	if ( ($b1 & 2) != ($b2 & 2))
		if ( ($b1 & 2) == 2 )
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_FRONT_LEFT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_FRONT_LEFT',$RoombaDataPathId),false);
	if ( ($b1 & 4) != ($b2 & 4))
		if ( ($b1 & 4) == 4 )
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_CENTER_LEFT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_CENTER_LEFT',$RoombaDataPathId),false);
	if ( ($b1 & 8) != ($b2 & 8))
		if ( ($b1 & 8) == 8 )
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_CENTER_RIGHT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_CENTER_RIGHT',$RoombaDataPathId),false);
	if ( ($b1 &16) != ($b2 &16))
		if ( ($b1 &16) ==16 )
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_FRONT_RIGHT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_FRONT_RIGHT',$RoombaDataPathId),false);
	if ( ($b1 &32) != ($b2 &32))
		if ( ($b1 &32) ==32 )
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_RIGHT',$RoombaDataPathId),true);
		else
			SetValueBoolean(IPS_GetVariableIDByName('LIGHT_BUMPER_RIGHT',$RoombaDataPathId),false);

	}

function packet_46($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 46	Light Bump Left Signal
	$b1 = u_0bis65535(46,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_LEFT_SIGNAL',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_LEFT_SIGNAL',$RoombaDataPathId),$b1);

	}
	
function packet_47($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 47	Light Bump Front Left Signal
	$b1 = u_0bis65535(47,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_FRONT_LEFT_SIGNAL',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_FRONT_LEFT_SIGNAL',$RoombaDataPathId),$b1);

	}
function packet_48($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 48	Light Bump Center Left Signal
	$b1 = u_0bis65535(48,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_CENTER_LEFT_SIGNAL',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_CENTER_LEFT_SIGNAL',$RoombaDataPathId),$b1);

	}

function packet_49($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 49	Light Bump Center Right Signal
	$b1 = u_0bis65535(49,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_CENTER_RIGHT_SIGNAL',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_CENTER_RIGHT_SIGNAL',$RoombaDataPathId),$b1);

	}

function packet_50($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 50	Light Bump Front Right Signal
	$b1 = u_0bis65535(50,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_FRONT_RIGHT_SIGNAL',$RoombaDataPathId));

	if ( $b1 != $b2 );
		SetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_FRONT_RIGHT_SIGNAL',$RoombaDataPathId),$b1);

	}

function packet_51($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 51	Light Bump Right Signal
	$b1 = u_0bis65535(51,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_RIGHT_SIGNAL',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LIGHT_BUMP_RIGHT_SIGNAL',$RoombaDataPathId),$b1);

	}

function packet_52($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 52	Infrared Character Left"
	$b1 = u_0bis255(52,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('INFRARED_CHARACTER_LEFT',$RoombaDataPathId));

	if ( $b1 == $b2 ) return;

	SetValueInteger(IPS_GetVariableIDByName('INFRARED_CHARACTER_LEFT',$RoombaDataPathId),$b1);
	//lighthouse(INFRARED_CHARACTER_LEFT,$b1);

	}
	
function packet_53($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 53	Infrared Character Right
	$b1 = u_0bis255(53,$Byte[0]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('INFRARED_CHARACTER_RIGHT',$RoombaDataPathId));

	if ( $b1 == $b2 ) return;

	SetValueInteger(IPS_GetVariableIDByName('INFRARED_CHARACTER_RIGHT',$RoombaDataPathId),$b1);

	//lighthouse(INFRARED_CHARACTER_RIGHT,$b1);

	}

function packet_54($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 54	Left Motor Current
	$b1 = u_32768bis32767(54,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('LEFT_MOTOR_CURRENT',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('LEFT_MOTOR_CURRENT',$RoombaDataPathId),$b1);

	}
	
function packet_55($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 55	Right Motor Current
	$b1 = u_32768bis32767(55,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('RIGHT_MOTOR_CURRENT',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('RIGHT_MOTOR_CURRENT',$RoombaDataPathId),$b1);

	}

function packet_56($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 56	Main Brush Motor Current
	$b1 = u_32768bis32767(56,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('MAIN_BRUSH_MOTOR_CURRENT',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('MAIN_BRUSH_MOTOR_CURRENT',$RoombaDataPathId),$b1);

	}

function packet_57($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 57	Side Brush Motor Current
	$b1 = u_32768bis32767(57,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(IPS_GetVariableIDByName('SIDE_BRUSH_MOTOR_CURRENT',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueInteger(IPS_GetVariableIDByName('SIDE_BRUSH_MOTOR_CURRENT',$RoombaDataPathId),$b1);

	}

function packet_58($Byte)
	{
	GLOBAL $RoombaDataPathId;
	//Packet ID: 58	Stasis
	$b1 = u_0bis1(58,$Byte[0]);
	$b2 = GetValueBoolean(IPS_GetVariableIDByName('STASIS',$RoombaDataPathId));

	if ( $b1 != $b2 )
		SetValueBoolean(IPS_GetVariableIDByName('STASIS',$RoombaDataPathId),$b1);

	}


//******************************************************************************
// Packet Group 0
//******************************************************************************
function packet_group_0($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:0";
	//BYTE 0		   Packet ID: 7	Bumps and Wheel Drops
	packet_7 (array($Byte[0]));
	//BYTE 1       Packet ID: 8	Wall
	packet_8 (array($Byte[1]));
	//BYTE 2       Packet ID: 9	Cliff Left
	packet_9 (array($Byte[2]));
	//BYTE 3       Packet ID: 10	Cliff Front Left
	packet_10(array($Byte[3]));
	//BYTE 4       Packet ID: 11	Cliff Front Right
	packet_11(array($Byte[4]));
	//BYTE 5       Packet ID: 12	Cliff Right
	packet_12(array($Byte[5]));
	//BYTE 6       Packet ID: 13	Virtual Wall
	packet_13(array($Byte[6]));
	//BYTE 7       Packet ID: 14	Wheel Overcurrents
	packet_14(array($Byte[7]));
	//BYTE 8       Packet ID: 15	Schmutzsensor
	packet_15(array($Byte[8]));
	//BYTE 9       Packet ID: 16	Unused
	packet_16(array($Byte[9]));
	//BYTE 10  	   Packet ID: 17	Infrared Character Omni
	packet_17(array($Byte[10]));
	//BYTE 11      Packet ID: 18	Buttons
	packet_18(array($Byte[11]));
	//BYTE 12-13	Packet ID: 19	Distanz
	packet_19(array($Byte[12],$Byte[13]));
	//BYTE 14-15	Packet ID: 20	Winkel
	packet_20(array($Byte[14],$Byte[15]));
	//BYTE 16    	Packet ID: 21	Ladestatus
	packet_21(array($Byte[16]));
	//BYTE 17-18	Packet ID: 22	Batteriespannung
	packet_22(array($Byte[17],$Byte[18]));
	//BYTE 19-20	Packet ID: 23	Batteriestrom
	packet_23(array($Byte[19],$Byte[20]));
	//BYTE 21		Packet ID: 24	Batterietemperatur
	packet_24(array($Byte[21]));
	//BYTE 22-23	Packet ID: 25	Batterieaufladung
	packet_25(array($Byte[22],$Byte[23]));
	//BYTE 24-25	Packet ID: 26	Batteriekapazitaet
	packet_26(array($Byte[24],$Byte[25]));
	}

//******************************************************************************
// Packet Group 1
//******************************************************************************
function packet_group_1($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:1";
	//BYTE 0		   Packet ID: 7	Bumps and Wheel Drops
	packet_7 (array($Byte[0]));
	//BYTE 1       Packet ID: 8	Wall
	packet_8 (array($Byte[1]));
	//BYTE 2       Packet ID: 9	Cliff Left
	packet_9 (array($Byte[2]));
	//BYTE 3       Packet ID: 10	Cliff Front Left
	packet_10(array($Byte[3]));
	//BYTE 4       Packet ID: 11	Cliff Front Right
	packet_11(array($Byte[4]));
	//BYTE 5       Packet ID: 12	Cliff Right
	packet_12(array($Byte[5]));
	//BYTE 6       Packet ID: 13	Virtual Wall
	packet_13(array($Byte[6]));
	//BYTE 7       Packet ID: 14	Wheel Overcurrents
	packet_14(array($Byte[7]));
	//BYTE 8       Packet ID: 15	Schmutzsensor
	packet_15(array($Byte[8]));
	//BYTE 9       Packet ID: 16	Unused
	packet_16(array($Byte[9]));
	}
//******************************************************************************
// Packet Group 2
//******************************************************************************
function packet_group_2($Byte)
	{
	$debug = false;

	if ($debug) echo "\nPacketgruppe:2";

	//BYTE 0  	   Packet ID: 17	Infrared Character Omni
	packet_17(array($Byte[0]));
	//BYTE 1      	Packet ID: 18	Buttons
	packet_18(array($Byte[1]));
	//BYTE 2-3		Packet ID: 19	Distanz
	packet_19(array($Byte[2],$Byte[3]));
	//BYTE 4-5		Packet ID: 20	Winkel
	packet_20(array($Byte[4],$Byte[5]));
	}

//******************************************************************************
// Packet Group 3
//******************************************************************************
function packet_group_3($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:3";
	//BYTE 0    	Packet ID: 21	Ladestatus
	packet_21(array($Byte[0]));
	//BYTE 1-2		Packet ID: 22	Batteriespannung
	packet_22(array($Byte[1],$Byte[2]));
	//BYTE 3-4		Packet ID: 23	Batteriestrom
	packet_23(array($Byte[3],$Byte[4]));
	//BYTE 5			Packet ID: 24	Batterietemperatur
	packet_24(array($Byte[5]));
	//BYTE 6-7		Packet ID: 25	Batterieaufladung
	packet_25(array($Byte[6],$Byte[7]));
	//BYTE 8-9 		Packet ID: 26	Batteriekapazitaet
	packet_26(array($Byte[8],$Byte[9]));
	}

//******************************************************************************
// Packet Group 4
//******************************************************************************
function packet_group_4($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:4";
	//BYTE 0-1		Packet ID: 27	Wall Signal
	packet_27(array($Byte[0],$Byte[1]));
	//BYTE 2-3   	Packet ID: 28	Cliff Left Signal
	packet_28(array($Byte[2],$Byte[3]));
	//BYTE 4-5   	Packet ID: 29	Cliff Front Left Signal
	packet_29(array($Byte[4],$Byte[5]));
	//BYTE 6-7   	Packet ID: 30	Cliff Front Right Signal
	packet_30(array($Byte[6],$Byte[7]));
	//BYTE 8-9   	Packet ID: 31	Cliff Right Signal
	packet_31(array($Byte[8],$Byte[9]));
	//BYTE 10-12	Packet ID: 32-33	Unused
	//BYTE 13      Packet ID: 34	Ladequelle
	packet_34(array($Byte[13]));
	}

//******************************************************************************
// Packet Group 5
//******************************************************************************
function packet_group_5($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:5";
	//BYTE 0      	Packet ID: 35	OI Mode
	packet_35(array($Byte[0]));
	//BYTE 1      	Packet ID: 36	Lied Nummer
	packet_36(array($Byte[1]));
	//BYTE 2      	Packet ID: 37	Lied
	packet_37(array($Byte[2]));
	//BYTE 3      	Packet ID: 38	Anzahl Stream Pakete
	packet_38(array($Byte[3]));
	//BYTE 4-5   	Packet ID: 39	Request Velocity
	packet_39(array($Byte[4],$Byte[5]));
	//BYTE 6-7   	Packet ID: 40	Request Radius
	packet_40(array($Byte[6],$Byte[7]));
	//BYTE 8-9   	Packet ID: 41	Request Right Velocity
	packet_41(array($Byte[8],$Byte[9]));
	//BYTE 10-11 	Packet ID: 42	Request Left Velocity
	packet_42(array($Byte[10],$Byte[11]));
	}


//******************************************************************************
// Packet Group 6
//******************************************************************************
function packet_group_6($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:6";
	//BYTE 0		   Packet ID: 7	Bumps and Wheel Drops
	packet_7 (array($Byte[0]));
	//BYTE 1       Packet ID: 8	Wall
	packet_8 (array($Byte[1]));
	//BYTE 2       Packet ID: 9	Cliff Left
	packet_9 (array($Byte[2]));
	//BYTE 3       Packet ID: 10	Cliff Front Left
	packet_10(array($Byte[3]));
	//BYTE 4       Packet ID: 11	Cliff Front Right
	packet_11(array($Byte[4]));
	//BYTE 5       Packet ID: 12	Cliff Right
	packet_12(array($Byte[5]));
	//BYTE 6       Packet ID: 13	Virtual Wall
	packet_13(array($Byte[6]));
	//BYTE 7       Packet ID: 14	Wheel Overcurrents
	packet_14(array($Byte[7]));
	//BYTE 8       Packet ID: 15	Schmutzsensor
	packet_15(array($Byte[8]));
	//BYTE 9       Packet ID: 16	Unused
	packet_16(array($Byte[9]));
	//BYTE 10  	   Packet ID: 17	Infrared Character Omni
	packet_17(array($Byte[10]));
	//BYTE 11      Packet ID: 18	Buttons
	packet_18(array($Byte[11]));
	//BYTE 12-13	Packet ID: 19	Distanz
	packet_19(array($Byte[12],$Byte[13]));
	//BYTE 14-15	Packet ID: 20	Winkel
	packet_20(array($Byte[14],$Byte[15]));
	//BYTE 16    	Packet ID: 21	Ladestatus
	packet_21(array($Byte[16]));
	//BYTE 17-18	Packet ID: 22	Batteriespannung
	packet_22(array($Byte[17],$Byte[18]));
	//BYTE 19-20	Packet ID: 23	Batteriestrom
	packet_23(array($Byte[19],$Byte[20]));
	//BYTE 21		Packet ID: 24	Batterietemperatur
	packet_24(array($Byte[21]));
	//BYTE 22-23	Packet ID: 25	Batterieaufladung
	packet_25(array($Byte[22],$Byte[23]));
	//BYTE 24-25	Packet ID: 26	Batteriekapazitaet
	packet_26(array($Byte[24],$Byte[25]));
	//BYTE 26-27	Packet ID: 27	Wall Signal
	packet_27(array($Byte[26],$Byte[27]));
	//BYTE 28-29   Packet ID: 28	Cliff Left Signal
	packet_28(array($Byte[28],$Byte[29]));
	//BYTE 30-31   Packet ID: 29	Cliff Front Left Signal
	packet_29(array($Byte[30],$Byte[31]));
	//BYTE 32-33   Packet ID: 30	Cliff Front Right Signal
	packet_30(array($Byte[32],$Byte[33]));
	//BYTE 34-35   Packet ID: 31	Cliff Right Signal
	packet_31(array($Byte[34],$Byte[35]));
	//BYTE 36-38	Packet ID: 32-33	Unused
	//BYTE 39      Packet ID: 34	Ladequelle
	packet_34(array($Byte[39]));
	//BYTE 40      Packet ID: 35	OI Mode
	packet_35(array($Byte[40]));
	//BYTE 41      Packet ID: 36	Lied Nummer
	packet_36(array($Byte[41]));
	//BYTE 42      Packet ID: 37	Lied
	packet_37(array($Byte[42]));
	//BYTE 43      Packet ID: 38	Anzahl Stream Pakete
	packet_38(array($Byte[43]));
	//BYTE 44-45   Packet ID: 39	Request Velocity
	packet_39(array($Byte[44],$Byte[45]));
	//BYTE 46-47   Packet ID: 40	Request Radius
	packet_40(array($Byte[46],$Byte[47]));
	//BYTE 48-49   Packet ID: 41	Request Right Velocity
	packet_41(array($Byte[48],$Byte[49]));
	//BYTE 50-51   Packet ID: 42	Request Left Velocity
	packet_42(array($Byte[50],$Byte[51]));
	}
//******************************************************************************
// Packet Group 100	Achtung! Kein Stream! da zu lang bei 19200 Baud
//******************************************************************************
function packet_group_100($Byte)
	{
	$debug = true;

	if ($debug) echo "\nPacketgruppe:100";
	//BYTE 0		   Packet ID: 7	Bumps and Wheel Drops
	packet_7 (array($Byte[0]));
	//BYTE 1       Packet ID: 8	Wall
	packet_8 (array($Byte[1]));
	//BYTE 2       Packet ID: 9	Cliff Left
	packet_9 (array($Byte[2]));
	//BYTE 3       Packet ID: 10	Cliff Front Left
	packet_10(array($Byte[3]));
	//BYTE 4       Packet ID: 11	Cliff Front Right
	packet_11(array($Byte[4]));
	//BYTE 5       Packet ID: 12	Cliff Right
	packet_12(array($Byte[5]));
	//BYTE 6       Packet ID: 13	Virtual Wall
	packet_13(array($Byte[6]));
	//BYTE 7       Packet ID: 14	Wheel Overcurrents
	packet_14(array($Byte[7]));
	//BYTE 8       Packet ID: 15	Schmutzsensor
	packet_15(array($Byte[8]));
	//BYTE 9       Packet ID: 16	Unused
	packet_16(array($Byte[9]));
	//BYTE 10  	   Packet ID: 17	Infrared Character Omni
	packet_17(array($Byte[10]));
	//BYTE 11      Packet ID: 18	Buttons
	packet_18(array($Byte[11]));
	//BYTE 12-13	Packet ID: 19	Distanz
	packet_19(array($Byte[12],$Byte[13]));
	//BYTE 14-15	Packet ID: 20	Winkel
	packet_20(array($Byte[14],$Byte[15]));
	//BYTE 16    	Packet ID: 21	Ladestatus
	packet_21(array($Byte[16]));
	//BYTE 17-18	Packet ID: 22	Batteriespannung
	packet_22(array($Byte[17],$Byte[18]));
	//BYTE 19-20	Packet ID: 23	Batteriestrom
	packet_23(array($Byte[19],$Byte[20]));
	//BYTE 21		Packet ID: 24	Batterietemperatur
	packet_24(array($Byte[21]));
	//BYTE 22-23	Packet ID: 25	Batterieaufladung
	packet_25(array($Byte[22],$Byte[23]));
	//BYTE 24-25	Packet ID: 26	Batteriekapazitaet
	packet_26(array($Byte[24],$Byte[25]));
	//BYTE 26-27	Packet ID: 27	Wall Signal
	packet_27(array($Byte[26],$Byte[27]));
	//BYTE 28-29   Packet ID: 28	Cliff Left Signal
	packet_28(array($Byte[28],$Byte[29]));
	//BYTE 30-31   Packet ID: 29	Cliff Front Left Signal
	packet_29(array($Byte[30],$Byte[31]));
	//BYTE 32-33   Packet ID: 30	Cliff Front Right Signal
	packet_30(array($Byte[32],$Byte[33]));
	//BYTE 34-35   Packet ID: 31	Cliff Right Signal
	packet_31(array($Byte[34],$Byte[35]));
	//BYTE 36-38	Packet ID: 32-33	Unused
	//BYTE 39      Packet ID: 34	Ladequelle
	packet_34(array($Byte[39]));
	//BYTE 40      Packet ID: 35	OI Mode
	packet_35(array($Byte[40]));
	//BYTE 41      Packet ID: 36	Lied Nummer
	packet_36(array($Byte[41]));
	//BYTE 42      Packet ID: 37	Lied
	packet_37(array($Byte[42]));
	//BYTE 43      Packet ID: 38	Anzahl Stream Pakete
	packet_38(array($Byte[43]));
	//BYTE 44-45   Packet ID: 39	Request Velocity
	packet_39(array($Byte[44],$Byte[45]));
	//BYTE 46-47   Packet ID: 40	Request Radius
	packet_40(array($Byte[46],$Byte[47]));
	//BYTE 48-49   Packet ID: 41	Request Right Velocity
	packet_41(array($Byte[48],$Byte[49]));
	//BYTE 50-51   Packet ID: 42	Request Left Velocity
	packet_42(array($Byte[50],$Byte[51]));
   //BYTE 52-53   Packet ID: 43	Right Encoder Counts
	packet_43(array($Byte[52],$Byte[53]));
   //BYTE 54-55   Packet ID: 44	Left Encoder Counts
	packet_44(array($Byte[54],$Byte[55]));
	//BYTE 56      Packet ID: 45	Light Bumper
	packet_45(array($Byte[56]));
	//BYTE 57-58   Packet ID: 46	Light Bump Left Signal
	packet_46(array($Byte[57],$Byte[58]));
	//BYTE 59-60   Packet ID: 47	Light Bump Front Left Signal
	packet_47(array($Byte[59],$Byte[60]));
	//BYTE 61-62   Packet ID: 48	Light Bump Center Left Signal
	packet_48(array($Byte[61],$Byte[62]));
	//BYTE 63-64   Packet ID: 49	Light Bump Center Right Signal
	packet_49(array($Byte[63],$Byte[64]));
	//BYTE 65-66   Packet ID: 50	Light Bump Front Right Signal
	packet_50(array($Byte[65],$Byte[66]));
	//BYTE 67-68   Packet ID: 51	Light Bump Right Signal
	packet_51(array($Byte[67],$Byte[68]));
	//BYTE 69  	   Packet ID: 52	Infrared Character Left
	packet_52(array($Byte[69]));
	//BYTE 70      Packet ID: 53	Infrared Character Right
	packet_53(array($Byte[70]));
	//BYTE 71-72  	Packet ID: 54	Left Motor Current
	packet_54(array($Byte[71],$Byte[72]));
	//BYTE 73-74  	Packet ID: 55	Right Motor Current
	packet_55(array($Byte[73],$Byte[74]));
	//BYTE 75-76  	Packet ID: 56	Main Brush Motor Current
	packet_56(array($Byte[75],$Byte[76]));
	//BYTE 77-78  	Packet ID: 57	Side Brush Motor Current
	packet_57(array($Byte[77],$Byte[78]));
	//BYTE 79      Packet ID: 58	Stasis
	packet_58(array($Byte[79]));
	}

//******************************************************************************
// Packet Group 101
//******************************************************************************
function packet_group_101($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:101";

   //BYTE 0-1   	Packet ID: 43	Right Encoder Counts
	packet_43(array($Byte[0],$Byte[1]));
   //BYTE 2-3   	Packet ID: 44	Left Encoder Counts
	packet_44(array($Byte[2],$Byte[3]));
	//BYTE 4      	Packet ID: 45	Light Bumper
	packet_45(array($Byte[4]));
	//BYTE 5-6   	Packet ID: 46	Light Bump Left Signal
	packet_46(array($Byte[5],$Byte[6]));
	//BYTE 7-8   	Packet ID: 47	Light Bump Front Left Signal
	packet_47(array($Byte[7],$Byte[8]));
	//BYTE 9-10   	Packet ID: 48	Light Bump Center Left Signal
	packet_48(array($Byte[9],$Byte[10]));
	//BYTE 11-12   Packet ID: 49	Light Bump Center Right Signal
	packet_49(array($Byte[11],$Byte[12]));
	//BYTE 13-14   Packet ID: 50	Light Bump Front Right Signal
	packet_50(array($Byte[13],$Byte[14]));
	//BYTE 15-16   Packet ID: 51	Light Bump Right Signal
	packet_51(array($Byte[15],$Byte[16]));
	//BYTE 17  	   Packet ID: 52	Infrared Character Left
	packet_52(array($Byte[17]));
	//BYTE 18      Packet ID: 53	Infrared Character Right
	packet_53(array($Byte[18]));
	//BYTE 19-20  	Packet ID: 54	Left Motor Current
	packet_54(array($Byte[19],$Byte[20]));
	//BYTE 21-22  	Packet ID: 55	Right Motor Current
	packet_55(array($Byte[21],$Byte[22]));
	//BYTE 23-24  	Packet ID: 56	Main Brush Motor Current
	packet_56(array($Byte[23],$Byte[24]));
	//BYTE 25-26  	Packet ID: 57	Side Brush Motor Current
	packet_57(array($Byte[25],$Byte[26]));
	//BYTE 27      Packet ID: 58	Stasis
	packet_58(array($Byte[27]));
	}

//******************************************************************************
// Packet Group 106
//******************************************************************************
function packet_group_106($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:106";

	//BYTE 0-1   	Packet ID: 46	Light Bump Left Signal
	packet_46(array($Byte[0],$Byte[1]));
	//BYTE 2-3   	Packet ID: 47	Light Bump Front Left Signal
	packet_47(array($Byte[2],$Byte[3]));
	//BYTE 4-5   	Packet ID: 48	Light Bump Center Left Signal
	packet_48(array($Byte[4],$Byte[5]));
	//BYTE 6-7   	Packet ID: 49	Light Bump Center Right Signal
	packet_49(array($Byte[6],$Byte[7]));
	//BYTE 8-9   	Packet ID: 50	Light Bump Front Right Signal
	packet_50(array($Byte[8],$Byte[9]));
	//BYTE 10-11   Packet ID: 51	Light Bump Right Signal
	packet_51(array($Byte[10],$Byte[11]));

	}

//******************************************************************************
// Packet Group 107
//******************************************************************************
function packet_group_107($Byte)
	{
	$debug = false;
	if ($debug) echo "\nPacketgruppe:107";

	//BYTE 0-1  	Packet ID: 54	Left Motor Current
	packet_54(array($Byte[0],$Byte[1]));
	//BYTE 2-3  	Packet ID: 55	Right Motor Current
	packet_55(array($Byte[2],$Byte[3]));
	//BYTE 4-5  	Packet ID: 56	Main Brush Motor Current
	packet_56(array($Byte[4],$Byte[5]));
	//BYTE 6-7  	Packet ID: 57	Side Brush Motor Current
	packet_57(array($Byte[6],$Byte[7]));
	//BYTE 8      	Packet ID: 58	Stasis
	packet_58(array($Byte[8]));

	}



//******************************************************************************
// Umrechnung von 2 Bytes in Werte von 0 bis 65535
//******************************************************************************
function u_0bis65535($id,$b1,$b2)
	{
	$b1 = ord($b1);$b2 = ord($b2);
	$b1 = $b2 + $b1*256;
	return $b1;
	}

//******************************************************************************
// Umrechnung von 2 Bytes in Werte von -32768 bis +32768
//******************************************************************************
function u_32768bis32767($id,$b1,$b2)
	{
	$b1 = ord($b1);$b2 = ord($b2);
	if ( $b1 & 128 )$b1 = ($b1 & 127)-128;
	$b1 = $b2 + $b1*256;
	return $b1;
	}

//******************************************************************************
// Umrechnung von 1 Byte in Werte von -128 bis +127
//******************************************************************************
function u_128bis127($id,$b1)
	{
	$b1 = ord($b1);
	if ( $b1 & 128 ){$b1 = ($b1 & 127)-128; }
	return $b1;
	}
//******************************************************************************
// Umrechnung von 1 Byte in Werte von 0 bis 255
//******************************************************************************
function u_0bis255($id,$b1)
	{
	$b1 = ord($b1);
	return $b1;
	}

//******************************************************************************
// Umrechnung von 1 Byte in Werte true oder false
//******************************************************************************
function u_0bis1($id,$b1)
	{
	$b1 = ord($b1);
	if ( $b1 == 1 )
	   $b1 = true;
	else
	   $b1 = false;
	return $b1;
	}

//******************************************************************************
// Telegramm ausgeben - DEBUG
//******************************************************************************
function debug_packet($instr)
	{
	$datenstr="";
	$laenge = strlen($instr);

	for($i=0; $i<strlen($instr); $i++)
		{
 		$datenstr.=strtoupper(ord($instr{$i}))." ";
    	}

	echo "\nLaenge:$laenge";echo "\n$datenstr";
	}

//******************************************************************************
// Lighthouse Verwaltung
//
// Reichweite :
//******************************************************************************
function lighthouse($i,$b1)
	{
	$debug = false;

	$i_fence 		= 33339;
	$i_force_field = 19709;
	$i_green_buoy 	= 11635;
	$i_red_buoy 	= 17528;

	$i_virtual_wall = LH_VIRTUAL_WALL_FENCE;


	if ( $i == INFRARED_CHARACTER_LEFT  ) $source = "LINKS";
	if ( $i == INFRARED_CHARACTER_RIGHT ) $source = "RECHTS";
	if ( $i == INFRARED_CHARACTER_OMNI  ) $source = "OMNI";

	//if($debug) echo "\nLighhouse - $source - $b1";

	if ( $b1 == 0 )
	   {
	   return;
	   }

	// Virtal Wall
	if ( $b1 == 162 )
	   {
	   if($debug) echo "\nVirtual Wall - $source - $b1";
		SetValueBoolean($i_virtual_wall,true);
	   return;
	   }

	// Charger
	if ( ($b1 & 128) == 128 )
	   {
	   if($debug) echo "\nCharger - $source - $b1";
		switch( $b1 )
		   {
		   case 161 :
		            SetValueBoolean(LH_CHARGER_FORCE_FIELD,true);
		            break;
		   case 164 :
		            SetValueBoolean(LH_CHARGER_GREEN_BUOY,true);
		            break;
		   case 165 :
		            SetValueBoolean(LH_CHARGER_GREEN_BUOY,true);
		            SetValueBoolean(LH_CHARGER_FORCE_FIELD,true);
		            break;
		   case 168 :
		            SetValueBoolean(LH_CHARGER_RED_BUOY,true);
		            break;
		   case 169 :
		            SetValueBoolean(LH_CHARGER_RED_BUOY,true);
		            SetValueBoolean(LH_CHARGER_FORCE_FIELD,true);
		            break;
		   case 172 :
		            SetValueBoolean(LH_CHARGER_GREEN_BUOY,true);
		            SetValueBoolean(LH_CHARGER_RED_BUOY,true);
		            break;
		   case 173 :
		            SetValueBoolean(LH_CHARGER_GREEN_BUOY,true);
		            SetValueBoolean(LH_CHARGER_RED_BUOY,true);
		            SetValueBoolean(LH_CHARGER_FORCE_FIELD,true);
		            break;

		   case 160 :
		            SetValueBoolean(LH_CHARGER_160,true);
		            break;


		   }
		return;
	   }


	// Lighthouse
	if ( ($b1 & 128) == 0 )
	   {
	   if($debug) echo "\nLighthouse - $source - $b1";

		$id = ($b1 & 120)/8;
		$bb = $b1 & 3;

	   switch($bb)
	      {
	      case 0 : $typ = "FENCE";
	               SetValueBoolean($i_fence,true);
	               break;
			case 1 : $typ = "FORCE FIELD";
						SetValueBoolean($i_force_field,true);
						break;
	      case 2 : $typ = "GREEN BUOY";
	               SetValueBoolean($i_green_buoy,true);
						break;
			case 3 : $typ = "RED BUOY";
			         SetValueBoolean($i_red_buoy,true);
						break;

	      }

		echo "\nID:$id-$bb-$typ";
		return;
	   }

	echo "\nLighthouse Data Invalid";


	}

//******************************************************************************
// Streamingfunktion - zur Zeit nicht verwenden !
function stream()
	{
//******************************************************************************
// Streaming ( im Moment noch nicht moeglich mit IPS
//******************************************************************************
	// Stream
	if (ord($instr[0]) == 19 )
	   {
	   //Checksumme testen
	   $checksumme = 0;
	   for($x=0;$x<$laenge;$x++)
	      {
	      $checksumme = $checksumme + ord($instr[$x]);
			}
		// Wenn Checksumme 256 dann OK
		// Fehler in OI Doku - 19 muss mitgezaehlt werden
		if ( $checksumme == 256 )
		   {
			$anzahl = ord($instr[1]);  // Anzahl der Bytes
		   $zeiger = 0;
		   if ($debug) echo "\nAnzahl:$anzahl";
			// Stream zerlegen
			while ( $zeiger < $anzahl )
				{
				$Byte = "";
				$ok = false;
  				$opcode =  ord($instr[$zeiger+2]);
  				switch($opcode)
  				   {
  				   case 52:    $Byte = array($instr[$zeiger+2+1]);
  				   				$zeiger = $zeiger + 2;
  				               $ok = true;
  				               packet_52($Byte);
					  				break;
  				   case 53:    $Byte = array($instr[$zeiger+2+1]);
  				   				$zeiger = $zeiger + 2;
  				               $ok = true;
  				               packet_53($Byte);
					  				break;
  				   default:    echo "\nUnknown Opcode[$opcode] raus";

  				               break;
  				   }
				if ( !$ok ) break;

				}
			// Stream zerlegen Ende
		   }
		else
			if ($debug) echo "\nChecksumme:$checksumme NOK";

		}

	}



function create_zufallspaket()
	{
	$s = "";
	
	for($x=0;$x<80;$x++)
		{
		$zufall = rand(0,255);
		if ( $x == 16 )
			$zufall = rand(1,5);

	   $s = $s . chr($zufall);
	   }
	
	return $s;
	
	}

?>