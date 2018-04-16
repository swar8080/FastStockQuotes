<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-15
 * Time: 3:10 PM
 */

namespace FastStockQuotes\exceptions;

use FastStockQuotes\utils\ArrayUtils;

class UnsupportedStockSymbolsException extends FastStockQuotesException {

	public function __construct($unsupportedSymbols) {
		$errorMessage = "The following Non-US quotes require AlphaVantage's API: ";

		$symbols = ArrayUtils::map($unsupportedSymbols, function($symbol){
			return $symbol->fullSymbol();
		});

		$errorMessage .= implode(", ", $symbols);
		$errorMessage .=  "\n" . 'See FastQuoteServiceBuilder->withAlphaVantageGlobalQuoteAPI($apiKey) to add support';
		$errorMessage .= "\n";

		parent::__construct($message=$errorMessage, $code=FastStockQuotesException::UNSUPPORTED_STOCK_SYMBOL, $previous=null, $symbols=$unsupportedSymbols);
	}
}