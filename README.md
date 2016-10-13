# Common Libs - Storage  [![GitHub release](https://img.shields.io/github/release/common-libs/storage.svg?maxAge=2592001&style=flat-square)](https://github.com/common-libs/storage) -  [![Packagist](https://img.shields.io/packagist/dt/common-libs/storage.svg?maxAge=2592001&style=flat-square)](https://packagist.org/packages/common-libs/storage) [![license](https://img.shields.io/github/license/common-libs/storage.svg?maxAge=2592000&style=flat-square)](https://github.com/common-libs/storage)

A php lib for storing data to files. The storage class can be used for working with own config files. So for example your php project uses a database like mysql and you want to store the creditails in an config file. By using this storage class its done withhin secounds.


## Installation

###Composer

`composer require common-libs/storage`

In php do: ```require_once("vendor/autoload.php");```.
###Without Composer

```php
 spl_autoload_register(function($class) {
    $prefix = 'profenter\\tools\\storage\\';

    if ( ! substr($class, 0, 14) === $prefix) {
        return;
    }

    $class = substr($class, strlen($prefix));
    $location = __DIR__ . 'path/to/this/dir/src/' . str_replace('\\', '/', $class) . '.php';

    if (is_file($location)) {
        require_once($location);
    }
});
```

## Use it


### Basic Usage

Create a new class `myConfigClass` and extend it to `libs\storage\storage`:
```php
<?php
use libs\storage\storage;

class myConfigClass extends storage
{
}
```
Now create `myConfigClass::setConfig()` and setup the config file:
```php
<?php
use libs\storage\storage;
use libs\storage\file;

class myConfigClass extends storage
{
	/**
	 * @return Closure
	 */
	public static function setConfig()
	{
		return function (file $options) {
			$options->setCreateIfNotExists(true);
			$options->setPath("path/to/config/file.extention");
			$options->setCache(true);

			return $options;
		};
	}
}
```
Maybe you already noticed that we haven't set the file format. You do it by importing the correct driver (using traits):
```php
<?php
use libs\storage\storage;
use libs\storage\file;
use libs\storage\json;

class myConfigClass extends storage
{
	use json;
	/**
	 * @return Closure
	 */
	public static function setConfig()
	{
		return function (file $options) {
			$options->setCreateIfNotExists(true);
			$options->setPath("path/to/config/file.extention");
			$options->setCache(true);

			return $options;
		};
	}
}
```
Supported formats: 
libs\storage\

 - ini
 - json
 - serialize
 - xml
 - yaml

----------

You are now ready to use it:
```php
<?php
require_once("./vendor/autoload.php");

$config = new myConfigClass();
$config->foo = "bar";
echo $config;
// ----------------------OR ----------------------//
myConfigClass::init()->foo = "bar";
echo myConfigClass::init();
```

For more details see [examples](https://github.com/common-libs/storage/blob/master/examples/storage) or [wiki](https://github.com/common-libs/storage/wiki/Storage:Overview)
