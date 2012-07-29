<?php 


  if 	(isset($_GET['Button']))  $button = $_GET['Button']; else die() ;

 	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");
  IPSUtils_Include("Plugwise_Include.ips.php","IPSLibrary::app::hardware::Plugwise");

  $VisuPath  = "Visualization.WebFront.Hardware.Plugwise.GRAPH";
  $IdGraph   = @get_ObjectIDByPath($VisuPath,true);

  $id = IPS_GetObjectIDByName('Auswahl',$IdGraph);
   
  $s = "[".$button."]";
  //IPS_logMessage("PlugwiseWebMenuController.php",$s);

  $newValue = intval($button);  
  $oldValue = GetValue($id);
  
  $object = IPS_GetObject($id);
  $ident = intval($object['ObjectIdent']);
  
  if ( $oldValue == $newValue )
    {
    $ident = $ident + 1; 
    if ( $ident > 2 )
      $ident = 0;
    }
  else
    {
    $ident = 0;
    }
        
  SetValue($id,intval($button));

  IPS_SetIdent($id,$ident);
  
  update_uebersicht_circles();  
  
?> 