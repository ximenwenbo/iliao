/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('animate-list', {
    mode: 'init',
    defaults: {
      child: '.panel',
      duration: 250,
      delay: 50,
      animate: 'scale-up',
      fill: 'backwards'
    },

    init: function(context) {
      var self = this;
      var frame$ = context ? context.$ : $;

      frame$('[data-plugin="animateList"]', context.document).each(function() {
        var $item = frame$(this);
        var options = $.extend({}, self.defaults, $(this).data(frame$), true);

        var AnimatedBox = function($el, opts) {
          var delay = 0;
          var that = this;

          this.options = opts;
          this.$children = $el.find(opts.child);
          this.$children.addClass('animation-' + opts.animate);
          this.$children.css('animation-fill-mode', opts.fill);
          this.$children.css('animation-duration', opts.duration + 'ms');

          this.$children.each(function() {
            frame$(this).css('animation-delay', delay + 'ms');
            delay += that.options.delay;
          });
        };

        AnimatedBox.prototype = {
          run: function(type) {
            var that = this;

            this.$children.removeClass('animation-' + this.options.animate);

            if (typeof type !== 'undefined') {
              this.options.animate = type;
            }
            setTimeout(function() {
              that.$children.addClass('animation-' + that.options.animate);
            }, 0);
          }
        };

        $item.data('animateList', new AnimatedBox($item, options), frame$);
      });
    }
  });
})(window, document, jQuery);
