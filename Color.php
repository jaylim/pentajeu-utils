<?php

namespace pentajeu\utils;

class Color
{
	/**
	 * Convert html color code into array
	 * @param string $color_code html color code
	 * @return array array length of 3 (red, green and blue)
	 */
	public static function HTMLToArray($color_code)
	{
		if (!is_string($color_code))
			throw new \Exception("\$color_code has to be a string");

		$len = strlen($color_code);
		$validate = array(
			'length' => in_array($len, array(4, 7)),
			'pattern' => preg_match('/#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})/', $color_code),
			);
		$split_len = ($len == 4)?1:2;

		foreach ($validate as $key => $v)
			if (!$v)
				throw new \Exception("\$color_code is not a valid html color code ($key)");

		$str = substr($color_code, 1);
		$result = str_split($str, $split_len);

		foreach ($result as $key => $c) {
			if ($len == 4) $c .= $c;
			$result[$key] = intval($c, 16);
		}

		return $result;
	}
}
