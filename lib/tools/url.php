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

/**
 * Class url
 *
 * @package profenter\tools
 * @since   1.0 based on https://github.com/profenter/simpleDirLister/blob/9e5782fed9631575c2ce5122c120e8db7c5a13ee/include/classes/url.php
 */
class url {
	/**
	 * parsed $_GET keys
	 *
	 * @var array
	 * @since   1.0
	 */
	protected static $GET = [ ];
	/**
	 * default $_GET value
	 *
	 * @var array
	 * @since   1.0
	 */
	protected static $default = [ ];
	/**
	 * allowed $_GET value
	 *
	 * @var array
	 * @since   1.0
	 */
	protected static $allowed = [ ];

	/**
	 * returns a parsed GET value
	 *
	 * @param string $name
	 *
	 * @return string
	 * @since   1.0
	 */
	public static function GET( $name ) {
		self::sync();
		if ( isset( self::$GET[ $name ] ) ) {
			return self::$GET[ $name ];
		}

		return "";
	}

	/**
	 * returns all $_GET
	 *
	 * @return array
	 */
	public static function GETAll() {
		self::sync();

		return self::$GET;
	}

	/**
	 * sets which keys are allowed to use within these class
	 *
	 * @param string|array $name allowed GET key
	 *
	 * @since   1.0
	 */
	public static function allowed( $name ) {
		if ( is_string( $name ) ) {
			self::$allowed[ $name ] = true;
		} else if ( is_array( $name ) ) {
			foreach ( $name as $item ) {
				self::$allowed[ $item ] = true;
			}
		}
		self::sync();
	}

	/**
	 * merges s url with given parameters
	 *
	 * @param array  $array parameters
	 * @param string $host  used url, if not set, the current url will be used
	 *
	 * @return string
	 * @since   1.0
	 */
	public static function parse( $array = [ ], $host = NULL ) {
		if ( is_null( $host ) ) {
			$url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} else {
			$url = $host;
		}
		$a     = explode( "&", parse_url( $url, PHP_URL_QUERY ) );
		$query = [ ];
		foreach ( $a as $val ) {
			$e              = explode( "=", $val );
			$query[ $e[0] ] = isset( $e[1] ) ? $e[1] : NULL;
		}
		foreach ( $array as $index => $value ) {
			if ( isset( $array[ $index ] ) && ! empty( $array[ $index ] ) ) {
				$query[ $index ] = $value;
			} else {
				$query[ $index ] = self::getDefault( $index );
			}
		}
		if ( is_null( $host ) ) {
			$url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . parse_url( $url, PHP_URL_PATH ) . "?" . http_build_query( $query, '', '&' );
		} else {
			$url = parse_url( $host, PHP_URL_SCHEME ) . '://' . parse_url( $host, PHP_URL_HOST ) . parse_url( $host, PHP_URL_PATH ) . "?" . http_build_query( $query, '', '&' );
		}

		return $url;
	}

	/**
	 * returns the current url
	 *
	 * @param bool $parsed include the default values for $_GET
	 *
	 * @return string
	 * @since   1.0
	 */
	public static function current( $parsed = true ) {
		$url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if ( $parsed ) {
			$url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . parse_url( $url, PHP_URL_PATH ) . "?" . http_build_query( self::GETAll(), '', '&' );
		}

		return $url;
	}

	/**
	 * syncs the $_GET value with internal data
	 *
	 * @since   1.0
	 */
	protected static function sync() {
		foreach ( self::$allowed as $key => $true ) {
			if ( isset( $_GET[ $key ] ) && ! empty( $_GET[ $key ] ) ) {
				self::$GET[ $key ] = $_GET[ $key ];
			} else {
				self::$GET[ $key ] = self::getDefault( $key );
			}
		}
	}

	/**
	 * get default values
	 *
	 * @param string $name GET key
	 *
	 * @return array|boolean
	 * @since   1.0
	 */
	public static function getDefault( $name ) {
		return ( isset( self::$default[ $name ] ) ) ? self::$default[ $name ] : "";
	}

	/**
	 * sets default values by key
	 *
	 * @param string $name  GET key
	 * @param string $value value
	 *
	 * @since   1.0
	 */
	public static function setDefault( $name, $value = "" ) {
		self::$default[ $name ] = $value;
		self::sync();
	}
}