<?php

namespace MyApp;

use stdClass;

class Carta{    
    
    public $id;
    public $nombre;
    public $tipo;
    public $contra;
    public $beneficio;
    public $referencia;
    public $costo;
    public $busca;
    public $roba;
    public $turno;
    public $descripcion;

    public function __construct($params){
        $this->id = $params['id'];
        $this->nombre = $params['nombre'];
        $this->tipo = $params['tipo'];
        $this->contra = intval($params['contra']);
        $this->beneficio = intval($params['beneficio']);
        $this->costo = intval(explode('@', $params['costo'])[0]);
        $this->roba = intval($params['roba']);
        $this->turno = $params['turnos'];
        $this->descripcion = $params['descripcion'];
        $this->setBusca($params['busca']);
    }

    private function setBusca($param){
        $temp = explode('|', $param);
        if(empty($temp)){
            $temp = explode('@', $param);
            $this->busca = new stdClass;
            $this->busca->cantidad = intval($temp[0]);
            $this->busca->tipo = $temp[1];
        }
    }

    public function activarEfecto(){
    }

}

?>