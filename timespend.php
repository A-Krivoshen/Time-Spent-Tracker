<?php
/*
Plugin Name: Time Spent Tracker
Plugin URI: https://github.com/A-Krivoshen/Time-Spent-Tracker
Description: Tracks the total time spent by users on the site, with shortcode output and color customization. Use the [time_spent] shortcode to display the tracker on your posts or pages.
Version: 1.0
Author: Aleksey Krivoshein
Author URI: https://krivoshein.site
Text Domain: time-spent-tracker
*/

// Load text domain for translations
function time_spent_tracker_load_textdomain() {
    load_plugin_textdomain('time-spent-tracker', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}
add_action('plugins_loaded', 'time_spent_tracker_load_textdomain');

// Enqueue JS for time tracking and output via shortcode
function time_spent_tracker_enqueue_script() {
    $text_color = esc_attr(get_option('time_spent_text_color', '#4758D0'));
    $background_color = esc_attr(get_option('time_spent_background_color', 'white'));
    $border_color = esc_attr(get_option('time_spent_border_color', '#4758D0'));
    $language = esc_js(get_option('time_spent_language', 'en'));

    ?>
    <script>
        let startTime;
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

        const storedLanguage = localStorage.getItem('timeSpentLanguage') || '<?php echo $language; ?>';

        if (localStorage.getItem('totalTimeSpent')) {
            totalTimeSpent = parseInt(localStorage.getItem('totalTimeSpent'), 10);
        }

        function formatTime(seconds) {
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            let result = "";
            if (days > 0) result += `${days}${translations[storedLanguage].days}`;
            if (hours > 0 || days > 0) result += `${hours}${translations[storedLanguage].hours}`;
            result += `${minutes}${translations[storedLanguage].minutes}${secs}${translations[storedLanguage].seconds}`;

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
                timeSpentElement.textContent = `${translations[storedLanguage].initialMessage}${formatTime(totalTime)}`;
                timeSpentElement.style.color = '<?php echo $text_color; ?>';
                timeSpentElement.style.backgroundColor = '<?php echo $background_color; ?>';
                timeSpentElement.style.border = '2px solid <?php echo $border_color; ?>';
                timeSpentElement.style.padding = '10px';
                timeSpentElement.style.borderRadius = '4px';
                timeSpentElement.style.boxShadow = '2px 2px 5px rgba(0, 0, 0, 0.5)';
                timeSpentElement.style.display = 'inline-block';
                timeSpentElement.style.marginTop = '10px';
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            startTime = Date.now();
            setInterval(updateTimeSpent, 1000);
        });
    </script>
    <?php
}
add_action('wp_footer', 'time_spent_tracker_enqueue_script');

// Shortcode to display time spent
function time_spent_tracker_display_time() {
    return '<p id="timeSpent">' . __('You have spent on the site: 0 min 0 sec', 'time-spent-tracker') . '</p>';
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
        <h2><?php _e('Time Spent Tracker Settings', 'time-spent-tracker'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('time_spent_tracker_options_group'); ?>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_text_color"><?php _e('Text Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_text_color" name="time_spent_text_color" value="<?php echo esc_attr(get_option('time_spent_text_color')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_background_color"><?php _e('Background Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_background_color" name="time_spent_background_color" value="<?php echo esc_attr(get_option('time_spent_background_color')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_border_color"><?php _e('Border Color', 'time-spent-tracker'); ?></label></th>
                    <td><input type="text" id="time_spent_border_color" name="time_spent_border_color" value="<?php echo esc_attr(get_option('time_spent_border_color')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_language"><?php _e('Language', 'time-spent-tracker'); ?></label></th>
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
