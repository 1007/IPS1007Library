<?

  $remoteRepository = 'https://raw.github.com/1007/IPS1007Library/master';
  $component = 'Plugwise';

  IPSUtils_Include ("IPSModuleManager.class.php", "IPSLibrary::install::IPSModuleManager");
  $moduleManager = new IPSModuleManager($component,$remoteRepository);
  $moduleManager->LoadModule($remoteRepository); 


  $moduleManager->InstallModule($remoteRepository);


?>