/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (document, window, $) {
    'use strict';

    if ($('.list-group[data-plugin="nav-tabs"]').length) {
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            $(e.target).addClass('active').siblings().removeClass('active');
        });
    }

})(document, window, jQuery);