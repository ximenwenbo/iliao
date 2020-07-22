/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document, $) {
    "use strict";

    var table = $('#dataTableExample').DataTable($.concatCpt('dataTable', {
        "scrollY": "200px",
        "paging": false
    }));

    $(document).on('click', '#DTToggleBtn .btn', function () {
        // 获取 API 对象
        var column = table.column($(this).attr('data-column'));

        // 显示切换
        column.visible(!column.visible());
    });

})(window, document, jQuery);

