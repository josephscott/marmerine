<?php
declare( strict_types = 1 );

test( 'flush_all', function() {
	$result = MC::$mc->flush();
	$this->assertEquals( true, $result );
} );

test( 'flush_all delay', function() {
	$key = 'thing';
	$value = 'abc';
	$delay = 3;

	$result = MC::$mc->add( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	$result = MC::$mc->flush( $delay );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	sleep( $delay + 1 );
	$result = MC::$mc->get( $key );
	$this->assertEquals( false, $result );
} );
