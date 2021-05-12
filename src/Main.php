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
 * Main EP class.
 * @since 0.0.2
 */
class Main extends EntryPoint
{

    /**
     * @var array $settings
     * All settings.
     * @since 0.1.8
     */
    protected $settings = [];

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
                'Настройки e-mail-уведомлений о мероприятиях',
                'Настройки e-mail-уведомлений',
                8,
                '',
                $this->url.SUBSBU_CONFIG['assets']['icons'].'mail.svg'
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
            ->getSettings()
            ->restApiInit()
            ->scriptAdd()
            ->buttonShortcodeInit()
            ->cronInit();
        
        return $this;

    }

    /**
     * Get settings to object property.
     * @since 0.1.8
     * 
     * @return $this
     */
    protected function getSettings() : self
    {

        $settings = Setting::where([])->all();

        foreach ($settings as $setting) {

            $this->settings[$setting->key] = $setting->value;

        }

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
                'style' => '',
                'donor' => '',
                'recipient' => '',
                'onclick' => ''
            ], $atts);
            
            if (empty($content)) $content = 'Зарегистрироваться|||Вы зарегистрированы!';

            $content = explode('|||', $content);
            
            $user_id = get_current_user_id();

            if (empty($user_id)) {

                $onclick = empty($atts['onclick']) ?
                    "SubsbuClient.flip('".htmlspecialchars($atts['donor'])."', '".htmlspecialchars($atts['recipient'])."');" :
                    htmlspecialchars($atts['onclick']);

                ob_start();

?>
<button type="button" id="<?= htmlspecialchars($atts['id']) ?>" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>" onclick="<?= $onclick ?>"><?= $content[0] ?></button>
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
                        false) {

                        ob_start();

?>
<button type="button" class="<?= htmlspecialchars($atts['class']) ?>" style="<?= htmlspecialchars($atts['style']) ?>" disabled="true"><?= $content[1] ?></button>
<?php

                        return ob_get_clean();

                    }

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
                '0.1.8',
                true
            );

            wp_enqueue_style(
                'subsbu-client',
                $this->url.SUBSBU_CONFIG['assets']['css'].'subsbu-client.css',
                [],
                '0.0.4'
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
                                    $audience->mailed = 'false';
                                    $audience->save();

                                } else return [
                                    'code' => $e->getCode(),
                                    'message' => $e->getMessage()
                                ];

                            }

                            $event_name = $post->post_title;
                            $event_url = $post->evcal_exlink;

                            $subject = $this->replaceMailPlaceholders(
                                $this->settings['notice_subject'],
                                $event_name,
                                $event_url
                            );

                            $text = $this->replaceMailPlaceholders(
                                $this->settings['notice_text'],
                                $event_name,
                                $event_url
                            );

                            $sender_name = $this->replaceMailPlaceholders(
                                $this->settings['sender_name'],
                                $event_name,
                                $event_url
                            );

                            $user = get_userdata($user_id);

                            wp_mail(
                                $user->user_email,
                                $subject,
                                $text,
                                [
                                    'Content-type: text/html; charset=utf-8',
                                    'From: '.$sender_name.
                                        ' <'.$this->settings['sender_email'].'>'
                                ]
                            );

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

        date_default_timezone_set('UTC');

        $events = $this->wpdb->get_results(
            "SELECT t1.post_id,
                    t.post_title,
                    t1.meta_value AS start_time,
                    t2.meta_value AS link,
                    t3.meta_value AS timezone
                FROM `".$this->wpdb->prefix."posts` AS t
                LEFT JOIN `".$this->wpdb->prefix."postmeta` AS t1
                ON t.ID = t1.post_id
                LEFT JOIN `".$this->wpdb->prefix."postmeta` AS t2
                ON t.ID = t2.post_id
                LEFT JOIN `".$this->wpdb->prefix."postmeta` AS t3
                ON t.ID = t3.post_id
                WHERE t.post_type = 'ajde_events'
                AND t1.meta_key = 'evcal_srow'
                AND t1.meta_value > ".((time() + $this->settings['mail_time'] * 60) - 1)."
                AND t2.meta_key = 'evcal_exlink'
                AND t3.meta_key = '_evo_tz'",
            ARRAY_A
        );

        foreach ($events as $event) {

            $timezone = empty($event['timezone']) ?
                'Europe/Moscow' : $event['timezone'];

            if (wp_next_scheduled(
                'subsbu-mailing-event-'.$event['post_id']
            ) === false) {

                date_default_timezone_set('UTC');

                $event_time = date("Y-m-d H:i:s", (int)$event['start_time']);

                date_default_timezone_set($timezone);

                $event_time = strtotime($event_time);

                wp_schedule_single_event(
                    $event_time -
                        ((int)$this->settings['mail_time'] * 60),
                    'subsbu-mailing-event-'.$event['post_id'],
                    [
                        (int)$event['post_id'],
                        $event['post_title'],
                        $event['link']
                    ]
                );

            }

            add_action(
                'subsbu-mailing-event-'.$event['post_id'],
                function($event_id, $event_name, $event_url) {

                    try {

                        $audience = Audience::where(
                            [
                                [
                                    'post_id' => [
                                        'condition' => '= %d',
                                        'value' => (int)$event_id
                                    ],
                                    'mailed' => [
                                        'condition' => '!= %s',
                                        'value' => 'true'
                                    ]
                                ]
                            ]
                        )->first();

                    } catch (ActiveRecordCollectionException $e) {

                        if ($e->getCode() === -9) return;
                        else throw $e;

                    }

                    $subscribers = explode(';', $audience->subscribers);

                    if (empty($subscribers)) return;

                    $where = '';

                    foreach ($subscribers as $user_id) {

                        $where .= empty($where) ? " WHERE" : " OR";
                        $where .= " t.ID = ".$user_id;

                    }

                    $emails = $this->wpdb->get_results(
                        "SELECT t.user_email
                            FROM `".$this->wpdb->prefix."users` AS t".$where,
                        ARRAY_A
                    );

                    if (empty($emails)) return;

                    $subject = $this->replaceMailPlaceholders(
                        $this->settings['mail_subject'],
                        $event_name,
                        $event_url
                    );

                    $text = $this->replaceMailPlaceholders(
                        $this->settings['mail_text'],
                        $event_name,
                        $event_url
                    );

                    $sender_name = $this->replaceMailPlaceholders(
                        $this->settings['sender_name'],
                        $event_name,
                        $event_url
                    );

                    foreach ($emails as $row) {

                        wp_mail(
                            $row['user_email'],
                            $subject,
                            $text,
                            [
                                'Content-type: text/html; charset=utf-8',
                                'From: '.$sender_name.
                                    ' <'.$this->settings['sender_email'].'>'
                            ]
                        );

                    }
                    
                    $audience->refresh()->mailed = 'true';
                    $audience->save();

                }, 10, 3);

        }

        return $this;

    }

    /**
     * Return string with replaced placeholders.
     * @since 1.0.7
     * 
     * @param string $content
     * String with placeholders.
     * 
     * @param string $event_name
     * Event name.
     * 
     * @param string $event_url
     * Event URL.
     * 
     * @return string
     */
    protected function replaceMailPlaceholders(string $content, string $event_name, string $event_url) : string
    {

        $placeholders = [
            '!%site_url%!',
            '!%site_name%!',
            '!%mail_time%!',
            '!%event_name%!',
            '!%event_url%!'
        ];

        $ph_values = [
            site_url(),
            get_bloginfo('name'),
            $this->settings['mail_time'],
            $event_name,
            rawurldecode($event_url)
        ];

        return str_replace(
            $placeholders,
            $ph_values,
            $content
        );

    }

}
