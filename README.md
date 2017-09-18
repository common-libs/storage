# Common Libs - Storage  [![GitHub release](https://img.shields.io/github/release/common-libs/storage.svg?maxAge=2592001&style=flat-square)](https://github.com/common-libs/storage) -  [![Packagist](https://img.shields.io/packagist/dt/common-libs/storage.svg?maxAge=2592001&style=flat-square)](https://packagist.org/packages/common-libs/storage) [![license](https://img.shields.io/github/license/common-libs/storage.svg?maxAge=2592000&style=flat-square)](https://github.com/common-libs/storage)

A php lib for storing data to files. The storage class can be used for working with own config files. So for example your php project uses a database like mysql and you want to store the creditails in an config file. By using this storage class its done withhin secounds.


## Installation

Run:
`composer require common-libs/storage`

As always load composer in your main file: ```require_once("vendor/autoload.php");```.

## Use it


### Basic Usage

Create a new class `myConfigClass` and extend it to `common\storage\Storage`:
```php
<?php
use common\storage\Storage;

class myConfigClass extends Storage
{
}
```
Now implement `myConfigClass::setConfig()` and setup the config file:
```php
<?php
use common\storage\Storage;
use common\storage\Options;

class myConfigClass extends Storage {
    static function setConfig(Options $options) {
        $options->setCreateIfNotExists(true);
        $options->setPath(\common\io\File::get("./.store/test1.json"));
    }
}
```
Maybe you already noticed that we haven't set the file format. You do it by importing the correct driver (using traits):
```php
<?php
use common\storage\Options;
use common\storage\Storage;
use common\storage\json;

class myConfigClass extends Storage {
	use json;

	static function setConfig(Options $options) {
		$options->setCreateIfNotExists(true);
		$options->setPath(\common\io\File::get("./.store/test1.json"));
	}
}

```
Supported formats: 
common\storage\

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

Set Defaults
```php
<?php
use common\storage\Options;
use common\storage\Storage;
use common\storage\json;

class myConfigClass extends Storage {
	use json;

	static function setConfig(Options $options) {
		$options->setCreateIfNotExists(true);
		$options->setPath(\common\io\File::get("./.store/test1.json"));
	}

	static function setDefaults(Storage $storage) {
		$storage->test  = "Hi";
	}
}

```