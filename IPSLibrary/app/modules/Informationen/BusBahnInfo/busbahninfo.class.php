<?
/***************************************************************************//**
* @ingroup busbahninfo
* @{
* @defgroup busbahninfoclass BusBahnInfo API
* @{
* @file          busbahninfo.class.php
* @author        Frederik Granna (sysrun)
* @version       0.1
*
* @brief Bus und Bahn API
*
* @class bahn
********************************************************************************/

    class bahn{
          	var $_BASEURL ="http://reiseauskunft.bahn.de/bin/bhftafel.exe/dn?maxJourneys=20&";  // aktuelle Daten ohne Ziel
          	var $_BASEURLZ="http://reiseauskunft.bahn.de/bin/query.exe/dn?maxJourneys=20&";     // aktuelle Daten mit Ziel

          	var $_PARAMS	= array();
          	var $timetable	= array();
          	var $bahnhof	= false;
          	var $noresult	= "";
          	var $_FETCHMETHOD;
          	function __construct($bahnhof=null)
              {
              	$this->_init($bahnhof);
              	$this->fetchMethodCURL(true);
              }

    			function Type($type='Abfahrt')      {$this->boardType($type);}

    			function TypeICE($state=true)     	{$this->_PARAMS['GUIREQProduct_0'] = ($state) ? "on" : false;}
    			function TypeIC($state=true)      	{$this->_PARAMS['GUIREQProduct_1'] = ($state) ? "on" : false;}
    			function TypeIR($state=true)      	{$this->_PARAMS['GUIREQProduct_2'] = ($state) ? "on" : false;}
    			function TypeRE($state=true)      	{$this->_PARAMS['GUIREQProduct_3'] = ($state) ? "on" : false;}
    			function TypeSBAHN($state=true)	  	{$this->_PARAMS['GUIREQProduct_4'] = ($state) ? "on" : false;}
    			function TypeBUS($state=true)     	{$this->_PARAMS['GUIREQProduct_5'] = ($state) ? "on" : false;}
    			function TypeFAEHRE($state=true)  	{$this->_PARAMS['GUIREQProduct_6'] = ($state) ? "on" : false;}
    			function TypeUBAHN($state=true)	  	{$this->_PARAMS['GUIREQProduct_7'] = ($state) ? "on" : false;}
    			function TypeTRAM($state=true)		{$this->_PARAMS['GUIREQProduct_8'] = ($state) ? "on" : false;}


/***************************************************************************//**
* 
*******************************************************************************/
function boardType($type)
    {
    $type = strtolower($type);
    if($type=="ankunft")
      $this->_PARAMS['boardType']="arr";
    if($type=="abfahrt")
      $this->_PARAMS['boardType']="dep";
    }


/***************************************************************************//**
* 
*******************************************************************************/
function datum($datum)
    {
    $this->_PARAMS['date']=$datum;
    } 


/***************************************************************************//**
*
*******************************************************************************/
function ziel($ziel)
    {
    
    $this->_ZIEL['ziel']=$ziel;
    }


/***************************************************************************//**
* 
*******************************************************************************/
function zeit($zeit)
    {
    $this->_PARAMS['time']=$zeit;
    }


/***************************************************************************//**
* 
*******************************************************************************/
function fetch($proxy,$counter)
	{
   if($this->_FETCHMETHOD=="CURL")
   	{
      return $this->_queryCurl($proxy,$counter);
      }
    }


/***************************************************************************//**
* 
*******************************************************************************/
function _queryCurl($proxy,$counter)
    {
    $this->buildQueryURL();
    $result=$this->_call($proxy);
	 if ( !$result )
	 	return false ;
	
    return $this->_parse($result,$counter);
    }


/***************************************************************************//**
* 
*******************************************************************************/
function buildQueryURL()
	{

	 // Kein Ziel angegeben also
	 // bhftafel.exe
	if ( $this->_ZIEL == false )
		{
    	$fields_string="";
    	foreach($this->_PARAMS as $key=>$value)
        {
        if($value)
          $fields_string .= $key.'='.urlencode($value).'&';
        };
    	rtrim($fields_string,'&');

    	$this->_URL=$this->_BASEURL.$fields_string;
    	logging($this->_URL);
    	return $this->_URL;
		}
	// query.exe
	else
	   {
	   //S={%Start|iso-8859-1}&Z={%Ziel|iso-8859-1}&T={%Zeit|iso-8859-1}&start=1
	   $startb = urlencode($this->_PARAMS['input']);
		$zielb  = urlencode($this->_ZIEL['ziel']);
	   $this->_URL=$this->_BASEURLZ."S=".$startb."&Z=".$zielb."&start=true";
	   logging($this->_URL);
	   return $this->_URL;
	   }
	   
    }


/***************************************************************************//**
* 
*******************************************************************************/
function _parse($data,$counter)
    {
	 GLOBAL $debug;
	 
	 
	 
    libxml_use_internal_errors(true);
    
    $dom = new DOMDocument();
    $err = $dom->loadHTML($data);

	 if ( $debug )
	   {
    	$ordner = IPS_GetKernelDir() . "logs\\BusBahnInfo";
    	$file = $ordner . "\\".$counter.".html";
    	$dom->saveHTMLFile($file);
      }
    
    $errors = libxml_get_errors();

    $select=$dom->getElementById("rplc0");
    
    if ( $select == NULL )
      {
      logging("ElementID rplc0 nicht gefunden");
      return false;
      
      }

    
    if(@$select->tagName=="select")
      {
      $options=$select->getElementsByTagName("option");
      foreach($options AS $op)
          {
          $sss = "<br>" . utf8_decode($op->getAttribute("value")." - ".$op->nodeValue);
          
          $this->noresult = $this->noresult . $sss;
          }
      return false;
      }
    else
      {
		$att = false;
		$att = @$select->getAttribute("value");
		
		$this->bahnhof=utf8_decode($att);
		//$this->bahnhof=($att);
		$this->_process_dom($dom);
      return true;
		
      }
    }

/***************************************************************************//**
* 
*******************************************************************************/
function _process_dom($dom)
    {
    $test=$dom->getElementById("sqResult")->getElementsByTagName("tr");
	
    $data=array();

    foreach($test as $k=>$t)
		{
      $tds=$t->getElementsByTagName("td");
		
		foreach($tds AS $td)
			{
         $dtype=$td->getAttribute("class");

			switch($dtype)
				{
            case 'train':
                        	if($a=$td->getElementsByTagName("a")->item(0))
										{
                              $data[$k]['train']=str_replace(" ","",$a->nodeValue);
                              if($img=$a->getElementsByTagName("img")->item(0))
											{
                                 if (preg_match('%/([a-z]*)_%', $img->getAttribute("src"), $regs))
												{
                                    switch($regs[1])
													{
                                       case 'EC':
                                             		$data[$k]['type']="IC";
                                          			break;
                                       default:
                                                	$data[$k]['type']=strtoupper($regs[1]);
                                            			break;
                                       }
                                 	}
                              	}
                            	}

                        	break;

				case 'route':
                     		if($span=@$td->getElementsByTagName("span")->item(0))
										{
                              $data[$k]['route_ziel'] = (trim($span->nodeValue));
                            	}
								   else break;
									
                           $tmp=array();
                           
									$td->nodeValue = trim($td->nodeValue);
									
                           $route=explode( "\n",$td->nodeValue);
									array_splice($route,0,7);
									$count = count($route);
									

									if ( $count )
									   {
									   $yy = 0;
										for ( $x=0;$x<$count;$x=$x+3)
									   	{
									   	
									   	$zwischenhalt = "?";
									   	$zwischenhalt = @$route[$x+1] . " - " .$route[$x];
									   	$data[$k]['route'][$yy] = utf8_decode($zwischenhalt);
									   	$yy++;
									   	}
										}
								
                        	break;

				case 'time':    

				case 'platform':    

				case 'ris':
                        	$data[$k][$dtype]=$td->nodeValue;
                        	break;


                    }
                    
                }
            }



	foreach($data AS $d)
		{
      if(array_key_exists("train",$d))
			{
         foreach($d AS $dk=>$dv)
         	if(!is_array($dv))
            	$d[$dk]=ltrim(str_replace("\n","",utf8_decode(trim(html_entity_decode($dv)))),"-");

            $d['route_start']=$this->bahnhof;
            
            $this->timetable[]=$d;
			}
      }

	}

    
    
/***************************************************************************//**
* 
*******************************************************************************/
function fetchMethodCURL($state)
    {
    if($state)
      {
      $this->_FETCHMETHOD="CURL";
      }
    else
      {
      $this->_FETCHMETHOD="OTHER";
      }
    }



/***************************************************************************//**
* 
*******************************************************************************/
function _call($proxy)
    {
     
    $this->_CH = curl_init();

    if ( $proxy != '' )
      {                                             
      curl_setopt($this->_CH, CURLOPT_HTTPPROXYTUNNEL, 1);
      curl_setopt($this->_CH, CURLOPT_PROXY, $proxy);
      }

	 curl_setopt($this->_CH, CURLOPT_CONNECTTIMEOUT, 10);
	 curl_setopt($this->_CH, CURLOPT_TIMEOUT, 60);

    curl_setopt($this->_CH,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($this->_CH,CURLOPT_URL,$this->_URL);
    $result = @curl_exec($this->_CH);
	if ( $result == false )
	   IPS_Logmessage(basename(__FILE__),"BusbahnInfo nicht erreichbar");
	   
    curl_close($this->_CH);
    return $result;
    }


/***************************************************************************//**
* 
*******************************************************************************/
function _init($bahnhof)
    {
    $this->_ZIEL=false;
    $this->_PARAMS=array
        (
        'country'=>'DEU',                   // Deutschland
        'rt'=>1,
        'GUIREQProduct_0'=>'on',            // ICE
        'GUIREQProduct_1'=>'on',            // Intercity- und Eurocityzüge
        'GUIREQProduct_2'=>'on',            // Interregio- und Schnellzüge
        'GUIREQProduct_3'=>'on',            // Nahverkehr, sonstige Züge
        'GUIREQProduct_4'=>'on',            // S-Bahn
        'GUIREQProduct_5'=>'on',            // BUS
        'GUIREQProduct_6'=>'on',            // Schiffe
        'GUIREQProduct_7'=>'on',            // U-Bahn
        'GUIREQProduct_8'=>'on',            // Strassenbahn
        'REQ0JourneyStopsSID'=>'',
        'REQTrain_name'=>'',
 //       'REQTrain_name_filterSelf'=>'1',
        'advancedProductMode'=>'',
        'boardType'=>'dep',                 // dep oder arr
        'date'=>date("d.m.y"),
        'input'=>$bahnhof,
        'start'=>'Suchen',
        'time'=>date("H:i")
        );
        
		
    }

}


function display_xml_error($error, $xml="")
{
	$return = "";
	
    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: ";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: ";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: ";
            break;
    }

    $return .= trim($error->message) .
               "\n  Line: $error->line" .
               "\n  Column: $error->column";

    if ($error->file) {
        $return .= "\n  File: $error->file";
    }

    return "$return\n\n--------------------------------------------\n\n";
}


/***************************************************************************//**
*	Logging
*******************************************************************************/
function logging($text,$file = 'busbahninfo.log' ,$space = false)
	{
	
	if ( !LOG_MODE )
		return;

	$ordner = IPS_GetKernelDir() . "logs\\BusBahnInfo";
   if ( !is_dir ( $ordner ) )
		mkdir($ordner);

   if ( !is_dir ( $ordner ) )
	   return;



	$time = date("d.m.Y H:i:s",time());
	$logdatei = IPS_GetKernelDir() . "logs\\BusBahnInfo\\" . $file;
	$datei = fopen($logdatei,"a+");
	if ( $space )
		fwrite($datei, $text . chr(13));
	else
		fwrite($datei, $time ." ". $text . chr(13));

	fclose($datei);

	}
/***************************************************************************//**
* @}
* @}
*******************************************************************************/


?>