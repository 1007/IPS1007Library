<?
/**
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


	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");


	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

	// Alle Circles durchlaufen und Zeit auslesen
	foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
		{   // alle Unterobjekte durchlaufen
   	$id_info = IPS_GetObject($item);

		// Kalibrierungsdaten vom Circle abrufen
		PW_SendCommand("0026".$id_info['ObjectIdent'],$id_info['ObjectIdent']);
		// Zeit lesen. Durch Antwort wird Zeit gestellt
      PW_SendCommand("003E".$id_info['ObjectIdent'],$id_info['ObjectIdent']);

		}


?>