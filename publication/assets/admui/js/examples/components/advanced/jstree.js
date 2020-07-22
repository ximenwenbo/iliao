/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global _, toastr */

  $(function() {
    var timer = null;

    // 内嵌数据
    $('#data').jstree({
      core: {
        data: [
          {
            text: '父节点',
            children: [{text: '子节点 1'}, {text: '子节点 2'}]
          }
        ]
      }
    });

    // 数据格式化
    $('#frmt').jstree({
      core: {
        data: [
          {
            text: '父节点',
            state: {opened: true},
            children: [
              {
                text: '子节点 1',
                state: {selected: true}
              },
              {text: '子节点 2', state: {disabled: true}}
            ]
          }
        ]
      }
    });

    // Ajax加载数据
    $('#ajax')
      .data('jstree', false)
      .empty()
      .jstree({
        core: {
          data: {
            url: '/public/data/examples/components/jstree-root.json',
            dataType: 'JSON'
          }
        }
      });

    // 懒加载
    $('#lazy').jstree({
      core: {
        data: {
          url: '/public/data/examples/components/jstree-root.json',
          dataType: 'JSON',
          data: function(node) {
            return {id: node.id};
          }
        }
      }
    });

    // 从回调函数中获取数据
    $('#clbk').jstree({
      core: {
        data: function(node, cb) {
          if (node.id === '#') {
            cb([{text: '父节点', id: '1', children: true}]);
          } else {
            cb(['子节点']);
          }
        }
      }
    });

    // 交互和事件
    $('#evts')
      .on('changed.jstree', function(e, data) {
        if (data.selected.length) {
          toastr.info('您选择的节点是：' + data.instance.get_node(data.selected[0]).text);
        }
      })
      .jstree({
        core: {
          multiple: false,
          data: [
            {
              text: '父节点',
              children: [{text: '子节点 1', id: 1}, {text: '子节点 2'}]
            }
          ]
        }
      });

    // 插件 - 复选框
    $('#checkbox').jstree({
      core: {
        data: [
          {
            text: '父节点 1'
          },
          {
            text: '父节点 2',
            state: {
              opened: true,
              selected: true
            },
            children: [
              {
                text: '子节点 1'
              },
              {
                text: '子节点 2'
              }
            ]
          }
        ]
      },
      plugins: ['checkbox']
    });

    // 插件 - 右键菜单
    $('#contextmenu').jstree({
      core: {
        check_callback: true,
        data: [
          {
            text: '父节点 1'
          },
          {
            text: '父节点 2',
            state: {
              opened: true,
              selected: true
            },
            children: [
              {
                text: '子节点 1'
              },
              {
                text: '子节点 2'
              }
            ]
          }
        ]
      },
      plugins: ['contextmenu']
    });

    // 插件 - 搜索
    $('#search').jstree({
      core: {
        data: [
          {
            text: '父节点 1'
          },
          {
            text: '父节点 2',
            state: {
              opened: true,
              selected: true
            },
            children: [
              {
                text: '子节点 1'
              },
              {
                text: '子节点 2'
              }
            ]
          }
        ]
      },
      plugins: ['search']
    });

    // 搜索触发
    $('#jstreeSearch').keyup(function() {
      !_.isNull(timer) && clearTimeout(timer);
      timer = setTimeout(function() {
        var v = $('#jstreeSearch').val();
        $('#search')
          .jstree(true)
          .search(v);
      }, 250);
    });

    // 插件 - dnd
    $('#dnd').jstree({
      core: {
        check_callback: true,
        data: [
          {
            text: '父节点 1'
          },
          {
            text: '父节点 2',
            state: {
              opened: true,
              selected: true
            },
            children: [
              {
                text: '子节点 1'
              },
              {
                text: '子节点 2'
              }
            ]
          }
        ]
      },
      plugins: ['dnd']
    });
  });
})(document, window, jQuery);
