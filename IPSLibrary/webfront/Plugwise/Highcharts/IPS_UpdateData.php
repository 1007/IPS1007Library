<?php
    IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
	IPSUtils_Include("IPSInstaller.inc.php",    "IPSLibrary::install::IPSInstaller");

	 $VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.DATA1";
   $IdData1   		= get_ObjectIDByPath($VisuPath);
	 $VisuPath  		= "Visualization.WebFront.Hardware.Plugwise.DATA2";
   $IdData2   		= get_ObjectIDByPath($VisuPath);

    
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
      $startTime = (int) $_GET['lastTimeStamp'];
      }
    $endTime = time();


  if ( $request == "HC" )
    {        
    // ScriptId wurde übergeben -> aktuelle Daten werden geholt
    if ($iScriptId != false) 
      {
        // Id des Config Scripts
        $ConfigScript = IPS_GetScript($iScriptId);
        include_once(IPS_GetKernelDir() . "scripts\\" .$ConfigScript['ScriptFile']);
        global $instances;
        
        $data = getLoggedData($CfgDaten['series'], $instances[0], $startTime, $endTime);
        
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

    echo $html;
    }

    
function getLoggedData($Series, $id_AH, $startTime, $endTime) 
  {
  $ret = array();
  
  foreach ($Series as $Serie) {
  
            $logEntries = AC_GetLoggedValues($id_AH, intval($Serie['Id']), $startTime, $endTime, 0 );
            if ($logEntries )
            foreach($logEntries as $logEntry) {
                $ret[] = array(CreateDateUTC($logEntry['TimeStamp']), $logEntry['Value']);
            }
        }
        return $ret;
    }
    
?>