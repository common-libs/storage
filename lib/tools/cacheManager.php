<?php
/**
 * Copyright (c) 2016.  Profenter Systems <service@profenter.de>
 *  
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace profenter\tools;

use Memcache;
use phpFastCache\CacheManager as c;

class cacheManager {
	/**
	 * @var bool
	 */
	protected static $setuped = false;
	/**
	 * @var null|\phpFastCache\Core\DriverAbstract
	 */
	public static $getCacheInstance = NULL;
	public static $path             = NULL;

	public static function auto() {
		if(is_null(self::$path)) {
			self::$path = getcwd();
		}
		$isMemcacheAvailable = false;
		if(class_exists('Memcache')) {
			$memcache            = new Memcache;
			$isMemcacheAvailable = @$memcache->connect('localhost');
		}
		if($isMemcacheAvailable) {
			self::setMemcache();
		}
		else if(function_exists('sqlite_open')) {
			self::setSqlite();
		}
		else {
			self::setFile();
		}
	}

	public static function setMemcache() {
		c::setup([
			'memcache' => [
				[
					'127.0.0.1',
					11211,
					1
				],
			],
		]);
		self::$getCacheInstance = c::getInstance('memcache');
	}

	public static function setSqlite() {
		c::setup("storage", "sqlite");
		self::$getCacheInstance = c::getInstance();
	}

	public static function setFile() {
		if(!defined("DS")) {
			define("DS", DIRECTORY_SEPARATOR);
		}
		if(is_null(self::$path)) {
			self::$path = getcwd();
		}
		c::setup([
			"path" => common::fixPaths(self::$path . DS . ".simpledirlister" . DS . "tmp" . DS),
		]);
		c::CachingMethod("phpfastcache");
		self::$getCacheInstance = c::Files();
	}
}