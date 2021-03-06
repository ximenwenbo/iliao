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
                {
                    "class": 'details-control',
                    "orderable": false,
                    "data": 'icon',
                    "defaultContent": ''
                },
                {data: 'id'},
                {data: 'user_nickname'},
                {data: 'mobile'},
                {data: 'avatar'},
                {data: 'sex'},
                {data: 'age'},
                {data: 'create_time'},
                {data: 'status'},
                {data: 'opera'},
            ],
            columnDefs: [
                { "orderable": false, "targets": 0 },
                { "orderable": false, "targets": 2 },
                { "orderable": false, "targets": 3 },
                { "orderable": false, "targets": 4 },
                { "orderable": false, "targets": 5 },
                { "orderable": false, "targets": 7 },
                { "orderable": false, "targets": 8 },
                { "orderable": false, "targets": 9 },
            ],
            order:[
                [1,'desc'],
            ],

            ajax: function(data, callback) {
              $.ajax({
                url: '/admin/merchant_user/index',
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

    let tb = $(".table");

    let format = function(d){
        return '<table class="table to_table table-bordered text-nowrap pl-50 mb-0" cellpadding="4">' +
                    '<tr>' +
                        '<td>金币(不可提现)：'+ d.coin + '</td>'+
                        '<td>金币(可提现)：'+ d.withdraw_coin + '</td>' +
                        '<td>冻结金币(不可提现)：'+ d.frozen_coin + '</td>'+
                        '<td>冻结金币(可提现)：'+ d.withdraw_frozen_coin + '</td>' +
                    '</tr>' +
            '<tr>' +
            '<td width="25%">腾讯QQ：'+ d.qq + '</td>'+
            '<td width="25%">weixin：'+ d.weixin + '</td>' +
            '<td width="25%">被关注人数：'+ d.be_follow_num + '</td>'+
            '<td width="25%">被查看人数：'+ d.be_look_num + '</td>'+
            '</tr>' +
            '<tr>' +
            '<td>资料是否完善：'+ d.info_complete + '</td>' +
            '<td>是否主播：'+ d.daren_status + '</td>'+
            '<td>是否vip：'+ d.is_vip + '</td>'+
            '<td>vip到期时间：'+ d.vip_expire_time + '</td>'+
            '</tr>'+
            '</table>'+
            '<table class="table to_table table-bordered text-nowrap pl-50 mb-0" cellpadding="2">' +
            '<td>个性签名：'+ d.signature + '</td>' +
            '</table>'
            ;
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


