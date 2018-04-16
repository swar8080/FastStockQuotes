<?php
namespace FastStockQuotes;

use FastStockQuotes\exceptions\InvalidStockSymbolsException;
use FastStockQuotes\exceptions\UnexpectedAPIResponseException;
use FastStockQuotes\exceptions\UnsupportedStockSymbolsException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

use FastStockQuotes\utils\ArrayUtils;
use FastStockQuotes\exceptions\FastStockQuotesException;
use GuzzleHttp\Psr7\Response;
use Mockery\Exception\InvalidArgumentException;

class QuoteServiceImpl implements QuoteService {
	private $avRequestHandler;
	private $iexRequestHandler;
	private $guzzleClient;

	const AV_SECONDS_BETWEEN_CALLS = 1;

	/**
	 * QuoteService constructor.
	 *
	 * @param $avRequestHandler  \FastStockQuotes\QuoteAPIs\AlphaVantageRequestHandler|null
	 * @param $iexRequestHandler \FastStockQuotes\QuoteAPIs\IEXRequestHandler
	 * @param $guzzleClient Client
	 */
	public function __construct( $avRequestHandler, $iexRequestHandler, $guzzleClient) {
		$this->avRequestHandler  = $avRequestHandler;
		$this->iexRequestHandler = $iexRequestHandler;
		$this->guzzleClient      = $guzzleClient;
	}

	/**
	 * @param StockSymbol[] $symbols
	 *
	 * @return array[string:FastStockQuote]
	 * @throws UnsupportedStockSymbolsException
	 * @throws FastStockQuotesException
	 */
	public function quotes($symbols){
		if (!is_array($symbols))
			throw new \InvalidArgumentException("expected array of StockSymbol but received: " . (string)$symbols);

		$nonUS = array();
		$US = array();

		$seenSymbolsSet = [];
		foreach ($symbols as $symbol){
			//ignore duplicates
			if (array_key_exists($symbol->fullSymbol(), $seenSymbolsSet))
				continue;

			if ($symbol->exchange()->isUS())
				array_push($US, $symbol);
			else
				array_push($nonUS, $symbol);

			$seenSymbolsSet[$symbol->fullSymbol()] = null;
		}

		if ( $this->avRequestHandler === null && sizeof($nonUS) > 0){
			throw new UnsupportedStockSymbolsException($nonUS);
		}

		return $this->_quotes($US, $nonUS);
	}

	/**
	 * @param $US StockSymbol[]
	 * @param $nonUS StockSymbol[]
	 *
	 * @return array[string:FastStockQuote]
	 * @throws FastStockQuotesException
	 * @throws InvalidStockSymbolsException
	 */
	private function _quotes($US, $nonUS) {
		$quotes = array();

		$iexPromise = null;

		if (sizeof($US) > 0){
			$fullSymbols = ArrayUtils::map($US, function($symbol) {
				return $symbol->fullSymbol();
			});
			$batchQuoteUrl = $this->iexRequestHandler->batchQuoteURL($fullSymbols);
			$iexPromise = $this->guzzleClient->getAsync($batchQuoteUrl);

			$iexPromise->then(
				function($response) use (&$quotes, &$US) {
					return $response;
				},
				function($rejectReason) use ($US) {
					throw new FastStockQuotesException($message="", $code=0, $previous=$rejectReason, $stockSymbols=$US);
				}
			);
		}

		foreach ($nonUS as $symbol){

			try {
				$quoteUrl = $this->avRequestHandler->dailyQuoteURL($symbol->fullSymbol());

				$quoteResponse = $this->guzzleClient->get($quoteUrl, ['on_stats' =>
                  function($stats) {
					  $secondsToSleepFor = max(self::AV_SECONDS_BETWEEN_CALLS - $stats->getTransferTime(), 0);
	                  sleep( $secondsToSleepFor);
                  }]
				);

				$quoteBody = strval($quoteResponse->getBody());

				try {
					$avQuote = $this->avRequestHandler->quoteFromResponse($quoteBody);
				}
				catch (UnexpectedAPIResponseException $e){
					$errorMessage = "Error retrieving quote from AlphaVantage API for symbol: " . $symbol->fullSymbol();
					throw new FastStockQuotesException($errorMessage, $code=$e->getCode(), $previous=$e, $stockSymbols=array($symbol));
				}

				$quotes[$symbol->fullSymbol()] = $avQuote;
			}
			catch (TransferException $te){
				throw new FastStockQuotesException($message="", $code=0, $previous=$te, $stockSymbols=array($symbol));
			}
		}

		if ($iexPromise !== null){
			$iexResponse = $iexPromise->wait();
			$usQuotes = $this->pricesFromIEXBatchResponse($iexResponse, $US);
			$quotes = array_merge($quotes, $usQuotes);
		}

		return $quotes;
	}

	/**
	 * @param $response Response
	 * @param $expectedSymbols StockSymbol[]
	 *
	 * @return array[string:FastStockQuote]
	 * @throws InvalidStockSymbolsException
	 * @throws FastStockQuotesException
	 */
	private function pricesFromIEXBatchResponse($response, $expectedSymbols){
		$quotes = array();

		$responseBody = strval($response->getBody());

		try {
			$iexQuotes = $this->iexRequestHandler->quotesFromBatchResponse($responseBody);
		}
		catch (UnexpectedAPIResponseException $e){
			$errorMessage = "Error retrieving quotes from IEX API";
			throw new FastStockQuotesException($errorMessage, $code=$e->getCode(), $previous=$e, $stockSymbols=$expectedSymbols);
		}

		if (sizeof($iexQuotes) != sizeof($expectedSymbols)){
			$invalidSymbols = array();
			$quoteSymbolsReturned = array_keys($iexQuotes);
			foreach ($expectedSymbols as $expectedSymbol){
				if (!in_array($expectedSymbol->fullSymbol(), $quoteSymbolsReturned)){
					array_push($invalidSymbols, $expectedSymbol);
				}
			}

			throw new InvalidStockSymbolsException($invalidSymbols);
		}

		foreach ($iexQuotes as $iexQuote){
			$quotes[$iexQuote->symbol()] = $iexQuote;
		}

		return $quotes;
	}
}