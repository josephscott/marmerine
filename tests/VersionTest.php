<?php
declare( strict_types = 1 );

test( 'version', function() {
	$result = MC::$mc->getVersion();
	$first_key = array_key_first( $result );

	expect( $result )->toBeArray();
	$this->assertEquals( '127.0.0.1:11211', $first_key );
	expect( $result[$first_key] )->toBeString();
} );
