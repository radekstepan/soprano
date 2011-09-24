<?php

final class Factory {

    /**
     * Build a model object or its stateful adapter.
     * @static
     * @param  $model
     * @param  $state
     * @return Object
     */
    public static function build($model, $state=null) {
        $stateFile = APP_DIR . '/states/' . ucfirst($model) . 'State.php';
        
        // is model stateful?
        if (is_readable($stateFile)) {
            include_once $stateFile;
            // name convention
            $model .= 'State';
            // build it
            return new $model($state);
        } else {
            // return a new vanilla model
            if (is_readable($modelFile = APP_DIR . "/models/{$model}Model.php")) {
                include_once $modelFile;
                return new $model();
            } else Soprano::exception('Model not found!');
        }
    }
    
}