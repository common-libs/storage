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

use stdClass;

class common {
	/**
	 * transforms stdClass to array
	 *
	 * @param stdClass|array $array stdClass which should be transformed to array
	 *
	 * @since   1.1.0
	 * @return array
	 */
	public static function stdClassToArray( $array ) {
		if ( is_array( $array ) ) {
			foreach ( $array as $key => $value ) {
				if ( is_array( $value ) ) {
					$array[ $key ] = self::stdClassToArray( $value );
				}
				if ( $value instanceof stdClass ) {
					$array[ $key ] = self::stdClassToArray( (array) $value );
				}
			}
		}
		if ( $array instanceof stdClass ) {
			return self::stdClassToArray( (array) $array );
		}

		return $array;
	}
}