<?php

namespace FastStockQuotes\Utils;

//Overrides the global DateTime class to allow mocking time
class DateTime extends \DateTime {

	//a queue of DateTime constructor arguements to be used in subsequent calls to new DateTime($time, $timezone)
	public static $mockDateTimeArguementQueue = array();

	public function __construct($time="now", $timezone=null){
		//deque the next mocked arguements, if any
		$mockArgs = array_shift(self::$mockDateTimeArguementQueue);

		if ($mockArgs !== null){
			parent::__construct(...$mockArgs);
		}
		else {
			parent::__construct($time, $timezone);
		}
	}
}