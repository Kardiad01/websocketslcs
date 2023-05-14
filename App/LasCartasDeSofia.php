<?php

namespace MyApp;

use stdClass;

class LasCartasDeSofia extends stdClass{

    private $nombre_partida;
    private $jugador_retante;
    private $jugador_retado;
    private $mazo;
    private $mano;
    private $mesa;
    private $replicas_usadas;
    private $contrareplicas_usadas;
    private $puntos_conviccion;
    private $win_conditions_disponibles;
    private $win_condition_realizada;
    private $total_cartas_diponibles;
    private $turno;
    private $chat;
    private $status = 'init';
    private $mana;
    private $propietarioTurno;
    private $coin;

    public function __construct($nombre_partida)
    {
        $this->nombre_partida = $nombre_partida;
        $this->mazo['retante'] = '';
        $this->mazo['retado'] = '';   
        $this->mano['retante'] = [];
        $this->mano['retado'] = [];
        $this->mesa['retante'] = [];
        $this->mesa['retado'] = [];
        $this->replicas_usadas['retante'] = [];
        $this->replicas_usadas['retado'] = [];
        $this->contrareplicas_usadas['retante'] = [];
        $this->contrareplicas_usadas['retado'] = [];
        $this->puntos_conviccion['retante'] = 0;
        $this->puntos_conviccion['retado'] = 0;
        $this->win_conditions_disponibles['retante'] = [];
        $this->win_conditions_disponibles['retado'] = [];
        $this->win_condition_realizada['retado'] = [];
        $this->win_condition_realizada['retante'] = [];
        $this->total_cartas_diponibles['retado'] = [];
        $this->total_cartas_diponibles['retante'] = [];
        $this->turno = 0;
        $this->mana['retante'] =10;
        $this->mana['retado'] =10;
        $this->chat=[];
    }

    public function setterRetante($jugador_retante, $mazo_retante){
        $this->jugador_retante = $jugador_retante;
        $this->mazo['retante'] = $mazo_retante;
    }

    public function setterRetado($jugador_retado, $mazo_retado){
        $this->jugador_retado = $jugador_retado;
        $this->mazo['retado'] = $mazo_retado;
    }

    public function getterPropietarioTurno(){
        return $this->propietarioTurno;
    }

    public function getterHabitacion(){
        return $this->nombre_partida;
    }

    ///ESTAMOS AQUI
    public function setInMesa($jugador, $card){
        $quienEsElJugadorQueJuega= '';
        if($this->jugador_retado==$jugador){
            $quienEsElJugadorQueJuega = 'retado';
        }
        if($this->jugador_retante==$jugador){
            $quienEsElJugadorQueJuega = 'retante';
        }
        $carta = $this->getCartaMazo($card, $quienEsElJugadorQueJuega);
        //Estos datos de cartas tienen que ser instanciados en la clase carta
        $cartaJugada = new Carta($carta[key($carta)]);
        echo "\n JUGADOR $quienEsElJugadorQueJuega SELECCIONA CARTA \n".var_dump($cartaJugada);
        $sePueJugar = $this->esjugable($cartaJugada, $quienEsElJugadorQueJuega) 
            && $this->consumirMana($cartaJugada->costo, $quienEsElJugadorQueJuega)
            && $this->referenciaEnCampo($cartaJugada, $quienEsElJugadorQueJuega);
        if($sePueJugar){
            $this->qutarDeMano($cartaJugada->id, $quienEsElJugadorQueJuega);
            $this->activarEfecto($cartaJugada, $jugador);
            $this->mesa[$quienEsElJugadorQueJuega][] = $cartaJugada;

        }
    }

    private function referenciaEnCampo($carta, $jugador){
        if($carta->referencia!=null && $carta->referencia!=""){
            $nombre = $carta->nombre;
            $filtro = array_filter($this->mazo[$jugador], function($cartas) use($nombre){
                if($cartas['nombre']===$nombre){
                    return $cartas;
                }
            });
            if(count($filtro)>0){
                return true;
            }
            return false;
        }
        return true;
    }

    private function activarEfecto($carta, $id){
        $jugador = '';
        $oponente = '';
        if($id===$this->jugador_retado){
            $jugador = 'retado';
            $oponente = 'retante';
        }else if($id===$this->jugador_retante){
            $jugador = 'retante';
            $oponente = 'retado';
        }
        if($carta->contra!=0 && $carta->contra!=null) $this->puntos_conviccion[$oponente] -= $carta->contra;
        if($carta->beneficio!=0 && $carta->beneficio!=null) $this->puntos_conviccion[$jugador] -= $carta->beneficio;
        if($carta->roba>0){
            for($x=0; $x<$carta->roba; $x++){
                $this->robarCarta($id);
            }
        }
        
        
    }

    private function qutarDeMano($id_carta, $player){
        $temp = array_filter($this->mano[$player], function($carta) use($id_carta){
            if($carta['id'] != $id_carta){
                return $carta;
            }
        });
        echo "\n CARTA JUGADA \n";
        //var_dump($temp);
        $this->mano[$player] = $temp;
    }

    public function consumirMana($costoCarta, $player){
        $currentMana = $this->mana[$player] - $costoCarta;
        if($currentMana < 0){
            return false;
        }else{
            $this->mana[$player] = $currentMana;
            return true;
        }
    }

    private function esJugable($cartaJugada, $tipo){
        if($cartaJugada->costo<=$this->mana[$tipo] && $cartaJugada->tipo === $this->status){
            return true;
        }
        return false;
    }

    private function getCartaMazo($card, $player){
        return array_filter($this->mano[$player], function($cartas) use($card){
            if($cartas['id']==$card){
                return $cartas;
            }
        });
    }

    private function robarCarta($player){
        $this->mano[$player][] = array_shift($this->mazo[$player]);
    }

    public function getterJugadorRetante(){
        return $this->jugador_retante;
    }

    public function getterJugadorRetado(){
        return $this->jugador_retado;
    }

    public function getterManoRetado(){
        return $this->mano['retado'];
    }

    public function getterManoRetante(){
        return $this->mano['retante'];
    }

    public function nuevoTurno(){
        $this->turno++;
    }

    public function setComentario($user, $mensaje){
        $this->chat[] =[
            'id' => $user,
            'mensaje' => $mensaje
        ];        
    }

    public function setStatus($status){
        if($this->status!=$status){
            $this->status = $status;
        }
    }

    public function getStatus(){
        return $this->status;
    }

    public function suffleInitDecks(){
        shuffle($this->mazo['retante']);
        shuffle($this->mazo['retado']);
        for($x=0; $x<5; $x++){
            $this->mano['retante'][] = array_shift($this->mazo['retante']);
            $this->mano['retado'][] = array_shift($this->mazo['retado']);
        }
        $this->coin = rand(0, 10);
        if($this->coin>=4){
            $this->propietarioTurno = $this->jugador_retado;
        }else{
            $this->propietarioTurno = $this->jugador_retante;
        }
    }

    public function partidaToString($id){
        $jugador = '';
        $oponente = '';
        if($id===$this->jugador_retado){
            $jugador = 'retado';
            $oponente = 'retante';
        }else if($id===$this->jugador_retante){
            $jugador = 'retante';
            $oponente = 'retado';
        }
        return json_encode([
            'nombre_partida' => $this->nombre_partida,
            'id_jugador' => ($this->jugador_retado===$id)?$this->jugador_retado:$this->jugador_retante,
            'rol' => $jugador,
            'oponente' => $oponente,
            "mano" => $this->mano[$jugador],
            'mesa' => $this->mesa,
            'replicas_usadas' => $this->replicas_usadas,
            'contrareplicas_usadas' => $this->contrareplicas_usadas,
            'puntos_conviccion' => $this->puntos_conviccion,
            'turno' => $this->turno,
            'chat' => $this->chat,
            'type' => $this->status,
            'propietario_turno' => $this->propietarioTurno,
            'cartas_mano_oponente' => count($this->mano[$oponente]),
            'mana' => $this->mana
        ]);
    }
}

?>