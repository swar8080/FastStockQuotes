<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-12
 * Time: 9:59 AM
 */

namespace FastStockQuotes\tests\unit;

use FastStockQuotes\Markets\StockExchange;
use FastStockQuotes\QuoteCacheRules;
use PHPUnit\Framework\TestCase;

class QuoteCaseRulesTest extends TestCase {

	/**
	 * @var QuoteCacheRules
	 */
	private $quoteCacheRules;

	public function setUp() {
		$this->quoteCacheRules = new QuoteCacheRules();
	}

	public function shouldCachePriceProvider(){
		$tz = new \DateTimeZone("America/New_York");

		//order: current time, last update time form quote source, last close time of exchange, whether or not should be cached
		return [
			"Morning before exchange open" => [new \DateTime("friday 10:00", $tz), new \DateTime("thursday 16:00", $tz), new \DateTime("thursday 16:00", $tz), true],
			"Exchange is open" => [new \DateTime("friday 13:05", $tz), new \DateTime("friday 13:00", $tz), new \DateTime("thursday 16:00", $tz), true],
			"Last update at closing time" => [new \DateTime("friday 16:00", $tz), new \DateTime("friday 16:00", $tz), new \DateTime("friday 16:00", $tz), true],
			"Exchange closed but last update before close" => [new \DateTime("friday 16:05", $tz), new \DateTime("friday 15:59", $tz), new \DateTime("friday 16:00", $tz), false]
		];

	}

	/**
	 *
	 * @dataProvider shouldCachePriceProvider
	 *
	 * @param $currentDateTime \DateTime
	 * @param $quoteSourceLastUpdatedDateTime \DateTime
	 * @param $exchangeLastCloseMockDateTime \DateTime
	 * @param $shouldCache boolean
	 *
	 * @throws
	 */
	public function testShouldCachePrice($currentDateTime, $quoteSourceLastUpdatedDateTime, $exchangeLastCloseMockDateTime, $shouldCache){
		$exchangeStub = $this->createMock(StockExchange::class);
		$exchangeStub->method('timestampOfLastClose')->willReturn($exchangeLastCloseMockDateTime->getTimestamp());

		$actualShouldCachePrice = $this->quoteCacheRules
			->shouldCacheQuote($quoteSourceLastUpdatedDateTime->getTimestamp(), $exchangeStub, $currentDateTime->getTimestamp());

		$this->assertSame($shouldCache, $actualShouldCachePrice,
			"Current: " . $currentDateTime->format("Y-m-d H:i"));
	}
}