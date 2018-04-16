<?php
namespace FastStockQuotes;

class QuoteServiceCached implements QuoteService {

	/**
	 * @var QuoteService
	 */
	private $quoteService;
	/**
	 * @var QuoteCacheRules
	 */
	private $quoteCacheRules;
	/**
	 * @var QuoteCache
	 */
	private $quoteCache;

	/**
	 * QuoteServiceCached constructor.
	 *
	 * @param $quoteService QuoteService
	 * @param $exchangeCacheRules QuoteCacheRules
	 * @param $quoteCache QuoteCache
	 */
	public function __construct($quoteService, $exchangeCacheRules, $quoteCache) {
		$this->quoteService    = $quoteService;
		$this->quoteCacheRules = $exchangeCacheRules;
		$this->quoteCache = $quoteCache;
	}

	/**
	 * @param $symbols \FastStockQuotes\StockSymbol[]
	 *
	 * @return array[string:FastStockQuote]
	 */
	public function quotes($symbols) {
		if (!is_array($symbols))
			throw new \InvalidArgumentException("expected array of StockSymbol but received: " . (string)$symbols);

		$quotes = array();
		$symbolsNotCached = array();

		foreach ($symbols as $symbol){
			$cachedQuote = $this->quoteCache->getCachedQuote($symbol);

			if ($cachedQuote !== null){
				$quotes[$symbol->fullSymbol()] = $cachedQuote;
			}
			else {
				array_push($symbolsNotCached, $symbol);
			}
		}

		$pricesToCache = $this->quoteService->quotes($symbolsNotCached);

		foreach ($pricesToCache as $fullSymbolString => $stockQuote){
			$quotes[$fullSymbolString] = $stockQuote;

			$stockSymbol = new StockSymbol($fullSymbolString, $exchangeCode=null);
			if ($this->quoteCacheRules->shouldCacheQuote($stockQuote->lastUpdated(), $stockSymbol->exchange())){
				$this->quoteCache->cacheQuote($stockSymbol, $stockQuote);
			}
		}

		return $quotes;
	}
}