<?
	IPSUtils_Include ("Roomba_Configuration.inc.php", 	"IPSLibrary::config::hardware::Roomba");
	IPSUtils_Include ("RoombaFuncpool.inc.php",    		"IPSLibrary::app::hardware::Roomba");
  	IPSUtils_Include ("IPSInstaller.inc.php",          "IPSLibrary::install::IPSInstaller");


	If ( $_IPS['SENDER'] != "WebFront" ) return;

	SetValueInteger($_IPS['VARIABLE'],1);

	$object = IPS_GetObject($_IPS['VARIABLE']);
	$name   = $object['ObjectName'];
	$parent = $object['ParentID'];
	//print_r($object);

	$parent = IPS_GetParent($parent);
	$systemdataid = IPS_GetObjectIDByName("SystemData",$parent);


	$splitter_id = GetValueInteger(IPS_GetVariableIDByName('XBEE_SPLITTER',	$systemdataid));
	
	//echo "-------------".$systemdataid;
   //echo "-------------".$splitter_id;

	switch ( $name )
	   {
	   case "CMD_INIT"   		: 	cmd_init($splitter_id	,$systemdataid);	break ;
	   case "CMD_POWER"   		: 	cmd_power($splitter_id	,$systemdataid);	break ;
	   case "CMD_CLEAN"   		: 	cmd_clean($splitter_id	,$systemdataid);	break ;
	   case "CMD_SPOT"   		: 	cmd_spot($splitter_id	,$systemdataid,"4848");		break ;
	   case "CMD_MAX"   			: 	cmd_max($splitter_id		,$systemdataid);  break ;
	   case "CMD_HOME"   		: 	cmd_home($splitter_id	,$systemdataid); 	break ;
	   case "CMD_WARTUNG"   	:	cmd_wartung($splitter_id,$systemdataid); 	break;
	   case "CMD_SONG1"   		:	cmd_song($splitter_id,$systemdataid,1); 	break;
	   case "CMD_SONG2"   		:	cmd_song($splitter_id,$systemdataid,2); 	break;
	   case "CMD_SONG3"   		:	cmd_song($splitter_id,$systemdataid,3); 	break;
	   case "CMD_SONG4"   		:	cmd_song($splitter_id,$systemdataid,4); 	break;
	   case "CMD_SONG5"   		:	cmd_song($splitter_id,$systemdataid,5); 	break;
	   case "CMD_SONG6"   		:	cmd_song($splitter_id,$systemdataid,6); 	break;
	   case "CMD_SONG7"   		:	cmd_song($splitter_id,$systemdataid,7); 	break;
	   case "CMD_SONG8"   		:	cmd_song($splitter_id,$systemdataid,8); 	break;

		default 					:   									break;
	   }
	   
   SetValueInteger($_IPS['VARIABLE'],0);

?>