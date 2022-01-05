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
				value: $data->value,
				exptime: 0
			);

			if ( $status ) {
				$conn->send( 'STORED' );
			} else {
				$conn->send( 'NOT_STORED' );
			}

			return;
	}
};

$server->onClose = function ( TcpConnection $conn ) {
};

Worker::runAll();
