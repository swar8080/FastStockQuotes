<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-14
 * Time: 12:19 PM
 */

namespace FastStockQuotes\markets;

/**
 * Class ExchangeCodes
 *
 * To identify the stock exchange a non-us symbol belongs to,
 * an exchange code is added with the format <symbol>.<exchange code>.
 * The exchange codes are consistent with the ones used on Yahoo Finance.
 * To add a new exchange, add the exchange code here
 * and add an entry to the @see{StockExchange::Exchanges} array constant.
 *
 * @package FastStockQuotes\markets
 */
class ExchangeCodes {

	const AUSTRALIA = "AX";
	const AMSTERDAM = "AS";
	const CANADA = 'TO';
	const GERMANY = "F";
	const HONG_KONG = "HK";
	const JAPAN = 'T';
	const LONDON = "L";
	const NEW_ZEALAND = "NZ";
	const NORWAY = "OL";
	const PARIS = "PA";
	const SHANGHAI = "SS";
	const SHENZHEN = "SZ";
	const STOCKHOLM = "ST";
	const US = '';

	private function __construct() {}
}