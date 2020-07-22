/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document, $) {
    "use strict";

    $.fn.dataTable.ext.type.order['salary-grade-pre'] = function (d) {
        switch (d) {
            case '低':
                return 1;
            case '中':
                return 2;
            case '高':
                return 3;
        }
        return 0;
    };

    $('#dataTableExample').DataTable($.concatCpt('dataTable', {
        "columnDefs": [{
            "type": "salary-grade",
            "targets": -1
        }]
    }));

})(window, document, jQuery);

