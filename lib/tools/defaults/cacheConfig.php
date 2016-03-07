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
namespace profenter\tools\defaults;

use profenter\exceptions\FileNotFoundException;
use profenter\exceptions\JsonException;
use profenter\tools\config;

/**
 * Class cacheConfig
 *
 * @package profenter\simpleDirLister
 * @since   1.1.0
 */
class cacheConfig extends config {
	/**
	 * the constructor for the cache config file class
	 *
	 * @throws \profenter\exceptions\FileNotFoundException
	 * @throws \profenter\exceptions\JsonException
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->setSource( false );
		parent::__construct();
		if ( ! defined( "DS" ) ) {
			define( "DS", DIRECTORY_SEPARATOR );
		}
		if ( defined( "CACHECONFIG" ) ) {
			try {
				$this->check( CACHECONFIG );
				$this->file = CACHECONFIG;
			} catch( JsonException $e ) {
				throw $e;
			} catch( FileNotFoundException $e ) {
				throw $e;
			}
		} else {
			$file = getcwd() . DS . ".profenter" . DS . "cache" . DS . "config.json";
			try {
				$this->check( $file );
				$this->file = $file;
			} catch( JsonException $e ) {
				throw $e;
			} catch( FileNotFoundException $e ) {
				throw $e;
			}
		}
		$this->config = json_decode( file_get_contents( $this->file ), true );
	}
}