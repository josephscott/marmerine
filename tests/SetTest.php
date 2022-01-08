<?php
declare( strict_types = 1 );

test( 'set', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->set( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );
} );

test( 'set duplicate', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->set( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->set( $key, $value );
	$this->assertEquals( true, $result );
} );

test( 'set exptime', function() {
	$key = 'thing';
	$value = 'abc';
	$exptime = 2;

	$result = MC::$mc->set( $key, $value, $exptime );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	sleep( $exptime + 1 );
	$result = MC::$mc->get( $key );
	$this->assertEquals( false, $result );
} );
