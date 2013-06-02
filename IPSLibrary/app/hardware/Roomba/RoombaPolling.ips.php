<?
	/*
	 * This file is part of the IPSLibrary.
	 *
	 * The IPSLibrary is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published
	 * by the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * The IPSLibrary is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with the IPSLibrary. If not, see http://www.gnu.org/licenses/gpl.txt.
	 */

	/**@ingroup roomba
	 * @{
	 *
	 * @file          RoombaPolling.ips.php
	 * @author        1007
	 * @version
	 *  Version 1.0.0, 27.03.2013<br/>
	 *
	 * Abrufen der Daten aller Roombas die aktiv sind ( Polling )
	 *
	 */

	IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("RoombaFuncpool.inc.php","IPSLibrary::app::hardware::Roomba");
	IPSUtils_Include ("Roomba_Configuration.inc.php","IPSLibrary::config::hardware::Roomba");
 	IPSUtils_Include ("IPSLogger.inc.php","IPSLibrary::app::core::IPSLogger");
//	IPSUtils_Include ("Funcpool.ips.php","");

	$debug = false;
	
	$HardwarePathId  = get_ObjectIDByPath("Hardware.Roomba");

	

	$one_moving   = false;
	$one_charging = false;
	$one_online   = false;
	
	$poll = POLLING_DEFAULT;

	//***************************************************************************
	// alle Roombas durchgegen
	//***************************************************************************
	foreach ( $roombas as $roomba )
	   {
		$name         = $roomba['Name'];
		$aktiv 	     = $roomba['Aktiv'];
		$xbeesplitter = $roomba['XBeeSplitter'];
		$xbeegateway  = $roomba['XBeeGateway'];

		$array = explode(":",IPS_GetConfiguration($xbeesplitter));
		$xbeeid = intval($array[1]);

		$DataPathId = get_ObjectIDByPath("Program.IPSLibrary.data.hardware.Roomba.$name.SystemData");

		if ( GetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_CHARGING',$DataPathId))  )
			$one_charging = true;
		if ( GetValueInteger(IPS_GetVariableIDByName('ROOMBA_DISTANCE',$DataPathId)) != 0 )
			$one_moving = true;

		if ( !GetValueBoolean(IPS_GetVariableIDByName('AKTIV',$DataPathId)))   // ist Roomba aktiviert ?
			{
			SetValueBoolean(IPS_GetVariableIDByName('POLLING_AKTIV',$DataPathId),false);
			SetValueBoolean(IPS_GetVariableIDByName('POLLING_STATUS',$DataPathId),false);
			continue;
			}
		if ($debug) IPSLogger_Dbg(__FILE__, 'Polling ' . $name  );

//		if ( GetValueBoolean(IPS_GetVariableIDByName('POLLING_STATUS',$DataPathId)))  // ist ein Polling noch aktiv ?

		if ( polling_timing($DataPathId,$name) );
			{
			command(QUERY_LIST,array(1,100),$xbeegateway,$xbeeid,$DataPathId);
			}
			   
		if ( !GetValueBoolean(IPS_GetVariableIDByName('POLLING_AKTIV',$DataPathId)))   // ist Polling aktiviert ?
   			SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_ONLINE'		,$DataPathId),false);


		}


	if ( $one_charging )
		$poll = POLLING_CHARGING;
	if ( $one_moving )
		$poll = POLLING_MOVING;
	if ( !$one_online )
		$poll = POLLING_DEFAULT;

	$timer = IPS_GetScriptTimer($_IPS['SELF']);
	if ( $poll != $timer )
	   {
	   if ($debug) IPSLogger_Dbg(__FILE__, 'Pollingzeit setzen auf ' . $poll  );
		IPS_SetScriptTimer($_IPS['SELF'],$poll);
		}

/***************************************************************************//**
* 	Checken welchen Status Roomba hat
*  ONLINE - CHARGING - MOVING
*******************************************************************************/
function polling_timing($DataPathId,$name)
	{
	GLOBAL $debug;
	
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
	   if ($debug) IPSLogger_Dbg(__FILE__, 'Online ' . $name   );
		}
	else
	   if ($debug) IPSLogger_Dbg(__FILE__, 'Offline ' . $name  );


   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_CHARGING'	,$DataPathId),$charging);
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_ONLINE'		,$DataPathId),$online);
   SetValueBoolean(IPS_GetVariableIDByName('ROOMBA_STATUS_MOVING'		,$DataPathId),$moving);


	$status = true;
	return $status;
	
	}



?>