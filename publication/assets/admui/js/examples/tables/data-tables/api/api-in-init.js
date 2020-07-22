/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document, $) {
    "use strict";

    $('#dataTableExample').dataTable($.concatCpt('dataTable', {
        "initComplete": function () {
            var api = this.api();
            api.$('td').click(function () {
                api.search(this.innerHTML).draw();
            });
        }
    }));

})(window, document, jQuery);

