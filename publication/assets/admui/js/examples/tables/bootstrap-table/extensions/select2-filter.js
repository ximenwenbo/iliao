/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  // select过滤后的表格tpl
  var getNumberFilterTemplate = function(fieldId) {
    var numberFilterClass = 'numberFilter-' + fieldId;
    var tpl = function(bootstrapTable) {
      var search = function(event, value) {
        var table = bootstrapTable;
        table.searchText = undefined;

        clearTimeout(table.timeoutId);
        table.timeoutId = setTimeout(function() {
          table.onColumnSearch(event, fieldId, value);
        }, table.options.searchTimeOut);
      };
      var $el = $(
        '<div class="input-group ' +
          numberFilterClass +
          '">' +
          '<div class="input-group-prepend"><span class="input-group-text">大于</span></div>' +
          '<input type="number" class="form-control">' +
          '</div>'
      );
      var $input = $el.find('input');

      $input.off('keyup').on('keyup', function(event) {
        search(event, $(this).val());
      });

      $input.off('mouseup').on('mouseup', function(event) {
        var $item = $(this);
        var oldValue = $item.val();

        if (oldValue === '') {
          return;
        }

        setTimeout(function() {
          var newValue = $item.val();

          if (newValue === '') {
            search(event, newValue);
          }
        }, 1);
      });

      return $el;
    };

    return tpl;
  };
  // 表格参数
  var options = {
    url: '/public/data/examples/tables/bootstrap-table/data1.json',
    columns: [
      {
        field: 'id',
        title: 'ID',
        filter: {
          type: 'input'
        }
      },
      {
        field: 'name',
        title: '条目名称',
        filter: {
          type: 'select',
          data: []
        }
      },
      {
        field: 'price',
        title: '条目价格',
        filter: {
          type: 'select',
          data: ['', '￥1', '￥2', '￥3']
        }
      },
      {
        field: 'amount',
        title: '合计',
        width: 200,
        filter: {
          template: getNumberFilterTemplate('amount'),
          setFilterValue: function($filter, field, value) {
            if (value) {
              $filter.find('input').val(value.value);
            }
          }
        }
      }
    ],
    filter: true,
    filterTemplate: {
      input: function(bootstrapTable, column, isVisible) {
        return (
          '<input type="text" class="form-control input-sm" data-filter-field="' +
          column.field +
          '" style="width: 100%; visibility:' +
          isVisible +
          '">'
        );
      }
    }
  };

  $(function() {
    // 初始化表格
    var $table = $('#table').bootstrapTable(options);
    $table.bootstrapTable('setSelect2Data', 'name', ['', '条目 1', '条目 2', '条目 3']);
  });
})(jQuery);
