var $filterDate = $('#filter-date');
//选择开关是否启用日期
$("input[name='VipLimit[status]']").click(function () {
    if($(this).val() == 1){
        $filterDate.removeAttr('disabled');
    }else{
        $filterDate.attr('disabled','false');
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
            timePickerSeconds: true,
            timePicker : true,
            timePicker24Hour : true,
            // alwaysShowCalendars: true,
            ranges: {
                '今天': [moment(),moment()],
                '昨天': [moment().subtract(1, 'days'),moment().subtract(1, 'days')],
                '近7天': [moment().subtract(7, 'days'), moment()],
                '这个月': [moment().startOf('month'), moment().endOf('month')],
                '上个月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: "MM月DD日 HH时mm分",
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
        $("#startTime").val(picker.startDate.format('YYYY-MM-DD HH:mm'));
        $("#endTime").val(picker.endDate.format('YYYY-MM-DD HH:mm'));
        $("#filter-date").val(picker.startDate.format('YYYY-MM-DD HH:mm')+" 至 "+picker.endDate.format('YYYY-MM-DD HH:mm'));
    });

    // 删除选择时间
    $('.date-clear').on('click', function() {
        $("#exampleFullForm").find('#filter-date').val('');
        $(this).parent().parent().children().eq(2).val('');
        $(this).parent().parent().children().eq(3).val('');
    });
}