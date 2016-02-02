<?php
class ValidationReport
{
    public $id = false;
    private $dir = false;
    private $status = false;
    private $lock = false;

    static private $statusTypes = array('PASS', 'FAIL', 'TIMEOUT', 'ERROR');

    public function __construct($id)
    {
        if(!ValidationReport::isValidValidationReport($id)) {
            throw new Exception('Not a valid report ID');
        }

        $this->id = $id;
        $this->dir = VALIDATION_REPORT_DIR.'/'.$id;
        $this->loadState();
    }

    public function getReport($filterString = null, $pageIndex = null, $pageSize = null)
    {
        $totalTests = 0;
        $totalResults = 0;

        $filters = (null !== $filterString) ? explode(',', $filterString) : self::$statusTypes;

        $report = json_decode(file_get_contents($this->dir.'/report'), true);

/*
        usort($report, function ($a, $b) {
            return $a['id'] - $b['id'];
        });
*/

        if(null !== $pageIndex && null !== $pageSize) {
            $report = array_slice($report, ($pageIndex - 1) * $pageSize, $pageSize);
        }

        return array('report' => $report);
    }

    public function getName()
    {
        return array_key_exists('name', $this->status) ? $this->status['name'] : '';
    }

    public function setName($newName)
    {
        $this->lock();
        $this->loadState();
        if(!array_key_exists('name', $this->status) ||
           $newName != $this->status['name'])
        {
            $this->status['name'] = $newName;
            $this->saveState();
        }
        $this->unlock();
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
            'rel' => 'report',
            'id' => $this->id,
            'name' => $this->getName(),
            'tests_not_run' => $this->status['tests_not_run'],
            'subtests_not_run' => $this->status['subtests_not_run'],
            'tests_failed' => $this->status['tests_failed'],
            'subtests_failed' => $this->status['subtests_failed'],
            'created' => $this->getCreatedTime(),
            'modified' => $this->getModifiedTime()
        );
    }

    public function delete()
    {
        // Delete the session file under lock to mark as invalid while we delete everything else
        $this->lock();
        unlink($this->dir.'/status');
        $this->unlock();

        if ($dh = opendir($this->dir))
        {
            while (($file = readdir($dh)) !== false)
            {
                if('.' != $file && '..' != $file)
                {
                    unlink($this->dir.'/'.$file);
                }
            }
            closedir($dh);
        }

        rmdir($this->dir);
    }

    public static function isValidValidationReport($id)
    {
        if(is_numeric($id))
        {
            $dir = VALIDATION_REPORT_DIR.'/'.$id;
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

    private static function getTestName(&$test)
    {
        if(!is_array($test)) {
            return $test;
        }

        if(array_key_exists('url', $test)) {
            return $test['url'];
        }
        
        return $test[0];
    }

    public static function newReport($id, $sessionId, $referenceId)
    {
        if(false === Session::isValidSession($sessionId)) {
            throw new Exception('Session ID not valid');
        }
        if(false === Reference::isValidResults($referenceId)) {
            throw new Exception('Reference ID not valid');
        }
        if(!is_numeric($id)) {
            throw new Exception('Not a valid report ID');
        }
        if(ValidationReport::isValidValidationReport($id)) {
            throw new Exception('Report already exists');
        }

        $dir = VALIDATION_REPORT_DIR.'/'.$id;
        mkdir($dir);

        $session = new Session($sessionId);
        $sessionResults = $session->getResults();

        $reference = new Reference($referenceId);
        $referenceResults = $reference->getResults();

        // Index the sessionResults so we can look them up
        $sessionResults['tests'] = array();
        foreach ($sessionResults['results'] as $test)
        {
            $testName = ValidationReport::getTestName($test['test']);
            if(!array_key_exists($testName, $sessionResults['tests'])) {
                $sessionResults['tests'][$testName] = array();
            }

            foreach ($test['subtests'] as $subtest) 
            {
                $subtestName = str_replace(array('web-platform.test:8000',
                                                 'WEB-PLATFORM.TEST:8000'),
                                           array('w3c-test.org',
                                                 'W3C-TEST.ORG'),
                               $subtest['name']);

                // Update the results
                $sessionResults['tests'][$testName][$subtestName] = $subtest['status'];
            }
        }

        // Test statistics
        $testsNotRun = 0;
        $subtestsNotRun = 0;
        $testsFailed = 0;
        $subtestsFailed = 0;
        $log = array();

        // Work through each test
        foreach ($referenceResults['results'] as $test)
        {

            $testName = ValidationReport::getTestName($test['test']);
            if(!array_key_exists($testName, $sessionResults['tests'])) 
            {
                array_push($log, array(
                    'type' => 'error',
                    'message' => 'FAILURE - TEST NOT RUN',
                    'test' => $testName
                ));
                
                $testsNotRun++;
                continue;
            }

            // Counter to see if any of the subtests fail
            $failCheck = $subtestsFailed;

            foreach ($test['subtests'] as $subtest) 
            {
                $subtestName = str_replace(array('web-platform.test:8000',
                                                 'WEB-PLATFORM.TEST:8000'),
                                           array('w3c-test.org',
                                                 'W3C-TEST.ORG'),
                               $subtest['name']);

                // Check the passed tests
                if ($subtest['status'] === "PASS") 
                {
                    if(!array_key_exists($subtestName, $sessionResults['tests'][$testName])) 
                    {
                        array_push($log, array(
                            'type' => 'error',
                            'message' => 'FAILURE - SUBTEST NOT RUN',
                            'test' => $testName,
                            'subtest' => $subtestName
                        ));

                        $subtestsNotRun++;
                        continue;
                    }

                    if ($sessionResults['tests'][$testName][$subtestName] === "PASS") {
                        //fprintf(STDOUT, "  PASS: %s /  %s\n", $testName, $subtestName);
                    } 
                    else 
                    {
                        array_push($log, array(
                            'type' => 'error',
                            'message' => 'FAILURE - SUBTEST FAILED',
                            'test' => $testName,
                            'subtest' => $subtestName
                        ));

                        $subtestsFailed++;
                    }
                }
            }

            // Check if any of the subtests failed
            if ($failCheck != $subtestsFailed) {
                $testsFailed++;
            }
        }

        $status = array(
            'tests_not_run' => $testsNotRun,
            'subtests_not_run' => $subtestsNotRun,
            'tests_failed' => $testsFailed,
            'subtests_failed' => $subtestsFailed
        );

        file_put_contents($dir.'/status', json_encode($status));
        file_put_contents($dir.'/report', json_encode($log));

        return new ValidationReport($id);
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

    static private function &createStatsArray()
    {
        $stats = array();
        foreach(self::$statusTypes as $type) {
            $stats[$type] = 0;
        }
        $stats['ALL'] = 0;

        return $stats;
    }

    private function updateTestStats(&$result, &$totals)
    {
        $result['time'] = microtime(true);

        $status = 'PASS';
        $subTotals = self::createStatsArray();

        switch ($result['status'])
        {
            case "OK":
                foreach($result['subtests'] as $item)
                {
                    if ('PASS' != $item['status']) {
                        $status = $item['status'];
                    }
                    $subTotals[$item['status']]++;
                }
                break;
            default:
                $status = $result['status'];
                $subTotals[$result['status']]++;
        }

        $subTotals['ALL'] = count($result['subtests']);
        $result['totals'] = $subTotals;
        $result['result'] = $status;

        foreach(array_keys($totals) as $key) {
            $totals[$key] += $subTotals[$key];
        }
    }
}

?>
