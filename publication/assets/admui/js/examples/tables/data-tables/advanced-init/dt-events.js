/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document, $) {
    "use strict";

    var eventFired = function (type) {
        var n = $('#dataTableEventDemoInfo')[0];
        n.innerHTML += '<div>' + type + ' 事件 - ' + new Date().getTime() + '</div>';
        n.scrollTop = n.scrollHeight;
    };

    $('#dataTableExample')
        .on('order.dt', function () {
            eventFired('排序');
        })
        .on('search.dt', function () {
            eventFired('查找');
        })
        .on('page.dt', function () {
            eventFired('分页');
        }).DataTable($.concatCpt('dataTable'));

})(window, document, jQuery);

