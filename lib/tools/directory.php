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

use DateInterval;
use DateTime;
use profenter\exceptions\FileNotFoundException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class directory
 *
 * @package profenter\tools
 * @since   1.1.0
 */
class directory {
	/**
	 * @var array|object
	 * @since 1.1.0
	 */
	protected $files;
	/**
	 * @var null|string
	 * @since 1.1.0
	 */
	protected $dir;
	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $basePath;
	/**
	 * @var string
	 * @since      1.1.0
	 * @deprecated 1.1.0
	 */
	protected $subDir;
	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $cacheDir;
	/**
	 * @var array
	 * @since 1.1.0
	 */
	protected $ignore = [ ];
	/**
	 * @var bool|string
	 * @since 1.1.0
	 */
	protected $cache = false;
	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $root = "/";
	/**
	 * @var bool|string
	 * @since 1.1.0
	 * @uses  \profenter\tools\defaults\cacheConfig
	 */
	protected $cacheConfig = '\profenter\tools\defaults\cacheConfig';

	/**
	 *  alias for new directory()
	 *
	 * Enter the root dir tools should work on
	 *
	 * @param string $dir root dir
	 *
	 * @since 1.1.0
	 * @return \profenter\tools\directory
	 */
	public static function init( $dir = NULL ) {
		return new self( $dir );
	}

	/**
	 * directory constructor.
	 *
	 * Enter the root dir tools should work on.
	 *
	 * @param string $dir root dir
	 *
	 * @since 1.1.0
	 *
	 * @return \profenter\tools\directory
	 */
	public function __construct( $dir = NULL ) {
		if ( ! defined( "DS" ) ) {
			define( "DS", DIRECTORY_SEPARATOR );
		}
		$this->setFilters();
		if ( $dir === NULL ) {
			$this->setBasePath( getcwd() );
			$this->setDir( getcwd() );
		} else {
			$this->setBasePath( $dir );
			$this->setDir( $dir );
		}
		$this->filePath     = __FILE__;
		$this->relativePath = str_replace( "lib" . DS . "tools" . DS . basename( __FILE__ ), "", __FILE__ );

		return $this;
	}

	/**
	 * add a subDir for later use
	 *
	 * @param string $dir subDir path relative to the main path
	 *
	 * @since      1.1.0
	 * @return \profenter\tools\directory
	 * @deprecated 1.1.0
	 */
	public function addSubDir( $dir ) {
		$this->subDir = $dir;

		return $this;
	}

	/**
	 * generates an array of all dir with subDirs for the given dir
	 *
	 * @since 1.1.0
	 *
	 * @param bool $cache use cache?
	 *
	 * @return \profenter\tools\directory
	 * @throws \profenter\exceptions\FileNotFoundException
	 */
	public function get( $cache = true ) {
		if ( $this->exists() ) {
			if ( $this->getCache() !== false && $cache ) {
				$path = $this->getBasePath() . DS . $this->getCache() . DS . "files.json";
				if ( ! is_file( $path ) ) {
					file_put_contents( $path, json_encode( [ "created" => date( "Y-m-d H:i:s" ) ] ) );
				}
				$cacheConfig = $this->getCacheConfig();
				if ( ! isset( $cacheConfig::init()->filePathDate ) ) {
					$dt                                = new DateTime();
					$cacheConfig::init()->filePathDate = $dt->format( 'Y-m-d H:i:s' );
					$cacheConfig::init()
					            ->save();
					$this->get( false );
					$content = $this->files;
					file_put_contents( $path, json_encode( $content ) );
				}
				$cacheDate = new DateTime( $cacheConfig::init()->filePathDate );
				$now       = new DateTime();
				if ( $cacheDate->add( new DateInterval( 'PT1H' ) ) < $now ) {
					$this->get( false );
					$content = $this->files;
					file_put_contents( $path, json_encode( $content ) );
					$dt                                = new DateTime();
					$cacheConfig::init()->filePathDate = $dt->format( 'Y-m-d H:i:s' );
					$cacheConfig::init()
					            ->save();
				} else {
					$content = common::stdclassToArray( json_decode( file_get_contents( $path ), true ) );
				}
				$this->files = $content;
				print_r( $this->files );

				return $this;
			}
			$iterator = new RecursiveDirectoryIterator( $this->getDir() );
			$iterator->setFlags( RecursiveDirectoryIterator::SKIP_DOTS );
			$this->setFilters();
			$files  = [ ];
			$aFiles = new RecursiveIteratorIterator( new DirListerRecursiveFilterIterator( $iterator ), RecursiveIteratorIterator::SELF_FIRST );
			foreach ( $aFiles as $file ) {
				$files[ str_replace( $this->getBasePath(), "", $file->getPathname() ) ] = str_replace( $this->getBasePath(), "", $file->getPathname() );
			}
			$this->files = $files;
		} else {
			throw new FileNotFoundException( "This directory was not found: " . $this->getDir() );
		}
		$this->extend();

		return $this;
	}

	/**
	 * checks if the dir exists
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function exists() {
		return is_dir( $this->getDir() );
	}

	/**
	 * adds filter for RecursiveDirectoryIterator
	 *
	 * @uses  DirListerRecursiveFilterIterator
	 * @since 1.1.0
	 */
	protected function setFilters() {
		$filter = [ ];
		foreach ( $this->ignore as $id => $hiddenPath ) {
			if ( strpos( "*", $hiddenPath ) == 0 ) {
				$filter[] = $hiddenPath;
			}
		}
		$dir                                       = explode( DS, rtrim( $this->getBasePath(), DS ) );
		$dir                                       = $dir[ count( $dir ) - 1 ];
		$filter[]                                  = str_replace( [
			                                                          "/",
			                                                          ""
		                                                          ], "", str_replace( $dir, "", $_SERVER["SCRIPT_NAME"] ) );
		DirListerRecursiveFilterIterator::$FILTERS = $filter;
	}

	/**
	 * adds to the file array infos about the file
	 *
	 * @param bool  $recursion is recursion used?
	 * @param array $array     when used with recursion
	 *
	 * @return array|\profenter\tools\directory
	 * @since 1.1.0
	 * @uses  \SplFileInfo
	 * @uses  url
	 * @uses  tools
	 */
	public function addInfos( $recursion = false, $array = [ ] ) {
		$a = [ ];
		if ( empty( $array ) ) {
			$array = $this->files;
		}
		foreach ( $array as $index => $value ) {
			$v = ( is_array( $value ) ) ? ( isset( $value["__base_val"] ) ? $value["__base_val"] : $index ) : $value;
			if ( is_dir( $this->getBasePath() . "/" . $v ) ) {
				if ( is_array( $value ) && isset( $value["__base_val"] ) ) {
					$path = $value["__base_val"];
					$data = $this->addInfos( true, $value );
				} else {
					$path = $value;
					$data = [ ];
				}
				$a[ $index ] = [
					"time" => "---",
					"path" => $this->getBasePath() . "/" . $path,
					"url"  => url::parse( [ "dir" => $path ] ),
					"type" => "dir",
					"data" => $data,
					"json" => json_encode( [ ] )
				];
			} else {
				$value       = $v;
				$file        = file::init( $this->getBasePath() . "/" . $value );
				$base        = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'];
				$url         = $base . $_SERVER['REQUEST_URI'];
				$a[ $index ] = [
					"path"      => $this->getBasePath() . "/" . $value,
					"url"       => url::parse( [ ], $base . parse_url( $url, PHP_URL_PATH ) . $value ),
					"time"      => date( 'Y-m-d H:i:s', filemtime( $this->getBasePath() . "/" . $value ) ),
					"type"      => "file",
					"extension" => $file->getExtension(),
					"size"      => $file->getSize(),
					"json"      => json_encode( [
						                            "size" => $file->getSize(),
						                            "md5"  => md5_file( $this->getBasePath() . "/" . $value ),
						                            "sha1" => sha1_file( $this->getBasePath() . "/" . $value ),
					                            ] )
				];
			}
		}
		if ( $recursion ) {
			return $a;
		} else {
			$this->files = $a;

			return $this;
		}
	}

	/**
	 * removes hidden paths
	 *
	 * @param bool  $recursion is recursion used?
	 * @param array $path      when used with recursion
	 *
	 * @return array|\profenter\tools\directory
	 * @since 1.1.0
	 *
	 */
	public function parse( $recursion = false, $path = [ ] ) {
		if ( empty( $path ) ) {
			$path = $this->files;
		}
		foreach ( $path as $nameRelative => $child ) {
			if ( is_array( $child ) ) {
				if ( $this->isHidden( $child["__base_val"] ) ) {
					unset( $path[ $nameRelative ] );
				} else {
					$path[ $nameRelative ] = $this->parse( true, $child );
				}
			} else {
				if ( $this->isHidden( $child ) ) {
					unset( $path[ $nameRelative ] );
				}
			}
		}
		if ( $recursion ) {
			return $path;
		} else {
			$this->files = $path;

			return $this;
		}
	}

	/**
	 * checks if a path is hidden
	 *
	 * @param string $path path to be checked
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function isHidden( $path ) {
		$path = ltrim( $path, '/' );
		foreach ( $this->ignore as $hiddenPath ) {
			if ( fnmatch( $hiddenPath, $path ) ) {
				return true;
			}
		}
		$actualPath = str_replace( $this->relativePath, "", $_SERVER["SCRIPT_FILENAME"] );
		if ( $path == $actualPath ) {
			return true;
		}

		return false;
	}

	/**
	 * return the files as array
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function asArray() {
		return $this->files;
	}

	/**
	 * return the files as json
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public function asJson() {
		return json_encode( $this->files );
	}

	/**
	 * extends the file array with to a extend array
	 *
	 * @since 1.1.0
	 * @return \profenter\tools\directory
	 */
	protected function extend() {
		$this->files = $this->explodeTree( $this->files, DS, true );

		return $this;
	}

	/**
	 * finds a file path in a array of a file tree
	 *
	 * @return \profenter\tools\directory
	 * @throws FileNotFoundException
	 * @since      1.1.0
	 * @deprecated 1.1.0
	 */
	public function gotoSubDir() {
		$pathArray = $this->files;
		$path      = $this->subDir;
		$e         = explode( "/", $path );
		foreach ( $e as $i => $value ) {
			if ( empty( $value ) ) {
				unset( $e[ $i ] );
			}
		}
		foreach ( $e as $val ) {
			if ( isset( $pathArray[ $val ] ) ) {
				if ( is_array( $pathArray[ $val ] ) ) {
					$pathArray = $pathArray[ $val ];
				} else {
					$pathArray = [ ];
				}
			} else {
				throw new FileNotFoundException( "404" );
			}
		}
		$this->files = $pathArray;

		return $this;
	}

	/**
	 *
	 */
	public function find() {
	}

	/**
	 * changes location based on unix expressions
	 *
	 * @param $location
	 *
	 * @return $this
	 * @throws \profenter\exceptions\FileNotFoundException
	 */
	public function cd( $location ) {
		$e = explode( DS, $location );
		if ( $location == "/" or $location[0] == "/" ) {
			while( $this->getDir() != $this->getRoot() ) {
				$this->above();
			}
		}
		foreach ( $e as $item ) {
			if ( ! empty( $item ) ) {
				if ( $item == ".." ) {
					$this->above();
				} else if ( $item == "." ) {
					continue;
				} else if ( isset( $this->files[ $item ] ) ) {
					$this->files = $this->files[ $item ];
					$this->setDir( $this->getDir() . DS . $item );
				} else {
					throw new FileNotFoundException( $this->getDir() . DS . $item );
				}
			}
		}

		return $this;
	}

	/**
	 * moves the file tree to dir above
	 */
	protected function above() {
		$e = explode( DS, $this->getDir() );
		unset( $e[ count( $e ) - 1 ] );
		$dir = implode( DS, $e );
		$iterator = new RecursiveDirectoryIterator( $dir );
		$iterator->setFlags( RecursiveDirectoryIterator::SKIP_DOTS );
		$this->setFilters();
		$files  = [ ];
		$aFiles = new RecursiveIteratorIterator( new DirListerRecursiveFilterIterator( $iterator ), RecursiveIteratorIterator::SELF_FIRST );
		$aFiles->setMaxDepth( 0 );
		foreach ( $aFiles as $file ) {
			$files[ ltrim( str_replace( $dir, "", $file->getPathname() ), DS ) ] = str_replace( $dir, "", $file->getPathname() );
		}
		$e       = explode( DS, $this->getDir() );
		$dirname = $e[ count( $e ) - 1 ];
		$tmp     = $files;
		unset( $tmp[ $dirname ] );
		foreach ( $tmp as $item ) {
			if ( is_dir( $dir . DS . $item ) ) {
				$iterator = new RecursiveDirectoryIterator( $dir . DS . $item );
				$iterator->setFlags( RecursiveDirectoryIterator::SKIP_DOTS );
				$this->setFilters();
				$f      = [ ];
				$aFiles = new RecursiveIteratorIterator( new DirListerRecursiveFilterIterator( $iterator ), RecursiveIteratorIterator::SELF_FIRST );
				foreach ( $aFiles as $file ) {
					$f[ ltrim( str_replace( $dir . DS . $item, "", $file->getPathname() ), DS ) ] = str_replace( $dir, "", $file->getPathname() );
				}
				$files[ ltrim( $item, DS ) ] = $this->explodeTree( $f, DS, true );
			}
		}
		$files[ $dirname ] = $this->files;
		$this->files       = $this->reCalcTreeValue( $this->explodeTree( $files, DS, true ) );
		$this->setDir( $dir );
	}

	/**
	 *
	 */
	public function rm() {
	}

	/**
	 * recalculates the exploded tree
	 *
	 * @param array  $tree current dir tree
	 * @param string $base in which dir we are
	 *
	 * @return array
	 */
	protected function reCalcTreeValue( $tree, $base = DS ) {
		$a               = [ ];
		$base            = str_replace( DS . DS, DS, $base );
		$a["__base_val"] = $base;
		foreach ( $tree as $key => $item ) {
			if ( is_string( $item ) ) {
				if ( $key != "__base_val" ) {
					$a[ $key ] = str_replace( DS . DS, DS, $base . DS . $key );
				}
			} else if ( is_array( $item ) ) {
				$a[ $key ] = $this->reCalcTreeValue( $item, $base . DS . $key );
			}
		}

		return $a;
	}

	/**
	 * @author    Kevin van Zonneveld <kevin@vanzonneveld.net>
	 * @author    Lachlan Donald
	 * @author    Takkie
	 * @copyright 2008 Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD Licence
	 * @version   SVN: Release: $Id: explodeTree.inc.php 89 2008-09-05 20:52:48Z kevin $
	 * @link      http://kevin.vanzonneveld.net/
	 *
	 * @param array   $array
	 * @param string  $delimiter
	 * @param boolean $baseval
	 *
	 * @return array
	 */
	protected function explodeTree( $array, $delimiter = '_', $baseval = false ) {
		if ( ! is_array( $array ) ) {
			return false;
		}
		$splitRE   = '/' . preg_quote( $delimiter, '/' ) . '/';
		$returnArr = [ ];
		foreach ( $array as $key => $val ) {
			// Get parent parts and the current leaf
			$parts    = preg_split( $splitRE, $key, - 1, PREG_SPLIT_NO_EMPTY );
			$leafPart = array_pop( $parts );
			// Build parent structure
			// Might be slow for really deep and large structures
			$parentArr = &$returnArr;
			foreach ( $parts as $part ) {
				if ( ! isset( $parentArr[ $part ] ) ) {
					$parentArr[ $part ] = [ ];
				} elseif ( ! is_array( $parentArr[ $part ] ) ) {
					if ( $baseval ) {
						$parentArr[ $part ] = [ '__base_val' => $parentArr[ $part ] ];
					} else {
						$parentArr[ $part ] = [ ];
					}
				}
				$parentArr = &$parentArr[ $part ];
			}
			// Add the final part to the structure
			if ( empty( $parentArr[ $leafPart ] ) ) {
				$parentArr[ $leafPart ] = $val;
			} elseif ( $baseval && is_array( $parentArr[ $leafPart ] ) ) {
				$parentArr[ $leafPart ]['__base_val'] = $val;
			}
		}

		return $returnArr;
	}

	/**
	 * sets base bath where tools can operate from
	 *
	 * @param string $basePath
	 *
	 * @since 1.1.0
	 * @return directory
	 */
	protected function setBasePath( $basePath ) {
		$this->basePath = $basePath;

		return $this;
	}

	/**
	 * returns base path
	 *
	 * @since 1.1.0
	 * @return string
	 */
	protected function getBasePath() {
		return $this->basePath;
	}

	/**
	 * set the path to cache dir. If it's "false" cache is disabled (default)
	 *
	 * @param boolean|string $cache path
	 *
	 * @return \profenter\tools\directory
	 */
	public function setCache( $cache ) {
		$this->cache = $cache;

		return $this;
	}

	/**
	 * get cache path if set
	 *
	 * @return boolean|string
	 */
	public function getCache() {
		return $this->cache;
	}

	/**
	 * adds files which are ignored by this class. Wildcard '*' can be used
	 *
	 * @param array|string $ignore
	 *
	 * @return \profenter\tools\directory
	 */
	public function addIgnore( $ignore ) {
		if ( is_string( $ignore ) ) {
			$this->ignore[] = $ignore;
		} else {
			$this->ignore = array_merge( $this->ignore, $ignore );
		}

		return $this;
	}

	/**
	 * add the full name of cacheConfig file with namespace
	 *
	 * @param bool|string $cacheConfig
	 *
	 * @return \profenter\tools\directory
	 */
	public function setCacheConfig( $cacheConfig ) {
		$this->cacheConfig = $cacheConfig;

		return $this;
	}

	/**
	 * gets the name of cacheConfig file
	 *
	 * @return bool|string
	 */
	public function getCacheConfig() {
		return $this->cacheConfig;
	}

	/**
	 * sets the root
	 *
	 * @param string $root
	 *
	 * @return \profenter\tools\directory
	 */
	public function setRoot( $root ) {
		$this->root = $root;

		return $this;
	}

	/**
	 * gets the root path
	 *
	 * @return string
	 */
	public function getRoot() {
		return $this->root;
	}

	/**
	 * sets the current dir this class work on
	 *
	 * @param null|string $dir
	 *
	 * @return directory
	 */
	public function setDir( $dir ) {
		$this->dir = $dir;

		return $this;
	}

	/**
	 * gets the current dir this class work on
	 *
	 * @return null|string
	 */
	public function getDir() {
		return $this->dir;
	}

	/**
	 * returns subDir
	 *
	 * @return string
	 * @deprecated 1.1.0
	 */
	protected function getSubDir() {
		return $this->subDir;
	}

	/**
	 * sets subDir
	 *
	 * @param string $subDir
	 *
	 * @deprecated 1.1.0
	 */
	protected function setSubDir( $subDir ) {
		$this->subDir = $subDir;
	}

	/**
	 * returns relative path
	 *
	 * @since 1.1.0
	 * @return string
	 */
	protected function getRelativePath() {
		return $this->relativePath;
	}
}
