<?php

require(__DIR__ .'/vendor/autoload.php');

$config = new \Mapado\CS\Config();

$config->getFinder()
    ->in([
        __DIR__.'/src',
    ])
    // if you want to exclude Tests directory
    // ->exclude([ 'Tests' ])
;

return $config;
