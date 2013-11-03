Generates a list of Zend Framework 2 components used by a project.  

In your `composer.json` file instead of using `"zendframework/zendframework" : "2.*"` and so requiring the whole framework,
use this script to get a list of used components, and require only those. Better yet, specify your composer file and the 
script will replace your `"zendframework/zendframework" : "2.*"` with the components used by your application.

For more info on why you should do this in your module/application read 
[this blog post](http://www.michaelgallego.fr/blog/2013/01/21/some-tips-to-write-better-zend-framework-2-modules/#only-set-dependencies-on-what-you-require).

#### Installation

1. Add this package to your `composer.json` file, in the `require-dev` section
```json
    {
        "require-dev": {
            "robertboloc/zf2-components-list-generator": "dev-master"
        }
    }
```

2. Run `composer update`

This will install the script into `vendor/bin/zf2_components_list_generator.php`

#### Usage

Execute the CLI script providing some/all of the following options : 

**--help | -h** Get usage information.  
**--project | -p** Path of the project to be scanned.  
**--composer | -c** Path to the composer.json file to be updated. If not specified the output will be printed to the standard output.

For example calling the script using only the `-p` option:
```php
php vendor/bin/zf2_components_list_generator.php -p /projects/MyProject/
```

Will output something like:
```php
Replace "zendframework/zendframework" in your composer.json file with :
"zendframework/zend-mvc": "2.*",
"zendframework/zend-form": "2.*",
"zendframework/zend-db": "2.*",
"zendframework/zend-inputfilter": "2.*",
"zendframework/zend-view": "2.*",
"zendframework/zend-servicemanager": "2.*",
"zendframework/zend-loader": "2.*",
"zendframework/zend-stdlib": "2.*"
```
You can copy and paste this information directly into your composer file.  
If the `-c` option was used, the composer file will be updated automatically and the message will be :
```php
/projects/MyProject/composer.json updated
```