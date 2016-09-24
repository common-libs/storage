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
class cdn
{
	/**
	 * @var string
	 */
	protected $apibase = "https://cdn.profenter.de/api/";
	/**
	 * @var string
	 */
	protected $base = "https://cdn.profenter.de/";
	/**
	 * @var bool|string
	 */
	protected $width = false;
	/**
	 * @var  bool|string
	 */
	protected $height = false;

	/**
	 * @return bool|string
	 */
	public function getWidth()
	{
		return $this->width;
	}

	/**
	 * @return bool|string
	 */
	public function getHeight()
	{
		return $this->height;
	}

	/**
	 * @param  string|array $url
	 *
	 * @return \profenter\tools\cdn
	 */
	public static function init($url)
	{
		return new self($url);
	}

	/**
	 * cdn constructor.
	 *
	 * @param string|array $url
	 */
	public function __construct($url)
	{
		$this->url = $url;
		cacheManager::setFile();
	}

	/**
	 * @return bool
	 */
	public function check()
	{
		$cache = cacheManager::$getCacheInstance;
		$test  = $cache->get(md5("profentercdntestmode"));
		if (is_null($test)) {
			$test = json_decode(file_get_contents("http://cdn.profenter.de/api/" . json_encode([
					"version"  => "2",
					"testmode" => true
				])), true);
			$cache->set("profentercdntestmode", $test);
		}
		if ($test["working"] == "true") {
			return true;
		}

		return false;
	}

	/**
	 * @param bool $keyname
	 *
	 * @return string
	 */
	public function getImage($keyname = false)
	{
		if (is_array($this->url)) {
			$re = [
				$keyname => [
					"url"    => $this->url[$keyname],
					"width"  => $this->getWidth(),
					"height" => $this->getHeight()
				]
			];
		} else {
			$re = [
				1 => [
					"url"    => $this->url,
					"width"  => $this->getWidth(),
					"height" => $this->getHeight()
				]
			];
		}

		return $this->getIncUrl($re);
	}

	/**
	 * @param bool $keyname
	 *
	 * @return string
	 */
	public function getUrl($keyname = false)
	{
		if (is_array($this->url)) {
			$re = [];
			foreach ($this->url as $key => $item) {
				$re[$key] = [
					"url"    => $item,
					"width"  => $this->getWidth(),
					"height" => $this->getHeight(),
					"get"    => [
						"sri"  => "sri",
						"type" => "type"
					]
				];
			}
			$request = $this->doRequest($re);
			print_r($request);

			return $this->base . $request[$keyname]["filestring"];
		} else {
			$request = $this->doRequest([
				1 => [
					"url"    => $this->url,
					"width"  => $this->getWidth(),
					"height" => $this->getHeight(),
					"get"    => [
						"sri"  => "sri",
						"type" => "type"
					]
				]
			]);

			return $this->base . $request[1]["filestring"];
		}
	}

	/**
	 * @return string
	 */
	public function getHtml()
	{
		if (is_array($this->url)) {
			$re = [];
			foreach ($this->url as $key => $item) {
				$re[$key] = [
					"url" => $item,
					"get" => [
						"sri"  => "sri",
						"type" => "type"
					]
				];
			}
			$request = $this->doRequest($re);
			$html    = "";
			foreach ($request as $item) {
				if ($item["get"]["type"] == "text/javascript") {
					$html .= '<script src="' . $this->base . $item["filestring"] . '" integrity="' . $item["get"]["sri"] . '" crossorigin="anonymous"></script>';
				}
				if ($request[1]["get"]["type"] == "text/css") {
					$html .= '<link  href="' . $this->base . $item["filestring"] . '" rel="stylesheet" integrity="' . $item["get"]["sri"] . '" crossorigin="anonymous">';
				}
			}

			return $html;
		} else {
			$request = $this->doRequest([
				1 => [
					"url" => $this->url,
					"get" => [
						"sri"  => "sri",
						"type" => "type"
					]
				]
			]);
			if ($request[1]["get"]["type"] == "text/javascript") {
				return '<script src="' . $this->base . $request[1]["filestring"] . '" integrity="' . $request[1]["get"]["sri"] . '" crossorigin="anonymous"></script>';
			}
			if ($request[1]["get"]["type"] == "text/css") {
				return '<link  href="' . $this->base . $request[1]["filestring"] . '" rel="stylesheet"  integrity="' . $request[1]["get"]["sri"] . '" crossorigin="anonymous">';
			}
		}
	}

	/**
	 * @param $width
	 *
	 * @return $this
	 */
	public function setWidth($width)
	{
		$this->width = $width;

		return $this;
	}

	/**
	 * @param $height
	 *
	 * @return $this
	 */
	public function setHeight($height)
	{
		$this->height = $height;

		return $this;
	}

	/**
	 * @param $a
	 *
	 * @return array
	 */
	protected function doRequest($a)
	{
		$cache    = cacheManager::$getCacheInstance;
		$newArray = [
			"version" => 2,
			"items"   => []
		];
		$res      = [];
		foreach ($a as $key => $item) {
			$jsonarray = $cache->get(md5($item["url"] . $item["width"] . $item["height"]));
			if (is_null($jsonarray) or !$cache) {
				$newArray["items"][$key] = $item;
				if (!isset($newArray["items"][$key]["get"])) {
					$newArray["items"][$key]["get"] = [];
				}
				$newArray["items"][$key]                  = $item;
				$newArray["items"][$key]["get"]["orgurl"] = "url";
			} else {
				$res[$key] = $jsonarray;
			}
		}
		if (!empty($newArray["items"])) {
			print_r($this->apibase . json_encode($newArray));
			$result = json_decode(file_get_contents($this->apibase . json_encode($newArray)), true);
			if (!empty($result)) {
				foreach ($result as $key => $item) {
					$cache->set(md5($item["get"]["orgurl"]), $item, 3600 * 24);
					$res[$key] = $item;
				}
			}
		}

		return $res;
	}

	/**
	 * @param $a
	 *
	 * @return string
	 */
	protected function getIncUrl($a)
	{
		$newArray = [
			"version" => 2,
			"items"   => $a
		];

		return $this->apibase . json_encode($newArray);
	}
}