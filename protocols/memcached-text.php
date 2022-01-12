<?php
declare( strict_types = 1 );

namespace Protocols;

use Workerman\Connection\ConnectionInterface;

class Memcached_Text {
	protected static $parts = null;

	public static function input( string $buffer, ConnectionInterface $conn ) {
		// Look for the text line command
		// https://github.com/memcached/memcached/blob/master/doc/protocol.txt
		$cmd_end = \strpos( $buffer, "\r\n" );
		if ( $cmd_end === false ) {
			// Need to read more input
			return 0;
		}

		// Break out the command pieces
		self::$parts = \explode( ' ', \substr( $buffer, 0, $cmd_end ) );
		self::$parts['cmd_end'] = $cmd_end;

		if ( !empty( self::$parts[0] ) ) {
			if (
				self::$parts[0] === 'add'
				|| self::$parts[0] === 'set'
			) {
				self::$parts[2] = (int) self::$parts[2];
				self::$parts[3] = (int) self::$parts[3];
				self::$parts[4] = (int) self::$parts[4];

				$full_size = $cmd_end + 2 + self::$parts[4] + 2;
				if ( \strlen( $buffer ) !== $full_size ) {
					// Need to read more input
					return 0;
				}
			}
		}

		return \strlen( $buffer );
	}

	public static function decode( string $buffer, ConnectionInterface $conn ) {
		$data = new \StdClass();
		$data->command = self::$parts[0];

		switch( $data->command ) {
		case 'add':
		case 'set':
			$data->key = self::$parts[1];
			$data->flags = self::$parts[2];
			$data->exptime = self::$parts[3];
			$data->value = \substr(
				$buffer,
				self::$parts['cmd_end'] + 2,
				self::$parts[4]
			);

			$data->noreply = false;
			if (
				isset( self::$parts[5] )
				&& self::$parts[5] === 'noreply'
			) {
				$data->noreply = true;
			}

			break;
		case 'get':
			$data->key = self::$parts[1];
			break;
		}

		return $data;
	}

	public static function encode( string $data, ConnectionInterface $conn ) {
		return $data . "\r\n";
	}
}
