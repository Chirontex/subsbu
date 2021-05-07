<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu\Models;

use Magnate\Tables\ActiveRecord;

/**
 * Settings model
 * @since 0.0.4
 */
class Setting extends ActiveRecord
{

    /**
     * @since 0.0.4
     */
    public static function tableName() : string
    {
        
        return SUBSBU_CONFIG['db']['prefix'].
            SUBSBU_CONFIG['db']['tables']['settings'];

    }

}
