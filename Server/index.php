<?php

namespace Server;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Dotenv;
use MyApp\Chat;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(getcwd());
$dotenv->load();
define('HOST', $dotenv->required('HOST'));
define('DBNAME', $dotenv->required('DBNAME'));
define('USER', $dotenv->required('DBUSER'));
define('PASS', $dotenv->required('DBPASS'));

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8181
);


echo "Servidor abierto escuchando por el puerto 8181 direccion ws://localhost:8181 \n";
$server->run();

?>