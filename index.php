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
    protected $dataArray; // Itt tároljuk a frontend által küldött tömböt

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->dataArray = []; // Üres tömb kezdetben
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";

        // Új kliensnek elküldjük az aktuális tömböt
        $conn->send(json_encode([
            "type" => "data_update",
            "data" => $this->dataArray
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        // Kiírás csak a szerver konzolra, de NEM küldjük a klienseknek
        fwrite(STDOUT, "Client {$from->resourceId} sent: $msg\n");
    
        $decoded = json_decode($msg, true);
    
        if (!$decoded) {
            return;
        }
    
        // Ha egy új tömb érkezik a klienstől, frissítjük
        if ($decoded["type"] === "update_data") {
            $this->dataArray = $decoded["data"];
           
    
            // Frissítést elküldjük minden kliensnek
            $this->broadcast([
                "type" => "data_update",
                "data" => $this->dataArray
            ]);
        }
    }
    

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} closed\n";
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
