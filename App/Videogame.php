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

    /**
     * Este será el método que reciba y traduzca los mensajes para la aplicación.
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        echo "MENSAJE ENTRANTE\n";
        var_dump($msg);
        echo "MENSAJE MOLDEABLE\n";
        $mesage = json_decode($msg, true);
        var_dump($mesage);
        $existeSala = $this->existeSala($mesage['room']);
        if($mesage['type']==='askstatus'){
            $from->id_user = $mesage['user'];
            echo "\n entro en askstatus\n";
            if($existeSala===false){
                $nuevaPartida = new LasCartasDeSofia($mesage['room']);
                echo "SALA CREADA CON ÉXITO\n";
                $this->rooms->attach($nuevaPartida);                
                $this->p2p($mesage['user'], $nuevaPartida);
                echo "\nJUGADOR 1 METIDO DE MANERA CORRECTA\n";
            }else if($existeSala->getStatus()!='init'){
                $this->p2p($from->id_user, $existeSala);
            }else{
                $this->p2p($mesage['user'], $existeSala);
                echo "\nJUGADOR 2 METIDO DE MANERA CORRECTA\n";
            }
        }
        if($existeSala!= null 
            && $mesage['type']==='start'){   
                echo "\n entro en start\n";      
                $this->anadirJugador($mesage['room'], $mesage['user'], $mesage['mazo']);
                $user1 = $existeSala->getterJugadorRetante();
                $user2 = $existeSala->getterJugadorRetado();
                if($user1!=null && $user2!=null){
                    $this->initPartida($mesage['room']);
                    echo "\nPARTIDA INICIADA CORRECTAMENTE\n";
                }
        }
        
        if($existeSala!= null 
            && $mesage['type']==='concepto' 
            && $existeSala->getterPropietarioTurno()==$mesage['id_jugador']){
                echo "\n entro en concepto\n";
                $user1 = $existeSala->getterJugadorRetado();
                $user2 = $existeSala->getterJugadorRetante();
                $existeSala->setInMesa($mesage['id_jugador'], $mesage['id_carta'], $mesage['type']);
                $this->p2p($user1, $existeSala);
                $this->p2p($user2, $existeSala);
        }

        if($mesage['type']==='replica'
            && $existeSala!= null 
            && $mesage['id_jugador']!=$existeSala->getterPropietarioTurno()
            && $existeSala->getStatus()==='concepto'){
                echo "\n entro en replica\n";
                $user1 = $existeSala->getterJugadorRetado();
                $user2 = $existeSala->getterJugadorRetante();         
                //$existeSala->setStatus($mesage['type']);
                $existeSala->setInMesa($mesage['id_jugador'], $mesage['id_carta'], $mesage['type']);
                $this->p2p($user1, $existeSala);
                $this->p2p($user2, $existeSala);
        }
        if($mesage['type']==='contrareplica'
            && $existeSala!= null 
            && $mesage['id_jugador']==$existeSala->getterPropietarioTurno()
            && $existeSala->getStatus()==='replica'){
                echo "\n entro en contraréplica\n";
                $user1 = $existeSala->getterJugadorRetado();
                $user2 = $existeSala->getterJugadorRetante();         
                //$existeSala->setStatus($mesage['type']);
                $existeSala->setInMesa($mesage['id_jugador'], $mesage['id_carta'], $mesage['type']);
                $this->p2p($user1, $existeSala);
                $this->p2p($user2, $existeSala);
            }
        if($mesage['type']==='finturno' 
            && $existeSala!= null 
            && $existeSala->getterPropietarioTurno()==$mesage['id_jugador']){
                echo "\n entro en finturno\n";
                $user1 = $existeSala->getterJugadorRetado();
                $user2 = $existeSala->getterJugadorRetante();
                if($user1!=$existeSala->getterPropietarioTurno()){
                    $existeSala->setPropietarioTurno($user1);
                }else{
                    $existeSala->setPropietarioTurno($user2);
                }
                $existeSala->resetMana();
                $existeSala->propietarioRobaCarta();
                $existeSala->setStatus('finturno');
                $this->p2p($user1, $existeSala);
                $this->p2p($user2, $existeSala);
        }
        if($mesage['type']==='finpartida'){
            $removedRoom = $this->existeSala($mesage['room']);
            if($removedRoom!=null){
                $this->rooms->detach($removedRoom);
            }
            var_dump($this->rooms);
        }
        if($mesage['type']==='surrender'){

        }
        
        echo "\nEND LOG \n----------------------------------------------------------------------------------------------------------------------------------------";

    }

    public function onClose(ConnectionInterface $conn) {
        var_dump($this->rooms);
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
            $correctRoom->setStatus('ready');
            $this->p2p($user1, $correctRoom);
            $this->p2p($user2, $correctRoom);
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
                    echo "\n Puede que haya pasado algo raro XD \n";
                }
            }
        }
    }

    /**
     * Ahora toca hacer un event looop de salas... para no sé, hacer que tenga que escribir 
     * un 200% menos de código para hacer lo mismo
     */

    /**
     * Este será el método que mande todas las mierdas a los usuarios... definido por la 
     * santa ley de mis cojones al viento
     */
    private function p2p($user, $room){
        echo "\nEMITIENDO MENSAJES\n";
        //este foreach es para emitir la información al jugador correspondiente.
        foreach($this->clients as $player){
            if($player->id_user == $user){
                if($room->getStatus()==='init'){
                    $player->send(json_encode(['type'=>'init']));
                }else{
                    $player->send($room->partidaToString($player->id_user));
                }
            }
        }
    }

}

?>