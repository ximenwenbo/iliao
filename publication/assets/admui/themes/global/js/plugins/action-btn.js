/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  var pluginName = 'actionBtn';

  var Plugin = function(element, options) {
    var $element = $(element);

    // 调用该方法的元素
    this.$element = $element;
    this.options = $.extend({}, Plugin.defaults, options, $element.data());

    // 初始化
    this.init();
  };

  Plugin.defaults = {
    trigger: 'click', // click, hover
    toggleSelector: '.site-action-toggle', // 切换按钮选择器
    listSelector: '.site-action-buttons', // 下拉内容选择器
    activeClass: 'active',
    showed: false, // 默认为隐藏状态
    onShow: function(element) {
      // 显示完成后触发方法
      // do Something
      element.html();
    },
    onHide: function(element) {
      // 隐藏完成后触发方法
      // do Something
      element.html();
    }
  };

  Plugin.prototype = {
    constructor: Plugin,
    init: function() {
      var self = this;
      var options = this.options;
      var toggleSelector = options.toggleSelector;
      var $element = this.$element;

      this.$toggle = $element.find(toggleSelector);
      this.$list = $element.find(options.listSelector);

      // 触发方式为hover(默认为：click)
      if (options.trigger === 'hover') {
        $element.on('mouseenter', toggleSelector, function() {
          !self.showed && self.show();
        });

        $element.on('mouseleave', toggleSelector, function() {
          self.showed && self.hide();
        });
        return;
      }

      $element.on('click', toggleSelector, function() {
        if (self.showed) {
          self.hide();
        } else {
          self.show();
        }
      });
    },
    show: function() {
      var $element = this.$element;
      // 状态为隐藏时显示内容
      if (!this.showed) {
        $element.addClass(this.options.activeClass);
        this.showed = true;

        // 触发显示完成方法
        this.options.onShow.call(this, $element);
      }
    },
    hide: function() {
      var $element = this.$element;

      // 状态为显示时隐藏内容
      if (this.showed) {
        $element.removeClass(this.options.activeClass);
        this.showed = false;

        // 触发隐藏完成函数
        this.options.onHide.call(this, $element);
      }
    }
  };

  $.fn[pluginName] = function(options) {
    var method = options;
    var api = this.first().data(pluginName);
    var methodArguments = Array.prototype.slice.call(arguments, 1);

    // 参数为字符串时触发方法
    if (typeof options === 'string') {
      // 禁止调用私有方法
      if (/^_/.test(method)) {
        console.error('No such method : ' + options);
      }

      if (/^(get)$/.test(method) && api && typeof api[method] === 'function') {
        return api[method].apply(api, methodArguments);
      }

      return this.each(function() {
        var instance = $.data(this, pluginName);

        if (instance && typeof instance[method] === 'function') {
          instance[method].apply(instance, methodArguments);
        }
      });
    }

    // 初始化对象实例
    return this.each(function() {
      if (!$.data(this, pluginName)) {
        $.data(this, pluginName, new Plugin(this, options));
      }
    });
  };
})(window, document, jQuery);
