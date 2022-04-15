<?php
declare( strict_types = 1 );

use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/protocols/memcached-text.php';
require_once __DIR__ . '/lib/storage/sqlite.php';

$options = [
	'verbose' => 0, // 0 or 1
	'port' => 11211,
];

foreach ( $argv as $arg_option ) {
	if ( !str_starts_with( $arg_option, '--' ) ) {
		continue;
	}

	$arg_option = substr( $arg_option, 2 );
	list( $arg_name, $arg_value ) = explode( '=', $arg_option );

	if ( isset( $options[$arg_name] ) ) {
		$options[$arg_name] = $arg_value;
	}
}

function verbose( $msg ) {
	global $options;

	if ( (int) $options['verbose'] !== 1 ) {
		return;
	}

	echo "$msg\n";
}

$server = new Worker( "Memcached_Text://127.0.0.1:{$options['port']}" );
$server->count = 4;

$server->onConnect = function ( TcpConnection $conn ) {
};

$server->onMessage = function ( TcpConnection $conn, object $data ) {
#	$storage = new Memcached_Storage( ':memory:' );
	$storage = new Memcached_Storage( __DIR__ . '/data/marmerine.db' );
	$storage->enable( 'WAL' );

	verbose( "{$conn->id} > {$data->command}" );
	switch ( $data->command ) {
		case 'add':
		case 'set':
		case 'replace':
		case 'append':
		case 'prepend':
		case 'cas':
			if ( $data->command === 'cas' ) {
				$status = $storage->{$data->command}(
					key: $data->key,
					flags: $data->flags,
					exptime: $data->exptime,
					value: $data->value,
					cas: $data->cas
				);
			} else {
				$status = $storage->{$data->command}(
					key: $data->key,
					flags: $data->flags,
					exptime: $data->exptime,
					value: $data->value,
				);
			}

			if ( $data->noreply ) {
				return;
			}

			if ( $status ) {
				$conn->send( 'STORED' );
			} else {
				$conn->send( 'NOT_STORED' );
			}

			return;

		case 'touch':
			$status = $storage->touch(
				key: $data->key,
				exptime: $data->exptime
			);

			if ( $status ) {
				$conn->send( 'TOUCHED' );
			} else {
				$conn->send( 'NOT_FOUND' );
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

		case 'gets':
			$results = $storage->get( keys: $data->keys );
			foreach ( $results as $r ) {
				$conn->send( 'VALUE ' . $r['key'] . ' ' . $r['flags'] . ' ' . strlen( $r['value'] ) . ' ' . $r['cas'] );
				$conn->send( $r['value'] );
			}
			$conn->send( 'END' );

			return;

		case 'incr':
		case 'decr':
			$results = $storage->{$data->command}(
				key: $data->key,
				value: $data->value
			);

			if ( $results === false ) {
				$conn->send( 'CLIENT_ERROR cannot increment or decrement non-numeric value' );
			} else {
				$conn->send( $results );
			}

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
			$conn->send( '0.0.2' );
			return;
	}
};

$server->onClose = function ( TcpConnection $conn ) {
};

Worker::runAll();
