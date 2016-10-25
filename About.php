<?php

/**
 * About short summary.
 *
 * About description.
 *
 * @version 1.0
 * @author jeremy
 */
class About
{
    private static function getServerVar($name, $default = 'n/a')
    {
        if(!array_key_exists($name, $_SERVER)) {
            return $default;
        }

        return $_SERVER[$name];
    }

    private static function getGitBranch()
    {
//        $shellOutput = [];
//        exec('git branch | ' . "grep ' * '", $shellOutput);
//        foreach ($shellOutput as $line) {
//            if (strpos($line, '* ') !== false) {
//                return trim(strtolower(str_replace('* ', '', $line)));
//            }
//        }
//        return null;
        return implode('/', array_slice(explode('/', file_get_contents('.git/HEAD')), 2));
    }

    public static function getInfo()
    {
        @list($system, $host, $kernel) = preg_split('/[\s,]+/', php_uname('a'), 5);
        return array(
            'version' => About::getGitBranch(),
            'date' => date('Y-m-d H:i:s T'),
            'system' => $system,
            'kernel' => $kernel,
            'host' => $host,
            'ip' => gethostbyname($host),
            'uptime' => @exec('uptime'),
            'http_server' => About::getServerVar('SERVER_SOFTWARE'),
            'php' => PHP_VERSION,
            'php_modules' => get_loaded_extensions(),
            'zend' => (function_exists('zend_version') ? zend_version() : 'n/a'),
            'hostbyaddress' => @gethostbyaddr(gethostbyname($host)),
            'http_proto' => About::getServerVar('SERVER_PROTOCOL'),
            'http_mode' => About::getServerVar('GATEWAY_INTERFACE'),
            'http_port' => About::getServerVar('SERVER_PORT')
        );
    }
}