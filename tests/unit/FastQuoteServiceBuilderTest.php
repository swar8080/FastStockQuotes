<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-15
 * Time: 10:42 PM
 */

namespace FastStockQuotes\Tests\unit;

use FastStockQuotes\FastQuoteServiceBuilder;
use FastStockQuotes\QuoteServiceCached;
use FastStockQuotes\QuoteServiceImpl;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Mockery;

class FastQuoteServiceBuilderTest extends TestCase {

	public function testWithRedisClientReturnsQuoteServiceCached(){
		$quoteService = FastQuoteServiceBuilder::builder()
		                                       ->withRedisCaching(Mockery::mock(Client::class))
		                                       ->build();

		$this->assertTrue($quoteService instanceof QuoteServiceCached, "should be instance of QuoteServiceCached");
	}

	public function testWithoutRedisClientReturnsQuoteServiceImpl(){
		$quoteService = FastQuoteServiceBuilder::builder()->build();

		$this->assertTrue($quoteService instanceof QuoteServiceImpl, "should be instance of QuoteServiceImpl");
	}

}

