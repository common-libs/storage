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

namespace common\storage;

use common\storage\exception\FileNotFoundException;


/**
 * Class file
 *
 * @package common\storage
 * @since   1.3.0
 */
class file {
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
	/**
	 * @var bool
	 */
	protected $createDefault = false;

	public function __construct() {

	}

	/**
	 * returns plain content
	 *
	 * @since 1.3.0
	 * @return null|string
	 */
	public function getContent() {
		if (!$this->isCache()) {
			$this->content = file_get_contents($this->getPath());
		}

		return $this->content;
	}

	/**
	 * sets content
	 *
	 * @since 1.3.0
	 *
	 * @param string|null $content
	 */
	public function setContent($content) {
		$this->content = $content;
		file_put_contents($this->getPath(), $content);
	}

	/**
	 * check if cache is enabled
	 *
	 * @since 1.3.0
	 * @return boolean
	 */
	public function isCache(): bool {
		return $this->cache;
	}

	/**
	 * set cache is enabled
	 *
	 * @since 1.3.0
	 *
	 * @param boolean $cache
	 */
	public function setCache(bool $cache) {
		$this->cache = $cache;
	}

	/**
	 * returns file path
	 *
	 * @since 1.3.0
	 * @return string
	 */
	public function getPath(): string {
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
	public function setPath(string $path) {
		$this->path = $path;
		if (!is_file($path)) {
			if ($this->isCreateIfNotExists()) {
				$dir = dirname($path);
				if (!is_dir($dir)) {
					mkdir($dir, 0770, true);
				}
				touch($path);
				$this->setCreateDefault(true);
				if (!is_file($path)) {
					throw new FileNotFoundException($path);
				}
			}
			else {
				throw new FileNotFoundException($path);
			}
		}
		$this->content = file_get_contents($this->getPath());
	}

	/**
	 * should the file created when not existing?
	 *
	 * @since 1.3.0
	 * @return boolean
	 */
	public function isCreateIfNotExists(): bool {
		return $this->createIfNotExists;
	}

	/**
	 * sets if the file should be created when not existing
	 *
	 * @since 1.3.0
	 *
	 * @param boolean $createIfNotExists
	 */
	public function setCreateIfNotExists(bool $createIfNotExists) {
		$this->createIfNotExists = $createIfNotExists;
	}

	/**
	 * @return bool
	 */
	public function isCreateDefault(): bool {
		return $this->createDefault;
	}

	/**
	 * @param bool $createDefault
	 */
	public function setCreateDefault(bool $createDefault) {
		$this->createDefault = $createDefault;
	}
}