<?php
declare( strict_types = 1 );

test( 'decr', function() {
	$key = 'thing';

	$result = MC::$mc->add( $key, 10 );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 10, $result );

	$result = MC::$mc->decrement( $key, 1 );
	$this->assertEquals( 9, $result );
} );

test( 'decr key does not exist', function() {
	$key = 'thing';

	$result = MC::$mc->decrement( $key, 1 );
	$this->assertEquals( false, $result );
} );

test( 'decr multiple times', function() {
	$key = 'thing';

	$result = MC::$mc->add( $key, 100 );
	$this->assertEquals( true, $result );

	$result = MC::$mc->decrement( $key, 1 );
	$this->assertEquals( 99, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 99, $result );

	$result = MC::$mc->decrement( $key, 11 );
	$this->assertEquals( 88, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 88, $result );

	$result = MC::$mc->decrement( $key, 18 );
	$this->assertEquals( 70, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 70, $result );
} );

test( 'decr string', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->add( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->decrement( $key, 1 );
	$this->assertEquals( false, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );
} );
