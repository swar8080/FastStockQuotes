<?php
namespace FastStockQuotes\QuoteAPIs;

use FastStockQuotes\Exceptions\FastStockQuotesException;
use FastStockQuotes\Exceptions\UnexpectedAPIResponseException;
use FastStockQuotes\Utils\ArrayUtils;

class AlphaVantageRequestHandler {

	private $apiKey;

	const DAILY_PRICE_URL_TEMPLATE = "https://www.alphavantage.co/query?function=TIME_SERIES_DAILY&symbol=%s&apikey=%s&outputsize=compact&datatype=json";


	/**
	 * Time series daily JSON response consntants
	 * Format is:
	 * "Time Series (Daily)": {
		"2018-04-10": {
			"1. open": "92.3900",
			"2. high": "93.2800",
			"3. low": "91.6400",
			"4. close": "92.8800",
			"5. volume": "26865981"
		},
		"2018-04-09": {
	 *  ...
	 */
	const TIME_SERIES_KEY = "Time Series (Daily)";
	const ERROR_KEY = "Error Message";

	const OPEN_KEY = "1. open";
	const CLOSE_KEY = "4. close";
	const LOW_KEY = "3. low";
	const HIGH_KEY = "2. high";
	const VOLUME_KEY = "5. volume";

	const META_DATA_KEY = "Meta Data";
	const SYMBOL_KEY = "2. Symbol";

	/**
	 * AlphaVantageRequestHandler constructor.
	 *
	 * @param $apiKey string
	 */
	public function __construct($apiKey) {
		$this->apiKey = $apiKey;
	}

	/**
	 * @param $fullSymbol string
	 *
	 * @return string
	 */
	public function dailyQuoteURL( $fullSymbol ) {
		return sprintf(self::DAILY_PRICE_URL_TEMPLATE, $fullSymbol, $this->apiKey);
	}

	/**
	 * @param $intlQuoteResponse string
	 *
	 * @return NonUSQuote
	 * @throws UnexpectedAPIResponseException
	 */
	public function quoteFromResponse( $intlQuoteResponse ) {
			$responseJson = json_decode($intlQuoteResponse, $assoc=true);

			if ($responseJson){
				if (array_key_exists(self::TIME_SERIES_KEY, $responseJson)){
					$timeseriesObjects = $responseJson[self::TIME_SERIES_KEY];
					$timeseriesDates = array_keys($timeseriesObjects);

					$mostRecentQuote = $timeseriesObjects[$timeseriesDates[0]];

					$isValidQuoteKeys = ArrayUtils::allKeysExist($mostRecentQuote,
						[self::OPEN_KEY, self::CLOSE_KEY, self::HIGH_KEY, self::LOW_KEY, self::VOLUME_KEY]);


					$isValidSymbolKeys = array_key_exists(self::META_DATA_KEY, $responseJson)
					                     && array_key_exists(self::SYMBOL_KEY, $responseJson[self::META_DATA_KEY]);


					if ($isValidQuoteKeys && $isValidSymbolKeys){
						$price = floatval($mostRecentQuote[self::CLOSE_KEY]);
						$open = floatval($mostRecentQuote[self::OPEN_KEY]);
						$high = floatval($mostRecentQuote[self::HIGH_KEY]);
						$low = floatval($mostRecentQuote[self::LOW_KEY]);
						$volume = intval($mostRecentQuote[self::VOLUME_KEY]);
						$symbol = strtoupper($responseJson[self::META_DATA_KEY][self::SYMBOL_KEY]);
						$previousDayClose = null;


						if (sizeof($timeseriesDates) > 1){
							$previousDayQuote = $timeseriesObjects[$timeseriesDates[1]];
							$previousDayClose = floatval($previousDayQuote[self::CLOSE_KEY]);
						}

						return new NonUSQuote($symbol, $price, $open, $previousDayClose, $volume, $low, $high);
					}
				}
				else if (array_key_exists(self::ERROR_KEY, $responseJson)) {
					$errorMessage = "The AlphaVantage stock API returned an error. Either an invalid stock symbol was provided or their API has changed";
					$errorMessage .= "\n\tError Message from AlphaVantage: " . $responseJson[self::ERROR_KEY];
					throw new UnexpectedAPIResponseException($message=$errorMessage, $code=FastStockQuotesException::AV_ERROR_RESPONSE_CODE);

				}
			}

			$errorMessage = "Unexpected JSON response from AlphaVantage API - they may have changed. Please contact author";
			$errorMessage .= "\n\tResponse: " . $intlQuoteResponse;
			throw new UnexpectedAPIResponseException($message=$errorMessage, $code=FastStockQuotesException::AV_UNKNOWN_ERROR_RESPONSE_CODE);
	}

}