<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
	/**
	 * Setup the test environment.
	 *
	 * @return void
	 */
	protected function setUp(): void
	{
		parent::setUp();
		
		// Install fresh data
		// $this->artisan('app:install-fresh-data', ['--confirm' => true]);
	}
	
	/**
	 * Clean up the testing environment before the next test.
	 *
	 * @return void
	 *
	 * @throws \Mockery\Exception\InvalidCountException
	 */
	protected function tearDown(): void
	{
		parent::tearDown();
	}
}
