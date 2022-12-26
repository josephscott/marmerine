<?php
declare( strict_types = 1 );

test( 'prepend', function() {
	$key = 'thing';
	$value = 'abc';

	// Compression breaks prepend
	MC::$mc->setOption( Memcached::OPT_COMPRESSION, false );

	$result = MC::$mc->add( $key, $value );
	$this->assertEquals( true, $result );

	$prepend = 'BEFORE-';
	$result = MC::$mc->prepend( $key, $prepend );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( "$prepend$value", $result );
} );

test( 'prepend without add', function() {
	$key = 'thing';
	$value = 'abc';

	// Compression breaks prepend
	MC::$mc->setOption( Memcached::OPT_COMPRESSION, false );

	$prepend = 'BEFORE-';
	$result = MC::$mc->prepend( $key, $prepend );
	$this->assertEquals( false, $result );
} );
