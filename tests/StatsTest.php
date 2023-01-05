<?php
declare( strict_types = 1 );

test( 'stats', function() {
	$result = MC::$mc->getStats();
	$first_key = array_key_first( $result );

	expect( $result )->toBeArray();
	$this->assertEquals( '127.0.0.1:11211', $first_key );
} );

test( 'stats uptime', function() {
	$result = MC::$mc->getStats();

	expect( $result['127.0.0.1:11211']['uptime'] )->toBeInt();

	# Really this could probably be only greater than zero, as most
	# tests are going to involve at least one second after the server starts.
	# But to make this test more reliable I'm going with the technically
	# more accurate greater than or equal.
	expect( $result['127.0.0.1:11211']['uptime'] )->toBeGreaterThanOrEqual( 0 );
} );
