/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('editableTable', {
    mode: 'init',
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var defaults;

      if (!frame$.fn.editableTableWidget) {
        return;
      }

      defaults = $.components.getDefaults('editableTable');

      frame$('[data-plugin="editableTable"]', context).each(function() {
        var $item = frame$(this);
        var options = $.extend(true, {}, defaults, $item.data(frame$));

        $item.editableTableWidget(options);
      });
    }
  });
})(window, document, jQuery);
