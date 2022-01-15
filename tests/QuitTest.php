<?php
declare( strict_types = 1 );

test( 'quit', function() {
	$result = MC::$mc->quit();
	$this->assertEquals( true, $result );
} );
