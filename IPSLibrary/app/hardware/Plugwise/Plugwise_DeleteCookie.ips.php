<?php

  	
		$childrenIds = IPS_GetChildrenIDs(0);
		foreach ($childrenIds as $childrenId) {
		   $object     = IPS_GetObject($childrenId);
		   $objectType = $object['ObjectType'];
		   if ($objectType==1 /*Instance*/) {
		      $instance= IPS_GetInstance($childrenId);
		      if ($instance['ModuleInfo']['ModuleID'] == '{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}') {
		        
		        $cookie = "webFront".$childrenId."state"; 
		        
		        setcookie ($cookie, "", time() - 3600);
		        
		      }
		   }
		}
	
  
 
  
  

?>
