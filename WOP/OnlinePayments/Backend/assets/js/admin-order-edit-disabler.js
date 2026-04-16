jQuery(function ($) {
    const originalSetPlugin = window.setPlugin;
    window.setPlugin = function (data) {
        originalSetPlugin(data);

        $('[data-class="order-settings-box"]').popUp({
            opened: function() {
                $('.pop-up-content .widget-content').addClass('disabled');
            }
        });
    };

    $('.order-content .widget').addClass('disabled');
    $('#save_checkout').addClass('disabled');
    $('.order-content').keydown(function() {
        return false;
    });

    const callback = function(mutationsList, observer) {
        for(const mutation of mutationsList) {
            if (mutation.type === 'childList' || mutation.type === 'characterData') {
                $('.order-content .widget').addClass('disabled');
            }
        }
    };

    const observer = new MutationObserver(callback);
    observer.observe(
        $('.order-content')[0],
        {childList: true, subtree: true, characterData: true}
    );
});