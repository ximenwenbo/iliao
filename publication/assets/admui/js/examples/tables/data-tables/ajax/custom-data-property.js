/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $('#dataTableExample').DataTable(
    $.concatCpt('dataTable', {
      processing: true,
      ajax: {
        url: $.configs.ctx + '/public/data/examples/tables/data-tables/dt-ajax-5.json',
        dataSrc: 'demo'
      }
    })
  );
})(window, document, jQuery);
