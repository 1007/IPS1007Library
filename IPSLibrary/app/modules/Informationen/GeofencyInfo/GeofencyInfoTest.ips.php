<?php


	$now = date('c');
	
	$lon = rand(7500000,8500000)/1000000;
	$lat = rand(48500000,49500000)/1000000;

	
	
$data = array ('date' => $now ,
					'name' => 'GeofencyTestLocation' ,
					'longitude' => $lon,
					'latitude' => $lat,
					'id' => '1234567890',
					'entry' => '1',
					'device' => '0987654321');
					
$data = http_build_query($data);

$context = stream_context_create(array(
    'http' => array(
      'method'  => 'POST',
      'header'  => "Content-type: application/x-www-form-urlencoded",
      'content' => $data
    ),
  ));
$return = file_get_contents('http://localhost:82/user/GeofencyInfo/Geofency.php?IPSName=GeofencyTestDevice', false, $context);





?>
