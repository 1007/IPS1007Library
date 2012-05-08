<?

	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");


	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

// Alle Circles durchlaufen und Zeit auslesen
foreach(IPS_GetChildrenIDs($idCatCircles) as $item){   // alle Unterobjekte durchlaufen
   $id_info = IPS_GetObject($item);

	// Kalibrierungsdaten vom Circle abrufen
	PW_SendCommand("0026".$id_info['ObjectIdent']);

}


?>