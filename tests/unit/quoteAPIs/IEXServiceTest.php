<?php
namespace FastStockQuotes\tests\unit\quoteAPIs;

use FastStockQuotes\Exceptions\FastStockQuotesException;
use FastStockQuotes\Exceptions\UnexpectedAPIResponseException;
use FastStockQuotes\QuoteAPIs\IEXRequestHandler;
use PHPUnit\Framework\TestCase;


class IEXServiceTest extends TestCase {

	private static $validResponse;
	private static $errorResponse;
	private static $responseWithMissingKeys;

	/**
	 * @var IEXRequestHandler
	 */
	private $iexService;

	public static function setUpBeforeClass() {
		self::$validResponse           = file_get_contents( __DIR__ . "/../../testdata/IEX/3BatchRealtimeQuotes.json" );
		self::$errorResponse           = file_get_contents( __DIR__ . "/../../testdata/IEX/ErrorResponse.json" );
		self::$responseWithMissingKeys = file_get_contents( __DIR__ . "/../../testdata/IEX/ResponseWithMissingKeyNames.json" );
	}

	public function setUp()/* The :void return type declaration that should be here would cause a BC issue */ {
		$this->iexService = new IEXRequestHandler();
	}

	public function testBatchQuoteURL(){
		$symbols = array("MSFT", "FB", "AAPL");
		$expected = "https://api.iextrading.com/1.0/stock/market/batch?&types=quote&symbols=MSFT,FB,AAPL";

		$this->assertSame($expected, $this->iexService->batchQuoteURL($symbols), "iex batch quote url should match");
	}

	public function testValidResponseReturnsAllQuotes(){
		$expectedQuotes = 3;

		$quotes = $this->iexService->quotesFromBatchResponse(self::$validResponse);

		$this->assertSame($expectedQuotes, sizeof($quotes), "should return all 3 quotes from response");
	}

	public function testIsValidQuoteEncoding(){
		$targetSymbol = "AAPL";

		$quotes = $this->iexService->quotesFromBatchResponse(self::$validResponse);

		$this->assertTrue(array_key_exists($targetSymbol, $quotes));

		$quote = $quotes[$targetSymbol];

		$this->assertSame($targetSymbol, $quote->symbol(), "symbol");
		$this->assertSame(173.21, $quote->price(), "price");
		$this->assertSame(172.08, $quote->open(), "open");
		$this->assertSame(173.25, $quote->previousDayClose(), "previous close");
		$this->assertSame(173.923, $quote->high(), "high");
		$this->assertSame(171.7, $quote->low(), "low");
		$this->assertSame(12601243, $quote->volume(), "volume");
		$this->assertSame(intval(1523467219040/1000), $quote->lastUpdated(), "last updated unix time");
	}

	public function testUnknownResponseExceptionThrown(){
		$this->expectException(UnexpectedAPIResponseException::class);

		try {
			$this->iexService->quotesFromBatchResponse("unexpected response");
		}
		catch (UnexpectedAPIResponseException $e){
			$this->assertSame(FastStockQuotesException::IEX_UNKNOWN_ERROR_RESPONSE_CODE, $e->getCode(), "should have unknown error response error code");
			throw $e;
		}
	}

	public function testKnownErrorResponseExceptionThrown(){
		$this->expectException(UnexpectedAPIResponseException::class);

		try {
			$this->iexService->quotesFromBatchResponse(self::$errorResponse);
		}
		catch (UnexpectedAPIResponseException $e){
			$this->assertSame(FastStockQuotesException::IEX_ERROR_RESPONSE_CODE, $e->getCode(), "should have known error response error code");
			throw $e;
		}
	}

	public function testUnexpectedFieldsExceptionThrown(){
		$this->expectException(UnexpectedAPIResponseException::class);

		try {
			$this->iexService->quotesFromBatchResponse(self::$responseWithMissingKeys);
		}
		catch (UnexpectedAPIResponseException $e){
			$this->assertSame(FastStockQuotesException::IEX_RESPONSE_MISSING_FIELDS, $e->getCode(), "should have missing fields error code");
			throw $e;
		}
	}
}