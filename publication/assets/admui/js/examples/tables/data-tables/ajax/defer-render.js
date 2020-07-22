/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $('#dataTableExample').DataTable(
    $.concatCpt('dataTable', {
      ajax: $.configs.ctx + '/public/data/examples/tables/data-tables/dt-ajax.json',
      deferRender: true
    })
  );
})(window, document, jQuery);
