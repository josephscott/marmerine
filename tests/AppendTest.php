<?php
declare( strict_types = 1 );

test( 'append', function() {
	$key = 'thing';
	$value = 'abc';

	// Compression breaks append
	MC::$mc->setOption( Memcached::OPT_COMPRESSION, false );

	$result = MC::$mc->add( $key, $value );
	$this->assertEquals( true, $result );

	$append = '-AFTER';
	$result = MC::$mc->append( $key, $append );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( "$value$append", $result );
} );

test( 'append without add', function() {
	$key = 'thing';
	$value = 'abc';

	// Compression breaks append
	MC::$mc->setOption( Memcached::OPT_COMPRESSION, false );

	$append = '-AFTER';
	$result = MC::$mc->append( $key, $append );
	$this->assertEquals( false, $result );
} );
