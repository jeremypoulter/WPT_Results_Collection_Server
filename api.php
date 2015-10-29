<?php

// Using;
// - https://github.com/slimphp/Slim
// - https://github.com/entomb/slim-json-api

require 'vendor/autoload.php';

require 'session.php';

error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
//header('Content-Type: application/json');

// Configuration
define('DATA_DIR', dirname(__FILE__).'/data');
define('SESSION_DIR', DATA_DIR.'/results');
define('STATUS_FILE', DATA_DIR.'/status');

// State
$found = false;

// Make sure our data/session dir is setup correctly
if(!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR);
}

if(!file_exists(SESSION_DIR)) {
    mkdir(SESSION_DIR);
}

// Load our status
if(file_exists(STATUS_FILE)) {
    $status = json_decode(file_get_contents(STATUS_FILE), true);
    $statusModified = false;
} else  {
    $status = array(
        'count' => 0
    );
    $statusModified = true;
}

$app = new \Slim\Slim();

$app->view(new \JsonApiView());
$app->add(new \JsonApiMiddleware());

$app->group('/results', function() use ($app) 
{
    $app->post('/', function () use ($app)  
    {
        global $status, $statusModified;
        // Create a new session
        $session = Session::createSession($status['count']);
        $status['count']++;
        $statusModified = true;
        $sessionInfo = $session->getInfo();
        $sessionInfo['href'] = $app->urlFor('results', array('id' => $session->id));
        Notify(array(
            'action' => 'create',
            'session' => $sessionInfo
        ));
        $app->render(200,array(
            'session' => $sessionInfo
        ));
    });
    $app->get('/', function () use ($app) 
    {
        $sessions = array();

        if ($dh = opendir(SESSION_DIR)) 
        {
            while (($file = readdir($dh)) !== false) 
            {
                if(Session::isValidSession($file))
                {
                    $session = new Session($file);
                    $sessionInfo = $session->getInfo();
                    $sessionInfo['href'] = $app->urlFor('results', array('id' => $file));
                    array_push($sessions, $sessionInfo);
                }
            }
            closedir($dh);
        }

        usort($sessions, function ($a, $b) {
            return $a['id'] - $b['id'];
        });

        $app->render(200, array(
            'sessions' => $sessions
        ));
    })->name('resultIndex');
    $app->get('/:id', function ($id) use($app) 
    {
        $session = new Session($id);
        $app->render(200, $session->GetResults());
    })->name('results');
    $app->post('/:id', function ($id) use($app) 
    {
        $session = new Session($id);
        $result = json_decode($app->request->getBody(), true);
        if(false != $result)
        {
            $index = $session->saveResult($result);
            $result['id'] = $index;
            Notify(array(
                'action' => 'result',
                'session' => $session->getInfo(),
                'result'  => $result,
            ));
            $app->render(200, array());
        } else {
            $app->render(400, array(
                'error' => true,
                'msg'   => 'Not JSON',
            ));
        }
    });
    $app->delete('/:id', function ($id) use($app) 
    {
        $session = new Session($id);
        $session->delete();
        Notify(array(
            'action' => 'delete',
            'session' => $id,
        ));
        $app->render(200, array());
    });
    $app->put('/:id/:index', function ($id, $index) use($app) 
    {
        $session = new Session($id);
        $result = json_decode($app->request->getBody(), true);
        if(false != $result)
        {
            $index = $session->saveResult($result, $index);
            $result['id'] = $index;
            Notify(array(
                'action' => 'result',
                'session' => $session->getInfo(),
                'result'  => $result,
            ));
            $app->render(200, array());
        } else {
            $app->render(400, array(
                'error' => true,
                'msg'   => 'Not JSON',
            ));
        }
    });
    $app->options('/:param+', function ($param) use($app) {
        $app->render(200, array());
    });
});
$app->get('/', function () use ($app)  {
    global $status, $statusModified;
    $app->render(200, array_merge($status, array(
        'links' => array(
            array(
                'rel' => 'results', 
                'href' => $app->urlFor('resultIndex')
            )
        )
    )));
});
$app->run();

if($statusModified)
{
    file_put_contents(STATUS_FILE, json_encode($status));
}

function Notify($entryData)
{
    $context = new ZMQContext();
    $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
    $socket->connect("tcp://localhost:5555");

    $socket->send(json_encode($entryData));
}

?>
