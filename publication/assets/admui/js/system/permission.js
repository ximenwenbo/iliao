/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  /* global toastr, template */
  /* eslint no-param-reassign: ["error", { "props": false }] */
  /* eslint consistent-return: "warn" */

  window.App.extend({
    run: function() {
      var ctx = $.configs.ctx;

      var $body = $('body');
      var $permissionLists = $('#permissionLists');

      var menuTree;
      var operatePermission = [];
      var operateType;

      // 获取当前页面操作权限
      var operationPermission = this.getOperPermission();

      // 权限列表新增|修改操作
      var permissionList = function($el) {
        var title = '新增权限';
        var val = '';
        var type = $el.data('type');

        if (type === 'edit') {
          title = '修改权限名称';
          val = $el.closest('.list-group-item').data('text');
        }

        window.top.layer.prompt(
          {
            title: title,
            value: val
          },
          function(value, index) {
            if (value) {
              // 增加部门
              $.ajax({
                url: ctx + '/permission',
                type: 'POST',
                data: JSON.stringify({id: $el.closest('.list-group-item').attr('id'), name: value}),
                dataType: 'JSON',
                contentType: 'application/json',
                success: function(res) {
                  if (res.success) {
                    toastr.success(res.msg);

                    if (type === 'edit') {
                      // 编辑权限列表项名称
                      $el
                        .closest('.list-group-item')
                        .data('text', res.data.name)
                        .addBack()
                        .find('span.list-text')
                        .text(res.data.name);
                      return;
                    }
                    // 渲染列表项 & 显示列表项操作面板
                    $body
                      .addClass('page-aside-fixed page-aside-left')
                      .find('.page>.page-content')
                      .attr('hidden', true)
                      .addBack()
                      .siblings()
                      .attr('hidden', false);

                    $permissionLists.append(
                      template('permissionItemTpl', {permissionLists: [res.data]})
                    );
                    window.top.layer.close(index);
                  } else {
                    toastr.error(res.msg);
                  }
                },
                error: function() {
                  toastr.error('服务器异常，请配合后端程序使用');
                }
              });
            }
          }
        );
      };

      // 操作权限按钮helper方法
      template.defaults.imports.operate_permission = function(id) {
        var result = false;
        $.each(operationPermission, function(index, value) {
          if (value.id === id) {
            result = true;
            return false;
          }
        });
        return result;
      };

      // 菜单树渲染
      $('#menuTree')
        .data('jstree', false)
        .empty()
        .jstree({
          checkbox: {
            keep_selected_style: false
          },
          plugins: ['checkbox', 'search'],
          core: {
            data: function(obj, callback) {
              $.ajax({
                url: ctx + '/public/data/system/menu/authmenutrees.json',
                type: 'GET',
                traditional: true,
                dataType: 'JSON',
                success: function(res) {
                  if (res.success) {
                    callback.call(this, res.data);
                  } else {
                    toastr.error(res.msg);
                  }
                },
                error: function(err) {
                  toastr.error(err);
                }
              });
            }
          }
        })
        .on('ready.jstree', function() {
          var $item = $(this);
          menuTree = $item.jstree(true);
          menuTree.open_all();

          // 给菜单树上节点的a元素绑定点击事件 --- 点击span元素获取操作权限按钮
          $item.find('a').on('click', function() {
            // 选择部门
            var $that = $(this);
            var permissionItemId = $permissionLists.find('>.list-group-item.active').attr('id');
            var menuId = $that.closest('li').attr('id');

            operateType = 'change';

            // 更新当前获取操作权限按钮选中类
            $('#menuTree')
              .find('a.active')
              .removeClass('active');

            // 当前菜单未选中时
            if (menuTree.is_selected(menuId)) {
              $('#operatePermissions').html(template('operatePermissionTpl', {type: 'deselected'}));
              return;
            }

            $that.closest('a').addClass('active');

            // 获取当前菜单操作权限
            $.ajax({
              url: ctx + '/public/data/system/operation/operations.json',
              type: 'GET',
              data: {permissionId: permissionItemId, menuId: menuId},
              dataType: 'JSON',
              success: function(res) {
                if (res.success) {
                  $('#operatePermissions').html(
                    template('operatePermissionTpl', {operatePermissions: res.data})
                  );
                } else {
                  toastr.err(res.msg);
                }
              },
              error: function(err) {
                toastr.error(err);
              }
            });
          });

          // 权限列表项渲染
          $.ajax({
            url: ctx + '/public/data/system/permission/permissions.json',
            type: 'GET',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                if (res.data.length > 0) {
                  // 有权限列表项时
                  // 渲染列表项 & 显示列表项操作面板
                  $permissionLists.html(template('permissionItemTpl', {permissionLists: res.data}));
                  $body
                    .addClass('page-aside-fixed page-aside-left')
                    .find('.page>.page-content')
                    .attr('hidden', true)
                    .addBack()
                    .siblings()
                    .attr('hidden', false);

                  // 默认选中第一项
                  $permissionLists.find('.list-group-item:first').addClass('active');

                  // 更新选中操作权限
                  operatePermission = $permissionLists
                    .find('.list-group-item:first')
                    .data('operIds');

                  // 选中已选择菜单节点
                  menuTree.select_node(res.data[0].menuIds);

                  // 默认触发第一个菜单项获取其操作权限项
                  $item.find('a:first>span').trigger('click');
                }
              } else {
                toastr.err(res.msg);
              }
            },
            error: function(err) {
              toastr.error(err);
            }
          });
        });

      // 选种当前权限项
      $permissionLists.on('click', '.list-group-item', function(e) {
        var $item = $(this);
        var $target = $(e.target).parent();

        var callback = function() {
          $item.siblings().removeClass('active');
          $item.addClass('active');

          // 默认触发第一个菜单项获取其操作权限项
          $('#menuTree')
            .find('a:first>span')
            .trigger('click');

          // 更新选中操作权限
          operatePermission = $item.data('operIds');

          // 选中当前全向项下菜单
          menuTree.deselect_all();
          menuTree.select_node($item.data('menus'));
        };

        // 当前项为编辑项 | 删除项时
        if ($target.is('.delete-item') || $target.is('.alter-item')) {
          return;
        }

        if (!$item.hasClass('active')) {
          // 当前项未选中时
          if (operateType === 'change') {
            window.top.layer.confirm(
              '当前权限项修改数据未保存，切换权限会清空数据，您确定切换吗？',
              function(index) {
                callback();
                operateType = '';
                window.top.layer.close(index);
              }
            );
          } else {
            callback();
          }
        }
      });

      // 新增权限列表项
      $('.add-permission').on('click', function() {
        permissionList($(this));
      });

      // 编辑权限列表项
      $(document).on('click', '.alter-item', function() {
        permissionList($(this));
      });

      // 删除权限列表项
      $(document).on('click', '.delete-item', function() {
        var $list = $(this).closest('.list-group-item');
        var itemId = $list.attr('id');

        window.top.layer.confirm('您确定要删除当前项吗？', function(index) {
          $.ajax({
            url: ctx + '/permission/' + itemId,
            type: 'DELETE',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                toastr.success(res.msg);

                if ($list.siblings().length === 0) {
                  // 仅剩当前项时
                  $body
                    .removeClass('page-aside-fixed page-aside-left')
                    .find('.page>.page-content')
                    .attr('hidden', false)
                    .addBack()
                    .siblings()
                    .attr('hidden', true);
                } else if ($list.hasClass('active')) {
                  // 当前项伟选中项时
                  if ($list.next().length > 0) {
                    // 有后一项
                    $list.next().trigger('click');
                  } else {
                    // 没有后一项
                    $list.prev().trigger('click');
                  }
                }

                $list.remove();
                toastr.success(res.msg);
                window.top.layer.close(index);
              } else if (res.code === '1001') {
                toastr.warning(res.msg);
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

      // 选中权限操作项
      $(document).on('change', '[name="operatePer"]', function() {
        var $item = $(this);
        var itemId = $item.attr('id');
        operateType = 'change';

        // 状态为选中时添加数组中的对应ID
        if ($item.prop('checked') && operatePermission.indexOf(itemId) === -1) {
          operatePermission.push(itemId);
        }

        if (!$item.prop('checked')) {
          // 状态为未选中时删除数组中的对应ID
          operatePermission.splice(operatePermission.indexOf(itemId), 1);
        }
      });

      // 保存权限
      $('#savePermission').on('click', function() {
        $.ajax({
          url: ctx + '/permission/assign',
          type: 'POST',
          data: JSON.stringify({
            permissionId: $permissionLists.find('>.list-group-item.active').attr('id'),
            menuIds: menuTree.get_selected().concat(menuTree.get_undetermined()),
            operationIds: operatePermission
          }),
          dataType: 'JSON',
          contentType: 'application/json',
          success: function(res) {
            if (res.success) {
              window.location.reload(true);
            } else {
              toastr.error(res.msg);
            }
          },
          error: function() {
            toastr.error('服务器异常，请配合后端程序使用');
          }
        });
      });
    }
  });

  $(function() {
    window.App.run();
  });
})(window, document, jQuery);
