<?php

class Template {

    private $values;

    /**
     * Overload saving values,
     * @param  $key
     * @param  $value
     * @return void
     */
    function __set($key, $value) {
        $this->values[$key] = $value;
    }

    /**
     * Overloading returning processed template.
     * @param  $template
     * @return void
     */
    public function render($template) {
        if (is_readable($templateFile = APP_DIR . "/templates/{$template}.phtml")) {
            extract($this->values, EXTR_SKIP);
            include $templateFile;
            die();
        } else {
            Soprano::exception('Template not found!');
        }
    }

}
