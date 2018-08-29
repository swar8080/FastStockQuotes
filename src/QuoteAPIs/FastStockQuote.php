<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-11
 * Time: 2:16 PM
 */

namespace FastStockQuotes\QuoteAPIs;


/**
 * Interface FastStockQuote
 *
 * Methods for accessing properties of both US and Non-US quotes
 *
 * @package FastStockQuotes\QuoteAPIs
 */
interface FastStockQuote {
	/**
	 * @return string
	 */
	public function symbol();

	/**
	 * @return float
	 */
	public function price();

	/**
	 * @return float
	 */
	public function open();

	/**
	 * @return float
	 */
	public function previousDayClose();

	/**
	 * @return float
	 */
	public function high();

	/**
	 * @return float
	 */
	public function low();

	/**
	 * @return int
	 */
	public function volume();

	/**
	 * unix timestamp of last update
	 * @return int
	 */
	public function lastUpdated();
}