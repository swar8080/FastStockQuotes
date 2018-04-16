<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-14
 * Time: 12:15 PM
 */

namespace FastStockQuotes\tests\unit;

use FastStockQuotes\exceptions\InvalidExchangeCodeException;
use FastStockQuotes\StockSymbol;
use PHPUnit\Framework\TestCase;
use FastStockQuotes\Markets\ExchangeCodes as EC;

class StockSymbolTest extends TestCase {

	public function testValidSymbolWithExchangeCode(){
		$symbol = new StockSymbol("abc", EC::CANADA);

		$this->assertSame("ABC", $symbol->prefix());
		$this->assertSame(EC::CANADA, $symbol->exchangeCode());
		$this->assertSame("ABC" . "." . EC::CANADA, $symbol->fullSymbol());
	}

	public function testValidFullSymbolNonUS(){
		$symbol = new StockSymbol("abc.to");

		$this->assertSame("ABC", $symbol->prefix());
		$this->assertSame(EC::CANADA, $symbol->exchangeCode());
		$this->assertSame("ABC" . "." . EC::CANADA, $symbol->fullSymbol());
	}

	public function testValidFullSymbolUS(){
		$symbol = new StockSymbol("abc");

		$this->assertSame("ABC", $symbol->prefix());
		$this->assertSame(EC::US, $symbol->exchangeCode());
		$this->assertSame("ABC", $symbol->fullSymbol());
	}

	public function testInvalidFullSymbolMultipleCodes(){
		$this->expectException(InvalidExchangeCodeException::class);
		new StockSymbol("abc" . "." . EC::CANADA . ".invalid");
	}

	public function testInvalidExchangeCodesThrowException(){
		$this->expectException(InvalidExchangeCodeException::class);

		$badExchangeCode = "FAKE EXCHANGE CODE";

		try {
			new StockSymbol("abc", $badExchangeCode);
		}
		catch (InvalidExchangeCodeException $e) {
			$this->assertSame($badExchangeCode, $e->invalidExchangeCode());
			throw $e;
		}

	}

	public function testNullSymbolPrefixThrowsException(){
		$this->expectException(\InvalidArgumentException::class);
		new StockSymbol(null);
	}

	public function testEmptySymbolPrefixThrowsException(){
		$this->expectException(\InvalidArgumentException::class);
		new StockSymbol("");
	}

}