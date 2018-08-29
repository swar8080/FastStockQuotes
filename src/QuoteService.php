<?php
namespace FastStockQuotes;

use FastStockQuotes\QuoteAPIs\FastStockQuote;
use FastStockQuotes\QuoteAPIs\NonUSQuote;

interface QuoteService {
	/**
	 * Used to retrieve stock quotes from third-party APIs for an array of @see StockSymbol
	 * Returned is an associative array mapping the StockSymbol's fullSymbol() to a @see FastStockQuote.
	 *
	 * Non-US symbols will be mapped to @see NonUSQuote and will contain:
	 * price, open, high, low, and volume for the latest trading day as well as the close from the previous trading day.
	 *
	 * US symbols will contain the same fields
	 * as well as all of the attributes listed at https://iextrading.com/developer/docs/#quote
	 *
	 * @param $symbols StockSymbol[]
	 *
	 * @return array[string:FastStockQuote]
	 */
	public function quotes($symbols);
}
?>

