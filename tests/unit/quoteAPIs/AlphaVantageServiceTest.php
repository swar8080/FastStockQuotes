<?php
namespace FastStockQuotes\tests\unit\quoteAPIs;

use FastStockQuotes\Exceptions\FastStockQuotesException;
use FastStockQuotes\Exceptions\UnexpectedAPIResponseException;
use FastStockQuotes\QuoteAPIs\AlphaVantageRequestHandler;
use PHPUnit\Framework\TestCase;


class AlphaVantageServiceTest extends TestCase{

	const API_KEY = "fake";

	private static $validResponse;
	private static $invalidResponse;

	/**
	 * @var AlphaVantageRequestHandler
	 */
	private $avService;

	public static function setUpBeforeClass()/* The :void return type declaration that should be here would cause a BC issue */ {
		parent::setUpBeforeClass();

		self::$validResponse = file_get_contents(__DIR__ . "/../../testdata/AlphaVantage/ValidDaily.json");
		self::$invalidResponse = file_get_contents( __DIR__ . "/../../testdata/AlphaVantage/ErrorResponse.json" );
	}

	public function setUp(){
		$this->avService = new AlphaVantageRequestHandler(self::API_KEY);
	}

	public function testDailyQuoteUrlIsValid(){
		$symbol = "SHOP.TO";

		$expectedUrl = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=" . $symbol . "&apikey=" . self::API_KEY;
		$expectedUrl .= "&outputsize=compact&datatype=json";

		$this->assertSame($expectedUrl, $this->avService->dailyQuoteURL($symbol), "Daily timeseries quote request URL should match");
	}

	/*
	 * Snippet from tests/testdata/AlphaVantage/ValidDaily.json
	 * {
	  "Meta Data": {
		"1. Information": "Daily Prices (open, high, low, close) and Volumes",
		"2. Symbol": "msft",
		"3. Last Refreshed": "2018-04-10",
		"4. Output Size": "Compact",
		"5. Time Zone": "US/Eastern"
	  },
	  "Time Series (Daily)": {
		"2018-04-10": {
		  "1. open": "92.3900",
		  "2. high": "93.2800",
		  "3. low": "91.6400",
		  "4. close": "92.8800",
		  "5. volume": "26865981"
		},
		"2018-04-09": {
		  "1. open": "91.0400",
		  "2. high": "93.1700",
		  "3. low": "90.6200",
		  "4. close": "90.7700",
		  "5. volume": "31533943"
		},
	 */
	public function testParsedQuoteIsValid(){
		$avQuote = $this->avService->quoteFromResponse(self::$validResponse);

		$this->assertSame(92.88, $avQuote->price(), "price");
		$this->assertSame(92.39, $avQuote->open(), "open");
		$this->assertSame(93.28, $avQuote->high(), "high");
		$this->assertSame(91.64, $avQuote->low(), "low");
		$this->assertSame(26865981, $avQuote->volume(), "volume");
		$this->assertSame(90.77, $avQuote->previousDayClose(), "previous close");
		$this->assertSame("MSFT", $avQuote->symbol(), "symbol");
	}

	public function testKnownAVErrorExceptionThrown(){
		$this->expectException(UnexpectedAPIResponseException::class);

		try {
			$this->avService->quoteFromResponse(self::$invalidResponse);
		}
		catch (UnexpectedAPIResponseException $e){
			$this->assertSame(FastStockQuotesException::AV_ERROR_RESPONSE_CODE, $e->getCode(), "known av error response code" );
			throw $e;
		}
	}

	public function testUnknownAVErrorExceptionThrow(){
		$this->expectException(UnexpectedAPIResponseException::class);

		try {
			$this->avService->quoteFromResponse("unexpected response");
		}
		catch (UnexpectedAPIResponseException $e){
			$this->assertSame(FastStockQuotesException::AV_UNKNOWN_ERROR_RESPONSE_CODE, $e->getCode(), "unknown av error response code" );
			throw $e;
		}
	}
}