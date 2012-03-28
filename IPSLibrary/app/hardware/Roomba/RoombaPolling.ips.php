<?

	require_once("RoombaFuncpool.ips.php");



	$b1 = GetValueBoolean(POLLING_STATUS);
	if ( $b1 == false ) return;

	// Muss beim Neustart des Roomba einmal ausgefuehrt werden
 	//command(START,0);
	//command(QUERY_LIST,array(1,100));
	command(QUERY_LIST,array(1,100));
	timing();

	return;

function timing()
	{

	$debug = false;

	$poll 		= 0;
	$online 		= false;
	$charging 	= false;
	$moving 		= false;
	$unknown    = false;

   $t1 = time() ;

	$array = IPS_GetVariable(PACKET_COUNTER);

   $t2 = $array["VariableUpdated"];
	$diff = $t1 - $t2;

	//echo "\ntiming:$diff";


	if ( $diff < 15 )
	   $online = true;

	if ( GetValueInteger(ROOMBA_CHARGING) != 0 and $online == true )
		$charging = true;

	if ( GetValueInteger(ROOMBA_DISTANCE) != 0 and $online == true )
	   $moving = true;

	if ( $online == true and $charging == false and $moving == false )
	   $unknown = true;

	change_state(ROOMBA_STATUS_ONLINE	,$online);
	change_state(ROOMBA_STATUS_CHARGING	,$charging);
	change_state(ROOMBA_STATUS_MOVING	,$moving);
	change_state(ROOMBA_STATUS_UNKNOWN	,$unknown);


	//echo "\nonline:$online - charging:$charging - moving:$moving";


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
	   $poll = POLLING_DEFAULT;

		}

	//echo "\nPoll:$poll";
	//$poll = 2;
	if ( $poll > 0 ) polling($poll);



	}

function change_state($instance,$state)
	{

	$b1 = GetValueBoolean($instance);

	if ( $instance == ROOMBA_STATUS_MOVING )
		{
	   if ( $b1 == true )
			laufzeit();

		if ( $b1 != $state and $state == false )
			   endzeit();
		}

	if ( $b1 != $state ) SetValueBoolean($instance,$state);
	}

//******************************************************************************
// Pollingzeit aendern
//******************************************************************************
function polling($p)
	{
	$p = intval($p);
	if ( $p == 0 )return;
	$t = IPS_GetScriptTimer(TIMER);
	if ( $p == $t ) return;

	IPS_SetScriptTimer(TIMER,$p);
	//echo "\nPolling: $t > $p";

	}


?>