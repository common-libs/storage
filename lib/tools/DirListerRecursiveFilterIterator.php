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

use RecursiveFilterIterator;

/**
 * Class DirListerRecursiveFilterIterator
 *
 * @package profenter\tools
 * @since   1.1.0
 */
class DirListerRecursiveFilterIterator extends RecursiveFilterIterator {
	/**
	 * which files should be filtered
	 *
	 * @var array
	 * @since   1.1.0
	 */
	public static $FILTERS = [
		'__MACOSX',
	];

	/**
	 * checks if the given file should be filtered
	 *
	 * @return bool
	 * @since   1.1.0
	 */
	public function accept() {
		return ! in_array( $this->current()
		                        ->getFilename(), self::$FILTERS, true );
	}
}