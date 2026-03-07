document.addEventListener('DOMContentLoaded', function() {
    const startTime = Date.now();
    let totalTimeSpent = 0;

    const translations = TimeSpentTracker.translations;
    const settings = TimeSpentTracker.settings;
    const timeSpentElement = document.getElementById('timeSpent');

    if (!timeSpentElement) {
        return;
    }

    if (localStorage.getItem('totalTimeSpent')) {
        totalTimeSpent = parseInt(localStorage.getItem('totalTimeSpent'), 10);
    }

    function formatTime(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        let result = '';
        if (days > 0) result += days + translations.days;
        if (hours > 0 || days > 0) result += hours + translations.hours;
        result += minutes + translations.minutes + secs + translations.seconds;

        return result;
    }

    function updateTimeSpent() {
        const currentTime = Math.floor((Date.now() - startTime) / 1000);
        const totalTime = totalTimeSpent + currentTime;

        timeSpentElement.textContent = translations.initialMessage + formatTime(totalTime);
        localStorage.setItem('totalTimeSpent', String(totalTime));
    }

    timeSpentElement.style.color = settings.textColor;
    timeSpentElement.style.backgroundColor = settings.backgroundColor;
    timeSpentElement.style.border = `2px solid ${settings.borderColor}`;

    updateTimeSpent();
    setInterval(updateTimeSpent, 1000);
});
