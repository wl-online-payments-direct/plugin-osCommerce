(function ($) {
  'use strict';

  $(function() {
    const $statusMessage = $('#payment-status-message');
    const $contactMessage = $('#payment-contact-message');

    if ($statusMessage.length === 0 || $statusMessage.data('polling-started')) {
      return;
    }

    // Mark as polling started to prevent multiple instances
    $statusMessage.data('polling-started', true);

    const ajaxUrl = $statusMessage.data('ajax-url');
    const startTime = Date.now();

    // Show contact message after 30 seconds
    setTimeout(function() {
      $contactMessage.fadeIn();
    }, 30000);

    /**
     * Progressive polling intervals
     * Before 30 seconds: poll every 3 seconds
     * Between 30 seconds and 3 minutes mark: pull every minute
     * After 3 minutes: poll every 5 minutes
     */
    function getNextPollDelay() {
      const elapsedSeconds = (Date.now() - startTime) / 1000;

      if (elapsedSeconds < 30) {
        // First 30 seconds: poll every 3 seconds
        return 3000;
      }

      if (elapsedSeconds < 180) {
        return 60000;
      }

      // After 3 minutes: poll every 5 minutes
      return 300000; // 5 minutes
    }

    function checkPaymentStatus() {
      $.ajax({
        url: ajaxUrl,
        method: 'GET',
        dataType: 'json',
        success: function (response) {
          // Check if we should redirect
          if (response.redirect_url) {
            window.location.href = response.redirect_url;
          } else {
            setTimeout(checkPaymentStatus, getNextPollDelay());
          }
        },
        error: function (xhr, status, error) {
          setTimeout(checkPaymentStatus, getNextPollDelay());
        }
      });
    }

    checkPaymentStatus();
  });

})(jQuery);
