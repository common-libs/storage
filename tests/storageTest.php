<?php
declare( strict_types=1 );

use common\storage\Options;
use common\storage\Storage;
use PHPUnit\Framework\TestCase;

require (__DIR__ . "/../vendor/autoload.php");
class storageTest extends TestCase {
	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
	}

	public function testBasic() {
		$this->assertEquals("Hi", TestStorage::init()->test);
	}
	public function testStatic() {
		$this->assertEquals("Hi", TestStorage::$test);
	}
}

class TestStorage extends Storage {
	use \common\storage\json;

	static function setConfig(Options $options) {
		$options->setCreateIfNotExists(true);
		$options->setPath(\common\io\File::get("./.store/test1.json"));
	}

	static function setDefaults(Storage $storage) {
		$storage->test  = "Hi";
		$storage->hallo = ["hi :D", "jo" => "hi", "Hi" => ["Hallo", "Hello"]];
	}
}
