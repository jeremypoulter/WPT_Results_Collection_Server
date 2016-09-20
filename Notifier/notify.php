<?php
require 'vendor/autoload.php';

require '../config.php';
require '../Session.php';

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Pusher implements WampServerInterface
{
    /**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();
    protected $clients = array();

private function subscribe(ConnectionInterface $conn, $topic)
    {
        $this->subscribedTopics[$topic->getId()] = $topic;
        if(preg_match("/".TEST_TOPIC."\/([0-9]+)/", $topic->getId(), $match) && 1 == $topic->count()) {
            $this->setSessionStatus(intval($match[1]), SessionState::Connected);
        }
    }

    private function unsubscribe(ConnectionInterface $conn, $topic)
    {
        if(preg_match("/".TEST_TOPIC."\/([0-9]+)/", $topic->getId(), $match) && 0 == $topic->count()) {
            $this->setSessionStatus(intval($match[1]), SessionState::Disconnected);
        }
    }

    private function setSessionStatus($sessionId, $status)
    {
        if(Session::isValidSession($sessionId))
        {
            $session = new Session($sessionId);
            $session->setStatus($status);
            $this->notify(ADMIN_TOPIC, array(
                'action' => 'update',
                'session' => $session->getInfo()
            ));
        }
    }

    private function notify($topicId, $data)
    {
        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($topicId, $this->subscribedTopics)) {
            return;
        }
        $topic = $this->subscribedTopics[$topicId];

        // send the data to all the clients subscribed to that action
        $topic->broadcast($data);
    }

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        echo "onSubscribe! {$topic->getId()} ({$conn->resourceId})\n";
        $this->subscribe($conn, $topic);
    }
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
        echo "onUnSubscribe! {$topic->getId()} ({$conn->resourceId})\n";
        $this->unsubscribe($conn, $topic);
    }
    public function onOpen(ConnectionInterface $conn) {
        echo "New connection! ({$conn->resourceId})\n";
        $this->clients[$conn->resourceId] = $conn;
    }
    public function onClose(ConnectionInterface $conn)
    {
        echo "Connection {$conn->resourceId} has disconnected\n";
        foreach($this->subscribedTopics as $topic) 
        {
            if($topic->has($conn)) {
                $this->unsubscribe($conn, $topic);
            }
        }
        unset($this->clients[$conn->resourceId]);
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->close();
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onAppEvent($entry)
    {
        $entryData = json_decode($entry, true);
        $this->notify($entryData['topic'], $entryData['data']);
    }
}

function clearStatus()
{
    if ($dh = opendir(SESSION_DIR))
    {
        while (($file = readdir($dh)) !== false)
        {
            if(Session::isValidSession($file))
            {
                $session = new Session($file);
                $session->setStatus(SessionState::Disconnected);
            }
        }
        closedir($dh);
    }
}

// Make sure all sessions are set to disconnect
clearStatus();

// Make sure that if we exit all the status are set to disconnect
register_shutdown_function('clearStatus');

$loop   = React\EventLoop\Factory::create();
$pusher = new Pusher;
// Listen for the web server to make a ZeroMQ push after an ajax request
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);
$pull->bind('tcp://127.0.0.1:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
$pull->on('message', array($pusher, 'onAppEvent'));
// Set up our WebSocket server for clients wanting real-time updates
$webSock = new React\Socket\Server($loop);
$webSock->listen(9001, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
$webServer = new Ratchet\Server\IoServer(
    new Ratchet\Http\HttpServer(
        new Ratchet\WebSocket\WsServer(
            new Ratchet\Wamp\WampServer(
                $pusher
            )
        )
    ),
    $webSock
);
$loop->run();
?>
