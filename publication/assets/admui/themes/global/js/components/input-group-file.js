/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('input-group-file', {
    api: function(context) {
      context.$(context.document).on('change', '.input-group-file [type=file]', function() {
        var $item = context.$(this);
        var $text = $item.parents('.input-group-file').find('.form-control');
        var value = '';

        $.each($item[0].files, function(i, file) {
          value += file.name + ', ';
        });
        value = value.substring(0, value.length - 2);

        $text.val(value);
      });
    }
  });
})(window, document, jQuery);
