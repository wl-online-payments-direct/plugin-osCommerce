function worldlineop_AdminProductExtender(module, tabTitle) {
    jQuery(function ($) {
        $('.nav-tabs-scroll').append(
            `<li data-bs-toggle="tab" data-bs-target="#tab_${module}_gift_card"><a href="#tab_${module}_gift_card" data-toggle="tab"><span>${tabTitle}</span></a></li>`
        );
        $('.tp-all-pages-btn ul').append(
            `<li data-bs-toggle="tab" data-bs-target="#tab_${module}_gift_card"><a href="#tab_${module}_gift_card" data-toggle="tab"><span>${tabTitle}</span></a></li>`
        );

        $(`a[href="#tab_${module}_gift_card"]`).click(function () {
            window.location.hash = $(this).attr('href').replace('#', '');
        });

        const tabContent = $(`#tab_${module}_gift_card`);
        $('.scrtabs-tab-container').parent().children('.tab-content:not(.platform-name-contents)').append(tabContent);
        tabContent.removeAttr('style');

        $('.nav-tabs-scroll').scrollingTabs('refresh');

        // Keep tabs in sync with all pages drop-down selector
        $('.tl-all-pages-block ul [data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            // click on "all pages" tab - activate tab and scroll
            var listId = $(this).parent().attr('id') ;
            if (typeof listId !== "undefined") {
                listId = listId.replace(/_scr$/, '');
                $('#' + listId + ' li.active').removeClass('active');
                $('#' + listId + ' [data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
            } else {
                $('.nav-tabs-scroll li.active').removeClass('active');
                $('.nav-tabs-scroll [data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
            }
            $('.nav-tabs-scroll').scrollingTabs('refresh');
        });
        $('.nav-tabs-scroll [data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            var listId = $(this).parent().attr('id') ;
            if (typeof listId !== "undefined") {
                $('#' + listId + '_scr li.active').removeClass('active');
                $('#' + listId + '_scr [data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
            } else {
                $('.tl-all-pages-block ul li.active').removeClass('active');
                $('.tl-all-pages-block ul li[data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
            }
        });

        // Reload tab on page refresh
        if (location.hash.length && window.bootstrap) {
            let urlHashArr = location.hash.substr(1).split('/');
            urlHashArr.forEach(function(hash){
                const triggerTabList = document.querySelectorAll('[data-bs-target="#' + hash + '"]');
                if (triggerTabList.length){
                    const tab = new bootstrap.Tab(triggerTabList[0]);
                    try {
                        tab.show();
                    } catch (e) {}
                    setTimeout(() => $(triggerTabList).trigger('shown.bs.tab'), 100)
                }
            })
        }
    });
}

