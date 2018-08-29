<?php
namespace FastStockQuotes\QuoteAPIs;


use FastStockQuotes\Exceptions\FastStockQuotesException;
use FastStockQuotes\Exceptions\UnexpectedAPIResponseException;
use FastStockQuotes\Utils\ArrayUtils;

class IEXRequestHandler {

	const BATCH_QUOTE_URL_TEMPLATE = "https://api.iextrading.com/1.0/stock/market/batch?&types=quote&symbols=%s";
	const BATCH_QUOTE_KEY = "quote";
	const BATCH_QUOTE_ERROR_KEY = "error";

	/**
	 * @param $fullSymbols array[string]
	 *
	 * @return string
	 */
	public function batchQuoteURL($fullSymbols) {
		$symbolList = implode(",", $fullSymbols);
		return sprintf(self::BATCH_QUOTE_URL_TEMPLATE, $symbolList);
	}

	/**
	 * @param $response string
	 *
	 * @return USQuote[]
	 * @throws UnexpectedAPIResponseException
	 */
	public function quotesFromBatchResponse( $response ) {
		$quotes = array();

		$responseJson = json_decode($response, $assoc=true);

		if (!$responseJson){
			$errorMessage = "IEX API returned unexpected response.";
			$errorMessage .= "\n\tIEX Response: " . $response;
			throw new UnexpectedAPIResponseException($message=$errorMessage, $code=FastStockQuotesException::IEX_UNKNOWN_ERROR_RESPONSE_CODE);
		}

		if (!array_key_exists(self::BATCH_QUOTE_ERROR_KEY, $responseJson)){
			foreach ($responseJson as $symbol => $dataGroups){
				$quote = $dataGroups[self::BATCH_QUOTE_KEY];

				if (!ArrayUtils::allKeysExist($quote, USQuote::KNOWN_IEX_JSON_KEYS)){
					$errorMessage = "IEX API Response did not return expected fields. The API may has changed, please contact the author";
					throw new UnexpectedAPIResponseException($message=$errorMessage, $code=FastStockQuotesException::IEX_RESPONSE_MISSING_FIELDS);
				}

				$quotes[$symbol] = USQuote::fromAssocArray($quote);
			}
		}
		else {
			$errorMessage = "IEX API responded with an error: "	. $responseJson[self::BATCH_QUOTE_ERROR_KEY];
			throw new UnexpectedAPIResponseException($message=$errorMessage, $code=FastStockQuotesException::IEX_ERROR_RESPONSE_CODE);
		}

		return $quotes;
	}
}