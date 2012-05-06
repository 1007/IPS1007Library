<?

	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");

	//$idCatCircles = CreateCategory("Circles",IPS_GetParent($IPS_SELF),0);
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

foreach(IPS_GetChildrenIDs($idCatCircles) as $item){   // alle Unterobjekte durchlaufen
	$id_info = IPS_GetObject($item);
	$LogAddress = GetValue(IPS_GetVariableIDByName ("LogAddress", $item));
	$LogAddress = 278528 + (32 * ($LogAddress));
	$LogAddress = str_pad(strtoupper(dechex($LogAddress)), 8 ,'0', STR_PAD_LEFT);
	PW_SendCommand("0048".$id_info['ObjectIdent'].$LogAddress);
}

?>