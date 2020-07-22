/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('buttons', {
    mode: 'api',
    defaults: {},
    api: function(context) {
      var frame$ = context ? context.$ : $;

      frame$(context.document).on('click.site.loading', '[data-loading-text]', function() {
        var $btn = frame$(this);
        var text = $btn.text();
        var i = 20;
        var loadingText = $btn.data('loadingText', frame$);

        var timeout = setInterval(function() {
          i -= 1;
          $btn.text(loadingText + '(' + i + ')');
          if (i === 0) {
            clearInterval(timeout);
            $btn.text(text).css('opacity', '1');
          }
        }, 1000);

        $btn.text(loadingText + '(' + i + ')').css('opacity', '.6');
      });

      frame$(context.document).on('click.site.morebutton', '[data-more]', function() {
        var $target = frame$(frame$(this).data('more', frame$));
        $target.toggleClass('show');
      });
    }
  });
})(window, document, jQuery);
