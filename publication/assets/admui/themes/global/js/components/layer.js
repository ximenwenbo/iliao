/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  /* eslint consistent-return: "warn" */

  $.components.register('layer', {
    mode: 'init',
    defaults: {
      target: 'parent',
      confirmBtn: ['确认', '取消'],
      prompt: 1
    },
    init: function(context) {
      var result = context && context.layer;
      var layer = result ? context.layer : window.layer;
      var frame$;
      var defaults;

      if (typeof layer === 'undefined') {
        return;
      }

      frame$ = context ? context.$ : $;
      defaults = this.defaults;

      frame$(context.document).on('click.site.layer', '[data-plugin="layer"]', function() {
        var $item = frame$(this);
        var options = $.extend(true, {}, defaults, $item.data());

        if (options.target === 'self') {
          if (!result) {
            return console.error('您在当前页面还没有引入layer插件');
          }
          layer = context.layer;
        } else {
          layer = result ? window.layer : layer;
        }

        switch (options.type) {
          case 'alert':
            layer.alert(options.message);
            break;
          case 'msg':
            layer.msg(options.message);
            break;
          case 'confirm':
            layer.confirm(
              options.title,
              {
                btn: options.confirmBtn
              },
              function() {
                layer.msg(options.successMessage);
              },
              function() {
                layer.msg(options.cancelMessage);
              }
            );
            break;
          case 'prompt':
            layer.prompt({title: options.title, formType: options.prompt}, function(text, index) {
              layer.close(index);
              layer.msg(options.message);
            });
            break;
          case 'tips':
            layer.tips(options.message, $item);
            break;
          case 'load':
            layer.load(options.style, {time: options.time});
            break;
          default:
            break;
        }
      });
    }
  });
})(window, document, jQuery);
