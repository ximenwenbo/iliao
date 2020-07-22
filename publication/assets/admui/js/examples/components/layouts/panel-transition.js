/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  $(function() {
    window.App.run();

    $(document).on('click.panel.transition', '[data-type]', function() {
      var type = $(this).data('type');

      $('#exampleTransition')
        .data('animateList')
        .run(type);
    });
  });
})(document, window, jQuery);
