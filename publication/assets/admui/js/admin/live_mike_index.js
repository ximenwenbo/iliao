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
        var $filterForm = $('#table-form');

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
                    {data: 'launch_uid'},
                    {data: 'accept_uid'},
                    {data: 'launch_get_coin'},
                    {data: 'accept_get_coin'},
                    {data: 'pk_result'},
                    {data: 'start_time'},
                    {data: 'end_time'},
                    {data: 'status'},
                    {data: 'create_time'},
                    {data: 'opera'},
                ],
                columnDefs: [
                    {"orderable":false,"aTargets":[1,2,3,4,5,6,7,8,9,10]}// 制定列不参与排序
                ],
                order:[
                    [0,'desc'],
                ],

                ajax: function(data, callback) {
                    $.ajax({
                        url: '/admin/live_mike/Index',
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

        /*删除数据*/
        $(".table").on('click','#delete-btn',function () {
            let id = $(this).parent().parent().children('td').eq(0).text();
            layer.confirm('确定删除该信息？', {
                btn: ['确定', '取消'] //可以无限个按钮
            }, function(index, layero){
                //点击确定后操作
                $.ajax({
                    type: 'POST',
                    url: "/admin/live_mike/DeleteInfo",
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

        //日期默认为空
        $('.date-clear').click()

    });

})(document, window, jQuery);


