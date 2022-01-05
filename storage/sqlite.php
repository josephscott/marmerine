<?php
declare( strict_types = 1 );

class Memcached_Storage {
	protected static $db = false;

	public function __construct() {
		if ( self::$db === false ) {
			self::$db = new SQLite3( dirname( __DIR__ ) . '/data/storage.db' );
		}

		$table_check = self::$db->query( 'SELECT name FROM sqlite_master WHERE type="table" AND name="storage"' );
		if ( $table_check === false ) {
			$table = <<<SQL
				CREATE TABLE IF NOT EXISTS 'storage' (
					'id' INTEGER,
					'key' TEXT NOT NULL UNIQUE,
					'exptime' INTEGER NOT NULL,
					'added_ts' INTEGER NOT NULL,
					'value' BLOB NOT NULL,
					PRIMARY KEY( 'id' AUTOINCREMENT )
				);
SQL;

			$index = <<<SQL
				CREATE INDEX 'idx_added_ts' ON 'storage' (
					'added_ts' DESC
				);
SQL;

			$create = self::$db->query( $table );
			$create = self::$db->query( $index );
		}
	}

	public function add( string $key, string|int $value, int $exptime ): bool {
		$query = self::$db->prepare( 'SELECT id FROM storage WHERE "key" = :key' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$id = $query->execute();

		if ( $id !== false ) {
			return false;
		}

		$query = self::$db->prepare( 'INSERT INTO storage ( "key", "exptime", "added_ts", "value" ) VALUES( :key, :exptime, :added_ts, :value )' );
		$query->bindValue( ':key', $key, SQLITE3_TEXT );
		$query->bindValue( ':exptime', $exptime + time(), SQLITE3_INTEGER );
		$query->bindValue( ':added_ts', time(), SQLITE3_INTEGER );
		$query->bindValue( ':value', $value, SQLITE3_BLOB );
		$result = $query->execute();

		if ( $result !== false ) {
			return true;
		}

		return false;
	}
} 
