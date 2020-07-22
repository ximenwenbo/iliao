/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  /* global moment, toastr */

  $(function() {
    var updateConfig;
    // 日期范围选择代码显示内容
    var $drpConfigText = $('#drpConfigText');

    // Jquery Timepicker（时间选择器） --- 重置时间
    $('#exampleTimeButton').on('click', function() {
      $('#inputTextCurrent').timepicker('setTime', new Date());
    });

    // Date Picker（日期选择器） --- 内嵌日期选择器
    $('#inlineDatepicker')
      .datepicker(
        $.concatCpt('datepicker', {
          language: 'zh-CN'
        })
      )
      .on('changeDate', function() {
        $('#inputHiddenInline').val($('#inlineDatepicker').datepicker('getFormattedDate'));
      });

    // Date Range Picker（日期范围选择）--- 日期选择框配置
    updateConfig = function() {
      var options = {};
      var timePickerIncrement = $('#timePickerIncrement').val();
      var cancelClass = $('#cancelClass').val();
      var opens = $('#opens').val();
      var drops = $('#drops').val();
      var buttonClasses = $('#buttonClasses').val();
      var applyClass = $('#applyClass').val();

      if ($('#singleDatePicker').is(':checked')) {
        options.singleDatePicker = true;
      }

      if ($('#showDropdowns').is(':checked')) {
        options.showDropdowns = true;
      }

      if ($('#showWeekNumbers').is(':checked')) {
        options.showWeekNumbers = true;
      }

      if ($('#showISOWeekNumbers').is(':checked')) {
        options.showISOWeekNumbers = true;
      }

      if ($('#timePicker').is(':checked')) {
        options.timePicker = true;
      }

      if ($('#timePicker24Hour').is(':checked')) {
        options.timePicker24Hour = true;
      }

      if (timePickerIncrement.length && timePickerIncrement !== 1) {
        options.timePickerIncrement = parseInt(timePickerIncrement, 10);
      }

      if ($('#timePickerSeconds').is(':checked')) {
        options.timePickerSeconds = true;
      }

      if ($('#autoApply').is(':checked')) {
        options.autoApply = true;
      }

      if ($('#dateLimit').is(':checked')) {
        options.dateLimit = {days: 7};
      }

      if ($('#ranges').is(':checked')) {
        options.ranges = {
          Today: [moment(), moment()],
          Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [
            moment()
              .subtract(1, 'month')
              .startOf('month'),
            moment()
              .subtract(1, 'month')
              .endOf('month')
          ]
        };
      }

      if ($('#locale').is(':checked')) {
        options.locale = {
          format: 'MM/DD/YYYY',
          separator: ' - ',
          applyLabel: 'Apply',
          cancelLabel: 'Cancel',
          fromLabel: 'From',
          toLabel: 'To',
          customRangeLabel: 'Custom',
          weekLabel: 'W',
          daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
          monthNames: [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
          ],
          firstDay: 1
        };
      }

      if (!$('#linkedCalendars').is(':checked')) {
        options.linkedCalendars = false;
      }

      if (!$('#autoUpdateInput').is(':checked')) {
        options.autoUpdateInput = false;
      }

      if (!$('#showCustomRangeLabel').is(':checked')) {
        options.showCustomRangeLabel = false;
      }

      if ($('#alwaysShowCalendars').is(':checked')) {
        options.alwaysShowCalendars = true;
      }

      if ($('#parentEl').val().length) {
        options.parentEl = $('#parentEl').val();
      }

      if ($('#startDate').val().length) {
        options.startDate = $('#startDate').val();
      }

      if ($('#endDate').val().length) {
        options.endDate = $('#endDate').val();
      }

      if ($('#minDate').val().length) {
        options.minDate = $('#minDate').val();
      }

      if ($('#maxDate').val().length) {
        options.maxDate = $('#maxDate').val();
      }

      if (opens.length && opens !== 'right') {
        options.opens = opens;
      }

      if (drops.length && drops !== 'down') {
        options.drops = drops;
      }

      if (buttonClasses.length && buttonClasses !== 'btn btn-sm') {
        options.buttonClasses = buttonClasses;
      }

      if (applyClass.length && applyClass !== 'btn-success') {
        options.applyClass = applyClass;
      }

      if (cancelClass.length && cancelClass !== 'btn-default') {
        options.cancelClass = cancelClass;
      }

      $drpConfigText.val(
        "$('#demo').daterangepicker(" +
          JSON.stringify(options, null, '    ') +
          ", function(start, end, label) {\n  console.log(\"New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')\");\n});"
      );

      $('#drpConfigDemo').daterangepicker($.concatCpt('daterangepicker', options), function(
        start,
        end
      ) {
        toastr.info(
          '新的日期选择范围: ' +
            start.format('YYYY-MM-DD') +
            ' to ' +
            end.format('YYYY-MM-DD') +
            ')'
        );
      });
    };

    // Date Range Picker（日期范围选择） --- 配置更新时更新代码显示内容&初始化日期选择框
    $('#rangePickerConfig')
      .find('input:not("#drpConfigDemo, #drpConfigText"), select')
      .change(function() {
        updateConfig();
      });

    // Date Range Picker（日期范围选择）--- 开始日期
    $('#startDate').daterangepicker(
      $.concatCpt('daterangepicker', {
        singleDatePicker: true,
        startDate: moment().subtract(6, 'days')
      })
    );

    // Date Range Picker（日期范围选择） --- 结束日期
    $('#endDate').daterangepicker(
      $.concatCpt('daterangepicker', {
        singleDatePicker: true,
        startDate: moment()
      })
    );

    updateConfig();
  });
})(document, window, jQuery);
