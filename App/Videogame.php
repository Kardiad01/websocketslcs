<?php

namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


class Videogame implements MessageComponentInterface {
    
    protected $clients;
    protected $rooms;


    public function __construct(){
        $this->clients = new \SplObjectStorage;
        $this->rooms = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {                
        $this->clients->attach($conn);
        echo "\nNew connection! ($conn->resourceId)\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "MENSAJE ENTRANTE\n";
        var_dump($msg);
        echo "MENSAJE MOLDEABLE\n";
        $mesage = json_decode($msg, true);
        var_dump($mesage);
        if($mesage['type']==='start'){
            $from->id_user = $mesage['user'];
            $existeSala = $this->existeSala($mesage['room']);
            echo "¿EXISTE ESA SALA?\n";
            var_dump($existeSala);
            if($existeSala){
                $this->anadirJugador($mesage['room'], $mesage['user'], $mesage['deck']);
            }else{
                $nuevaPartida = new LasCartasDeSofia($mesage['room']);
                $this->rooms->attach($nuevaPartida);
                $this->anadirJugador($mesage['room'], $mesage['user'], $mesage['deck']);
            }
        }
        echo "SALAS DISPONIBLES \n";
        var_dump($this->rooms);
        echo "USUARIOS EXISTENTES\n";
        //var_dump($this->clients);
    }

    public function onClose(ConnectionInterface $conn) {
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }

    private function existeSala($userroom){
        $bool = false;
        foreach($this->rooms as $room){
            if($room->getterHabitacion()===$userroom){
                $bool = true;
            }
        }
        return $bool;
    }

    private function anadirJugador($userroom, $user, $mazo){
        foreach($this->rooms as $room){
            if($room->getterHabitacion()===$userroom){
                if($room->getterJugadorRetante()===null){
                    $room->setterRetante($user, $mazo);
                }else if($room->getterJugadorRetado()===null){
                    $room->setterRetado($user, $mazo);
                }else{
                    echo "\n Puede que haya pasado algo raro XD";
                }
            }
        }
    }

}

?>