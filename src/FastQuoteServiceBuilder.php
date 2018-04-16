<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-15
 * Time: 2:47 PM
 */

namespace FastStockQuotes;


use FastStockQuotes\QuoteAPIs\AlphaVantageRequestHandler;
use FastStockQuotes\QuoteAPIs\IEXRequestHandler;
use Predis\Client;

/**
 * Class FastQuoteServiceBuilder
 *
 * Used to configure a @see QuoteService
 *
 * Example:
 * $quoteService = FastQuoteServiceBuilder::builder()
 *  ->withAlphaVantageGlobalQuoteAPI("My API key")
 *  ->withRedisCaching($myPredisClient, 60)
 *  ->build();
 *
 * @package FastStockQuotes
 */
class FastQuoteServiceBuilder {

	/**
	 * @var Client
	 */
	private $redis;

	/**
	 * @var AlphaVantageRequestHandler|null
	 */
	private $avRequestHandler;

	/**
	 * @var int|null
	 */
	private $minCachingLength;

	private function __construct() {
		$this->redis            = null;
		$this->avRequestHandler = null;
		$this->minCachingLength = null;
	}

	/**
	 * @return FastQuoteServiceBuilder
	 */
	public static function builder(){
		return new FastQuoteServiceBuilder();
	}

	/**
	 * The AlphaVantage API is used for Non-US quotes
	 * An API key is required, which can be obtained for free from https://www.alphavantage.co/support/#api-key
	 *
	 * @param $apiKey string Your AlphaVantage API key
	 *
	 * @return $this
	 */
	public function withAlphaVantageGlobalQuoteAPI($apiKey){
		if (!is_string($apiKey)){
			throw new \InvalidArgumentException("Invalid alphavantage API key: " . (string)$apiKey);
		}

		$this->avRequestHandler = new AlphaVantageRequestHandler($apiKey);

		return $this;
	}

	/**
	 * @param $predisClient Client A connected Predis-based Redis client, see https://github.com/nrk/predis for details
	 * @param $minCachingLengthSeconds int|null The minimum number of seconds a quote will be cached for.
	 *
	 * @return $this
	 */
	public function withRedisCaching($predisClient, $minCachingLengthSeconds=QuoteCache::MIN_TIMEOUT_SECONDS){
		if (!($predisClient instanceof Client))
			throw new \InvalidArgumentException("Expecting instance of Predis\Client but received: " . (string)$predisClient );

		if ( $minCachingLengthSeconds !== null && ( !is_integer($minCachingLengthSeconds) || $minCachingLengthSeconds < 0)){
			throw new \InvalidArgumentException("Invalid maximum caching length seconds: " . (string)$minCachingLengthSeconds);
		}

		$this->redis            = $predisClient;
		$this->minCachingLength = $minCachingLengthSeconds;

		return $this;
	}

	/**
	 * @return QuoteService
	 */
	public function build(){
		if ($this->redis !== null)
			return $this->buildCachingService();
		else
			return $this->buildQuoteServiceImpl();
	}

	/**
	 * @return QuoteService
	 */
	private function buildCachingService(){
		$serviceImpl = $this->buildQuoteServiceImpl();
		$quoteCacheRules = new QuoteCacheRules();

		if ( $this->minCachingLength !== null){
			$quoteCache = new QuoteCache($this->redis, $this->minCachingLength);
		}
		else {
			$quoteCache = new QuoteCache($this->redis);
		}

		return new QuoteServiceCached($serviceImpl, $quoteCacheRules, $quoteCache);
	}

	/**
	 * @return QuoteService
	 */
	private function buildQuoteServiceImpl(){
		return new QuoteServiceImpl($this->avRequestHandler, new IEXRequestHandler(), new \GuzzleHttp\Client());
	}
}