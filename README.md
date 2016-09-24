# php-tools - v0.2.0 -  [![Packagist](https://img.shields.io/packagist/dt/profenter/tools.svg?maxAge=2592000)](https://packagist.org/packages/profenter/tools) [![GitHub release](https://img.shields.io/github/release/profenter/php-tools.svg?maxAge=2592000)](https://github.com/profenter/php-tools) [![license](https://img.shields.io/github/license/profenter/php-tools.svg?maxAge=2592000?style=flat-square)](https://github.com/profenter/php-tools)
A php lib of often used functions and classes

## Installation

###Composer

`composer require profenter/tools`

In php do: ```require_once("vendor/autoload.php");```.
###Without Composer

```php
 spl_autoload_register(function($class) {
    $prefix = 'profenter\\tools\\';

    if ( ! substr($class, 0, 14) === $prefix) {
        return;
    }

    $class = substr($class, strlen($prefix));
    $location = __DIR__ . 'path/to/php-tools/lib/' . str_replace('\\', '/', $class) . '.php';

    if (is_file($location)) {
        require_once($location);
    }
});
```

## Componets

### storage
The storage class can be used for working with own config files. So for example your php project uses a database like mysql and you want to store the creditails in an config file. By using this storage class its done withhin secounds.

#### Basic Usage

Create a new class `myConfigClass` and extend it to `profenter\tools\storage\storage`:
```php
<?php
use profenter\tools\storage\storage;

class myConfigClass extends storage
{
}
```
Now create `myConfigClass::setConfig()` and setup the config file:
```php
<?php
use profenter\tools\storage\storage;
use profenter\tools\storage\file;

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
use profenter\tools\storage\storage;
use profenter\tools\storage\file;
use profenter\tools\storage\json;

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
profenter\tools\storage\

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

For more details see [examples](https://github.com/profenter/php-tools/blob/master/examples/storage) or [wiki](https://github.com/profenter/php-tools/wiki/Storage:Overview)
