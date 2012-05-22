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
	         Aufbau des Buffers :
						278528			0044000 - 0044007  umgerechnete Logadresse  0
						278536			0044008 - 004400F
						278544			0044010 - 0044017
											0044018 - 004401F
											0044020 - 0044027  umgerechnete Logadresse  1
											....... - .......
											....... - .......

						282592			0044FE0 - 0044FE7  umgerechnete Logadresse  127
						282600			0044FE8 - 0044FEF
						282608			0044FF0 - 0044FF7
						282616			0044FF8 - 0044FFF

											0045000            umgerechnete Logadresse  128
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
		if ( $LogAddress < 278528 )
		   $LogAddress = 278528;
		
		$LogAddress = str_pad(strtoupper(dechex($LogAddress)), 8 ,'0', STR_PAD_LEFT);
      
		
		
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