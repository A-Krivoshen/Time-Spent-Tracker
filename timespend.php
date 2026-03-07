<?php
/*
Plugin Name: Time Spent Tracker
Plugin URI: https://github.com/A-Krivoshen/Time-Spent-Tracker
Description: Tracks the total time spent by users on the site, with shortcode output and color customization. Use the [time_spent] shortcode to display the tracker on your posts or pages.
Version: 1.1
Author: Aleksey Krivoshein
Author URI: https://krivoshein.site
Text Domain: time-spent-tracker
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Load text domain for translations
function time_spent_tracker_load_textdomain() {
    load_plugin_textdomain('time-spent-tracker', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'time_spent_tracker_load_textdomain');

/**
 * Return default plugin settings.
 *
 * @return array<string, string>
 */
function time_spent_tracker_get_default_settings() {
    return [
        'text_color' => '#4758D0',
        'background_color' => 'white',
        'border_color' => '#4758D0',
        'language' => 'en',
    ];
}

/**
 * Validate a color value.
 *
 * @param string $color Raw color value.
 * @return string
 */
function time_spent_tracker_sanitize_color($color) {
    $color = trim((string) $color);
    $hex_color = sanitize_hex_color($color);

    if (!empty($hex_color)) {
        return $hex_color;
    }

    if (preg_match('/^[a-zA-Z]+$/', $color)) {
        return sanitize_text_field($color);
    }

    return time_spent_tracker_get_default_settings()['background_color'];
}

/**
 * Sanitize selected language.
 *
 * @param string $language Raw language code.
 * @return string
 */
function time_spent_tracker_sanitize_language($language) {
    $allowed_languages = ['en', 'ru'];
    $language = sanitize_text_field((string) $language);

    return in_array($language, $allowed_languages, true) ? $language : time_spent_tracker_get_default_settings()['language'];
}

/**
 * Determine whether plugin assets are needed on the current request.
 *
 * @return bool
 */
function time_spent_tracker_should_enqueue_assets() {
    if (is_admin()) {
        return false;
    }

    if (!is_singular()) {
        return false;
    }

    $post = get_post();
    if (!$post instanceof WP_Post) {
        return false;
    }

    return has_shortcode($post->post_content, 'time_spent');
}

/**
 * Return translated labels based on selected plugin language.
 *
 * @return array<string, string>
 */
function time_spent_tracker_get_translations() {
    $selected_language = get_option('time_spent_language', time_spent_tracker_get_default_settings()['language']);

    $labels = [
        'initialMessage' => [
            'en' => __('You have spent on the site: ', 'time-spent-tracker'),
            'ru' => __('Вы провели на сайте: ', 'time-spent-tracker'),
        ],
        'days' => [
            'en' => __(' days ', 'time-spent-tracker'),
            'ru' => __(' дн ', 'time-spent-tracker'),
        ],
        'hours' => [
            'en' => __(' hours ', 'time-spent-tracker'),
            'ru' => __(' ч ', 'time-spent-tracker'),
        ],
        'minutes' => [
            'en' => __(' minutes ', 'time-spent-tracker'),
            'ru' => __(' мин ', 'time-spent-tracker'),
        ],
        'seconds' => [
            'en' => __(' seconds ', 'time-spent-tracker'),
            'ru' => __(' сек', 'time-spent-tracker'),
        ],
    ];

    $result = [];
    foreach ($labels as $key => $values) {
        $result[$key] = $values[$selected_language] ?? $values['en'];
    }

    return $result;
}

// Enqueue scripts and styles
function time_spent_tracker_enqueue_assets() {
    if (!time_spent_tracker_should_enqueue_assets()) {
        return;
    }

    $defaults = time_spent_tracker_get_default_settings();

    wp_enqueue_script('time-spent-tracker-js', plugins_url('js/time-spent-tracker.js', __FILE__), [], '1.1', true);
    wp_enqueue_style('time-spent-tracker-css', plugins_url('css/time-spent-tracker.css', __FILE__), [], '1.1');

    wp_localize_script('time-spent-tracker-js', 'TimeSpentTracker', [
        'translations' => time_spent_tracker_get_translations(),
        'settings' => [
            'textColor' => esc_attr(get_option('time_spent_text_color', $defaults['text_color'])),
            'backgroundColor' => esc_attr(get_option('time_spent_background_color', $defaults['background_color'])),
            'borderColor' => esc_attr(get_option('time_spent_border_color', $defaults['border_color'])),
        ],
    ]);
}
add_action('wp_enqueue_scripts', 'time_spent_tracker_enqueue_assets');

// Shortcode to display time spent
function time_spent_tracker_display_time() {
    return '<p id="timeSpent">' . esc_html__('You have spent on the site: 0 min 0 sec', 'time-spent-tracker') . '</p>';
}
add_shortcode('time_spent', 'time_spent_tracker_display_time');

// Register settings
function time_spent_tracker_register_settings() {
    $defaults = time_spent_tracker_get_default_settings();
    add_option('time_spent_text_color', $defaults['text_color']);
    add_option('time_spent_background_color', $defaults['background_color']);
    add_option('time_spent_border_color', $defaults['border_color']);
    add_option('time_spent_language', $defaults['language']);

    register_setting('time_spent_tracker_options_group', 'time_spent_text_color', ['sanitize_callback' => 'time_spent_tracker_sanitize_color']);
    register_setting('time_spent_tracker_options_group', 'time_spent_background_color', ['sanitize_callback' => 'time_spent_tracker_sanitize_color']);
    register_setting('time_spent_tracker_options_group', 'time_spent_border_color', ['sanitize_callback' => 'time_spent_tracker_sanitize_color']);
    register_setting('time_spent_tracker_options_group', 'time_spent_language', ['sanitize_callback' => 'time_spent_tracker_sanitize_language']);
}
add_action('admin_init', 'time_spent_tracker_register_settings');

// Create settings page
function time_spent_tracker_register_options_page() {
    add_options_page('Time Spent Tracker', 'Time Spent Tracker', 'manage_options', 'time-spent-tracker', 'time_spent_tracker_options_page');
}
add_action('admin_menu', 'time_spent_tracker_register_options_page');

function time_spent_tracker_options_page() {
    ?>
    <div>
        <h2><?php esc_html_e('Time Spent Tracker Settings', 'time-spent-tracker'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('time_spent_tracker_options_group'); ?>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_text_color"><?php esc_html_e('Text Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_text_color" name="time_spent_text_color" value="<?php echo esc_attr(get_option('time_spent_text_color')); ?>" class="color-field" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_background_color"><?php esc_html_e('Background Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_background_color" name="time_spent_background_color" value="<?php echo esc_attr(get_option('time_spent_background_color')); ?>" class="color-field" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_border_color"><?php esc_html_e('Border Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_border_color" name="time_spent_border_color" value="<?php echo esc_attr(get_option('time_spent_border_color')); ?>" class="color-field" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_language"><?php esc_html_e('Language', 'time-spent-tracker'); ?></label></th>
                    <td>
                        <select id="time_spent_language" name="time_spent_language">
                            <option value="en" <?php selected(get_option('time_spent_language'), 'en'); ?>>English</option>
                            <option value="ru" <?php selected(get_option('time_spent_language'), 'ru'); ?>>Русский</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
