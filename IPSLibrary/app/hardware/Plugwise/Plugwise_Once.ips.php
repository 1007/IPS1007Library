<?

	/** Anlegen einer IO Instanze mit seriellem Port
	 *
	 * Die Funktion legt eine Serielle IO Instanze an. Wenn unter der ParentId bereits eine Instanze mit selbem
	 * Namen existiert, werden alle Parameter auf den aktuellen Wert gesetzt.
	 *
	 * @param string $Name Name der IO Instanze
	 * @param string $ComPort Name des Com Ports
	 * @param integer $Baud Baud Rate des seriellen Ports
	 * @param integer $StopBits Einstellung Stop Bits des seriellen Ports
	 * @param integer $DataBits Parity Data Bits des seriellen Ports
	 * @param string $Parity Parity Einstellung des seriellen Ports
	 * @param integer $Position Positions Wert des Objekts
	 * @param boolean $IgnoreError Ignoriren von Fehlern bei der Generierung der Instanz, andernfalls wird das Script abgebrochen
	 * @return integer ID der seriellen Port Instanze
	 *
	 */
	function CreateSerialPortNew($Name, $ComPort, $Baud=9600, $StopBits=1, $DataBits=8, $Parity='None', $Position=0, $IgnoreError=true)
		{

		$InstanceID = CreateInstance($Name, 0, "{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}",$Position);
		IPS_SetProperty($InstanceID, 'BaudRate', $Baud);
		IPS_SetProperty($InstanceID, 'StopBits', $StopBits);
		IPS_SetProperty($InstanceID, 'DataBits', $DataBits);
		IPS_SetProperty($InstanceID, 'Parity', $Parity);
		IPS_SetProperty($InstanceID, 'Port', $ComPort);
		IPS_SetProperty($InstanceID, 'Open', true);

		if (!@IPS_ApplyChanges($InstanceID) and !$IgnoreError) {
			Error ("Error applying Changes to ComPort Instance --> Abort Script");
		};
		return $InstanceID;
	}


if (!function_exists('Cutter_SetParseType'))
{	
	function Cutter_SetParseType($InstanceID, $_Type)
 	{
		IPS_SetProperty($InstanceID, 'ParseType', $_Type);
	}
}

if (!function_exists('Cutter_SetLeftCutChar'))
{
	function Cutter_SetLeftCutChar($InstanceID, $Value)
 	{
		IPS_SetProperty($InstanceID, 'LeftCutChar', $Value);
	}
}

if (!function_exists('Cutter_SetRightCutChar'))
{
	function Cutter_SetRightCutChar($InstanceID, $Value)
 	{
		IPS_SetProperty($InstanceID, 'RightCutChar', $Value);
	}
}
	
if (!function_exists('Cutter_SetTimeout'))
{
	function Cutter_SetTimeout($InstanceID, $Milliseconds)
 	{
		IPS_SetProperty($InstanceID, 'Timeout', $Milliseconds);
	}
}


if (!function_exists('IPS_SetEventCyclicTimeBounds')) {
	
	function IPS_SetEventCyclicTimeBounds($EventID, $FromTime, $ToTime)
	{
		$ret = true;
		if($FromTime == 0) {
			$ret = $ret & IPS_SetEventCyclicTimeFrom($EventID, 0, 0, 0);
		} else {
			$ret = $ret & IPS_SetEventCyclicTimeFrom($EventID, (int)date("H", $FromTime), (int)date("i", $FromTime), (int)date("s", $FromTime));
		}
		if($ToTime == 0) {
			$ret = $ret & IPS_SetEventCyclicTimeTo($EventID, 0, 0, 0);
		} else {
			$ret = $ret & IPS_SetEventCyclicTimeTo($EventID, (int)date("H", $ToTime), (int)date("i", $ToTime), (int)date("s", $ToTime));
		}
		return $ret;
	}
}

  
if (!function_exists('RegVar_SetRXObjectID'))
{  
	function RegVar_SetRXObjectID($InstanceID, $ObjectID)
 	{
		IPS_SetProperty($InstanceID, 'RXObjectID', $ObjectID);
	}
}


?>