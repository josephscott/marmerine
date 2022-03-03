<?php
declare( strict_types = 1 );

test( 'cas', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->add( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key, null, Memcached::GET_EXTENDED );
	expect( $result )->toBeArray();
	expect( $result )->toHaveKeys( [ 'value', 'cas', 'flags' ] );
	$this->assertEquals( $value, $result['value'] );
} );
