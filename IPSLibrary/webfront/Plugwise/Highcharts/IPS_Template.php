<?php 

	IPSUtils_Include ("IPSInstaller.inc.php",          "IPSLibrary::install::IPSInstaller");
	IPSUtils_Include ("Plugwise_Configuration.inc.php","IPSLibrary::config::hardware::Plugwise");

	// ab IPS-Highcharts Script V2.0000		 
	// 07.05.2012 	geändert auf jquery/1.7.2
	
	if (!isset($_GET['CfgFile'])) 
      { 
       $CfgFile = false; 
      } 
   else 
      { 
       $CfgFile = $_GET['CfgFile']; 
      } 

   if (!isset($_GET['ScriptId'])) 
      { 
       $iScriptId = false; 
      } 
   else 
      { 
       $iScriptId = (int)$_GET['ScriptId']; 
      } 

	
	// ScriptId wurde übergeben -> aktuelle Daten werden geholt
	if ($iScriptId != false)
	{
		$ConfigScript=IPS_GetScript($iScriptId);      // Id des Config Scripts
	
		include_once(IPS_GetKernelDir() . "scripts\\" .$ConfigScript['ScriptFile']);
		global $sConfig;
		//$sConfig = IPS_RunScriptWait($iScriptId);
		$s = utf8_encode($sConfig);	
		
	}
	// Filename würde übergeben -> Daten aus Datei lesen
	else if ($CfgFile != false)
	{
		// prüfen ob übergeben Datei existiert
		if (!file_exists($CfgFile))
		{
			echo "Datei '$CfgFile' nicht vorhanden!!!";
			return;
		}

		// file vorhanden -> einlesen
		$handle = fopen($CfgFile,"r");
		$s ="";
		while (!feof($handle))
		{
			$s .= fgets($handle);
		}
		fclose($handle);
		$s = utf8_encode($s);
	}
	else
	{
		echo "Achtung! Fehlerhafte Parameter CfgFile bzw ScriptId";
		return;
	}
	
	// Bereiche splitten -> erster Teil sind diverse Config Infos, zweiter Teik sind die Daten für Highcharts
	$s = explode("|||" , $s);
	
	if (count($s) >= 2)
	{
		$TempString = trim($s[0],"\n ");
		$JavaScriptConfigForHighchart = $s[1];

		$LangOptions="lang: {}";
		if (count($s) > 2)
			$LangOptions = trim($s[2],"\n ");
		
        if (count($s) > 3) {
            $lastTimeStamp = trim($s[3],"\n ");
        } else {
            $lastTimeStamp = 0;
        }
		
		// aus den Daten ein schönes Array machen
		$TempStringArr = explode("\n", $TempString);
		foreach($TempStringArr as $Item)
		{
			$KeyValue = explode("=>", $Item);
			$AdditionalConfigData[trim($KeyValue[0]," []")] = trim($KeyValue[1]," ");
		}
		
		// Verzeichnis + Theme
		if ($AdditionalConfigData['Theme'] != '')
			$AdditionalConfigData['Theme']= 'js/themes/' . $AdditionalConfigData['Theme'];

    if ( defined('HIGHCHARTS_THEME') )
      {
      $theme = HIGHCHARTS_THEME;
      $AdditionalConfigData['Theme']= 'js/themes/'.$theme;
      }


			
	}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Highcharts</title>
		
				<!-- 1. Add these JavaScript inclusions in the head of your page -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
		<!-- wenn lokal vorhanden .... <script type="text/javascript" src="jquery/1.7.2/jquery.js"></script> -->
		<script type="text/javascript" src="js/highcharts.js"></script>

		
		<!-- 1a) add a theme file -->
		<script type="text/javascript" src="<?php echo $AdditionalConfigData['Theme'] ?>"></script>
			
		<!-- 1b) the exporting module -->
		<script type="text/javascript" src="js/modules/exporting.js"></script>
		
		<!-- 2. Add the JavaScript to initialize the chart on document ready -->
		
		<script type="text/javascript">
            function renderData(points) {
                var series = chart.series[0];
                
                var renderLater = false;
                if(points.length > 10) {
                    renderLater = true;
                }
                $.map(points, function(point, idx) {
                    point[0] = eval(point[0]);
                    if(point[0] > lastTimeStamp) {
                        lastTimeStamp = point[0];
                        series.addPoint(point, !renderLater, true);
                    }
                });
                if(renderLater) {
                    chart.redraw();
                }
            }
            
            function requestData() {
                // online request data when the chart is already instantiated
                if(typeof chart !== "undefined")
                   {
                    $.ajax({
                        url: 'IPS_UpdateData.php',
                        data: {"scriptId": scriptId,
                                "Request": "HC",
                               "lastTimeStamp": lastTimeStamp
                              },
                        success: function(points) {
                            renderData(points);
                        },
                        cache: false
                    });
                    $.ajax({
                        url: 'IPS_UpdateData.php',
                        data: {"scriptId": scriptId,
                               "Request": "DATA1DATA2",
                               "lastTimeStamp": lastTimeStamp
                              },
                        success: function(data) { document.getElementById("data1data2").innerHTML = data;} ,
                        cache: false
                    });
                    
                    
                    setTimeout(requestData, 10000);
                    } 
                else 
                    {
                    setTimeout(requestData, 1000);
                    }
                
                
            }
            var chart, lastTimeStamp = <?php echo $lastTimeStamp; ?>;
            var scriptId = <?php echo $iScriptId; ?>;
			Highcharts.setOptions({<?php echo $LangOptions; ?>});
				
			$(document).ready(function() {
				chart = new Highcharts.Chart({<?php echo $JavaScriptConfigForHighchart; ?>});

		</script>
	</head>
		
	<body>
	
		<!-- 3. Add the container
	<table border="1" width=100% height=600px>	
	<tr height=210px><td> 
  <div id="data1data2" style="width: 100% height:200"></div> 
  </td>
  </tr>   -->
	<tr height=216px><td> 		
  <div id="container"  style="width: <?php echo $AdditionalConfigData['Width'] ?>; height: <?php echo $AdditionalConfigData['Height'] ?>; margin: 0 auto"></div>
  
  </td></tr>
  </table>

	</body>
</html>
