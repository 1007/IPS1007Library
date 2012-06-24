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
* @defgroup bplugwisewebfront Plugwise Webfront
* @{
* @file          Plugwise_Webfront.ips.php
* @author        1007
* @version       1.0
*
********************************************************************************/


	if ( $IPS_SENDER != 'WebFront' ) return;
	
	
	
	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	$CircleVisuPath = "Visualization.WebFront.Hardware.Plugwise.MENU.Stromzähler";
  	$CircleIdCData  = get_ObjectIDByPath($CircleVisuPath);
	$AppPath        = "Program.IPSLibrary.app.hardware.Plugwise";

	$IdApp     = get_ObjectIDByPath($AppPath);
	
	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.DATA1";
   $IdData1   = get_ObjectIDByPath($VisuPath);
	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.DATA2";
   $IdData2   = get_ObjectIDByPath($VisuPath);
	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.GRAPH";
   $IdGraph   = get_ObjectIDByPath($VisuPath);
	$VisuPath  = "Visualization.WebFront.Hardware.Plugwise.MENU";
   $IdMenu    = get_ObjectIDByPath($VisuPath);

	$parent = IPS_GetParent($IPS_VARIABLE);
	$object = IPS_GetObject($parent);
	
	$self = IPS_GetObject($IPS_VARIABLE);

	
	

	//***************************************************************************
	// Menu Uebersicht
	//***************************************************************************
	if ( $self['ObjectIdent'] == 'Auswahl' )
	   {
		SetValue($IPS_VARIABLE, $IPS_VALUE);
		update_uebersicht();
		return;
		}
	//***************************************************************************

	//***************************************************************************
	// Systemsteuerung
	// Systemsteuerung auswaehlen
	//***************************************************************************
	
	if ( $self['ObjectName'] == 'Systemsteuerung' )
	   {	// Systemsteuerung anwaehlen
	   if ( GetValue($IPS_VARIABLE) == 0 )
			{
			reset_groups(true);
			SetValue($IPS_VARIABLE, 1);
			}
		else
		   {
			reset_groups(true);
		   SetValue($IPS_VARIABLE,0);
		   }
		}
	//***************************************************************************

	//***************************************************************************
	// Scripte starten
	//***************************************************************************

	if ( $self['ObjectInfo'] == 'Script' )
	   {
		$id = IPS_GetObjectIDByName("Uebersicht",$IdGraph);

		SetValue($IPS_VARIABLE, 1);

		if ( $self['ObjectName'] == 'OnlineUpdate' )
		   {
		   SetValue($id,"Online Update wird gestartet");
			IPS_RunScript(IPS_GetScriptIDByName("Plugwise_IPSModulupdaten",$IdApp));
		   }

		if ( $self['ObjectName'] == 'Kalibrierung' )
		   {
		   SetValue($id,"Kalibrierung wird gestartet");
			IPS_RunScript(IPS_GetScriptIDByName("Plugwise_Recalibrate",$IdApp));
		   }

		if ( $self['ObjectName'] == 'Circles suchen' )
		   {
		   SetValue($id,"Circlesuche wird gestartet");
			IPS_RunScript(IPS_GetScriptIDByName("Plugwise_Circlesearch",$IdApp));
		   }

		if ( $self['ObjectName'] == 'Circlezeit lesen' )
		   {
		   SetValue($id,"Circlezeit wird gelesen");
			IPS_RunScript(IPS_GetScriptIDByName("Plugwise_ReadTime",$IdApp));
		   }

		if ( $self['ObjectName'] == 'Circlezeit setzen' )
		   {
		   SetValue($id,"Circlezeit wird gestellt");
			IPS_RunScript(IPS_GetScriptIDByName("Plugwise_SetTime",$IdApp));
		   }

		if ( $self['ObjectName'] == 'Update vorhanden?' )
		   {
		   SetValue($id,"Update wird gesucht");
			$string = IPS_RunScriptWait(IPS_GetScriptIDByName("Plugwise_CheckUpdate",$IdApp));
		   SetValue($id,$string);
		   }

		if ( $self['ObjectName'] == 'Versionsinfo' )
		   {
		   SetValue($id,get_version());
		   }

		   
      SetValue($IPS_VARIABLE, 0);
		return;
		}
		
	//***************************************************************************
	// Gruppenmenu
	// Gruppe auswaehlen
	//***************************************************************************
	$sonstige = false;
	if ( $object['ObjectName'] == 'Gruppen' )
	   {
	   if ( GetValue($IPS_VARIABLE) == 1 )
	      {  // wenn bereits eine Gruppe gewaehlt dann alle Gruppen abwaehlen
			$childs = IPS_GetChildrenIDs($parent);
			foreach ( $childs as $child )
	   			{
	   			SetValue($child, 0);
					IPS_SetHidden($child,false);
					$hidecircles = true;
	   			}
	      }
	   else
	      {  // andere Gruppe waehlen
			$childs = IPS_GetChildrenIDs($parent);
			foreach ( $childs as $child )
	   			{
	   			SetValue($child, 0);
					IPS_SetHidden($child,true);
	   			}
			SetValue($IPS_VARIABLE, $IPS_VALUE);
			IPS_SetHidden($IPS_VARIABLE,false);
			$object = IPS_GetObject ($IPS_VARIABLE);
			
			if ( $object['ObjectIdent'] == "SYSTEM_REST" )
			   $sonstige = true;

			$hidecircles = false;
			$id = IPS_GetObjectIDByIdent('Systemsteuerung',$IdMenu);  // Systemsteuerung aus
			SetValue($id,0);
			}


		// alle Cirles durchgehen
		$childs = IPS_GetChildrenIDs($CircleIdCData);
		if ( $hidecircles )  // wenn Circles versteckt werden sollen
		   {
			IPS_SetHidden($CircleIdCData,true); // Ueberschrift verstecken
			
  			foreach ( $childs as $child )
  			   	{
  					IPS_SetHidden($child,true);   // Circles verstecken
					SetValue($child,0);           // und auf 0
					}
			}
		else
		   {
			if ( $sonstige == false )
			IPS_SetHidden($CircleIdCData,false);   // Ueberschrift anzeigen ausser bei Sonstige
			}
			
		// Circles anzeigen die in der angewaehlten Gruppe sind
		$gruppenname = IPS_GetObject($IPS_VARIABLE);
		$gruppenname = $gruppenname['ObjectName'];
		$array = array();
		foreach ( $CircleGroups as $group ) array_push($array,$group[1]);
		
		$x = 0 ;
  		foreach ( $CircleGroups as $cycle )
      		{
      		if ( $cycle[0] != "" )
        			{
         		$id = IPS_GetObjectIDByName($cycle[1],$CircleIdCData);

					if ( $gruppenname == $cycle[2] and !$hidecircles)
						{
						IPS_SetHidden($id,false);
						}
					else
			   		{
						IPS_SetHidden($id,true);
						}

        			}
				
      		}
		}
	//***************************************************************************


	//***************************************************************************
	// Circlemenu
	// Button farblich darstellen. Alle anderen auf 0
	//***************************************************************************
	if ( $object['ObjectName'] == 'Stromzähler' )
	   {
	   //hide_data1data2();
	   $value = GetValue($IPS_VARIABLE);
		$childs = IPS_GetChildrenIDs($parent);
		foreach ( $childs as $child )
	   		{
	   		SetValue($child, 0);
	   		}

	   if ( $value == 0 )
		   {
      	SetValue($IPS_VARIABLE, 1);
         }
		else
		   {
      	SetValue($IPS_VARIABLE, 0);
         }

         
		}
	//***************************************************************************
	

	//***************************************************************************
	// ab hier wird entschieden was angezeigt werden soll
	// **************************************************************************
	hide_graph(true);
   hide_data1data2();
	$showid = false;
	
	$id = IPS_GetObjectIDByIdent('Systemsteuerung',$IdMenu);  // Systemsteuerung 

	if ( GetValue($id) > 0 )
	   { 
	   show_main($IdData1,$IdData2);
	   return;
	   }

	$aktuelle_gruppenid = 0;
	
	$id = IPS_GetObjectIDByIdent('Gruppen',$IdMenu);  // Gruppen
	$childs = IPS_GetChildrenIDs($id);
	$ok = false;
	foreach ( $childs as $child )
	   		{
	   		if ( GetValue($child) > 0 )
	   		   {
					$ok = true;
					$aktuelle_gruppenid = $child;
					$showid = $IPS_VARIABLE ; 
					break;
					}
				}
	if ( !$ok )
	   {  // Keine Gruppe angewaehlt - Gesamtverbrauch anzeigen

		show_webfront(0);
		return;

		
		}


	//$id = IPS_GetObjectIDByIdent('GruppenItem',$IdMenu);  // Circles
	$id = $CircleIdCData;
	$childs = IPS_GetChildrenIDs($id);
	$ok = false;
	foreach ( $childs as $child )
	   		{
	   		if ( GetValue($child) > 0 )
	   		   {
					$ok = true;
					$showid = $IPS_VARIABLE;
					show_webfront($showid); 
					return;
					}
				}

	if ( !$ok )
	   {  // Kein Circle angewaehlt - Gesamtverbrauch der Gruppe anzeigen
					
		show_webfront($aktuelle_gruppenid);
		return;

		}

/***************************************************************************//**
*	VersionsInfo mit Changelog zurueckgeben
*******************************************************************************/
function get_version()
	{
	IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');
   $moduleManager = new IPSModuleManager('Plugwise');
	$version = "<h3>Version : " .$moduleManager->VersionHandler()->GetModuleVersion() ."</h3>";

	$pfad =IPS_GetKernelDir()."webfront\\User\\Plugwise";
	$file = $pfad . "\\Changelog.txt";
	
	//$version = $version . "<br>" . $file;
	if ( file_exists ( $file ) )
		$string = file_get_contents($file);
	else
	   $string = "<br>Changelog.txt nicht gefunden<br>";
	$string = nl2br($string);
	
	$version = $version . "<br>" . $string;

	return $version;
	}
	
//******************************************************************************
// zeigt Data1 , Data2 , Graph
//******************************************************************************
function show_webfront($showid)
	{
	GLOBAL $IdApp;
	show_status_in_menu($showid);
   show_data1data2($showid);

	$id = IPS_GetScriptIDByName('Plugwise_Config_Highcharts',$IdApp);
	IPS_RunScript($id);

	}

//******************************************************************************
// leert die HTMLBox
//******************************************************************************
function hide_graph($status = true)
	{
	GLOBAL $IdGraph;
	$id = IPS_GetObjectIDByName("Uebersicht",$IdGraph);
	SetValueString($id,"");
	
	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	IPS_SetHidden($id,true);

	
	// geht nicht ohne Reload WFC - wahrscheinlich wegen ~HTML
	// IPS_SetHidden($id,$status);
	}

//******************************************************************************
// zeigt die Scripte - Sytemsteuerung an
//******************************************************************************
function show_main($IdData1,$IdData2)
	{
	GLOBAL $IdGraph;

	hide_data1data2();
	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		$object = IPS_GetObject($child);
		if ( $object['ObjectInfo'] == 'Script' )
			IPS_SetHidden($child,false);
		}
	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		$object = IPS_GetObject($child);
		if ( $object['ObjectInfo'] == 'Script' )
			IPS_SetHidden($child,false);
		}

	update_uebersicht();
	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	IPS_SetHidden($id,false);

	}

//******************************************************************************
// Reset Gruppen und Circlegruppe verstecken
//******************************************************************************
function reset_groups()
	{
	GLOBAL $IdMenu;
	GLOBAL $CircleIdCData;
	GLOBAL $IdGraph;
	
	$parent = IPS_GetObjectIDByName('Gruppen',$IdMenu);

	$childs = IPS_GetChildrenIDs($parent);
	foreach ( $childs as $child )
			{
	   	SetValue($child, 0);
         $object = IPS_GetObject($child);
         if ( !$object['ObjectIsHidden'] )
				IPS_SetHidden($child,false);
			}
	   	
	IPS_SetHidden($CircleIdCData,true);
	
	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	IPS_SetHidden($id,true);


	}
	
//******************************************************************************
// versteckt alle Data1 und Data2
//******************************************************************************
function hide_data1data2()
	{
	GLOBAL $IdData1;
	GLOBAL $IdData2;
	
	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		$object = IPS_GetObject($child);
      if ( !$object['ObjectIsHidden'] )
			IPS_SetHidden($child,true);
		}
	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		$object = IPS_GetObject($child);
      if ( !$object['ObjectIsHidden'] )
			IPS_SetHidden($child,true);
		}
	}
	
	
function show_status_in_menu($id)
	{
	GLOBAL $IdMenu;
	
	$object = IPS_GetObject($id);
	$name = $object['ObjectName'];
	$info = $object['ObjectInfo'];

	foreach ( IPS_GetChildrenIDs($IdMenu) as $child )
		{
		$object = IPS_GetObject($child);
		if ( $object['ObjectType'] == 6 )   // Link
		   {
		   if ( $object['ObjectInfo'] == $info )
		   	{
				IPS_SetHidden($child,false);
				}
			else
				{
		   	IPS_SetHidden($child,true);
				}
		   }


		}

	}
	
//******************************************************************************
// zeigt die in $id uebergebenen Daten in Data1 und Data2 an
//******************************************************************************
function show_data1data2($id)
	{
	GLOBAL $IdGraph;
	GLOBAL $IdData1;
	GLOBAL $IdData2;

	
	$object = IPS_GetObject($id);
	$name = $object['ObjectName'];
	$info = $object['ObjectInfo'];

	if ( $object['ObjectID'] == 0 )
		{
	   $name = "Gesamt";
	   $info = "Gesamt";
	   }

	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		$object = IPS_GetObject($child);

		if ( $object['ObjectIdent'] == "WEBDATA1" )
		   {
		   IPS_SetName($child,$name);
         IPS_SetHidden($child,false);
         IPS_SetInfo($child,$info);
         }
         
		if ( $object['ObjectType'] == 6 )   // Link
		   {
		   if ( $object['ObjectInfo'] == $info )
		   	{
				IPS_SetHidden($child,false);
				}
			else
				{
		   	IPS_SetHidden($child,true);
				}
		   }
		   
		}
		
	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		$object = IPS_GetObject($child);
		
		if ( $object['ObjectIdent'] == "WEBDATA2" )
		   {
		   IPS_SetName($child,$name);
         IPS_SetHidden($child,false);
         }
		}

   update_data1_data2();
	
	return;
	
	if ( $id == 0 )
		{
		$id = @IPS_GetObjectIDByIdent('Gesamt',$IdData1);
		IPS_SetHidden($id,false);
		$id = @IPS_GetObjectIDByIdent('Gesamt',$IdData2);
		IPS_SetHidden($id,false);
		}

   $object2 = IPS_GetObject($id);

	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		$object1 = IPS_GetObject($child);
		if ( $object1['ObjectInfo'] == $object2['ObjectInfo'] )
			IPS_SetHidden($child,false);
		else
		   IPS_SetHidden($child,true);
		}
	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		$object1 = IPS_GetObject($child);
		if ( $object1['ObjectInfo'] == $object2['ObjectInfo'] )
			IPS_SetHidden($child,false);
		else
		   IPS_SetHidden($child,true);
		}
	
	}

/***************************************************************************//**
* @}
* @}
*******************************************************************************/

?>