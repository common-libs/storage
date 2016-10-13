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
 * Class serialize
 *
 * provides serialize driver
 *
 * @package libs\storage
 * @since   1.3.0
 */
trait serialize
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
			$parsed = unserialize($this->file->getContent());
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
		$this->file->setContent(serialize($this->getContent()));
	}
}