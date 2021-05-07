<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu\Models;

use Magnate\Tables\ActiveRecord;

/**
 * Posts model class.
 * @since 0.0.9
 */
class Post extends ActiveRecord
{

    /**
     * @since 0.0.9
     */
    public static function tableName(): string
    {
        
        return 'posts';

    }

}
