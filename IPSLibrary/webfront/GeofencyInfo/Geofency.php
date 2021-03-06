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

  if ( isset( $_POST["radius"] ))    $GEOradius       =$_POST["radius"]  ;   else $GEOradius = ""; 
  if ( isset( $_POST["beaconUUID"])) $GEObeaconUUID   =$_POST["beaconUUID"]; else $GEObeaconUUID ="";
  if ( isset( $_POST["major"] ))     $GEOmajor        =$_POST["major"];      else $GEOmajor ="";
  if ( isset( $_POST["minor"] ))     $GEOminor        =$_POST["minor"];      else $GEOminor ="";
  if ( isset( $_POST["address"] ))   $GEOaddress      =$_POST["address"];    else $GEOaddress ="";
  
  
  $GEOlogStringAddress = str_replace(chr(10), ",", $GEOaddress);
  $GEOlogStringAddress = str_replace(chr(226), ",", $GEOlogStringAddress);
  $GEOlogStringAddress = str_replace(chr(128), "", $GEOlogStringAddress);
  $GEOlogStringAddress = str_replace(chr(142), "", $GEOlogStringAddress);

  $GEOlogStringAddress = formatAdresse($GEOlogStringAddress);
 
  $out = $IPSName.",".$GEOdate.",".$GEOname.",".$GEOid.",".$GEOlongitude.",".$GEOlatitude.",".$GEOentry.",".$GEOdevice;
  $out = $out . "," .$GEOradius .",".$GEObeaconUUID.",". $GEOmajor.",".$GEOminor.",".$GEOlogStringAddress;
  Geofencylogging(false,$out,'incoming.log');


  if ( $GEOentry == '1' )
    $GEOentry = true;
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
  $IDAction    = CreateVariable('Action'    ,3,$IDLocation,10);  
  $IDradius    = CreateVariable('Radius'    ,3,$IDLocation,11);  
  $IDaddress   = CreateVariable('Address'   ,3,$IDLocation,12);  
 

  SetValue($IDentry,$GEOentry);
  SetValue($IDlatitude,$GEOlatitude);
  SetValue($IDlongitude,$GEOlongitude);
  SetValue($IDlocID,$GEOid);
  SetValue($IDdevice,$GEOdevice);
  SetValue($IDradius,$GEOradius);
  SetValue($IDaddress,$GEOlogStringAddress);
  
  if ($GEOentry)
    {
    SetValue($IDankunftIPS,time());
    $t = strtotime($GEOdate);
    SetValue($IDankunftGEO,$t);
    SetValue($IDabfahrtGEO,0);    
    $loc = str_pad ( $GEOname, 15 , ' ' );     
    $out = "Ankunft: " . $IPSName . " - " . $loc  ;
    $richtung = "Ankunft";
   

    }
  else
    {
    SetValue($IDabfahrtIPS,time());
    $t = strtotime($GEOdate);
    SetValue($IDabfahrtGEO,$t);
    $loc = str_pad ( $GEOname, 15 , ' ' );     
    $out = "Abfahrt: " . $IPSName . " - " . $loc  ; ;
    $richtung = "Abfahrt";
    

    }
    
   
    
  Geofencylogging($Parent,$out,'geofency.log');

  $DeviceID = IPSUtil_ObjectIDByPath("Program.IPSLibrary.data.modules.Informationen.GeofencyInfo.".$IPSName);
  $HTMLBoxID = CreateVariable('GoogleMap'  ,3,$DeviceID,99,'~HTMLBox'); 
  DoGoogleMaps($HTMLBoxID,trim($GEOlatitude),trim($GEOlongitude));

  $HTMLBoxID = CreateVariable('OSMMap'  ,3,$DeviceID,99,'~HTMLBox'); 
  
  DoOSMMap($HTMLBoxID,trim($GEOlatitude),trim($GEOlongitude),$GEOentry,$GEOradius);
  
  $ActionOK = GEOActions($GEOentry,trim($IPSName),trim($GEOname),$_POST);

  $OldActionValue = GetValue($IDAction);
  $OldActionValues = explode(',',$OldActionValue);
  
  if ($GEOentry)
    $OldActionValues[0] = $ActionOK;
  else
    $OldActionValues[1] = $ActionOK;

  if ( !isset($OldActionValues[0]) )
    $OldActionValues[0] = '1';
  if ( !isset($OldActionValues[1]) )
    $OldActionValues[1] = '1';
        
  $ActionValues = $OldActionValues[0] . "," . $OldActionValues[1];      
  SetValue($IDAction,$ActionValues);
  
  HTMLlogging($Parent,$GEOentry,$GEOdevice,$GEOname,$GEOdate,$IPSName,$ActionOK);
  
  //CreateHTMLBoxWithMap($Parent,$IPSName,$ActionOK);
 
  $out = ";".$IPSName.";".$GEOname.";".$richtung.";".$ActionOK; 
  Geofencylogging($Parent,$out,'Device_'.$IPSName.'.log');

  RefreshHTMLBoxWithMap($IPSName);

function formatAdresse($adress)
  {
	$ad = explode(",",$adress);
	
	$adress = "";
	foreach($ad as $a)
		$adress = $adress .trim($a).",";
	$adress = substr($adress,0,-1);
	
	$ad = explode(",",$adress);
	
	$a = explode(" ",$ad[0]);
	
	$counter = 0;
	$match = false;
	foreach($a as $x)
	   {
	   if ( @is_numeric($x[0]) )
	      $match = $counter;
	   $counter = $counter + 1;
	   }

	$target = "";
	if( $match != false )
		{
		$counter = 0;
		foreach($a as $x)
	   	{
	   	if ( $counter == $match )
				$target = $target .",".$x ;
			else
				$target = $target ." ".$x ;
			$counter = $counter + 1;
			}
		$ad[0] = trim($target);

		}

	$ad1 = implode(",",$ad);
  
  return $ad1;  
  }              
?>
