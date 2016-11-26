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

namespace common\storage\legacy;
use common\storage\exception\FileNotFoundException;

/**
 * Class file
 *
 * @package common\storage\legacy
 * @since   1.3.0
 */
class file
{
	/**
	 * @var bool
	 * @since 1.3.0
	 */
	protected $cache = true;
	/**
	 * @var string
	 * @since 1.3.0
	 */
	protected $path = "";
	/**
	 * @var null
	 * @since 1.3.0
	 */
	protected $content = NULL;
	/**
	 * @var boolean
	 * @since 1.3.0
	 */
	protected $createIfNotExists = true;

	/**
	 * file constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct()
	{

	}

	/**
	 * returns plain content
	 *
	 * @since 1.3.0
	 * @return null|string
	 */
	public function getContent()
	{
		if (!$this->isCache()) {
			$this->content = file_get_contents($this->getPath());
		}

		return $this->content;
	}

	/**
	 * check if cache is enabled
	 *
	 * @since 1.3.0
	 * @return boolean
	 */
	public function isCache()
	{
		return $this->cache;
	}

	/**
	 * set cache is enabled
	 *
	 * @since 1.3.0
	 *
	 * @param boolean $cache
	 */
	public function setCache($cache)
	{
		$this->cache = $cache;
	}

	/**
	 * returns file path
	 *
	 * @since 1.3.0
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * sets file path
	 *
	 * @param string $path file path
	 *
	 * @since 1.3.0
	 * @throws FileNotFoundException
	 */
	public function setPath($path)
	{
		$this->path = $path;
		if (!is_file($path)) {
			if ($this->isCreateIfNotExists()) {
				$dir = dirname($path);
				if (!is_dir($dir)) {
					mkdir($dir, 0770, true);
				}
				touch($path);
				if (!is_file($path)) {
					throw new FileNotFoundException($path);
				}
			} else {
				throw new FileNotFoundException($path);
			}
		}
		$this->content = file_get_contents($this->getPath());
	}

	/**
	 * sets content
	 *
	 * @since 1.3.0
	 *
	 * @param string|null $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
		file_put_contents($this->getPath(), $content);
	}

	/**
	 * should the file created when not existing?
	 *
	 * @since 1.3.0
	 * @return boolean
	 */
	public function isCreateIfNotExists()
	{
		return $this->createIfNotExists;
	}

	/**
	 * sets if the file should be created when not existing
	 *
	 * @since 1.3.0
	 *
	 * @param boolean $createIfNotExists
	 */
	public function setCreateIfNotExists($createIfNotExists)
	{
		$this->createIfNotExists = $createIfNotExists;
	}
}