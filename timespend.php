<?php
/*
Plugin Name: Time Spent Tracker
Plugin URI: https://github.com/A-Krivoshen/Time-Spent-Tracker
Description: Tracks the total time spent by users on the site, with shortcode output and color customization. Use the [time_spent] shortcode to display the tracker on your posts or pages.
Version: 1.0
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

// Enqueue JS for time tracking and output via shortcode
function time_spent_tracker_enqueue_script() {
    // Register an empty script for inline JavaScript
    wp_register_script('time-spent-tracker-js', '', [], false, true);

    // Enqueue the registered script
    wp_enqueue_script('time-spent-tracker-js');

    // Inline JavaScript code for tracking user time on site
    $text_color = esc_attr(get_option('time_spent_text_color', '#4758D0'));
    $background_color = esc_attr(get_option('time_spent_background_color', 'white'));
    $border_color = esc_attr(get_option('time_spent_border_color', '#4758D0'));
    $language = esc_js(get_option('time_spent_language', 'en'));

    $inline_script = <<<EOT
    document.addEventListener('DOMContentLoaded', function() {
        let startTime = Date.now();
        let totalTimeSpent = 0;

        const translations = {
            en: {
                initialMessage: 'You have spent on the site: ',
                days: ' days ',
                hours: ' hours ',
                minutes: ' minutes ',
                seconds: ' seconds ',
            },
            ru: {
                initialMessage: 'Вы провели на сайте: ',
                days: ' дн ',
                hours: ' ч ',
                minutes: ' мин ',
                seconds: ' сек',
            },
        };

        const storedLanguage = localStorage.getItem('timeSpentLanguage') || '{$language}';

        // Check if there is a previously saved time in localStorage
        if (localStorage.getItem('totalTimeSpent')) {
            totalTimeSpent = parseInt(localStorage.getItem('totalTimeSpent'), 10);
        }

        function formatTime(seconds) {
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            let result = '';
            if (days > 0) result += days + translations[storedLanguage].days;
            if (hours > 0 || days > 0) result += hours + translations[storedLanguage].hours;
            result += minutes + translations[storedLanguage].minutes + secs + translations[storedLanguage].seconds;

            return result;
        }

        function updateTimeSpent() {
            const currentTime = Math.floor((Date.now() - startTime) / 1000);
            const totalTime = totalTimeSpent + currentTime;

            window.addEventListener('beforeunload', function() {
                localStorage.setItem('totalTimeSpent', totalTime);
            });

            const timeSpentElement = document.getElementById('timeSpent');
            if (timeSpentElement) {
                timeSpentElement.textContent = translations[storedLanguage].initialMessage + formatTime(totalTime);
                timeSpentElement.style.color = '{$text_color}';
                timeSpentElement.style.backgroundColor = '{$background_color}';
                timeSpentElement.style.border = '2px solid {$border_color}';
                timeSpentElement.style.padding = '10px';
                timeSpentElement.style.borderRadius = '4px';
                timeSpentElement.style.boxShadow = '2px 2px 5px rgba(0, 0, 0, 0.5)';
                timeSpentElement.style.display = 'inline-block';
                timeSpentElement.style.marginTop = '10px';
            }
        }

        setInterval(updateTimeSpent, 1000);
    });
EOT;

    // Add inline script directly
    wp_add_inline_script('time-spent-tracker-js', $inline_script);
}
add_action('wp_enqueue_scripts', 'time_spent_tracker_enqueue_script');

// Shortcode to display time spent
function time_spent_tracker_display_time() {
    return '<p id="timeSpent">' . esc_html__('You have spent on the site: 0 min 0 sec', 'time-spent-tracker') . '</p>';
}
add_shortcode('time_spent', 'time_spent_tracker_display_time');

// Register settings
function time_spent_tracker_register_settings() {
    add_option('time_spent_text_color', '#4758D0');
    add_option('time_spent_background_color', 'white');
    add_option('time_spent_border_color', '#4758D0');
    add_option('time_spent_language', 'en');
    register_setting('time_spent_tracker_options_group', 'time_spent_text_color');
    register_setting('time_spent_tracker_options_group', 'time_spent_background_color');
    register_setting('time_spent_tracker_options_group', 'time_spent_border_color');
    register_setting('time_spent_tracker_options_group', 'time_spent_language');
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
                    <td><input type="text" id="time_spent_text_color" name="time_spent_text_color" value="<?php echo esc_attr(get_option('time_spent_text_color')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_background_color"><?php esc_html_e('Background Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_background_color" name="time_spent_background_color" value="<?php echo esc_attr(get_option('time_spent_background_color')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_border_color"><?php esc_html_e('Border Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_border_color" name="time_spent_border_color" value="<?php echo esc_attr(get_option('time_spent_border_color')); ?>" /></td>
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
