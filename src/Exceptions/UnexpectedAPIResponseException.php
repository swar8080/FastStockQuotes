<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-15
 * Time: 3:30 PM
 */

namespace FastStockQuotes\Exceptions;

class UnexpectedAPIResponseException extends FastStockQuotesException {
	public function __construct($message = "", $code = 0) {
		parent::__construct($message, $code);
	}
}