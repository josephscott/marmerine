<?php
declare( strict_types = 1 );

test( 'incr', function() {
	$key = 'thing';

	$result = MC::$mc->add( $key, 1 );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 1, $result );

	$result = MC::$mc->increment( $key, 1 );
	$this->assertEquals( 2, $result );
} );

test( 'incr key does not exist', function() {
	$key = 'thing';

	$result = MC::$mc->increment( $key, 1 );
	$this->assertEquals( false, $result );
} );

test( 'incr multiple times', function() {
	$key = 'thing';

	$result = MC::$mc->add( $key, 1 );
	$this->assertEquals( true, $result );

	$result = MC::$mc->increment( $key, 1 );
	$this->assertEquals( 2, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 2, $result );

	$result = MC::$mc->increment( $key, 21 );
	$this->assertEquals( 23, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 23, $result );

	$result = MC::$mc->increment( $key, 23 );
	$this->assertEquals( 46, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( 46, $result );
} );

test( 'incr string', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->add( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->increment( $key, 1 );
	$this->assertEquals( false, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );
} );
