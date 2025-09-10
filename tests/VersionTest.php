<?php
declare( strict_types = 1 );

// This test does not work if the memcached server returns something less
// than 1.0.0 - so disable this for now
/*
test( 'version', function() {
	$result = MC::$mc->getVersion();
	$first_key = array_key_first( $result );

	expect( $result )->toBeArray();
	$this->assertEquals( '127.0.0.1:' . MARMERINE_PORT, $first_key );
	expect( $result[$first_key] )->toBeString();
} );
 */
