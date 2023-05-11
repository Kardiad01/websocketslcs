<?php

namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use stdClass;

class Videogame implements MessageComponentInterface {
    
    protected $clients;
    protected $rooms;


    public function __construct(){
        $this->clients = new \SplObjectStorage;
        $this->rooms = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {                
        $this->clients->attach($conn);
        echo "\n----------------------------------------------------------------------------------------------------------------------------------------\nSTART LOG";
        echo "\nNew connection! ($conn->resourceId)\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "MENSAJE ENTRANTE\n";
        var_dump($msg);
        echo "MENSAJE MOLDEABLE\n";
        $mesage = json_decode($msg, true);
        var_dump($mesage);
        if($mesage['type']==='askstatus'){
            $from->id_user = $mesage['user'];
            $existeSala = $this->existeSala($mesage['room']);
            echo "ESTADO DE LA SALA EN ASKTATUS\n";
            var_dump($existeSala);
            if($existeSala===false){
                $nuevaPartida = new LasCartasDeSofia($mesage['room']);
                $this->rooms->attach($nuevaPartida);                
                $this->p2p($mesage['user'], $from, $nuevaPartida);
            }else{
                $this->p2p($mesage['user'], $from, $existeSala);
            }
        }
        if($mesage['type']==='start'){
            $existeSala = $this->existeSala($mesage['room']);
            echo "ESTADO DE LA SALA EN START\n";
            var_dump($existeSala);
            $this->anadirJugador($mesage['room'], $mesage['user'], $mesage['mazo']);
            $user1 = $existeSala->getterJugadorRetante();
            $user2 = $existeSala->getterJugadorRetado();
            if($user1!=null && $user2!=null){
                $this->initPartida($mesage['room']);
            }
        }
        
        if($mesage['type']==='conceptPlay'){
            
        }
        echo "\nEND LOG \n----------------------------------------------------------------------------------------------------------------------------------------";
        var_dump($this->rooms);
    }

    public function onClose(ConnectionInterface $conn) {
        echo "\n User ($conn->resourceId) disconected";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
    }

    private function existeSala($userroom){
        $bool = false;
        foreach($this->rooms as $room){
            if($room->getterHabitacion()===$userroom){
                $bool = $room;
            }
        }
        return $bool;
    }

    private function initPartida($room){
        //encuentra la sala correcta y mezcla los mazos y rescato la sala para reutilizarla
        $correctRoom = new stdClass;
        foreach($this->rooms as $availablerooms){
            if($availablerooms->getterHabitacion()===$room){
                $availablerooms->suffleInitDecks();
                $correctRoom = $availablerooms;
            }
        }
        if($correctRoom->getStatus()!=='ready'){
            //con la sala ya obtenida lo que hago es obtener el id de jugador para obtener el id source para emitir información
            $user1 = $correctRoom->getterJugadorRetante();
            $user2 = $correctRoom->getterJugadorRetado();
            //este foreach es para emitir la información al jugador correspondiente.
            foreach($this->clients as $player){
                if($player->id_user === $user1){
                    $player->send(json_encode(['type'=>'ready', 'data'=>$correctRoom->getterManoRetante()]));
                }
                if($player->id_user === $user2){
                    $player->send(json_encode(['type'=>'ready', 'data'=>$correctRoom->getterManoRetado()]));
                }
            }
            $correctRoom->setStatus('ready');
        }
    }

    private function anadirJugador($userroom, $user, $mazo){
        foreach($this->rooms as $room){
            if($room->getterHabitacion()===$userroom){
                if($room->getterJugadorRetante()===null){
                    $room->setterRetante($user, $mazo);
                }else if($room->getterJugadorRetado()===null){
                    $room->setterRetado($user, $mazo);
                }else{
                    echo "\n Puede que haya pasado algo raro XD\n";
                }
            }
        }
    }

    private function p2p($user, $from, $room){
        //este foreach es para emitir la información al jugador correspondiente.
        foreach($this->clients as $player){
            if($player->id_user == $user){
                if($room->getStatus()==='init'){
                    $from->send(json_encode(['type'=>'init']));
                }
                if($room->getStatus()==='ready' && $room->getterJugadorRetante()==$user){
                    $player->send(json_encode(['type'=>$room->getStatus(), 'data'=>$room->getterManoRetante()]));
                }
                if($room->getStatus()==='ready' && $room->getterJugadorRetado()==$user){
                    $player->send(json_encode(['type'=>$room->getStatus(), 'data'=>$room->getterManoRetado()]));
                }
            }
        }
    }

}

?>