<?php
declare( strict_types = 1 );

test( 'flush_all', function() {
	$result = MC::$mc->flush();
	$this->assertEquals( true, $result );
} );
