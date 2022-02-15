<?php
declare( strict_types = 1 );

test( 'touch', function() {
	$key = 'thing';
	$value = 'abc';
	$exptime = 2;

	$result = MC::$mc->add( $key, $value, $exptime );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	$result = MC::$mc->touch( $key, 3 );
	$this->assertEquals( true, $result );

	sleep( 2 );
	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	sleep( $exptime + 3 );
	$result = MC::$mc->get( $key );
	$this->assertEquals( false, $result );
} );
