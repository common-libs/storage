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

/**
 * Created by PhpStorm.
 * User: alexander
 * Date: 24.06.16
 * Time: 13:57
 */
namespace profenter\tools;

use Memcache;
use phpFastCache\CacheManager;

class dir {
	protected static $instances        = [];
	protected static $getCacheInstance = NULL;

	/**
	 * @param $id
	 *
	 * @return directory
	 */
	public static function getInstance($id) {
		if(!$id or !isset(self::$instances[$id])) {
			return self::newInstance($id);
		}

		return self::$instances[$id];
	}

	/**
	 * @param      $path
	 * @param bool $id
	 *
	 * @return directory
	 */
	public static function newInstance($path, $id = false) {
		if(!$id) {
			$id = rand(0, 10000);
		}
		if(!isset(self::$instances[$id])) {
			self::$instances[$id] = new directory($path);

			return self::$instances[$id];
		}
		else {
			return self::getInstance($id);
		}
	}

	/**
	 *
	 */
	public static function setup() {
		$isMemcacheAvailable = false;
		if(class_exists('Memcache')) {
			$memcache            = new Memcache;
			$isMemcacheAvailable = @$memcache->connect('localhost');
		}
		if($isMemcacheAvailable) {
			CacheManager::setup([
				'memcache' => [
					[
						'127.0.0.1',
						11211,
						1
					],
				],
			]);
			self::$getCacheInstance = CacheManager::getInstance('memcache');
		}
	}
}