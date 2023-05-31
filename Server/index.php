<?php

use MyApp\Videogame;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;


require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(getcwd());
$dotenv->load();
print_r("dsn = mysql:host=" . $_ENV['HOST'].";dbname=" . $_ENV['DBNAME'] . ";charset=utf8mb4");
print_r("\n ".$_ENV['DBUSER'].' '.$_ENV['DBPASS']."\n");
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Videogame()
        )
    ),
    8282
);
echo "Servidor VIDEOJUEGO abierto por el puerto 8282 en dirección ws://localhost:8282";
$server->run();