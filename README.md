# ShellExtensionBundle 

The ShellExtensionBundle extends the symfony 2 shell with a php cli and som additional commands.

## Installation

### Add to deps file

	[ShellExtensionBundle]
         git=https://github.com/hanken/FRNK-ShellExtensionBundle
         target=/bundles/FRNK/ShellExtensionBundle
### update vendors
   run
	 php bin/vendors install

   to download and install the CliBundle

### edit autoload

   add the following to your app/autoload.php

     # .../Symfony/app/autoload.php
	$loader->registerNamespaces(array(
	//....
   	 'FRNK' => __DIR__.'/../vendor/bundles',
   	//... 
	));     


### Application Kernel

Add CliBundle to the `registerBundles()` method of your application kernel:

    public function registerBundles()
    {
        return array(
		//....
            new FRNK\ShellExtensionBundle\FRNKShellExtensionBundle(),
        );
    }

## edit app/console
   
   change app/console to load the FRNK\ShellExtensionBundle\Application instead of the standart Symfony one.
   
   `....
   //use Symfony\Bundle\FrameworkBundle\Console\Application;
   use FRNK\ShellExtensionBundle\Console\Application;
   use Symfony\Component\Console\Input\ArgvInput;
   ....`


