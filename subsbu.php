<?php
/**
 * Plugin Name: Subsbu
 * Plugin URI: https://github.com/chirontex/subsbu
 * Description: Плагин, позволяющий пользователям записываться на мероприятия, созданные в плагине myEventON.
 * Version: 0.0.7
 * Author: Dmitry Shumilin
 * Author URI: mailto://chirontex@yandex.ru
 * 
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 * @since 0.0.7
*/
use Magnate\Injectors\EntryPointInjector;
use Subsbu\Main;

require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/subsbu-autoload.php';

define('SUBSBU_CONFIG', [
    'db' => [
        'prefix' => 'subsbu_',
        'tables' => [
            'audience' => 'audience',
            'settings' => 'settings'
        ]
    ],
    'assets' => [
        'js' => 'assets/js/'
    ]
]);

new Main(
    new EntryPointInjector(
        plugin_dir_path(__FILE__),
        plugin_dir_url(__FILE__)
    )
);
