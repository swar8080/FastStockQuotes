<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-09
 * Time: 11:54 PM
 */

namespace FastStockQuotes\Utils;


class ArrayUtils {

	public static function map($array, $callback){
		$mapped = array();
		foreach ($array as $elem){
			array_push($mapped, $callback($elem));
		}
		return $mapped;
	}

	public static function allKeysExist($array, $keys){
		foreach ($keys as $key){
			if (!array_key_exists($key, $array))
				return false;
		}
		return true;
	}

}