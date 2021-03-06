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
      ajax: $.configs.ctx + '/public/data/examples/tables/data-tables/dt-ajax-1.json',
      columns: [
        {data: 'name'},
        {data: 'hr.position'},
        {data: 'contact.0'},
        {data: 'contact.1'},
        {data: 'hr.start_date'},
        {data: 'hr.salary'}
      ]
    })
  );
})(window, document, jQuery);
