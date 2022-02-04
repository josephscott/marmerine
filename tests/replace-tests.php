<?php
declare( strict_types = 1 );

test( 'replace', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->add( $key, $value );
	$this->assertEquals( true, $result );

	$value .= '-replaced';
	$result = MC::$mc->replace( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );
} );

test( 'replace exptime', function() {
	$key = 'thing';
	$value = 'abc';
	$exptime = 2;

	$result = MC::$mc->set( $key, $value, $exptime );
	$this->assertEquals( true, $result );

	$value .= '-replaced';
	$result = MC::$mc->replace( $key, $value, $exptime );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	sleep( $exptime + 1 );
	$result = MC::$mc->get( $key );
	$this->assertEquals( false, $result );
} );
