
<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Controllers//GameController.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory;
use React\Socket\SocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Shape\Shape;
use GameController\GameController;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    private $game;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->game = new GameController();
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
        
        $this->game->createShape($conn->resourceId);
        
        // Új kliensnek elküldjük az aktuális tömböt
        $conn->send(json_encode([
            "type" => "data_update",
            "id" => $conn->resourceId
        ]));
        

        $shapes = $this->game->getShapes();
        $this->broadcast([
            "type" => "shape_update",
            "userShapes" => array_map(function($shape) {
                return [
                    "id" => $shape->id,
                    "position" => $shape->position,
                    "shape" => $shape->type
                ];
            },$shapes), // Átalakítjuk a Shape objektumokat egy tömbre
        ]);
    }
    

    public function onMessage(ConnectionInterface $from, $msg) {
        // Ellenőrizzük, hogy a JSON érvényes-e
        $decoded = json_decode($msg, true);
    
        if (!is_array($decoded)) {
            echo "Invalid JSON received: $msg\n";
            return;
        }
    
        // Ellenőrizzük, hogy a "type" kulcs létezik-e
        if (!isset($decoded["type"])) {
            echo "Missing 'type' key in received message: $msg\n";
            return;
        }
    
        // Ha egy új tömb érkezik a klienstől, frissítjük
        if ($decoded["type"] === "update_shape_position") {
            if (!isset($decoded["position"]) || !is_array($decoded["position"])) {
                echo "Invalid data format\n";
                return;
            }
            
            $this->game->moveShape($decoded['id'],$decoded['position']);

            if($this->game->checkPosition($decoded['position'],$decoded['id'])){
                $currentResult = json_encode($this->game->getResult());
                $this->game->restoreShapePosition();

                $this->broadcast([
                    "type"=>"alert",
                    "message"=>"{$decoded['id']} elérte a határt.",
                    "result"=>"{$currentResult}"
                ]);
                $shapes = $this->game->getShapes();
                $this->broadcast([
                    "type" => "shape_reset",
                    "userShapes" => array_map(function($shape) {
                        return [
                            "id" => $shape->id,
                            "position" => $shape->position,
                            "shape" => $shape->type
                        ];
                    },$shapes),
                ]);

               
            }
          
            $this->broadcast([
                "type" => "shape_movement",
                "id" => $decoded['id'],
                "position"=>$decoded['position']
            ]);
        }
    }
    

    public function onClose(ConnectionInterface $conn) {    
        $this->clients->detach($conn);
        $this->game->removeShape($conn->resourceId);

        
        echo "Connection {$conn->resourceId} closed\n";
        $shapes = $this->game->getShapes();
        $this->broadcast([
            "type" => "user_disconnected",
            "user_id" => $conn->resourceId,
            "userShapes" => array_map(function($shape) {
                return [
                    "id" => $shape->id,
                    "position" => $shape->position,
                    "shape" => $shape->type
                ];
            },$shapes), // Átalakítjuk a Shape objektumokat egy tömbre
        ]);

    }
    

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    private function broadcast($message) {
        foreach ($this->clients as $client) {
            $client->send(json_encode($message));
        }
    }
}



// Event Loop létrehozása (ReactPHP)
$loop = Factory::create();
$socket = new SocketServer('0.0.0.0:8080', [], $loop);
$server = new IoServer(new HttpServer(new WsServer(new WebSocketServer())), $socket, $loop);

echo "WebSocket server running on ws://localhost:8080\n";
$loop->run();
