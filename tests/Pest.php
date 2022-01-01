<?php
declare( strict_types = 1 );

class MC {
	public static $mc = false;

	public function __construct() { }

	public static function start() {
		if ( self::$mc === false ) {
			self::$mc = new Memcached();
			self::$mc->addServer( '127.0.0.1', 11211 );
		}
	}
}

MC::start();

// Make this available to all of the tests
// https://pestphp.com/docs/underlying-test-case#uses
uses()->afterEach( function() {
	// Remove all the entries in Memcached after each test run
	// https://pestphp.com/docs/setup-and-teardown#afterEach
	MC::$mc->flush();
} )->in( __DIR__ );
