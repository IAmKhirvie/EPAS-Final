(function() {
  // Error popup utility with console logging for debugging
  window.showErrorPopup = function(message, title, type) {
    type = type || 'error';
    var overlay = document.getElementById('errorPopupOverlay');
    var titleEl = document.getElementById('errorPopupTitle');
    var bodyEl = document.getElementById('errorPopupBody');
    var iconEl = document.getElementById('errorPopupIcon');

    // If popup elements don't exist, fall back to console only
    if (!overlay || !titleEl || !bodyEl || !iconEl) {
      console.error('[JOMS]', title || 'Error', '-', message);
      return;
    }

    var config = {
      error:   { title: title || 'Error',   icon: 'fas fa-exclamation-circle', iconClass: 'error' },
      warning: { title: title || 'Warning', icon: 'fas fa-exclamation-triangle', iconClass: 'warning' },
      info:    { title: title || 'Notice',  icon: 'fas fa-info-circle', iconClass: 'info' },
      success: { title: title || 'Success', icon: 'fas fa-check-circle', iconClass: 'info' }
    };

    var c = config[type] || config.error;
    titleEl.textContent = c.title;
    bodyEl.textContent = message;
    iconEl.className = 'error-popup-icon ' + c.iconClass;
    iconEl.innerHTML = '<i class="' + c.icon + '"></i>';
    overlay.classList.add('active');

    // Log errors only in development (suppress in production)
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      var logMethod = type === 'error' ? 'error' : (type === 'warning' ? 'warn' : 'info');
      console[logMethod]('[JOMS ' + type + '] ' + message);
    }
  };

  // Close popup handlers
  var closeBtn = document.getElementById('errorPopupCloseBtn');
  var overlayEl = document.getElementById('errorPopupOverlay');

  if (closeBtn) {
    closeBtn.addEventListener('click', function() {
      overlayEl.classList.remove('active');
    });
  }
  if (overlayEl) {
    overlayEl.addEventListener('click', function(e) {
      if (e.target === this) this.classList.remove('active');
    });
  }
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && overlayEl) overlayEl.classList.remove('active');
  });

  // ── Global uncaught JS error handler ──
  window.onerror = function(message, source, lineno, colno, error) {
    // Suppress console noise in production
    return false;
  };

  // ── Unhandled promise rejection handler ──
  window.addEventListener('unhandledrejection', function(event) {
    var msg = 'Unhandled promise rejection';
    if (event.reason) {
      msg = event.reason.message || event.reason.toString();
    }
    if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
      console.error('[JOMS Promise Error]', msg);
    }
    // Don't show popup — just log
  });

  // ── Intercept AJAX errors globally (jQuery) ──
  if (typeof $ !== 'undefined') {
    $(document).ajaxError(function(event, jqXHR, settings, error) {
      if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
          console.error('[AJAX Error]', jqXHR.responseJSON.message);
      } else {
          console.error('[AJAX Error]', error);
      }
    });
  }

  if (typeof $ !== 'undefined' && $.ajaxSetup) {
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    $.ajaxSetup({
      headers: csrfMeta ? { 'X-CSRF-TOKEN': csrfMeta.content } : {},
      error: function(xhr) {
        var msg = 'An unexpected error occurred.';
        try {
          var json = JSON.parse(xhr.responseText);
          msg = json.message || json.error || msg;
        } catch(e) {}
        window.showErrorPopup(msg, 'Error ' + xhr.status, 'error');
      }
    });
  }

  // ── Intercept fetch errors globally ──
  var originalFetch = window.fetch;
  window.fetch = function() {
    var url = arguments[0];
    if (typeof url === 'object' && url.url) url = url.url;
    var urlStr = String(url);

    // Skip error handling for background polling endpoints
    var silentEndpoints = ['badge-counts', 'unread-count', 'pending-count'];
    var isSilent = silentEndpoints.some(function(ep) { return urlStr.includes(ep); });

    return originalFetch.apply(this, arguments).then(function(response) {
      if (!response.ok && !isSilent && response.headers.get('content-type') && response.headers.get('content-type').includes('application/json')) {
        var cloned = response.clone();
        cloned.json().then(function(data) {
          if (data.error || data.message) {
            window.showErrorPopup(data.message || 'An error occurred.', 'Error ' + response.status, 'error');
          }
        }).catch(function() {});
      }
      return response;
    }).catch(function(error) {
      if (error.name !== 'AbortError' && !isSilent) {
        window.showErrorPopup('Network error. Please check your connection and try again.', 'Connection Error', 'warning');
      }
      throw error;
    });
  };

  // ── Intercept Axios errors if available ──
  if (typeof axios !== 'undefined') {
    axios.interceptors.response.use(
      function(response) { return response; },
      function(error) {
        if (error.response) {
          var msg = 'An unexpected error occurred.';
          if (error.response.data && error.response.data.message) {
            msg = error.response.data.message;
          }
          var url = error.config ? error.config.url : 'unknown';
          // Log detailed info to console
          console.error('[JOMS Axios Error]', error.response.status, msg, 'URL:', url);
          window.showErrorPopup(msg + ' (URL: ' + url + ')', 'Error ' + error.response.status, 'error');
        } else if (error.request) {
          window.showErrorPopup('Network error. Please check your connection.', 'Connection Error', 'warning');
        }
        return Promise.reject(error);
      }
    );
  }
})();
