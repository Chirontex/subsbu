<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 * @since 0.0.2
 */
spl_autoload_register(function($classname) {

    if (strpos($classname, 'Subsbu') !== false) {

        $path = __DIR__.'/src/';

        $file = explode('\\', $classname);

        if (count($file) > 2) {

            for ($i = 1; $i < (count($file) - 1); $i++) {

                $path .= $file[$i].'/';

            }

        }

        $file = $file[count($file) - 1].'.php';

        if (file_exists($path.$file)) require_once $path.$file;

    }

});

define('SUBSBU_CONFIG', [
    'db' => [
        'prefix' => 'subsbu_',
        'tables' => [
            'audience' => 'audience',
            'settings' => 'settings'
        ]
    ],
    'assets' => [
        'js' => 'assets/js/',
        'css' => 'assets/css/',
        'icons' => 'assets/icons/'
    ],
    'views' => 'views/'
]);
