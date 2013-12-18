<?php
	/*
	 * This file is part of the IPSLibrary.
	 *
	 * The IPSLibrary is free software: you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published
	 * by the Free Software Foundation, either version 3 of the License, or
	 * (at your option) any later version.
	 *
	 * The IPSLibrary is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with the IPSLibrary. If not, see http://www.gnu.org/licenses/gpl.txt.
	 */

	/**@defgroup geofencyinfo GeofencyInfo
	 * @ingroup modules
	 * @{
	 *
	 * @file          GeofencyInfoTest.php
	 * @author        Juergen Gerharz
	 * @version
	 *  Version 1.0.0, 13.12.2013<br/>
	 *
	 * GeofencyInfo Test
	 *
	 */

	IPSUtils_Include("GeofencyInfo_Configuration.inc.php","IPSLibrary::config::modules::Informationen::GeofencyInfo");

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
    				),));

	$return = file_get_contents(TESTLOCALWEBSERVER.'user/GeofencyInfo/Geofency.php?IPSName=GeofencyTestDevice', false, $context);

	$data = array ('date' => $now ,
					'name' => 'GeofencyTestLocation' ,
					'longitude' => $lon,
					'latitude' => $lat,
					'id' => '1234567890',
					'entry' => '0',
					'device' => '0987654321');

	$data = http_build_query($data);

	$context = stream_context_create(array(
    				'http' => array(
      			'method'  => 'POST',
      			'header'  => "Content-type: application/x-www-form-urlencoded",
      			'content' => $data
    				),));

	$return = file_get_contents(TESTLOCALWEBSERVER.'user/GeofencyInfo/Geofency.php?IPSName=GeofencyTestDevice', false, $context);




?>
