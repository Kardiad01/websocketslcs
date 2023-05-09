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
        $this->puntos_conviccion['retante'] = [];
        $this->puntos_conviccion['retado'] = [];
        $this->win_conditions_disponibles['retante'] = [];
        $this->win_conditions_disponibles['retado'] = [];
        $this->win_condition_realizada['retado'] = [];
        $this->win_condition_realizada['retante'] = [];
        $this->total_cartas_diponibles['retado'] = [];
        $this->total_cartas_diponibles['retante'] = [];
        $this->turno = 0;
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

    public function getterHabitacion(){
        return $this->nombre_partida;
    }

    public function getterJugadorRetante(){
        return $this->jugador_retante;
    }

    public function getterJugadorRetado(){
        return $this->jugador_retado;
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

}

?>