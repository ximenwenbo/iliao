/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  $.validator.addMethod(
    'hexaColor',
    function(value) {
      return /#[A-Fa-f0-9]{6}/.test(value);
    },
    '请输入正确的HEXA颜色值'
  );

  $.validator.addMethod(
    'nameRegexp',
    function(value) {
      return /^[a-zA-Z0-9]+$/.test(value);
    },
    '请输入正确的字符串'
  );

  // 完整示例
  $('#exampleFullForm').validate({
    rules: {
      username: {
        required: true,
        minlength: 6,
        maxlength: 30,
        nameRegexp: true
      },
      email: {
        required: true,
        emial: true
      },
      password: {
        required: true,
        minlength: 8
      },
      birthday: {
        required: true,
        date: true
      },
      github: {
        required: true,
        url: true
      },
      skills: {
        required: true,
        maxlength: 300
      },
      'for[]': {
        required: true
      },
      company: {
        required: true
      },
      browsers: {
        required: true
      }
    }
  });

  // 规则
  $('#exampleConstraintsForm').validate({
    rules: {
      requiredInput: {
        required: true
      },
      requiredSelect: {
        required: true
      },
      requiredCheckbox: {
        required: true
      },
      vMinLength: {
        required: true,
        minlength: 8
      },
      vMaxLength: {
        required: true,
        maxlength: 5
      },
      vBetweenLength: {
        required: true,
        rangelength: [5, 10]
      },
      vMin: {
        required: true,
        min: 6
      },
      vMax: {
        required: true,
        max: 50
      },
      vRange: {
        required: true,
        range: [6, 50]
      },
      vRegExp: {
        required: true,
        hexaColor: true
      },
      foo_one: {
        required: true
      },
      foo_two: {
        required: true,
        equalTo: '#foo_one'
      }
    }
  });

  // 类型
  $('#exampleConstraintsFormTypes').validate({
    rules: {
      type_email: {
        required: true,
        email: true
      },
      type_url: {
        required: true,
        url: true
      },
      type_digits: {
        required: true,
        digits: true
      },
      type_numberic: {
        required: true,
        number: true
      },
      type_phone: {
        required: true,
        mobileCN: true
      },
      type_credit_card: {
        required: true,
        creditcard: true
      },
      type_date: {
        required: true,
        date: true
      },
      type_color: {
        required: true,
        hexaColor: true
      },
      type_ip: {
        required: true,
        ipv4: true
      }
    }
  });

  // 标准模式
  $('#exampleStandardForm').validate({
    rules: {
      standard_name: {
        required: true
      },
      standard_email: {
        required: true,
        email: true
      },
      standard_content: {
        required: true,
        maxlength: 500
      }
    }
  });

  // 摘要模式
  $('#exampleSummaryForm').validate({
    rules: {
      summary_name: {
        required: true
      },
      summary_email: {
        required: true,
        email: true
      },
      summary_content: {
        required: true,
        maxlength: 500
      }
    }
  });
})(document, window, jQuery);
