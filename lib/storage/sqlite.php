<?php
declare( strict_types = 1 );

class Memcached_Storage {
	protected static SQLite3 $db;

	public function __construct( string $db ) {
		if ( isset( self::$db ) ) {
			return;
		}

		self::$db = new SQLite3( $db );

		//
		// These are special case items that must be done before
		// any queries happen.
		//

		// Re-try when we run into a lock situation
		self::$db->busyTimeout( 1000 );

		// Enable Write-Ahead Logging
		// https://www.sqlite.org/wal.html
		$sql = 'PRAGMA journal_mode=WAL';
		verbose( "SQLite: $sql" );
		self::$db->exec( $sql );

		// After this point queries can happen

		$sql = 'SELECT name FROM sqlite_master WHERE type="table" AND name="storage"';
		verbose( "SQLite: $sql" );
		$table_check = self::$db->querySingle( $sql );
		if ( $table_check === null ) {
			$sql = <<<SQL
				CREATE TABLE IF NOT EXISTS 'storage' (
					'key' TEXT NOT NULL UNIQUE,
					'exptime' INTEGER NOT NULL,
					'flags' INTEGER NOT NULL,
					'added_ts' INTEGER NOT NULL,
					'cas' INTEGER NOT NULL,
					'value' BLOB NOT NULL,
					PRIMARY KEY( 'key' )
				);

				CREATE INDEX 'idx_added_ts' ON 'storage' (
					'added_ts' DESC
				);

				CREATE TABLE IF NOT EXISTS 'stats' (
					'action' TEXT NOT NULL UNIQUE,
					'count' INTEGER NOT NULL,
					PRIMARY KEY( 'action' )
				);
SQL;

			verbose( "SQLite: $sql" );
			self::$db->exec( $sql );
		}
	}

	private function _remove_key( string $key ): bool {
		$query = self::$db->prepare( 'DELETE FROM storage WHERE "key" = :key' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		verbose( "SQLite: {$query->getSQL( true )}" );
		return (bool) $query->execute();
	}

	public function add( string $key, int $flags, int $exptime, string|int $value ): bool {
		$query = self::$db->prepare( 'SELECT key FROM storage WHERE "key" = :key' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		verbose( "SQLite: {$query->getSQL( true )}" );
		$row = $query->execute()->fetchArray( SQLITE3_ASSOC );

		if ( $row !== false ) {
			return false;
		}

		$query = self::$db->prepare( 'INSERT INTO storage ( "key", "exptime", "flags", "added_ts", "cas", "value" ) VALUES ( :key, :exptime, :flags, :added_ts, :cas, :value )' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$query->bindValue( ':exptime', $exptime + time(), SQLITE3_INTEGER );
		$query->bindValue( ':flags', $flags, SQLITE3_INTEGER );
		$query->bindValue( ':added_ts', time(), SQLITE3_INTEGER );
		$query->bindValue( ':cas', 1, SQLITE3_INTEGER );
		$query->bindValue( ':value', $value, SQLITE3_BLOB );
		verbose( "SQLite: {$query->getSQL( true )}" );
		return (bool) $query->execute();

	}

	public function append( string $key, int $flags, int $exptime, string|int $value ): bool {
		$results = $this->get( [ $key ] );
		if ( [] === $results ) {
			return false;
		}

		return $this->set( $key, $flags, $exptime, "{$results[0]['value']}$value" );
	}

	public function cas( string $key, int $flags, int $exptime, string|int $value, int $cas ): bool {
		$query = self::$db->prepare( '
			UPDATE storage SET
				"exptime" = :exptime,
				"flags" = :flags,
				"cas" = cas + 1,
				"value" = :value
			WHERE
				"key" = :key
				AND "cas" = :cas
		' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$query->bindValue( ':exptime', $exptime + time(), SQLITE3_INTEGER );
		$query->bindValue( ':flags', $flags, SQLITE3_INTEGER );
		$query->bindValue( ':cas', $cas, SQLITE3_INTEGER );
		$query->bindValue( ':value', $value, SQLITE3_BLOB );
		verbose( "SQLite: {$query->getSQL( true )}" );

		return ( $query->execute() !== false && self::$db->changes() === 1 );
	}

	public function decr( string $key, int $value): mixed {
		return $this->incr( $key, -$value );
	}

	public function delete( string $key ): bool {
		$results = $this->get( [ $key ] );
		if ( [] === $results ) {
			return false;
		}

		$this->_remove_key( $key );
		return true;
	}

	public function flush_all(): bool {
		$sql = 'DELETE FROM storage';
		verbose( "SQLite: $sql" );
		return self::$db->exec( $sql );
	}

	public function get( array $keys ): array {
		$sql = 'SELECT * FROM storage WHERE "key" IN ( ';
		foreach ( $keys as $i => $k ) {
			$sql .= ":key{$i}, ";
		}
		$sql = substr( $sql, 0, -2 ) . ' )';

		$query = self::$db->prepare( $sql );
		foreach ( $keys as $i => $k ) {
			$query->bindValue( ":key{$i}", $k, SQLITE3_TEXT );
		}

		verbose( "SQLite: {$query->getSQL( true )}" );
		$result = $query->execute();
		$data = [];
		while ( $row = $result->fetchArray( SQLITE3_ASSOC ) ) {
			if ( time() > $row['exptime'] ) {
				$this->_remove_key( $row['key'] );
				continue;
			}

			$data[] = $row;
		}

		return $data;
	}

	public function incr( string $key, int $value): mixed {
		$results = $this->get( [ $key ] );
		if ( [] === $results ) {
			return false;
		}

		if ( !ctype_digit( $results[0]['value'] ) ) {
			return false;
		}

		$new_value = $results[0]['value'] + $value;

		$results = $this->set(
			$key,
			$results[0]['flags'],
			$results[0]['exptime'],
			$new_value
		);

		return ( $results === true ) ? $new_value : false;
	}

	public function prepend( string $key, int $flags, int $exptime, string|int $value ): bool {
		$results = $this->get( [ $key ] );
		if ( [] === $results ) {
			return false;
		}

		return $this->set( $key, $flags, $exptime, "$value{$results[0]['value']}" );
	}

	public function replace( string $key, int $flags, int $exptime, string|int $value ): bool {
		$current = $this->get( [ $key ] );
		if ( [] === $current ) {
			return false;
		}

		return $this->set( $key, $flags, $exptime, $value );
	}

	public function set( string $key, int $flags, int $exptime, string|int $value ): bool {
		$query = self::$db->prepare( 'INSERT INTO storage ( "key", "exptime", "flags", "added_ts", "cas", "value" ) VALUES ( :key, :exptime, :flags, :added_ts, :cas, :value ) ON CONFLICT("key") DO UPDATE SET "exptime" = :exptime, "flags" = :flags, "added_ts" = :added_ts, "cas" = :cas + 1, "value" = :value' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$query->bindValue( ':exptime', $exptime + time(), SQLITE3_INTEGER );
		$query->bindValue( ':flags', $flags, SQLITE3_INTEGER );
		$query->bindValue( ':added_ts', time(), SQLITE3_INTEGER );
		$query->bindValue( ':cas', 1, SQLITE3_INTEGER );
		$query->bindValue( ':value', $value, SQLITE3_BLOB );
		verbose( "SQLite: {$query->getSQL( true )}" );
		return (bool) $query->execute();

	}

	public function stat_curr_items() {
		$query = self::$db->prepare( 'SELECT count( "key" ) AS curr_items FROM storage' );
		verbose( "SQLite: {$query->getSQL( true )}" );
		$result = $query->execute();

		if ( $result !== false ) {
			return $result->fetchArray( SQLITE3_ASSOC ) ['curr_items'];
		}

		return false;
	}

	public function touch( string $key, int $exptime ): bool {
		$current = $this->get( [ $key ] );
		if ( [] === $current ) {
			return false;
		}

		return $this->set(
			$key,
			$current[0]['flags'],
			$exptime,
			$current[0]['value']
		);
	}
} 
