<?php

abstract class Soprano {

    private $routes = array();
    protected $template;

    public function __construct() {
        if (!isset($_SESSION)) session_start();
        
        ini_set('display_errors', 1);
        error_reporting(E_ALL|E_STRICT);
        //ini_set('display_errors', 0);
        //error_reporting(E_ALL);

        include "template.php";
        $this->template = new Template();

        include "factory.php";
        include "state.php";

        define('APP_DIR', dirname(__FILE__) . '/../application');
        define('HTTP_DIR', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'] . '../'));
    }

    /**
     * Run the application
     * @return void
     */
    public function run() {
        $requestLength = count($request = explode('/', @$_GET['request']));
        //self::diagnostics($request);
        foreach ($this->routes as $route) {
            // matching method
            if ($route['httpMethod'] == $_SERVER['REQUEST_METHOD']) {
                $parameters = array();
                // capture params
                preg_match_all('/:([a-zA-Z]+)/', implode('/', $route['url']), $captured, PREG_PATTERN_ORDER);
                foreach ($captured[1] as $param) $parameters[$param] = '';
                
                $mapLength = count($route['url']);
                // parts match
                if ($mapLength == $requestLength) {
                    // traverse matching each part in turn
                    for ($i = 0; $i < $mapLength; $i++) {
                        if (strpos($route['url'][$i], ':') !== FALSE) {
                            // replace variable param with param passed
                            $parameters[substr($route['url'][$i], 1)] = $request[$i];
                        } else {
                            if ($route['url'][$i] != $request[$i]) break;
                        }
                        // reached the end...
                        if ($i + 1 == $mapLength) {
                            $method = new ReflectionMethod($this, $route['methodName']);
                            if (!empty($parameters)) {
                                echo $method->invokeArgs($this, $parameters);
                                return;
                            } else {
                                echo $method->invoke($this, null);
                                return;
                            }
                        }
                    }
                }
            }
        }
        self::exception('No route match!');
    }

    /**
     * HTTP GET
     * @param  $url
     * @param  $methodName
     * @return void
     */
    public function get($url, $methodName) {
        $this->map('GET', $url, $methodName);
    }

    /**
     * Map url to a resource
     * @param  $httpMethod
     * @param  $url
     * @param  $methodName
     * @return void
     */
    private function map($httpMethod, $url, $methodName) {
        if (method_exists($this, $methodName)) {
            // cleanup on aisle four
            $url = explode('/', trim($url, '/'));
            //self::diagnostics($url);
            array_push($this->routes, array('httpMethod' => $httpMethod, 'url' => $url, 'methodName' => $methodName));
        } else {
            self::exception("Resource {$methodName} not implemented!");
        }
    }

    /**
     * Fire up an 'exception', terminating app execution.
     * @static
     * @param  $text
     * @return void
     */
    public static function exception($text) {
        die("
            <div style='color:#333;font-family:\"Helvetica\"'>
                <h3>Application Exception</h3>
                <pre>{$text}</pre>
            </div>
        ");
    }

    public static function diagnostics($var) {
        echo '<pre style="background:#FFF;color:#333;font-size:10px;font-family:"Helvetica"">';
        print_r($var);
        echo '</pre>';
    }

}