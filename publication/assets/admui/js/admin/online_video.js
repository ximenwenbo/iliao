/**
 * 用户反馈列表
 *
 */
(function(document, window, $) {
  'use strict';

  /* global moment, toastr */

  $(function() {
    var oTable;
    var $filterDate = $('#filter-date');
    var $filterForm = $('#logForm');

    // 表格初始化
    oTable = $('.dataTable').DataTable(
      $.concatCpt('dataTable', {
        autoWidth: false,
        processing: true,
        serverSide: true,
        searching: false,
        pagingType: 'simple_numbers',
        columns: [
            {data: 'id'},
            {data: 'home'},
            {data: 'users'},
            {data: 'time'},
            {data: 'opera'},
        ],
        columnDefs: [
            { "orderable": false, "targets": 0 },
            { "orderable": false, "targets": 1 },
            { "orderable": false, "targets": 2 },
            { "orderable": false, "targets": 3 },
            { "orderable": false, "targets": 4 },
        ],
        order:[
            [1,'desc'],
        ],

        ajax: function(data, callback) {
          $.ajax({
            url: '/admin/online_video/index',
            cache: false,
            dataType: 'JSON',
            data: $.extend(
              {data: $filterForm.serializeObject()},
              {
                pageIndex: data.start + 0,
                sortField: data.order[0].column,
                sortType: data.order[0].dir,
                pageSize: data.length
              }
            ),
            success: function(res) {
              callback({
                recordsTotal: res.total,
                recordsFiltered: res.total,
                data: res.pageList
              });
              $("input[name='status']").eq(res.data).prop('selected')
            },
            error: function(err) {
              toastr.error(err);
            }
          });
        }
      })
    );

      // 有日期筛选按钮时 --- 日志信息页面
      if ($filterDate.length > 0) {
          // 日期范围选择器初始化
          $filterDate.daterangepicker
          (
              {
                  // autoApply: true,
                  autoUpdateInput: false,
                  // alwaysShowCalendars: true,
                  ranges: {
                      '今天': [moment(),moment()],
                      '昨天': [moment().subtract(1, 'days'),moment().subtract(1, 'days')],
                      '近7天': [moment().subtract(7, 'days'), moment()],
                      '这个月': [moment().startOf('month'), moment().endOf('month')],
                      '上个月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                  },
                  locale: {
                      format: "MM月DD日",
                      separator: " 至 ",
                      applyLabel: "确认",
                      cancelLabel: "清空",
                      fromLabel: "开始时间",
                      toLabel: "结束时间",
                      customRangeLabel: "自定义",
                      daysOfWeek: ["日","一","二","三","四","五","六"],
                      monthNames: ["一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"]
                  }
              }
          ).on('cancel.daterangepicker', function(ev, picker) {
              $("#filter-date").val('');
              $("#startTime").val("");
              $("#endTime").val("");
          }).on('apply.daterangepicker', function(ev, picker) {
              $("#startTime").val(picker.startDate.format('YYYY-MM-DD'));
              $("#endTime").val(picker.endDate.format('YYYY-MM-DD'));
              $("#filter-date").val(picker.startDate.format('YYYY-MM-DD')+" 至 "+picker.endDate.format('YYYY-MM-DD'));
          });


          // 提交日志筛选表单
          $filterForm.on('submit', function() {
              // 重载表格数据
              oTable.ajax.reload();
              return false;
          });

          // 删除选择时间
          $('.date-clear').on('click', function() {
              $filterForm.find('#filter-date').val('');
              $(this).parent().parent().children().eq(2).val('');
              $(this).parent().parent().children().eq(3).val('');
          });
      } else {
          // 切换到日志选项卡时重绘表格 --- 账户信息页面
          $('[href="#log"]').on('shown.bs.tab', function() {
              oTable.draw();
          });
      }

      // 提交日志筛选表单
      $filterForm.on('submit', function() {
          // 重载表格数据
          oTable.ajax.reload();
          return false;
      });

    let tb = $(".table");
    /*删除数据*/
    tb.on('click','#authDel',function () {
      let id = $(this).parent().parent().children('td').eq(0).text();
      layer.confirm('确定删除该信息？', {
          btn: ['确定', '取消'] //可以无限个按钮
      }, function(index, layero){
          //点击确定后操作
          $.ajax({
              type: 'POST',
              url: "/admin/real_name_auth/authDelete",
              data: {id:id},
              dataType: "json",
              success: function(data){
                  if(data.code === 200){
                      layer.alert(data.msg, {icon: 7});
                      oTable.ajax.reload();
                      return false;
                  }else {
                      layer.alert(data.msg, {icon: 5});
                      oTable.ajax.reload();
                      return false;
                  }

              }
          })
      }, function(index){
          //点击取消
      });
    });

    let format = function(d){
        let u = d.user;
        return '<table class="table table-bordered text-nowrap pl-50 mb-0 addTable" cellpadding="5">' +
                    '<tr>' +
                        '<td colspan="4"><b>直播用户信息</b>：</td>' +
                    '</tr>'+
                    '<tr>' +
                        '<td>用户id：'+ u.id + '</td>' +
                        '<td>用户昵称：'+ u.nickname + '</td>' +
                        '<td>手机号码：'+ u.mobile + '</td>'+
                        '<td>性别：'+ u.sex + '</td>'+

                    '</tr>' +
                    '<tr>' +
                        '<td>年龄：'+ u.age + '</td>' +
                        '<td>qq：'+ u.qq + '</td>' +
                        '<td>微信：'+ u.weixin + '</td>'+
                        '<td></td>'+
                    '</tr>'+
                    '<tr>'+
                        '<td>是否vip：'+ u.is_vip + '</td>' +
                        '<td>主播认证：'+ u.daren + '</td>'+
                        '<td></td>'+
                        '<td></td>'+
                    '</tr>'+
                '</table>'+
                '<div style="height: 10px;"></div>';
    };
    // 展开关闭详情时的事件监听
    tb.on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = oTable.row(tr);
        if (row.child.isShown()) {
          // 本行已展开
          row.child.hide();
          tr.removeClass('shown');
          $(this).children().attr('class','icon wb-dropright');
        }
        else {
          // 展开本行
          row.child(format(row.data())).show();
          tr.addClass('shown');
          $(this).children().attr('class','icon wb-dropdown');
        }
    });
      //日期默认为空
      $('.date-clear').click()

  });

})(document, window, jQuery);
