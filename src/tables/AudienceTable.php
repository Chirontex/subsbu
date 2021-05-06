<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu\Tables;

use Magnate\Tables\Migration;

/**
 * Audience table migration class.
 * @since 0.0.3
 */
class AudienceTable extends Migration
{

    /**
     * @since 0.0.3
     */
    protected function up() : self
    {

        $this
            ->table(SUBSBU_CONFIG['db']['prefix'].
                SUBSBU_CONFIG['db']['tables']['audience'])
            ->field('post_id', 'BIGINT(20) UNSIGNED NOT NULL')
            ->field('subscribers', 'LONGTEXT');
        
        return $this;

    }

}
