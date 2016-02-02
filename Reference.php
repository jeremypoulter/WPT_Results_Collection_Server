<?php
class Reference
{
    public $id = false;
    private $dir = false;
    private $status = false;
    private $lock = false;

    static private $statusTypes = array('PASS', 'FAIL', 'TIMEOUT', 'ERROR');

    public function __construct($id)
    {
        if(!Reference::isValidResults($id)) {
            throw new Exception('Not a valid reference ID');
        }

        $this->id = $id;
        $this->dir = REFERENCE_DIR.'/'.$id;
        $this->loadState();
    }

    public function getResults($filterString = null, $pageIndex = null, $pageSize = null)
    {
        $results = array();

        $totalTests = 0;
        $totalResults = 0;

        $filters = (null !== $filterString) ? explode(',', $filterString) : self::$statusTypes;

        $temp = json_decode(file_get_contents($this->dir.'/results'), true);
        foreach($temp['results'] as $result)
        {
            $testStatus = $result['result'];
            $totalTests += count($result['subtests']);

            if(in_array($testStatus, $filters))
            {
                array_push($results, $result);
                $totalResults++;
            }
        }

        usort($results, function ($a, $b) {
            return $a['id'] - $b['id'];
        });

        if(null !== $pageIndex && null !== $pageSize) {
            $results = array_slice($results, ($pageIndex - 1) * $pageSize, $pageSize);
        }

        return array('results' => $results,
                     'totals' => $this->status['totals'],
                     'numResults' => $totalResults
                     );
    }

    public function verify($session)
    {
    }

    public function getName()
    {
        return array_key_exists('name', $this->status) ? $this->status['name'] : '';
    }

    public function getCount()
    {
        return $this->status['count'];
    }

    public function getCreatedTime()
    {
        return filectime($this->dir.'/status');
    }

    public function getModifiedTime()
    {
        return filemtime($this->dir.'/status');
    }

    public function getInfo()
    {
        return array(
            'rel' => 'reference',
            'id' => $this->id,
            'name' => $this->getName(),
            'count' => $this->getCount(),
            'totals' => $this->status['totals'],
            'created' => $this->getCreatedTime(),
            'modified' => $this->getModifiedTime()
        );
    }

    public static function isValidResults($id)
    {
        if(is_numeric($id))
        {
            $dir = REFERENCE_DIR.'/'.$id;
            if(is_dir($dir))
            {
                if(is_file($dir.'/status'))
                {
                    return true;
                }
            }
        }

        return false;
    }

    private function loadState()
    {
        $this->status = json_decode(file_get_contents($this->dir.'/status'), true);
    }
}
?>
