/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('material', {
    init: function(context) {
      var frame$ = context ? context.$ : $;

      frame$('.form-material').each(function() {
        var $item = frame$(this);
        var $control = $item.find('.form-control');
        var placeholder = $control.attr('placeholder');

        if ($item.data('material', frame$) === true) {
          return;
        }

        // Add hint label if required
        if ($control.attr('data-hint')) {
          $control.after('<div class=hint>' + $control.attr('data-hint') + '</div>');
        }

        if ($item.hasClass('floating')) {
          // Add floating label if required
          if ($control.hasClass('floating-label')) {
            $control.attr('placeholder', null).removeClass('floating-label');
            $control.after('<div class=floating-label>' + placeholder + '</div>');
          }

          // Set as empty if is empty
          if ($control.val() === null || $control.val() === 'undefined' || $control.val() === '') {
            $control.addClass('empty');
          }
        }

        // Support for file input
        if ($control.next().is('[type=file]')) {
          $item.addClass('form-material-file');
        }

        $item.data('material', true, frame$);
      });
    },
    api: function(context) {
      var frame$ = context ? context.$ : $;

      var isChar = function(e) {
        if (typeof e.which === 'undefined') {
          return true;
        } else if (typeof e.which === 'number' && e.which > 0) {
          return !e.ctrlKey && !e.metaKey && !e.altKey && e.which !== 8 && e.which !== 9;
        }
        return false;
      };

      frame$(context.document)
        .on('keydown.site.material paste.site.material', '.form-control', function(e) {
          if (isChar(e)) {
            frame$(this).removeClass('empty');
          }
        })
        .on('keyup.site.material change.site.material', '.form-control', function() {
          var $that = frame$(this);
          if (
            $that.val() === '' &&
            (typeof $that[0].checkValidity !== 'undefined' && $that[0].checkValidity())
          ) {
            $that.addClass('empty');
          } else {
            $that.removeClass('empty');
          }
        })
        .on('focus', '.form-material-file', function() {
          frame$(this)
            .find('input')
            .addClass('focus');
        })
        .on('blur', '.form-material-file', function() {
          frame$(this)
            .find('input')
            .removeClass('focus');
        })
        .on('change', '.form-material-file [type=file]', function() {
          var $that = frame$(this);
          var value = '';
          $.each($that[0].files, function(i, file) {
            value += file.name + ', ';
          });
          value = value.substring(0, value.length - 2);
          if (value) {
            $that.prev().removeClass('empty');
          } else {
            $that.prev().addClass('empty');
          }
          $that.prev().val(value);
        });
    }
  });
})(window, document, jQuery);
