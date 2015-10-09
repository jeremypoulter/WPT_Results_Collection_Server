<?php
class Session
{
    public $id = false;
    private $dir = false;
    private $status = false;
    private $lock = false;

    public function __construct($id)
    {
        $this->id = $id;
        $this->dir = SESSION_DIR.'/'.$id;
        if(!is_dir($this->dir)) {
            mkdir($this->dir);
        }
        if(!is_file($this->dir.'/status')) 
        {
            $this->status = array(
                'count' => 0
            );
            $this->saveState();
        }
    }

    public function getResults()
    {
        $results = array();

        if ($dh = opendir($this->dir)) 
        {
            while (($file = readdir($dh)) !== false) 
            {
                if(is_numeric($file)) 
                {
                    $result = json_decode(file_get_contents($this->dir.'/'.$file), true);
                    array_push($results, $result);
                }
            }
            closedir($dh);
        }

        return array('results' => $results);
    }

    public function saveResult($result, $index = false)
    {
        $this->lock();

        $this->loadState();
        if(false === $index) {
            $index = $this->status['count'];
            $this->status['count']++;
            $this->saveState();
        } else if($index >= $this->status['count']) {
            $this->status['count'] = $index + 1;
            $this->saveState();
        }

        $this->unlock();

        file_put_contents($this->dir.'/'.$index, json_encode($result));

    }

    private function loadState()
    {
        $this->status = json_decode(file_get_contents($this->dir.'/status'), true);
    }

    private function saveState()
    {
        file_put_contents($this->dir.'/status', json_encode($this->status));
    }

    private function lock()
    {
        $this->lock = fopen($this->dir.'/lock', "w");
        return flock($this->lock, LOCK_EX); // acquire an exclusive lock
    }

    private function unlock()
    {
        flock($this->lock, LOCK_UN);    // release the lock
        fclose($this->lock);

        $this->lock = false;
        unlink($this->dir.'/lock');
    }
}
?>