<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu;

use Magnate\EntryPoint;
use Subsbu\Tables\AudienceTable;

/**
 * @final
 * Main EP class.
 * @since 0.0.2
 */
final class Main extends EntryPoint
{

    /**
     * @since 0.0.2
     */
    protected function init() : self
    {

        new AudienceTable;
        
        return $this;

    }

}