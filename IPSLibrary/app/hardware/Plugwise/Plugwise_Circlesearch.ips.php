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
* @defgroup Plugwise_Circlesearch Plugwise Cirlcesearch
* @{
*
* @file       Plugwise_Circlesearch.ips.php
* @author     Axel Philippson (axelp) , Juergen Gerharz (1007)
* @version    Version 1.0.0
* @date       18.05.2012
*
*
* @brief   Plugwise Circlesearch
*
*******************************************************************************/

	IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");

   unknowncircles("",true);

	PW_SendCommand("0008");
	IPS_Sleep(10);
	PW_SendCommand("000801");

/***************************************************************************//**
* @}
* @}
*******************************************************************************/
?>