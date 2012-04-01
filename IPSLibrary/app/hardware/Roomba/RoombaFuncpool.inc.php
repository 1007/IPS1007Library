<?
//******************************************************************************
// Roomba
//******************************************************************************



	define ('START'					,128);
	define ('BAUD'						,129);
	define ('SAFE'						,131);
	define ('FULL'						,132);
	define ('POWER'					,133);
	define ('SPOT'						,134);
	define ('CLEAN'					,135);
	define ('MAX'						,136);
	define ('DRIVE'  					,137);
	define ('MOTORS' 					,138);
	define ('LEDS'   					,139);
	define ('SONG'   					,140);
	define ('PLAY'   					,141);
	define ('SENSORS'					,142);
	define ('SEEK_DOCK'				,143);
	define ('PWM_MOTORS'				,144);
	define ('DRIVE_DIRECT' 			,145);
	define ('DRIVE_PWM'				,146);
	define ('STREAM' 					,148);
	define ('QUERY_LIST'				,149);
	define ('PAUSE_RESUME_STREAM'	,150);
	define ('SCHEDULING_LEDS'		,162);
	define ('DIGIT_LEDS_RAW'		,163);
	define ('DIGIT_LEDS_ASCII'		,164);
	define ('CMD_BUTTONS'			,165);
	define ('SCHEDULE'  				,167);
	define ('SET_DAY_TIME' 			,168);


	
//******************************************************************************
// Daten senden
//******************************************************************************
function xbee_send($gateway_id,$xbee_id,$command)
	{
	$debug = false;
	
	if ($debug) print_r($command);


	$string = "";
	for ( $x = 0 ; $x< sizeof($command) ; $x++)
	   {
	   $c = chr($command[$x]);
	   $string =  $string . $c   ;
	   }

	$len = strlen($string);
	//if ($debug) echo "\nGateway':".$gateway_id;
	//if ($debug) echo "\nXBee':".$xbee_id;

	$ok = XBee_SendBuffer($gateway_id,$xbee_id,$string);
	//if ($debug) echo "\nOK=".$ok;
	}

//******************************************************************************
// Kommando zusammensetzen
//******************************************************************************
function command($opcode,$databytes,$xbee_id,$DataPathId)
	{
	
	$aktiv_id = IPS_GetVariableIDByName('AKTIV',$DataPathId);
	if ( GetValueBoolean($aktiv_id)){ echo "\nScript ist gesperrt! Opcode:[$opcode]"; return false; }
	SetValueBoolean($aktiv_id,true);

	$gateway_id = GetValueInteger(IPS_GetVariableIDByName('XBEE_GATEWAY',$DataPathId));

	$sendbuffer = array();


	$debug = false;
	if ($debug) echo "\nOpcode:$opcode";

	switch($opcode)
	   {
		// Getting Started Commands
			// Start
	      case 128 :
							$sendbuffer[0] = 128;
							xbee_send($gateway_id,$xbee_id,$sendbuffer);
							break;
			// Baud
	      case 129 :
	      				$sendbuffer[0] = 129;
							xbee_send($sendbuffer);
							break;
		// Mode Commands
			// Safe
	      case 131 :
							$sendbuffer[0] = 131;
							xbee_send($gateway_id,$xbee_id,$sendbuffer);
							break;
			// Full
	      case 132 :
	      				$sendbuffer[0] = 132;
							xbee_send($gateway_id,$xbee_id,$sendbuffer);
							break;

		// Cleaning Commands
			// Clean
	   	case 135 :
							$sendbuffer[0] = 135;
							xbee_send($gateway_id,$xbee_id,$sendbuffer);
							break;
			// Max
	   	case 136 :  xbee_send(136); break;
			// Spot
		   case 134 :
							$sendbuffer[0] = 134;
							xbee_send($sendbuffer);
							break;
			// Seek Dock
	      case 143 :
							$sendbuffer[0] = 143;
							xbee_send($gateway_id,$xbee_id,$sendbuffer);
							break;
			// Schedule
			case 167 :	xbee_send(167);
							xbee_send($databytes[0]);
							xbee_send($databytes[1]);
							xbee_send($databytes[2]);
							xbee_send($databytes[3]);
							xbee_send($databytes[4]);
							xbee_send($databytes[5]);
							xbee_send($databytes[6]);
							xbee_send($databytes[7]);
							xbee_send($databytes[8]);
							xbee_send($databytes[9]);
							xbee_send($databytes[10]);
							xbee_send($databytes[11]);
							xbee_send($databytes[12]);
							xbee_send($databytes[13]);
							xbee_send($databytes[14]);
							break;
			// SetDay/Time
			case 168 :	xbee_send(168);
							xbee_send($databytes[0]);
							xbee_send($databytes[1]);
							xbee_send($databytes[2]);
							break;
			// Power
			case 133 :	xbee_send(133); break;
		// Actuator Commands
			// Drive
			case 137 :  xbee_send(137);
							xbee_send($databytes[0]);
							xbee_send($databytes[1]);
							xbee_send($databytes[2]);
							xbee_send($databytes[3]);
							break;
			// Drive Direct
			case 145 :

							$sendbuffer[0] = 145;
							$sendbuffer[1] = $databytes[0];
							$sendbuffer[2] = $databytes[1];
							$sendbuffer[3] = $databytes[2];
							$sendbuffer[4] = $databytes[3];

							xbee_send($sendbuffer);

							break;
			// Drive PWM
			case 146 :  xbee_send(146);
							xbee_send($databytes[0]);
							xbee_send($databytes[1]);
							xbee_send($databytes[2]);
							xbee_send($databytes[3]);
							break;
			// Motors
			case 138 :  xbee_send(138);
							xbee_send($databytes[0]);
							break;
			// PWM Motors
			case 144 :  xbee_send(144);
							xbee_send($databytes[0]);
							xbee_send($databytes[1]);
							xbee_send($databytes[2]);
							break;
			// LEDs
			case 139 :  xbee_send(139);
							xbee_send($databytes[0]);
							xbee_send($databytes[1]);
							xbee_send($databytes[2]);
							break;
			// Scheduling LEDs
			case 162 :  xbee_send(162);
							xbee_send($databytes[0]);
							xbee_send($databytes[1]);
							break;
			// Digit LED Raw
			case 163 :
							$sendbuffer[0] = 163;
							$sendbuffer[1] = $databytes[0];
							$sendbuffer[2] = $databytes[1];
							$sendbuffer[3] = $databytes[2];
							$sendbuffer[4] = $databytes[3];

							xbee_send($sendbuffer);

							break;
			// Digit LEDs ASCII
			case 164 :
							$sendbuffer[0] = 164;
							$sendbuffer[1] = $databytes[0];
							$sendbuffer[2] = $databytes[1];
							$sendbuffer[3] = $databytes[2];
							$sendbuffer[4] = $databytes[3];

							xbee_send($gateway_id,$xbee_id,$sendbuffer);


							break;
			// Buttons
			case 165 :  xbee_send(165);
							xbee_send($databytes[0]);
							break;
			// Song
			case 140 :	$sendbuffer[0] = 140;
							xbee_send($gateway_id,$xbee_id,$sendbuffer);
							//xbee_send(140);
							//$sendbuffer[0] = 140;
							$sendbuffer = array_merge($sendbuffer,$databytes);
			            $song   = $databytes[0];
			            $laenge = $databytes[1];
			            $laenge = ($laenge*2)+2;
			            echo "\nSong:$song";
							echo "\nLaenge:".$laenge;
			            for ($x=0;$x<$laenge;$x++)
			               	{//echo "\n".$databytes[$x];
			            		xbee_send($gateway_id,$xbee_id,array($databytes[$x]));
									}
									
                     //xbee_send($gateway_id,$xbee_id,$sendbuffer);
							//print_r($databytes);
							break;

			// Play
			case 141 :  $sendbuffer[0] = 141;
							$sendbuffer[1] = $databytes[0];
							xbee_send($gateway_id,$xbee_id,$sendbuffer);

							break;
		// Input Commands
			// Sensors
			case 142 :  xbee_send(142);
							xbee_send($databytes[0]);
							break;
			// Query List
			case 149 :
							$anzahl = $databytes[0];
			            if ( $anzahl > 1 ) break; // Im Moment nur eine Gruppe
			            SetValueInteger(IPS_GetVariableIDByName('PACKET_REQUESTED',$DataPathId),$databytes[1]);

							$sendbuffer[0] = 149;
							for ($x=0;$x<=$anzahl;$x++)
			               $sendbuffer[$x+1] = $databytes[$x];

							xbee_send($gateway_id,$xbee_id,$sendbuffer);

							break;
			// Stream
			case 148 :  xbee_send(148);
			            $anzahl = $databytes[0];
			            for ($x=0;$x<=$anzahl;$x++)
			               xbee_send($databytes[$x]);
							break;
			// Pause/Resume Stream
			case 150 :  xbee_send(150);
							xbee_send($databytes[0]);
							break;
		// default
			default:    echo "\nCommand:$opcode unbekannt";break;

		}

   SetValueBoolean($aktiv_id,false);

   return true;

	}



//******************************************************************************
// Ausgabe von Text auf dem LCD
//******************************************************************************
function show_lcd_text($roomba_id,$DataPathId,$string)
	{
	$xbee_id = XBee_GetDeviceID($roomba_id);
	
	$byte1 = ord($string[0]);
	$byte2 = ord($string[1]);
	$byte3 = ord($string[2]);
	$byte4 = ord($string[3]);
	command(DIGIT_LEDS_ASCII,array($byte1,$byte2,$byte3,$byte4),$xbee_id,$DataPathId);
	}


function cmd_init($roomba_id,$DataPathId)
	{
	
	$xbee_id = XBee_GetDeviceID($roomba_id);
 	command(START,0,$xbee_id,$DataPathId);
 
	}
	
function cmd_max($roomba_id)
	{
	
	
	}
	
function cmd_spot($roomba_id,$DataPathId,$string)
	{
	$xbee_id = XBee_GetDeviceID($roomba_id);

 	command(SAFE,0,$xbee_id,$DataPathId);

	show_lcd_text($roomba_id,$DataPathId,$string);
	sleep(2);
 	command(START,0,$xbee_id,$DataPathId);

	}
	
	
function cmd_power($roomba_id,$DataPathId)
	{
	$xbee_id = XBee_GetDeviceID($roomba_id);


	}

function cmd_song($roomba_id,$DataPathId)
	{
	$xbee_id = XBee_GetDeviceID($roomba_id);

	$song = decode(0,SONG3);

 	command(SAFE,0,$xbee_id,$DataPathId);
	command(SONG,$song,$xbee_id,$DataPathId);       // Lied ueberspielen
	command(PLAY,array(0),$xbee_id,$DataPathId);   	// Melodie ausgeben


	}

function cmd_clean($roomba_id,$DataPathId)
	{
	$xbee_id = XBee_GetDeviceID($roomba_id);

 	command(CLEAN,0,$xbee_id,$DataPathId);

	return;
	go_wartung();

	reset_data();
	startzeit();

	SetValueBoolean(POLLING_STATUS,false);

   command(START,0);
   command(SAFE,0);
   sleep(2);
   command(CLEAN,0);
	sleep(5);

   SetValueBoolean(POLLING_STATUS,true);




	}


function cmd_wartung($roomba_id)
	{

	return;
	reset_data();
	startzeit();

   //SetValueBoolean(POLLING_STATUS,false);

   command(START,0);
   command(SAFE,0);
   sleep(2);
   //command(CLEAN,0);
	sleep(5);
   command(FULL,0);
   sleep(1);
   command(SAFE,0);
	sleep(10);
	//return;
	//command(SONG,array(0,7,60,16,67,32,60,16,60,40,60,32,48,32,67,32));
	//command(PLAY,array(0));                         // Melodie ausgeben
	command(DRIVE_DIRECT,speed_to_byte(-50,-50));  	// 50 mm/s
	sleep(10);                               // x * 50mm
	//command(DRIVE_DIRECT,speed_to_byte(-50,50));  	// 50 mm/s
	//sleep(13);
	command(DRIVE_DIRECT,speed_to_byte(0,0));   		// Stop
	show_lcd_text(get_batterie());                  // Batterieladung auf LCD
	//command(SONG,array(0,7,60,16,67,32,60,16,60,40,60,32,48,32,67,32));
	//command(PLAY,array(0));                         // Melodie ausgeben
	//sleep(5);                                // warte bis Melodie fertig
	command(START,0);                               // Passive Mode

   //SetValueBoolean(POLLING_STATUS,true);

	}

function cmd_home($roomba_id,$DataPathId)
	{
	$xbee_id = XBee_GetDeviceID($roomba_id);
 	command(SEEK_DOCK,0,$xbee_id,$DataPathId);

	return;
	
   SetValueBoolean(POLLING_STATUS,false);

	command(SAFE,0);	sleep(2);
	command(START,0); 	sleep(2);
	command(SEEK_DOCK,0);	sleep(2);

   SetValueBoolean(POLLING_STATUS,true);

	}


function speed_to_byte($speed_rechts,$speed_links)
	{
   $a = array(0,0,0,0);

	$speed_rechts = intval($speed_rechts);
	$speed_links  = intval($speed_links);

	if ( $speed_rechts >  500 or $speed_links >  500 ) 	return $a;
	if ( $speed_rechts < -500 or $speed_links < -500 ) 	return $a;

	$hbr = ($speed_rechts & bindec('1111111100000000'))/256;
	$lbr = ($speed_rechts & bindec('0000000011111111'));
	$hbl = ($speed_links  & bindec('1111111100000000'))/256;
	$lbl = ($speed_links  & bindec('0000000011111111'));

	$a = array($hbr,$lbr,$hbl,$lbl);

	return $a;

	}

function reset_data()
	{

	SetValueString(STARTZEIT,"");
	SetValueString(ENDZEIT,"");
	SetValueString(LAUFZEIT,"");
	SetValueInteger(KILOMETERZAEHLER,0);


	SetValueBoolean(LH_VIRTUAL_WALL_FENCE,false);
	SetValueBoolean(LH_CHARGER_160,false);
	SetValueBoolean(LH_CHARGER_FORCE_FIELD,false);
	SetValueBoolean(LH_CHARGER_GREEN_BUOY,false);
	SetValueBoolean(LH_CHARGER_RED_BUOY,false);

	SetValueInteger(LH_01_ID,0);
	SetValueBoolean(LH_01_FENCE,false);
	SetValueBoolean(LH_01_FORCE_FIELD,false);
	SetValueBoolean(LH_01_GREEN_BUOY,false);
	SetValueBoolean(LH_01_RED_BUOY,false);

	SetValueInteger(LH_02_ID,0);
	SetValueBoolean(LH_02_FENCE,false);
	SetValueBoolean(LH_02_FORCE_FIELD,false);
	SetValueBoolean(LH_02_GREEN_BUOY,false);
	SetValueBoolean(LH_02_RED_BUOY,false);

	}

function startzeit()
	{

	$timestamp = time();

	$s = strftime("%H:%M",$timestamp);

	SetValueInteger(STARTTIMESTAMP,$timestamp);
	SetValueString(STARTZEIT,$s);

	}

function endzeit()
	{

	//echo "\nEndzeit";

	$timestamp = time();

	$s = strftime("%H:%M",$timestamp);

	SetValueString(ENDZEIT,$s);

	}

function laufzeit()
	{

	//echo "\nLaufzeit";

	$timestamp1 = time();
	$timestamp2 = GetValueInteger(STARTTIMESTAMP);

	$sec = $timestamp1 - $timestamp2;

	$stunden = intval($sec/3600);
	$minuten = intval(($sec - ($stunden*3600))/60);
	//echo "\nse:$sec - $stunden - $minuten";
	$s = sprintf('%02d:%02d', $stunden, $minuten);

	SetValueString(LAUFZEIT,$s);

	}

function decode($nr,$s)
	{

	$song 	= array($nr,16,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	$s 		= str_replace(':',',',$s);
	$array 	= explode(',',$s);

	$titel 	= $array[0];
	$duration= intval(strrev($array[1]));
	$scale 	= intval(strrev($array[2]));
	$beats 	= $array[3];

	$beats = preg_replace('![^0-9]!','',$beats);
	$beats   = $beats/60;
	$counter = 2;

	for ( $x=4;$x<20;$x++ )
	   {
		$d = $duration;
		$s = $scale;
		$n = $array[$x];
		$b1 = intval($n);
		$s1 = intval(strrev($n));
		if ( $b1 > 0 ) $d = $b1;
		if ( $s1 > 0 ) $s = intval($s1);
   	$n1 = preg_replace('/[^A-Za-z#]*/','',strtoupper($n));
		$b1 = note($s,$n1);
		$song[$counter] = $b1;
		$counter++;
		$song[$counter] = intval($d*$beats);
		$counter++;
	   }

	return $song;
	}

function note($scale,$note)
	{

	$b = 0;

	if ( $note == "C" ) $b = 0;
	if ( $note == "C#" )$b = 1;
	if ( $note == "D" ) $b = 2;
	if ( $note == "D#" )$b = 3;
	if ( $note == "E" ) $b = 4;
	if ( $note == "F" ) $b = 5;
	if ( $note == "F#" )$b = 6;
	if ( $note == "G" ) $b = 7;
	if ( $note == "G#" )$b = 8;
	if ( $note == "A" ) $b = 9;
	if ( $note == "A#" )$b = 10;
	if ( $note == "B" ) $b = 11;

	$b = ($scale*12) + $b;

	if ( $note == "P" ) $b = 0;

	$b = intval($b);

	return $b;
	}



?>