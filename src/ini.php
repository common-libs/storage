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
namespace libs\storage;

/**
 * Class ini
 *
 * provides ini driver
 *
 * @package libs\storage
 * @since   1.3.0
 */
trait ini
{
	use driver;

	/**
	 * render method
	 *
	 * @since 1.3.0
	 */
	protected function render()
	{
		if (empty($this->getContent())) {
			$parsed = parse_ini_string($this->file->getContent(), true);
			$this->setContent(is_array($parsed) ? $parsed : []);
		}
	}

	/**
	 * save method
	 *
	 * @since 1.3.0
	 */
	protected function save()
	{
		$this->file->setContent(arr2ini($this->getContent()));
	}
}

/**
 * migrates an array ton an ini string
 *
 * @link  http://stackoverflow.com/a/17317168
 *
 * @param array $a      start array
 * @param array $parent parent array, used for recursion
 *
 * @since 1.3.0
 * @return string
 */
function arr2ini(array $a, array $parent = []) : string
{
	$out = '';
	foreach ($a as $k => $v) {
		if (is_array($v)) {
			//subsection case
			//merge all the sections into one array...
			$sec = array_merge((array)$parent, (array)$k);
			//add section information to the output
			$out .= '[' . join('.', $sec) . ']' . PHP_EOL;
			//recursively traverse deeper
			$out .= arr2ini($v, $sec);
		} else {
			//plain key->value case
			$out .= "$k=$v" . PHP_EOL;
		}
	}

	return $out;
}