/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  $.components.register('wizard', {
    mode: 'default',
    defaults: {
      step: '.steps .step, .pearls .pearl',
      buttonLabels: {
        back: '上一步',
        next: '下一步',
        finish: '完成'
      },
      templates: {
        buttons: function() {
          var options = this.options;
          return (
            '<div class="wizard-buttons"><a class="btn btn-default btn-outline" href="#' +
            this.id +
            '" data-wizard="back" role="button">' +
            options.buttonLabels.back +
            '</a><a class="btn btn-primary btn-outline float-right ml-10" href="#' +
            this.id +
            '" data-wizard="next" role="button">' +
            options.buttonLabels.next +
            '</a><a class="btn btn-success btn-outline float-right" href="#' +
            this.id +
            '" data-wizard="finish" role="button">' +
            options.buttonLabels.finish +
            '</a></div>'
          );
        }
      },
      classes: {
        step: {
          done: 'done',
          error: 'error',
          active: 'active',
          disabled: 'disabled',
          activing: 'activing',
          loading: 'loading'
        },
        pane: {
          active: 'active',
          activing: 'activing'
        },
        button: {
          hide: 'd-none d-sm-block',
          disabled: 'disabled'
        }
      }
    }
  });
})(window, document, jQuery);
