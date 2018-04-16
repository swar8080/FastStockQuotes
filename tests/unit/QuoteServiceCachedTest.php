<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-08
 * Time: 10:23 PM
 */

namespace FastStockQuotes\Tests\unit;

use FastStockQuotes\quoteAPIs\FastStockQuote;
use FastStockQuotes\QuoteServiceCached;
use FastStockQuotes\QuoteCacheRules;
use FastStockQuotes\QuoteService;
use FastStockQuotes\StockSymbol;
use FastStockQuotes\Tests\utils\MockeryTearDownTrait;

use PHPUnit\Framework\TestCase;
use Mockery;

class QuoteServiceCachedTest extends TestCase {

	use MockeryTearDownTrait;

	private $cacheRulesMock;
	private $quoteServiceMock;
	private $quoteCacheMock;
	private $quoteServiceCached;

	private static $stockSymbol = "ABC";

	public function setUp(){
		$this->quoteServiceMock   = Mockery::mock(QuoteService::class);
		$this->cacheRulesMock     = Mockery::mock(QuoteCacheRules::class);
		$this->quoteCacheMock = Mockery::mock(QuoteCache::class);
		$this->quoteServiceCached = new QuoteServiceCached($this->quoteServiceMock, $this->cacheRulesMock, $this->quoteCacheMock);
	}

	public function testUseCacheIfAvailable(){
		$cachedPrice = 10.0;
		$stubQuote = Mockery::mock(FastStockQuote::class);

		$stubQuote->allows("price")->andReturns($cachedPrice);
		$this->quoteCacheMock->allows([
			'getCachedQuote' => $stubQuote
		]);
		$this->quoteServiceMock->allows()->quotes(array())->andReturn(array());

		$quotes = $this->quoteServiceCached->quotes(StockSymbol::Symbols(self::$stockSymbol));
		$this->assertEquals($quotes[self::$stockSymbol]->price(), $cachedPrice);
	}

	public function testUseQuoteServiceIfNotCached(){
		$this->quoteCacheMock->allows([
			'getCachedQuote' => null
		]);

		$quoteArgs = StockSymbol::Symbols(self::$stockSymbol);
		$this->quoteServiceMock->shouldReceive('quotes')->with($quoteArgs)->andReturn(array());

		$this->quoteServiceCached->quotes($quoteArgs);
	}

	public function testUsesMixOfCacheAndQuoteService(){
		$cachedSymbol = new StockSymbol("CACHED");
		$cachedQuote = Mockery::mock(FastStockQuote::class);
		$cachedQuote->allows("symbol")->andReturns($cachedSymbol->fullSymbol());
		$this->quoteCacheMock->shouldReceive('getCachedQuote')->with($cachedSymbol)->andReturn($cachedQuote);

		$nonCachedSymbol= new StockSymbol("NOTCACHED");
		$nonCachedQuote = Mockery::mock(FastStockQuote::class);
		$cachedQuote->allows("symbol")->andReturns($nonCachedSymbol->fullSymbol());
		$this->quoteCacheMock->shouldReceive('getCachedQuote')->with($nonCachedSymbol)->andReturn(null);
		$this->quoteServiceMock->shouldReceive('quotes')->with(array($nonCachedSymbol))->andReturn($nonCachedQuote);

		$this->quoteServiceCached->quotes(array($cachedSymbol, $nonCachedSymbol));
	}

	public function testEmptyArray(){
		$this->quoteServiceMock->shouldReceive('quotes')->with(array())->andReturn(array());

		$actualResult = $this->quoteServiceCached->quotes(array());
		$this->assertSame(array(), $actualResult);
	}

	public function testValidatesParameterIsArray(){
		$this->expectException(\InvalidArgumentException::class);
		$this->quoteServiceCached->quotes(null);
	}
}