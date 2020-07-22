/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  /* global toastr */
  /* eslint consistent-return: "warn" */

  // 查询IP信息
  var queryIp = function() {
    var $ipMsg = $('table .ip-msg');

    // 初始化popover面板
    $ipMsg.webuiPopover(
      $.extend(true, {}, $.concatCpt('webuiPopover'), $ipMsg.data(), {
        trigger: 'click',
        title: '详细信息',
        type: 'async',
        content: function(res) {
          var message = '出错了，请重试！';
          if (res.success) {
            message = res.msg;
          }
          return message;
        }
      })
    );
  };
  // 获取当前页面操作权限
  var operationPermission = window.App.getOperPermission();

  $(function() {
    var oTable;
    // 项目地址
    var ctx = $.configs.ctx;
    var $blackList = $('#blackList');
    var $blackListModal = $('#modal');
    var blacklistValidator = null;

    // 表格初始化
    oTable = $('.dataTable').DataTable(
      $.concatCpt('dataTable', {
        autoWidth: false,
        processing: true,
        rowId: 'id',
        ajax: ctx + '/public/data/system/blacklist/blacklists.json',
        columns: [
          {
            render: function(data, type, row, meta) {
              return meta.row + 1;
            }
          },
          {
            data: 'ip',
            render: function(data) {
              return (
                '<a href="javascript:;" class="ip-msg" data-url="/info/ip?ip=' +
                data +
                '">' +
                data +
                '</a>'
              );
            }
          },
          {data: 'createDate'},
          {data: 'remark'},
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

              // 删除按钮
              if (ermissionStatus('700003')) {
                deleteBtnTpl =
                  '<a class="btn btn-pure btn-xs btn-default icon wb-close delete-tr" href="javascript:;"></a>';
              }

              return deleteBtnTpl;
            }
          }
        ],
        drawCallback: function() {
          this.api()
            .column(0)
            .nodes()
            .each(function(cell, i) {
              var iCell = cell;
              iCell.innerHTML = i + 1;
            });

          queryIp();
        }
      })
    );

    // 添加黑名单表单验证
    blacklistValidator = $blackList.validate({
      ignore: '.ignore',
      rules: {
        ip: {
          required: true,
          ipv4: true
        }
      },
      submitHandler: function(form) {
        var $form = $(form);
        var blacklist = {
          ip: $form.find('[name="ip"]').val(),
          remark: $form.find('[name="comment"]').val()
        };

        $.ajax({
          url: ctx + '/blacklist',
          type: 'POST',
          data: JSON.stringify(blacklist),
          dataType: 'JSON',
          contentType: 'application/json',
          success: function(res) {
            if (res.success) {
              oTable.row.add(res.data).draw(false);

              toastr.success(res.msg);
              $blackListModal.modal('hide');
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

    // 模态窗隐藏后
    $blackListModal.on('hidden.bs.modal', function() {
      $blackList.find('input, textarea').val('');
      blacklistValidator.resetForm();
    });

    // 删除当前黑名单配置
    $(document).on('click', '.delete-tr', function(e) {
      var $item = $(this).closest('tr');
      var blacklistId;

      // 当前行为折叠状态时以父级为主
      if ($item.hasClass('child')) {
        blacklistId = oTable.row($item.prev()).id();
      } else {
        blacklistId = oTable.row($item).id();
      }

      // 确定删除弹框提示
      window.top.layer.confirm('你确定要删除吗？', function(index) {
        $.ajax({
          url: ctx + '/blacklist/' + blacklistId,
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
            toastr.error('服务器异常，请稍后再试！');
          }
        });
      });
      e.preventDefault();
    });
  });
})(window, document, jQuery);
