/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global _, template, toastr, sortable, Breakpoints */
  /* eslint consistent-return: "warn" */

  var $subMenTree;
  var $topMenus;
  var $topMenuModal;
  var $pageMain;
  var ctx = $.configs.ctx;
  var operationPermission;

  window.App.extend({
    run: function() {
      var self = this;
      var newMenu = 0;

      self.menuObj = [];

      // 获取当前页面操作权限
      operationPermission = this.getOperPermission();

      this.topMenusInit();

      // 保存选中顶部菜单下子菜单修改
      $('#saveChlidMenu').on('click', function() {
        self.saveMenu();
      });

      // 选中顶部菜单
      $(document).on('click', '.top-menu .list-group-item', function() {
        var $item = $(this);
        // 切换选中菜单
        var checkoutMenu = function() {
          $item.siblings('[data-children]').removeClass('active');
          $item.addClass('active');

          // 重新渲染子菜单
          self.submenuRender($item.data('children'));
        };

        // 当前菜单已选中时
        if ($item.hasClass('active')) {
          return;
        }

        if (self.subType) {
          window.top.layer.confirm(
            '您还未对修改过的信息进行保存，请先进行保存！',
            {
              btn: ['保存', '不保存']
            },
            function(index) {
              self.saveMenu();
              window.top.layer.close(index);
            },
            function(index) {
              self.subType = undefined;
              self.menuObj = self.getMenuData($item);
              checkoutMenu();
              window.top.layer.close(index);
            }
          );
          newMenu = 0;
        } else {
          self.subType = undefined;
          self.menuObj = self.getMenuData($item);
          checkoutMenu();
        }
      });

      // 小屏幕下点击页面主体部分收起顶部菜单
      $pageMain.on('click', function() {
        Breakpoints.current().name === 'xs' && $('.page-aside').removeClass('open');
      });

      // 添加 | 编辑顶部菜单
      $(document).on('click', '[data-tag="list-editable"], #addMenuToggle', function(e) {
        var $item = $(this);
        var data = self.getMenuData($item.parents('[data-children]'));
        var html;

        // 当前未编辑操作时包装一个操作类型数据
        if ($item.is('[data-tag="list-editable"]')) {
          $.extend(data, {type: 'edit'});
        } else {
          // 新增顶部菜单时添加一个默认图标
          data.icon = 'fa-bars';
        }

        // 根据菜单数据渲染modal内容
        html = template('selectOption', data);
        $topMenuModal.find('.modal-content').html(html);

        // 显示顶部菜单modal
        $topMenuModal.modal('show');

        // 初始化图标选择器 & 选择图标
        $('.icp-dd')
          .iconpicker($.concatCpt('iconpickerWb'))
          .on('iconpickerSelected', function(event) {
            $(this)
              .prev('span')
              .children('i[data-icon]')
              .data('icon', event.iconpickerValue)
              .attr('data-icon', event.iconpickerValue);
          });

        self.topMenuValidate(data.id);
        e.stopPropagation();
      });

      // 删除顶部菜单
      $(document).on('click', '[data-tag="list-delete"]', function(e) {
        var $item = $(this).parents('div[data-children]');
        var itemId = $item.data('id');

        // 仅剩一个菜单时提醒
        if ($item.siblings('[data-children]').length === 0) {
          window.top.layer.alert('您必须保留一个菜单！');
          return;
        }

        // 确认删除提示
        window.top.layer.confirm('您确定要删除该菜单吗？', function(index) {
          $.ajax({
            url: ctx + '/menu/' + itemId,
            type: 'DELETE',
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                if ($item.is('.active')) {
                  // 当前菜单为选中项
                  if ($item.next().length > 0) {
                    // 当前菜单有后一项
                    $item.next().addClass('active');
                  } else {
                    // 当前菜单有前一项
                    $item.prev().addClass('active');
                  }

                  // 渲染选中菜单的子菜单
                  self.submenuRender($item.siblings('.active').data('children'));
                }

                $item.remove();
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
        e.stopPropagation();
      });

      // 选中子菜单
      $(document).on('click', '#menuTree .dd-content:not(.active)', function() {
        var $item = $(this).parent('li');

        // 更新选中项
        $subMenTree.find('.active').removeClass('active');
        $item.find('>.dd-content').addClass('active');

        // 渲染当前子菜单编辑面板
        self.submenuEditPanel(self.getMenuData($item));
      });

      $(document).on('focusout', 'input[name="submenu_name"]', function() {
        var thisVal = $(this).val();
        var icon;
        var $item = $subMenTree.find('.active').parent('li');
        var storeVal = $item.data('name');

        if (thisVal === storeVal) {
          return;
        } else if (thisVal === '') {
          thisVal = storeVal;
        } else {
          self.subType = 'change';
          $item.data('name', thisVal).attr('data-name', thisVal);
          if ($item.data('icon') !== '') {
            icon = $item.data('icon');
            $item
              .children('div.dd-content')
              .children('span.menu-name')
              .html('<i class="menu-icon ' + icon + '"></i> ' + thisVal);
          } else {
            $item
              .children('div.dd-content')
              .children('span.menu-name')
              .text(thisVal);
          }

          $('.menu-box h4').text(thisVal);

          if ($item.data('type') !== 'add') {
            $item.data('type', 'update').attr('data-type', 'update');
          }
        }

        $(this).val(thisVal);
      });

      $(document).on('focusout', 'input[name="submenu_url"]', function() {
        var thisVal = $(this).val();
        var $item = $subMenTree.find('.active').parent('li');
        var storeVal = $item.data('url');

        if (thisVal === '') {
          thisVal = storeVal;
        } else {
          self.subType = 'change';
          $item.data('url', thisVal).attr('data-url', thisVal);

          if ($item.data('type') !== 'add') {
            $item.data('type', 'update').attr('data-type', 'update');
          }
        }

        $(this).val(thisVal);
      });

      // 删除子菜单
      $(document).on('click', '.delete-submenu', function() {
        var data;
        var flag = false;
        var $item = $subMenTree.find('.active').parent('li');
        var itemId = $item.data('id');
        // 删除操作
        var deleteCallback = function() {
          if ($item.next().length > 0) {
            // 同级有后一项菜单时
            $item
              .next()
              .children('.dd-content')
              .addClass('active');
          } else if ($item.prev().length > 0) {
            // 同级有前一项菜单时
            $item
              .prev()
              .children('.dd-content')
              .addClass('active');
          } else {
            // 同级没有兄弟菜单时
            // 选中父级菜单
            $item
              .parent('ol')
              .closest('li')
              .children('.dd-content')
              .addClass('active');
            // 删除当前菜单包裹容器
            $item.parent('ol').remove();
            flag = true; // 标记同级没有兄弟菜单
          }

          // 删除菜单
          $item.remove();
          toastr.success('菜单删除成功！');

          // 获取当前子菜单选中项数据
          data = self.getMenuData($subMenTree.find('.active').parent('li'));

          if (flag) {
            // 同级没有兄弟菜单时设置新选中的腹肌菜单url为'#'
            data.url = '#';
          }

          if ($subMenTree.find('li').length === 0) {
            // 没有子菜单时
            // 更新选中顶部菜单children值为null
            $topMenus
              .find('>.list-group-item.active')
              .data('children', 'null')
              .attr('data-children', 'null');

            // 更新子菜单操作面板
            self.updateSubmenuPanel(null);
            return;
          }

          // 渲染选中菜单项编辑面板
          self.submenuEditPanel(data);
        };

        // 菜单删除确认提示
        window.top.layer.confirm('您确定要删除该菜单吗？', function(index) {
          if (itemId) {
            // 菜单项有id时
            $.ajax({
              url: ctx + '/menu/' + itemId,
              type: 'DELETE',
              dataType: 'JSON',
              success: function(res) {
                if (res.success) {
                  deleteCallback();
                  window.top.layer.close(index);
                } else {
                  toastr.error(res.msg);
                }
              },
              error: function() {
                toastr.error('服务器异常，请配合后端程序使用');
              }
            });
          } else if ($subMenTree.find('li').length > 1) {
            // 新添加的菜单项且菜单数量大于1时
            deleteCallback();
          } else {
            // 新添加的菜单项且菜单数量等于1时
            self.subType = '';
            // 删除当前项
            $item.remove();
            // 更新子菜单操作面板
            self.updateSubmenuPanel(null);
          }
        });
      });

      $(document).on('click', '.add-submenu', function() {
        var $item = $(this);
        var menuData;
        var $liActive;
        var dataRrank;
        var flag;

        newMenu += 1;
        self.subType = 'change';

        if ($pageMain.hasClass('vertical-align')) {
          menuData = [
            {
              name: '自定义菜单一',
              type: 'add',
              url: '#'
            }
          ];
          self.submenuRender(menuData);
          return;
        }

        $liActive = $subMenTree.find('.active').parent('li');
        dataRrank = $liActive.data('rank');
        flag = self.insertChildMenu($item, $liActive, dataRrank, newMenu);

        if (typeof flag === 'undefined') {
          menuData = self.getMenuData($subMenTree.find('.active').parent('li'));

          self.submenuEditPanel(menuData);
        }
      });
    },
    updateTopMenuOrder: function($item) {
      var order = [];
      $item.children('.list-group-item').each(function() {
        order.push({
          id: $(this).data('id'),
          orderNo: $(this).index()
        });
      });

      $.ajax({
        url: ctx + '/admin/MenuManage/updatetoporder',
        type: 'POST',
        data: JSON.stringify(order),
        dataType: 'JSON',
        contentType: 'application/json',
        success: function(res) {
          if (res.success) {
            toastr.success('重新登录可更新顶部菜单！');
          } else {
            toastr.error(res.msg);
          }
        },
        error: function() {
          toastr.error('服务器异常，请配合后端程序使用');
        }
      });
    },
    saveMenu: function() {
      var self = this;
      this.menuObj.children = $subMenTree.nestable('serialize');

      $.ajax({
        url: ctx + '/menu',
        type: 'POST',
        data: JSON.stringify(this.menuObj),
        dataType: 'JSON',
        contentType: 'application/json;',
        success: function(res) {
          if (res.success) {
            self.subType = undefined;
            toastr.info('当前菜单保存成功，重新登录可更新菜单数据！');
            window.location.reload(true);
          } else {
            toastr.error(res.msg);
          }
        },
        error: function() {
          toastr.error('服务器异常，请配合后端程序使用');
        }
      });
    },
    getMenuData: function($el) {
      return {
        id: $el.data('id'),
        url: $el.data('url'),
        name: $el.data('name') || '自定义菜单',
        icon: $el.data('icon'),
        layer: $el.data('layer')
      };
    },
    updateTopMenu: function(data, itemId) {
      var $navMenu;
      var opts = data;

      if (itemId) {
        // 编辑菜单操作
        $navMenu = $topMenus.find('>[data-id="' + itemId + '"]');
        // 更新菜单信息
        $navMenu
          .data({
            name: opts.name,
            icon: opts.icon
          })
          .attr({
            'data-name': opts.name,
            'data-icon': opts.icon
          });
        $navMenu.find('.top_menuname').text(opts.name);
        $navMenu
          .find('i.topmenu-icon')
          .removeClass()
          .addClass('icon ' + opts.icon);

        // 当前菜单为选中项
        if (!$navMenu.hasClass('active')) {
          return;
        }

        // 更新子菜单操作面板
        this.updateSubmenuPanel($navMenu.data('children'));

        return;
      }

      // 添加菜单操作 --- 删除选中菜单项
      $('.top-menu > [data-children].active').removeClass('active');

      // 添加菜单操作 --- 包装添加菜单数据重新渲染顶部菜单
      this.topMenuRender([opts]);
    },
    topMenusInit: function() {
      // 初始化顶部菜单列表
      var self = this;

      $.ajax({
        url: '/publication/data/system/menu/trees.json',
        dataType: 'JSON',
        success: function(res) {
          if (res.success) {
            self.topMenuRender(res.data);
          } else {
            toastr.warning(res.msg);
          }
        },
        error: function() {
          toastr.error('服务器异常，请稍后再试！');
        }
      });
    },
    topMenuRender: function(menu) {
      var self = this;
      var data = {};
      var html;

      // 模板渲染顶部菜单
      data.navMenus = menu;
      template.defaults.imports.json_str = function(submenu) {
        return JSON.stringify(submenu);
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
      html = template('navMenu', data);

      $topMenus.append(html);

      // 初始化顶部菜单拖拽功能
      sortable($topMenus, {
        disableIEFix: true
      })[0].addEventListener('sortupdate', function() {
        // 顶部菜单拖拽移动排序
        self.updateTopMenuOrder($(this));
      });

      // 渲染子菜单
      this.submenuRender($topMenus.children('.list-group-item.active').data('children'));

      if (this.menuObj.type !== 'add') {
        this.menuObj = this.getMenuData($topMenus.children('div[data-children].active'));
      }
    },
    updateSubmenuPanel: function(opt) {
      // [arg]有无子菜单，值'existed'时为有菜单但
      var $topMenu = $topMenus.find('>.list-group-item.active');
      // 无子菜单的操作面板
      var $noSubMenu = $pageMain.children('.no-submenu');
      // 有子菜单的操作面板
      var $pageContent = $pageMain.children('.page-content');

      // 更新状态为无子菜单时
      if (_.isNull(opt)) {
        // 当前显示为有子菜单时操作面板先将其隐藏
        if (!$pageMain.hasClass('vertical-align')) {
          $pageMain.removeClass('has-submenu').addClass('vertical-align');
          $pageContent.hide();
          $noSubMenu.removeAttr('hidden');
        }

        // 更新无子菜单操作面板中顶部菜单名称及图标信息
        $noSubMenu.find('i.topmenu-icon').addClass($topMenu.data('icon'));
        $noSubMenu.find('.nav-menu-name').html($topMenu.data('name'));
      } else {
        // 更新状态为有子菜单时

        // 隐藏为无子菜单的操作面板
        $noSubMenu.attr('hidden', true);
        // 显示为有子菜单的操作面板
        $pageMain.addClass('has-submenu').removeClass('vertical-align');
        $pageContent.show();
        // 重置无子菜单操作面板中顶部菜单图标信息
        $noSubMenu
          .find('i')
          .removeClass()
          .addClass('icon topmenu-icon');
      }
    },
    submenuRender: function(obj) {
      var self = this;
      var data;
      var html;
      var menuData;
      var opts = obj;

      var $dragAgoParentNode;

      // 更新子菜单操作面板
      this.updateSubmenuPanel(opts);

      // 获取菜单树容器元素
      $subMenTree = $('#menuTree .menu-tree');

      // 模板渲染子菜单
      data = {menus: opts};
      html = template('childMenu', data);
      $subMenTree.html(html);

      $subMenTree.nestable(
        $.concatCpt('nestable', {
          callback: function(l, e) {
            var dataRank;
            var index;
            var flag;
            var $item = e;

            index = $item.index();

            function changRank($el) {
              var currentRank = $el
                .parent()
                .closest('li')
                .data('rank');
              var storeParentId = $dragAgoParentNode.data('id');
              var currentParentId = $el
                .parent()
                .closest('li')
                .data('id');

              currentRank = _.isNaN(currentRank) ? 2 : currentRank + 1;

              if (typeof dataRank === 'undefined') {
                dataRank = currentRank;

                if (
                  self.dataRank === dataRank &&
                  self.subMenuIndex === index &&
                  storeParentId === currentParentId
                ) {
                  return false;
                }
              }

              $el.data('rank', currentRank).attr('data-rank', currentRank);

              if (currentRank !== 3) {
                $el.data('icon', '').attr('data-icon', '');
                $el
                  .children('.dd-content')
                  .find('i.menu-icon')
                  .remove();
              } else if (e.data('icon') === '') {
                $el.data('icon', 'fa-bars').attr('data-icon', 'fa-bars');
                $el
                  .children('.dd-content')
                  .find('span.menu-name')
                  .prepend("<i class='menu-icon fa-bars'></i> ");
              }

              if (currentRank === 2) {
                $el.data('url', '').attr('data-url', '');
              } else if (e.find('li').length < 1 && $el.data('url') === '') {
                $el.data('url', '#').attr('data-url', '#');
              }

              if ($el.children('ol>li[data-name]').length > 0) {
                $el
                  .children('ol')
                  .children('li[data-name]')
                  .each(function() {
                    changRank($(this));
                  });
              }

              return undefined;
            }

            flag = changRank($item, self.dataRank);

            if (typeof flag !== 'undefined') {
              return;
            }

            self.subType = 'change';

            if ($item.data('type') !== 'add') {
              $item.data('type', 'update').attr('data-type', 'update');
            }

            if (
              $item
                .parent('ol')
                .closest('li')
                .data('url') !== ''
            ) {
              $item
                .parent('ol')
                .closest('li')
                .data({url: '', type: 'update'})
                .attr({'data-url': '', 'data-type': 'update'});
            }

            // 拖拽前父节点中没有子节点时
            if ($dragAgoParentNode.find('li').length < 1) {
              $dragAgoParentNode
                .data({url: '#', type: 'update'})
                .attr({'data-url': '#', 'data-type': 'update'});
            }

            self.submenuEditPanel(self.getMenuData($subMenTree.find('.active').parent('li')));
          },
          onDragStart: function(l, e) {
            var $item = e;

            self.subMenuIndex = $item.index();
            self.dataRank = $item.data('rank');
            $dragAgoParentNode = $item.parent().closest('li');
          }
        })
      );

      // 子菜单信息
      menuData = this.getMenuData($subMenTree.find('.active').parent('li'));

      this.submenuEditPanel(menuData);
    },
    submenuEditPanel: function(menuData) {
      // 子菜单编辑面板
      var self = this;
      var html = template('menuInfo', menuData);

      // 模板渲染子菜单编辑面板
      $('.menu-info').html(html);

      // 初始化子菜单图标选择器
      $('.icp-dd1')
        .iconpicker($.concatCpt('iconpicker'))
        .on('iconpickerSelected', function(e) {
          var value = e.iconpickerValue;
          var $item = $subMenTree.find('.active').parent('li');

          self.subType = 'change';
          // 更新子菜单图标信息
          $item.data('icon', value).attr('data-icon', value);
          $item
            .find('i.menu-icon')
            .removeClass()
            .addClass('menu-icon ' + value);

          // 当前不是新加菜单时添加dat-type为update
          if ($item.data('type') !== 'add') {
            $item.data('type', 'update').attr('data-type', 'update');
          }
        });
    },
    insertChildMenu: function($item, $liActive, dataRrank, newMenu) {
      var rank = dataRrank;
      var newMenuNum = newMenu;
      var callback = function(icon) {
        var html;

        if (icon) {
          html =
            '<li class="dd-item dd-item-alt" data-id="" data-rank="' +
            rank +
            '" new-build="' +
            newMenuNum +
            '" data-name="自定义菜单" data-url="' +
            '#" data-icon="fa-bars" data-type="add"><div class="dd-handle"></div>' +
            '<div class="dd-content active"><span class="menu-name"><i class="menu-icon' +
            ' fa-bars"></i> 自定义菜单</span><span class="float-right fa-angle-right">' +
            '</span></div></li>';
        } else {
          html =
            '<li class="dd-item dd-item-alt" data-id="" data-rank="' +
            rank +
            '" new-build="' +
            newMenuNum +
            '" data-name="自定义菜单" data-url="#" data-icon="" data-type="add">' +
            '<div class="dd-handle"></div><div class="dd-content active"><span class="menu-name">自定义菜单' +
            '</span><span class="float-right fa-angle-right"></span></div></li>';
        }

        return html;
      };

      if ($item.hasClass('after')) {
        if (rank === 3) {
          $liActive.after(callback('icon'));
        } else {
          $liActive.after(callback());
        }
      }

      if ($item.hasClass('append')) {
        if (rank >= 5) {
          toastr.warning('已经是最后一级菜单，不能再为其添加子菜单了！');
          newMenuNum -= 1;
          return false;
        }

        if ($liActive.find('ol').length === 0) {
          $liActive.append('<ol class="dd-list"></ol>');
          $liActive.data('url', '').attr('data-url', '');
        }

        rank = Number(rank) + 1;

        if (rank === 3) {
          $liActive.children('ol').append(callback('icon'));
        } else {
          $liActive.children('ol').append(callback());
        }
      }
      $subMenTree.find('.active:first').removeClass('active');
      return undefined;
    },
    topMenuValidate: function(itemId) {
      // 校验当前菜单信息，成功后保存菜单
      var self = this;
      var topMenuForm = $('#controlMenu').validate({
        rules: {
          menu_name: {
            required: true
          }
        },
        messages: {
          menu_name: {
            required: '请填写菜单名称'
          }
        },
        submitHandler: function(form) {
          var $form = $(form);
          var menuData = {
            id: itemId,
            name: $form.find('[name="menu_name"]').val(),
            icon: $form.find('i[data-icon]').data('icon')
          };

          $.ajax({
            url: ctx + '/menu',
            type: 'POST',
            contentType: 'application/json;',
            data: JSON.stringify(menuData),
            dataType: 'JSON',
            success: function(res) {
              if (res.success) {
                if (itemId) {
                  // 更新菜单
                  $.extend(true, self.menuObj, menuData);
                } else {
                  // 添加菜单
                  $.extend(menuData, {
                    id: res.data.id,
                    layer: res.data.layer,
                    children: res.data.children
                  });

                  self.menuObj = menuData;
                }
                toastr.info('修改成功，重新登录可更新顶部菜单！');

                $topMenuModal
                  .one('hidden.bs.modal', function() {
                    self.updateTopMenu(menuData, itemId);
                    // modal隐藏后重置表单
                    topMenuForm.resetForm();
                  })
                  .modal('hide');
              } else {
                toastr.error(res.msg);
              }
            },
            error: function(err) {
              toastr.error(err);
            }
          });
        }
      });
    }
  });

  $(function() {
    $topMenus = $('.top-menu');
    $topMenuModal = $('#addMenu');
    $pageMain = $('.page-main');

    window.App.run();
  });
})(document, window, jQuery);
