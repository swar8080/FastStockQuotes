<?php

namespace FastStockQuotes\Markets;

use FastStockQuotes\exceptions\InvalidExchangeCodeException;
use FastStockQuotes\utils\DateTime;

class StockExchange {

	public static function  isValidExchangeCode($exchangeCode){
		return array_key_exists($exchangeCode, self::Exchanges);
	}

	/**
	 * @param $exchangeCode string
	 *
	 * @return StockExchange
	 * @throws InvalidExchangeCodeException
	 */
	public static function fromExchangeCode($exchangeCode){
		if (!self::isValidExchangeCode($exchangeCode)){
			throw new InvalidExchangeCodeException($exchangeCode);
		}

		$e = self::Exchanges[$exchangeCode];

		return new StockExchange($exchangeCode, $e['name'], $e['timezone'], $e['opens'], $e['closes'], $e['isUS']);
	}

	private $code;
	private $fullName;
	private $timezone;
	private $opens;
	private $closes;
	private $isUS;

	/**
	 * StockExchange constructor.
	 *
	 * @param code
	 * @param $fullName
	 * @param $timezone
	 * @param $opens
	 * @param $closes
	 * @param $isUS
	 */
	public function __construct($code, $fullName, $timezone, $opens, $closes, $isUS ) {
		if (!self::isValidExchangeCode($code)){
			throw new \InvalidArgumentException("Invalid exchange code: " . $code);
		}

		$this->code     = $code;
		$this->fullName = $fullName;
		$this->timezone = $timezone;
		$this->opens    = $opens;
		$this->closes   = $closes;
		$this->isUS     = $isUS;
	}

	public function exchangeCode(){
		return $this->code;
	}

	public function fullName(){
		return $this->fullName;
	}

	public function isUS(){
		return $this->isUS;
	}

	public function opens(){
		return $this->opens;
	}

	public function closes(){
		return $this->closes;
	}

	public function timezone(){
		return $this->timezone;
	}

	public function isOpen(){
		$localTime = new DateTime('now', new \DateTimeZone($this->timezone));

		//closed if its the weekend
		if (intval($localTime->format("N")) >= 6){
			return false;
		}

		$openTime = new DateTime($this->opens, new \DateTimeZone($this->timezone));
		$closeTime = new DateTime($this->closes, new \DateTimeZone($this->timezone));

		return $localTime >= $openTime && $localTime <= $closeTime;
	}

	public function secondsUntilNextOpen(){
		$localDatetime = new DateTime('now', new \DateTimeZone($this->timezone));

		$dayOfWeek = $localDatetime->format("N");
		$todayDayName = jddayofweek(intval($dayOfWeek)%7 - 1, 1);

		$currentTime = $localDatetime->getTimestamp();
		$todayOpenTime = (new DateTime($todayDayName . " " . $this->opens, new \DateTimeZone($this->timezone)))->getTimestamp();
		$tomorrowOpenTime = $todayOpenTime + 60*60*24;

		if ($dayOfWeek >= 6 || ($dayOfWeek == 5 && $currentTime > $todayOpenTime)) { //weekend or after close on friday opens next on monday
			$nextOpen = (new DateTime( "monday " . $this->opens, new \DateTimeZone($this->timezone)))->getTimestamp();
		}
		else if ($currentTime > $todayOpenTime){ //monday-thursday, market has already opened and will open at same time tomorrow
			$nextOpen = $tomorrowOpenTime;
		}
		else { //monday-thursday, market will open later today
			$nextOpen = $todayOpenTime;
		}

		return $nextOpen - $currentTime;
	}

	/**
	 * Return unix time of last close time
	 * @return int
	 */
	public function timestampOfLastClose(){
		$localDateTime = new DateTime('now', new \DateTimeZone($this->timezone));
		$dayOfWeek = intval($localDateTime->format("N"));

		$closingTimeToday = new DateTime($this->closes, new \DateTimeZone($this->timezone));


		if ($dayOfWeek < 6){
			$closedToday = $localDateTime >= $closingTimeToday;

			if ($closedToday){
				//return closing time from earlier today
				return $closingTimeToday->getTimestamp();
			}
			else {
				//return yesterday's close
				return ($closingTimeToday->sub(new \DateInterval("P1D")))->getTimestamp();
			}
		}
		else {
			//get friday's close time
			$daysSinceFridayInterval = "P" . strval($dayOfWeek - 5) . "D";
			return ($closingTimeToday->sub(new \DateInterval($daysSinceFridayInterval)))->getTimestamp();
		}
	}

	//hours and timezone from https://www.stockmarketclock.com/exchanges
	const Exchanges = [
		ExchangeCodes::US   => [
			'symbolSuffix' => '',
			'name' => 'NYSE/NASDAQ',
			'timezone' => 'America/New_York',
			'opens' => '9:30',
			'closes' => '16:00',
			'isUS' => true
		],
		ExchangeCodes::AMSTERDAM => [
			'name' => 'Euronext Amsterdam',
			'timezone' => 'Europe/Amsterdam',
			'opens' => '9:00',
			'closes' => '17:40',
			'isUS' => false
		],
		ExchangeCodes::AUSTRALIA  => [
			'name' => 'Australian Securities Exchange',
			'timezone' => 'Australia/Sydney',
			'opens' => '9:50',
			'closes' => '16:12',
			'isUS' => false
		],
		ExchangeCodes::CANADA => [
			'name' => 'Toronto Stock Exchange',
			'timezone' => 'America/Toronto',
			'opens' => '9:30',
			'closes' => '16:00',
			'isUS' => false
		],
		ExchangeCodes::GERMANY => [
			'name' => 'Frankfurt Stock Exchange',
			'timezone' => 'Europe/Berlin',
			'opens' => '8:00',
			'closes' => '20:00',
			'isUS' => false
		],
		ExchangeCodes::HONG_KONG => [
			'name' => 'Hong Kong Stock Exchange',
			'timezone' => 'Asia/Hong_Kong',
			'opens' => '9:30',
			'closes' => '16:00',
			'isUS' => false
		],
		ExchangeCodes::JAPAN  => [
			'name' => 'Tokyo Stock Exchange',
			'timezone' => 'Asia/Tokyo',
			'opens' => '9:00',
			'closes' => '15:00',
			'isUS' => false
		],
		ExchangeCodes::LONDON  => [
			'name'	=> 'London Stock Exchange',
			'timezone' => 'Europe/London',
			'opens' => '8:00',
			'closes' => '16:30',
			'isUS' => false
		],
		ExchangeCodes::NEW_ZEALAND => [
			'name' => 'New Zealand Stock Exchange',
			'timezone' => 'Pacific/Auckland',
			'opens' => '10:00',
			'closes' => '16:45',
			'isUS' => false
		],
		ExchangeCodes::PARIS => [
			'name' => 'Euronext Paris',
			'timezone' => 'Europe/Paris',
			'opens' => '9:00',
			'closes' => '15:30',
			'isUs' => false
		],
		ExchangeCodes::SHANGHAI => [
			'name' => 'Shanghai Stock Exchange',
			'timezone' => 'Asia/Shanghai',
			'opens' => '9:30',
			'closes' => '15:00',
			'isUs' => false
		],
		ExchangeCodes::SHENZHEN => [
			'name' => 'Shenzhen Stock Exchange',
			'timezone' => 'Asia/Shanghai',
			'opens' => '9:30',
			'closes' => '15:00',
			'isUs' => false
		]
	];
}
