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

	$StromzaehlerVisuPath = "Visualization.WebFront.Hardware.Plugwise.MENU.Stromzähler";
  	$StromzaehlerIdData   = get_ObjectIDByPath($StromzaehlerVisuPath);



	$VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.DATA1";
   $IdData1   		= get_ObjectIDByPath($VisuPath);
	$VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.DATA2";
   $IdData2   		= get_ObjectIDByPath($VisuPath);
	$VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.GRAPH";
   $IdGraph   		= get_ObjectIDByPath($VisuPath);
	$VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.MENU";
   $IdMenu    		= get_ObjectIDByPath($VisuPath);
	$AllgPath  		= "Visualization.WebFront.Hardware.Plugwise.MENU.Allgemeines";
   $IdAllg    		= get_ObjectIDByPath($AllgPath);
	$GroupsPath  	= "Visualization.WebFront.Hardware.Plugwise.MENU.Gruppen";
   $IdGroups    	= get_ObjectIDByPath($GroupsPath);
	$SystemstPath  = "Visualization.WebFront.Hardware.Plugwise.MENU.Systemsteuerung";
   $IdSystemst    = get_ObjectIDByPath($SystemstPath);
//	$AuswPath     	= "Visualization.WebFront.Hardware.Plugwise.MENU.Antwortzeiten";
//   $IdAusw       	= get_ObjectIDByPath($AuswPath);

	$parent = IPS_GetParent($IPS_VARIABLE);
	$object = IPS_GetObject($parent);
	
	$self = IPS_GetObject($IPS_VARIABLE);

	
	

	//***************************************************************************
	// UnterMenu Uebersicht
	//***************************************************************************
//	if ( $self['ObjectIdent'] == 'Auswahl' )
//	   {
//
//		SetValue($IPS_VARIABLE, $IPS_VALUE);
//		update_uebersicht_circles();
//		return;
//		}
	//***************************************************************************

	//***************************************************************************
	// Systemsteuerung
	// Systemsteuerung auswaehlen
	//***************************************************************************
	if ( $self['ObjectName'] == 'Systemsteuerung' )
	   {	// Systemsteuerung
	   if ( GetValue($IPS_VARIABLE) == 0 )  // ist abgewaehlt
			{
			//IPS_LogMessage($IPS_VALUE,"Systemsteuerung ist abgewaehlt soll angewaehlt werden");
			reset_menu_stromzaehler();
			reset_gruppen();
			IPS_SetHidden($IdSystemst,false);
			//SetValue(IPS_GetVariableIDByName("Antwortzeiten",$IdAllg),0);
			//IPS_SetHidden($IdAusw,true);
			IPS_SetHidden(IPS_GetVariableIDByName("Auswahl",$IdGraph),false);
			SetValue($IPS_VARIABLE,1);
			
			}
		else // ist angewaehlt angewaehlt
		   {
			//IPS_LogMessage($IPS_VALUE,"Systemsteuerung ist angewaehlt soll abgewaehlt werden");
			reset_menu_stromzaehler();
			reset_gruppen(true);
		   IPS_SetHidden($IdSystemst,true);
         //SetValue(IPS_GetVariableIDByName("Antwortzeiten",$IdAllg),0);
         //IPS_SetHidden($IdAusw,true);
			IPS_SetHidden(IPS_GetVariableIDByName("Auswahl",$IdGraph),false);
			SetValue($IPS_VARIABLE,0);
		   }
		}
	//***************************************************************************
	
	//***************************************************************************
	// Auswertungen
	//***************************************************************************
	if ( $self['ObjectName'] == 'Antwortzeiten' )
	   {	
	   if ( GetValue($IPS_VARIABLE) == 0 )  // ist abgewaehlt
			{
			//IPS_LogMessage($IPS_VALUE,"Auswertungen ist abgewaehlt soll angewaehlt werden");
			reset_menu_stromzaehler();
			reset_gruppen();
			IPS_SetHidden($IdAusw,false);
			SetValue(IPS_GetVariableIDByName("Systemsteuerung",$IdAllg),0);
			IPS_SetHidden($IdSystemst,true);
			//IPS_SetHidden(IPS_GetVariableIDByName("Auswahl",$IdGraph),false);
			SetValue($IPS_VARIABLE,1);
			}
		else // ist angewaehlt angewaehlt
		   {
			//IPS_LogMessage($IPS_VALUE,"Auswertungen ist angewaehlt soll abgewaehlt werden");
			reset_menu_stromzaehler();
			reset_gruppen(true);
		   IPS_SetHidden($IdAusw,true);
         SetValue(IPS_GetVariableIDByName("Systemsteuerung",$IdAllg),0);
         IPS_SetHidden($IdSystemst,true);
			SetValue($IPS_VARIABLE,0);
		   }

		}

	//***************************************************************************
	// Scripte starten
	//***************************************************************************
	if ( $self['ObjectInfo'] == 'Script' )
	   {
	   start_script($self,$IPS_VARIABLE);
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
				reset_gruppen(true);
				$hidecircles = true;
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
			//$id = IPS_GetObjectIDByIdent('Systemsteuerung',$IdAllg);  // Systemsteuerung aus
			//SetValue($id,0);
			}


		// alle Cirles durchgehen
		$childs = IPS_GetChildrenIDs($StromzaehlerIdData);
		if ( $hidecircles )  // wenn Circles versteckt werden sollen
		   {
			IPS_SetHidden($StromzaehlerIdData,true); // Ueberschrift verstecken
			
  			foreach ( $childs as $child )
  			   	{
  					IPS_SetHidden($child,true);   // Circles verstecken
					SetValue($child,0);           // und auf 0
					}
			}
		else
		   {
			if ( $sonstige == false )
			IPS_SetHidden($StromzaehlerIdData,false);    // Ueberschrift anzeigen ausser bei Sonstige
			}

			
		//************************************************************************
		// Circles anzeigen die in der angewaehlten Gruppe sind
		//************************************************************************
		$gruppenname = IPS_GetObject($IPS_VARIABLE);
		$gruppenname = $gruppenname['ObjectName'];
		$array = array();
	
		foreach ( $CircleGroups as $group ) array_push($array,$group[1]);
		
   	foreach ( $CircleGroups as $cycle )
      	if ( $cycle[0] != "" )
				if ( $gruppenname == $cycle[2] and !$hidecircles)
				   {
				   //IPS_logmessage(__FILE__,$StromzaehlerIdData."-".$cycle[1]);
					IPS_SetHidden(IPS_GetObjectIDByName($cycle[1],$StromzaehlerIdData),false);
					}
				else
				   {
					//IPS_logmessage(__FILE__,$StromzaehlerIdData."-".$cycle[1]);
					IPS_SetHidden(IPS_GetObjectIDByName($cycle[1],$StromzaehlerIdData),true);
					}

		//************************************************************************
		// Externe anzeigen die in der angewaehlten Gruppe sind
		//************************************************************************
		$gruppenname = IPS_GetObject($IPS_VARIABLE);
		$gruppenname = $gruppenname['ObjectName'];
		$array = array();

		foreach ( $ExterneStromzaehlerGroups as $group ) array_push($array,$group[1]);

  		foreach ( $ExterneStromzaehlerGroups as $extern )
      	if ( $extern[0] != "" )
				if ( $gruppenname == $extern[1] and !$hidecircles)
					IPS_SetHidden($id = IPS_GetObjectIDByName($extern[0],$StromzaehlerIdData),false);
				else
			   	IPS_SetHidden($id = IPS_GetObjectIDByName($extern[0],$StromzaehlerIdData),true);

      		
      		
		}
	//***************************************************************************


	//***************************************************************************
	// Stromzaehlermenu
	// Button farblich darstellen. Alle anderen auf 0
	//***************************************************************************
	if ( $object['ObjectName'] == 'Stromzähler' )
	   {
	   //hide_data1data2();
	   $value = GetValue($IPS_VARIABLE);
		$childs = IPS_GetChildrenIDs($parent);
		foreach ( $childs as $child )
	   		SetValue($child, 0);

	   if ( $value == 0 )
      	SetValue($IPS_VARIABLE, 1);
		else
      	SetValue($IPS_VARIABLE, 0);

         
		}
	//***************************************************************************
	

	//***************************************************************************
	// ab hier wird entschieden was angezeigt werden soll
	// **************************************************************************
	$aktuelle_gruppenid = 0;
	$ok = false;
	//***************************************************************************
	// Soll Systemsteuerung angezeigt werden ?
	// **************************************************************************
	if ( GetValue(IPS_GetObjectIDByIdent('Systemsteuerung',$IdAllg)) > 0 )
	   {
	   show_status_in_menu(0,true);
		update_webfront_123("SYSTEMSTEUERUNG",0,true);
		update_uebersicht_circles();
		return;
		}
		
	//***************************************************************************
	// Soll Auswertung angezeigt werden ?
	// **************************************************************************
/*
	if ( GetValue(IPS_GetObjectIDByIdent('Antwortzeiten',$IdAllg)) > 0 )
	   {
      show_status_in_menu(0,true);
		update_webfront_123("ANTWORTZEITEN",0,true);
		return;
		}
*/
	//***************************************************************************
	// Soll ein Stromzaehler angezeigt werden ?
	// **************************************************************************
	foreach ( IPS_GetChildrenIDs($StromzaehlerIdData) as $child )
		if ( GetValue($child) > 0 )
	   	{
	   	resetHCTimeline();
	   	show_status_in_menu($child);
         update_webfront_123("ZAEHLER",$child,true);
			return;
			}
		
	//***************************************************************************
	// Soll eine Gruppe angezeigt werden ?
	// **************************************************************************
	foreach ( IPS_GetChildrenIDs(IPS_GetObjectIDByIdent('Gruppen',$IdMenu)) as $child )
		if ( GetValue($child) > 0 )
	   	{
         show_status_in_menu(0,true);
         update_webfront_123("GRUPPE",$child,true);
			return;
			}
				
	//***************************************************************************
	// immer noch $ok==false dann Gesamt anzeigen
	// **************************************************************************
	if ( !$ok )
	   {
	   show_status_in_menu(0,true);
		update_webfront_123("GESAMT",0,true);
		return;
		}
	
/***************************************************************************//**
*	Starten von Scripten
*******************************************************************************/
function start_script($self)
	{
	GLOBAL $IdGraph;
	GLOBAL $IdApp;

	//IPS_LogMessage("Start Script",$self['ObjectName']);
	
	$id = IPS_GetObjectIDByIdent("Uebersicht",$IdGraph);

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

	}


/***************************************************************************//**
*	Reset Zeiteinstellunge fuer Highcharts
*******************************************************************************/
function resetHCTimeline()
	{
	$HighchartsPath    = "Visualization.WebFront.Hardware.Plugwise.Highcharts";
	$HighchartsId      = get_ObjectIDByPath($HighchartsPath);

	//***************************************************************************
	// Zeitraum welcher dargestellt werden soll
	//***************************************************************************
	$startid = IPS_GetVariableIDByName('StartTime',$HighchartsId);
	$endeid  = IPS_GetVariableIDByName('EndTime',$HighchartsId);
	$nowid   = IPS_GetVariableIDByName('Now',$HighchartsId);


	if ( defined('HIGHCHARTS_ZEITRAUM') )
		$zeitrum_stunden = HIGHCHARTS_ZEITRAUM;
	else
		$zeitrum_stunden = 24;

	$starttime = time() - (60*60*$zeitrum_stunden );

	SetValue($startid,$starttime);

	$endetime = time() ;

	SetValue($endeid,$endetime);

	SetValue($nowid,true);


	}
/***************************************************************************//**
*	VersionsInfo mit Changelog zurueckgeben
*******************************************************************************/
function get_version()
	{
	IPSUtils_Include ('IPSModuleManager.class.php', 'IPSLibrary::install::IPSModuleManager');
   $moduleManager = new IPSModuleManager('Plugwise');
	$version = "<h3>Version : " .$moduleManager->VersionHandler()->GetVersion('Plugwise') ."</h3>";

	$pfad =IPS_GetKernelDir()."webfront/User/Plugwise";
	$file = $pfad . "/Changelog.txt";
	
	//$version = $version . "<br>" . $file;
	if ( file_exists ( $file ) )
		$string = file_get_contents($file);
	else
	   $string = "<br>Changelog.txt nicht gefunden<br>";
	$string = nl2br($string);
	
	$version = $version . "<br>" . $string;

	return $version;
	}
	



/***************************************************************************//**
*  Die Gruppen reseten ( Auswahl auf 0 ) und Gruppen verstecken
*******************************************************************************/
function reset_gruppen($show_gruppen = false)
	{
	GLOBAL $IdMenu;
	GLOBAL $IdGroups;
	GLOBAL $IdGraph;

	$parent = IPS_GetObjectIDByName('Gruppen',$IdMenu);

	$childs = IPS_GetChildrenIDs($parent);

	foreach ( $childs as $child )
			{
	   	SetValue($child, 0);
         $object = IPS_GetObject($child);
         if ( $object['ObjectIsHidden'] )
				IPS_SetHidden($child,false);
			}

	if ( $show_gruppen == false )
		IPS_SetHidden($IdGroups,true);
	else
		IPS_SetHidden($IdGroups,false);

	$id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
	IPS_SetHidden($id,true);

	}

	
/***************************************************************************//**
*  Die Gruppe Stromzaehler reseten ( Auswahl auf 0 ) und Gruppe verstecken
*******************************************************************************/
function reset_menu_stromzaehler()
	{
	GLOBAL	$StromzaehlerIdData;

	foreach ( IPS_GetChildrenIDs($StromzaehlerIdData) as $child )
		{
		$object = IPS_GetObject($child);
      if ( !$object['ObjectIsHidden'] )
			IPS_SetHidden($child,true);
		if ( GetValue($child) != 0 )
		   SetValue($child,0);
		}

	$object = IPS_GetObject($StromzaehlerIdData);
   if ( !$object['ObjectIsHidden'] )
		IPS_SetHidden($StromzaehlerIdData,true);


	}


/***************************************************************************//**
*  Den Status eine Circles ( AN/AUS ) im Menue anzeigen oder alle verbergen
*******************************************************************************/
function show_status_in_menu($id,$hideall = false)
	{
	GLOBAL $IdMenu;
	
	$object = IPS_GetObject($id);
	$name = $object['ObjectName'];
	$info = $object['ObjectInfo'];

	//IPS_LogMessage("Start- SHOW",$id."-".$name."-".$info."-".$hideall);

	foreach ( IPS_GetChildrenIDs($IdMenu) as $child )
		{
		$object = IPS_GetObject($child);

		if ( $object['ObjectType'] == 6 )   // Link
		   {
			if ( $hideall == true )
			   { //IPS_LogMessage(".","NOLINK");
				IPS_SetHidden($child,true);
				}
			else
			   {
				if ( $object['ObjectInfo'] == $info )
		   		{//IPS_LogMessage(".","FALSE");
					IPS_SetHidden($child,false);
					}
				else
					{//IPS_LogMessage(".","TRUE");
		   		IPS_SetHidden($child,true);
					}
				}
		   }


		}

	//IPS_LogMessage("Ende- SHOW",$id."-".$name."-".$info);

	}

	
/***************************************************************************//**
* @}
* @}
*******************************************************************************/

?>
