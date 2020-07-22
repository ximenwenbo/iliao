/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global toastr */

  $(function() {
    // 普通
    var wizard;
    var wizardContainer;

    $.validator.addMethod(
      'nameRegExp',
      function(value) {
        return /^[a-zA-Z0-9]+$/.test(value);
      },
      '用户名只能由字母，数字，小数点和下划线组成'
    );

    // 普通向导-账户表单验证
    $('#exampleAccountForm').validate({
      onsubmit: false,
      onfocusout: false,
      rules: {
        username: {
          required: true,
          minlength: 6,
          maxlength: 30,
          nameRegExp: true
        },
        password: {
          required: true
        }
      },
      messages: {
        username: {
          required: '请填写用户名',
          minlength: '用户名长度不能少于6个字符',
          maxlength: '用户名长度不能大于30个字符'
        },
        password: {
          required: '请填写密码'
        }
      }
    });

    // 普通向导-订单表单验证
    $('#exampleBillingForm').validate({
      onsubmit: false,
      onfocusout: false,
      rules: {
        number: {
          required: true
        },
        cvv: {
          required: true
        }
      },
      messages: {
        number: {
          required: '请填写信用卡卡号'
        },
        cvv: {
          required: '请填写CVV码'
        }
      }
    });

    // 初始化
    wizard = $('#exampleWizardForm')
      .wizard(
        $.concatCpt('wizard', {
          buttonsAppendTo: '.panel-body'
        })
      )
      .data('wizard');

    // 普通向导-账户表单验证
    wizard.get('#exampleAccount').setValidator(function() {
      return $('#exampleAccountForm').valid();
    });

    // 普通向导-订单表单验证
    wizard.get('#exampleBilling').setValidator(function() {
      return $('#exampleBillingForm').valid();
    });

    // 链式向导-账户表单验证
    $('#formContainerOne').validate({
      onsubmit: false,
      onfocusout: false,
      rules: {
        username: {
          required: true
        },
        password: {
          required: true
        }
      },
      messages: {
        username: {
          required: '请填写用户名'
        },
        password: {
          required: '请填写密码'
        }
      }
    });

    // 链式向导-订单表单验证
    $('#formContainerTwo').validate({
      onsubmit: false,
      onfocusout: false,
      rules: {
        number: {
          required: true
        },
        cvv: {
          required: true
        }
      },
      messages: {
        number: {
          required: '请填写信用卡卡号'
        },
        cvv: {
          required: '请填写CVV码'
        }
      }
    });

    // 初始化
    wizardContainer = $('#exampleWizardFormContainer')
      .wizard(
        $.concatCpt('wizard', {
          buttonsAppendTo: '.panel-body'
        })
      )
      .data('wizard');

    // 链式向导-账户表单验证
    wizardContainer.get('#exampleAccountWizard').setValidator(function() {
      return $('#exampleAccountFormWizard').valid();
    });

    // 链式向导-订单表单验证
    wizardContainer.get('#exampleBillingOne').setValidator(function() {
      return $('#exampleBillingFormWizard').valid();
    });

    // 分页
    $('#exampleWizardPager').wizard(
      $.concatCpt('wizard', {
        step: '.wizard-pane',
        templates: {
          buttons: function() {
            var options = this.options;
            return (
              '<div class="btn-group btn-group-sm">' +
              '<a class="btn btn-default btn-outline" href="#' +
              this.id +
              '" data-wizard="back" role="button">' +
              options.buttonLabels.back +
              '</a>' +
              '<a class="btn btn-success btn-outline float-right" href="#' +
              this.id +
              '" data-wizard="finish" role="button">' +
              options.buttonLabels.finish +
              '</a>' +
              '<a class="btn btn-default btn-outline float-right" href="#' +
              this.id +
              '" data-wizard="next" role="button">' +
              options.buttonLabels.next +
              '</a>' +
              '</div>'
            );
          }
        },
        buttonLabels: {
          next: '<i class="icon wb-chevron-right" aria-hidden="true"></i>',
          back: '<i class="icon wb-chevron-left" aria-hidden="true"></i>',
          finish: '<i class="icon wb-check" aria-hidden="true"></i>'
        },

        buttonsAppendTo: '.panel-actions'
      })
    );

    // 进度条
    $('#exampleWizardProgressbar').wizard(
      $.concatCpt('wizard', {
        step: '.wizard-pane',
        onInit: function() {
          this.$progressbar = this.$element.find('.progress-bar').addClass('progress-bar-striped');
        },
        onBeforeShow: function(step) {
          step.$element.tab('show');
        },
        onFinish: function() {
          this.$progressbar.removeClass('progress-bar-striped').addClass('progress-bar-success');
        },
        onAfterChange: function(prev, step) {
          var total = this.length();
          var current = step.index + 1;
          var percent = (current / total) * 100;

          this.$progressbar
            .css({
              width: percent + '%'
            })
            .find('.sr-only')
            .text(current + '/' + total);
        },
        buttonsAppendTo: '.panel-body'
      })
    );

    // 选项卡
    $('#exampleWizardTabs').wizard(
      $.concatCpt('wizard', {
        step: '> .nav > li > a',
        onBeforeShow: function(step) {
          step.$element.tab('show');
        },
        classes: {
          step: {
            // done: 'color-done',
            error: 'color-error'
          }
        },
        onFinish: function() {
          toastr.success('完成');
        },
        buttonsAppendTo: '.tab-content'
      })
    );
    // 折叠面板
    $('#exampleWizardAccordion').wizard(
      $.concatCpt('wizard', {
        step: '.panel-title[data-toggle="collapse"]',
        classes: {
          step: {
            // done: 'color-done',
            error: 'color-error'
          }
        },
        templates: {
          buttons: function() {
            return (
              '<div class="panel-footer">' +
              $.components.getDefaults('wizard').templates.buttons.call(this) +
              '</div>'
            );
          }
        },
        onBeforeShow: function(step) {
          step.$pane.collapse('show');
        },

        onBeforeHide: function(step) {
          step.$pane.collapse('hide');
        },

        onFinish: function() {
          toastr.success('完成');
        },

        buttonsAppendTo: '.panel-collapse'
      })
    );
  });
})(document, window, jQuery);
