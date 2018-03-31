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

/**
 * Class storage
 *
 * @package common\storage
 * @method void save()
 * @method void render()
 * @since   1.0.0
 */
abstract class Storage {
	/**
	 * contains the parsed storage settings
	 *
	 * @var array
	 * @since 0.2.1
	 */
	protected $content = [];
	/**
	 * @var options
	 * @since 0.2.1
	 */
	protected $file;

	/**
	 * alias for __construct
	 *
	 * @return Storage
	 * @since 1.0.0
	 */
	public static function init(): Storage {
		return new static();
	}

	/**
	 * constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if (!defined("DS")) {
			define("DS", DIRECTORY_SEPARATOR);
		}
		$file = new Options();
		static::setConfig($file);
		$this->file = $file;
		$this->render();
		if (method_exists($this, "setDefaults") && $file->isCreateDefault()) {
			$this->setDefaults($this);
		}
	}

	abstract static function setConfig(Options $options);

	/**
	 * returns a settings value by name
	 *
	 * @param string $name name
	 *
	 * @return bool|array|string
	 * @since 1.0.0
	 */
	public function __get($name) {
		if (isset($this->content[$name])) {
			return $this->content[$name];
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
	public function __set($name, $value) {
		$this->content[$name] = $value;
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
	public function __isset(string $name): bool {
		return isset($this->content[$name]);
	}

	/**
	 * transforms all settings to a JSON string
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function __toString(): string {
		return json_encode($this->content);
	}

	/**
	 * returns file content
	 *
	 * @return array
	 * @since 0.2.1
	 */
	public function getContent(): array {
		return $this->content;
	}

	/**
	 * sets file content
	 *
	 * @param array $content
	 *
	 * @since 0.2.1
	 */
	public function setContent(array $content) {
		$this->content = $content;
	}
}