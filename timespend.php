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
    load_plugin_textdomain('timespend', false, dirname(plugin_basename(__FILE__)) . '/languages/');
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

        const storedLanguage = localStorage.getItem('timeSpentLanguage') || '<?php echo esc_js($language); ?>';

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
                timeSpentElement.style.color = '<?php echo esc_attr($text_color); ?>';
                timeSpentElement.style.backgroundColor = '<?php echo esc_attr($background_color); ?>';
                timeSpentElement.style.border = '2px solid <?php echo esc_attr($border_color); ?>';
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
