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

use phpFastCache\Exceptions\phpFastCacheDriverException;
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
	 * @deprecated 1.1.5
	 */
	protected $cacheDir;
	/**
	 * @var array
	 * @since 1.1.0
	 */
	protected $ignore = [];
	/**
	 * @var bool|string
	 * @since      1.1.0
	 * @deprecated 1.1.5
	 */
	protected $cache = false;
	/**
	 * @var string
	 * @since 1.1.0
	 */
	protected $root = "/";


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
	public static function init($dir = NULL) {
		return new self($dir);
	}

	/**
	 * directory constructor.
	 *
	 * Enter the root dir tools should work on.
	 *
	 * @param string $dir root dir
	 * @param bool   $cache
	 *
	 * @throws FileNotFoundException
	 * @since 1.1.0
	 *
	 */
	public function __construct($dir = NULL, $cache = true) {
		if(!defined("DS")) {
			define("DS", DIRECTORY_SEPARATOR);
		}
		$this->setFilters();
		if($dir === NULL) {
			$this->setBasePath(getcwd());
			$this->setDir(getcwd());
		}
		else {
			$this->setBasePath($dir);
			$this->setDir(DS);
			$this->setRoot($dir);
		}
		$this->filePath     = __FILE__;
		$this->relativePath = str_replace("lib" . DS . "tools" . DS . basename(__FILE__), "", __FILE__);

		return $this;
	}

	protected function run() {
		if($this->exists()) {

			try {

				$cache = dir::$getCacheInstance;
				$id    = md5($this->getDir());
				$files = $cache->get($id);
				if(is_null($files) or !$cache) {
					$iterator = new RecursiveDirectoryIterator(common::fixPaths($this->getBasePath() . $this->getDir()));
					$iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
					$this->setFilters();
					$files  = [];
					$aFiles = new RecursiveIteratorIterator(new DirListerRecursiveFilterIterator($iterator), RecursiveIteratorIterator::SELF_FIRST);
					foreach($aFiles as $file) {
						$files[str_replace($this->getBasePath(), "", $file->getPathname())] = str_replace($this->getBasePath(), "", $file->getPathname());
					}
					$files = $this->parse($this->explodeTree($files, DS, true));
					$cache->set($id, $files, 3600);
				}
				$this->files = $files;
			} catch(phpFastCacheDriverException $e) {
				echo $e->getMessage();
				echo "<br>Path: " . $this->getDir() . DS . ".simpledirlister" . DS . "tmp";
				echo "<br>User: " . shell_exec("whoami");
				die;
			}
		}
		else {
			throw new FileNotFoundException("This directory was not found: " . common::fixPaths($this->getBasePath() . $this->getDir()));
		}
	}
	/**
	 * generates an array of all dir with subDirs for the given dir
	 *
	 * @since      1.1.0
	 *
	 * @param bool $cache use cache?
	 *
	 * @return \profenter\tools\directory
	 * @throws \profenter\exceptions\FileNotFoundException
	 * @deprecated 1.1.5
	 */
	public function get($cache = true) {
		return $this;
	}

	/**
	 * checks if the dir exists
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function exists() {
		return is_dir(common::fixPaths($this->getBasePath() . DS . $this->getDir()));
	}

	/**
	 * adds filter for RecursiveDirectoryIterator
	 *
	 * @uses  DirListerRecursiveFilterIterator
	 * @since 1.1.0
	 */
	protected function setFilters() {
		$filter = [];
		foreach($this->ignore as $id => $hiddenPath) {
			if(strpos("*", $hiddenPath) == 0) {
				$filter[] = $hiddenPath;
			}
		}
		$dir                                       = explode(DS, rtrim($this->getBasePath(), DS));
		$dir                                       = $dir[count($dir) - 1];
		$filter[]                                  = str_replace([
			"/",
			""
		], "", str_replace($dir, "", $_SERVER["SCRIPT_NAME"]));
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
	public function addInfos($recursion = false, $array = []) {
		$a = [];
		if(empty($array)) {
			$array = $this->files;
		}
		foreach($array as $index => $value) {
			$v = (is_array($value)) ? (isset($value["__base_val"]) ? $value["__base_val"] : $index) : $value;
			if(is_dir($this->getBasePath() . "/" . $v)) {
				if(is_array($value) && isset($value["__base_val"])) {
					$path = $value["__base_val"];
					$data = $this->addInfos(true, $value);
				}
				else {
					$path = $value;
					$data = [];
				}
				$a[$index] = [
					"time" => "---",
					"path" => $this->getBasePath() . "/" . $path,
					"url"  => url::parse(["dir" => $path]),
					"type" => "dir",
					"data" => $data,
					"json" => json_encode([])
				];
			}
			else {
				$value     = $v;
				$file      = file::init($this->getBasePath() . "/" . $value);
				$base      = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
				$url       = $base . $_SERVER['REQUEST_URI'];
				$a[$index] = [
					"path"      => $this->getBasePath() . "/" . $value,
					"url"       => url::parse([], $base . parse_url($url, PHP_URL_PATH) . $value),
					"time"      => date('Y-m-d H:i:s', filemtime($this->getBasePath() . "/" . $value)),
					"type"      => "file",
					"extension" => $file->getExtension(),
					"size"      => $file->getSize(),
					"json"      => json_encode([
						"size" => $file->getSize(),
						"md5"  => md5_file($this->getBasePath() . "/" . $value),
						"sha1" => sha1_file($this->getBasePath() . "/" . $value),
					])
				];
			}
		}
		if($recursion) {
			return $a;
		}
		else {
			$this->files = $a;

			return $this;
		}
	}

	/**
	 * removes hidden paths
	 *
	 * @param array $path      when used with recursion
	 *
	 * @return array|\profenter\tools\directory
	 * @since 1.1.0
	 *
	 */
	protected function parse($path = []) {
		if(empty($path)) {
			$path = $this->files;
		}
		foreach($path as $nameRelative => $child) {
			if(is_array($child)) {
				if(isset($child["__base_val"]) && $this->isHidden($child["__base_val"])) {
					unset($path[$nameRelative]);
				}
				else {
					$path[$nameRelative] = $this->parse($child);
				}
			}
			else {
				if($this->isHidden($child)) {
					unset($path[$nameRelative]);
				}
			}
		}

		return $path;
	}

	/**
	 * checks if a path is hidden
	 *
	 * @param string $path path to be checked
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function isHidden($path) {
		$path = ltrim($path, DS);
		foreach($this->ignore as $hiddenPath) {
			if(fnmatch($hiddenPath, $path)) {
				return true;
			}
		}
		$actualPath = str_replace($this->relativePath, "", $_SERVER["SCRIPT_FILENAME"]);
		if($path == $actualPath) {
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
		$this->run();

		return $this->files;
	}

	/**
	 * return the files as array
	 *
	 * @since 1.1.0
	 * @return array
	 */
	public function getPlain() {
		$this->run();
		return $this->files;
	}

	/**
	 * return the files as json
	 *
	 * @since 1.1.0
	 * @return string
	 */
	public function asJson() {
		$this->run();

		return json_encode($this->files);
	}

	/**
	 * extends the file array with to a extend array
	 *
	 * @since      1.1.0
	 * @return \profenter\tools\directory
	 * @deprecated 1.1.5
	 */
	protected function extend() {
		return $this;
	}

	/**
	 * search for a file or directory
	 *
	 * @param string $search name of file or directory which should be found
	 *
	 * @since 1.2.0
	 * @return \profenter\tools\directory
	 */
	public function find($search) {
		$this->run();
		$e = explode(DS, $search);
		foreach($e as $item) {
			if(!empty($item)) {
				$this->files = $this->search($this->files, $item);
			}
		}

		return $this;
	}

	/**
	 * search for a string recursively in an array
	 *
	 * wildcard '*' can be used
	 *
	 * @param array  $array  array which should be searched in
	 * @param string $search search string
	 *
	 * @since 1.2.0
	 * @return array
	 */
	public function search($array, $search) {
		$found = [];
		foreach($array as $key => $item) {
			if(fnmatch($search, $key)) {
				$found[$key] = $item;
			}
			else if(is_array($item)) {
				$rec = $this->search($item, $search);
				if(!empty($rec)) {
					$found[$key] = $rec;
				}
			}
		}

		return $found;
	}

	/**
	 * changes location based on unix expressions
	 *
	 * @param string $location
	 *
	 * @return \profenter\tools\directory
	 * @throws \profenter\exceptions\FileNotFoundException
	 * @since 1.1.0
	 */
	public function cd($location) {
		$e = explode(DS, $location);
		if($location == DS or $location[0] == DS) {
			while($this->getDir() != $this->getRoot()) {
				$this->above();
			}
		}
		foreach($e as $item) {
			if(!empty($item)) {
				if($item == "..") {
					$this->above();
				}
				else if($item == ".") {
					continue;
				}
				else if(is_dir(common::fixPaths($this->getBasePath() . DS . $this->getDir() . DS . $item))) {
					$this->setDir($this->getDir() . DS . $item);
				}
				else {
					throw new FileNotFoundException(common::fixPaths($this->getBasePath() . $this->getDir() . DS . $item));
				}
			}
		}

		return $this;
	}

	/**
	 * moves the file tree to dir above
	 *
	 * @since 1.1.0
	 * @todo  check
	 */
	protected function above() {
		$e = explode(DS, $this->getDir());
		if(count($e) > 1) {
			unset($e[count($e) - 1]);
		}
		$dir      = implode(DS, $e);
		$this->setDir($dir);
	}

	/**
	 * @todo implement
	 */
	public function rm() {
	}

	/**
	 * recalculates the exploded tree
	 *
	 * @param array  $tree current dir tree
	 * @param string $base in which dir we are
	 *
	 * @since 1.1.0
	 * @return array
	 */
	protected function reCalcTreeValue($tree, $base = DS) {
		$a               = [];
		$base            = str_replace(DS . DS, DS, $base);
		$a["__base_val"] = $base;
		foreach($tree as $key => $item) {
			if(is_string($item)) {
				if($key != "__base_val") {
					$a[$key] = str_replace(DS . DS, DS, $base . DS . $key);
				}
			}
			else if(is_array($item)) {
				$a[$key] = $this->reCalcTreeValue($item, $base . DS . $key);
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
	 * @since     1.1.0
	 */
	protected function explodeTree($array, $delimiter = '_', $baseval = false) {
		if(!is_array($array)) {
			return false;
		}
		$splitRE   = '/' . preg_quote($delimiter, '/') . '/';
		$returnArr = [];
		foreach($array as $key => $val) {
			// Get parent parts and the current leaf
			$parts    = preg_split($splitRE, $key, -1, PREG_SPLIT_NO_EMPTY);
			$leafPart = array_pop($parts);
			// Build parent structure
			// Might be slow for really deep and large structures
			$parentArr = &$returnArr;
			foreach($parts as $part) {
				if(!isset($parentArr[$part])) {
					$parentArr[$part] = [];
				}
				elseif(!is_array($parentArr[$part])) {
					if($baseval) {
						$parentArr[$part] = ['__base_val' => $parentArr[$part]];
					}
					else {
						$parentArr[$part] = [];
					}
				}
				$parentArr = &$parentArr[$part];
			}
			// Add the final part to the structure
			if(empty($parentArr[$leafPart])) {
				$parentArr[$leafPart] = $val;
			}
			elseif($baseval && is_array($parentArr[$leafPart])) {
				$parentArr[$leafPart]['__base_val'] = $val;
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
	protected function setBasePath($basePath) {
		$this->basePath = common::fixPaths($basePath);

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
	 * @since 1.1.0
	 * @deprecated 1.1.5
	 */
	public function setCache($cache) {
		return $this;
	}

	/**
	 * get cache path if set
	 *
	 * @return boolean|string
	 * @since 1.1.0
	 * @deprecated 1.1.5
	 */
	public function getCache() {
		return [];
	}

	/**
	 * adds files which are ignored by this class. Wildcard '*' can be used
	 *
	 * @param array|string $ignore
	 *
	 * @return \profenter\tools\directory
	 * @since 1.1.0
	 */
	public function addIgnore($ignore) {
		if(is_string($ignore)) {
			$this->ignore[] = $ignore;
		}
		else {
			$this->ignore = array_merge($this->ignore, $ignore);
		}
		return $this;
	}

	/**
	 * add the full name of cacheConfig file with namespace
	 *
	 * @param bool|string $cacheConfig
	 *
	 * @return \profenter\tools\directory
	 * @deprecated 1.1.5
	 * @since      1.1.0
	 */
	public function setCacheConfig($cacheConfig) {
		return $this;
	}

	/**
	 * gets the name of cacheConfig file
	 *
	 * @return bool|string|\profenter\tools\config
	 * @since      1.1.0
	 * @deprecated 1.1.5
	 */
	public function getCacheConfig() {
	}

	/**
	 * sets the root
	 *
	 * @param string $root
	 *
	 * @return \profenter\tools\directory
	 * @since 1.1.0
	 */
	public function setRoot($root) {
		$this->root = $root;

		return $this;
	}

	/**
	 * gets the root path
	 *
	 * @return string
	 * @since 1.1.0
	 */
	public function getRoot() {
		return $this->root;
	}

	/**
	 * sets the current dir this class work on
	 *
	 * @param null|string $dir
	 *
	 * @return \profenter\tools\directory
	 * @since 1.1.0
	 */
	public function setDir($dir) {
		$this->dir = common::fixPaths($dir);
		return $this;
	}

	/**
	 * gets the current dir this class work on
	 *
	 * @return null|string
	 * @since 1.1.0
	 */
	public function getDir() {
		return $this->dir;
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