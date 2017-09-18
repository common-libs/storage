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

use common\io\File;
use common\storage\exception\FileNotFoundException;

/**
 * Class file
 *
 * @package common\storage
 * @since   0.2.1
 */
class Options {
	/**
	 * @var File
	 * @since 0.2.1
	 */
	protected $path;
	/**
	 * @var null
	 * @since 0.2.1
	 */
	protected $content = NULL;
	/**
	 * @var boolean
	 * @since 0.2.1
	 */
	protected $createIfNotExists = true;
	/**
	 * file constructor.
	 *
	 * @since 0.2.1
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
	 * @since 0.2.1
	 * @return null|string
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * sets content
	 *
	 * @since 0.2.1
	 *
	 * @param string|null $content
	 */
	public function setContent($content) {
		$this->content = $content;
		$this->getPath()->write($content);
	}


	/**
	 * returns file path
	 *
	 * @since 0.2.1
	 * @return File
	 */
	public function getPath()
	: File {
		return $this->path;
	}

	/**
	 * sets file path
	 *
	 * @param File $path file path
	 *
	 * @since 0.2.1
	 * @throws FileNotFoundException
	 */
	public function setPath(File $path) {
		$this->path = $path;
		if (!$path->isFile()) {
			if ($this->isCreateIfNotExists()) {
				$path->getDirectory()->mkdir();
				$path->write("");
				$this->setCreateDefault(true);
				if (!$path->isFile()) {
					throw new FileNotFoundException($path);
				}
			} else {
				throw new FileNotFoundException($path);
			}
		}
		$this->content = $path->getContent();
	}

	/**
	 * should the file created when not existing?
	 *
	 * @since 0.2.1
	 * @return boolean
	 */
	public function isCreateIfNotExists()
	: bool {
		return $this->createIfNotExists;
	}

	/**
	 * sets if the file should be created when not existing
	 *
	 * @since 0.2.1
	 *
	 * @param boolean $createIfNotExists
	 */
	public function setCreateIfNotExists(bool $createIfNotExists) {
		$this->createIfNotExists = $createIfNotExists;
	}

	/**
	 * @return bool
	 */
	public function isCreateDefault()
	: bool {
		return $this->createDefault;
	}

	/**
	 * @param bool $createDefault
	 */
	public function setCreateDefault(bool $createDefault) {
		$this->createDefault = $createDefault;
	}
}