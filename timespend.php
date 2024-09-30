<?php
/*
Plugin Name: Time Spent Tracker
Plugin URI: https://yourwebsite.com
Description: Отслеживание общего времени, проведенного на сайте пользователем, с возможностью вывода через шорткод и настройки цвета.
Version: 1.0
Author: Aleksey Krivoshein
Author URI: https://krivoshein.site
*/

// Подключаем JS для отслеживания времени и выводим через шорткод
function time_spent_tracker_enqueue_script() {
    $text_color = get_option('time_spent_text_color', '#4758D0');
    $background_color = get_option('time_spent_background_color', 'white');
    $border_color = get_option('time_spent_border_color', '#4758D0');

    ?>
    <!-- HTML и JS код для отслеживания времени на сайте -->
    <script>
        let startTime;
        let totalTimeSpent = 0;

        // Проверяем, есть ли ранее сохраненное время в localStorage
        if (localStorage.getItem('totalTimeSpent')) {
            totalTimeSpent = parseInt(localStorage.getItem('totalTimeSpent'), 10);
        }

        // Функция для форматирования времени (дни, часы, минуты, секунды)
        function formatTime(seconds) {
            const days = Math.floor(seconds / 86400);
            const hours = Math.floor((seconds % 86400) / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;

            let result = "";
            if (days > 0) result += `${days} дн `;
            if (hours > 0 || days > 0) result += `${hours} ч `;
            result += `${minutes} мин ${secs} сек`;

            return result;
        }

        // Функция обновления времени
        function updateTimeSpent() {
            const currentTime = Math.floor((Date.now() - startTime) / 1000); // Время с момента открытия страницы
            const totalTime = totalTimeSpent + currentTime; // Общее время

            // Обновляем localStorage при уходе пользователя с сайта
            window.addEventListener('beforeunload', function() {
                localStorage.setItem('totalTimeSpent', totalTime);
            });

            // Обновляем текст с временем на сайте
            const timeSpentElement = document.getElementById('timeSpent');
            if (timeSpentElement) {
                timeSpentElement.textContent = `Вы провели на сайте: ${formatTime(totalTime)}`;
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
            startTime = Date.now(); // Запоминаем момент загрузки страницы
            setInterval(updateTimeSpent, 1000); // Обновляем каждую секунду
        });
    </script>
    <?php
}
add_action('wp_footer', 'time_spent_tracker_enqueue_script');

// Функция для вывода времени через шорткод
function time_spent_tracker_display_time() {
    return '<p id="timeSpent">Вы провели на сайте: 0 мин 0 сек</p>';
}
add_shortcode('time_spent', 'time_spent_tracker_display_time');

// Создание страницы настроек
function time_spent_tracker_register_settings() {
    add_option('time_spent_text_color', '#4758D0');
    add_option('time_spent_background_color', 'white');
    add_option('time_spent_border_color', '#4758D0');
    register_setting('time_spent_tracker_options_group', 'time_spent_text_color');
    register_setting('time_spent_tracker_options_group', 'time_spent_background_color');
    register_setting('time_spent_tracker_options_group', 'time_spent_border_color');
}
add_action('admin_init', 'time_spent_tracker_register_settings');

function time_spent_tracker_register_options_page() {
    add_options_page('Time Spent Tracker', 'Time Spent Tracker', 'manage_options', 'time-spent-tracker', 'time_spent_tracker_options_page');
}
add_action('admin_menu', 'time_spent_tracker_register_options_page');

function time_spent_tracker_options_page() {
    ?>
    <div>
        <h2>Time Spent Tracker Настройки</h2>
        <form method="post" action="options.php">
            <?php settings_fields('time_spent_tracker_options_group'); ?>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_text_color">Цвет текста</label></th>
                    <td><input type="text" id="time_spent_text_color" name="time_spent_text_color" value="<?php echo get_option('time_spent_text_color'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_background_color">Цвет фона</label></th>
                    <td><input type="text" id="time_spent_background_color" name="time_spent_background_color" value="<?php echo get_option('time_spent_background_color'); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="time_spent_border_color">Цвет границы</label></th>
                    <td><input type="text" id="time_spent_border_color" name="time_spent_border_color" value="<?php echo get_option('time_spent_border_color'); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
