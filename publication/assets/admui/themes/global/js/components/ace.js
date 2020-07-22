/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('ace', {
    mode: 'init',
    defaults: {},
    init: function(context) {
      var ace = context && context.ace ? context.ace : window.ace;
      var frame$;

      if (typeof ace === 'undefined') {
        return;
      }

      frame$ = context ? context.$ : $;

      // ace.config.set("themePath", "../theme");
      ace.config.loadModule('ace/ext/language_tools');

      frame$('[data-plugin="ace"]').each(function() {
        var $item = frame$(this);
        var id = $item.attr('id');
        var mode = $(this).data('mode', frame$);
        var theme = $(this).data('theme', frame$);
        var editor = ace.edit(id);

        editor.container.style.opacity = '';
        if (mode) {
          editor.session.setMode('ace/mode/' + mode);
        }
        if (theme) {
          editor.setTheme('ace/theme/' + theme);
        }

        editor.setOption('maxLines', 40);
        editor.setAutoScrollEditorIntoView(true);

        ace.config.loadModule('ace/ext/language_tools', function() {
          editor.setOptions({
            enableSnippets: true,
            enableBasicAutocompletion: true
          });
        });
      });
    }
  });
})(window, document, jQuery);
