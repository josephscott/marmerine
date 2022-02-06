<?php
declare( strict_types = 1 );

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/protocols/memcached-text.php';
require_once __DIR__ . '/lib/storage/sqlite.php';

$server = new Worker( 'Memcached_Text://127.0.0.1:11211' );
$server->count = 4;

$server->onConnect = function ( TcpConnection $conn ) {
};

$server->onMessage = function ( TcpConnection $conn, object $data ) {
	$storage = new Memcached_Storage( __DIR__ . '/data/marmerine.db' );

	switch ( $data->command ) {
		case 'add':
		case 'set':
		case 'replace':
		case 'append':
			$status = $storage->{$data->command}(
				key: $data->key,
				flags: $data->flags,
				exptime: $data->exptime,
				value: $data->value,
			);

			if ( $data->noreply ) {
				return;
			}

			if ( $status ) {
				$conn->send( 'STORED' );
			} else {
				$conn->send( 'NOT_STORED' );
			}

			return;

		case 'delete':
			$status = $storage->delete( key: $data->key );

			if ( $data->noreply ) {
				return;
			}

			if ( $status ) {
				$conn->send( 'DELETED' );
			} else {
				$conn->send( 'NOT_FOUND' );
			}

			return;

		case 'get':
			$results = $storage->get( keys: $data->keys );
			foreach ( $results as $r ) {
				$conn->send( 'VALUE ' . $r['key'] . ' ' . $r['flags'] . ' ' . strlen( $r['value'] ) );
				$conn->send( $r['value'] );
			}
			$conn->send( 'END' );

			return;

		case 'flush_all':
			if ( $data->delay > 0 ) {
				$timer_id = Timer::add(
					$data->delay,
					function () use ( &$timer_id, $storage ) {
						$storage->flush_all();
						Timer::del( $timer_id );
					}
				);
			} else {
				$storage->flush_all();
			}

			if ( !$data->noreply ) {
				$conn->send( 'OK' );
			}
			return;

		case 'quit':
			$conn->close();
			return;

		case 'version':
			$conn->send( '0.0.1' );
			return;
	}
};

$server->onClose = function ( TcpConnection $conn ) {
};

Worker::runAll();
