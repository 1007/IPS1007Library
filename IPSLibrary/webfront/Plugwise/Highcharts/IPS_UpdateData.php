<?php
    IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");
    
    $iScriptId = isset($_GET['scriptId']) ? (int)$_GET['scriptId'] : false; 

    if(!isset($_GET['lastTimeStamp'])) {
        $startTime = time() - 60 * 60 * 2;
    } else {
        $startTime = (int) $_GET['lastTimeStamp'];
    }
    $endTime = time();
    
    
    function getLoggedData($Series, $id_AH, $startTime, $endTime) {
        $ret = array(); 
        foreach ($Series as $Serie) {   
            $logEntries = @AC_GetLoggedValues($id_AH, intval($Serie['Id']), $startTime, $endTime, 0 );
            foreach($logEntries as $logEntry) {
                $ret[] = array(CreateDateUTC($logEntry['TimeStamp']), $logEntry['Value']);
            }
        }
        return $ret;
    }
    
    // ScriptId wurde übergeben -> aktuelle Daten werden geholt
    if ($iScriptId != false) {
        // Id des Config Scripts
        $ConfigScript = IPS_GetScript($iScriptId);
        include_once(IPS_GetKernelDir() . "scripts\\" .$ConfigScript['ScriptFile']);
        global $instances;
        
        $data = getLoggedData($CfgDaten['series'], $instances[0], $startTime, $endTime);
        
        header("Content-type: text/json");
        echo json_encode($data);
    }
?>