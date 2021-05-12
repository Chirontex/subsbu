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
 * Settings page entry point.
 * @since 0.1.2
 */
class SettingsPage extends AdminPage
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

        if (isset($_POST['subsbuSettingsNonce'])) $this->settingsFormSave();

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

        add_filter('subsbu-mail-sender-name', function() {

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => 'sender_name'
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

        add_filter('subsbu-mail-sender-email', function() {

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => 'sender_email'
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

        add_filter('subsbu-notice-subject', function() {

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => 'notice_subject'
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

        add_filter('subsbu-notice-text', function() {

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => 'notice_text'
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

    /**
     * Saving settings data.
     * @since 0.1.6
     * 
     * @return $this
     */
    protected function settingsFormSave() : self
    {

        require_once ABSPATH.WPINC.'/pluggable.php';

        if (wp_verify_nonce(
            $_POST['subsbuSettingsNonce'],
            'subsbuSettingsNonce-wpnp'
        ) === false) {
            
            $this->notice(
                'error',
                $this->fail_nonce_notice
            );

            return $this;
    
        }

        foreach ([
                'subsbuMailTime' => 'mail_time',
                'subsbuMailSubject' => 'mail_subject',
                'subsbuMailText' => 'mail_text',
                'subsbuMailSenderName' => 'sender_name',
                'subsbuMailSenderEmail' => 'sender_email',
                'subsbuNoticeSubject' => 'notice_subject',
                'subsbuNoticeText' => 'notice_text'
            ] as $name => $key) {

            if (!isset($_POST[$name])) {

                $this->notice(
                    'error',
                    'Некоторые необходимые поля не были заполнены.'
                );

                return $this;

            }

            try {

                $setting = Setting::where(
                    [
                        [
                            'key' => [
                                'condition' => '= %s',
                                'value' => $key
                            ]
                        ]
                    ]
                )->first();

            } catch (ActiveRecordCollectionException $e) {

                if ($e->getCode() === -9) {

                    $setting = new Setting;
                    $setting->key = $key;

                } else throw $e;

            }

            $setting->value = $_POST[$name];
            $setting->save();

        }

        $this->notice(
            'success',
            'Настройки сохранены!'
        );

        return $this;

    }

}
