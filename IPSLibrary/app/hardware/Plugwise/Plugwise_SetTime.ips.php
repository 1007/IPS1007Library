<?

// date_default_timezone_set('UTC');

	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");


	//$idCatCircles = CreateCategory("Circles",IPS_GetParent($IPS_SELF),0);
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

// Alle Circles durchlaufen und Zeit stellen
foreach(IPS_GetChildrenIDs($idCatCircles) as $item){   // alle Unterobjekte durchlaufen
   $id_info = IPS_GetObject($item);
 	PW_SendCommand("0016".$id_info['ObjectIdent'].unixtime2pwtime());
}


?>