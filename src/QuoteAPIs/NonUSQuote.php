<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-10
 * Time: 12:14 AM
 */

namespace FastStockQuotes\QuoteAPIs;

class NonUSQuote implements FastStockQuote {

	private $symbol;
	private $price;
	private $open;
	private $previousDayClose;
	private $volume;
	private $low;
	private $high;
	private $lastUpdated;

	const LAST_UPDATE_TIMEZONE = "America/New_York";

	/**
	 * InternationalQuote constructor.
	 *
	 * @param $price float
	 * @param $open float
	 * @param $previousDayClose float
	 * @param $volume int
	 * @param $low float
	 * @param $high float
	 */
	public function __construct( $symbol, $price, $open, $previousDayClose, $volume, $low, $high ) {
		$this->symbol           = $symbol;
		$this->price            = $price;
		$this->open             = $open;
		$this->previousDayClose = $previousDayClose;
		$this->volume           = $volume;
		$this->low              = $low;
		$this->high             = $high;

		//todo determine if possible to find out delay on AV quotes
		$this->lastUpdated = time();
	}

	public function symbol(){
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
	 * @return int
	 */
	public function volume() {
		return $this->volume;
	}

	/**
	 * @return float
	 */
	public function low() {
		return $this->low;
	}

	/**
	 * @return float
	 */
	public function high() {
		return $this->high;
	}

	/**
	 * @return int
	 */
	public function lastUpdated() {
		return $this->lastUpdated;
	}
}