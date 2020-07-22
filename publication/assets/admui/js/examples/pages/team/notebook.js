/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  window.App.extend({
    run: function() {
      var $actionBtn = $('.site-action')
        .actionBtn({
          toggleSelector: '.list-group-item',
          listSelector: '.site-action-buttons'
        })
        .data('actionBtn');
      var $noteList = $('.list-group-item');

      $actionBtn.show();

      $(document).on('click', '.site-action-toggle', function(e) {
        if (!$noteList.hasClass('active')) {
          $('#addNewNote').modal('show');

          e.stopPropagation();
        } else {
          $('.list-group-item').removeClass('active');
          $actionBtn.hide();
        }
      });

      $(document).on('click', '.list-group-item', function() {
        $(this)
          .addClass('active')
          .siblings()
          .removeClass('active');

        if ($(this).hasClass('active')) {
          $actionBtn.show();
        }
      });
    }
  });

  $(function() {
    window.App.run();
  });
})(window, document, jQuery);
