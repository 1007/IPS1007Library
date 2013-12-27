<?php
/***************************************************************************//**
*	geofency.php             
*******************************************************************************/

	IPSUtils_Include ("IPSLogger.inc.php","IPSLibrary::app::core::IPSLogger");
  IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");

	IPSUtils_Include("GeofencyInfo.inc.php","IPSLibrary::app::modules::Informationen::GeofencyInfo");
	IPSUtils_Include("GeofencyInfo_Configuration.inc.php","IPSLibrary::config::modules::Informationen::GeofencyInfo");

      
  $Parent = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.GeofencyInfo");

  if ( !isset($_GET["IPSName"] ) )
    { 
    if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Kein IPSName angegeben");
    echo "Kein IPSName angegeben";
    return;
    }
  else
    {
    $IPSName = $_GET["IPSName"];
    $_POST["IPSName"] = $IPSName;
    }
  
  if ( isset( $_POST["date"] ) )     $GEOdate         =$_POST["date"] ;      else $GEOdate="";
  if ( isset( $_POST["name"] ) )     $GEOname         =$_POST["name"] ;      else $GEOname="";    
  if ( isset( $_POST["id"] ) )       $GEOid           =$_POST["id"] ;        else $GEOid="";
  if ( isset( $_POST["longitude"] ) )$GEOlongitude    =$_POST["longitude"] ; else $GEOlongitude="";
  if ( isset( $_POST["latitude"] ) ) $GEOlatitude     =$_POST["latitude"] ;  else $GEOlatitude="";
  if ( isset( $_POST["entry"] ) )    $GEOentry        =$_POST["entry"] ;     else $GEOentry="";
  if ( isset( $_POST["device"] ) )   $GEOdevice       =$_POST["device"] ;    else $GEOdevice="";

  $out = $IPSName.",".$GEOdate.",".$GEOname.",".$GEOid.",".$GEOlongitude.",".$GEOlatitude.",".$GEOentry.",".$GEOdevice;
  logging(false,$out,'incoming.log');


  if ( $GEOentry == '1' )
    $GEOentry == true;
  else
    $GEOentry = false;
    
    
  if ( $IPSName == '' )
    {
    if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Keine Location angegeben");
    echo "Keine Location angegeben";
    return;
    }

  
  if ( !$Parent )
    {
    if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,"Parent Pfad NOK");
    echo "Parent-Pfad NOK";
    return;    
    }
  
  $IDname     = CreateCategory($IPSName,$Parent,0);
  $IDLocation = CreateCategory($GEOname,$IDname,0);

  $IDlatitude  = CreateVariable('Latitude'  ,3,$IDLocation,7);  
  $IDlongitude = CreateVariable('Longitude' ,3,$IDLocation,8);  
  $IDlocID     = CreateVariable('LocationID',3,$IDLocation,6);  
  $IDankunftIPS= CreateVariable('IPSAnkunft',1,$IDLocation,4,'~UnixTimestamp',false,0);  
  $IDabfahrtIPS= CreateVariable('IPSAbfahrt',1,$IDLocation,5,'~UnixTimestamp',false,0);  
  $IDankunftGEO= CreateVariable('GEOAnkunft',1,$IDLocation,2,'~UnixTimestamp',false,0);  
  $IDabfahrtGEO= CreateVariable('GEOAbfahrt',1,$IDLocation,3,'~UnixTimestamp',false,0);  
  $IDentry     = CreateVariable('Entry'     ,0,$IDLocation,1,'~Presence',false,false);  
  $IDdevice    = CreateVariable('Device'    ,3,$IDLocation,9);  
 

  SetValue($IDentry,$GEOentry);
  SetValue($IDlatitude,$GEOlatitude);
  SetValue($IDlongitude,$GEOlongitude);
  SetValue($IDlocID,$GEOid);
  SetValue($IDdevice,$GEOdevice);
  
  if ($GEOentry)
    {
    SetValue($IDankunftIPS,time());
    $t = strtotime($GEOdate);
    SetValue($IDankunftGEO,$t);
    SetValue($IDabfahrtGEO,0);
    
    $loc = str_pad ( $GEOname, 15 , ' ' );     
    $out = "Ankunft: " . $IPSName . " - " . $loc  ;
    }
  else
    {
    SetValue($IDabfahrtIPS,time());
    $t = strtotime($GEOdate);
    SetValue($IDabfahrtGEO,$t);
    $loc = str_pad ( $GEOname, 15 , ' ' );     
    $out = "Abfahrt: " . $IPSName . " - " . $loc  ; ;
    }
    
   if ( DEBUG_MODE ) IPSLogger_Dbg(__FILE__,$out);
    
  Logging($Parent,$out,'geofency.log');

  $DeviceID = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.GeofencyInfo.".$IPSName);
  $HTMLBoxID = CreateVariable('GoogleMap'  ,3,$DeviceID,99,'~HTMLBox'); 
  DoGoogleMaps($HTMLBoxID,trim($GEOlatitude),trim($GEOlongitude));

  $HTMLBoxID = CreateVariable('OSMMap'  ,3,$DeviceID,99,'~HTMLBox'); 
  DoOSMMap($HTMLBoxID,trim($GEOlatitude),trim($GEOlongitude),$GEOentry);

  
  $ActionOK = GEOActions($GEOentry,trim($IPSName),trim($GEOname),$_POST);
  
  HTMLlogging($Parent,$GEOentry,$GEOdevice,$GEOname,$GEOdate,$IPSName,$ActionOK);
  
  CreateHTMLBoxWithMap($Parent,$IPSName,$ActionOK);      
      
?>
