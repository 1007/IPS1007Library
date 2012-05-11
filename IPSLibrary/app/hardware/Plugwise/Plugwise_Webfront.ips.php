<?

	if ( $IPS_SENDER != 'WebFront' ) return;
	
	IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	$CircleVisuPath = "Visualization.WebFront.Hardware.Plugwise.MENU.Circles";
  	$CircleIdCData  = get_ObjectIDByPath($CircleVisuPath);

	//echo $IPS_SENDER;
 	//echo $IPS_VARIABLE ;
  	//echo $IPS_VALUE ;
   //SetValue($IPS_VARIABLE, $IPS_VALUE);

	$parent = IPS_GetParent($IPS_VARIABLE);
	$object = IPS_GetObject($parent);
	
	//***************************************************************************
	// Gruppenmenu
	// Gruppe auswaehlen
	//***************************************************************************
	if ( $object['ObjectName'] == 'Gruppen' )
	   {
	   if ( GetValue($IPS_VARIABLE) == 1 )
	      {
			$childs = IPS_GetChildrenIDs($parent);
			foreach ( $childs as $child )
	   			{
	   			SetValue($child, 0);
					IPS_SetHidden($child,false);
					$hidecircles = true;
	   			}
	      }
	   else
	      {
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
		if ( $hidecircles )
		   {
			IPS_SetHidden($CircleIdCData,true);

  			foreach ( $childs as $child )
  			   	{
  					IPS_SetHidden($child,true);
					SetValue($child,0);
					}
			}
		else
		   {
			IPS_SetHidden($CircleIdCData,false);
			}
			
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
						//echo $x ;
						if ( $x < 1 ) SetValue($id,1);
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
      //print_R(IPS_GetObject($IPS_VARIABLE));

	   $VisuPath  = "Visualization.WebFront.Hardware.Plugwise.DATA1";
      $IdData1   = get_ObjectIDByPath($VisuPath);
	   $VisuPath  = "Visualization.WebFront.Hardware.Plugwise.DATA2";
      $IdData2   = get_ObjectIDByPath($VisuPath);

      
      $object2 = IPS_GetObject($IPS_VARIABLE);
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
	//***************************************************************************
?>