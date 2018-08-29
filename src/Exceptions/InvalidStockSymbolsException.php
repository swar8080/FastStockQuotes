<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-12
 * Time: 11:09 AM
 */

namespace FastStockQuotes\Exceptions;

use FastStockQuotes\Utils\ArrayUtils;

class InvalidStockSymbolsException extends FastStockQuotesException {

	private $invalidSymbols;

	/**
	 * InvalidStockSymbol constructor.
	 *
	 * @param $invalidSymbols StockSymbol[]
	 */
	public function __construct($invalidSymbols) {
		$this->invalidSymbols = $invalidSymbols;

		$fullSymbols = ArrayUtils::map($invalidSymbols, function($symbol){
			return $symbol->fullSymbol();
		});

		$errorMessage = "Invalid stock symbols: " . implode(", ", $fullSymbols);

		parent::__construct( $message=$errorMessage, $code=FastStockQuotesException::INVALID_STOCK_SYMBOLS, $previous=null, $stockSymbols=$invalidSymbols);
	}

	public function symbols(){
		return $this->invalidSymbols;
	}

}