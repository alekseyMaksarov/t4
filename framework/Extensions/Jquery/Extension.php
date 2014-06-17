<?php

namespace T4\Extensions\Jquery;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        if ( isset($this->options->location) && 'local'==$this->options->location ) {
            $assets->publishJs($this->assetsPath.'/lib/js/jquery-2.1.0.min.js');
        } else {
            $assets->registerJs('http://code.jquery.com/jquery-2.1.0.min.js');
        }

        if (!empty($this->options->ui)) {
            $assets->registerCss('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
            $assets->registerJs('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js');
        }
    }

}