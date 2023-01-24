<?php
declare( strict_types = 1 );

const MARMERINE_VERSION = '0.0.4';

define( 'MARMERINE_START_TIME', time() );

$stats = [
	'start_time' => MARMERINE_START_TIME,
	'version' => MARMERINE_VERSION,
	'pid' => getmypid(),
	'total_connections' => 0
];

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
		// Options that expect integers
		if (
			$arg_name === 'verbose'
			|| $arg_name === 'port'
		) {
			$arg_value = (int) $arg_value;
		}

		$options[$arg_name] = $arg_value;
	}
}

function verbose( $msg ) {
	global $options;

	if ( $options['verbose'] === 1 ) {
		echo trim( $msg ) . "\n";
	}
}

function bump_stat( string $stat ) {
	global $stats;

	if ( !isset( $stats[$stat] ) ) {
		$stats[$stat] = 1;
	} else {
		$stats[$stat]++;
	}
}

$server = new Worker( "Memcached_Text://127.0.0.1:{$options['port']}" );
$server->count = 4;
$server->name = 'Marmerine v'.MARMERINE_VERSION;

$server->onWorkerStart = static function() {
	global $storage;
	//$storage = new Memcached_Storage( ':memory:' );
	$storage = new Memcached_Storage( __DIR__ . '/data/marmerine.db' );
};

$server->onConnect = static function ( TcpConnection $conn ) {
	bump_stat( 'total_connections' );
};

$server->onMessage = static function ( TcpConnection $conn, object $data ) {

	global $storage;

	bump_stat( "cmd_{$data->command}" );

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
				bump_stat( "{$data->command}_hits" );
				$conn->send( 'STORED' );
			} else {
				bump_stat( "{$data->command}_misses" );
				$conn->send( 'NOT_STORED' );
			}

			return;

		case 'touch':
			$status = $storage->touch(
				key: $data->key,
				exptime: $data->exptime
			);

			if ( $status ) {
				bump_stat( "{$data->command}_hits" );
				$conn->send( 'TOUCHED' );
			} else {
				bump_stat( "{$data->command}_misses" );
				$conn->send( 'NOT_FOUND' );
			}

			return;

		case 'delete':
			$status = $storage->delete( key: $data->key );

			if ( $data->noreply ) {
				return;
			}

			if ( $status ) {
				bump_stat( "{$data->command}_hits" );
				$conn->send( 'DELETED' );
			} else {
				bump_stat( "{$data->command}_misses" );
				$conn->send( 'NOT_FOUND' );
			}

			return;

		case 'get':
			$results = $storage->get( keys: $data->keys );

			if ( count( $results ) > 0 ) {
				bump_stat( "{$data->command}_hits" );
			} else {
				bump_stat( "{$data->command}_misses" );
			}

			foreach ( $results as $r ) {
				$conn->send( 'VALUE ' . $r['key'] . ' ' . $r['flags'] . ' ' . strlen( $r['value'] ) );
				$conn->send( $r['value'] );
			}
			$conn->send( 'END' );

			return;

		case 'gets':
			$results = $storage->get( keys: $data->keys );

			if ( count( $results ) > 0 ) {
				bump_stat( "{$data->command}_hits" );
			} else {
				bump_stat( "{$data->command}_misses" );
			}

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
				bump_stat( "{$data->command}_misses" );
				$conn->send( 'CLIENT_ERROR cannot increment or decrement non-numeric value' );
			} else {
				bump_stat( "{$data->command}_hits" );
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

		case 'stats':
			global $stats;

			// The protocol supports an arguement for the stats command, but
			// but using it is explicitly not documented, and generally
			// throws an error.
			if ( isset( $data->key ) ) {
				$conn->send( 'ERROR' );
				return;
			}

			$conn->send( 'STAT uptime ' . ( time() - MARMERINE_START_TIME ) );
			$conn->send( 'STAT time ' . time() );

			foreach ( $stats as $k => $v ) {
				$conn->send( "STAT $k $v" );
			}

			$curr_items = $storage->stat_curr_items();
			if ( $curr_items !== false ) {
				$conn->send( "STAT curr_items $curr_items" );
			}

			$conn->send( 'END' );
			return;

		case 'quit':
			$conn->close();
			return;

		case 'version':
			$conn->send( MARMERINE_VERSION );
			return;

		// Command not suported
		default:
			$conn->send( 'ERROR' );
	}
};

$server->onClose = static function ( TcpConnection $conn ) {
};

Worker::runAll();
