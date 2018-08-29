<?php
namespace FastStockQuotes;

use FastStockQuotes\Exceptions\InvalidExchangeCodeException;
use FastStockQuotes\Markets\ExchangeCodes;
use FastStockQuotes\Markets\StockExchange;


/**
 * Class StockSymbol
 *
 * Used to identify a stock belonging to a stock exchange.
 * Each stock (except US ones) has an exchange code which identifies the stock exchange it trades on,
 * as well as a symbol to identify which stock it is on that exchange.
 * The APIs require stock symbols and exchange codes to be in the format "<symbol>.<exchange code>".
 * For example, "SHOP" would be Shopify stock on the New York Stock Exchange
 * and "SHOP.TO" would be Shopify stock on the Toronto Stock Exchange.
 * @see ExchangeCodes for a list of exchanges codes or get the exchange code from Yahoo Finance.
 *
 * @package FastStockQuotes
 */
class StockSymbol {

	private $prefix;
	private $exchangeCode;
	private $exchange;
	private $full;

	/**
	 * StockSymbol constructor.
	 *
	 * @param $symbol string Either the full stock symbol (<symbol>.<exchange code>) or just the symbol
	 * @param $exchangeCode string required if the full symbol is not provided for the $symbol parameter
	 *
	 * @throws InvalidExchangeCodeException
	 */
	public function __construct( $symbol, $exchangeCode=null) {
		if ($symbol == null || strlen($symbol) == 0){
			throw new \InvalidArgumentException("Symbol cannot be empty");
		}

		if ($exchangeCode !== null){
			$this->prefix       = strtoupper($symbol);
			$this->exchangeCode = strtoupper($exchangeCode);
		}
		else {
			$symbol = strtoupper($symbol);
			$symbolParts = explode(".", $symbol);

			$this->prefix = $symbolParts[0];
			$this->exchangeCode = (sizeof($symbolParts) > 1)? implode("",array_slice($symbolParts, 1)) : "";
		}

		$this->exchange = StockExchange::fromExchangeCode($this->exchangeCode);
		$this->full = ($this->exchangeCode)? $this->prefix . "." . $this->exchangeCode : $this->prefix;
	}

	/**
	 * Convenience method for creating an array of @see StockSymbol.
	 * For example StockSymbol::Symbols("AAPL", "SHOP.TO", "BARC.L")
	 * is equivalent to array(new StockSymbol("AAPL"), new StockSymbol("SHOP.TO"), new StockSymbol("BARC.L"))
	 *
	 * @param $fullSymbols One or more full symbol (string)
	 *
	 * @return StockSymbol[]
	 */
	public static function Symbols(...$fullSymbols){
		$symbols = array();
		foreach ($fullSymbols as $fullSymbol)
			array_push($symbols, new StockSymbol($fullSymbol));
		return $symbols;
	}

	/**
	 * @return string The stock symbol without the exchange code
	 */
	public function prefix(){
		return $this->prefix;
	}

	/**
	 * @return string
	 */
	public function exchangeCode(){
		return $this->exchangeCode;
	}

	/**
	 * @return string The symbol with the exchange code
	 */
	public function fullSymbol(){
		return $this->full;
	}

	/**
	 * @return StockExchange
	 */
	public function exchange(){
		return $this->exchange;
	}

}