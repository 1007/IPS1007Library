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

/***************************************************************************//**
* @ingroup plugwise
* @{
* @defgroup Plugwise_Readbuffer Plugwise Readbuffer
* @{
*
* @file       Plugwise_Readbuffer.ips.php
* @author     Axel Philippson (axelp) , Juergen Gerharz (1007)
* @version    Version 1.0.0
* @date       18.05.2012
*
*
*  @brief   Plugwise Readbuffer - sendet Anfrage fuer Buffer
*
* @todo
*
*******************************************************************************/

	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");

	$CircleDataPath = "Program.IPSLibrary.data.hardware.Plugwise.Circles";
   $idCatCircles = get_ObjectIDByPath($CircleDataPath);

	foreach(IPS_GetChildrenIDs($idCatCircles) as $item)
		{	// alle Unterobjekte durchlaufen
		$id_info = IPS_GetObject($item);
		$LogAddress = GetValue(IPS_GetVariableIDByName ("LogAddress", $item));
		$LogAddress = $LogAddress - 24;
		//$LogAddress = 278528 + (32 * ($LogAddress));
		$LogAddress = str_pad(strtoupper(dechex($LogAddress)), 8 ,'0', STR_PAD_LEFT);
      
		echo "\n" . $LogAddress ;
		
		PW_SendCommand("0048".$id_info['ObjectIdent'].$LogAddress);
		}


	dummy();

/***************************************************************************//**
* Dummy Routine gegen DoxygenBug
* wenn foreach() als letzter Befehl im Kopf dann wird foreach
* als Routine/Variable dokumentiert
*******************************************************************************/
function dummy()
  {
  }

/***************************************************************************//**
* @}
* @}
*******************************************************************************/

?>