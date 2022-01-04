<?php
declare( strict_types = 1 );

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/protocols/memcached.php';
require_once __DIR__ . '/storage/sqlite.php';

$server = new Worker( 'Memcached://127.0.0.1:11211' );
$server->count = 4;

$server->onConnect = function ( TcpConnection $conn ) {
};

$server->onMessage = function ( TcpConnection $conn, object $data ) {

};

$server->onClose = function ( TcpConnection $conn ) {
}
