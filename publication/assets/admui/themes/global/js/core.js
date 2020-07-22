/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  /* global _ */

  // 配置基本信息
  $.configs = $.configs || {};

  $.extend($.configs, {
    _data: {},
    get: function() {
      // 获取config信息
      var data = this._data;
      var i = 0;
      var key = '';

      // 获取对象及子对象中的配置信息
      for (; i < arguments.length; i += 1) {
        key = arguments[i];
        if (_.isObject(data)) {
          data = data[key];
        } else {
          console.error('没有配置该信息');
          return null;
        }
      }

      return data;
    },
    set: function(name, value) {
      // 设置config信息
      this._data[name] = value;
    },
    extend: function(name, options) {
      // 扩展当前configs对象
      return $.extend(true, this.get(name), options);
    }
  });

  // 实现插件的data-Api注册使用（提前检测和调用）
  $.components = $.components || {};

  $.extend($.components, {
    _components: {},
    register: function(name, obj) {
      // 注册自定义组件
      this._components[name] = obj;
    },
    init: function() {
      // 参数[name, [global]]
      var self = this;
      var obj;
      var name = arguments[0];
      var context = arguments[1] || window;

      // 仅有一个参数且为Object类型时重置name和context值
      if (arguments.length === 1 && _.isObject(arguments[0])) {
        name = undefined;
        context = arguments[0];
      }

      // 没有组件名称时遍历初始化所有组件
      if (_.isUndefined(name)) {
        $.each(this._components, function(key) {
          self.init(key, context);
        });
        return;
      }

      // 获取组件定义对象
      obj = this.get(name);

      // 根据mode类型执行对应的定义方法
      switch (obj.mode) {
        case 'default':
          this._initDefault(name, context);
          break;
        case 'init':
          this._initComponent(obj, context);
          break;
        case 'api':
          this._initApi(obj, context);
          break;
        default:
          this._initApi(obj, context);
          this._initComponent(obj, context);
      }
    },
    _initDefault: function(name, global) {
      // jquery插件以$.fn方式的基本用法
      var defaults;

      if (_.isUndefined(global.$.fn[name])) {
        return;
      }

      // 获取自定义配置参数
      defaults = this.getDefaults(name);

      // 遍历需使用该组件方法元素进行初始化
      $('[data-plugin=' + name + ']', global.document).each(function() {
        var $item = global.$(this);

        $item[name]($.extend(true, {}, defaults, $item.data()));
      });
    },
    _initComponent: function(obj, global) {
      // jquery插件的高级用法（根据具体需求额外配置的初始化方法,可以重复调用）
      _.isFunction(obj.init) && obj.init.call(obj, global);
    },
    _initApi: function(obj, global) {
      // 其他处理（对一些特殊插件根据具体需求额外配置的初始化方法，仅能调用一次）
      var opt = obj;

      // 全局对象不是当前window对象时
      if (global !== window && _.isFunction(opt.api)) {
        opt.api.call(opt, global);
        // 标记为已完成一次调用
        opt.apiCalled = true;
      }

      // 尚未初始化 & opt.api是function类型时调用该方法
      if (!opt.apiCalled && _.isFunction(opt.api)) {
        opt.api.call(opt, global);
        // 标记为已完成一次调用
        opt.apiCalled = true;
      }
    },
    getDefaults: function(name) {
      // 获取自定义组件默认配置参数
      return this.get(name).defaults || {};
    },
    get: function(name) {
      // 没有发现注册的自定义组件时返回错误信息
      if (_.isUndefined(this._components[name])) {
        console.error('没有 component:' + name + ' 的任何注册信息！');
      }
      return this._components[name];
    }
  });

  // 配置基本信息
  $.configs = $.configs || {};

  $.extend($.configs, {
    _data: {},
    get: function() {
      // 获取config信息
      var data = this._data;
      var i = 0;
      var key = '';

      // 获取对象及子对象中的配置信息
      for (; i < arguments.length; i += 1) {
        key = arguments[i];
        if (_.isObject(data)) {
          data = data[key];
        } else {
          console.error('没有配置该信息');
          return null;
        }
      }

      return data;
    },
    set: function(name, value) {
      // 设置config信息
      this._data[name] = value;
    },
    extend: function(name, options) {
      // 扩展当前configs对象
      return $.extend(true, this.get(name), options);
    }
  });

  // 获取颜色配置信息
  $.getColor = function(name, level) {
    if (_.isUndefined($.configs.colors)) {
      console.error('不存在颜色配置对象，请检查配置文件');
    }

    if (_.isUndefined($.configs.colors[name])) {
      return undefined;
    }

    // 有level参数时根据level返回颜色值
    if (level && !_.isUndefined($.configs.colors[name][level])) {
      return $.configs.colors[name][level];
    }

    return $.configs.colors[name];
  };

  // 获取定义组件默认参数
  $.concatCpt = function(name, options) {
    return $.extend(true, {}, $.components.getDefaults(name), options);
  };

  // 本地存储对象操作 sessionStorage
  $.sessionStorage = $.sessionStorage || {};

  $.extend($.sessionStorage, {
    set: function(key, value) {
      var data = value;
      // 设置本地seesionStorage存储内容
      if (_.isUndefined(window.sessionStorage)) {
        console.error('该浏览器不支持sessionStorage对象');
      }

      // 没有参数时不存储任何内容
      if (arguments.length === 0) {
        return;
      }

      // 没有存储值时默认为空字符串
      if (_.isUndefined(data)) {
        data = '';
      }

      // 以字符串类型存储数据
      if (_.isObject(data)) {
        data = JSON.stringify(data);
      }

      sessionStorage.setItem(key, data);
    },
    get: function(key) {
      // 获取存储内容
      var value;

      if (!sessionStorage) {
        console.error('该浏览器不支持sessionStorage对象');
      }

      value = sessionStorage.getItem(key);

      // 不存在该存储内容
      if (!value) {
        return null;
      }

      return JSON.parse(value);
    },
    remove: function(key) {
      // 删除指定缓存
      if (!sessionStorage) {
        console.error('该浏览器不支持sessionStorage对象');
      }

      sessionStorage.removeItem(key);
    }
  });

  function dataAttr(elem, key, data, iJquery) {
    var rbrace = /^(?:\{[\w\W]*\}|\[[\w\W]*\])$/;
    var rmultiDash = /([A-Z])/g;

    // If nothing was found internally, try to fetch any
    // data from the HTML5 data-* attribute
    if (data === undefined && elem.nodeType === 1) {
      var name = 'data-' + key.replace(rmultiDash, '-$1').toLowerCase();

      data = elem.getAttribute(name);

      if (typeof data === 'string') {
        try {
          data =
            data === 'true'
              ? true
              : data === 'false'
                ? false
                : data === 'null'
                  ? null
                  : // Only convert to a number if it doesn't change the string
                    +data + '' === data
                    ? +data
                    : rbrace.test(data)
                      ? $.parseJSON(data)
                      : data;
        } catch (e) {}

        // Make sure we set the data so it isn't changed later
        iJquery.data(elem, key, data);
      } else {
        data = undefined;
      }
    }

    return data;
  }

  $.fn.extend({
    data: function(key, value, iJquery) {
      var i;
      var name;
      var data;
      var elem = this[0];
      var attrs = elem && elem.attributes;

      // Special expections of .data basically thwart jQuery.access,
      // so implement the relevant behavior ourselves

      // Gets all values
      if (key === undefined || $.isFunction(key)) {
        iJquery = key || $;

        if (this.length) {
          data = iJquery.data(elem);

          if (elem.nodeType === 1 && !iJquery._data(elem, 'parsedAttrs')) {
            i = attrs.length;
            while (i--) {
              // Support: IE11+
              // The attrs elements can be null (#14894)
              if (attrs[i]) {
                name = attrs[i].name;
                if (name.indexOf('data-') === 0) {
                  name = iJquery.camelCase(name.slice(5));
                  dataAttr(elem, name, data[name], iJquery);
                }
              }
            }
            iJquery._data(elem, 'parsedAttrs', true);
          }
        }

        return data;
      }

      if ($.isFunction(value) && value.fn.jquery) {
        return elem ? dataAttr(elem, key, value.data(elem, key), value) : undefined;
      }

      // Sets multiple values
      if (typeof key === 'object') {
        return this.each(function() {
          value.data(this, key);
        });
      }

      iJquery = iJquery || $;

      return arguments.length > 1
        ? // Sets one value
          this.each(function() {
            iJquery.data(this, key, value);
          })
        : // Gets one value
          // Try to fetch any internally stored data first
          elem
          ? dataAttr(elem, key, iJquery.data(elem, key), iJquery)
          : undefined;
    }
  });
})(window, document, jQuery);
