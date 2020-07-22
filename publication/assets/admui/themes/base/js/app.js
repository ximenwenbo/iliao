/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  /* global _ */
  /* eslint no-underscore-dangle: ["error", { "allowAfterThis": true, "allow": ["_trigger", "_dequeue"] }] */

  var mainFrame = window.top;
  var app;

  // 配置基本信息
  $.configs = mainFrame.$.configs;

  // 获取颜色配置信息
  $.getColor = mainFrame.$.getColor;

  // 获取定义组件默认参数
  $.concatCpt = mainFrame.$.concatCpt;

  // 本地存储对象操作
  $.storage = mainFrame.$.storage;
  $.sessionStorage = mainFrame.$.sessionStorage;

  // 网址基础设置对象
  $.site = mainFrame.$.site;

  $.fn.serializeObject = function() {
    var obj = {};
    var array = this.serializeArray();

    $(array).each(function() {
      var name = this.name;
      var value = this.value;

      if (obj[name]) {
        if ($.isArray(obj[name])) {
          obj[name].push(value);
        } else {
          obj[name] = [obj[name], value];
        }
      } else {
        obj[name] = value;
      }
    });
    return obj;
  };

  // 实现插件的data-Api注册使用（提前检测和调用）
  $.components = mainFrame.$.components;

  // 注册组件初始化
  $.components.init(window);

  // 为当前document对象中的a标签绑定创建标签页功能
  $.site.contentTabs.ifameTabs(document);

  // 公用对象
  window.Breakpoints = mainFrame.Breakpoints;
  window.toastr = mainFrame.toastr;
  window.notifyFn = mainFrame.$.notifyFn;

  // 自定义功能模块队列扩展（每个功能模块为一个成员）
  $.queueModal = $.queueModal || {};

  $.extend($.queueModal, {
    _queue: {
      prepare: [],
      run: [],
      complete: []
    },
    run: function() {
      var self = this;

      // 运行prepare队列项，完成后触发before.run事件
      this._dequeue('prepare', function() {
        self._trigger('before.run', self);

        // 运行run队列项，完成后触发after.run事件
        self._dequeue('run', function() {
          self._dequeue('complete', function() {
            self._trigger('after.run', self);
          });
        });
      });
    },
    _dequeue: function(name, done) {
      // 获取当前队列成员，执行该成员包含动作
      var self = this;
      // 获取该项队列中方法
      var fn = this.getQueue(name).shift();

      // 该项队列中有数据时继续执行队列中方法
      if (!_.isUndefined(fn)) {
        $.when(fn.call(self)).then(function() {
          self._dequeue(name, done);
        });
      } else if (_.isFunction(done)) {
        // 该项队列中没有数据 & done为function类型时执行该方法
        done.call(self);
      }
    },
    getQueue: function(name) {
      // 获取该项队列数据
      var result = false;
      // 遍历查询是否具有该队列项
      $.each(this._queue, function(key) {
        if (key === name) {
          result = true;
        }
      });

      // 没有该队列项时返回错误信息
      if (!result) {
        console.error('该队列项不存在');
      }

      return this._queue[name];
    },
    extend: function(obj) {
      // 公用模块对象队列扩展方法
      var opt = obj;

      // 遍历队列对象
      $.each(this._queue, function(name, queue) {
        // 自定义对象中有队列项并且为方法时将其推入队列，删除自定义对象中方法
        if (_.isFunction(opt[name])) {
          queue.unshift(opt[name]);

          delete opt[name];
        }
      });

      // 将剩余自定义对象合并到主对象中
      $.extend(this, opt);
      return this;
    },
    _trigger: function(name, obj) {
      // 队列阶段性完成时执行动作
      if (_.isUndefined(name)) {
        return;
      }

      // 触发绑定事件
      $(document).trigger(name + '.app', obj);
    }
  });

  // 公有方法配置
  app = {
    pageAside: function() {
      // 小屏幕下侧边栏（展开&收起）操作
      var pageAside = $('.page-aside');
      var isOpen = pageAside.hasClass('open');

      pageAside.toggleClass('open', !isOpen);
    },
    getOperPermission: function() {
      var path = window.location.pathname;
      var result;

      $.ajax({
        url: $.configs.ctx + '/public/data/system/operation/operauths.json',
        type: 'GET',
        async: false,
        data: {requestUrl: path},
        dataType: 'JSON',
        success: function(res) {
          if (res.success) {
            result = res.data;
          } else {
            console.warn(res.msg);
          }
        },
        error: function() {
          console.log('服务器异常，请配合后端程序使用');
        }
      });
      return result;
    },
    run: function() {
      var self = this;

      // 侧边栏开关
      $(document).on('click', '.page-aside-switch', function(e) {
        self.pageAside();
        e.stopPropagation();
      });
    }
  };

  // 公有属性和方法（可扩展）
  window.App = $.queueModal.extend(app);
})(window, document, jQuery);
