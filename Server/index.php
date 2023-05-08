<?php

namespace Server;

use Dotenv;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require dirname(__DIR__) . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(getcwd());
$dotenv->load();
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8181
);
echo "Servidor CHAT abierto escuchando por el puerto 8181 direccion ws://localhost:8181 \n";
$server->run();

?>