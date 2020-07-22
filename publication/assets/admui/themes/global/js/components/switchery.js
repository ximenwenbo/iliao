/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('switchery', {
    mode: 'init',
    defaults: {
      color: $.getColor('purple', 600)
    },
    init: function(context) {
      var Switchery = context && context.Switchery ? context.Switchery : window.Switchery;
      var frame$ = context ? context.$ : $;
      var defaults;

      if (typeof Switchery === 'undefined') {
        return;
      }

      defaults = $.components.getDefaults('switchery');

      frame$('[data-plugin="switchery"]', context.document).each(function() {
        var $item = $(this);
        var options = $.extend({}, defaults, $item.data(frame$));

        var instance = new Switchery(this, options);
        console.log(instance);
      });
    }
  });
})(window, document, jQuery);
