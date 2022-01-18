<?php
declare( strict_types = 1 );

test( 'delete', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->set( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	$result = MC::$mc->delete( $key );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( false, $result );

} );
