<?php
declare( strict_types = 1 );

define('MARMERINE_PORT', (int) getenv('MARMERINE_PORT') ?: 11211);
//print_r($_SERVER ); //.PHP_EOL;
echo 'PORT: ' . MARMERINE_PORT . PHP_EOL;

class MC {
	public static Memcached $mc;

	public function __construct() { }

	public static function start() {
		
		if ( isset( self::$mc )) {
			return;
		}

		self::$mc = new Memcached();
		self::$mc->addServer( '127.0.0.1', MARMERINE_PORT);
		self::$mc->flush();
	}
}

MC::start();

// Make this available to all of the tests
// https://pestphp.com/docs/underlying-test-case#uses
uses()
	->afterEach( function() {
	// Remove all the entries in Memcached after each test run
	// https://pestphp.com/docs/setup-and-teardown#afterEach
	MC::$mc->flush();
} )->in( __DIR__ );
