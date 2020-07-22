/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('filterable', {
    mode: 'init',
    defaults: {
      animationOptions: {
        duration: 750,
        easing: 'linear',
        queue: false
      }
    },
    init: function(context) {
      var frame$ = context ? context.$ : $;
      var global = context || window;
      var defaults;
      var callback;

      if (typeof frame$.fn.isotope === 'undefined') {
        return;
      }

      defaults = $.components.getDefaults('filterable');

      callback = function() {
        frame$('[data-filterable]', context).each(function() {
          var $item = frame$(this);

          var options = $.extend(true, {}, defaults, $item.data(), {
            filter: '*'
          });

          $item.isotope(options);
        });

        frame$('[data-filter]', context).click(function(e) {
          var $item = frame$(this);
          var target = $item.data('target', frame$);
          var $li = $item.parent('li');
          var $list;
          var filter;

          if (!target) {
            target = $item.attr('href');
            target = target && target.replace(/.*(?=#[^\s]*$)/, '');
          }

          $li.siblings('.active').each(function() {
            var $that = frame$(this);

            $that.find('a').attr('aria-expanded', false);
            $that.removeClass('active');
          });

          $li.addClass('active');
          $item.attr('aria-expanded', true);

          $list = frame$(target, context.document);
          filter = $item.attr('data-filter');

          if (filter !== '*') {
            filter = '[data-type="' + filter + '"]';
          }

          $list.isotope({
            filter: filter
          });

          e.preventDefault();
        });
      };

      if (context.document !== window.document) {
        callback();
      } else {
        $(global).on('load', function() {
          callback();
        });
      }
    }
  });
})(window, document, jQuery);
