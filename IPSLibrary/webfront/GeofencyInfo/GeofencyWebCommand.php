<?php


	IPSUtils_Include ("IPSLogger.inc.php","IPSLibrary::app::core::IPSLogger");
  IPSUtils_Include ("IPSInstaller.inc.php","IPSLibrary::install::IPSInstaller");

	IPSUtils_Include("GeofencyInfo.inc.php","IPSLibrary::app::modules::Informationen::GeofencyInfo");
	IPSUtils_Include("GeofencyInfo_Configuration.inc.php","IPSLibrary::config::modules::Informationen::GeofencyInfo");
 
  if ( isset( $_GET["Device"] ) ) $Device=$_GET["Device"] ;      else return;

  RefreshHTMLBoxWithMap($Device,true);
    

?>

