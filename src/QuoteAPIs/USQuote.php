<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-10
 * Time: 12:14 AM
 */

namespace FastStockQuotes\QuoteAPIs;

class USQuote implements FastStockQuote {

	private $symbol;
	private $price;
	private $open;
	private $previousDayClose;
	private $high;
	private $low;
	private $volume;
	private $lastUpdated;

	const KNOWN_IEX_JSON_KEYS = ["symbol", "latestPrice", "open", "close", "high", "low", "latestVolume", "latestUpdate"];

	/**
	 * @param $kvps array[string: mixed]
	 *
	 * @return USQuote
	 */
	public static function fromAssocArray($kvps){

		$iexQuote = new USQuote();

		$iexQuote->symbol = $kvps["symbol"];
		$iexQuote->price = floatval($kvps["latestPrice"]);
		$iexQuote->open = floatval($kvps["open"]);
		$iexQuote->previousDayClose = floatval($kvps["close"]);
		$iexQuote->high = floatval($kvps["high"]);
		$iexQuote->low = floatval($kvps["low"]);
		$iexQuote->volume = intval($kvps["latestVolume"]);

		$iexQuote->lastUpdated = intval($kvps["latestUpdate"] / 1000);

		$otherKeys = array_diff_key($kvps, self::KNOWN_IEX_JSON_KEYS);
		foreach ($otherKeys as $key => $value){
			$iexQuote->{$key} = $value;
		}

		return $iexQuote;
	}

	private function __construct() {
	}

	/**
	 * @return string
	 */
	public function symbol() {
		return $this->symbol;
	}

	/**
	 * @return float
	 */
	public function price() {
		return $this->price;
	}

	/**
	 * @return float
	 */
	public function open() {
		return $this->open;
	}

	/**
	 * @return float
	 */
	public function previousDayClose() {
		return $this->previousDayClose;
	}

	/**
	 * @return float
	 */
	public function high() {
		return $this->high;
	}

	/**
	 * @return float
	 */
	public function low() {
		return $this->low;
	}

	/**
	 * @return int
	 */
	public function volume() {
		return $this->volume;
	}

	/**
	 * @return int
	 */
	public function lastUpdated() {
		return $this->lastUpdated;
	}
}
