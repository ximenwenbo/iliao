/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  $(function() {
    var tableContent = $('#examplePopoverTable').html();
    var listContent = $('#examplePopoverList').html();
    var largeContent = $('#examplePopoverLargeContent').html();

    $('[data-toggle="tooltip"]:not(#tooltipClick)').tooltip({trigger: 'hover'});
    $('#tooltipClick').tooltip({trigger: 'click'});
    $('[data-toggle="popover"]').popover();

    // Webui Popover - 表格
    // ------------------------------------

    $('#examplePopWithTable').webuiPopover(
      $.concatCpt('webuiPopover', {
        title: 'WebUI Popover',
        content: tableContent,
        width: 500
      })
    );

    // Webui Popover - 列表组
    // -----------------------------------

    $('#examplePopWithList').webuiPopover(
      $.concatCpt('webuiPopover', {
        content: listContent,
        title: '',
        padding: false
      })
    );

    // Webui Popover - 内容较多
    // --------------------------------------------

    $('#examplePopWithLargeContent').webuiPopover(
      $.concatCpt('webuiPopover', {
        title: 'WebUI Popover',
        content: largeContent,
        width: 400,
        height: 350,
        closeable: true
      })
    );
  });
})(document, window, jQuery);
