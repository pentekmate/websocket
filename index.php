
<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/Shape.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory;
use React\Socket\SocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Shape\Shape;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $position; // Itt tároljuk a frontend által küldött tömböt
    protected $userShapes;

    private $shapes = ["triangle","circle","square"];
    private $gate1Position = [0,229,729,200];
    private $gate2Position = [752,229,729,952];

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->userShapes = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
        
        $newShape = new Shape($conn->resourceId);
        
        // $this->userShapes[] = $newShape;
        
        array_push($this->userShapes,$newShape);

        // Új kliensnek elküldjük az aktuális tömböt
        $conn->send(json_encode([
            "type" => "data_update",
            "id" => $conn->resourceId
        ]));
    
        $this->broadcast([
            "type" => "shape_update",
            "userShapes" => array_map(function($shape) {
                return [
                    "id" => $shape->id,
                    "position" => $shape->position,
                    "shape" => $shape->type
                ];
            }, $this->userShapes), // Átalakítjuk a Shape objektumokat egy tömbre
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
            
            foreach ($this->userShapes as $key => $value) {
                if ($value['id'] === $decoded['id']) {
                    // Frissítsd az adott elemet
                    $this->userShapes[$key]['position'] = $decoded['position']; // Példa új érték
                }
            }

            $shapePosition = $decoded['position'];
            $condition1 =  $shapePosition[1] < $this->gate1Position[2]  && $shapePosition[2] > $this->gate1Position[1]  &&
            $shapePosition[0] < $this->gate1Position[3] && $shapePosition[3] > $this->gate1Position[0];

            $condition2 =  $shapePosition[1] < $this->gate2Position[2]  && $shapePosition[2] > $this->gate2Position[1]  &&
            $shapePosition[0] < $this->gate2Position[3] && $shapePosition[3] > $this->gate2Position[0];

            if($condition1 || $condition2){
                $this->broadcast([
                    "type"=>"alert",
                    "message"=>"{$decoded['id']} elérte a határt."
                ]);
            }
            // Frissítést elküldjük minden kliensnek
            $this->broadcast([
                "type" => "shape_movement",
                "id" => $decoded['id'],
                "position"=>$decoded['position']
            ]);
        }
    }
    

    public function onClose(ConnectionInterface $conn) {    
        $this->clients->detach($conn);
        $filtereduserShapes = array_filter($this->userShapes, function($shape) use ($conn) {
            return $shape['id'] !== $conn->resourceId;
        });
        $this->userShapes = $filtereduserShapes;
        echo "Connection {$conn->resourceId} closed\n";

        $this->broadcast([
            "type" => "user_disconnected",
            "user_id" => $conn->resourceId,
            "userShapes"=>$this->userShapes
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
