<?php
declare( strict_types = 1 );

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/protocols/memcached-text.php';
require_once __DIR__ . '/storage/sqlite.php';

$server = new Worker( 'Memcached_Text://127.0.0.1:11211' );
$server->count = 4;

$server->onConnect = function ( TcpConnection $conn ) {
};

$server->onMessage = function ( TcpConnection $conn, object $data ) {
	$storage = new Memcached_Storage();

	switch ( $data->command ) {
		case 'add':
		case 'set':
			$status = $storage->{$data->command}(
				key: $data->key,
				flags: $data->flags,
				exptime: $data->exptime,
				value: $data->value,
			);

			if ( $status ) {
				$conn->send( 'STORED' );
			} else {
				$conn->send( 'NOT_STORED' );
			}

			return;

		case 'get':
			$results = $storage->get( key: $data->key );
			if ( $results !== false ) {
				$conn->send( 'VALUE ' . $results['key'] . ' ' . $results['flags'] . ' ' . strlen( $results['value'] ) );
				$conn->send( $results['value'] );
			}
			$conn->send( 'END' );

			return;
	}
};

$server->onClose = function ( TcpConnection $conn ) {
};

Worker::runAll();
