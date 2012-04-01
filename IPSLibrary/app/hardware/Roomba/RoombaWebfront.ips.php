<?
	IPSUtils_Include ("Roomba_Configuration.inc.php", 	"IPSLibrary::config::hardware::Roomba");
	IPSUtils_Include ("RoombaFuncpool.inc.php",    		"IPSLibrary::app::hardware::Roomba");

	

	IPS_LogMessage("zzz",$_IPS['SENDER']);

	If ( $_IPS['SENDER'] != "WebFront" ) return;

	SetValueInteger($_IPS['VARIABLE'],1);

	$object = IPS_GetObject($_IPS['VARIABLE']);
	$name   = $object['ObjectName'];
	$parent = $object['ParentID'];

	$splitter_id = GetValueInteger(IPS_GetVariableIDByName('XBEE_SPLITTER',$parent));


	switch ( $name )
	   {
	   case "CMD_INIT"   	: 	cmd_init($splitter_id,$parent);		break ;
	   case "CMD_POWER"   	: 	cmd_power($splitter_id,$parent);	break ;
	   case "CMD_CLEAN"   	: 	cmd_clean($splitter_id,$parent);	break ;
	   case "CMD_SPOT"   	: 	cmd_spot($splitter_id,$parent,"4848");		break ;
	   case "CMD_MAX"   		: 	cmd_max($splitter_id,$parent);  	break ;
	   case "CMD_HOME"   	: 	cmd_home($splitter_id,$parent); 	break ;
	   case "CMD_WARTUNG"   :	cmd_wartung($splitter_id,$parent); break;
		default 					:   									break;
	   }
	   
   SetValueInteger($_IPS['VARIABLE'],0);

?>