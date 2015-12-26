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
// date_default_timezone_set('UTC');

	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");

	//$idCatCircles = CreateCategory("Circles",IPS_GetParent($_IPS['SELF']),0);
	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

	// Alle Circles durchlaufen und Zeit stellen
	foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
		{	// alle Unterobjekte durchlaufen
   	$id_info = IPS_GetObject($item);
 		PW_SendCommand("0016".$id_info['ObjectIdent'].unixtime2pwtime(),$id_info['ObjectIdent']);
		}


?>