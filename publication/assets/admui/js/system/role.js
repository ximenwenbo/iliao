/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global toastr, template */
  /* eslint no-param-reassign: ["error", { "props": false }] */
  /* eslint consistent-return: "warn" */

  window.App.extend({
    run: function() {
      var ctx = $.configs.ctx;
      var oTable;
      var $table = $('#roleMemberTable');
      var $roleModal = $('#roleModal');
      var $groupModal = $('#roleGroupModal');
      var $roleForm = $('#roleForm');
      var $groupForm = $('#groupForm');
      var groupForm = null;
      var roleForm = null;
      var roleModal = {
        type: 'add'
      };
      var groupModal = {
        type: 'add'
      };

      var roleMembers = 0;

      // 左侧角色树对象
      var roleTree = null;
      var memberRoleTree = null;
      var memberDepTree = null;

      // 初始化成员表格信息
      var memberTable = function() {
        // 表格复选框初始化全选方法
        $table.asSelectable($.concatCpt('selectable'));

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
              {data: 'mobile'},
              {data: 'userName'},
              {data: 'email'}
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
              var selectedRole = roleTree.get_selected(true)[0];
              var roleId = selectedRole.id;
              // 搜索参数
              var params = {
                pageIndex: data.start / data.length + 1,
                pageSize: data.length,
                data: {
                  roleId: roleId
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
                  // 更新当前角色成员人数
                  roleMembers = res.total;
                  callback(options);

                  // 显示当前选中部门名称及成员人数
                  $('#depMsg')
                    .find('>span')
                    .text(selectedRole.text + '(' + res.total + ')');
                },
                error: function(err) {
                  toastr.error(err);
                }
              });
            }
          })
        );
      };

      // 选中成员过滤函数
      var memberFilter = function(members, persons) {
        var callback = function fn(m, p) {
          $.each(p, function(i, n) {
            // 当前节点id等于选中成员id时
            if (n.li_attr.nodeId === m.id) {
              n.state = {selected: true};
            }
            // 当前节点有子节点时
            if (n.children) {
              fn(m, n.children);
            }
          });
        };

        // 遍历当前角色下成员
        $.each(members, function(i, n) {
          callback(n, persons);
        });
        return persons;
      };

      // 已选成员UI渲染
      var selectedMemberDraw = function(node) {
        var $selectedMember = $('#selectedMember');
        var data;
        var html;
        var result = false;

        // 选中渲染成员
        if (!node.personLists) {
          $selectedMember.find('a').each(function() {
            if ($(this).attr('id') === node.li_attr.nodeId) {
              result = true;
            }
          });

          if (result) {
            return;
          }

          data = {
            personLists: [
              {
                id: node.li_attr.nodeId,
                name: node.text
              }
            ]
          };
          html = template('selectedMemberTpl', data);
          $selectedMember.append(html);
        } else {
          // 初次渲染成员
          html = template('selectedMemberTpl', node);
          $selectedMember.html(html);
        }
      };

      // 选中一个树节点添加到成员列表
      var selectMember = function(node, type, status) {
        if (status === 'select_node') {
          // 当前选中项不是成员时
          if (node.type !== 'U') {
            return;
          }

          // 渲染选中成员列表
          selectedMemberDraw(node);

          if (type === 'role') {
            // 选中部门树的成员
            $('#posTreeView')
              .find('[nodeid="' + node.li_attr.nodeId + '"]')
              .each(function() {
                var $item = $(this);
                memberDepTree.get_node($item) &&
                  !memberDepTree.is_selected($item) &&
                  memberDepTree.select_node($item);
              });
          } else if (type === 'dep') {
            // 选中角色树的成员
            $('#roleTreeView')
              .find('[nodeid="' + node.li_attr.nodeId + '"]')
              .each(function() {
                var $item = $(this);
                memberRoleTree.get_node($item) &&
                  !memberRoleTree.is_selected($item) &&
                  memberRoleTree.select_node($item);
              });
          }
        } else if (status === 'deselect_node') {
          if (type === 'role') {
            // 取消部门树的成员选中
            $('#posTreeView')
              .find('[nodeid="' + node.li_attr.nodeId + '"]')
              .each(function() {
                var $item = $(this);
                memberDepTree.get_node($item) &&
                  memberDepTree.is_selected($item) &&
                  memberDepTree.deselect_node($item);
              });
          } else if (type === 'dep') {
            // 取消角色树的成员选中
            $('#roleTreeView')
              .find('[nodeid="' + node.li_attr.nodeId + '"]')
              .each(function() {
                var $item = $(this);
                memberRoleTree.get_node($item) &&
                  memberRoleTree.is_selected($item) &&
                  memberRoleTree.deselect_node($item);
              });
          }

          $('#selectedMember')
            .children('#' + node.li_attr.nodeId)
            .remove();
        }
      };

      // 改变成员树节点状态
      var changeTreeMember = function(node, type, status) {
        var callback = function(n) {
          var childNode;
          if (type === 'dep') {
            // 部门树
            childNode = memberDepTree.get_node(n);
          } else if (type === 'role') {
            // 角色树
            childNode = memberRoleTree.get_node(n);
          }

          // 当前节点没有子节点时
          if (childNode.children.length === 0) {
            selectMember(childNode, type, status);
          } else {
            changeTreeMember(childNode, type, status);
          }
        };

        if (node.children.length > 0) {
          // 当前节点有子节点时
          $.each(node.children, function(i, n) {
            callback(n);
          });
        } else if (node.children.length === 0) {
          // 当前节点没有子节点时
          selectMember(node, type, status);
        }
      };

      // 成员树渲染
      var memberTree = function(membersData) {
        // 按部门渲染树
        $('#posTreeView')
          .jstree('destroy')
          .jstree({
            types: {
              U: {
                icon: 'fa-child'
              },
              C: {
                icon: 'fa-building-o'
              }
            },
            checkbox: {
              keep_selected_style: false
            },
            search: {
              show_only_matches: true,
              show_only_matches_children: true
            },
            plugins: ['types', 'wholerow', 'search', 'checkbox'],
            core: {
              data: function(obj, callback) {
                $.ajax({
                  url: ctx + '/public/data/system/org/trees_type.json',
                  data: {structureType: 'OU'},
                  type: 'GET',
                  dataType: 'JSON',
                  success: function(res) {
                    var json;
                    if (res.success) {
                      // 将选中项包装到当前树data中
                      json = memberFilter(membersData, res.data);
                      callback.call(this, json);
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
            memberDepTree = $(this).jstree(true);
            memberDepTree.open_all();
          })
          .on('changed.jstree', function(event, obj) {
            if (obj.action !== 'select_node' && obj.action !== 'deselect_node') {
              return;
            }
            changeTreeMember(obj.node, 'dep', obj.action);
          });

        // 按角色渲染树
        $('#roleTreeView')
          .jstree('destroy')
          .jstree({
            types: {
              default: {
                icon: 'fa-folder-o'
              },
              R: {
                icon: 'fa-file-o'
              },
              U: {
                icon: 'fa-child'
              }
            },
            checkbox: {
              keep_selected_style: false
            },
            search: {
              show_only_matches: true,
              show_only_matches_children: true
            },
            plugins: ['types', 'wholerow', 'search', 'checkbox'],
            core: {
              data: function(obj, callback) {
                $.ajax({
                  url: ctx + '/public/data/system/role/trees_type.json',
                  data: {structureType: 'RU'},
                  type: 'GET',
                  traditional: true,
                  dataType: 'JSON',
                  success: function(res) {
                    var json;
                    if (res.success) {
                      // 将选中项包装到当前树data中
                      json = memberFilter(membersData, res.data);
                      callback.call(this, json);
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
            memberRoleTree = $(this).jstree(true);
            memberRoleTree.open_all();
          })
          .on('changed.jstree', function(event, obj) {
            if (obj.action !== 'select_node' && obj.action !== 'deselect_node') {
              return;
            }
            changeTreeMember(obj.node, 'role', obj.action);
          });
      };

      // 加载角色对应成员
      var memberDraw = function(selectedId) {
        $.ajax({
          url: ctx + '/public/data/system/role/users.json',
          type: 'GET',
          data: {
            roleId: selectedId
          },
          dataType: 'JSON',
          success: function(res) {
            var data = [];
            if (res.success) {
              data = res.data;
              // 绘制选中成员
              selectedMemberDraw({personLists: data});
            } else {
              toastr.error(res.msg);
            }

            // 加载成员树
            memberTree(data);
          },
          error: function(err) {
            toastr.error(err);
          }
        });
      };

      // 左侧角色树初始化
      $('#roleTree')
        .jstree({
          plugins: ['types', 'search', 'wholerow'],
          types: {
            default: {
              icon: 'fa-file-o'
            },
            D: {
              icon: 'fa-folder-o'
            },
            G: {
              icon: 'fa-folder-o'
            }
          },
          search: {
            show_only_matches: true,
            show_only_matches_children: true
          },
          core: {
            check_callback: true,
            data: function(obj, callback) {
              // 获取角色树数据
              $.ajax({
                url: ctx + '/public/data/system/role/trees.json',
                type: 'GET',
                dataType: 'JSON',
                success: function(res) {
                  var data;
                  if (res.success) {
                    data = res.data;
                    // 将默认分组下第一个角色添加选中状态
                    $.each(data, function(key, value) {
                      if (value.type === 'D' && value.children.length > 0) {
                        value.children[0].state = {selected: true};
                        return false;
                      }
                    });

                    // 渲染树
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
        })
        .on('ready.jstree', function() {
          var $item = $(this);

          // 更新角色树对象
          roleTree = $item.jstree(true);

          // 展开树节点
          roleTree.open_all();

          // 加载默认选中角色下成员
          oTable = memberTable();
        })
        .on('select_node.jstree', function(event, node) {
          var role = node.node;

          // 当前选中节点不是分组时加载角色下成员
          role.parent !== '#' && oTable.ajax.reload();
        });

      // 左侧角色树搜索
      $('#roleTreeSearch').on('keyup', function() {
        roleTree.search($(this).val());
      });

      // 角色操作下拉列表显示时筛选应显示的操作按钮
      $('#operateDropdown')
        .on('shown.bs.dropdown', function() {
          var selected = roleTree.get_selected(true)[0];
          var $dropdownMenu = $(this).find('.dropdown-menu');
          var parent = selected.parent;

          // 选中节点为分组时隐藏编辑和删除角色操作按钮
          if (parent === '#' || parent === '1001') {
            $dropdownMenu.find('>.alter-role').attr('hidden', true);
            $dropdownMenu.find('>.delete-role').attr('hidden', true);
          }

          // 选中节点为默认分组或角色时隐藏编辑和删除角色操作按钮
          if (parent !== '#' || selected.id === '1001') {
            $dropdownMenu.find('>.alter-group').attr('hidden', true);
            $dropdownMenu.find('>.delete-group').attr('hidden', true);
          }
        })
        .on('hidden.bs.dropdown', function() {
          $(this)
            .find('.dropdown-menu > .dropdown-item')
            .attr('hidden', false);
        });

      // 添加角色
      $('.add-role').on('click', function() {
        // 更新角色MODAL状态
        roleModal.type = 'add';
      });

      // 编辑角色
      $('.alter-role').on('click', function() {
        // 更新角色MODAL状态和角色信息
        $roleModal.modal('show');
        roleModal = {
          type: 'edit',
          data: roleTree.get_selected(true)[0]
        };
      });

      // 添加 || 编辑角色MODAL绑定事件
      $roleModal
        .on('shown.bs.modal', function() {
          var $item = $(this);
          var selectedNode = roleTree.get_selected(true)[0];
          var defaultGroup = selectedNode;
          var roleId = '';
          var $roleGroup = $('#roleGroup');

          if (roleModal.type === 'edit') {
            // 编辑角色
            $item
              .find('.modal-title')
              .text('编辑角色')
              .end()
              .find('[name="roleName"]')
              .val(roleModal.data.text)
              .end()
              .find('[name="id"]')
              .val(roleModal.data.id)
              .end()
              .find('[name="roleGroupId"]')
              .attr('disabled', true);
          } else {
            // 添加角色
            $item
              .find('.modal-title')
              .text('添加角色')
              .end()
              .find('[name="roleGroupId"]')
              .attr('disabled', false);
          }

          // 初始化角色分组选择框
          $roleGroup.select2(
            $.concatCpt('select2', {
              minimumResultsForSearch: 'Infinity',
              ajax: {
                url: ctx + '/rolegroup/dropdownlist',
                processResults: function(res) {
                  $.each(res.data, function(key, value) {
                    if (value.id === '1001') {
                      value.disabled = true;
                    }
                  });
                  return {
                    results: res.data
                  };
                }
              }
            })
          );

          if (selectedNode.parent !== '#') {
            // 选中项不是分组时
            defaultGroup = roleTree.get_node(selectedNode.parent);
          }

          // 编辑角色时预置选中分组 && 更新选中角色id
          if (roleModal.type === 'edit') {
            $roleGroup
              .append(new Option(defaultGroup.text, defaultGroup.id, true, true))
              .trigger('select2:select');
            roleId = roleModal.data.id;
          }

          // 权限列表项初始化
          $.ajax({
            url: ctx + '/public/data/system/permission/checkboxlist.json',
            type: 'GET',
            data: {roleId: roleId},
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                // 渲染权限项
                $('#permissionLists').html(
                  template('permissionItemTpl', {permissionLists: res.data})
                );
              } else {
                toastr.error(res.msg);
              }
            },
            error: function(err) {
              toastr.error(err);
            }
          });
        })
        .on('hide.bs.modal', function() {
          // 重置表单元素
          $(this)
            .find('input')
            .val('');
          $('#roleGroup')
            .val(null)
            .trigger('change');
          roleForm.resetForm();
        });

      // 添加 || 编辑角色FORM验证
      roleForm = $roleForm.validate({
        rules: {
          roleName: {
            required: true
          },
          roleGroupId: {
            required: true
          }
        },
        submitHandler: function(form) {
          var data = $(form).serializeObject();

          // 更新permissionIds
          data.permissionIds = [];
          $(form)
            .find('[name="permissionIds"]:checked')
            .each(function() {
              data.permissionIds.push($(this).val());
            });

          $.ajax({
            url: ctx + '/role',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
            dataType: 'JSON',
            success: function(res) {
              var options;
              if (res.success) {
                options = res.data;
                if (roleModal.type === 'edit') {
                  // 编辑角色
                  roleTree.rename_node(roleTree.get_selected(), options.roleName);
                } else {
                  // 添加角色
                  roleTree.create_node(options.roleGroupId, {
                    id: options.id,
                    text: options.roleName
                  });
                }
                toastr.success(res.msg);
                $roleModal.modal('hide');
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

      // 删除角色
      $('.delete-role').on('click', function() {
        var node = roleTree.get_selected(true)[0];

        if (roleMembers > 0) {
          toastr.warning('该角色下有成员，不能被删除！');
          return;
        }

        window.top.layer.confirm('您确定要删除选中角色吗？', function(index) {
          var nodeId = node.id;

          $.ajax({
            url: ctx + '/role/' + nodeId,
            type: 'DELETE',
            traditional: true,
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                roleTree.delete_node(node.id);
                // 选中默认分组中第一个角色
                roleTree.select_node(roleTree.get_json('1001').children[0]);
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

      // 添加角色分组
      $('.add-group').on('click', function() {
        // 更新角色分组MODAL状态
        groupModal.type = 'add';
      });

      // 编辑角色分组
      $('.alter-group').on('click', function() {
        // 更新角色分组MODAL状态和角色分组信息
        groupModal = {
          type: 'edit',
          data: roleTree.get_selected(true)[0]
        };
        $groupModal.modal('show');
      });

      // 添加 || 编辑角色分组MODAL绑定事件
      $groupModal
        .on('show.bs.modal', function() {
          var $item = $(this);

          if (groupModal.type === 'edit') {
            // 编辑分组
            $item
              .find('.modal-title')
              .text('编辑分组')
              .end()
              .find('[name="id"]')
              .val(groupModal.data.id)
              .end()
              .find('[name="groupName"]')
              .val(groupModal.data.text);
          } else {
            // 添加分组
            $item.find('.modal-title').text('添加分组');
          }
        })
        .on('hide.bs.modal', function() {
          // 添加 || 编辑角色分组FORM重置
          $(this)
            .find('input')
            .val('');
          groupForm.resetForm();
        });

      // 添加 || 编辑角色分组表单验证
      groupForm = $groupForm.validate({
        rules: {
          groupName: {
            required: true
          }
        },
        submitHandler: function(form) {
          $.ajax({
            url: ctx + '/rolegroup',
            type: 'POST',
            data: JSON.stringify($(form).serializeObject()),
            dataType: 'JSON',
            contentType: 'application/json',
            success: function(res) {
              var data;
              if (res.success) {
                data = res.data;
                if (groupModal.type === 'edit') {
                  // 编辑角色分组
                  roleTree.rename_node(roleTree.get_selected(), data.groupName);
                } else {
                  // 添加角色分组
                  roleTree.create_node('#', {
                    id: data.id,
                    text: data.groupName
                  });
                }
                toastr.success(res.msg);
                $groupModal.modal('hide');
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

      // 删除角色分组
      $('.delete-group').on('click', function() {
        var node = roleTree.get_selected(true)[0];

        if (node.children.length > 0) {
          toastr.warning('该分组下有角色，不能被删除！');
          return;
        }

        window.top.layer.confirm('您确定要删除选中分组吗？', function(index) {
          $.ajax({
            url: ctx + '/rolegroup/' + node.id,
            type: 'DELETE',
            dataType: 'JSON',
            contentType: 'application/json',
            success: function(res) {
              if (res.success) {
                roleTree.delete_node(node.id);
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

      // 添加成员
      $(document).on('click', '.add-member', function() {
        if (roleTree.get_selected(true)[0].parent === '#') {
          // 选中节点为角色分组时
          toastr.error('分组下不能添加成员，请选择分组下的角色进行添加！');
          return;
        }

        $('#inviteModal').modal('show');
      });

      // 成员MODAL显示后 --- 添加成员MODAL
      $('#inviteModal').on('show.bs.modal', function() {
        memberDraw(roleTree.get_selected()[0]);
      });

      // 按部门或角色选项卡
      $('#userTreeType').on('click', 'button', function() {
        var $item = $(this);

        $item
          .addClass('active')
          .siblings()
          .removeClass('active');
        $('#treeTypeView')
          .find('[data-target="' + $item.data('type') + '"]')
          .removeAttr('hidden')
          .siblings()
          .attr('hidden', true);
      });

      // 按角色或部门搜索成员 --- 添加成员MODAL中
      $('#userTreeSearch').on('keyup', function() {
        var $item = $(this);
        var $memberTree = null;
        var $treeType = $('#userTreeType>button.active');

        if ($treeType.data('type') === 'pos') {
          $memberTree = $('#posTreeView');
        } else {
          $memberTree = $('#roleTreeView');
        }

        $memberTree.jstree(true).search($item.val());
      });

      // 删除选中成员 --- 添加成员MODAL中
      $('#selectedMember').on('click', '.del-selected', function() {
        var $item = $(this);
        var memberId = $item.parent().attr('id');

        $item.parent().remove();

        // 取消按角色成员树和按部门角色树种的成员选中项
        $('#posTreeView')
          .find('[nodeid="' + memberId + '"]')
          .each(function() {
            memberDepTree.deselect_node($(this));
          });
        $('#roleTreeView')
          .find('[nodeid="' + memberId + '"]')
          .each(function() {
            memberRoleTree.deselect_node($(this));
          });
      });

      // 保存选中成员 --- 添加成员MODAL中
      $('#saveMember').on('click', function() {
        var users = [];
        var selectedRoleId = roleTree.get_selected()[0];

        $('#selectedMember')
          .children('a')
          .each(function() {
            users.push($(this).attr('id'));
          });

        $.ajax({
          url: ctx + '/role/assignuser',
          type: 'POST',
          dataType: 'JSON',
          data: JSON.stringify({masterId: selectedRoleId, itemIds: users}),
          contentType: 'application/json',
          success: function(res) {
            if (res.success) {
              toastr.success(res.msg);

              $('#inviteModal').modal('hide');
            } else {
              toastr.error(res.msg);
            }
          },
          error: function() {
            toastr.error('服务器异常，请配合后端程序使用');
          }
        });
      });

      // 移除选中成员
      $(document).on('click', '.delete-member', function() {
        var $tr = $table.find('tbody > tr');
        var userIds = [];
        var selectedRoleId = roleTree.get_selected()[0];

        $tr.each(function() {
          if (
            $(this)
              .find(':checkbox')
              .is(':checked')
          ) {
            userIds.push($(this).attr('id'));
          }
        });

        if (userIds.length === 0) {
          toastr.error('请选择需要移除的成员');
          return;
        }

        window.top.layer.confirm('您确定要移除所选成员吗？', function(index) {
          $.ajax({
            url: ctx + '/role/userrole/' + selectedRoleId,
            type: 'DELETE',
            data: JSON.stringify(userIds),
            dataType: 'JSON',
            contentType: 'application/json',
            success: function(res) {
              if (res.success) {
                $tr.each(function() {
                  if (
                    $(this)
                      .find(':checkbox')
                      .is(':checked')
                  ) {
                    $(this).remove();
                  }
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
    }
  });

  $(function() {
    window.App.run();
  });
})(document, window, jQuery);
