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
namespace profenter\exceptions;

/**
 * Class FileNotFoundException
 *
 * @package profenter\exceptions
 * @since   1.0.0 based on https://github.com/profenter/simpleDirLister/blob/9e5782fed9631575c2ce5122c120e8db7c5a13ee/include/classes/FileNotFoundException.php
 */
class FileNotFoundException extends \Exception {
	/**
	 * @var string
	 */
	protected $path = "/";

	/**
	 * FileNotFoundException constructor.
	 *
	 * @param string          $message path which was not found
	 * @param bool            $code    error code, unused at the moment
	 * @param \Exception|NULL $previous
	 */
	public function __construct( $message, $code = false, \Exception $previous = NULL ) {
		if ( ! defined( "DS" ) ) {
			define( "DS", DIRECTORY_SEPARATOR );
		}
		$this->path = $message;
		$message    = "\nCould not find file: '" . $this->path . "' Reason:";
		if ( is_dir( $this->path ) ) {
			$message .= "It's a dir, not a file.";
		} else if ( ( $dir = $this->checkForDirs() ) === false ) {
			$message .= "File not found.";
		} else if ( ( $dir = $this->checkForDirs() ) !== true ) {
			$message .= "Path to file is wrong, check the following dir:" . $dir . " .";
		} else {
			$message .= "File does not exists.";
		}
		$message .= "\n";
		parent::__construct( $message, $code, $previous );
	}

	/**
	 * checks if the dir exists
	 *
	 * @return bool|string dir which not exists
	 */
	protected function checkForDirs() {
		$e     = explode( DS, $this->path );
		$s     = "";
		$break = false;
		foreach ( $e as $dir ) {
			if ( ! empty( $dir ) ) {
				$s .= DS . $dir;
				if ( ! is_dir( $s ) ) {
					$break = true;
					break;
				}
			}
		}
		if ( $s == $this->path ) {
			return false;
		} else if ( $break ) {
			return $s;
		}

		return true;
	}
}