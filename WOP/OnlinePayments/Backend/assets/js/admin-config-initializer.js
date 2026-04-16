function opAdminConfigInit(module, adminConfigUrl) {
    adminConfigUrl = new URL(adminConfigUrl, window.location);

    jQuery(function ($) {
        const callback = function(mutationsList, observer) {
            for(const mutation of mutationsList) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    initModule();
                }
            }
        };

        function initModule() {
            const editButton = $(`a[href*="${module}"][href*="admin/modules/edit"]`, '#modules_management_data');
            if (!editButton.length) {
                return;
            }

            $(`a[href*="${module}"][href*="admin/modules/export"]`, '#modules_management_data').remove();
            $(`a.btn-import`, '#modules_management_data').remove();

            const editUrl = new URL(editButton.attr("href"), window.location);
            adminConfigUrl.searchParams.set("platform_id", editUrl.searchParams.get("platform_id"));
            editButton.attr("href", adminConfigUrl.href);
        }

        // Setup the target element observer
        const targetNode = document.getElementById('modules_management_data');
        const config = { childList: true, subtree: true, characterData: true };

        const observer = new MutationObserver(callback);
        observer.observe(targetNode, config);
    });
}

