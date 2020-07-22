/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  /* global _ */

  var $content;
  var $navContabs;
  var $instance;
  var defaultTabOpts;
  var storageKey = $.configs.get('tabSittings');

  /** *
   * 左侧菜单相关事件功能（默认为折叠效果）
   *
   * 提供了多个可触发事件：
   *  deactive.site.menu : 取消菜单选中项
   *  active.site.menu : 设置菜单选中项
   *  open.site.menu : 展开子菜单
   *  close.site.menu : 收起子菜单
   *
   *  可自定义事件：
   *  expanded.site.menu : 子菜单展开后动作
   *  collapsed.site.menu : 子菜单收起后动作
   *
   *
   *  方法：
   *  enable : 定位系统菜单方法
   * * */

  // 更新标签信息存储对象
  var updateSetting = function(item, value) {
    var settings = $.sessionStorage.get(storageKey);
    settings = settings || {};

    switch (arguments.length) {
      case 1:
        if (_.isObject(item)) {
          // 更新存储对象
          $.extend(true, settings, item);
        } else {
          // 删除存储对象中某一项
          delete settings[item];
        }
        break;
      case 2:
        // 更新存储对象中某一项
        settings[item] = value;
        break;
      default:
        break;
    }

    // 设置存储信息
    $.sessionStorage.set(storageKey, settings);
  };

  // 存储默认标签的信息
  var defaultTab = function() {
    var $defaultTab;
    var settings = $.sessionStorage.get(storageKey);

    // 本地存储对象中没有值时存储默认标签信息
    if (_.isNull(settings)) {
      $defaultTab = $navContabs.find('li:first > a');
      // 配置默认标签参数
      defaultTabOpts = {
        name: $.trim($defaultTab.attr('title')),
        url: $.trim($defaultTab.attr('href'))
      };
      settings = $.extend(
        true,
        {},
        {
          'frame-0': defaultTabOpts,
          checked: $defaultTab.attr('target'),
          tabId: 0
        }
      );

      updateSetting(settings);
    }
  };

  // 创建frame窗口
  var buildFrame = function(option) {
    var frameName = option.name;
    var frameUrl = option.url;
    var checkedClass = frameUrl === '' ? '' : ' active';

    // 修改选中元素 & 创建frame元素
    frameUrl && $content.children('.active').removeClass('active');
    $content.append(
      '<iframe src="' +
        frameUrl +
        '" frameborder="0" name="' +
        frameName +
        '" class="page-frame animation-fade' +
        checkedClass +
        '"></iframe>'
    );

    // 为选中框架绑定事件
    frameUrl && $.site.frameEvents($content.find('iframe.active'));
  };

  // 切换标签页
  var checkoutTab = function($el, opts) {
    var $tab = $el.find('a');
    var tabId = $tab.attr('target');
    var tabName = $.trim($tab.attr('title'));
    var tabUrl = opts ? opts.url : $tab.attr('href');
    var $checkedTab = $content.find('> iframe[name="' + tabId + '"]');

    // 当前方法不是浏览器历史记录后退功能触发时添加浏览器历史记录
    if (!opts || opts.type !== 'history') {
      window.history.pushState(
        {
          name: tabName,
          url: tabUrl
        },
        '',
        '#!' + tabUrl
      );
    }

    // 点击不是系统菜单时调用系统菜单定位方法
    $.site.contentTabs.enable($el);

    // 更新frame选中项
    $content.find('> .active').removeClass('active');
    $checkedTab.addClass('active');

    // 更新标签页选中项
    updateSetting('checked', tabId);

    if (!$checkedTab.attr('src')) {
      // frame框架未加载时
      // 为当前frame添加地址进行加载
      $checkedTab.attr('src', tabUrl);
      // 为当前选中frame绑定事件
      $.site.frameEvents($checkedTab);
    } else {
      // frame框架已加载时
      // 标签页地址不同时刷新页面
      if ($tab.attr('href') !== tabUrl) {
        $.site.contentTabs.reloadTab(tabUrl);
        return;
      }

      // 更新主框架页面title
      $('title', window.top.document).text($('title', $.contentFrame.document()).text());
    }
  };

  // 标签页查重
  var checkTab = function($el, opts) {
    var x;
    var prevAll;
    var nextAll;
    var contentW;
    var contentTabs = $.site.contentTabs;
    var view = contentTabs.view;
    var tab = contentTabs.tabWidth;
    var tabUrl = opts.url;
    var result = tabUrl.indexOf('?');
    var url = result > 0 ? tabUrl.substring(0, result) : '';
    var $currentTab = $el.find("a[href='" + tabUrl + "']").closest('li');

    // 标签不存在 - 根据href寻找（主地址）
    if ($currentTab.length === 0) {
      if (url !== '') {
        // 有data-url地址时
        $currentTab = $el.find("a[data-url='" + url + "']").closest('li');
      }

      if ($currentTab.length === 0) {
        // 标签不存在 - 根据data-url寻找（主地址查询参数）
        return false;
      }

      // 标签存在 & 已选中 - 根据data-url寻找（主地址查询参数）
      if ($currentTab.hasClass('active')) {
        // 刷新当前页面
        $.site.contentTabs.reloadTab(tabUrl);
        return true;
      }
    }

    // 标签存在 & 已选中
    if ($currentTab.hasClass('active')) {
      return true;
    }

    // 标签存在未选中
    $el.find('li.active').removeClass('active');
    $currentTab.addClass('active');

    // 切换标签页
    checkoutTab($currentTab, opts);

    // 标签位移到可视界面显示
    x = $el.position().left;
    contentW = $el.width();
    prevAll = $currentTab.prevAll('li').length * tab;
    nextAll = $currentTab.nextAll('li').length * tab;

    if (-prevAll < x) {
      if (prevAll + x < view) {
        return true;
      }

      x = -(prevAll - view + tab);
    } else {
      if (-x < contentW - nextAll) {
        return true;
      }

      x = -(contentW - nextAll - tab);
    }

    $el.animate(
      {
        left: x
      },
      100
    );

    return true;
  };

  // 函数节流操作
  var throttle = function(fn, interval) {
    var timer;
    var firstTime = true;
    return function() {
      var args = arguments;
      var self = this;

      if (firstTime) {
        fn.apply(self, args);
        firstTime = false;
      }

      if (timer) {
        return;
      }

      timer = setTimeout(function() {
        clearTimeout(timer);
        timer = null;
        fn.apply(self, args);
      }, interval || 500);
    };
  };

  // 监听浏览器历史记录改变
  window.addEventListener(
    'popstate',
    function() {
      var state = window.history.state;
      // 转向指定的URL

      if (state === null) {
        state = defaultTabOpts;
      }
      $.extend(state, {type: 'history'});
      $.site.contentTabs.buildTab(state);
    },
    false
  );

  $.site.contentTabs = {
    relative: 0,
    tabTimeout: 30,
    init: function() {
      $content = $('#admui-pageContent');
      $navContabs = $('#admui-siteConTabs');
      $instance = $('#admui-navTabs .site-menu');

      this.bind();
      defaultTab();
    },
    bind: function() {
      var self = this;
      var $navContent = $navContabs.find('ul.con-tabs');

      this.tabWidth = $navContent.find('li').width();
      this.view = $navContabs.find('.contabs-scroll').width();

      this.ifameTabs();

      // 标签页的左右移动  &&  关闭单个标签页  && 切换标签页
      $navContabs
        .on('click.site.contabs', '#admui-tabL', function() {
          self.tabPosition($navContent, self.tabWidth, 'right');
        })
        .on('click.site.contabs', '#admui-tabR', function() {
          var content = $navContent.width();

          self.tabPosition($navContent, self.tabWidth, 'left', self.view, content);
        })
        .on('click.site.contabs', 'ul.con-tabs>li', function(e) {
          var $target = $(e.target);
          var $item = $(this);

          if ($target.is('i.wb-close-mini')) {
            // 关闭
            self.closeTab($item.find('a').attr('href'));
          } else if (!$item.is('.active')) {
            // 切换
            $item.siblings('li').removeClass('active');
            $item.addClass('active');

            checkoutTab($item);
          }
          return false;
        });

      // 刷新当前页 && 关闭其他 && 所有标签页
      $navContabs
        .on('click.site.contabs', '.reload-page', function() {
          // 刷新当前页
          self.reloadTab();
        })
        .on('click.site.contabs', '.close-other', function() {
          var $navLis = $navContabs.find('ul.con-tabs>li');

          $navLis.each(function() {
            var $that = $(this);
            var tabId;

            if (!$that.is('.active') && $that.index() !== 0) {
              tabId = $that.find('a').attr('target');
              $that.remove();
              $content.children('[name="' + tabId + '"]').remove();
              updateSetting(tabId);
            }
          });

          $navContent.animate({left: 0}, 100);

          self.btnView('hide');
        })
        .on('click.site.contabs', '.close-all', function() {
          var $tabs = $navContabs.find('ul.con-tabs>li');
          var $checked = $tabs.eq(0);

          $tabs.each(function() {
            var $that = $(this);
            var tabId;

            if ($that.index() !== 0) {
              tabId = $that.find('a').attr('target');
              $that.remove();
              updateSetting(tabId);
            }
          });

          $navContent.animate({left: 0}, 100);

          self.btnView('hide');

          $checked.addClass('active');
          checkoutTab($checked);

          // 关闭所有
          $content.children(':not(:first)').remove();

          self.tabSize();
        });

      // 登出清楚标签页缓存
      $(document).on('click', '#admui-signOut', function() {
        $.sessionStorage.remove(storageKey);
      });

      // 浏览器窗口大小改变,标签页的对应状态
      $(window).on('resize', this.resize);
    },
    ifameTabs: function(el) {
      // 为当前doc对象中的a标签绑定创建标签页功能
      var self = this;
      var doc = el || document;
      var linkResult = /^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i;

      // 新建标签页
      $(doc).on('click', 'a', function(e) {
        var $item = $(this);
        var url = $.trim($item.attr('href'));
        var tabOpts;
        var settings;
        var checked;
        var opts;

        // 有url地址时可以创建自定义标签
        if (url !== '') {
          // url是无效地址时；url是外链，[data-frame="false"]或者没有data-frame属性时；url符合要求，[data-frame="false"]时阻止创建自定义标签
          if (
            /^(#|javascript)/i.test(url) ||
            (linkResult.test(url) && !$item.data('frame')) ||
            $item.data('frame') === false
          ) {
            return;
          }

          // 配置标签页信息
          tabOpts = {
            name: $.trim($item.attr('title') || $item.data('title') || $item.text() || '加载中...'),
            url: url
          };

          // 不创建新标签时刷新当前标签页
          if ($item.attr('target') !== '_blank') {
            settings = $.sessionStorage.get(storageKey);
            checked = settings.checked;
            opts = settings[checked];

            // 更新当前标签页title
            $navContabs
              .find('li.active>a')
              .attr('title', tabOpts.name)
              .find('>span')
              .text(tabOpts.name);

            // 更新当前标签页地址
            $content.find('iframe.active').attr('src', url);
            $.site.frameEvents($content.find('> iframe.active'));

            // 更新本地存储信息
            updateSetting(checked, $.extend(opts, tabOpts));

            return;
          }

          // 添加浏览器历史记录
          window.history.pushState(tabOpts, '', '#!' + url);

          self.buildTab(tabOpts);

          // 点击不是系统菜单时调用系统菜单定位方法
          $instance.find($item).length === 0 && self.enable($item.parent());
        }

        e.preventDefault();
      });
    },
    enable: function($el) {
      var href = $.trim($el.find('a').attr('href'));
      var result = href.indexOf('#');
      var tabUrl = result > 0 ? href.substring(0, result) : href;
      var $current = $instance.find('a[href="' + tabUrl + '"]');
      var $instanceLi;
      var currentId;
      var menuExpand = function($item) {
        if ($item.length > 0) {
          $item.trigger('open.site.menu');
          menuExpand($item.parent().closest('li.has-sub'));
        }
      };
      // 增加router属性地址扩展定位
      var routeFilter = function() {
        var flag = false;

        $instance.find('a').each(function() {
          var $item = $(this);
          var router = $item.data('router');

          // [data-router="/examples/charts/chart1.html, /examples/charts/chart2.html"]
          if (typeof router !== 'undefined') {
            $.each(router.split(','), function(i, n) {
              if (n === tabUrl) {
                $current = $item;
                flag = true;
              }
            });
          }
        });
        return flag;
      };

      // 系统菜单中没有发现该菜单
      if ($current.length === 0 && !routeFilter()) {
        $.site.menu.refresh();
        return;
      }

      // 切换到url指向的系统菜单面板
      currentId = $.trim($current.closest('.tab-pane').attr('id'));

      // 切换到当前地址对应系统菜单面板
      if ($.trim($instance.closest('.tab-pane.active').attr('id')) !== currentId) {
        $('#admui-navMenu a[href="#' + currentId + '"]').tab('show');
      }

      $instanceLi = $instance.find('li.open');

      // 删除其他菜单选中类，为当前菜单添加选中类
      $instance.find('li.active').trigger('deactive.site.menu');
      $current.closest('li').trigger('active.site.menu');

      // 当前菜单父菜单没有展开时
      if (!$current.closest('li.has-sub').hasClass('open')) {
        // 收起当前面板之外的展开菜单
        if ($instanceLi.length > 0) {
          $instanceLi.not($current.parents('li.has-sub')).trigger('close.site.menu');
        }

        // 展开父菜单
        menuExpand($current.closest('li.has-sub'));
      }
    },
    resize: function() {
      var self = $.site.contentTabs;
      var $navContent = $navContabs.find('ul.con-tabs');

      throttle(function() {
        self.view = $navContabs.find('.contabs-scroll').width();
        self.tabEvent($navContent, 'media');
      }, 200)();
    },
    buildTab: function(opts, store) {
      // 新建标签页 [store]默认值为'on'，可配置[on|off]
      var $tabNav = $('.con-tabs');
      var tabName;
      var checkedClass = '';
      var obj = {};
      var text;
      var tabId;
      var tabUrl;
      var result;
      var url;
      var frameUrl = '';

      // 标签页查重
      if (checkTab($tabNav, opts)) {
        return;
      }

      // 设置创建标签信息
      text = opts.name;
      tabUrl = opts.url;
      result = tabUrl.indexOf('?');
      url = result > 0 ? tabUrl.substring(0, result) : '';

      // 存储状态默认为true，存储新建标签信息
      if (_.isUndefined(store) || store === 'on') {
        // 设置创建标签信息
        tabId = $.sessionStorage.get(storageKey).tabId;
        tabId += 1;
        tabName = 'frame-' + tabId;
        checkedClass = 'active';
        frameUrl = tabUrl;

        obj = {
          checked: tabName,
          tabId: tabId
        };

        obj[tabName] = {
          url: tabUrl,
          name: opts.name
        };

        updateSetting(obj);
        // 修改当前选中的标签页
        $tabNav.find('li.active').removeClass('active');
      } else if (store === 'off') {
        // 更新标签名称
        tabName = opts.index;
      }

      // 创建标签页
      $tabNav.append(
        '<li class="' +
          checkedClass +
          '"><a href="' +
          tabUrl +
          '" data-url="' +
          url +
          '" target="' +
          tabName +
          '" title="' +
          text +
          '' +
          '" rel="contents"><span>' +
          text +
          '</span><i class="icon' +
          ' wb-close-mini">' +
          '</i></a></li>'
      );

      buildFrame({name: tabName, url: frameUrl});
      this.tabSize();
      this.tabEvent($tabNav, 'media');
    },
    tabSize: function() {
      // 修改标签页盒子尺寸
      var content;
      var $tabNav = $('.con-tabs');
      var num = $tabNav.find('li').length;

      content = this.tabWidth * num;
      $tabNav.css('width', content);
    },
    tabEvent: function(doc, media) {
      // 增删标签页的对应状态
      var content = $('.con-tabs').width();
      var view = this.view;
      var tab = this.tabWidth;

      if (content > this.view) {
        this.tabPosition(doc, tab, 'left', view, content, media);
        this.btnView('visible');
      } else {
        this.btnView('hide');
      }

      if (this.currentView < view || this.currentContent > content) {
        this.tabPosition(doc, tab, 'right', view, content, media);
      }
      this.currentView = this.view;
      this.currentContent = content;
    },
    tabPosition: function(doc, width, dir, view, content, media) {
      // 标签页的位移
      var self = this;
      var x = doc.position().left;
      var callback = function(n) {
        var flag = n + width;

        if (flag > 0) {
          self.relative = n;
          return 0;
        }
        return n;
      };

      if (dir === 'left') {
        if (x <= view - content) {
          return;
        }
        if (typeof media !== 'undefined') {
          x = view - content;
        } else {
          x = this.relative !== 0 ? x - width + this.relative : x - width;
          this.relative = 0;
        }
      } else if (dir === 'right') {
        if (x === 0) {
          return;
        }

        if (typeof media !== 'undefined') {
          x = content <= view ? 0 : view - content;
        } else {
          x = callback(x + width);
        }
      }

      doc.animate(
        {
          left: x
        },
        100
      );
    },
    closeTab: function(url) {
      // 默认关闭当前选中标签页;使用地址参数关闭指向的标签页
      var $conTabs = $navContabs.find('ul.con-tabs');
      var $current = !_.isUndefined(url)
        ? $conTabs.find('a[href="' + url + '"]').parent()
        : $conTabs.find('>li.active');
      var checkedTabId = $current.children('a').attr('target');
      var $checked = '';
      var $nextLi = $current.next('li');

      // 关闭选中标签
      if ($current.is('.active')) {
        if ($nextLi.length > 0) {
          $checked = $nextLi;
        } else {
          $checked = $current.prev('li');
        }

        $checked.addClass('active');
        checkoutTab($checked);
      }

      $current.remove();
      $content.children('[name="' + checkedTabId + '"]').remove();
      updateSetting(checkedTabId);

      this.tabSize();
      this.tabEvent($('.con-tabs'), 'media');
    },
    reloadTab: function(url) {
      var settings = $.sessionStorage.get(storageKey);
      var checked = settings.checked;
      var opts = settings[checked];

      var tabUrl = url || $navContabs.find('ul.con-tabs>li.active>a').attr('href');

      // 更新当前标签页地址
      $content.find('iframe.active').attr('src', tabUrl);
      $.site.frameEvents($content.find('> iframe.active'));

      // 更新本地存储信息
      opts.url = tabUrl;
      updateSetting(checked, opts);
    },
    btnView: function(status) {
      // 标签页左右移动按钮状态
      var $contabsLeftBtn = $('#admui-tabL');
      var $contabsRightBtn = $('#admui-tabR');

      if (status === 'visible') {
        $contabsLeftBtn.removeClass('hide');
        $contabsRightBtn.removeClass('hide');
      } else if (status === 'hide') {
        $contabsLeftBtn.addClass('hide');
        $contabsRightBtn.addClass('hide');
      }
    }
  };
})(window, document, jQuery);
