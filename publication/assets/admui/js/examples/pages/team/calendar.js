/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  window.App.extend({
    handleFullcalendar: function() {
      // 自定义事件配置
      var myEvents = [
        {
          title: '每天',
          start: '2016-10-01'
        },
        {
          title: '长事件',
          start: '2016-10-07',
          end: '2016-10-10',
          backgroundColor: $.getColor('cyan', 600),
          borderColor: $.getColor('cyan', 600)
        },
        {
          id: 999,
          title: '重复事件',
          start: '2016-10-09T16:00:00',
          backgroundColor: $.getColor('red', 600),
          borderColor: $.getColor('red', 600)
        },
        {
          title: '会议',
          start: '2016-10-11',
          end: '2016-10-13'
        },
        {
          title: '开会',
          start: '2016-10-12T10:30:00',
          end: '2016-10-12T12:30:00'
        },
        {
          title: '午餐',
          start: '2016-10-12T12:00:00'
        },
        {
          title: '开会',
          start: '2016-10-12T14:30:00'
        },
        {
          title: '休息',
          start: '2016-10-12T17:30:00'
        },
        {
          title: '活动',
          start: '2016-10-12T20:00:00'
        },
        {
          title: '生日会',
          start: '2016-10-13T07:00:00'
        }
      ];
      // 初始化添加按钮
      var actionBtn = $('.site-action')
        .actionBtn()
        .data('actionBtn');
      // 日历参数
      var myOptions = {
        header: {
          left: null,
          center: 'prev,title,next',
          right: 'month,agendaWeek,agendaDay'
        },
        defaultDate: '2016-10-12',
        selectable: true,
        selectHelper: true,
        select: function() {
          $('#addNewEvent').modal('show');
        },
        editable: true,
        eventLimit: true,
        windowResize: function(view) {
          var width = $(window).outerWidth();
          var options = $.extend({}, myOptions);
          options.events = view.calendar.getEventCache();
          options.aspectRatio = width < 667 ? 0.5 : 1.35;

          $('#calendar')
            .fullCalendar('destroy')
            .end()
            .fullCalendar(options);
        },
        eventClick: function(event, jsEvent) {
          var opts = event;
          var color = opts.backgroundColor ? opts.backgroundColor : $.getColor('blue', 600);
          $('#editEname').val(event.title);

          if (opts.start) {
            $('#editStarts').datepicker('update', opts.start.d);
          } else {
            $('#editStarts').datepicker('update', '');
          }
          if (opts.end) {
            $('#editEnds').datepicker('update', opts.end.d);
          } else {
            $('#editEnds').datepicker('update', '');
          }

          $('#editColor [type=radio]').each(function() {
            var $this = $(this);
            var value = $this.data('color').split('|');
            value = $.getColor(value[0], value[1]);

            if (value === color) {
              $this.prop('checked', true);
            } else {
              $this.prop('checked', false);
            }
          });
          $('#editColor [value="' + event.backgroundColor + '"]').prop('checked', true);

          $('#editNewEvent')
            .modal('show')
            .one('hidden.bs.modal', function() {
              var color1 = $('#editColor [type=radio]:checked')
                .data('color')
                .split('|');

              opts.title = $('#editEname').val();
              color = $.getColor(color1[0], color1[1]);
              opts.backgroundColor = color1;
              opts.borderColor = color1;

              opts.start = new Date(
                $('#editStarts')
                  .data('datepicker')
                  .getDate()
              );
              opts.end = new Date(
                $('#editEnds')
                  .data('datepicker')
                  .getDate()
              );
              $('#calendar').fullCalendar('updateEvent', event);
            });
          jsEvent.stopPropagation();
        },
        eventDragStart: function() {
          actionBtn.show();
        },
        eventDragStop: function() {
          actionBtn.hide();
        },
        events: myEvents,
        droppable: true
      };
      var options;
      var myOptionsMobile = $.extend({}, myOptions);

      myOptionsMobile.aspectRatio = 0.5;
      options = $(window).outerWidth() < 667 ? myOptionsMobile : myOptions;

      $('#editNewEvent').modal();

      // 初始化日历
      $('#calendar').fullCalendar(options);
    },
    handleSelective: function() {
      // selective自定义参数
      var member = [
        {
          id: 'uid_1',
          name: '刘松洋',
          avatar: '../../../../public/images/portraits/1.jpg'
        },
        {
          id: 'uid_2',
          name: '邓艳红',
          avatar: '../../../../public/images/portraits/2.jpg'
        },
        {
          id: 'uid_3',
          name: '张润展',
          avatar: '../../../../public/images/portraits/3.jpg'
        },
        {
          id: 'uid_4',
          name: '李吉琴',
          avatar: '../../../../public/images/portraits/4.jpg'
        }
      ];
      var items = [
        {
          id: 'uid_1',
          name: '赵振宁',
          avatar: '../../../../public/images/portraits/1.jpg'
        },
        {
          id: 'uid_2',
          name: '陈宝荣',
          avatar: '../../../../public/images/portraits/2.jpg'
        }
      ];

      // 初始化人员选择功能
      $('[data-plugin="jquery-selective"]').selective({
        namespace: 'addMember',
        local: member,
        selected: items,
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
    },
    handleEventList: function() {
      // 新建当前事件
      $('#addNewEventBtn').on('click', function() {
        $('#addNewEvent').modal('show');
      });

      // 显示已添加日历事件
      $('.calendar-list .calendar-event').each(function() {
        var $this = $(this);
        var color = $this.data('color').split('-');

        $this.data('event', {
          title: $this.data('title'),
          stick: $this.data('stick'),
          backgroundColor: $.getColor(color[0], color[1]),
          borderColor: $.getColor(color[0], color[1])
        });

        $this.draggable({
          zIndex: 999,
          revert: true,
          revertDuration: 0,
          helper: function() {
            return (
              '<a class="fc-day-grid-event fc-event fc-start fc-end" style="background-color:' +
              $.getColor(color[0], color[1]) +
              ';border-color:' +
              $.getColor(color[0], color[1]) +
              '">' +
              '<div class="fc-content">' +
              '<span class="fc-title">' +
              $this.data('title') +
              '</span>' +
              '</div>' +
              '</a>'
            );
          }
        });
      });
    },
    handleListItem: function() {
      // 添加新日历
      $('.site-action').on('click', function(e) {
        $('#addNewCalendar').modal('show');
        e.stopPropagation();
      });

      // 删除当前日历
      $(document).on('click', '[data-tag=list-delete]', function() {
        window.top.layer.alert('你想删除这个日历吗?', {icon: 4}, function(index) {
          // $(e.target).closest('.list-group-item').remove();
          window.top.layer.close(index);
        });
      });
    },
    run: function() {
      // 新建日历modal初始化
      $('#addNewCalendarForm').modal({
        show: false
      });

      // 当日事件modal初始化
      $('#addNewEvent').modal({
        show: false
      });
      $('#editNewEvent').modal({
        show: false
      });

      this.handleEventList();
      this.handleFullcalendar();
      this.handleSelective();
      this.handleListItem();
    }
  });

  $(function() {
    window.App.run();
  });
})(document, window, jQuery);
