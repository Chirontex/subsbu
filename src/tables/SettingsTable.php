<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu\Tables;

use Magnate\Tables\Migration;

/**
 * Settings table.
 * @since 0.0.4
 */
class SettingsTable extends Migration
{

    /**
     * @since 0.0.4
     */
    protected function up() : self
    {

        $this
            ->table(SUBSBU_CONFIG['db']['prefix'].
                SUBSBU_CONFIG['db']['tables']['settings'])
            ->field('key', 'VARCHAR(255) NOT NULL')
            ->field('value', 'LONGTEXT');

        return $this;
        
    }

}
