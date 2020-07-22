/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  /* global toastr */

  var ctx = $.configs.ctx;

  $(function() {
    // 验证密码是否有效
    $.validator.addMethod(
      'validPwd',
      function(value) {
        var result = false;

        $.ajax({
          url: '/admin/user_center/checkPassword',
          type: 'POST',
          data: JSON.stringify({oldPassword: value}),
          dataType: 'JSON',
          contentType: 'application/json',
          async: false,
          success: function(res) {
            result = res.success;
          },
          error: function() {
            toastr.error('服务器异常，请配合后端程序使用');
          }
        });

        return result;
      },
      '请填写正确的密码'
    );

    // 密码修改表单验证等相关操作
    $('#accountMsg').validate({
      rules: {
        oldPwd: {
          required: true,
          validPwd: true
        },
        newPwd: {
          required: true,
          minlength: 6,
          maxlength: 30
        },
        confirm: {
          required: true,
          equalTo: '#newPwd'
        }
      },
      messages: {
        oldPwd: {
          required: '密码不能为空'
        },
        newPwd: {
          required: '密码不能为空',
          minlength: '密码必须大于等于6个字符',
          maxlength: '密码必须小于等于30个字符'
        },
        confirm: {
          required: '确认密码不能为空',
          equalTo: '确认密码必须和密码保持一致'
        }
      },
      submitHandler: function(form) {
        var oldPwd = $(form)
          .find('[name="oldPwd"]')
          .val();
        var newPwd = $(form)
          .find('[name="newPwd"]')
          .val();
        var confirmPwd = $(form)
          .find('[name="confirm"]')
          .val();
        $.ajax({
          url: '/admin/user_center/changePassword',
          type: 'POST',
          data: JSON.stringify({oldPassword: oldPwd, newPassword: newPwd, repPassword: confirmPwd}),
          dataType: 'JSON',
          contentType: 'application/json',
          success: function(res) {
            var time = 5;
            var timer;
            if (res.success) {
              window.top.layer.alert('修改成功,请重新登录',{icon: 5},function () {
                window.parent.location.href = '/admin/public/logout';
              });

            } else {
              window.top.layer.alert(res.msg,{icon:7});
            }
          },
          error: function() {
            toastr.error('服务器异常，请稍后再试！');
          }
        });
        return false;
      }
    });
  });
})(window, document, jQuery);
