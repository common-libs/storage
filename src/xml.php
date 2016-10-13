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

use SimpleXMLElement;

/**
 * Class xml
 *
 * provides xml driver
 *
 * @package libs\storage
 * @since   1.3.0
 */
trait xml
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
			$parsed = simplexml_load_string($this->file->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);
			$parsed = json_decode(json_encode($parsed), true);
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
		$xml     = new SimpleXMLElement('<storage/>');
		$content = $this->getContent();
		array_to_xml($content, $xml);
		$this->file->setContent($xml->asXML());
	}
}


/**
 * migrates an array to xml object
 *
 * @param array            $array
 * @param SimpleXMLElement $xml
 *
 * @link  http://stackoverflow.com/a/5965940
 * @since 1.3.0
 */
function array_to_xml(array $array, SimpleXMLElement &$xml)
{
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			if (!is_numeric($key)) {
				$subnode = $xml->addChild("$key");
				array_to_xml($value, $subnode);
			} else {
				array_to_xml($value, $xml);
			}
		} else {
			$xml->addChild("$key", "$value");
		}
	}
}