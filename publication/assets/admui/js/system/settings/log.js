/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global toastr, _ */
  /* eslint consistent-return: "warn" */

  // 获取当前页面操作权限
  var operationPermission = window.App.getOperPermission();

  $(function() {
    var $editRow;
    var $logModal = $('#logsForm');
    var $logForm = $('#configForm');
    var $configId = $('[name="id"]');
    var $configUrl = $('[name="url"]');
    var $configType = $('[name="type"]');
    var logForm = null;
    // 项目地址
    var ctx = $.configs.ctx;

    // 表格初始化
    var oTable = $('.dataTable').DataTable(
      $.concatCpt('dataTable', {
        autoWidth: false,
        processing: true,
        rowId: 'id',
        ajax: ctx + '/public/data/system/logconfig/logconfigs.json',
        columns: [
          {data: 'url'},
          {data: 'type'},
          {data: 'modifyUserName'},
          {data: 'modifyDate'},
          {
            render: function() {
              // 操作权限按钮helper方法
              var ermissionStatus = function(id) {
                var result = false;
                $.each(operationPermission, function(index, value) {
                  if (value.id === id) {
                    result = true;
                    return false;
                  }
                });
                return result;
              };
              var deleteBtnTpl = '';
              var editBtnTpl = '';

              // 编辑按钮
              if (ermissionStatus('110003')) {
                editBtnTpl =
                  '<button type="button" class="btn btn-xs btn-icon btn-pure btn-default edit-row" ' +
                  'data-target="#logsForm" data-toggle="modal"><i class="icon wb-edit" aria-hidden="true"></i></button>';
              }

              // 删除按钮
              if (ermissionStatus('110005')) {
                deleteBtnTpl =
                  '<button type="button" class="btn btn-xs btn-icon btn-pure btn-default delete-row">' +
                  '<i class="icon wb-close" aria-hidden="true"></i></button>';
              }

              return '<div class="btn-group">' + editBtnTpl + deleteBtnTpl + '</div>';
            }
          }
        ]
      })
    );

    // 日志添加 | 编辑表单验证初始化
    logForm = $logForm.validate({
      rules: {
        url: {
          required: true
        },
        type: {
          required: true
        }
      },
      messages: {
        url: {
          required: '请填写URL地址'
        },
        type: {
          required: '请填写URL对应名称'
        }
      },
      submitHandler: function(form) {
        $.ajax({
          url: ctx + '/logconfig',
          type: 'POST',
          data: JSON.stringify($(form).serializeObject()),
          dataType: 'JSON',
          contentType: 'application/json',
          success: function(res) {
            if (res.success) {
              // 隐藏模态框
              $logModal.modal('hide');

              if (!_.isNull($editRow)) {
                // 编辑设置信息
                oTable
                  .row($editRow)
                  .data(res.data)
                  .draw(false);
              } else {
                // 新增日志设置
                oTable.row.add(res.data).draw(false);
              }
              toastr.success(res.msg);
            } else {
              toastr.error(res.msg);
            }
          },
          error: function() {
            toastr.error('服务器异常，请配合后端程序使用');
          }
        });
      }
    });

    // modal隐藏完成后
    $logModal.on('hidden.bs.modal', function() {
      // 清空表单数据
      $logForm.find('input').val('');
      logForm.resetForm();
    });

    // 删除指定日志
    $(document).on('click', '.delete-row', function() {
      var $item = $(this).closest('tr');
      var configId;

      // 当前行为折叠状态时以父级为主
      if ($item.hasClass('child')) {
        configId = oTable.row($item.prev()).id();
      } else {
        configId = oTable.row($item).id();
      }

      // 确定删除弹框
      window.top.layer.confirm('你确定要删除吗？', function(index) {
        $.ajax({
          url: ctx + '/logconfig/' + configId,
          type: 'DELETE',
          dataType: 'JSON',
          success: function(res) {
            if (res.success) {
              oTable
                .row($item)
                .remove()
                .draw(false);
              toastr.success(res.msg);
              window.top.layer.close(index);
            } else {
              toastr.error(res.msg);
            }
          },
          error: function() {
            toastr.error('服务器异常，请配合后端程序使用');
          }
        });
      });
    });

    // 新增日志
    $('.add-row').on('click', function() {
      $editRow = null;
      $configId.val('');
    });

    // 编辑当前日志
    $(document).on('click', '.edit-row', function() {
      var data;
      $editRow = $(this).closest('tr');
      data = oTable.row($editRow).data();

      // 添加表单编辑信息
      $configId.val(data.id);
      $configUrl.val(data.url);
      $configType.val(data.type);
    });
  });
})(document, window, jQuery);
