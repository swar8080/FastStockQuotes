<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-21
 * Time: 3:21 PM
 */

namespace unit\markets;


use FastStockQuotes\exceptions\InvalidExchangeCodeException;
use FastStockQuotes\markets\ExchangeCodes;
use FastStockQuotes\Markets\StockExchange;
use PHPUnit\Framework\TestCase;

class StockExchangeTest extends TestCase {

	public function testInvalidExchangeCode(){
		$this->expectException(InvalidExchangeCodeException::class);
		StockExchange::fromExchangeCode("invalid");
	}

	public function exchangeCodeCaseInsensitive(){
		$validExchangeCodeLowercase = strtolower(ExchangeCodes::CANADA);
		$exchange = StockExchange::fromExchangeCode($validExchangeCodeLowercase);
		$this->assertSame(ExchangeCodes::CANADA, $exchange->exchangeCode(), "lower case exchange code should be converted to match ExchangeCode case");

		$validExchangeCodeUppercase = strtoupper(ExchangeCodes::CANADA);
		$exchange = StockExchange::fromExchangeCode($validExchangeCodeUppercase);
		$this->assertSame(ExchangeCodes::CANADA, $exchange->exchangeCode(), "upper case exchange code should be converted to match ExchangeCode case");
	}

	public function testGetters(){
		$exchange = StockExchange::fromExchangeCode(ExchangeCodes::CANADA);

		$this->assertSame(ExchangeCodes::CANADA, $exchange->exchangeCode());
		$this->assertSame(false, $exchange->isUS());
		$this->assertSame("9:30", $exchange->opens());
		$this->assertSame("16:00", $exchange->closes());
		$this->assertSame("America/Toronto", $exchange->timezone());
		$this->assertSame("Toronto Stock Exchange", $exchange->fullName());
	}

}