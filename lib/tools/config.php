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
 * Date: 05.03.16
 * Time: 13:11
 */
namespace profenter\tools;

use profenter\exceptions\FileNotFoundException;
use profenter\exceptions\JsonException;

/**
 * Class config
 *
 * @package profenter\tools
 * @since   1.0.0 based on https://github.com/profenter/simpleDirLister/blob/9e5782fed9631575c2ce5122c120e8db7c5a13ee/include/classes/JSONConfig.php
 */
class config {
	/**
	 * contains the parsed config settings
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $config = [ ];
	/**
	 * the path to config file
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $file;
	/**
	 * the source config file
	 *
	 * @var string|boolean
	 * @since 1.0.0
	 */
	protected $source;

	/**
	 * a short way for `new JSONConfig`
	 *
	 * @return \profenter\tools\config
	 * @since 1.0.0
	 */
	public static function init() {
		return new static();
	}

	/**
	 * constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
	}

	/**
	 * checks if a file exists. If not the target dir will be created and the default file will be copied into it.
	 *
	 * @param string $file Config file name
	 *
	 * @return bool
	 * @throws \profenter\exceptions\FileNotFoundException
	 * @throws \profenter\exceptions\JsonException
	 * @since 1.0.0
	 */
	protected function check( $file ) {
		if ( is_file( $file ) ) {
			json_decode( file_get_contents( $file ) );
			if ( json_last_error() != JSON_ERROR_NONE ) {
				throw new JsonException( "Config file is wrong formatted. Json error." );
			}
		} else {
			if ( ! is_dir( dirname( $file ) ) ) {
				$mk = mkdir( dirname( $file ), 0777, true );
				if ( ! $mk ) {
					throw new FileNotFoundException( "Config file not found.  Couldn't create target dir. File: " . $file );
				}
			}
			$source = $this->source;
			if ( $source === false ) {
				file_put_contents( $file, json_encode( [ ] ) );
				if ( ! $this->check( $file ) ) {
					throw new FileNotFoundException( "Config file not found. Could not create file. File: " . $file );
				} else {
					return true;
				}
			} else {
				copy( $source, $file );
				if ( ! $this->check( $file ) ) {
					throw new FileNotFoundException( "Config file not found. Could not copy default file (" . $source . "). File: " . $file );
				} else {
					return true;
				}
			}
		}

		return true;
	}

	/**
	 * saves the config file
	 *
	 * @todo  check if file exists
	 * @since 1.0.0
	 */
	public function save() {
		file_put_contents( $this->file, json_encode( $this->config ) );
	}

	/**
	 * returns a settings value by name
	 *
	 * @param string $name name
	 *
	 * @return bool|array|string
	 * @since 1.0.0
	 */
	public function __get( $name ) {
		if ( isset( $this->config[ $name ] ) ) {
			return $this->config[ $name ];
		}

		return false;
	}

	/**
	 * sets a settings value by name
	 *
	 * @param string $name  name
	 * @param string $value value
	 *
	 * @since 1.0.0
	 */
	public function __set( $name, $value ) {
		$this->config[ $name ] = $value;
		$this->save();
	}

	/**
	 * checks if a setting is set
	 *
	 * @param string $name
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function __isset( $name ) {
		return isset( $this->config[ $name ] );
	}

	/**
	 * transforms all settings to a JSON string
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function __toString() {
		return json_encode( $this->config );
	}

	/**
	 * returns the config file path
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * sets the config file path
	 *
	 * @param string $file file path
	 *
	 * @since 1.0.0
	 */
	public function setFile( $file ) {
		$this->file = $file;
	}

	/**
	 * returns the default file path of the config file
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * sets the default file path for the config file
	 *
	 * @param string $source default file path
	 *
	 * @since 1.0.0
	 */
	public function setSource( $source ) {
		$this->source = $source;
	}
}