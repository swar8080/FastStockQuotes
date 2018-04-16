<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-10
 * Time: 12:29 AM
 */

namespace FastStockQuotes\exceptions;

use Throwable;
use FastStockQuotes\utils\ArrayUtils;

class FastStockQuotesException extends \Exception{

	const AV_ERROR_RESPONSE_CODE = 100;
	const AV_UNKNOWN_ERROR_RESPONSE_CODE = 101;

	const IEX_ERROR_RESPONSE_CODE = 110;
	const IEX_UNKNOWN_ERROR_RESPONSE_CODE = 111;
	const IEX_RESPONSE_MISSING_FIELDS = 112;

	const INVALID_STOCK_SYMBOLS = 200;
	const INVALID_EXCHANGE_CODE = 201;
	const UNSUPPORTED_STOCK_SYMBOL = 202;

	private $failedSymbols;

	public function __construct( $message = "", $code = 0, Throwable $previous = null, $stockSymbols=null ) {
		$this->failedSymbols = $stockSymbols;

		if ($stockSymbols !== null){
			$customMessage = "Error retrieving stock quote(s) for: ";

			$symbols = ArrayUtils::map($stockSymbols, function($stockSymbol){
				return $stockSymbol->fullSymbol();
			});
			$customMessage .= '"' . implode(", ", $symbols) . '"';

			if ($previous !== null){
				$customMessage .= "\nCause: " . $previous->getMessage();
			}
			elseif ($message !== null){
				$customMessage .= ":\n\t" . implode("\n\t", explode("\n", $message));
			}

			$message = $customMessage;
		}

		parent::__construct( $message, $code, $previous );
	}

	public function failedSymbols(){
		return $this->failedSymbols;
	}
}