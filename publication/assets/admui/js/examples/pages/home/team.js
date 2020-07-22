/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global Chartist */
  /* eslint no-underscore-dangle: ["error", { "allow": ["_node"] }] */

  var teamPage = {
    taskExample: function() {
      // 任务一
      var task1 = new Chartist.Pie(
        '#taskOne',
        {
          series: [86, 14]
        },
        {
          donut: true,
          donutWidth: 10,
          startAngle: 0,
          showLabel: false
        }
      );

      // 任务二
      var task2 = new Chartist.Pie(
        '#taskTwo',
        {
          series: [62, 38]
        },
        {
          donut: true,
          donutWidth: 10,
          startAngle: 0,
          showLabel: false
        }
      );

      // 任务三
      var task3 = new Chartist.Pie(
        '#taskThree',
        {
          series: [56, 44]
        },
        {
          donut: true,
          donutWidth: 10,
          startAngle: 0,
          showLabel: false
        }
      );

      console.log(task1);
      console.log(task2);
      console.log(task3);
    },
    chartExample: function() {
      // 图表参数
      var options = {
        showArea: true,
        low: 0,
        high: 1000,
        height: 453,
        fullWidth: true,
        axisX: {
          offset: 30
        },
        axisY: {
          offset: 30,
          labelInterpolationFnc: function(value) {
            if (value === 0) {
              return null;
            }
            return value;
          },
          scaleMinSpace: 50
        },
        chartPadding: {
          bottom: 12,
          left: 10
        },
        plugins: [Chartist.plugins.tooltip()]
      };
      var labelList = [
        '4月',
        '5月',
        '6月',
        '7月',
        '8月',
        '9月',
        '10月',
        '11月',
        '12月',
        '1月',
        '2月',
        '3月'
      ];
      var series1List = {
        name: 'series-1',
        data: [0, 180, 600, 980, 850, 600, 300, 350, 600, 200, 630, 350]
      };
      var series2List = {
        name: 'series-2',
        data: [0, 100, 520, 810, 620, 500, 630, 400, 380, 405, 210, 220]
      };

      // 创建团队任务完成情况折现图
      var newScoreLineChart = function(chartId, label, series1, series2, opts) {
        var lineChart = new Chartist.Line(
          chartId,
          {
            labels: label,
            series: [series1, series2]
          },
          opts
        );

        lineChart.on('draw', function(data) {
          var elem;
          var parent;

          if (data.type === 'point') {
            elem = data.element;
            parent = new Chartist.Svg(elem._node.parentNode);

            parent.elem('line', {
              x1: data.x,
              y1: data.y,
              x2: data.x + 0.01,
              y2: data.y,
              class: 'ct-point-content'
            });
          }
        });
      };

      newScoreLineChart(
        '#teamCompletedWidget .ct-chart',
        labelList,
        series1List,
        series2List,
        options
      );
    },
    waitingThing: function() {
      // 任务面板初始化
      var handleSelective = function(handleSelectiveItem) {
        var member = [
          {
            id: 'uid_1',
            name: '周伊娅',
            avatar: '../../../../public/images/portraits/1.jpg'
          },
          {
            id: 'uid_2',
            name: '程思思',
            avatar: '../../../../public/images/portraits/2.jpg'
          },
          {
            id: 'uid_3',
            name: '张倩',
            avatar: '../../../../public/images/portraits/3.jpg'
          },
          {
            id: 'uid_4',
            name: '蒋海燕',
            avatar: '../../../../public/images/portraits/4.jpg'
          }
        ];

        $('[data-plugin="jquery-selective"]').selective({
          namespace: 'addMember',
          local: member,
          selected: handleSelectiveItem,
          buildFromHtml: false,
          tpl: {
            optionValue: function(data) {
              return data.id;
            },
            frame: function() {
              return (
                '<div class="' +
                this.namespace +
                '">' +
                this.options.tpl.items.call(this) +
                '<div class="' +
                this.namespace +
                '-trigger">' +
                this.options.tpl.triggerButton.call(this) +
                '<div class="' +
                this.namespace +
                '-trigger-dropdown">' +
                this.options.tpl.list.call(this) +
                '</div>' +
                '</div>' +
                '</div>'
              );
            },
            triggerButton: function() {
              return (
                '<div class="' + this.namespace + '-trigger-button"><i class="wb-plus"></i></div>'
              );
            },
            listItem: function(data) {
              return (
                '<li class="' +
                this.namespace +
                '-list-item"><img class="avatar" src="' +
                data.avatar +
                '">' +
                data.name +
                '</li>'
              );
            },
            item: function(data) {
              return (
                '<li class="' +
                this.namespace +
                '-item"><img class="avatar" src="' +
                data.avatar +
                '" title="' +
                data.name +
                '">' +
                this.options.tpl.itemRemove.call(this) +
                '</li>'
              );
            },
            itemRemove: function() {
              return (
                '<span class="' + this.namespace + '-remove"><i class="wb-minus-circle"></i></span>'
              );
            },
            option: function(data) {
              return (
                '<option value="' +
                this.options.tpl.optionValue.call(this, data) +
                '">' +
                data.name +
                '</option>'
              );
            }
          }
        });
      };

      // 点击到焊条显示任务面板
      $('#addNewItemBtn').on('click', function() {
        var handleSelectiveItem = [
          {
            id: 'uid_1',
            name: '程思思',
            avatar: '../../../../public/images/portraits/1.jpg'
          },
          {
            id: 'uid_2',
            name: '蒋海燕',
            avatar: '../../../../public/images/portraits/2.jpg'
          }
        ];

        handleSelective(handleSelectiveItem);

        $('#addtodoItemForm').modal('show');
      });

      // 点击任务条阻止默认操作
      $('#toDoListWidget .list-group-item input').on('click', function(e) {
        e.stopPropagation();
      });

      // 点击任务条改变modal表单中input值
      $('#toDoListWidget .list-group-item').on('click', function() {
        var oldTitle = $(this)
          .find('.item-title')
          .text();
        var dueDate = $(this)
          .find('.item-due-date > span')
          .text();
        var handleSelectiveItem = [];

        if (dueDate === 'No due date') {
          dueDate = null;
        } else {
          dueDate = '8/25/2015';
        }

        $('#editTitle').val(oldTitle);
        $('#editDueDate').val(dueDate);
        handleSelective(handleSelectiveItem);

        $('#edittodoItemForm').modal('show');
      });
    },
    run: function() {
      // 折线图
      this.chartExample();
      // 待办事项
      this.waitingThing();
      // 任务图表
      this.taskExample();
    }
  };

  $(function() {
    teamPage.run();
  });
})(document, window, jQuery);
