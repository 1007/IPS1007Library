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
* @defgroup Plugwise_CheckUpdate Plugwise CheckUpdate
* @{
*
* @file       Plugwise_CheckUpdate.ips.php
* @author     Juergen Gerharz (1007)
* @version    Version 1.0.0
* @date       23.06.2012
*
*
*  @brief   Plugwise CheckUpdate - checkt ob Update verfuegbar
*
*******************************************************************************/


		
	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
  	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");

	if ( defined('CHECK_VERSION') )
	   if ( CHECK_VERSION == false )
	      {
	      return;
	      }
		else
		   {
			}
	else
		{
	   return;
		}

	$local_file  = IPS_GetKernelDir() ."webfront/user/Plugwise/Changelog.txt";
	
	
	$remote_file = "https://raw.github.com/1007/IPS1007Library/master/IPSLibrary/webfront/Plugwise/Changelog.txt";

   $sourceFile = str_replace('\\','/',$remote_file);
	//echo 'Load File '.$sourceFile."\n";
	$curl_handle=curl_init();
	curl_setopt($curl_handle, CURLOPT_URL,$sourceFile);
	curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_handle, CURLOPT_FAILONERROR, true);
	$fileContent = curl_exec($curl_handle);

	if ($fileContent===false)
		{
		throw new Exception('Download of File '.$sourceFile.' failed !!!');
		}
	curl_close($curl_handle);

	$remote_file = explode("\n", $fileContent);
	$local_file  = explode("\n", file_get_contents($local_file));

	$update = $remote_file;

	$remote_file = trim($remote_file[0]);
	$local_file  = trim($local_file[0]);
	//echo "\n".$remote_file;
	//echo "\n".$local_file;

	if ( $remote_file != $local_file)
		{
		// neue Version vorhanden
		if ( defined('ALT_BUTTON_RED') )
		if ( ALT_BUTTON_RED == true )
		   {
      	$normal_file  = IPS_GetKernelDir() ."webfront/user/Plugwise/".ALT_BUTTON_RED;
      	$dest_file    = IPS_GetKernelDir() ."webfront/user/Plugwise/tabPane.png";
      	copy ( $normal_file,$dest_file);
      	IPS_logmessage("Plugwise","Neue Version vorhanden");
         if ( $IPS_SENDER != 'Timer' )
      		ReloadAllWebFronts() ;
			}
		}

	for ($x=0;$x<10;$x++)
	   {
		$s = @$update[$x];
		$s = $s ."<br>";
		echo $s;
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