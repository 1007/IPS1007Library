<?php
 	IPSUtils_Include ("IPSLogger.inc.php","IPSLibrary::app::core::IPSLogger");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");

	$HighchartsPath    = "Visualization.WebFront.Hardware.Plugwise.Highcharts";
	$HighchartsId      = get_ObjectIDByPath($HighchartsPath);

  $debug = false;
  
  IPS_Sleep(50);
  
  if 	(isset($_GET['VarID']))     $varid      = intval($_GET['VarID']); else die() ;
  if 	(isset($_GET['Time']))      $time       = $_GET['Time']; else die() ;
  if 	(isset($_GET['Start']))     $starttime  = intval($_GET['Start']); else die() ;
  if 	(isset($_GET['End']))       $endtime    = intval($_GET['End']); else die() ;

  $starttime_string = date('d.m.Y h:i:s',$starttime);
  $endtime_string   = date('d.m.Y h:i:s',$endtime);

  
  $s = $varid."-".$time ."-".$starttime_string."-".$endtime_string;
	
  if ($debug) IPSLogger_Dbg(__FILE__,$s );

  $CfgDaten = array();

  $CfgDaten['RefreshID'] = $varid;
   
  $akt_zeitraum = $endtime - $starttime ;     // aktueller Zeitraum
  $sprungweite  = $akt_zeitraum/2; 
  
  $now = false;   // Automatisch updaten HC
  
  switch($time)
    {
    case "Backward" : 
                      $CfgDaten['StartTime'] =  $starttime - $sprungweite;
                      $CfgDaten['EndTime']   =  $endtime   - $sprungweite;
                      
                      break;
    case "Home"     : 
                      $CfgDaten['StartTime'] =  0;
                      $CfgDaten['EndTime']   =  0;
                      $now = true;
                      
                      break;
    case "Forward"  : 
                      $CfgDaten['StartTime'] =  $starttime + $sprungweite;
                      $CfgDaten['EndTime']   =  $endtime   + $sprungweite;
                      if ( $CfgDaten['EndTime'] > time() )
                        { 
                        //unset( $CfgDaten['StartTime'] );
                        //unset( $CfgDaten['EndTime'] );
                        die();
                        }
                        
                      break;
    case "Hour"     :
                      $CfgDaten['StartTime'] =  time() - 3600;
                      $CfgDaten['EndTime']   =  time() ;
                      break;
    case "Day"     : 
                      $CfgDaten['StartTime'] =  time() - ( 3600 * 24 );
                      $CfgDaten['EndTime']   =  time() ;

                      break;
    case "Week"     : 
                      $CfgDaten['StartTime'] =  time() - ( 3600 * 24 * 7 );
                      $CfgDaten['EndTime']   =  time() ;
    
                      break;
    case "Month"     : 
                      $CfgDaten['StartTime'] =  time() - ( 3600 * 24 * 31);
                      $CfgDaten['EndTime']   =  time() ;

                      break;
    case "Year"     : 
                      $CfgDaten['StartTime'] =  time() - ( 3600 * 24 * 365);
                      $CfgDaten['EndTime']   =  time() ;
    
                      break;

    
    default         : die();      
    }

  $startid = IPS_GetVariableIDByName('StartTime',$HighchartsId);
	$endeid  = IPS_GetVariableIDByName('EndTime',$HighchartsId);
	$nowid   = IPS_GetVariableIDByName('Now',$HighchartsId);
  SetValue($nowid,$now);
  SetValue($startid,$CfgDaten['StartTime']);
  SetValue($endeid,$CfgDaten['EndTime']);

  
  $scriptid = IPSUtil_ObjectIDByPath("Program.IPSLibrary.app.hardware.Plugwise.Plugwise_Config_Highcharts");

	$s = IPS_GetScript($scriptid); 
	$s = IPS_GetKernelDir() ."scripts/" .  $s['ScriptFile'];

	include($s);

?>