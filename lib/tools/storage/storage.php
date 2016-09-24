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

namespace profenter\tools\storage;

/**
 * Class storage
 *
 * @package profenter\tools\storage
 * @method void save()
 * @method void render()
 * @since   1.0.0
 */
class storage
{
	/**
	 * contains the parsed storage settings
	 *
	 * @var array
	 * @since 1.3.0
	 */
	protected $content = [];
	/**
	 * @var file
	 * @since 1.3.0
	 */
	protected $file;

	/**
	 * alias for __construct
	 *
	 * @return \profenter\tools\storage\storage
	 * @since 1.0.0
	 */
	public static function init() : storage
	{
		return new static();
	}

	/**
	 * constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		if (!defined("DS")) {
			define("DS", DIRECTORY_SEPARATOR);
		}
		$this->file = call_user_func(static::setConfig(), new file());

		$this->render();
	}

	/**
	 * returns a settings value by name
	 *
	 * @param string $name name
	 *
	 * @return bool|array|string
	 * @since 1.0.0
	 */
	public function __get($name)
	{
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
	public function __set($name, $value)
	{
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
	public function __isset(string $name) : bool
	{
		return isset($this->content[$name]);
	}

	/**
	 * transforms all settings to a JSON string
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function __toString() : string
	{
		return json_encode($this->content);
	}

	/**
	 * returns file content
	 *
	 * @return array
	 * @since 1.3.0
	 */
	public function getContent(): array
	{
		return $this->content;
	}

	/**
	 * sets file content
	 *
	 * @param array $content
	 *
	 * @since 1.3.0
	 */
	public function setContent(array $content)
	{
		$this->content = $content;
	}
}