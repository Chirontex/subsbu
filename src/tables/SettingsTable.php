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
            ->field('value', 'LONGTEXT')
            ->entry([
                'key' => 'mail_time',
                'value' => '15'
            ])
            ->entry([
                'key' => 'mail_subject',
                'value' => 'Осталось !%mail_time%! минут до начала !%event_name%!'
            ]);

            ob_start();

?>
<p>Вы зарегистрированы на !%event_name%! как участник. До начала осталось !%mail_time%! минут.</p>
<p>Принять участие можно по этой ссылке: !%event_url%!</p>
<p>С уважением, администрация !%site_name%!</p>
<?php

            $this
                ->entry([
                    'key' => 'mail_text',
                    'value' => ob_get_clean()
            ])
                ->entry([
                    'key' => 'sender_name',
                    'value' => '!%site_name%!'
                ])
                ->entry([
                    'key' => 'sender_email',
                    'value' => 'noreply@'.$_SERVER['HTTP_HOST']
                ])
                ->entry([
                    'key' => 'notice_subject',
                    'value' => 'Вы зарегистрированы на !%event_name%!'
                ]);

                ob_start();

?>
<p>Поздравляем! Вы зарегистрированы на !%event_name%!!</p>
<p>Принять участие можно будет по этой ссылке: !%event_url%!</p>
<p>С уважением, администрация !%site_name%!</p>
<?php

                $this
                    ->entry([
                        'key' => 'notice_text',
                        'value' => ob_get_clean()
                    ]);

        return $this;
        
    }

}
