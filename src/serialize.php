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
 * Class serialize
 *
 * provides serialize driver
 *
 * @package common\storage
 * @since   0.2.1
 */
trait serialize
{
	use driver;

	/**
	 * render method
	 *
	 * @since 0.2.1
	 */
	protected function render()
	{
		if (empty($this->getContent())) {
			$parsed = unserialize($this->file->getContent());
			$this->setContent(is_array($parsed) ? $parsed : []);
		}
	}

	/**
	 * save method
	 *
	 * @since 0.2.1
	 */
	protected function save()
	{
		$this->file->setContent(serialize($this->getContent()));
	}
}