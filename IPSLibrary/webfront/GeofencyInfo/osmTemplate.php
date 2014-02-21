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

	/**@defgroup osmtemplate osmTemplate
	 * @ingroup modules
	 * @{
	 *
	 * @file          osmTemplate.php
	 * @author        Juergen Gerharz
	 * @version
	 *  Version 1.0.0, 19.12.2013<br/>
	 *
	 * GeofencyInfo Include
	 *
	 */

	IPSUtils_Include("GeofencyInfo_Configuration.inc.php","IPSLibrary::config::modules::Informationen::GeofencyInfo");
 	IPSUtils_Include ("IPSLogger.inc.php","IPSLibrary::app::core::IPSLogger");


  if (isset($_GET["zoom"]))   $zoomlevel = $_GET["zoom"];  else { IPSLogger_Dbg(__FILE__,"Kein Zoomlevel"); return; };
  if (isset($_GET["lon"]))    $longitude = $_GET["lon"];   else { IPSLogger_Dbg(__FILE__,"Kein Longitude"); return; };
  if (isset($_GET["lat"]))    $latitude  = $_GET["lat"];   else { IPSLogger_Dbg(__FILE__,"Kein Latitude");  return; };
  if (isset($_GET["entry"]))  $entry     = $_GET["entry"]; else { IPSLogger_Dbg(__FILE__,"Kein Entry");     return; };
  if (isset($_GET["radius"])) $radius    = $_GET["radius"];else { IPSLogger_Dbg(__FILE__,"Kein Radius"); $radius = 100;};
  
    
  
  if ( $entry == 1 )     
    $circleColor = "#00CC00" ;
  if ( $entry == 0 )     
    $circleColor = "#FF0000" ;
   


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de-de">
<!DOCTYPE HTML>
<html>
   <head>
     <title>OSM</title>
     <style type="text/css">
       html, body, #basicMap {
           width:  100%;
           height: 100%;
           margin: 0;
       }
     </style>
     <script src="OpenLayers/OpenLayers.js"></script>
     <script>
       function init() {
           var lat            = <?php echo $latitude;  ?>;
           var lon            = <?php echo $longitude; ?>;
           var zoom           = <?php echo $zoomlevel; ?>;
           var radius         = <?php echo $radius;    ?>;

          var circleStyleOnline = {
                         strokeColor: "<?php echo $circleColor; ?>",
                         fillColor: "<?php echo $circleColor; ?>",
                         fillOpacity: 0.3,
                         strokeWidth: 0,
                         strokeDashstyle: "solid",
                 };
          var circleStyleOffline = {
                         strokeColor: "<?php echo $circleColor; ?>",
                         fillColor: "<?php echo $circleColor; ?>",
                         fillOpacity: 0.5,
                         strokeWidth: 0,
                         strokeDashstyle: "solid",
                 };
                 

         map = new OpenLayers.Map("basicMap");
         var mapnik         = new OpenLayers.Layer.OSM();
         var fromProjection = new OpenLayers.Projection("EPSG:4326"); // Transform from WGS 1984
         var toProjection   = new OpenLayers.Projection("EPSG:900913");// to Spherical Mercator Projection
         var position       = new OpenLayers.LonLat(lon,lat).transform(fromProjection, toProjection);
         var vectorLayer    = new OpenLayers.Layer.Vector("Overlay");

 
         var Point  = new OpenLayers.Geometry.Point( position.lon,position.lat );
         var Circle = OpenLayers.Geometry.Polygon.createRegularPolygon(Point, radius, 50, 0 );
         var addCircul = new OpenLayers.Feature.Vector(Circle, null,circleStyleOffline);
         vectorLayer.addFeatures([ addCircul ]);
 
         map.addLayer(vectorLayer);


         map.addLayer(mapnik);
         map.setCenter(position, zoom );
       }
     </script>
   </head>
   <body onload="init();">
     <div id="basicMap"></div>
   </body>
</html> 