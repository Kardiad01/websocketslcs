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
        $message = json_decode($msg, true);
        sleep(1);
        if($message['type']==='init'){
            $this->permissions[] = [
                'user' => $message['user'],
                'resource' => $from->resourceId
            ];
            var_dump($this->permissions);
        }
        $reciver = '';
        if($message['type']==='chat'){      
            $id_oyente = $message['data']['id_hablante'];
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
            $key = key($reciver);
            //SE GUARDA EN LA BASE DE DATOS EN CASO DE
            (new Model())->queryExec("INSERT INTO chat(id_hablante, id_oyente, mensaje) values (?, ?, ?)", 
                [$message['data']['id_hablante'], $message['data']['id_oyente'], $message['data']['message']]);
            echo "¿quien lo recibe?\n";
            var_dump($reciver);
            echo "estado de la lista\n";
            foreach ($this->clients as $client) {
                echo 'tipo de mensaje '.$message['type']."\n";
                echo 'cliente '.$client->resourceId."\n";
                echo "receptor ".$reciver[$key]['resource']."\n";
                //Implementada la lógica para el tipo de mensaje que tiene que ser enviado y a la persona conectada que se tiene que enviar.
                if ($message['type']==='chat' && $client->resourceId == $reciver[$key]['resource']) {
                    // The sender is not the receiver, send to each client connected
                    $client->send($msg);
                }
            }
        }
    }

    //se cierra conexión
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        unset($this->permissions[$conn->resourceId]);
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    //da error
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

}
?>