/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  /* global toastr, template */
  /* eslint no-param-reassign: ["error", { "props": false }] */
  /* eslint consistent-return: "warn" */

  // 项目地址
  var ctx;
  var operationPermission;
  // 内容容器 & 部门树默认参数
  var $depTree0;
  var depTree;
  // 获取部门树配置参数
  var depTreeOptions = function(type) {
    var options = {
      types: {
        default: {
          icon: 'fa-building-o'
        }
      },
      search: {
        show_only_matches: true,
        show_only_matches_children: true
      },
      plugins: ['types', 'wholerow', 'search'],
      core: {
        check_callback: true,
        data: function(obj, callback) {
          $.ajax({
            url: ctx + '/public/data/system/org/trees.json',
            traditional: true,
            dataType: 'JSON',
            success: function(res) {
              var data;
              if (res.success) {
                data = res.data;
                // 包装部门树一级节点图标
                $.each(data, function(key, value) {
                  if (type === 'display' && key === 0) {
                    // 默认选中部门 - 左侧部门树
                    value.state = {
                      selected: true
                    };
                  }
                });

                // 返回树数据
                callback.call(this, data);
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
    };

    // 渲染批量修改部门树
    if (type === 'modify') {
      options.plugins.push('checkbox');
      options.checkbox = {
        three_state: false
      };
    }

    return options;
  };

  window.App.extend({
    run: function() {
      var self = this;

      // 获取当前页面操作权限
      operationPermission = this.getOperPermission();

      // 部门管理操作
      this.departmentFn();
      // 部门成员管理操作
      this.memberFn();

      // 初始化角色选择框
      $('#queryRole')
        .select2(
          $.concatCpt('select2', {
            minimumResultsForSearch: 20,
            ajax: {
              url: ctx + '/role/trees',
              data: function(params) {
                return {
                  name: params.term
                };
              },
              processResults: function(res) {
                // 包装一个所有角色值
                res.data.unshift({id: '', text: '所有角色'});
                return {
                  results: res.data
                };
              }
            }
          })
        )
        .on('change', function() {
          // 角色筛选成员
          self.table.ajax.reload();
        });

      // 状态筛选成员
      $('#queryStatus').on('change', function() {
        self.table.ajax.reload();
      });

      // 搜索条件筛选成员
      $('#searchMember').on('change', function() {
        self.table.ajax.reload();
      });
    },
    departmentFn: function() {
      var self = this;
      var departmentData = {
        type: ''
      };

      // 左侧公司部门树渲染
      $depTree0
        .jstree(depTreeOptions('display'))
        .on('ready.jstree', function() {
          depTree = $(this).jstree(true);
          depTree.open_all();
          self.table = self.memberTable($('#memberTable'));
        })
        .on('select_node.jstree', function() {
          // 重载成员信息
          self.table.ajax.reload();
          // 更新全选按钮状态
          $('#memberTable')
            .find('.selectable-all')
            .prop('checked', false);
        });

      // 左侧公司部门树搜索
      $('#departmentSearch').on('keyup', function() {
        depTree.search($(this).val());
      });

      // 添加部门
      $('.add-dep').on('click', function() {
        departmentData.type = '';
      });

      // 添加子部门
      $('.add-child-dep').on('click', function() {
        var depInfo = self.getDeInfo();

        $.extend(departmentData, {
          type: 'child',
          depName: depInfo.name,
          depId: depInfo.id
        });
      });

      // 添加部门modal显示前
      $('#departmentModal')
        .on('show.bs.modal', function() {
          $(this)
            .find('.modal-content')
            .html(template('departmentTpl', departmentData));
        })
        .on('shown.bs.modal', function() {
          if (departmentData.type !== 'child') {
            // 新增部门时需要选择上级部门
            $(this)
              .on('shown.bs.dropdown', '.choose-dep', function() {
                // 部门树渲染
                var $depTree = $(this).find('.choose-dep-tree');

                !$depTree.jstree(true) &&
                  $depTree.jstree(depTreeOptions()).on('ready.jstree', function() {
                    $(this)
                      .jstree(true)
                      .open_all();
                    // 初始化滚动条
                    $depTree.parent().mCustomScrollbar($.concatCpt('mCustomScrollbar'));
                  });
              })
              .on('click', '.choose-dep>.dropdown-menu', function(event) {
                // 选择部门
                var $target = $(event.target);
                var $chooseDep = $('.choose-dep');
                var $department = $chooseDep.find('>[data-toggle="dropdown"]');
                var depName;
                var depId;

                if ($target.is('a.jstree-clicked')) {
                  // 点击目标为jstree节点时确认该项为选中项
                  depName = $target.text();
                  depId = $target.parent('li').attr('id');

                  $department.dropdown('toggle').val(depName);
                  $chooseDep.find('>[name="parentId"]').val(depId);
                  $chooseDep
                    .closest('form')
                    .validate()
                    .element($department);
                }

                event.stopPropagation();
              });
          }

          // 添加部门表单提交
          $('#addDepartment').validate({
            rules: {
              orgName: {
                required: true
              }
            },
            submitHandler: function(form) {
              var data = $(form).serializeObject();

              // 删除无用的字段
              delete data.parentName;

              $.ajax({
                url: ctx + '/org',
                type: 'POST',
                dataType: 'JSON',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function(res) {
                  var options;
                  if (res.success) {
                    options = res.data;
                    toastr.success(res.msg);
                    $('#departmentModal').modal('hide');

                    // 上级部门id为'0'时，在jstree中节点id应为'#'
                    if (options.parentId === '0') {
                      options.parentId = '#';
                    }

                    // 左侧公司部门创建新部门
                    depTree.create_node(options.parentId, {id: options.id, text: options.orgName});
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
        });

      // 修改部门名称
      $('.alter-dep').on('click', function() {
        var depInfo = self.getDeInfo();
        var depId = depInfo.id;

        window.top.layer.prompt(
          {
            title: '修改部门名称',
            value: depInfo.name
          },
          function(value, index, e) {
            var params;

            if (value) {
              // 修改部门名称
              params = {
                id: depId,
                orgName: value
              };

              $.ajax({
                url: ctx + '/org',
                type: 'POST',
                dataType: 'JSON',
                data: JSON.stringify(params),
                contentType: 'application/json',
                success: function(res) {
                  if (res.success) {
                    // 修改名称
                    depTree.rename_node(depTree.get_selected(), res.data.orgName);
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
            }
            e.preventDefault();
          }
        );
      });

      // 删除选中部门
      $('.del-dep').on('click', function() {
        var depId = self.getDeInfo().id;
        var selectedNode = depTree.get_selected(true)[0];

        if (self.depMembers > 0) {
          toastr.warning('该部门有成员，不能被删除！');
          return;
        }

        if (selectedNode.children.length > 0) {
          toastr.warning('该部门下有子部门，不能被删除！');
          return;
        }

        selectedNode = depTree.get_next_dom(depId, true);

        if (!selectedNode) {
          // 没有下一个节点时，选择同级上一个节点
          selectedNode = depTree.get_prev_dom(depId, true);
        }

        if (!selectedNode) {
          // 没有上一个节点时，选择父节点
          selectedNode = depTree.get_parent(depId);
        }

        if (selectedNode === '#') {
          // 父节点为jstree默认'#'时不再选择，不能删除唯一节点
          toastr.warning('不能删除唯一部门！');
          return;
        }

        window.top.layer.confirm('您确定要删除该部门吗？', function(index) {
          $.ajax({
            url: ctx + '/org/' + depId,
            type: 'DELETE',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                depTree.delete_node(depId);
                depTree.select_node(selectedNode);
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
    },
    memberFn: function() {
      var self = this;
      var memberData = {
        type: 'edit'
      };
      // 批量修改部门树元素
      var $partyTree = $('#multipleEditDep');

      // 新增成员
      $('#addmember').on('click', function() {
        memberData = {
          type: 'create'
        };
      });

      // 编辑当前成员信息
      $(document).on('click', '.edit-member', function() {
        var memberId = $(this)
          .parent()
          .parent()
          .data('id');

        $.ajax({
          url: ctx + '/public/data/system/user/' + memberId + '.json',
          type: 'GET',
          dataType: 'JSON',
          success: function(res) {
            if (res.success) {
              // 包装成员信息
              $.extend(memberData, res.data, {
                type: 'edit'
              });
              $('#memberModal').modal('show');
            } else {
              toastr.error(res.msg);
            }
          },
          error: function(err) {
            toastr.error(err);
          }
        });
      });

      // 成员管理MODAL显示
      $('#memberModal')
        .on('show.bs.modal', function() {
          // 渲染成员信息modal
          $(this)
            .find('.modal-dialog')
            .html(template('memberTpl', memberData));
        })
        .on('shown.bs.modal', function() {
          // 成员管理modal显示完成
          var $depTree;
          // 成员form验证规则
          var validateRules = {
            userName: {
              required: true
            },
            password: {
              required: true,
              minlength: 6,
              maxlength: 30
            },
            name: {
              required: true
            },
            userType: {
              required: true
            },
            position: {
              required: true
            },
            mobile: {
              required: true,
              mobileCN: true
            },
            email: {
              email: true
            },
            telephone: {
              phoneCN: true
            },
            phoneExt: {
              number: true
            },
            jobNum: {
              required: true
            },
            remark: {
              maxlength: 500
            },
            department: {
              required: true
            },
            role: {
              required: true
            }
          };
          var modalType = memberData.type;
          var value = '已选择所属部门';

          if (modalType === 'edit') {
            // 编辑时密码为非必填项
            validateRules.password.required = false;
          }

          // 添加成员表单提交
          $('#memberForm').validate({
            ignore: '',
            rules: validateRules,
            invalidHandler: function(event, validator) {
              var $item = $(validator.errorList[0].element);
              var $baseInfo = $('#baseInfo');
              var $deptJob = $('#deptJob');

              // invalid项在成员信息中且当前未选中显示成员信息面板时
              if ($baseInfo.find($item).length > 0 && !$baseInfo.hasClass('active')) {
                $('a[href="#baseInfo"]').tab('show');
              }

              // invalid项在部门及角色中且当前未选中显示部门及角色面板时
              if ($deptJob.find($item).length > 0 && !$deptJob.hasClass('active')) {
                $('a[href="#deptJob"]').tab('show');
              }
            },
            submitHandler: function(form) {
              var data = $(form).serializeObject();

              // 删除无用字段
              delete data.role;
              delete data.department;

              // 转字符串为JSON对象
              data.roleIds = JSON.parse(data.roleIds);
              data.orgIds = JSON.parse(data.orgIds);

              $.ajax({
                url: ctx + '/user',
                type: 'POST',
                dataType: 'JSON',
                data: JSON.stringify(data),
                contentType: 'application/json',
                success: function(res) {
                  if (res.success) {
                    toastr.success(res.msg);
                    self.table.ajax.reload();
                    $('#memberModal').modal('hide');
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

          // 选择所属部门下拉列表
          $('#deptJob')
            .on('shown.bs.dropdown', '.choose-dep', function() {
              // 初始化部门树
              $depTree = $(this).find('.choose-dep-tree');

              !$depTree.jstree(true) &&
                $depTree.jstree(depTreeOptions()).on('ready.jstree', function() {
                  $(this)
                    .jstree(true)
                    .open_all();
                  // 初始化滚动条
                  $(this)
                    .parent()
                    .mCustomScrollbar($.concatCpt('mCustomScrollbar'));

                  // 编辑成员时显示预设置选中部门
                  if (modalType === 'edit' && memberData.orgs.length > 0) {
                    $.each(memberData.orgs, function(key, val) {
                      $depTree.find('#' + val.orgId + '>a').addClass('jstree-selected');
                    });
                  }
                });
            })
            .on('hidden.bs.dropdown', '.choose-dep', function(e) {
              var $item = $(this);
              var $department = $item.find('>[data-toggle="dropdown"]');
              var selectedIds = [];
              var depName;

              $depTree.find('a.jstree-selected').each(function() {
                selectedIds.push(
                  $(this)
                    .parent()
                    .attr('id')
                );
              });

              if (selectedIds.length > 1) {
                // 选择多个部门时
                depName = '已选择所属部门';
              } else if (selectedIds.length === 1) {
                // 仅选择一个部门时
                depName = $depTree.find('#' + selectedIds[0] + '>a').text();
              }

              // 更新选择部门
              $department.val(depName);
              $item.find('#orgIds').val(JSON.stringify(selectedIds));
              // 验证当前选择部门元素
              $item
                .closest('form')
                .validate()
                .element($department);

              e.stopPropagation();
            })
            .on('click', '.choose-dep>.dropdown-menu', function(event) {
              // 选择部门
              var $target = $(event.target);

              if (!$target.is('a.jstree-clicked')) {
                // 点击目标不是jstree选中项
                return;
              }

              if (!$target.hasClass('jstree-selected')) {
                // 当前节点有jstree-selected类时
                $target.addClass('jstree-selected');
              } else {
                // 当前节点有jstree-selected类时
                $target.removeClass('jstree-selected');
              }

              event.stopPropagation();
            });

          // 初始化角色选择框
          $('#role')
            .select2(
              $.concatCpt('select2', {
                multiple: true,
                closeOnSelect: false,
                ajax: {
                  url: ctx + '/role/trees',
                  processResults: function(res) {
                    return {
                      results: res.data
                    };
                  }
                }
              })
            )
            .on('select2:select select2:unselect', function() {
              var $item = $(this);
              // 验证当前选择部门元素
              $item.siblings('[name="roleIds"]').val(JSON.stringify($item.val()));
              $item
                .closest('form')
                .validate()
                .element($item);
            });

          // 编辑成员时预设置选中角色
          if (modalType === 'edit' && memberData.roles.length > 0) {
            $.each(memberData.roles, function(key, val) {
              $('#role')
                .append(new Option(val.roleName, val.id, true, true))
                .trigger('select2:select');
            });
          }

          // 编辑成员时显示预设置选中部门
          if (modalType === 'edit' && memberData.orgs.length > 0) {
            if (memberData.orgs.length === 1) {
              value = memberData.orgs[0].orgName;
            }

            $('#department').val(value);
          }
        });

      // 删除当前成员
      $(document).on('click', '.del-member', function() {
        var $item = $(this);
        var itemId = $item
          .parent()
          .parent()
          .data('id');

        window.top.layer.confirm(
          '你确定要删除该成员吗？该操作将彻底销毁该成员，请谨慎操作！',
          function(index) {
            $.ajax({
              url: ctx + '/user/U',
              type: 'DELETE',
              data: JSON.stringify({masterId: depTree.get_selected()[0], itemIds: [itemId]}),
              contentType: 'application/json',
              dataType: 'JSON',
              success: function(res) {
                if (res.success) {
                  self.table
                    .row('#' + itemId)
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
          }
        );
      });

      // 移除当前成员
      $(document).on('click', '.remove-member', function() {
        var $item = $(this);
        var itemId = $item
          .parent()
          .parent()
          .data('id');

        window.top.layer.confirm('你确定要从当前部门中移除该成员吗？', function(index) {
          $.ajax({
            url: ctx + '/user/R',
            type: 'DELETE',
            data: JSON.stringify({masterId: depTree.get_selected()[0], itemIds: [itemId]}),
            contentType: 'application/json',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                self.table
                  .row('#' + itemId)
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

      // 禁用当前成员
      $(document).on('click', '.disable-member', function() {
        var $item = $(this);
        var itemId = $item
          .parent()
          .parent()
          .data('id');

        window.top.layer.confirm('你确定禁用该成员吗？', function(index) {
          $.ajax({
            url: ctx + '/user/1',
            type: 'POST',
            data: JSON.stringify([itemId]),
            contentType: 'application/json',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                // 更新当前按钮和当前行状态
                $item
                  .closest('table')
                  .find('#' + itemId)
                  .addClass('table-disabled')
                  .find(':checkbox')
                  .prop({
                    disabled: true,
                    checked: false
                  });
                $item
                  .removeClass('disable-member')
                  .addClass('enable-member')
                  .text('启用成员');
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

      // 启用当前成员
      $(document).on('click', '.enable-member', function() {
        var $item = $(this);
        var itemId = $item
          .parent()
          .parent()
          .data('id');

        window.top.layer.confirm('你确定启用该成员吗？', function(index) {
          $.ajax({
            url: ctx + '/user/0',
            type: 'POST',
            data: JSON.stringify([itemId]),
            contentType: 'application/json',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                // 更新当前按钮和当前行状态
                $item
                  .closest('table')
                  .find('#' + itemId)
                  .removeClass('table-disabled')
                  .find(':checkbox')
                  .prop('disabled', false);
                $item
                  .removeClass('enable-member')
                  .addClass('disable-member')
                  .text('禁用成员');
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

      // 修改选中成员部门 --- 批量操作
      $(document).on('click', '.modify-selected-item', function() {
        // 没有选中成员时
        if ($('#memberTable').asSelectable('getSelected').length === 0) {
          toastr.warning('请先选择成员！');
          return;
        }

        $('#deptPicker').modal('show');
      });

      // 修改选中成员部门MODAL显示
      $('#deptPicker')
        .on('shown.bs.modal', function() {
          // 左侧公司部门树渲染
          if ($partyTree.jstree(true)) {
            return;
          }

          $partyTree
            .jstree('destroy')
            .jstree(depTreeOptions('modify'))
            .on('ready.jstree', function() {
              $(this)
                .jstree(true)
                .open_all();
            })
            .on('changed.jstree', function(event, obj) {
              // 添加选择部门节点
              var $selectedDeps = $('.selected_dep');
              var node = obj.node;

              if (obj.action !== 'select_node' && obj.action !== 'deselect_node') {
                return;
              }

              if (obj.action === 'select_node') {
                $selectedDeps.append(
                  '<a class="list-group-item" href="javascript:;" title="' +
                    node.text +
                    '" data-id="' +
                    node.id +
                    '">' +
                    '<i class="icon wb-folder"></i><span>' +
                    node.text +
                    '</span><i class="icon wb-close delete-item"></i></a>'
                );
              } else {
                // 当前节点有jstree-selected类时
                $selectedDeps.find('[data-id="' + node.id + '"]').remove();
              }
            });
        })
        .on('hidden.bs.modal', function() {
          // 重置已选中部门
          $partyTree.jstree(true).redraw(true);
          $('.selected_dep').html('');
        })
        .on('click', '.change-deps', function() {
          // 确认改变成员部门

          // 获取选中部门ID
          var selectedDeps = $partyTree.jstree(true).get_selected();
          // 获取选中成员ID
          var memberIds = [];

          // 获取选中成员Id集合
          $('#memberTable')
            .asSelectable('getSelected')
            .each(function() {
              memberIds.push(
                $(this)
                  .closest('tr')
                  .attr('id')
              );
            });

          if (selectedDeps.length === 0) {
            toastr.warning('您需要选择一个部门！');
            return;
          }

          // 更新选中成员所在部门
          $.ajax({
            url: ctx + '/user/updateuserdept',
            type: 'POST',
            data: JSON.stringify({masterIds: selectedDeps, itemIds: memberIds}),
            dataType: 'json',
            contentType: 'application/json',
            success: function(res) {
              if (res.success) {
                toastr.success(res.msg);
                self.table.ajax.reload();
                $('#deptPicker').modal('hide');
              } else {
                toastr.error(res.msg);
              }
            },
            error: function() {
              toastr.error('服务器异常，请配合后端程序使用');
            }
          });
        })
        .on('click', '.delete-item', function() {
          // 删除选中部门
          var $item = $(this).closest('a');

          $partyTree.jstree(true).deselect_node($item.data('id'));
          $item.remove();
        })
        .on('keyup', '.search-dep', function() {
          $partyTree.jstree(true).search($(this).val());
        });

      // 禁用选中成员 --- 批量操作
      $(document).on('click', '.disable-selected-item', function() {
        var $selectedItems = $('#memberTable').asSelectable('getSelected');
        var selectedItems = self.selectedMember($selectedItems);

        // 没有选中成员时
        if (!selectedItems) {
          toastr.warning('请先选择成员！');
          return;
        }

        window.top.layer.confirm('您确定要禁用所有选中成员吗？', function(index) {
          $.ajax({
            url: ctx + '/user/1',
            type: 'POST',
            data: JSON.stringify(selectedItems),
            traditional: true,
            contentType: 'application/json',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                $selectedItems.each(function() {
                  var $item = $(this);
                  var $tr = $item.closest('tr');

                  // 更新当前按钮和当前行状态
                  $item.prop({
                    disabled: true,
                    checked: false
                  });
                  $tr.addClass('table-disabled');
                  $tr
                    .find('.disable-member')
                    .removeClass('disable-member')
                    .addClass('enable-member')
                    .text('启用成员');
                });
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
    },
    selectedMember: function($lists) {
      // 获取选中成员
      var memberIds = [];

      // 没有选中成员时
      if ($lists.length === 0) {
        return false;
      }

      // 获取选中成员Id集合
      $lists.each(function() {
        memberIds.push(
          $(this)
            .closest('tr')
            .attr('id')
        );
      });

      return memberIds;
    },
    memberTable: function($table) {
      var self = this;

      // 表格复选框初始化全选方法
      $table.asSelectable($.concatCpt('selectable'));

      // 初始化成员表格信息
      return $table.DataTable(
        $.concatCpt('dataTable', {
          dom: '<"row"<"col-lg-12"tr>><"row table-toolbar"<"col-sm-7"l><"col-sm-5"p>>',
          autoWidth: true,
          processing: true,
          serverSide: true,
          searching: true,
          pagingType: 'simple_numbers',
          rowId: 'id',
          columns: [
            {
              render: function() {
                return (
                  '<div class="checkbox-custom checkbox-primary user-select-item"><input type="checkbox" ' +
                  'class="user-checkbox selectable-item"><label></label></div>'
                );
              }
            },
            {data: 'name'},
            {data: 'sexName'},
            {
              data: 'orgNames',
              render: function(data) {
                return '<span class="inline-block text-truncate w-200">' + data + '</span>';
              }
            },
            {
              data: 'roleNames',
              render: function(data) {
                return '<span class="inline-block text-truncate w-200">' + data + '</span>';
              }
            },
            {data: 'mobile'},
            {data: 'userName'},
            {data: 'email'},
            {
              render: function(data, type, row) {
                var statusBtn = {
                  selector: 'disable-member'
                };
                var permission;
                var html = '';
                // 操作权限按钮helper方法
                var ermissionStatus = function(id) {
                  var result = false;
                  $.each(operationPermission, function(index, value) {
                    if (value.id === id) {
                      permission = value;
                      result = true;
                      return false;
                    }
                  });
                  return result;
                };

                var editBtnTpl = '';
                var disableBtnTpl = '';
                var removeBtnTpl = '';
                var deleteBtnTpl = '';

                // 编辑成员按钮
                if (ermissionStatus('500019')) {
                  editBtnTpl =
                    '<a href="javascript:;" class="dropdown-item edit-member">' +
                    permission.name +
                    '</a>';
                }

                // 禁用成员按钮
                if (ermissionStatus('500011')) {
                  statusBtn.text = permission.name;

                  // 当前用户状态为已禁用时
                  if (row.status === 1) {
                    statusBtn.selector = 'enable-member';
                    statusBtn.text = '启用成员';
                  }
                  disableBtnTpl =
                    '<a href="javascript:;" class="dropdown-item ' +
                    statusBtn.selector +
                    '">' +
                    statusBtn.text +
                    '</a>';
                }

                // 移除成员按钮
                if (ermissionStatus('500017')) {
                  removeBtnTpl =
                    '<a href="javascript:;" class="dropdown-item remove-member">' +
                    permission.name +
                    '</a>';
                }

                // 删除成员按钮
                if (ermissionStatus('500021')) {
                  deleteBtnTpl =
                    '<a href="javascript:;" class="dropdown-item del-member">' +
                    permission.name +
                    '</a>';
                }

                if (typeof permission !== 'undefined') {
                  html =
                    '<div class="btn-group user-edit" data-id="' +
                    row.id +
                    '"><button type="button" class="btn ' +
                    'btn-outline btn-default dropdown-toggle btn-xs" id="exampleSizingDropdown4" data-toggle="dropdown" ' +
                    'aria-expanded="true">编辑 </button><div class="dropdown-menu dropdown-menu-right"> ' +
                    editBtnTpl +
                    disableBtnTpl +
                    removeBtnTpl +
                    deleteBtnTpl +
                    '</div></div>';
                }

                return html;
              }
            }
          ],
          rowCallback: function(row, data) {
            if (data.status === 1) {
              $(row)
                .addClass('table-disabled')
                .find('input:checkbox')
                .prop('disabled', true);
            }
          },
          ajax: function(data, callback) {
            var depInfo = self.getDeInfo();
            var depId = depInfo.id;
            // 搜索参数
            var params = {
              pageIndex: data.start / data.length + 1,
              pageSize: data.length,
              data: {
                orgId: depId,
                roleId: $('#queryRole').val(),
                status: $('#queryStatus').val(),
                name: $('#searchMember').val()
              }
            };

            $.ajax({
              url: ctx + '/public/data/system/user/users.json',
              type: 'GET',
              dataType: 'JSON',
              data: params,
              success: function(res) {
                // 包装response成员信息
                var options = {
                  recordsTotal: res.total,
                  recordsFiltered: res.total,
                  data: res.pageList
                };
                self.depMembers = res.total;
                callback(options);

                // 显示当前选中部门名称及成员人数
                $('#depMsg')
                  .find('>span')
                  .text(depInfo.name + '(' + res.total + ')');
              },
              error: function(err) {
                toastr.error(err);
              }
            });
          }
        })
      );
    },
    getDeInfo: function() {
      // 获取选中部门信息
      var node = depTree.get_selected(true)[0];
      return {
        id: node.id,
        name: node.text
      };
    }
  });

  $(function() {
    // 项目地址
    ctx = $.configs.ctx;
    // 内容容器 & 部门树默认参数
    $depTree0 = $('#departmentJstree');

    window.App.run();
  });
})(document, window, jQuery);
