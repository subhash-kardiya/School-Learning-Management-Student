(function() {
    function ensureHost() {
        var host = document.getElementById('appToastHost');
        if (host) return host;

        host = document.createElement('div');
        host.id = 'appToastHost';
        host.className = 'app-toast-host';
        document.body.appendChild(host);
        return host;
    }

    function iconFor(type) {
        if (type === 'success') return 'fas fa-check-circle';
        if (type === 'error') return 'fas fa-exclamation-circle';
        if (type === 'warning') return 'fas fa-exclamation-triangle';
        return 'fas fa-info-circle';
    }

    function labelFor(type) {
        if (type === 'success') return 'Success';
        if (type === 'error') return 'Error';
        if (type === 'warning') return 'Warning';
        return 'Info';
    }

    function removeMatchingAlert(selector, message) {
        var alerts = Array.prototype.slice.call(document.querySelectorAll(selector));
        alerts.forEach(function(el) {
            var text = (el.textContent || '').replace(/\s+/g, ' ').trim();
            if (text === message.trim()) {
                el.remove();
            }
        });
    }

    function showToast(message, type, duration) {
        if (!message) return;
        var host = ensureHost();
        var toast = document.createElement('div');
        toast.className = 'app-toast app-toast--' + type;
        var totalDuration = Math.max(parseInt(duration || 4000, 10), 1000);
        toast.style.setProperty('--toast-duration', totalDuration + 'ms');

        var icon = document.createElement('div');
        icon.className = 'app-toast__icon';
        icon.innerHTML = '<i class="' + iconFor(type) + '"></i>';

        var text = document.createElement('div');
        text.className = 'app-toast__text';
        text.innerHTML = '<span class="app-toast__title">' + labelFor(type) + '</span>' +
            '<span class="app-toast__message"></span>';
        text.querySelector('.app-toast__message').textContent = message;

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'app-toast__close';
        close.innerHTML = '&times;';

        var hide = function() {
            toast.classList.add('is-hiding');
            setTimeout(function() {
                toast.remove();
            }, 200);
        };

        close.addEventListener('click', hide);

        toast.appendChild(icon);
        toast.appendChild(text);
        toast.appendChild(close);
        host.appendChild(toast);

        var timeoutId = null;
        var startTime = Date.now();
        var remaining = totalDuration;

        var scheduleHide = function(ms) {
            clearTimeout(timeoutId);
            startTime = Date.now();
            timeoutId = setTimeout(hide, ms);
        };

        toast.addEventListener('mouseenter', function() {
            toast.classList.add('is-paused');
            clearTimeout(timeoutId);
            remaining = Math.max(0, remaining - (Date.now() - startTime));
        });

        toast.addEventListener('mouseleave', function() {
            if (remaining <= 0) {
                hide();
                return;
            }
            toast.classList.remove('is-paused');
            toast.style.setProperty('--toast-duration', remaining + 'ms');
            scheduleHide(remaining);
        });

        scheduleHide(remaining);
    }

    window.AppToast = {
        show: showToast
    };

    document.addEventListener('DOMContentLoaded', function() {
        var body = document.body;
        var success = body.getAttribute('data-flash-success') || '';
        var error = body.getAttribute('data-flash-error') || '';
        var warning = body.getAttribute('data-flash-warning') || '';
        var info = body.getAttribute('data-flash-info') || '';
        var duration = parseInt(body.getAttribute('data-flash-duration') || '4000', 10);

        if (success) {
            showToast(success, 'success', duration);
            removeMatchingAlert('.main-content .alert.alert-success', success);
        }
        if (error) {
            showToast(error, 'error', duration);
            removeMatchingAlert('.main-content .alert.alert-danger', error);
        }
        if (warning) {
            showToast(warning, 'warning', duration);
            removeMatchingAlert('.main-content .alert.alert-warning', warning);
        }
        if (info) {
            showToast(info, 'info', duration);
            removeMatchingAlert('.main-content .alert.alert-info', info);
        }
    });
})();
