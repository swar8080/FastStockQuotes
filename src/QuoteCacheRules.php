<?php
namespace FastStockQuotes;

use FastStockQuotes\Markets\StockExchange;
use FastStockQuotes\QuoteAPIs\FastStockQuote;

class QuoteCacheRules {
	/**
	 * This implementation will not cache
	 * if the market is closed but the API isn't up-to-date
	 * - otherwise it could incorrectly cache a stale price until next open
	 *
	 * @param $lastUpdatedUnixTime int
	 * @param $exchange StockExchange
	 * @param $currentTime int
	 *
	 * @return boolean
	 */
	public function shouldCacheQuote($lastUpdatedUnixTime, $exchange, $currentTime=null){
		$currentTime = ($currentTime)? $currentTime : time();
		$lastCloseTime = $exchange->timestampOfLastClose();

		$isCurrentTradingDayOver = $currentTime >= $lastCloseTime;

		return !$isCurrentTradingDayOver || $lastUpdatedUnixTime >= $lastCloseTime;
	}
}