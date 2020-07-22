/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  /* global sprintf */

  var $tableSelect = $('#tableSelect');
  var $columnSelect = $('#columnSelect');
  var $table = $('#table');
  var defaultTableOptions = {};
  var defaultColumnOptions = {};

  // 表格初始化
  function initTable() {
    var defaultColumns = [
      {
        field: 'id',
        title: '条目 ID'
      },
      {
        field: 'name',
        title: '条目名称'
      },
      {
        field: 'price',
        title: '条目价格'
      }
    ];
    var selectsColumns = [
      {
        field: 'state'
      },
      {
        field: 'id',
        title: '条目 ID'
      },
      {
        field: 'name',
        title: '条目名称'
      },
      {
        field: 'price',
        title: '条目价格'
      }
    ];
    var columns = [];
    var tableOptions = {};
    var columnOptions = {};

    // 记录需要显示的表格数据
    $.each($('option:selected', $tableSelect), function() {
      var key = $(this).val();
      tableOptions[key] = true;
    });
    $.each($('option:selected', $columnSelect), function() {
      var key = $(this).val();
      columnOptions[key] = true;
    });

    if (columnOptions.checkbox) {
      columns = selectsColumns.slice(0);
      columns[0].checkbox = true;
    } else if (columnOptions.radio) {
      columns = selectsColumns.slice(0);
      columns[0].radio = true;
    } else {
      columns = defaultColumns.slice(0);
    }
    delete columnOptions.checkbox;
    delete columnOptions.radio;

    $.each(columns, function(i, column) {
      columns[i] = $.extend({}, defaultColumnOptions, columnOptions, column);
    });

    $table.bootstrapTable('destroy').bootstrapTable(
      $.extend(
        {
          url: '/public/data/examples/tables/bootstrap-table/data1.json',
          columns: columns
        },
        defaultTableOptions,
        tableOptions
      )
    );
  }

  function initOptions(defaults, defaultOptions, $select) {
    var html = [];
    var opts = defaultOptions;

    $.each(defaults, function(key, value) {
      if (typeof value === 'boolean') {
        html.push(
          sprintf('<option value="%(key)s" %(value)s>%(key)s</option>', {
            key: key,
            value: value ? 'selected' : ''
          })
        );

        if ($.inArray(key, ['radio', 'checkbox'] === -1)) {
          opts[key] = false;
        }
      }
    });
    $select
      .html(html.join(''))
      .selectpicker($.concatCpt('selectpicker'))
      .on('changed.bs.select', function() {
        initTable();
      });
  }

  $(function() {
    initOptions($.fn.bootstrapTable.defaults, defaultTableOptions, $tableSelect);
    initOptions($.fn.bootstrapTable.columnDefaults, defaultColumnOptions, $columnSelect);
    initTable();
  });
})(jQuery);
