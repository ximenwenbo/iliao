/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document, $) {
    "use strict";

    var table = $('#dataTableExample').DataTable($.concatCpt('dataTable'));

    $(document).on('click', '#dataTableExample tbody tr', function () {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        }
        else {
            table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });

    $(document).on('click', '#DTDelRow', function () {
        table.row('.selected').remove().draw(false);
    });

})(window, document, jQuery);

