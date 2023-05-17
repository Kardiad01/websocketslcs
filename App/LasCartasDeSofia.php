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
    private $ganador; 

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
        $this->ganador = null;
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

    public function setPropietarioTurno($id){
        $this->propietarioTurno = $id;
    }

    public function getterHabitacion(){
        return $this->nombre_partida;
    }

    public function resetMana(){
        $this->mana['retado'] = 10;
        $this->mana['retante'] = 10;
    }

    public function propietarioRobaCarta(){
        $jugador = '';
        if($this->propietarioTurno === $this->jugador_retado){
            //roba carta
            $jugador = 'retado';
        }else{
            //roba carta
            $jugador = 'retante';
        }
        if(count($this->mazo[$jugador])>0){
            $this->mano[$jugador][] = array_shift($this->mazo[$jugador]);
        }
        echo "\nmano tras robar carta\n";
        var_dump($this->mano[$jugador]);
    }

    ///ESTAMOS AQUI
    public function setInMesa($jugador, $card, $status){
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
        $esJugable = $this->esjugable($cartaJugada, $quienEsElJugadorQueJuega, $status);
        echo "\n STATUS ACTUAL\n";
        var_dump($this->status);
        echo "\n ¿¿Es jugable??";
        var_dump($esJugable);
        $porMaba = $this->consumirMana($cartaJugada->costo, $quienEsElJugadorQueJuega);
        echo "\n hay mana \n";
        var_dump($porMaba);
        $referencias = $this->referenciaEnCampo($cartaJugada, $quienEsElJugadorQueJuega);
        echo "\n hey estas ahí\n";
        var_dump($referencias);
        $sePueJugar = $this->esjugable($cartaJugada, $quienEsElJugadorQueJuega, $status) 
        && $this->consumirMana($cartaJugada->costo, $quienEsElJugadorQueJuega)
        && $this->referenciaEnCampo($cartaJugada, $quienEsElJugadorQueJuega);
        echo "\n JUGADOR $quienEsElJugadorQueJuega SELECCIONA CARTA ".var_dump($cartaJugada)." Y SE PUEDE JUEGAR? ".$sePueJugar."\n";
        if($sePueJugar){
            $this->status = $status;
            $currentMana = $this->mana[$quienEsElJugadorQueJuega] - $cartaJugada->costo;
            $this->mana[$quienEsElJugadorQueJuega] = $currentMana;
            $this->qutarDeMano($cartaJugada->id, $quienEsElJugadorQueJuega);
            $this->activarEfecto($cartaJugada, $jugador);
            $this->mesa[$quienEsElJugadorQueJuega][] = $cartaJugada;
        }
        /*echo "\n ESTADO DE LA MESA \n";
        var_dump($this->mesa);
        echo "\n Estado de la mano \n";
        var_dump($this->mano);*/
        /*echo "\n ESTADO DEL MAZO \n";
        var_dump($this->mazo);*/
    }

    private function referenciaEnCampo($carta, $jugador){
        if($carta->referencia!=null && $carta->referencia!="" && !is_array($carta->referencia)){
            $nombre = $carta->referencia;
            echo "\n Pues aquí está el nombre de la carta\n";
            var_dump($nombre);
            $filtro = array_filter($this->mesa[$jugador], function($cartas) use($nombre){
                echo "\n Id cartas en mesa \n";
                var_dump($cartas->id);
                if($cartas->id==$nombre){
                    return $cartas;
                }
            });
            if(count($filtro)>0){
                return true;
            }
            return false;
        }
        if($carta->referencia!=null && $carta->referencia!="" && is_array($carta->referencia)){
            $cumple = [];
            foreach($carta->referencia as $referencia){
                $filtro = array_filter($this->mesa[$jugador], function($cartas) use($referencia){
                    if($referencia == $cartas->referencia){
                        $cumple[] = true;
                    }
                });
            }
            $contTrues = 0;
            for($x=0; $x<count($cumple)-1; $x++){
                if($cumple[$x]==true){
                    $contTrues++;
                }
            }
            if($contTrues == count($cumple)-1){
                return true;
            } 
            return false;
        }
        return true;
    }

    private function recursiveRandom($numbers){
        echo "\n NUMEROS CANDIDATOS \n";
        var_dump($numbers);
        return $numbers[rand(0, count($numbers)-1)];
    }

    private function activarEfecto($carta, $id){
        $jugador = '';
        $oponente = '';
        echo "\n cosas entrantes \n";
        var_dump($carta, $id);
        echo "\n CARTA A JUGAR \n";
        var_dump($carta);
        if($id==$this->jugador_retado){
            echo "tenemos retado";
            $jugador = 'retado';
            $oponente = 'retante';
        }else if($id==$this->jugador_retante){
            echo "tenemos retante";
            $jugador = 'retante';
            $oponente = 'retado';
        }
        echo "\n Quitando puntos \n";
        if($carta->contra!=0 && $carta->contra!=null){
            $this->puntos_conviccion[$oponente] -= $carta->contra;
        } 
        echo "\n Ganando puntos \n";
        if($carta->beneficio!=0 && $carta->beneficio!=null){
            $this->puntos_conviccion[$jugador] += $carta->beneficio;
        } 
        echo "\n Robando carta \n";
        if($carta->roba>0){
            for($x=0; $x<$carta->roba; $x++){
                $this->robarCarta($jugador);
            }
        }
        echo "\n Bucando carta por tipo \n";
        $busca = $carta->busca;
        if($busca->cantidad!=0 && $busca->cantidad!=null){
            $tipo = $carta->busca->tipo;
            $cantidad = $carta->busca->cantidad;
            $candidates = array_filter($this->mazo[$jugador], function($element) use($tipo){
                if($element['tipo']===$tipo){
                    return $element;
                }
            });
            if(count($candidates)>$cantidad){
                $aux = 0;
                $rand = $this->recursiveRandom(array_keys($candidates));
                for($x=0; $x<$cantidad; $x++){
                    if($aux != $rand){
                        $aux = $rand;
                        $cartaBuscada = $candidates[$rand];
                        echo "\n LA CARTA ALEATORIAMENTE SELECCIONADA \n";
                        var_dump($cartaBuscada);
                        //quita de candidatas
                        unset($cartaBuscada[$rand]);
                        //quita del mazo
                        unset($this->mazo[$jugador][$rand]);
                        $this->mano[$jugador][] = $cartaBuscada;
                    }else{
                        $x--;
                        $rand = $this->recursiveRandom(array_keys($candidates));
                    }
                }            
            }
        }
        echo "\n Bucando carta individual \n";
        if($busca->cantidad==0 && intval(($busca->tipo)>0)){
            foreach($this->mazo[$jugador] as $cartas){
                if($cartas['id']==$busca->tipo){
                    echo "\n carta encontrada\n";
                    echo "\n $cartas->nombre \n";
                    $this->mano[$jugador][] = $cartas;
                }
            }
        }
        $this->testWinCondition();
    }

    private function qutarDeMano($id_carta, $player){
        $temp = array_filter($this->mano[$player], function($carta) use($id_carta){
            if($carta['id'] != $id_carta){
                return $carta;
            }
        });
        $this->mano[$player] = $temp;
    }

    
    public function consumirMana($costoCarta, $player){
        $currentMana = $this->mana[$player] - $costoCarta;
        if($currentMana < 0){
            return false;
        }else{
            return true;
        }
    }

    private function esJugable($cartaJugada, $tipo, $solicitud){
        if($cartaJugada->costo<=$this->mana[$tipo] && $cartaJugada->tipo === $solicitud){
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
        if(count($this->mazo[$player])>0){
            $this->mano[$player][] = array_shift($this->mazo[$player]);
            return true;
        }else{
            return false;
        }
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

    private function testWinCondition(){
        if($this->puntos_conviccion['retante']>=10){
            $this->ganador = $this->jugador_retante;
            $this->status = 'finpartida';
        }
        if($this->puntos_conviccion['retado']>=10){
            $this->ganador = $this->jugador_retado;
            $this->status = 'finpartida';
        }
        if(count($this->mazo['retante'])==0 && count($this->mazo['retado'])==0){
            $this->ganador = 'empate';
            $this->status = 'finpartida';
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
        $mensaje_a_emitir = json_encode([
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
            'cartas_mazo_oponente' => count($this->mazo[$oponente]),
            'cartas_mazo_jugador'=> count($this->mazo[$jugador]),
            'mana' => $this->mana,
            'ganador' => $this->ganador
        ]);
        return $mensaje_a_emitir;
    }
}

?>