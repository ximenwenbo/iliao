/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document, $) {
    "use strict";

    function filterGlobal() {
        $('#dataTableExample').DataTable($.concatCpt('dataTable')).search(
            $('#global_filter').val(),
            $('#global_regex').prop('checked'),
            $('#global_smart').prop('checked')
        ).draw();
    }

    function filterColumn(i) {
        $('#dataTableExample').DataTable($.concatCpt('dataTable')).column(i).search(
            $('#col' + i + '_filter').val(),
            $('#col' + i + '_regex').prop('checked'),
            $('#col' + i + '_smart').prop('checked')
        ).draw();
    }

    $('#dataTableExample').DataTable($.concatCpt('dataTable'));

    $(document).on('keyup click', 'input.global_filter', function () {
        filterGlobal();
    });

    $(document).on('keyup click', 'input.column_filter', function () {
        filterColumn($(this).parents('tr').attr('data-column'));
    });

})(window, document, jQuery);

