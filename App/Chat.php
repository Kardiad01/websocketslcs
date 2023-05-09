<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Model\Model;

class Chat implements MessageComponentInterface {

    protected $clients;
    protected array $permissions;
    protected $reciver;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    //se conecta
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ($conn->resourceId)\n";
    }

    //recibe mensaje
    public function onMessage(ConnectionInterface $from, $msg) {
        //hay que alterar el num rec al id_oyente del mensaje, de modo que luego lo reciba él validando previamente que sea él.
        /*echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');*/
        //1º Mandar los amigos del pive para que haga pum y ya sabe a quien tiene que dar mensaje.
        //var_dump($msg);
        echo "MENSAJE RECIBIDO \n";
        var_dump($msg);
        $message = json_decode(trim($msg), true);
        echo "MENSAJE PARSEADO \n";
        var_dump($message);
        if($message['type']=='accept' 
            || $message['type']=='delete' 
                || $message['type']=='request'
                    || $message['type']=='duelrequest'
                        || $message['type']==='aceptduel'){
            $this->p2pShort($msg, $message);
        }
        if($message['type']==='aceptduel'){
            $this->duplex($msg, $message, $from);
        }
        if($message['type']==='init'){
            $this->permissions[] = [
                'user' => $message['user'],
                'resource' => $from->resourceId
            ];
            $friends = json_decode($message['friends'], true);
            echo "AMIGOS\n";
            var_dump($friends);
            (new Model())->queryExec("UPDATE jugador SET enlinea = 1 WHERE id = ?", 
            [intval($message['user'])]);
            foreach($this->permissions as $user){
                foreach($friends as $otheruser){
                    if($user['user']==$otheruser['id_solicitante']){
                        foreach($this->clients as $client){
                            if($client->resourceId==$user['resource']){
                                $client->send($msg);
                            }
                        }
                    }
                }
            }
        }
        $reciver = '';
        if($message['type']==='chat'){      
            $id_oyente = $message['data']['id_oyente'];
            echo "lo que estoy recibiendo\n";
            var_dump($message);
            echo "Oyente \n";
            var_dump($id_oyente);
            $reciver = array_filter($this->permissions, function($element) use ($id_oyente){
                if($element['user']===$id_oyente){
                    echo "Elemento filtrado \n";
                    var_dump($element);
                    return $element;
                }
            });
            (new Model())->queryExec("INSERT INTO chat(id_hablante, id_oyente, mensaje) values (?, ?, ?)", 
                [$message['data']['id_hablante'], $message['data']['id_oyente'], $message['data']['message']]);
            if(!empty($reciver)){
                $key = key($reciver);
                foreach ($this->clients as $client) {
                    /*echo 'tipo de mensaje '.$message['type']."\n";
                    echo 'cliente '.$client->resourceId."\n";
                    echo "receptor ".$reciver[$key]['resource']."\n";*/
                    //Implementada la lógica para el tipo de mensaje que tiene que ser enviado y a la persona conectada que se tiene que enviar.
                    if ($message['type']==='chat' && $client->resourceId == $reciver[$key]['resource']) {
                        // The sender is not the receiver, send to each client connected
                        $client->send($msg);
                    }
                }
            }
        }
    }

    //se cierra conexión
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $resourceId = $conn->resourceId;
        $key = key(array_filter($this->permissions, function($element) use($resourceId){
            if($element['resource']===$resourceId){
                return $element;
            }
        }));
        (new Model())->queryExec("UPDATE jugador SET enlinea = 0 WHERE id = ?", 
        [intval($this->permissions[$key]['user'])]);
        foreach($this->clients as $client){
            if($client != $conn){
                $client->send(json_encode(['type'=>'close', 'close'=>$this->permissions[$key]]));
            }
        }
        unset($this->permissions[$key]);
        sort($this->permissions);
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    //da error
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * @method p2pShort() emite mensaje con id a usuario correspondiente
     */
    private function p2pShort($msg, $message){
        $id_oyente = $message['id'];
            $reciver = array_filter($this->permissions, function($element) use ($id_oyente){
                if($element['user']===$id_oyente){
                    echo "Elemento filtrado \n";
                    var_dump($element);
                    return $element;
                }
            });
        if(!empty($reciver)){
            $key = key($reciver);
            foreach ($this->clients as $client) {
                /*echo 'tipo de mensaje '.$message['type']."\n";
                echo 'cliente '.$client->resourceId."\n";
                echo "receptor ".$reciver[$key]['resource']."\n";*/
                //Implementada la lógica para el tipo de mensaje que tiene que ser enviado y a la persona conectada que se tiene que enviar.
                $key = key($reciver);
                echo "RECIVER\n";
                var_dump($reciver);
                if ($client->resourceId == $reciver[$key]['resource']) {
                    // The sender is not the receiver, send to each client connected
                    $client->send($msg);
                }
            }
        }
    }

    private function duplex($msg, $message){
        //$chalenge['user']==$message['id'] ||
        $ids = array_map(function($chalenge) use($message){
            if($chalenge['user']==$message['idr']){
                return $chalenge['resource'];
            }
        },
        $this->permissions);
        foreach($this->clients as $client){
            if(in_array($client->resourceId, $ids)){
                $client->send($msg);
            }
        }
    }

}
?>