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
        $this->setReferencia($params['referencia']);
    }

    private function setReferencia($param){
        echo "\n PARAM ENTRANTE REFERENCIA\n";
        var_dump($param);
        $temp = explode('|', $param);
        echo "\n PARAM SALIENTE TRAS CHOPEO\n";
        var_dump($temp);
        if(count($temp)>1){
            $this->referencia = $temp;
        }else{
            $this->referencia = intval($param);
        }
    }

    private function setBusca($param){
        echo "\n PARAM ENTRANTE \n";
        var_dump($param);
        echo "\n PARAM CHOPEADO \n";
        $temp = explode('|', $param);
        var_dump($temp);
        if(count($temp)>1){
            echo "\n BUSCO MIERDA VARIADA\n";
            $tempcant = [];
            $temptipo = [];
            foreach($temp as $find){
                $temp = explode('@', $find);
                $tempcant[] = intval($temp[0]);
                $temptipo[] = $temp[1];
            }
            $this->busca = new stdClass;
            $this->busca->cantidad = $tempcant;
            $this->busca->tipo = $temptipo;
            return;
        }
        $temp = explode('@', $param);
        if(count($temp)>1){
            echo "\n BUSCO UNA MIERDA PERO VARIAS\n";
            $this->busca = new stdClass;
            $this->busca->cantidad = explode('@', $param)[0];
            $this->busca->tipo = explode('@', $param)[1];
            return;
        }
        if(!str_contains('@', $param)){
            echo "\n BUSCO UNICA MIERDA\n";
            $this->busca = new stdClass;
            $this->busca->cantidad = 0;
            $this->busca->tipo = $param;
            return;
        }
        $this->busca = null;
    }

}

?>