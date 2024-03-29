<?php
declare( strict_types = 1 );

test( 'get', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->set( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );
} );

test( 'get again', function() {
	$key = 'thing';
	$value = 'abc';

	$result = MC::$mc->set( $key, $value );
	$this->assertEquals( true, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );

	$result = MC::$mc->get( $key );
	$this->assertEquals( $value, $result );
} );

test( 'get exptime', function() {
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

test( 'get multiple keys', function() {
	$data = [
		'thing1' => 'abc',
		'thing2' => 'xyz',
		'thing3' => 123,
		'thing4' => 'hello world'
	];

	foreach ( $data as $k => $v ) {
		$result = MC::$mc->add( $k, $v );
		$this->assertEquals( true, $result );
	}

	$result = MC::$mc->getMulti( array_keys( $data ) );
	$this->assertEquals( $data, $result );
} );
