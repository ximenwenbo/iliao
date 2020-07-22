/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function (window, document, $) {
    "use strict";

    var table = $('#dataTableExample').DataTable($.concatCpt('dataTable'));

    $(document).on('click', '#DTSubmitBtn', function () {
        var data = table.$('input, select').serialize();

        toastr.info("将向服务器提交以下数据：<br>" + data.substr(0, 120) + '...');
    });

})(window, document, jQuery);

