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

use profenter\exceptions\FileNotFoundException;

class file {
	protected $file = NULL;
	/**
	 * @var \SplFileInfo
	 */
	protected $info;

	/**
	 * file constructor.
	 *
	 * @param $file
	 *
	 * @throws \profenter\exceptions\FileNotFoundException
	 */
	public function __construct( $file ) {
		$this->file = $file;
		if ( ! $this->exists() ) {
			throw new FileNotFoundException( $this->file );
		}
		$this->info = new \SplFileInfo( $this->file );

		return $this;
	}

	public static function init( $file ) {
		return new self( $file );
	}

	/**
	 * checks if the dir exists
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function exists() {
		return is_file( $this->file );
	}

	/**
	 * get formatted file size
	 *
	 * @param int $precision
	 *
	 * @link    http://stackoverflow.com/a/2510540
	 * @since   1.1.0
	 * @return string
	 */
	public function getSize( $precision = 2 ) {
		$size = sprintf( '%u', filesize( $this->file ) );
		if ( $size > 0 ) {
			$base     = log( $size, 1024 );
			$suffixes = [ 'B', 'KB', 'MB', 'GB', 'TB' ];

			return round( pow( 1024, $base - floor( $base ) ), $precision ) . $suffixes[ floor( $base ) ];
		}

		return $size;
	}

	/**
	 * removes the file type so you get the filename without extension
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public function getName() {
		$e = explode( ".", $this->getFilename() );
		if ( strpos( $this->getExtension(), "." ) ) {
			unset( $e[ count( $e ) - 1 ] );
			unset( $e[ count( $e ) - 1 ] );
		} else {
			unset( $e[ count( $e ) - 1 ] );
		}

		return implode( ".", $e );
	}

	public function getExtension() {
		$extension = $this->info->getExtension();
		if ( $extension == 'gz' ) {
			$ext       = new \SplFileInfo( $this->getName() );
			$extension = $ext->getExtension() . "." . $extension;
		}

		return $extension;
	}

	public function getOwner() {
		if ( function_exists( "posix_getpwuid" ) ) {
			$o = posix_getpwuid( $this->info->getOwner() );

			return $o["name"];
		}

		return $this->info->getOwner();
	}

	public function getFilename() {
		return $this->info->getFilename();
	}

	public function getGroup() {
		if ( function_exists( "posix_getgrgid" ) ) {
			$o = posix_getgrgid( $this->info->getOwner() );

			return $o["name"];
		}

		return $this->info->getGroup();
	}
}