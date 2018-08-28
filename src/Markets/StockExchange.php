<?php

namespace FastStockQuotes\Markets;

use FastStockQuotes\exceptions\InvalidExchangeCodeException;
use FastStockQuotes\utils\DateTime;

class StockExchange {

	public static function isValidExchangeCode($exchangeCode){
		return array_key_exists($exchangeCode, self::Exchanges);
	}

	/**
	 * @param $exchangeCode string
	 *
	 * @return StockExchange
	 * @throws InvalidExchangeCodeException
	 */
	public static function fromExchangeCode($exchangeCode){
		$exchangeCode = strtoupper($exchangeCode);
		if (!self::isValidExchangeCode($exchangeCode)){
			throw new InvalidExchangeCodeException($exchangeCode);
		}

		$e = self::Exchanges[$exchangeCode];

		return new StockExchange($exchangeCode, $e['name'], $e['timezone'], $e['opens'], $e['closes']);
	}

	private $code;
	private $fullName;
	private $timezone;
	private $opens;
	private $closes;

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
	private function __construct($code, $fullName, $timezone, $opens, $closes) {
		$this->code     = $code;
		$this->fullName = $fullName;
		$this->timezone = $timezone;
		$this->opens    = $opens;
		$this->closes   = $closes;
	}

	public function exchangeCode(){
		return $this->code;
	}

	public function fullName(){
		return $this->fullName;
	}

	public function isUS(){
		return $this->code == ExchangeCodes::US;
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
				$yesterdayClose = $closingTimeToday->sub(new \DateInterval("P1D"));
				return $yesterdayClose->getTimestamp();
			}
		}
		else {
			//get friday's close time
			$daysSinceFridayInterval = "P" . strval($dayOfWeek - 5) . "D";
			$fridayClose = $closingTimeToday->sub(new \DateInterval($daysSinceFridayInterval));
			return $fridayClose->getTimestamp();
		}
	}

	//hours and timezone from https://www.stockmarketclock.com/exchanges
	const Exchanges = [
		ExchangeCodes::US   => [
			'name' => 'NYSE/NASDAQ',
			'timezone' => 'America/New_York',
			'opens' => '9:30',
			'closes' => '16:00'
		],
		ExchangeCodes::AMSTERDAM => [
			'name' => 'Euronext Amsterdam',
			'timezone' => 'Europe/Amsterdam',
			'opens' => '9:00',
			'closes' => '17:40'
		],
		ExchangeCodes::AUSTRALIA  => [
			'name' => 'Australian Securities Exchange',
			'timezone' => 'Australia/Sydney',
			'opens' => '9:50',
			'closes' => '16:12'
		],
		ExchangeCodes::CANADA => [
			'name' => 'Toronto Stock Exchange',
			'timezone' => 'America/Toronto',
			'opens' => '9:30',
			'closes' => '16:00'
		],
		ExchangeCodes::GERMANY => [
			'name' => 'Frankfurt Stock Exchange',
			'timezone' => 'Europe/Berlin',
			'opens' => '8:00',
			'closes' => '20:00'
		],
		ExchangeCodes::HONG_KONG => [
			'name' => 'Hong Kong Stock Exchange',
			'timezone' => 'Asia/Hong_Kong',
			'opens' => '9:30',
			'closes' => '16:00'
		],
		ExchangeCodes::JAPAN  => [
			'name' => 'Tokyo Stock Exchange',
			'timezone' => 'Asia/Tokyo',
			'opens' => '9:00',
			'closes' => '15:00'
		],
		ExchangeCodes::LONDON  => [
			'name'	=> 'London Stock Exchange',
			'timezone' => 'Europe/London',
			'opens' => '8:00',
			'closes' => '16:30'
		],
		ExchangeCodes::NEW_ZEALAND => [
			'name' => 'New Zealand Stock Exchange',
			'timezone' => 'Pacific/Auckland',
			'opens' => '10:00',
			'closes' => '16:45'
		],
		ExchangeCodes::NORWAY => [
			'name' => 'Stockholm Stock Exchange',
			'timezone' => 'Europe/Oslo',
			'opens' => '9:00',
			'closes' => '17:30'
		],
		ExchangeCodes::PARIS => [
			'name' => 'Euronext Paris',
			'timezone' => 'Europe/Paris',
			'opens' => '9:00',
			'closes' => '17:30'
		],
		ExchangeCodes::SHANGHAI => [
			'name' => 'Shanghai Stock Exchange',
			'timezone' => 'Asia/Shanghai',
			'opens' => '9:30',
			'closes' => '15:00',
		],
		ExchangeCodes::SHENZHEN => [
			'name' => 'Shenzhen Stock Exchange',
			'timezone' => 'Asia/Shanghai',
			'opens' => '9:30',
			'closes' => '15:00'
		],
		ExchangeCodes::STOCKHOLM => [
			'name' => 'Stockholm Stock Exchange',
			'timezone' => 'Europe/Stockholm',
			'opens' => '9:00',
			'closes' => '17:30'
		]
	];
}
