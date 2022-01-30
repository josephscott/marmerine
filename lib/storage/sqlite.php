<?php
declare( strict_types = 1 );

class Memcached_Storage {
	protected static $db = false;

	public function __construct( string $db ) {
		if ( self::$db === false ) {
			self::$db = new SQLite3( $db );
		}

		$table_check = self::$db->querySingle( 'SELECT name FROM sqlite_master WHERE type="table" AND name="storage"' );
		if ( $table_check === null ) {
			$sql = <<<SQL
				CREATE TABLE IF NOT EXISTS 'storage' (
					'key' TEXT NOT NULL UNIQUE,
					'exptime' INTEGER NOT NULL,
					'flags' INTEGER NOT NULL,
					'added_ts' INTEGER NOT NULL,
					'value' BLOB NOT NULL,
					PRIMARY KEY( 'key' )
				);

				CREATE INDEX 'idx_added_ts' ON 'storage' (
					'added_ts' DESC
				);
SQL;

			$create = self::$db->exec( $sql );
		}
	}

	private function _remove_key( string $key ): bool {
		$query = self::$db->prepare( 'DELETE FROM storage WHERE "key" = :key' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$result = $query->execute();

		if ( $result === false ) {
			return false;
		} else {
			return true;
		}
	}

	public function add( string $key, int $flags, int $exptime, string|int $value ): bool {
		$query = self::$db->prepare( 'SELECT key FROM storage WHERE "key" = :key' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$row = $query->execute()->fetchArray( SQLITE3_ASSOC );

		if ( $row !== false ) {
			return false;
		}

		$query = self::$db->prepare( 'INSERT INTO storage ( "key", "exptime", "flags", "added_ts", "value" ) VALUES ( :key, :exptime, :flags, :added_ts, :value )' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$query->bindValue( ':exptime', $exptime + time(), SQLITE3_INTEGER );
		$query->bindValue( ':flags', $flags, SQLITE3_INTEGER );
		$query->bindValue( ':added_ts', time(), SQLITE3_INTEGER );
		$query->bindValue( ':value', $value, SQLITE3_BLOB );
		$result = $query->execute();

		if ( $result !== false ) {
			return true;
		}

		return false;
	}

	public function delete( string $key ) {
		$results = $this->get( [ $key ] );
		if ( count( $results ) === 0 ) {
			return false;
		}

		$this->_remove_key( $key );
		return true;
	}

	public function flush_all(): bool {
		$result = self::$db->exec( 'DELETE FROM storage' );
		return $result;
	}

	public function get( array $keys ): mixed {
		$sql = 'SELECT * FROM storage WHERE "key" IN ( ';
		foreach ( $keys as $i => $k ) {
			$sql .= ":key{$i}, ";
		}
		$sql = substr( $sql, 0, -2 ) . ' )';

		$query = self::$db->prepare( $sql );
		foreach ( $keys as $i => $k ) {
			$query->bindValue( ":key{$i}", $k, SQLITE3_TEXT );
		}

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

	public function set( string $key, int $flags, int $exptime, string|int $value ): bool {
		$query = self::$db->prepare( 'INSERT INTO storage ( "key", "exptime", "flags", "added_ts", "value" ) VALUES ( :key, :exptime, :flags, :added_ts, :value ) ON CONFLICT("key") DO UPDATE SET "exptime" = :exptime, "flags" = :flags, "added_ts" = :added_ts, "value" = :value' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$query->bindValue( ':exptime', $exptime + time(), SQLITE3_INTEGER );
		$query->bindValue( ':flags', $flags, SQLITE3_INTEGER );
		$query->bindValue( ':added_ts', time(), SQLITE3_INTEGER );
		$query->bindValue( ':value', $value, SQLITE3_BLOB );
		$result = $query->execute();

		if ( $result !== false ) {
			return true;
		}

		return false;
	}

} 