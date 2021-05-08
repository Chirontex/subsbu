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
    protected function init() : self
    {

        $this->mailFiltersInit();
        
        return $this;

    }

    /**
     * Initialize mail content filters.
     * @since 0.1.5
     * 
     * @return $this
     */
    protected function mailFiltersInit() : self
    {

        add_filter('subsbu-mail-time', function() {

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => 'mail_time'
                            ]
                        ]
                    ]
                )->first();

                return $setting->value;

            } catch (ActiveRecordCollectionException $e) {

                if ($e->getCode() === -9) return '';
                else throw $e;

            }

        });

        add_filter('subsbu-mail-subject', function() {

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => 'mail_subject'
                            ]
                        ]
                    ]
                )->first();

                return $setting->value;

            } catch (ActiveRecordCollectionException $e) {

                if ($e->getCode() === -9) return '';
                else throw $e;

            }

        });

        add_filter('subsbu-mail-text', function() {

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => 'mail_text'
                            ]
                        ]
                    ]
                )->first();

                return $setting->value;

            } catch (ActiveRecordCollectionException $e) {

                if ($e->getCode() === -9) return '';
                else throw $e;

            }

        });

        return $this;

    }

}
