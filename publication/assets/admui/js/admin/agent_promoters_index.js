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
                    {data: 'agent_id'},
                    {data: 'user_id'},
                    {data: 'invite_divide_into'},
                    {data: 'recharge_divide_into'},
                    {data: 'anchor_divide_into'},
                    {data: 'create_time'},
                    {data: 'status'},
                    {data: 'opera'},
                ],
                columnDefs: [
                    {"orderable":false,"aTargets":[1,2,3,4,5,6,7,8]}// 制定列不参与排序
                ],
                order:[
                    [0,'desc'],
                ],

                ajax: function(data, callback) {
                    $.ajax({
                        url: '/admin/agent_promoters/index',
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

        //操作+
        let _tbody = $('table tbody');
        _tbody.on( 'mouseenter', 'tr>td:last-child', function (data) {
            let _this = $(this);
            if(_this.parent().children().length < 2){
                return;
            }
            let num = _this.children();
            if(num.length > 1){
                _this.children('ul').show();
            }else{
                let ul = '<ul>';
                let btn = ['分成设置','删除'];
                for (let i = 0; i < btn.length; i++){
                    ul += '<li>'+btn[i]+'</li>';
                }
                ul += '</ul>';
                _this.append(ul);
                _this.css({
                    'position' : 'relative',
                });
                _this.children('ul').css({
                    'padding' : '0',
                    'margin' : 0,
                    'width' : '112px',
                    'position' : 'absolute',
                    'right':'38px',
                    'top':'46px',
                    'background-color':'#fff',
                    'border':'1px solid #e0e0e0',
                    'z-index' : 99
                });
                _this.children('ul').children('li').css({
                    'padding' : '12px 24px',
                    'margin' : 0,
                    'line-height' : '20px',
                });
                _this.children('ul').children('li').mouseenter(function () {
                    $(this).css({
                        'background' : '#e5e5e5',
                        'cursor' : 'pointer',
                    });
                }).mouseleave(function () {
                    $(this).css({
                        'background' : '#fff',
                    });
                });
            }
        }).on( 'mouseleave', 'tr>td:last-child', function () {
            $(this).children('ul').hide();
        });
        //更多按钮
        _tbody.on( 'click', 'tr>td:last-child>ul>li', function (data) {
            let _index = $(this).index();
            let id = $(this).parent().parent().parent().children().eq(0).text();
            switch(_index){
                case 0:
                    layer.open({
                        type: 2,    //0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
                        title: '分成设置', //弹出框的标题
                        shade: 0.2,
                        area: ['100%','100%'],
                        content: "/admin/agent_promoters/DividedSetting/id/"+id,
                    });
                    break;
                case 1:
                    layer.confirm('确定要删除推广员?',{btn: ['确定', '取消'], title: "警告"},function () {
                        $.ajax({
                            type: 'POST',
                            url: "/admin/agent_promoters/delete",
                            data: {
                                id: id,
                            },
                            dataType: "json",
                            success: function (data) {
                                if (data.code == 200 || data.code == 1) {
                                    layer.alert(data.msg, {icon: 5}, function () {
                                        window.parent.location.reload();
                                        let index = parent.layer.getFrameIndex(window.name);
                                        parent.layer.close(index);
                                    });
                                } else {
                                    layer.alert(data.msg, {icon: 7});
                                }
                            },
                        });
                    });
                    break;
            }
        });


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

    });

})(document, window, jQuery);


