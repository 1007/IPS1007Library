<?

	require_once("RoombaFuncpool.ips.php");
	require_once("Funcpool.ips.php");


//******************************************************************************
// Im Moment nur einzelne Gruppen !!
//******************************************************************************
	$debug = false;

  	$instr = $IPS_VALUE;

	$laenge = strlen($instr);

	$packet  = GetValueInteger(PACKET_REQUESTED);
	$counter = GetValueInteger(PACKET_COUNTER);
	$counter++;
	SetValueInteger(PACKET_COUNTER,$counter);

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


	// Batterie berechnen
	$x = GetValueInteger(BATTERY_CHARGE);
	$y = GetValueInteger(BATTERY_CAPACITY);
	$s = strval(($x /$y)*100);
	$bat = round($s);

	SetValueInteger(BATTERIE,$bat);

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
	//Packet ID: 7	Bumps and Wheel Drops
	$b1 = u_0bis255(7,$Byte[0]);
	$b2 = GetValueInteger(BUMP_AND_WHEEL_DROPS);
	if ( $b1 != $b2 )SetValueInteger(BUMP_AND_WHEEL_DROPS,$b1);else return;
	if ( ($b1 & 1) != ($b2 & 1))if ( ($b1 & 1) == 1 ) SetValueBoolean(BUMP_AND_WHEEL_DROPS_BUMP_RIGHT			,true); else SetValueBoolean(BUMP_AND_WHEEL_DROPS_BUMP_RIGHT			,false);
	if ( ($b1 & 2) != ($b2 & 2))if ( ($b1 & 2) == 2 ) SetValueBoolean(BUMP_AND_WHEEL_DROPS_BUMP_LEFT			,true); else SetValueBoolean(BUMP_AND_WHEEL_DROPS_BUMP_LEFT				,false);
	if ( ($b1 & 4) != ($b2 & 4))if ( ($b1 & 4) == 4 ) SetValueBoolean(BUMP_AND_WHEEL_DROPS_WHEEL_DROP_RIGHT	,true); else SetValueBoolean(BUMP_AND_WHEEL_DROPS_WHEEL_DROP_RIGHT	,false);
	if ( ($b1 & 8) != ($b2 & 8))if ( ($b1 & 8) == 8 ) SetValueBoolean(BUMP_AND_WHEEL_DROPS_WHEEL_DROP_LEFT	,true); else SetValueBoolean(BUMP_AND_WHEEL_DROPS_WHEEL_DROP_LEFT		,false);
	}
function packet_8($Byte)
	{
	//Packet ID: 8	Wall
	$b1 = u_0bis1(8,$Byte[0]);
	$b2 = GetValueBoolean(WALL);
	if ( $b1 != $b2 )SetValueBoolean(WALL,$b1);
	}
function packet_9($Byte)
	{
	//Packet ID: 9	Cliff Left
	$b1 = u_0bis1(9,$Byte[0]);
	$b2 = GetValueBoolean(CLIFF_LEFT);
	if ( $b1 != $b2 )SetValueBoolean(CLIFF_LEFT,$b1);
	}
function packet_10($Byte)
	{
	//Packet ID: 10	Cliff Front Left
	$b1 = u_0bis1(10,$Byte[0]);
	$b2 = GetValueBoolean(CLIFF_FRONT_LEFT);
	if ( $b1 != $b2 )SetValueBoolean(CLIFF_FRONT_LEFT,$b1);
	}
function packet_11($Byte)
	{
	//Packet ID: 11	Cliff Front Right
	$b1 = u_0bis1(11,$Byte[0]);
	$b2 = GetValueBoolean(CLIFF_FRONT_RIGHT);
	if ( $b1 != $b2 )SetValueBoolean(CLIFF_FRONT_RIGHT,$b1);
	}
function packet_12($Byte)
	{
	//Packet ID: 12	Cliff Right
	$b1 = u_0bis1(12,$Byte[0]);
	$b2 = GetValueBoolean(CLIFF_RIGHT);
	if ( $b1 != $b2 )SetValueBoolean(CLIFF_RIGHT,$b1);
	}
function packet_13($Byte)
	{
	//Packet ID: 13	Virtual Wall
	$b1 = u_0bis1(13,$Byte[0]);
	$b2 = GetValueBoolean(VIRTUAL_WALL);
	if ( $b1 != $b2 )SetValueBoolean(VIRTUAL_WALL,$b1);
	}
function packet_14($Byte)
	{
	//Packet ID: 14   Wheel Overcurrents
	$b1 = u_0bis255(14,$Byte[0]);
	$b2 = GetValueInteger(WHEEL_OVERCURRENTS);
	if ( $b1 != $b2 )SetValueInteger(WHEEL_OVERCURRENTS,$b1);else return;
	if ( ($b1 & 1) != ($b2 & 1))if ( ($b1 & 1) == 1 ) SetValueBoolean(WHEEL_OVERCURRENTS_SIDE_BRUSH		,true); else SetValueBoolean(WHEEL_OVERCURRENTS_SIDE_BRUSH	,false);
	if ( ($b1 & 4) != ($b2 & 4))if ( ($b1 & 4) == 4 ) SetValueBoolean(WHEEL_OVERCURRENTS_MAIN_BRUSH		,true); else SetValueBoolean(WHEEL_OVERCURRENTS_MAIN_BRUSH	,false);
	if ( ($b1 & 8) != ($b2 & 8))if ( ($b1 & 8) == 8 ) SetValueBoolean(WHEEL_OVERCURRENTS_RIGHT_WHEEL	,true); else SetValueBoolean(WHEEL_OVERCURRENTS_RIGHT_WHEEL	,false);
	if ( ($b1 &16) != ($b2 &16))if ( ($b1 &16) ==16 ) SetValueBoolean(WHEEL_OVERCURRENTS_LEFT_WHEEL		,true); else SetValueBoolean(WHEEL_OVERCURRENTS_LEFT_WHEEL	,false);
	}
function packet_15($Byte)
	{
	//Packet ID: 15   Dirt Detect
	$b1 = u_0bis255(15,$Byte[0]);
	$b2 = GetValueInteger(DIRT_DETECT);
	if ( $b1 != $b2 )SetValueInteger(DIRT_DETECT,$b1);
	}
function packet_16($Byte)
	{
	//Packet ID: 16   Unused
	}
function packet_17($Byte)
	{
	//Packet ID: 17	Infrared Character Omni
	$b1 = u_0bis255(17,$Byte[0]);
	$b2 = GetValueInteger(INFRARED_CHARACTER_OMNI);
	if ( $b1 == $b2 ) return;
	SetValueInteger(INFRARED_CHARACTER_OMNI,$b1);
	lighthouse(INFRARED_CHARACTER_OMNI,$b1);
	}
function packet_18($Byte)
	{
	//Packet ID: 18   Buttons
	$b1 = u_0bis255(18,$Byte[0]);
	$b2 = GetValueInteger(BUTTONS);
	if ( $b1 != $b2 )SetValueInteger(BUTTONS,$b1);else return;
	if ( ($b1 & 1) != ($b2 & 1))if ( ($b1 & 1) == 1 ) SetValueBoolean(BUTTONS_CLEAN		,true); else SetValueBoolean(BUTTONS_CLEAN		,false);
	if ( ($b1 & 2) != ($b2 & 2))if ( ($b1 & 2) == 2 ) SetValueBoolean(BUTTONS_SPOT		,true); else SetValueBoolean(BUTTONS_SPOT			,false);
	if ( ($b1 & 4) != ($b2 & 4))if ( ($b1 & 4) == 4 ) SetValueBoolean(BUTTONS_DOCK		,true); else SetValueBoolean(BUTTONS_DOCK			,false);
	if ( ($b1 & 8) != ($b2 & 8))if ( ($b1 & 8) == 8 ) SetValueBoolean(BUTTONS_MINUTE		,true); else SetValueBoolean(BUTTONS_MINUTE		,false);
	if ( ($b1 &16) != ($b2 &16))if ( ($b1 &16) ==16 ) SetValueBoolean(BUTTONS_HOUR		,true); else SetValueBoolean(BUTTONS_HOUR			,false);
	if ( ($b1 &32) != ($b2 &32))if ( ($b1 &32) ==32 ) SetValueBoolean(BUTTONS_DAY			,true); else SetValueBoolean(BUTTONS_DAY			,false);
	if ( ($b1 &64) != ($b2 &64))if ( ($b1 &64) ==64 ) SetValueBoolean(BUTTONS_SCHEDULE	,true); else SetValueBoolean(BUTTONS_SCHEDULE	,false);
	if ( ($b1 &128)!= ($b2&128))if ( ($b1&128) ==128) SetValueBoolean(BUTTONS_CLOCK		,true); else SetValueBoolean(BUTTONS_CLOCK		,false);
	}
function packet_19($Byte)
	{
	//Packet ID: 19   DISTANCE
	$b1 = u_32768bis32767(19,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(DISTANCE);
	if ( $b1 != $b2 ) SetValueInteger(DISTANCE,$b1);

	//echo "\nerg:" . $b1 . " byte0:". ord($Byte[0]) . "byte1:" .ord($Byte[1]);

	$km = GetValueInteger(KILOMETERZAEHLER);
	$km = $km + abs($b1);

	SetValueInteger(KILOMETERZAEHLER,$km);

	}
function packet_20($Byte)
	{
	//Packet ID: 20   ANGLE
	$b1 = u_32768bis32767(20,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(ANGLE);
	if ( $b1 != $b2 )SetValueInteger(ANGLE,$b1);
	}
function packet_21($Byte)
	{
	//Packet ID: 21	Ladestatus
	$b1 = u_0bis255(21,$Byte[0]);
	$b2 = GetValueInteger(CHARGING_STATE);
	if ( $b1 != $b2 )SetValueInteger(CHARGING_STATE,$b1);else return;
	if ( $b1	== 0 ) SetValueBoolean(CHARGING_STATE_NOT_CHARGING					,true); else SetValueBoolean(CHARGING_STATE_NOT_CHARGING					,false);
	if ( $b1 == 1 ) SetValueBoolean(CHARGING_STATE_RECONDITIONING_CHARGING	,true); else SetValueBoolean(CHARGING_STATE_RECONDITIONING_CHARGING	,false);
	if ( $b1 == 2 ) SetValueBoolean(CHARGING_STATE_FULL_CHARGING				,true); else SetValueBoolean(CHARGING_STATE_FULL_CHARGING				,false);
	if ( $b1 == 3 ) SetValueBoolean(CHARGING_STATE_TRICKLE_CHARGING			,true); else SetValueBoolean(CHARGING_STATE_TRICKLE_CHARGING			,false);
	if ( $b1 == 4 ) SetValueBoolean(CHARGING_STATE_WAITING						,true); else SetValueBoolean(CHARGING_STATE_WAITING						,false);
	if ( $b1 == 5 ) SetValueBoolean(CHARGING_STATE_CHARGING_FAULT_CONDITION	,true); else SetValueBoolean(CHARGING_STATE_CHARGING_FAULT_CONDITION	,false);
	}
function packet_22($Byte)
	{
	//Packet ID: 22	Batteriespannung
	$b1 = u_0bis65535(23,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(VOLTAGE);
	if ( $b1 != $b2 )SetValueInteger(VOLTAGE,$b1);
	}
function packet_23($Byte)
	{
	//Packet ID: 23	Batteriestrom
	$b1 = u_32768bis32767(23,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(CURRENT);
	if ( $b1 != $b2 )SetValueInteger(CURRENT,$b1);
	}
function packet_24($Byte)
	{
	//Packet ID: 24	Batterietemperatur
	$b1 = u_128bis127(24,$Byte[0]);
	$b2 = GetValueInteger(TEMPERATURE);
	if ( $b1 != $b2 )SetValueInteger(TEMPERATURE,$b1);
	}
function packet_25($Byte)
	{
	//Packet ID: 25	Batterieaufladung
	$b1 = u_0bis65535(25,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(BATTERY_CHARGE);
	if ( $b1 != $b2 )SetValueInteger(BATTERY_CHARGE,$b1);
	}
function packet_26($Byte)
	{
	//Packet ID: 26	Batteriekapazitaet
	$b1 = u_0bis65535(26,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(BATTERY_CAPACITY);
	if ( $b1 != $b2 )SetValueInteger(BATTERY_CAPACITY,$b1);
	}
function packet_27($Byte)
	{
	//Packet ID: 27	Wall Signal
	$b1 = u_0bis65535(27,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(WALL_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(WALL_SIGNAL,$b1);
	}
function packet_28($Byte)
	{
	//Packet ID: 28	Cliff Left Signal
	$b1 = u_0bis65535(28,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(CLIFF_LEFT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(CLIFF_LEFT_SIGNAL,$b1);
	}
function packet_29($Byte)
	{
	//Packet ID: 29	Cliff Front Left Signal
	$b1 = u_0bis65535(29,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(CLIFF_FRONT_LEFT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(CLIFF_FRONT_LEFT_SIGNAL,$b1);
	}
function packet_30($Byte)
	{
	//Packet ID: 30	Cliff Front Right Signal
	$b1 = u_0bis65535(30,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(CLIFF_FRONT_RIGHT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(CLIFF_FRONT_RIGHT_SIGNAL,$b1);
	}
function packet_31($Byte)
	{
	//Packet ID: 31	Cliff Right Signal
	$b1 = u_0bis65535(31,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(CLIFF_RIGHT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(CLIFF_RIGHT_SIGNAL,$b1);
	}
function packet_32($Byte)
	{
	//Packet ID: 32	Unused

	}
function packet_33($Byte)
	{
	//Packet ID: 33	Unused

	}
function packet_34($Byte)
	{
	//Packet ID: 34	Ladequelle
	$b1 = u_0bis255(34,$Byte[0]);
	$b2 = GetValueInteger(CHARGING_SOURCES_AVAILABLE);
	if ( $b1 != $b2 )SetValueInteger(CHARGING_SOURCES_AVAILABLE,$b1); else return;
	if ( ($b1 & 1) == 1 ) SetValueBoolean(CHARGING_SOURCES_AVAILABLE_INTERNAL_CHARGER,true); else SetValueBoolean(CHARGING_SOURCES_AVAILABLE_INTERNAL_CHARGER,false);
	if ( ($b1 & 2) == 2 ) SetValueBoolean(CHARGING_SOURCES_AVAILABLE_HOME_BASE			,true); else SetValueBoolean(CHARGING_SOURCES_AVAILABLE_HOME_BASE			,false);
	}
function packet_35($Byte)
	{
	//Packet ID: 35	OI Mode

	$b1 = u_0bis255(35,$Byte[0]);
	$b2 = GetValueInteger(OI_MODE);
	if ( $b1 != $b2 )SetValueInteger(OI_MODE,$b1); else return;
	if ( $b1 == 0 ) SetValueBoolean(OI_MODE_OFF		,true); else SetValueBoolean(OI_MODE_OFF		,false);
	if ( $b1 == 1 ) SetValueBoolean(OI_MODE_PASSIVE	,true); else SetValueBoolean(OI_MODE_PASSIVE	,false);
	if ( $b1 == 2 ) SetValueBoolean(OI_MODE_SAFE		,true); else SetValueBoolean(OI_MODE_SAFE		,false);
	if ( $b1 == 3 ) SetValueBoolean(OI_MODE_FULL		,true); else SetValueBoolean(OI_MODE_FULL		,false);
	}
function packet_36($Byte)
	{
	//Packet ID: 36	Lied Nummer
	$b1 = u_0bis255(36,$Byte[0]);
	$b2 = GetValueInteger(SONG_NUMBER);
	if ( $b1 != $b2 )SetValueInteger(SONG_NUMBER,$b1);
	}
function packet_37($Byte)
	{
	//Packet ID: 37	Lied
	$b1 = u_0bis255(37,$Byte[0]);
	$b2 = GetValueInteger(SONG_PLAYING);
	if ( $b1 != $b2 )SetValueInteger(SONG_PLAYING,$b1);
	}
function packet_38($Byte)
	{
	//Packet ID: 38	Anzahl Stream Pakete
	$b1 = u_0bis255(38,$Byte[0]);
	$b2 = GetValueInteger(NUMBER_OF_STREAM_PACKETS);
	if ( $b1 != $b2 )SetValueInteger(NUMBER_OF_STREAM_PACKETS,$b1);
	}
function packet_39($Byte)
	{
	//Packet ID: 39	Request Velocity
	$b1 = u_32768bis32767(39,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(REQUESTED_VELOCITY);
	if ( $b1 != $b2 )SetValueInteger(REQUESTED_VELOCITY,$b1);
	}
function packet_40($Byte)
	{
	//Packet ID: 40	Request Radius
	$b1 = u_32768bis32767(40,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(REQUESTED_RADIUS);
	if ( $b1 != $b2 )SetValueInteger(REQUESTED_RADIUS,$b1);
	}
function packet_41($Byte)
	{
	//Packet ID: 41	Request Right Velocity
	$b1 = u_32768bis32767(41,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(REQUESTED_RIGHT_VELOCITY);
	if ( $b1 != $b2 )SetValueInteger(REQUESTED_RIGHT_VELOCITY,$b1);
	}
function packet_42($Byte)
	{
	//Packet ID: 42	Request Left Velocity
	$b1 = u_32768bis32767(42,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(REQUESTED_LEFT_VELOCITY);
	if ( $b1 != $b2 )SetValueInteger(REQUESTED_LEFT_VELOCITY,$b1);
	}
function packet_43($Byte)
	{
   //Packet ID: 43	Right Encoder Counts
	$b1 = u_0bis65535(43,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(RIGHT_ENCODER_COUNTS);
	if ( $b1 != $b2 )SetValueInteger(RIGHT_ENCODER_COUNTS,$b1);
	//echo "rechts counter = $b1";

	}
function packet_44($Byte)
	{
   //Packet ID: 44	Left Encoder Counts
	$b1 = u_0bis65535(44,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LEFT_ENCODER_COUNTS);
	if ( $b1 != $b2 )SetValueInteger(LEFT_ENCODER_COUNTS,$b1);

	//echo "links counter = $b1";

	}
function packet_45($Byte)
	{
	//Packet ID: 45	Light Bumper
	$b1 = u_0bis255(45,$Byte[0]);
	$b2 = GetValueInteger(LIGHT_BUMPER);
	if ( $b1 != $b2 )SetValueInteger(LIGHT_BUMPER,$b1);else return;
	if ( ($b1 & 1) != ($b2 & 1))if ( ($b1 & 1) == 1 ) SetValueBoolean(LIGHT_BUMPER_LEFT				,true); else SetValueBoolean(LIGHT_BUMPER_LEFT			,false);
	if ( ($b1 & 2) != ($b2 & 2))if ( ($b1 & 2) == 2 ) SetValueBoolean(LIGHT_BUMPER_FRONT_LEFT		,true); else SetValueBoolean(LIGHT_BUMPER_FRONT_LEFT	,false);
	if ( ($b1 & 4) != ($b2 & 4))if ( ($b1 & 4) == 4 ) SetValueBoolean(LIGHT_BUMPER_CENTER_LEFT	,true); else SetValueBoolean(LIGHT_BUMPER_CENTER_LEFT	,false);
	if ( ($b1 & 8) != ($b2 & 8))if ( ($b1 & 8) == 8 ) SetValueBoolean(LIGHT_BUMPER_CENTER_RIGHT	,true); else SetValueBoolean(LIGHT_BUMPER_CENTER_RIGHT,false);
	if ( ($b1 &16) != ($b2 &16))if ( ($b1 &16) ==16 ) SetValueBoolean(LIGHT_BUMPER_FRONT_RIGHT	,true); else SetValueBoolean(LIGHT_BUMPER_FRONT_RIGHT	,false);
	if ( ($b1 &32) != ($b2 &32))if ( ($b1 &32) ==32 ) SetValueBoolean(LIGHT_BUMPER_RIGHT			,true); else SetValueBoolean(LIGHT_BUMPER_RIGHT			,false);
	}
function packet_46($Byte)
	{
	//Packet ID: 46	Light Bump Left Signal
	$b1 = u_0bis65535(46,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LIGHT_BUMP_LEFT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(LIGHT_BUMP_LEFT_SIGNAL,$b1);
	}
function packet_47($Byte)
	{
	//Packet ID: 47	Light Bump Front Left Signal
	$b1 = u_0bis65535(47,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LIGHT_BUMP_FRONT_LEFT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(LIGHT_BUMP_FRONT_LEFT_SIGNAL,$b1);
	}
function packet_48($Byte)
	{
	//Packet ID: 48	Light Bump Center Left Signal
	$b1 = u_0bis65535(48,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LIGHT_BUMP_CENTER_LEFT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(LIGHT_BUMP_CENTER_LEFT_SIGNAL,$b1);
	}
function packet_49($Byte)
	{
	//Packet ID: 49	Light Bump Center Right Signal
	$b1 = u_0bis65535(49,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LIGHT_BUMP_CENTER_RIGHT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(LIGHT_BUMP_CENTER_RIGHT_SIGNAL,$b1);
	}
function packet_50($Byte)
	{
	//Packet ID: 50	Light Bump Front Right Signal
	$b1 = u_0bis65535(50,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LIGHT_BUMP_FRONT_RIGHT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(LIGHT_BUMP_FRONT_RIGHT_SIGNAL,$b1);
	}
function packet_51($Byte)
	{
	//Packet ID: 51	Light Bump Right Signal
	$b1 = u_0bis65535(51,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LIGHT_BUMP_RIGHT_SIGNAL);
	if ( $b1 != $b2 )SetValueInteger(LIGHT_BUMP_RIGHT_SIGNAL,$b1);
	}
function packet_52($Byte)
	{
	//Packet ID: 52	Infrared Character Left"
	$b1 = u_0bis255(52,$Byte[0]);
	$b2 = GetValueInteger(INFRARED_CHARACTER_LEFT);
	if ( $b1 == $b2 ) return;
	SetValueInteger(INFRARED_CHARACTER_LEFT,$b1);
	lighthouse(INFRARED_CHARACTER_LEFT,$b1);
	}
function packet_53($Byte)
	{
	//Packet ID: 53	Infrared Character Right
	$b1 = u_0bis255(53,$Byte[0]);
	$b2 = GetValueInteger(INFRARED_CHARACTER_RIGHT);
	if ( $b1 == $b2 ) return;
	SetValueInteger(INFRARED_CHARACTER_RIGHT,$b1);
	lighthouse(INFRARED_CHARACTER_RIGHT,$b1);
	}
function packet_54($Byte)
	{
	//Packet ID: 54	Left Motor Current
	$b1 = u_32768bis32767(54,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(LEFT_MOTOR_CURRENT);
	if ( $b1 != $b2 )SetValueInteger(LEFT_MOTOR_CURRENT,$b1);

	}
function packet_55($Byte)
	{
	//Packet ID: 55	Right Motor Current
	$b1 = u_32768bis32767(55,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(RIGHT_MOTOR_CURRENT);
	if ( $b1 != $b2 )SetValueInteger(RIGHT_MOTOR_CURRENT,$b1);
	}
function packet_56($Byte)
	{
	//Packet ID: 56	Main Brush Motor Current
	$b1 = u_32768bis32767(56,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(MAIN_BRUSH_MOTOR_CURRENT);
	if ( $b1 != $b2 )SetValueInteger(MAIN_BRUSH_MOTOR_CURRENT,$b1);
	}
function packet_57($Byte)
	{
	//Packet ID: 57	Side Brush Motor Current
	$b1 = u_32768bis32767(57,$Byte[0],$Byte[1]);
	$b2 = GetValueInteger(SIDE_BRUSH_MOTOR_CURRENT);
	if ( $b1 != $b2 )SetValueInteger(SIDE_BRUSH_MOTOR_CURRENT,$b1);
	}
function packet_58($Byte)
	{
	//Packet ID: 58	Stasis
	$b1 = u_0bis1(58,$Byte[0]);
	$b2 = GetValueBoolean(STASIS);
	if ( $b1 != $b2 )SetValueBoolean(STASIS,$b1);
	}


//******************************************************************************
// Packet Group 0
//******************************************************************************
function packet_group_0($Byte)
	{
	$debug = true;
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
	$debug = true;
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
	$debug = true;
	/*
	if ($debug) echo "\nPacketgruppe:2";
	echo "\n";
	echo ord($Byte[0]) ."-";
	echo ord($Byte[1]) ."-";
	echo ord($Byte[2]) ."-";
	echo ord($Byte[3]) ."-";
	echo ord($Byte[4]) ."-";
	echo ord($Byte[5]) ."-";
	*/

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
	$debug = true;
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
	$debug = true;
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
	$debug = true;
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
	$debug = false;

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
	$debug = true;
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
	$debug = true;
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
	$debug = true;
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
	if ( $b1 & 128 ){$b1 = ($b1 & 127)-128; echo $b1;}
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

function change_state($instance,$state)
	{
	$b1 = GetValueBoolean($instance);
	if ( $b1 != $state ) SetValueBoolean($instance,$state);
	}



?>