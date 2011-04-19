<?php
/**
 * Static methods for operations on strings
 * @author johnbyrne
 *
 */
class String
{
	public static function prepareValueForDB($string)
	{
		$search = array('/', ' ', ':');
		$replace = array('_', '_', '');
		$val = str_replace($search, $replace, strtolower($string));

		$string = trim(preg_replace("/\n\r|\r\n|\n|\r|\t/", "", $string));
		$string = preg_replace("/\s\s+/", " ", $string);

		return $string;
	}

	public static function prepareFieldNameForDB($string)
	{
		$string = self::prepareValueForDB($string);
		$search = array('-', ' ');
		$replace = array('_', '_');
		$val = str_replace($search, $replace, strtolower($string));

		return $val;
	}
}