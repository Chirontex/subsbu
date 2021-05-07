<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu;

use Magnate\EntryPoint;
use Magnate\Exceptions\ActiveRecordException;
use Subsbu\Tables\AudienceTable;
use Subsbu\Tables\SettingsTable;
use Subsbu\Models\Audience;
use Subsbu\Models\Post;

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
            
            if (empty($content)) $content = 'Зарегистрироваться|||Вы зарегистрированы!';

            $content = explode('|||', $content);
            
            $user_id = get_current_user_id();

            if (empty($user_id)) {

                ob_start();

?>
<button type="button" id="<?= htmlspecialchars($atts['id']) ?>" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>"><?= $content[0] ?></button>
<?php

                return ob_get_clean();

            } else {
            
                try {

                    $post = Post::find((int)$atts['event']);

                } catch (ActiveRecordException $e) {

                    return;

                }

                if ($post->post_type !== 'ajde_events') return;

                $postmeta = $this->wpdb->get_results(
                    "SELECT *
                        FROM `".$this->wpdb->prefix."postmeta` AS t
                        WHERE t.post_id = '".$post->ID."'
                        AND t.meta_key = 'evcal_srow'",
                    ARRAY_A
                );

                if (empty($postmeta)) return;

                ini_set('date.timezone', '');

                if (time() > $postmeta[0]['meta_value']) return;

                $audience = Audience::where(
                    [
                        [
                            'post_id' => [
                                'condition' => '= %d',
                                'value' => $post->ID
                            ]
                        ]
                    ]
                )->all();

                if (!empty($audience)) {

                    $audience = $audience[0];

                    $subscribers = explode(';', $audience->subscribers);

                    if (array_search((string)$user_id, $subscribers) !==
                        false) return;

                }

                ob_start();

?>
<button type="button" id="<?= htmlspecialchars($atts['id']) ?>-user-authorized" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>" onclick="SubsbuClient.subscribe('<?= htmlspecialchars($atts['id']) ?>-user-authorized', <?= $post->ID ?>, <?= $user_id ?>, '<?= $content[1] ?>');"><?= $content[0] ?></button>
<?php

                return ob_get_clean();

            }

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
                '0.0.2',
                true
            );

        });

        return $this;

    }

}
