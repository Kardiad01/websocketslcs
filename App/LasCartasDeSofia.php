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

    public function getterHabitacion(){
        return $this->nombre_partida;
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

    public function consumirMana($costoCarta, $player){
        $currentMana = $this->mana[$player] - $costoCarta;
        if($currentMana < 0){
            return 'No puedes jugar esa carta';
        }else{
            $this->mana[$player] = $currentMana;
        }
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
    }

    public function endPartida(){
        $this->nombre_partida = '';
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

    public function partidaToString(){
        return json_encode([
            'nombre_partida'=>$this->nombre_partida, 
            'mazoRetante'=>$this->mazo['retante'],
            'mazoRetado'=>['retado'] ,
            'manoRetante'=>$this->mano['retante'],
            'manoRetado'=>$this->mano['retado'],
            'mesaRetante'=>$this->mesa['retante'],
            'mesaRetado'=>$this->mesa['retado'],
            'replicasUsadasRetante'=>$this->replicas_usadas['retante'],
            'replicasUsadasRetado'=>$this->replicas_usadas['retado'],
            'contrareplicasUsadasRetante'=>$this->contrareplicas_usadas['retante'],
            'contrareplicasUsadasRetado'=>$this->contrareplicas_usadas['retado'],
            'puntosConviccionRetante'=>$this->puntos_conviccion['retante'],
            'puntosConviccionRetado'=>$this->puntos_conviccion['retado'],
            'winConditionsDisponiblesRetante'=>$this->win_conditions_disponibles['retante'],
            'winConditionsDisponiblesRetado'=>$this->win_conditions_disponibles['retado'],
            'winConditionsRealizadaRetado'=>$this->win_condition_realizada['retado'],
            'winConditionsRealizadaRetante'=>$this->win_condition_realizada['retante'],
            'totalCartasDisponiblesRetado'=>$this->total_cartas_diponibles['retado'],
            'totalCartasDisponiblesRetante'=>$this->total_cartas_diponibles['retante'],
            'turno'=>$this->turno,
        ]);
    }
}

?>