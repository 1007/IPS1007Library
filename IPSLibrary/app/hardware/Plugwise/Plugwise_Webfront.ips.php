<?
	
	if ( $IPS_SENDER != 'WebFront' ) return;
	
	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	$CircleVisuPath = "Visualization.WebFront.Hardware.Plugwise.MENU.Circles";
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


	//echo $IPS_SENDER;
 	//echo $IPS_VARIABLE ;
  	//echo $IPS_VALUE ;
   //SetValue($IPS_VARIABLE, $IPS_VALUE);

	$parent = IPS_GetParent($IPS_VARIABLE);
	$object = IPS_GetObject($parent);
	
	//***************************************************************************
	// Systemsteuerung
	// Systemsteuerung auswaehlen
	//***************************************************************************
	$self = IPS_GetObject($IPS_VARIABLE);
	
	if ( $self['ObjectName'] == 'Systemsteuerung' )
	   {	// Systemsteuerung anwaehlen
	   if ( GetValue($IPS_VARIABLE) == 0 )
			{
			reset_groups(true);
			hide_circles(true);
      	hide_graph(true);
      	hide_data1data2($IdData1,$IdData2);
      	show_main($IdData1,$IdData2);
			SetValue($IPS_VARIABLE, 1);

			return;
			}
		else
		   {
			reset_groups(true);
			hide_circles(true);
			hide_data1data2($IdData1,$IdData2);
      	hide_graph(false);
      	//($IdData1,$IdData2);

		   SetValue($IPS_VARIABLE,0);

		   }
			

		}
		
	//***************************************************************************
	// Gruppenmenu
	// Gruppe auswaehlen
	//***************************************************************************
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
			$hidecircles = false;
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
			IPS_SetHidden($CircleIdCData,false);   // Ueberschrift anzeigen
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
						
						//ersten Eintrag anwaehlen
						if ( $x < 1 )
							{
							hide_data1data2($IdData1,$IdData2);
							SetValue($id,1);
							show_data1data2($id,$IdData1,$IdData2);

							}
						$x++;
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
	// Circlemenu
	// Button farblich darstellen. Alle anderen auf 0
	//***************************************************************************
	if ( $object['ObjectName'] == 'Circles' )
	   {
		$childs = IPS_GetChildrenIDs($parent);
		foreach ( $childs as $child )
	   		{
	   		SetValue($child, 0);
	   		}
      SetValue($IPS_VARIABLE, $IPS_VALUE);
      
      show_data1data2($IPS_VARIABLE,$IdData1,$IdData2);
		}
	//***************************************************************************
	

	$id = IPS_GetScriptIDByName('Plugwise_Config_Highcharts',$IdApp);
	IPS_RunScript($id);


function hide_graph($status = true)
	{
	GLOBAL $IdGraph;
	$id = IPS_GetObjectIDByName("Uebersicht",$IdGraph);

	SetValueString($id,"");
	// geht nicht ohne Reload WFC - wahrscheinlich wegen ~HTML
	// IPS_SetHidden($id,$status);
	}
	
function show_main($IdData1,$IdData2)
	{
	hide_data1data2($IdData1,$IdData2);
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

	}

function hide_circles()
	{
	
	}
function reset_groups()
	{
	GLOBAL $IdMenu;
	GLOBAL $CircleIdCData;
	$parent = IPS_GetObjectIDByName('Gruppen',$IdMenu);

	$childs = IPS_GetChildrenIDs($parent);
	foreach ( $childs as $child )
			{
	   	SetValue($child, 0);
			IPS_SetHidden($child,false);
			}
	   	
	IPS_SetHidden($CircleIdCData,true);

	}
	
function hide_data1data2($IdData1,$IdData2)
	{
	foreach ( IPS_GetChildrenIDs($IdData1) as $child )
		{
		IPS_SetHidden($child,true);
		}
	foreach ( IPS_GetChildrenIDs($IdData2) as $child )
		{
		IPS_SetHidden($child,true);
		}
	}
	
	
	
function show_data1data2($id,$IdData1,$IdData2)
	{
	GLOBAL $IdGraph;
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
?>