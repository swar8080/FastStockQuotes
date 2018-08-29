<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-14
 * Time: 12:30 PM
 */

namespace FastStockQuotes\Exceptions;


use Throwable;

class InvalidExchangeCodeException extends FastStockQuotesException {

	private $invalidExchangeCode;

	public function __construct($exchangeCode) {
		$this->invalidExchangeCode = $exchangeCode;
		$errorMessage = "Invalid exchange code: " . $exchangeCode;
		parent::__construct( $message=$errorMessage, $code=FastStockQuotesException::INVALID_EXCHANGE_CODE, $previous=null, $stockSymbols=null);
	}

	public function invalidExchangeCode(){
		return $this->invalidExchangeCode;
	}

}