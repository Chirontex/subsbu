<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu;

use Magnate\EntryPoint;
use Subsbu\Tables\AudienceTable;
use Subsbu\Tables\SettingsTable;
use Subsbu\Models\Audience;

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
        new SettingsTable;

        $this
            ->scriptAdd()
            ->buttonShortcodeInit();
        
        return $this;

    }

    /**
     * Initialize button shortcode.
     * @since 0.0.5
     * 
     * @return $this
     */
    protected function buttonShortcodeInit() : self
    {

        add_shortcode('subsbu', function($atts, $content) {

            $atts = shortcode_atts([
                'id' => '',
                'event' => '',
                'class' => '',
                'style' => ''
            ], $atts);

            $user_id = get_current_user_id();

            if (empty($content)) $content = 'Зарегистрироваться|||Вы уже зарегистрированы';

            $content = explode('|||', $content);

            ob_start();

            if (empty($user_id)) {

?>
<button type="button" id="<?= htmlspecialchars($atts['id']) ?>" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>"><?= $content[0] ?></button>
<?php

            } else {

                $posts = $this->wpdb->get_results(
                    $this->wpdb->prepare(
                        "SELECT *
                            FROM `".$this->wpdb->prefix."posts` AS t
                            WHERE t.post_type = 'ajde_events'
                            AND t.ID = %d",
                        (int)$atts['event']
                    ),
                    ARRAY_A
                );

                if (empty($posts)) return ob_get_clean();

                $audience = Audience::where(
                    [
                        [
                            'post_id' => [
                                'condition' => '= %d',
                                'value' => (int)$atts['event']
                            ]
                        ]
                    ]
                )->all();

                $subscribed = false;

                if (!empty($audience)) {

                    $audience = $audience[0];

                    $subscribers = explode(';', $audience->subscribers);

                    if (array_search($user_id, $subscribers) !==
                        false) $subscribed = true;

                }

                if ($subscribed) {

?>
<button type="button" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>" disabled="true"><?= $content[1] ?></button>
<?php

                } else {

?>
<form action="" method="post" id="subsbuForm">
<?php wp_nonce_field('subsbu-subscribe', 'subsbu-subscribe-wpnp') ?>
</form>
<button type="button" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>" onclick="SubsbuClient.subscribe(<?= $user_id ?>);"><?= $content[0] ?></button>
<?php

                }

            }

            return ob_get_clean();

        });

        return $this;

    }

    /**
     * Add client script.
     * @since 0.0.7
     * 
     * @return $this
     */
    protected function scriptAdd() : self
    {

        add_action('wp_enqueue_scripts', function() {

            wp_enqueue_script(
                'subsbu-client',
                $this->url.SUBSBU_CONFIG['assets']['js'].'subsbu-client.js',
                [],
                '0.0.1',
                true
            );

        });

        return $this;

    }

}
