<?php

abstract class State {

    /** var @Object puppy storage */
    private $modelObject;

    /**
     * Return a name of the object so we know our child.
     * @abstract
     * @return string name of @Object
     */
    abstract function getObjectName();

    /**
     * A method launched on 'new' factory creation.
     * @abstract
     * @return void
     */
    abstract function initialize();

    /**
     * Load the object or create anew.
     * @param  $state set to 'new' to skip any saved objects
     * @return void
     */
    public function __construct($state=null) {
        $model = $this->getObjectName();
        // serializing needs class definition
        $modelFile = APP_DIR . "/models/{$model}Model.php";
        if (is_readable($modelFile)) {
            include_once $modelFile;

            // are we scrapping old object?
            if ($state == 'new') {
                $this->destroy($this->getObjectName());
                $this->modelObject = new $model();
                $this->initialize();
            } else {
                // check if we can load ourselves from session/db etc...
                if (!$this->load($this->getObjectName())) {
                    $this->modelObject = new $model();
                }
            }
        } else Soprano::exception('Model not found!');
    }

    /**
     * Overload all calls to the adapter and redir them to the @Object contained.
     * __callStatic() only in PHP >= 5.3.0
     * @param  $method
     * @param  $parameters
     * @return
     */
    public function __call($method, $parameters) {
        try {
            $method = new ReflectionMethod($this->modelObject, $method);
            if ($method->getNumberOfRequiredParameters() > 0) {
                return $method->invokeArgs($this->modelObject, $parameters);
            } else {
                return $method->invoke($this->modelObject, null);
            }
        } catch (ReflectionException $e) {
            Soprano::exception($e->getMessage());
        }
    }

    /**
     * Save the object, maintain state.
     * @return void
     */
    public function __destruct() {
        $this->save($this->getObjectName());
    }

    /**
     * Load an object from a database.
     * @param  $objectName
     * @return bool
     */
    private function load($objectName) {
        if (isset($_SESSION[$objectName])) {
            $this->modelObject = unserialize(urldecode($_SESSION[$objectName]));
            return true;
        }
        return false;
    }

    /**
     * Save object into the database.
     * @param  $objectName
     * @return void
     */
    private function save($objectName) {
        $_SESSION[$objectName] = urlencode(serialize($this->modelObject));
    }

    /**
     * Delete object in a database.
     * @param  $objectName
     * @return void
     */
    private function destroy($objectName) {
        if (isset($_SESSION[$objectName])) unset($_SESSION[$objectName]);
    }

}
