/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  $(function() {
    var $table = $('#table');

    // 树形表格初始化
    $table.bootstrapTable({
      url: '/public/data/examples/tables/bootstrap-table/treegrid.json',
      height: $(window).height(),
      striped: true,
      sidePagination: 'server',
      idField: 'id',
      columns: [
        {
          field: 'ck',
          checkbox: true
        },
        {
          field: 'name',
          title: '名称'
        },
        // {field: 'id', title: '编号', sortable: true, align: 'center'},
        // {field: 'pid', title: '所属上级'},
        {
          field: 'status',
          title: '状态',
          sortable: true,
          align: 'center',
          formatter: 'statusFormatter'
        },
        {
          field: 'permissionValue',
          title: '权限值'
        }
      ],
      // bootstrap-table-tree-column.js 插件配置
      // treeShowField: 'name',
      // parentIdField: 'pid'
      // bootstrap-table-tree-column.js 插件配置

      // bootstrap-table-treegrid.js 插件配置
      treeShowField: 'name',
      parentIdField: 'pid',
      onLoadSuccess: function() {
        // jquery.treegrid.js
        $table.treegrid(
          $.concatCpt('treegrid', {
            // initialState: 'collapsed',
            treeColumn: 1,
            // expanderExpandedClass: 'glyphicon glyphicon-minus',
            // expanderCollapsedClass: 'glyphicon glyphicon-plus',
            onChange: function() {
              $table.bootstrapTable('resetWidth');
            }
          })
        );
      }
      // bootstrap-table-treetreegrid.js 插件配置
    });
  });

  // 格式化类型
  /*  typeFormatter(value, row, index) {
      if (value === 'menu') {
        return '菜单';
      }
      if (value === 'button') {
        return '按钮';
      }
      if (value === 'api') {
        return '接口';
      }
      return '-';
    } */

  // 格式化状态
  /* function statusFormatter(value, row, index) {
      if (value === 1) {
        return '<span class="label label-success">正常</span>';
      }
      return '<span class="label label-default">锁定</span>';
    } */
})(jQuery);
