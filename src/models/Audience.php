<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu\Models;

use Magnate\Tables\ActiveRecord;

/**
 * Audience model.
 * @since 0.0.3
 */
class Audience extends ActiveRecord
{

    /**
     * @since 0.0.3
     */
    public static function tableName(): string
    {
        
        return SUBSBU_CONFIG['db']['prefix'].
            SUBSBU_CONFIG['db']['tables']['audience'];

    }

}
