# php-tools - v0.1.6
A php lib of often used functions and classes

## installation
 `composer require profenter/tools`
 
packagist.org: [profenter/tools](https://packagist.org/packages/profenter/tools)
## use it

Include  ```vendor/autoload.php```.
 
You are now able to use this lib using the methods in `\profenter\tools`:
- [directory](#directory)
- file
- config
- common
- url
 
### directory
Examples:
#### simple
```php
use \profenter\tools\directory;
try {
	$dir = directory::init("/tmp/test/vendor/profenter")
	                ->get() // fetch dirs
	                ->asArray(); // return as array
} catch( \Exception $e ) {
	die( $e->getMessage() );
}
```
```php
Array
 (
     [tools] => Array
         (
             [__base_val] => /tools
             [composer.json] => /tools/composer.json
             [.gitmodules] => /tools/.gitmodules
             [.gitignore] => /tools/.gitignore
             [README.md] => /tools/README.md
         )
 
 )
```
#### clean up ->parse()
firest add ignored files
```php
use \profenter\tools\directory;
try {
	$dir = directory::init("/tmp/test/vendor/profenter")
	                ->get() // fetch dirs
	                ->ignore([".git*"])
	                ->parse() //remove hidden path 
	                ->asArray(); // return as array
} catch( \Exception $e ) {
	die( $e->getMessage() );
}
```
```php
Array
 (
     [tools] => Array
         (
             [__base_val] => /tools
             [composer.json] => /tools/composer.json
             [README.md] => /tools/README.md
         )
 
 )
```
#### add infos ->addInfos()
```php
use \profenter\tools\directory;
try {
	$dir = directory::init("/tmp/test/vendor/profenter")
	                ->get() // fetch dirs
	                ->ignore([".git*"])
	                ->parse() //remove hidden path
	                ->addInfos() //add infos like md5,sha1,size
	                ->asArray(); // return as array
} catch( \Exception $e ) {
	die( $e->getMessage() );
}
```
```php
Array
 (
     [tools] => Array
         (
             [time] => ---
             [path] => /tmp/test/vendor/profenter//tools
             [url] => http://localhost:63342/tools/test.php?dir=%2Fsimpledirlister
             [type] => dir
             [data] => Array
                 (
                     [__base_val] => Array
                         (
                             [time] => ---
                             [path] => /tmp/test/vendor/profenter//tools
                             [url] => http://localhost:63342/tools/test.php?dir=%tools
                             [type] => dir
                             [data] => Array
                                 (
                                 )
 
                             [json] => []
                         )
 
                     [composer.json] => Array
                         (
                             [path] => /tmp/test/vendor/profenter//tools/composer.json
                             [url] => http://localhost:63342/tools/test.php/tools/composer.json
                             [time] => 2016-02-21 22:26:26
                             [type] => file
                             [extension] => json
                             [size] => 756
                             [json] => {"size":"756","md5":"1583443cd3d27452228abc5810d09389","sha1":"a573bed6ea03a6b16bb7e9fb143d217e033739aa"}
                         )
 
                     [README.md] => Array
                         (
                             [path] => /tmp/test/vendor/profenter//tools/README.md
                             [url] => http://localhost:63342/tools/test.php/tools/README.md
                             [time] => 2016-02-21 22:26:26
                             [type] => file
                             [extension] => md
                             [size] => 389
                             [json] => {"size":"389","md5":"e7e8c7774933b2dd89287f180a6324f6","sha1":"ec7a63f88faec26c6987aca4d4a9912843bf0784"}
                         )
 
                 )
 
             [json] => []
         )
 
 )
```
#### change location ->cd()

```php
use \profenter\tools\directory;
try {
	$dir = directory::init( "/tmp/test/vendor/" )
	                ->get();
	print_r( $dir->asArray() ); // get content of complete dir
	print_r( $dir->cd( "../example-dir/" )// get content of "../example-dir/"
	             ->asArray() );
} catch( \Exception $e ) {
	die( $e->getMessage() );
}
```
#### find directorys and files ->find()

```php
use \profenter\tools\directory;
try {
	$dir = directory::init( "/tmp/test/vendor/" )
	                ->get()
	                ->find( "*.json" )// just get json files
	                ->asArray();
} catch( \Exception $e ) {
	die( $e->getMessage() );
}
```
