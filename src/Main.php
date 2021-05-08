<?php
/**
 * @package Subsbu
 * @author Dmitry Shumilin (chirontex@yandex.ru)
 */
namespace Subsbu;

use Magnate\EntryPoint;
use Magnate\Injectors\EntryPointInjector;
use Magnate\Injectors\AdminPageInjector;
use Magnate\Exceptions\ActiveRecordCollectionException;
use Subsbu\Tables\AudienceTable;
use Subsbu\Tables\SettingsTable;
use Subsbu\Models\Audience;
use Subsbu\Models\Setting;
use WP_REST_Request;

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

        new SettingsPage(
            new EntryPointInjector($this->path, $this->url),
            (new AdminPageInjector(
                'subsbu-settings',
                $this->path.SUBSBU_CONFIG['views'].'settings.php',
                'Subsbu: настройки',
                'Subsbu',
                8
            ))->addStyle(
                'bootstrap',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css',
                [],
                '5.0.0'
            )->addStyle(
                'subsbu-settings',
                $this->url.SUBSBU_CONFIG['assets']['css'].'settings.css',
                [],
                '0.1.1'
            )
        );

        $this
            ->restApiInit()
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

                $post = get_post((int)$atts['event']);

                if (empty($post)) return;

                if ($post->post_type !== 'ajde_events') return;

                $start = (int)$post->evcal_srow;

                ini_set('date.timezone', '');

                if (time() > $start) return;

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
<button type="button" id="<?= htmlspecialchars($atts['id']) ?>-user-authorized" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>" onclick="SubsbuClient.subscribe('<?= htmlspecialchars($atts['id']) ?>-user-authorized', <?= $post->ID ?>, <?= $user_id ?>, '<?= $content[1] ?>', '<?= md5('subsbu-subscribe-'.$atts['event'].'-'.$user_id) ?>');"><?= $content[0] ?></button>
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
                '0.1.2',
                true
            );

        });

        return $this;

    }

    /**
     * Initialize REST API routes.
     * @since 0.1.0
     * 
     * @return $this
     */
    protected function restApiInit() : self
    {

        add_action('rest_api_init', function() {

            register_rest_route(
                'subsbu/v1',
                '/subscribe',
                [
                    'methods' => 'POST',
                    'callback' => function(WP_REST_Request $request) {

                        $event = $request->get_param('subsbu-client-event');
                        $user_id = $request->get_param('subsbu-client-user');

                        if (!empty($event) &&
                            !empty($user_id)) {

                            $event = (int)$event;
                            $user_id = (int)$user_id;

                            $post = get_post($event);

                            if (empty($post)) return [
                                'code' => -97,
                                'message' => 'Event not found.'
                            ];

                            if ($post->post_type !==
                                'ajde_events') return [
                                    'code' => -98,
                                    'message' => 'Invalid post type.'
                                ];

                            try {

                                $audience = Audience::where(
                                    [
                                        [
                                            'post_id' => [
                                                'condition' => '= %d',
                                                'value' => $event
                                            ]
                                        ]
                                    ]
                                )->first();

                                $subscribers = explode(';', $audience->subscribers);

                                if (array_search($user_id, $subscribers) ===
                                    false) {

                                    $subscribers[] = $user_id;

                                    $subscribers = implode(';', $subscribers);

                                    $audience->subscribers = $subscribers;
                                    $audience->save();

                                }

                            } catch (ActiveRecordCollectionException $e) {

                                if ($e->getCode() === -9) {

                                    $audience = new Audience;

                                    $audience->post_id = $event;
                                    $audience->subscribers = (string)$user_id;
                                    $audience->save();

                                } else return [
                                    'code' => $e->getCode(),
                                    'message' => $e->getMessage()
                                ];

                            }

                            return [
                                'code' => 0,
                                'message' => 'Success.'
                            ];

                        } else return [
                            'code' => -99,
                            'message' => 'Too few arguments for this request.'
                        ];

                    },
                    'permission_callback' => function(WP_REST_Request $request) {

                        require_once ABSPATH.WPINC.'/pluggable.php';

                        return $request->get_param('subsbu-client-key') ===
                            md5('subsbu-subscribe-'.$request->get_param('subsbu-client-event').
                                '-'.$request->get_param('subsbu-client-user'));

                    }
                ]
            );

        });

        return $this;

    }

    /**
     * Initialize cron mailing tasks.
     * @since 0.1.7
     * 
     * @return $this
     */
    protected function cronInit() : self
    {

        ini_set('date.timezone', '');

        $events = $this->wpdb->get_results(
            "SELECT t1.post_id,
                    t.post_title,
                    t1.meta_value AS start_time,
                    t2.meta_value AS link
                FROM `".$this->wpdb->prefix."posts` AS t
                LEFT JOIN `".$this->wpdb->prefix."postmeta` AS t1
                ON t.ID = t1.post_id
                LEFT JOIN `".$this->wpdb->prefix."postmeta` AS t2
                ON t.ID = t2.post_id
                WHERE t.post_type = 'ajde_events'
                AND t1.meta_key = 'evcal_srow'
                AND t1.meta_value > ".time()."
                AND t2.meta_key = 'evcal_exlink'",
            ARRAY_A
        );

        if (!empty($events)) {

            $mail_time = Setting::where(
                [
                    [
                        'key' => [
                            'condition' => '= %s',
                            'value' => 'mail_time'
                        ]
                    ]
                ]
            )->first();

            foreach ($events as $event) {

                if (!wp_next_scheduled(
                    'subsbu-mailing-event-'.$event['post_id']
                )) {

                    //

                }

            }

        }

        return $this;

    }

}
