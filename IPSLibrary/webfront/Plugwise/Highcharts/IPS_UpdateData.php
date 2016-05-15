<?php
    IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");

	 $VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.DATA1";
   $IdData1   		= @get_ObjectIDByPath($VisuPath,true);
	 $VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.DATA2";
   $IdData2   		= @get_ObjectIDByPath($VisuPath,true);

  if ( !$IdData1 )
    return;
  if ( !$IdData2 )
    return;

	$HighchartsPath    = "Visualization.WebFront.Hardware.Plugwise.Highcharts";
	$HighchartsId      = get_ObjectIDByPath($HighchartsPath);
    
    if(!isset($_GET['Request'])) return false;
    
    $request = $_GET['Request'] ;
    
    //IPS_logMessage("....",$request);
    
    $iScriptId = isset($_GET['scriptId']) ? (int)$_GET['scriptId'] : false; 

    if(!isset($_GET['lastTimeStamp'])) 
      {
      $startTime = time() - 60 * 60 * 2;
      } 
    else 
      {
      $startTime = $_GET['lastTimeStamp'];
      $startTime = substr($startTime,0,9);
       
      $startTime = intval($startTime);
      }
    $endTime = time();

  //IPS_logMessage("-----",$_GET['lastTimeStamp']);
  //IPS_logMessage("-----",$startTime);
  
  if ( $request == "HC" )
    {           
    	$nowid   = IPS_GetVariableIDByName('Now',$HighchartsId);
      $now = GetValue($nowid);
      if ( $now == false )
        return;
    // ScriptId wurde übergeben -> aktuelle Daten werden geholt
    if ($iScriptId != false) 
      {
        // Id des Config Scripts
        $ConfigScript = IPS_GetScript($iScriptId);
        include_once(IPS_GetKernelDir() . "scripts/" .$ConfigScript['ScriptFile']);
        global $instances;
        
        $data = getLoggedData($CfgDaten['series'], $instances[0], $startTime, $endTime);
        
        //IPS_logMessage("....",$request."-");
        header("Content-type: text/json");
        echo json_encode($data);
    }
      }

  if ( $request == "DATA1DATA2" )
    {
    $childs = IPS_GetChildrenIDs($IdData1);
    $s1 = GetValue($childs[0]);
    $childs = IPS_GetChildrenIDs($IdData2);
    $s2 = GetValue($childs[0]);

    $html = "";
    $html = $html . "<table  width='100%'>";
    $html = $html . "<tr>";
    $html = $html . "<td width='50%'>" . $s1;
    $html = $html . "</td>";
    $html = $html . "<td width='50%'>" . $s2;
    $html = $html . "</td>";


    $html = $html . "</tr>";    
    $html = $html . "</table>";

    //echo $html;
    }

    
function getLoggedData($Series, $id_AH, $startTime, $endTime) 
  {
  $ret = array();
  
  foreach ($Series as $Serie) {
  
            $logEntries = AC_GetLoggedValues($id_AH, intval($Serie['Id']), $startTime, $endTime, 0 );

              $startTime = date("l jS \of F Y h:i:s A",$startTime);
              $endTime = date("l jS \of F Y h:i:s A",$endTime);

              //IPS_logMessage("....",$startTime."-".$endTime);    


            if ($logEntries )
              foreach($logEntries as $logEntry) {
                  $ret[] = array(CreateDateUTC($logEntry['TimeStamp']), $logEntry['Value']);
                  }
            //else
              //IPS_logMessage("....","NADA");    
            
        }
        return $ret;
    }
    
?>
