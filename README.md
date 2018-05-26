# FastStockQuotes Overview
This library provides efficient access to two free stock quote APIs: [Alpha Vantage](https://www.alphavantage.co/) and [IEX](https://iextrading.com/developer). IEX is fast but limited to US stocks only. Alpha Vantage supports most stocks in the world but requires a separate request for each quote and is limited to ~1 request per second. To make Alpha Vantage usable for a moderate number of stocks, caching using [redis](https://redis.io/) can optionally be enabled to limit API requests.

# Installation
Install with the composer command: `composer require swar8080/fast-stock-quotes`

## Prerequisites
* Claim your free [Alpha Vantage API key](https://www.alphavantage.co/support/#api-key) for access to non-US quotes
* Optionally [download and run a redis server](https://redis.io/) to enable caching of stock quotes. Review the [Predis library](https://github.com/nrk/predis#connecting-to-redis) for options when connecting to your redis server from PHP. 

# Stock Exchange Support
The APIs used by this library support most stock exchanges in this world, however, they must be configured before use.

Adding support for a stock exchange is easy: add its exchange code to [StockExchange.php](https://github.com/swar8080/FastStockQuotes/blob/master/src/markets/ExchangeCodes.php) and an entry to the `Exchanges` array in [StockExchange.php](https://github.com/swar8080/FastStockQuotes/blob/master/src/markets/StockExchange.php#L149). If you run into problems, feel free to open an issue on github.

# Benefits of Using Caching
* If the quote is cached, a network call to the API can be avoided
* You can configure the number of seconds before the cached quote expires and is removed
* If the stock exchange is closed, the quote will be cached until it re-opens

The following table shows time spent making requests (in seconds) from different sources. The biggest time savings can come from caching non-US Alpha Vantage quotes.

Number of Stocks | IEX  | Alpha Vantage | From Cache (redis)
-----------------|------|---------------|-----------------
1 Stock | 0.28 | 0.82 | 0.002
5 Stocks | 0.35 | 1.89 | 0.003
10 Stocks | 0.56 | 10.16 | 0.003

# Demo
```php
require "vendor/autoload.php";

use FastStockQuotes\FastQuoteServiceBuilder;
use FastStockQuotes\StockSymbol;
use FastStockQuotes\markets\ExchangeCodes;
use FastStockQuotes\quoteAPIs\USQuote;

$quoteService = FastQuoteServiceBuilder::builder()
	->withAlphaVantageGlobalQuoteAPI("your API key")
	->withRedisCaching(new \Predis\Client(), $minCachingLengthSeconds=300)
	->build();

//For US stocks, just pass the symbol
$usSymbol = new StockSymbol("MSFT");

//For non-us stocks, an exchange code must be added to the stock symbol to identify the stock exchange it belongs to.
//If you're unsure of the exchange code, use the built-in ExchangeCodes constant or check Yahoo Finance
$canadianSymbol = new StockSymbol("SHOP", $exchangeCode=ExchangeCodes::CANADA); 
$australianSymbol = new StockSymbol("WBC.AX"); //equivalent to: new StockSymbol("WBC", ExchangeCodes::AUSTRALIA)

//request the quotes from the appropriate APIs
$quotes = $quoteService->quotes(array($usSymbol, $canadianSymbol, $australianSymbol));

//this is equivalent
$quotes = $quoteService->quotes(StockSymbol::Symbols("MSFT", "SHOP.TO", "WBC.AX"));

foreach ($quotes as $symbol => $quote){
	echo "--" . $quote->symbol() . "--" . PHP_EOL;
	echo $quote->price() . PHP_EOL;
	echo $quote->open() . PHP_EOL;
	echo $quote->previousDayClose() . PHP_EOL;
	echo $quote->high() . PHP_EOL;
	echo $quote->low() . PHP_EOL;
	echo $quote->volume() . PHP_EOL;
	echo $quote->lastUpdated() . PHP_EOL;

	if ($quote instanceof USQuote){
		echo $quote->marketCap . PHP_EOL;
		echo $quote->week52High . PHP_EOL;
		echo $quote->peRatio . PHP_EOL;
		//full list of fields available for US quotes: https://iextrading.com/developer/docs/#quote
	}
	echo PHP_EOL;
}
```
