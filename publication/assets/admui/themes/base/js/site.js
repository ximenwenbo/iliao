/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  /* global _, screenfull, Breakpoints */

  var $html;
  var $body;
  var $siteMenubar;
  var $navMenu;
  var $navbarCollapse;
  var $conTabs;
  var $content;
  var themeColor;

  // 框架皮肤渲染
  var frameSkin = function(frame) {
    var context = frame || document;
    var theme = $body.data('theme');
    var $link = $('#admui-siteStyle', context);
    var etx = $link.prop('href').indexOf('?v=') === -1 ? '' : '.min';

    // themeColor空或者为默认皮肤时
    if (!themeColor || themeColor === 'primary') {
      return;
    }

    if (!frame) {
      setTimeout(function() {
        $link.attr(
          'href',
          '/public/themes/' + theme + '/css/skins/' + themeColor + '/index' + etx + '.css'
        );
      }, 100);
    } else {
      $link.attr(
        'href',
        '/public/themes/' + theme + '/css/skins/' + themeColor + '/site' + etx + '.css'
      );
    }
  };

  // 主题渲染
  var theme = function() {
    var sidebar;
    var navbar;
    var menuDisplay;
    var menuTxtIcon;

    // 获取存储的显示设置
    var settings = $.sessionStorage.get($.configs.get('displaySittings'));

    if (_.isNull(settings)) {
      return;
    }

    // 暂存显示配置
    themeColor = settings.themeColor;
    sidebar = settings.sidebar;
    navbar = settings.navbar;
    menuDisplay = settings.menuDisplay;
    menuTxtIcon = settings.menuTxtIcon;

    // 注意：在前端渲染主题不是最优雅体验最好的，建议通过后端渲染
    // 皮肤文件加载
    frameSkin();

    // 菜单主题
    if (sidebar && sidebar === 'site-menubar-light') {
      $siteMenubar.addClass('site-menubar-light');
    }

    // 导航条颜色
    if (navbar) {
      $('#admui-siteNavbar').addClass(navbar);
    }

    // 是否通栏显示
    if (!settings.navbarInverse) {
      $('#admui-siteNavbar').removeClass('navbar-inverse');
    }

    // 菜单显示
    if (menuDisplay && menuDisplay === 'site-menubar-fold') {
      $.site.menubar.fold();

      // 鼠标经过菜单时显示
      if (menuTxtIcon && menuTxtIcon === 'site-menubar-keep') {
        $body.addClass('site-menubar-keep');
      } else {
        $body.addClass('site-menubar-fold-alt');
      }
    }
  };

  // 小屏下收起导航条
  var hideNavbar = function() {
    $navbarCollapse.collapse('hide');
  };

  // 记录标签页重绘
  var tabsDraw = function() {
    var storageKey = $.configs.get('tabSittings');
    var settings = $.sessionStorage.get(storageKey);
    var tab;
    var tabName = '加载中...';
    var $firstFrame;
    var firstTabUrl = '';
    var firstTabId = 'frame-0';
    var pageUrl = window.location.hash.substring(2);
    var contentTabs = $.site.contentTabs;

    // 遍历标签页存储对象
    _.forIn(settings, function(value, key) {
      tab = settings[key];

      switch (key) {
        case 'checked':
        case 'tabId':
          break;
        case firstTabId:
          firstTabUrl = tab.url;
          break;
        default:
          // 创建标签页
          contentTabs.buildTab($.extend(tab, {index: key}), 'off');
          // 定位系统菜单选中项
          contentTabs.enable($conTabs.find('.active'));
      }
    });

    // 页面地址为空时确认首页为选中项
    if (pageUrl === '') {
      settings.checked !== firstTabId &&
        $.sessionStorage.set(storageKey, $.extend(settings, {checked: firstTabId}));

      $firstFrame = $content.find('iframe:first');

      // 标签页添加选中类
      $conTabs.find('li:first').addClass('active');
      // 为当前frame添加地址 & 绑定事件 && 添加选中类
      $firstFrame.attr('src', firstTabUrl).addClass('active');
      $.site.frameEvents($firstFrame);
    } else {
      // 当前地址页面路径不为空时检测是否为系统菜单地址
      $.site.menu.$instance.find('a').each(function() {
        var $item = $(this);

        if ($item.attr('href') === pageUrl) {
          // 为系统菜单地址时
          tabName = $.trim($item.attr('title') || $item.data('title') || $item.text());

          return false;
        }
        return true;
      });

      // 创建标签页
      contentTabs.buildTab({name: tabName, url: pageUrl});
      // 定位系统菜单选中项
      contentTabs.enable($conTabs.find('.active'));
    }
  };

  // 基础布局
  $.site = $.site || {};

  /** *
   * 系统菜单折叠收起动画操作（默认为折叠效果）
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
   * * */
  $.site.menu = {
    speed: 250,
    init: function() {
      var $instance = $siteMenubar.find('.site-menu');
      this.$instance = $instance;

      $instance.length > 0 && this.bind();
    },
    bind: function() {
      var self = this;
      var menubar = $.site.menubar;
      // 在屏幕尺寸级别为'md'或'sm'时，系统菜单的下拉列表的位置操作功能
      var mdPostion = function($item, $dropdown) {
        var offsetTop = $item.position().top;
        var menubarHeight = menubar.$instance.outerHeight();
        var itemHeight = $item.find('> a').outerHeight();

        $dropdown.removeClass('site-menu-sub-up').css('max-height', '');

        if (offsetTop > menubarHeight / 2) {
          menubar.foldAlt && (offsetTop -= itemHeight);

          $dropdown.addClass('site-menu-sub-up');
          $dropdown.css('max-height', offsetTop + itemHeight);
        } else {
          menubar.foldAlt && (offsetTop += itemHeight);

          $dropdown.removeClass('site-menu-sub-up');
          $dropdown.css('max-height', menubarHeight - offsetTop);
        }
      };
      // 在屏幕尺寸级别为'md'或'sm'时判断规则
      var mdResult = function($item) {
        return menubar.folded && $item.is('.has-sub') && $item.parent('.site-menu').length > 0;
      };

      // 系统菜单项绑定事件
      self.$instance
        .on('mouseenter.site.menu', '.site-menu-item', function() {
          var $item = $(this);

          if (mdResult($item)) {
            mdPostion($item, $item.find('>.site-menu-sub'));
            $body.addClass('site-menubar-fold-hover');
          }

          $item.addClass('hover');
        })
        .on('mouseleave.site.menu', '.site-menu-item', function() {
          var $item = $(this);

          if (mdResult($item)) {
            $item.find('>.site-menu-sub').css('max-height', '');
            $body.removeClass('site-menubar-fold-hover');
          }

          $item.removeClass('hover');
        })
        .on('deactive.site.menu', '.site-menu-item.active', function() {
          $(this).removeClass('active');
        })
        .on('active.site.menu', '.site-menu-item', function() {
          $(this).addClass('active');
        })
        .on('open.site.menu', '.site-menu-item', function(e) {
          var $item = $(this);

          self.expand($item, function() {
            $item.addClass('open');
          });

          e.stopPropagation();
        })
        .on('close.site.menu', '.site-menu-item.open', function(e) {
          var $item = $(this);

          self.collapse($item, function() {
            $item.removeClass('open');
          });

          e.stopPropagation();
        })
        .on('click.site.menu ', '.site-menu-item > a', function() {

          //点击侧栏刷新内容窗口
          var $parent = $(this).parent();
          let old_src = $(this).attr('href');
          let new_src = '';

          if(old_src != 'javascript:void (0);'){
            if(old_src.indexOf("?") > 0){
              new_src = old_src.substr(0,old_src.indexOf('?'))+'?t='+new Date().getTime();
            }else{
              new_src = old_src+'?t='+new Date().getTime();
            }
            $(this).attr('href',new_src);
          }

          if ($parent.is('.has-sub')) {
            $parent.is('.open')
              ? $parent.trigger('close.site.menu')
              : $parent.trigger('open.site.menu');
          } else {
            self.$instance.find('li.active').removeClass('active');
            $parent.addClass('active');
          }
        })
        .on('tap.site.menu', '> .site-menu-item > a', function() {
          var link = $(this).attr('href');

          if (link) {
            window.location = link;
          }
        })
        .on('touchend.site.menu', '> .site-menu-item > a', function() {
          var $item = $(this).parent('.site-menu-item');

          if (!menubar.folded) {
            return;
          }

          if ($item.is('.has-sub') && $item.parent('.site-menu').length > 0) {
            $item.siblings('.hover').removeClass('hover');
            $item.is('.hover') ? $item.removeClass('hover') : $item.addClass('hover');
          }
        })
        .on('scroll.site.menu', '.site-menu-sub', function(e) {
          e.stopPropagation();
        });
    },
    collapse: function($item, callback) {
      // 子级菜单的折叠动作
      var self = this;

      $item.children('.site-menu-sub').slideUp(self.speed, function() {
        callback && callback();
        // TODO: 在文档中说明，可以绑定一个系统菜单收起事件
        self.$instance.trigger('collapsed.site.menu');
      });
    },
    expand: function($item, callback) {
      // 子级菜单的展开动作
      var self = this;
      var $sub = $item.children('.site-menu-sub');

      $item.siblings('.open').trigger('close.site.menu');
      $sub.slideDown(self.speed, function() {
        callback && callback();

        // 优化折叠展开效果
        // TODO: 在文档中说明，可以绑定一个系统菜单展开事件
        self.$instance.trigger('expanded.site.menu');
      });
    },
    refresh: function() {
      // 重置左侧菜单状态
      /*$siteMenubar.find('li.open').trigger('close.site.menu');
      $siteMenubar.find('li.active').trigger('deactive.site.menu');*/
    }
  };

  // 系统菜单不同屏幕尺寸下的响应式操作
  $.site.menubar = {
    opened: true,
    folded: false,
    top: false,
    foldAlt: false,
    $instance: null,
    init: function() {
      var self = this;
      var $navItem = $navMenu.find('>ul>li.nav-item');
      var $instance = $('#admui-navTabs');

      // 基于js的显示优化
      $html.removeClass('css-menubar').addClass('js-menubar');

      this.$instance = $instance;

      // 系统菜单标识ID
      this.tabId = $navItem.find('>a.active').attr('href');

      if (
        $navItem
          .find('>a.active')
          .parent()
          .is('#admui-navbarSubMenu')
      ) {
        this.tabId = $navItem.find('a.dropdown-item.active').attr('href');
      }

      if ($instance.length === 0) {
        return;
      }

      // 鼠标经过左侧菜单显示图标
      if ($body.is('.site-menubar-fold-alt')) {
        this.foldAlt = true;
      }

      // 导航条状态改变后操作
      $instance.on('changed.site.menubar', function() {
        self.update();
      });

      // 系统菜单显示完成后导航条操作
      $navMenu.find('a[data-toggle="tab"]').on('shown.bs.tab', function(event) {
        var tabId = $(event.target).attr('href');
        self.tabId = tabId;

        if ($body.hasClass('site-menubar-fold')) {
          self.hoverscroll.enable(tabId);
        } else if ($body.hasClass('site-menubar-unfold')) {
          self.mCustomScrollbar.enable();
        }
      });
      this.change();
    },
    change: function() {
      // 屏幕尺寸改变触发声明节点函数时操作
      var self = this;
      var breakpoint = Breakpoints.current();
      var pointName = breakpoint.name;

      if (breakpoint) {
        switch (pointName) {
          case 'lg':
            this.unfold();
            break;
          case 'md':
          case 'sm':
            this.fold();
            break;
          case 'xs':
            this.hide();
            break;
          default:
            break;
        }
      }

      // 离开小屏尺寸时导航条响应式操作
      Breakpoints.on('xs', 'leave', function() {
        hideNavbar();
        self.resetNav();
      });
    },
    animate: function(doing, callback) {
      var $item = this.$instance;

      $body.addClass('site-menubar-changing');

      doing.call(this);
      $item.trigger('changing.site.menubar');

      callback.call(this);
      $body.removeClass('site-menubar-changing');

      $item.trigger('changed.site.menubar');
    },
    reset: function() {
      // 重置系统菜单状态
      this.opened = true;
      this.folded = false;
      $body.removeClass(
        'site-menubar-hide site-menubar-open site-menubar-fold site-menubar-unfold'
      );
      $html.removeClass('disable-scrolling');
    },
    open: function() {
      if (!this.opened) {
        this.reset();
        this.animate(
          function() {
            $body.addClass('site-menubar-open site-menubar-unfold');
            this.opened = true;

            $html.addClass('disable-scrolling');
          },
          function() {
            this.mCustomScrollbar.enable();
          }
        );
      }
    },
    hide: function() {
      this.hoverscroll.disable();

      if (this.opened) {
        this.reset();
        this.animate(
          function() {
            $html.removeClass('disable-scrolling');
            $body.addClass('site-menubar-hide site-menubar-unfold');
            this.opened = false;
          },
          function() {
            this.mCustomScrollbar.enable();
          }
        );
      }
    },
    unfold: function() {
      this.hoverscroll.disable();

      if (this.folded) {
        this.reset();
        this.animate(
          function() {
            $body.addClass('site-menubar-unfold');
            this.folded = false;
          },
          function() {
            this.mCustomScrollbar.enable();
          }
        );
      } else {
        this.mCustomScrollbar.enable();
      }
    },
    fold: function() {
      this.mCustomScrollbar.disable();

      if (!this.folded) {
        this.reset();
        this.animate(
          function() {
            $body.addClass('site-menubar-fold');
            this.folded = true;
          },
          function() {
            this.hoverscroll.enable(this.tabId);
          }
        );
      }
    },
    resetNav: function() {
      // 重置导航条布局
      $navMenu.responsiveTab('reset');
    },
    toggle: function() {
      // 系统菜单状态变化
      var breakpoint = Breakpoints.current();
      var folded = this.folded;
      var opened = this.opened;

      // 展开 | 收起
      var fn = function(obj) {
        if (folded) {
          obj.unfold();
        } else {
          obj.fold();
        }
      };

      switch (breakpoint.name) {
        case 'lg':
          fn(this);
          break;
        case 'md':
        case 'sm':
          fn(this);
          this.resetNav();
          break;
        case 'xs':
          if (!opened) {
            this.open();
          } else {
            this.hide();
          }
          break;
        default:
          break;
      }
    },
    update: function() {
      this.hoverscroll.update();
    },
    mCustomScrollbar: {
      api: null,
      native: false,
      init: function() {
        $.site.menubar.$instance
          .find('>.tab-pane')
          .mCustomScrollbar($.concatCpt('mCustomScrollbar', {theme: 'light'}));
      },
      enable: function() {
        !this.native && this.init();
      },
      disable: function() {
        $.site.menubar.$instance.find('>.tab-pane').mCustomScrollbar('destroy');
      }
    },
    hoverscroll: {
      api: null,
      init: function(tabId) {
        this.api = $.site.menubar.$instance
          .find(tabId)
          .asHoverScroll({
            namespace: 'hoverscorll',
            direction: 'vertical',
            list: '.site-menu',
            item: '> li',
            exception: '.site-menu-sub',
            fixed: false,
            boundary: 100,
            onEnter: function() {
              // $(this).siblings().removeClass('hover');
              // $(this).addClass('hover');
            },
            onLeave: function() {
              // $(this).removeClass('hover');
            }
          })
          .data('asHoverScroll');
      },
      update: function() {
        var api = this.api;
        api && api.update();
      },
      enable: function(tabId) {
        if (tabId !== this.tabId) {
          this.tabId = tabId;
          this.init(tabId);
        } else {
          this.api.enable();
        }
      },
      disable: function() {
        var api = this.api;
        api && api.disable();
      }
    }
  };

  $.extend($.site, {
    frameEvents: function($el) {
      // 子框架主体内容绑定事件

      // 显示加载动画
      $content.next('.page-loading').show();
      // 子框架加载完成时
      $el.on('load', function() {
        var frameDocument = $.contentFrame.document();
        var frameTitle = $('title', frameDocument).text();
        var $checkedTab = $conTabs.find('li.active>a');

        // 隐藏加载动画
        $content.next('.page-loading').hide();

        // 创建标签页没有名称（系统配置为'加载中...'）时使用frame框架title更新
        if ($checkedTab.attr('title') === '加载中...') {
          $checkedTab.attr('title', frameTitle).text(frameTitle);
        }

        // 更新主框架页面title
        $('title').text(frameTitle);

        // 给frame框架区域绑定点击事件
        $(frameDocument).on('click', function() {
          var $dropdown = $('[data-toggle="dropdown"]');

          // 小屏下收起顶部导航条和左侧菜单
          if (Breakpoints.is('xs') && $body.hasClass('site-menubar-open')) {
            $.site.menubar.hide();

            hideNavbar();
          }

          // 收起主框架中展开的下拉菜单
          $dropdown.each(function() {
            var $item = $(this);
            $item.next().is('.show') && $item.dropdown('toggle');
          });
        });

        // frame框架中皮肤样式文件加载完成后
        $('#admui-siteStyle', frameDocument).on('load', function() {
          frameSkin(frameDocument);
        });
      });
    },

    run: function() {
      var self = this;
      var menu = self.menu;
      var menubar = self.menubar;

      // 获取frame框架中全局对象
      $.contentFrame = $.contentFrame || {};

      $.extend($.contentFrame, {
        global: function() {
          // 获取frame框架中window对象
          var name = $content.find('iframe.active').attr('name');
          return window.frames[name];
        },
        document: function() {
          // 获取frame框架中document对象
          return this.global().document;
        }
      });

      !_.isUndefined(menu) && menu.init();

      !_.isUndefined(this.contentTabs) && this.contentTabs.init();

      // 导航条响应式方法调用
      $navMenu.responsiveTab({
        tabContent: '#admui-navTabs',
        complete: function(el) {
          el.removeClass('is-load');
        }
      });

      // 导航条 & 菜单的响应式工作
      if (!_.isUndefined(menubar)) {
        menubar.init();

        // 屏幕尺寸改变系统菜单响应式操作
        Breakpoints.on('change', function() {
          menubar.change();
        });

        // 系统菜单展开 | 收起控制按钮
        $(document).on('click', '[data-toggle="menubar"]', function() {
          // 小屏幕时收起导航条
          Breakpoints.is('xs') && menubar.opened && hideNavbar();
          menubar.toggle();

          return false;
        });

        // 小屏下系统菜单展开 | 收起按钮状态改变
        $siteMenubar.on('changing.site.menubar', function() {
          var $menubar = $('button[data-toggle="menubar"]');

          $menubar.toggleClass('hided', !menubar.opened);
          $menubar.toggleClass('unfolded', !menubar.folded);
        });

        // 小屏幕下导航条展开 | 收起按钮
        $navbarCollapse
          .on('show.bs.collapse', function() {
            $body.addClass('site-navbar-collapsing');
          })
          .on('shown.bs.collapse', function() {
            $body.addClass('site-navbar-collapse-show').removeClass('site-navbar-collapsing');
            menubar.resetNav();
          })
          .on('hide.bs.collapse', function() {
            $body.addClass('site-navbar-collapsing');
          })
          .on('hidden.bs.collapse', function() {
            $body.removeClass('site-navbar-collapse-show').removeClass('site-navbar-collapsing');
          });

        // 导航条收起菜单项对应系统菜单响应式操作
        $navMenu.find('li:not(.no-menu)').on('click', function() {
          if ($(this).is('.dropdown')) {
            return;
          }

          Breakpoints.is('xs') && menubar.open();
        });
      }

      // 导航条全屏按钮操作
      if (!_.isUndefined(screenfull)) {
        $('#admui-navbarFullscreen').on('click', function() {
          screenfull.enabled && screenfull.toggle();
        });

        // 监听全屏状态变化
        screenfull.enabled &&
          document.addEventListener(screenfull.raw.fullscreenchange, function() {
            $('#admui-navbarFullscreen > a').toggleClass('active', screenfull.isFullscreen);
          });
      }

      // 在dropdown组件显示时增加动画效果
      $(document).on('show.bs.dropdown', function(e) {
        var $target = $(e.target);
        var $dropdownMenu;
        var $trigger = e.relatedTarget
          ? $(e.relatedTarget)
          : $target.find('>[data-toggle="dropdown"]');
        var animation = $trigger.data('animation');

        if (animation) {
          $dropdownMenu = $target.find('>.dropdown-menu');

          $dropdownMenu.addClass('animation-' + animation);
          $dropdownMenu.one(
            'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
            function() {
              $dropdownMenu.removeClass('animation-' + animation);
            }
          );
        }
      });

      theme();
      tabsDraw();
      $.components.init();
    }
  });

  $(function() {
    $html = $('html');
    $body = $('body');
    $siteMenubar = $('#admui-siteMenubar');
    $navMenu = $('#admui-navMenu');
    $navbarCollapse = $('#admui-navbarCollapse');
    // 标签页
    $conTabs = $('#admui-siteConTabs .con-tabs');
    // 子框架容器
    $content = $('#admui-pageContent');

    $.site.run();
  });
})(window, document, jQuery);
