<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use React\EventLoop\Factory;
use React\Socket\SocketServer;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class WebSocketServer implements MessageComponentInterface {
    protected $clients;
    protected $position; // Itt tároljuk a frontend által küldött tömböt
    protected $shapes;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->position = []; // Üres tömb kezdetben
        $this->shapes = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
    
        // Felhasználóhoz rendelt shape tárolása asszociatív tömbként
        $this->shapes[] = [
            "id" => $conn->resourceId,
            "shape" => "triangle" // Vagy bármilyen más alakzat
        ];
    
        // Új kliensnek elküldjük az aktuális tömböt
        $conn->send(json_encode([
            "type" => "data_update",
            "data" => $this->position,
            "id" => $conn->resourceId
        ]));
    
        $this->broadcast([
            "type" => "shape_update",
            "shapes" => $this->shapes, // Az összes felhasználó alakzatainak listája
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
        if ($decoded["type"] === "update_data") {
            if (!isset($decoded["data"]) || !is_array($decoded["data"])) {
                echo "Invalid data format\n";
                return;
            }
    
            $this->position = $decoded["data"];
    
            // Frissítést elküldjük minden kliensnek
            $this->broadcast([
                "type" => "data_update",
                "data" => $this->position
            ]);
        }
    }
    

    public function onClose(ConnectionInterface $conn) {

    
        $this->clients->detach($conn);
        $filteredShapes = array_filter($this->shapes, function($shape) use ($conn) {
            return $shape['id'] === $conn->resourceId;
        });
        $this->shapes = $filteredShapes;
        echo "Connection {$conn->resourceId} closed\n";

        $this->broadcast([
            "type" => "user_disconnected",
            "user_id" => $conn->resourceId
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
