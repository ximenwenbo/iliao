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
                    {data: 'user_nickname'},
                    {data: 'mobile'},
                    {data: 'avatar'},
                    {data: 'sex'},
                    {data: 'age'},
                    {data: 'coin'},
                    {data: 'daren_status'},
                    {data: 'vip_expire_time'},
                    {data: 'create_time'},
                    {data: 'last_login_time'},
                    {data: 'last_login_ip'},
                    {data: 'status'},
                    {data: 'is_online'},
                    {data: 'device_type'},
                    {data: 'opera'},
                ],
                aoColumnDefs: [
                  {"orderable":false,"aTargets":[0,2,3,4,5,7,8,9,10,11,12,13,14,15]}// 制定列不参与排序
                ],
                order:[
                    [1,'desc'],
                ],
                //ajax请求后台datatable数据
                ajax: function(data, callback) {
                    $.ajax({
                        url: '/admin/staff_managing/ListAjax',
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
                        },
                        error: function(err) {
                            toastr.error(err);
                        }
                    });
                }
            })
        );

        // 有日期筛选按钮时 --- 日志信息页面
        let date_clear = $(".date-clear");
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
           date_clear.on('click', function() {
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

        //日期默认为空
        date_clear.click()

    });//function end

})(document, window, jQuery);


