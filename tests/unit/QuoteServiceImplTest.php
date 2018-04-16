<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-15
 * Time: 9:22 PM
 */

namespace FastStockQuotes\Tests\unit;

use FastStockQuotes\exceptions\FastStockQuotesException;
use FastStockQuotes\exceptions\InvalidStockSymbolsException;
use FastStockQuotes\exceptions\UnsupportedStockSymbolsException;
use FastStockQuotes\markets\ExchangeCodes;
use FastStockQuotes\QuoteAPIs\AlphaVantageRequestHandler;
use FastStockQuotes\QuoteAPIs\IEXRequestHandler;
use FastStockQuotes\quoteAPIs\USQuote;
use FastStockQuotes\QuoteServiceImpl;
use FastStockQuotes\StockSymbol;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\Response;
use Mockery;
use PHPUnit\Framework\TestCase;

class QuoteServiceImplTest extends TestCase {

	private $quoteService;

	private $avServiceMock;
	private $iexServiceMock;
	private $guzzleClientMock;

	public function setUp() {
		parent::setUp();

		$this->avServiceMock = Mockery::mock(AlphaVantageRequestHandler::class);
		$this->iexServiceMock = Mockery::mock(IEXRequestHandler::class);
		$this->guzzleClientMock = Mockery::mock(Client::class);

		$this->quoteService = new QuoteServiceImpl($this->avServiceMock, $this->iexServiceMock, $this->guzzleClientMock);
	}

	public function testReturnsAssociativeArrayMappingCorrectQuotes(){
		$symbol1Name = strtoupper("SYMB1");
		$symbol2Name = strtoupper("SYMB2");

		$mockSymbol1Quote = Mockery::mock(USQuote::class);
		$mockSymbol1Quote->shouldReceive('symbol')->andReturn($symbol1Name);
		$mockSymbol2Quote = Mockery::mock(USQuote::class);
		$mockSymbol2Quote->shouldReceive('symbol')->andReturn($symbol2Name);

		$this->iexServiceMock->shouldReceive('batchQuoteURL')->andReturn('');
		$this->iexServiceMock->shouldReceive('quotesFromBatchResponse')
		                     ->andReturn([$symbol1Name => $mockSymbol1Quote, $symbol2Name => $mockSymbol2Quote]);
		$this->stubAllGuzzleClientCalls();

		$symbols = StockSymbol::Symbols($symbol2Name, $symbol1Name);
		$quotes = $this->quoteService->quotes($symbols);

		$this->assertArrayHasKey($symbol1Name, $quotes);
		$this->assertArrayHasKey($symbol2Name, $quotes);
		$this->assertSame($quotes[$symbol1Name]->symbol(), $symbol1Name);
		$this->assertSame($quotes[$symbol2Name]->symbol(), $symbol2Name);
	}

	public function testMissingIEXQuotesThrowsInvalidStockSymbolsException(){
		$validSymbolName = strtoupper("VALID");
		$invalidSymbolName = strtoupper("INVALID");
		$this->iexServiceMock->shouldReceive('batchQuoteURL')->andReturn('');
		$validSymbolQuoteStub = Mockery::mock(USQuote::class);
		$this->iexServiceMock->shouldReceive('quotesFromBatchResponse')->andReturn([$validSymbolName => $validSymbolQuoteStub]);

		$this->stubAllGuzzleClientCalls();

		$validSymbol = new StockSymbol($validSymbolName);
		$invalidSymbol = new StockSymbol($invalidSymbolName);
		$this->expectException(InvalidStockSymbolsException::class);
		try {
			$this->quoteService->quotes(array($validSymbol, $invalidSymbol));
		}
		catch (InvalidStockSymbolsException $e){
			$this->assertSame($e->getCode(), FastStockQuotesException::INVALID_STOCK_SYMBOLS);
			$this->assertSame(sizeof($e->failedSymbols()), 1, "should be 1 invalid symbol in exception");
			$this->assertSame($e->symbols()[0], $invalidSymbol, "invalid symbol in exception should be same as one not returned by IEX service");

			throw $e;
		}
	}

	/**
	 * checks for bug where duplicate symbols would appear as if a symbol was invalid because number of requested symbols
	 * did not match number of returned quotes
	 */
	public function testDuplicateSymbolsDoesNotThrowInvalidStockQuotesException(){
		$dupeSymbolName = strtoupper("DUPE");
		$dupeSymbol = new StockSymbol($dupeSymbolName);

		$this->iexServiceMock->shouldReceive('batchQuoteURL')->andReturn('');
		$mockUSQuote = Mockery::mock(USQuote::class);
		$mockUSQuote->shouldReceive('symbol')->andReturn($dupeSymbolName);
		$this->iexServiceMock->shouldReceive('quotesFromBatchResponse')->andReturn([$dupeSymbolName => $mockUSQuote]);

		$this->stubAllGuzzleClientCalls();

		$this->quoteService->quotes(array($dupeSymbol, $dupeSymbol));
		$this->addToAssertionCount(1);
	}

	public function testThrowsUnsupportedStockSymbolsExceptionIfAlphaVantageServiceNotProvided(){
		$this->expectException(UnsupportedStockSymbolsException::class);

		$nonUsSymbols = array(new StockSymbol("ABC", ExchangeCodes::CANADA));
		$quoteServiceWithoutAV = new QuoteServiceImpl(null, $this->iexServiceMock, $this->guzzleClientMock);
		$quoteServiceWithoutAV->quotes($nonUsSymbols);
	}

	public function testEmptyArray(){
		$actualResult = $this->quoteService->quotes(array());
		$this->assertSame(array(), $actualResult);
	}

	private function stubAllGuzzleClientCalls(){
		$mockGuzzleResponse = Mockery::mock(Response::class);
		$mockGuzzleResponse->shouldReceive("getBody")->andReturn('');
		$this->guzzleClientMock->shouldReceive('getAsync')->andReturn(new FulfilledPromise($mockGuzzleResponse));
	}

	public function testValidatesParameterIsArray(){
		$this->expectException(\InvalidArgumentException::class);
		$this->quoteService->quotes(null);
	}
}
