<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu;

use Magnate\AdminPage;
use Magnate\Exceptions\ActiveRecordCollectionException;
use Subsbu\Models\Setting;

/**
 * @final
 * Settings page entry point.
 * @since 0.1.2
 */
final class SettingsPage extends AdminPage
{

    /**
     * @var string $fail_nonce_notice
     * Typical nonce checking failure notice text.
     * @since 0.1.4
     */
    protected $fail_nonce_notice = 'Произошла ошибка отправки формы. Попробуйте ещё раз.';

    /**
     * @since 0.1.4
     */
    protected function init(): self
    {
        
        return $this;

    }

}
