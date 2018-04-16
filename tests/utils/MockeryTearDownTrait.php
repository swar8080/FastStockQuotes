<?php
/**
 * Created by PhpStorm.
 * User: Steven
 * Date: 2018-04-15
 * Time: 12:37 AM
 */

namespace FastStockQuotes\Tests\utils;

use Mockery;

/**
 * Trait MockeryTearDownTrait
 *
 * Need for mockery tests that expect method calls but make no assertions
 * source: https://github.com/mockery/mockery/issues/376
 *
 * @package FastStockQuotes\Tests\utils
 */
trait MockeryTearDownTrait {
	public function tearDown()
	{
		parent::tearDown();
		if ($container = Mockery::getContainer()) {
			$this->addToAssertionCount($container->mockery_getExpectationCount());
		}
		Mockery::close();
	}
}