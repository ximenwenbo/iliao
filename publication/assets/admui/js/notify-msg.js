/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  /* global toastr, layer,template */

  /**
   * 消息通知
   */

  // 项目地址
  var ctx = $.configs.ctx;

  var notifyMsg = {
    init: function() {
      var self = this;
      var $msgDom = $('#admui-navbarMessage').find('span.msg-num');

      // 建立websocket连接
      var socket = new WebSocket('ws://' + window.location.host + ctx + '/socket');

      socket.onopen = function() {
        socket.send('发送数据');
      };

      socket.onmessage = function(event) {
        var fn = null;
        var allMsg = '';
        var msgFn = null;
        var msgNumber = 0;
        var unReadFn = window.notifyFn.unReadMsg;
        var data = JSON.parse(event.data);

        if (data.status) {
          // 初次链接时未读消息
          msgNumber = self.firstMsg(data.total, $msgDom);

          if (unReadFn && $.isFunction(unReadFn)) {
            unReadFn(msgNumber);
          }
          return;
        }

        fn = window.notifyFn.messagePage;
        msgFn = window.notifyFn.messageNum;
        allMsg = $msgDom.data('message') + 1;

        $msgDom.data('message', allMsg);

        // 未读消息数大于99时
        if (allMsg > 99) {
          // 未读消息数大于99时
          allMsg = '99+';
        }
        $msgDom.text(allMsg);

        toastr.options = {
          positionClass: 'toast-bottom-right',
          onclick: function() {
            self.readMsg(data);
          }
        };
        toastr.clear();
        toastr.info(data.title);

        if ($('#admui-navbarMessage ul').is(':visible')) {
          // 顶部导航条消息栏展开时
          self.loading();
        }

        if (fn && $.isFunction(fn)) {
          // 存在message页面时相关操作
          fn(self);
        }

        if (msgFn && $.isFunction(msgFn)) {
          // 存在账户信息页面时消息计数
          msgFn('1');
        }
      };

      socket.onclose = function(event) {
        toastr.info('消息通知服务已关闭', event);
      };
    },
    firstMsg: function(count, $el) {
      // 初次加载未读消息数
      var msgNumber;

      if (count === 0) {
        msgNumber = '';
      } else if (count > 99) {
        msgNumber = '99+';
      } else {
        msgNumber = count;
      }

      $el.data('message', count).text(msgNumber);
      return msgNumber;
    },
    readMsg: function(opts) {
      // 阅读当前消息，改变消息状态
      var $navNews = $('#admui-navbarMessage').find('span.msg-num');
      var msgsNum = Number($navNews.data('message'));

      toastr.clear();
      layer.alert(opts.content, {
        size: 'small',
        title: opts.title
      });

      if (opts.readFlag) {
        return;
      }

      $.ajax({
        url: ctx + '/message/' + opts.messageId,
        type: 'POST',
        dataType: 'JSON',
        success: function(data) {
          var msgNumber = 0;
          var fn = window.notifyFn.msgStatus;
          var msgFn = window.notifyFn.messageNum;

          if (data.success) {
            msgsNum -= 1;
            msgNumber = msgsNum;

            if (msgsNum > 99) {
              // 未读消息数
              msgNumber = '99+';
            } else if (msgNumber === 0) {
              msgNumber = '';
            }

            $navNews.data('message', msgsNum).text(msgNumber);

            if (fn && $.isFunction(fn)) {
              fn(opts.messageId);
            }

            if (msgFn && $.isFunction(msgFn)) {
              // 存在账户信息页面时消息计数
              msgFn();
            }
          } else {
            toastr.error('出错了，请重试！');
          }
        },
        error: function() {
          toastr.error('服务器异常，请稍后再试！');
        }
      });
    },
    render: function() {
      // 当前通知辅助渲染（图标&日期）
      var self = this;

      template.defaults.imports.iconType = function(type) {
        switch (type) {
          case 'SYSTEM':
            return 'fa-desktop system';
          case 'TASK':
            return 'fa-tasks task';
          case 'SETTING':
            return 'fa-cog setting';
          case 'EVENT':
            return 'fa-calendar event';
          default:
            return 'fa-comment-o other';
        }
      };

      template.defaults.imports.timeMsg = function(date) {
        var currentTime = new Date();
        // ios new Data兼容
        var arr = date.split(/[- :/]/);
        var msgTime = new Date(arr[0], arr[1] - 1, arr[2], arr[3], arr[4], arr[5]);

        return self.timeDistance(msgTime, currentTime);
      };
    },
    loading: function() {
      // 加载最近5条通知
      this.render();

      $.ajax({
        url: ctx + '/message/messages',
        type: 'GET',
        dataType: 'JSON',
        data: {pageIndex: 1, pageSize: 5, data: {readFlag: 0}},
        contentType: 'application/json;charset=utf-8',
        success: function(res) {
          if (res && typeof res.pageList !== 'undefined') {
            $('#admui-messageContent').html(template('admui-messageTpl', res));
          } else {
            toastr.error(res.msg);
          }
        },
        error: function() {
          toastr.error('服务器异常，请稍后再试！');
        }
      });
    },
    timeDistance: function(reference, current) {
      // 间隔时间
      var time = current.getTime() - reference.getTime();
      var i = 0;

      for (; i < 6; i += 1) {
        switch (i) {
          case 0:
            time /= 1000;
            if (time >= 1 && time < 60) {
              return time.toFixed(0) + '秒前';
            } else if (time < 1) {
              return '刚刚';
            }
            break;
          case 1:
            time /= 60;
            if (time >= 0 && time < 60) {
              return time.toFixed(0) + '分钟前';
            }
            break;
          case 2:
            time /= 60;
            if (time >= 0 && time < 60) {
              return time.toFixed(0) + '小时前';
            }
            break;
          case 3:
            time /= 24;
            if (time >= 0 && time < 60) {
              return time.toFixed(0) + '天前';
            }
            break;
          case 4:
            time /= 30;
            if (time >= 0 && time < 60) {
              return time.toFixed(0) + '月前';
            }
            break;
          case 5:
            time /= 365;
            if (time >= 0 && time < 60) {
              return time.toFixed(0) + '年前';
            }
            break;
          default:
            break;
        }
      }

      return undefined;
    }
  };

  window.notifyFn = notifyMsg;

  // 需配合后端使用
  notifyMsg.init();

  // 获取消息
  $('#admui-navbarMessage > .msg-btn').on('click', function() {
    notifyMsg.loading();
  });

  // 查看未读消息
  $(document).on('click', '#admui-messageContent > a', function(e) {
    var opts = $(this).data();

    notifyMsg.readMsg(opts);
    e.preventDefault();
  });
})(window, document, jQuery);
