<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-15
 * Time: 12:04 AM
 */

namespace FastStockQuotes;

use FastStockQuotes\quoteAPIs\FastStockQuote;
use Predis\Client;

class QuoteCache {

	private $redis;
	private $timeoutSeconds;

	const MIN_TIMEOUT_SECONDS = 60;

	/**
	 * QuoteCache constructor.
	 *
	 * @param $redis Client
	 * @param $cacheTimeoutSeconds int
	 */
	public function __construct($redis, $cacheTimeoutSeconds=self::MIN_TIMEOUT_SECONDS) {
		$this->redis           = $redis;
		$this->timeoutSeconds  = max($cacheTimeoutSeconds, self::MIN_TIMEOUT_SECONDS);
	}

	/**
	 * @param $symbol StockSymbol
	 * @param $quote FastStockQuote
	 */
	public function cacheQuote($symbol, $quote){
		$key = self::quoteKey($symbol);
		$serializedQuote = serialize($quote);

		if ($symbol->exchange()->isOpen()){
			$this->redis->set($key, $serializedQuote,  "ex", $this->timeoutSeconds);
		}
		else {
			$this->redis->set($key, $serializedQuote, "ex", $symbol->exchange()->secondsUntilNextOpen());
		}
	}

	/**
	 * @param $symbol StockSymbol
	 *
	 * @return FastStockQuote|null
	 */
	public function getCachedQuote($symbol){
		$key = self::quoteKey($symbol);
		$serializedQuote = $this->redis->get($key);
		return ($serializedQuote !== null)? unserialize($serializedQuote) : null;
	}

	/**
	 * @param $symbol \FastStockQuotes\StockSymbol
	 *
	 * @return string
	 */
	private static function quoteKey($symbol){
		return "q:" . $symbol->fullSymbol();
	}
}