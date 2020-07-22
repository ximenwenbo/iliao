/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  'use strict';

  // 表单验证
  $('#loginForm').validate({
    rules: {
      loginName: {
        required: true
      },
      password: {
        required: true,
        minlength: 6,
        maxlength: 30
      },
      validCode: {
        required: true
      }
    },
    messages: {
      loginName: {
        required: '用户名不能为空'
      },
      password: {
        required: '密码不能为空',
        minlength: '密码必须大于6个字符',
        maxlength: '密码必须小于30个字符'
      },
      validCode: {
        required: '验证码不能为空'
      }
    },
    errorElement: 'small',
    errorPlacement: function(error, element) {
      // 在error 上添加 `invalid-feedback` class
      error.addClass('invalid-feedback');

      element.parent().append(error);
    },
    highlight: function(element) {
      $(element)
        .addClass('is-invalid')
        .removeClass('is-valid');
    },
    unhighlight: function(element, errorClass, validClass) {
      var $valid = $(element);

      if (!validClass) {
        $valid.removeClass('is-invalid is-valid');
      } else {
        $valid.addClass('is-valid').removeClass('is-invalid');
      }
    }
  });

  // 验证码刷新
  $('.reload-vify').on('click', function() {
    var $img = $(this).children('img');
    var URL = $img.prop('src');

    $img.prop('src', URL + '?tm=' + Math.random());
  });
})(jQuery);
