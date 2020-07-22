/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(window, document, $) {
  'use strict';

  /* global _ */

  // 主框架document对象
  var mainFrame = window.top.document;
  var theme;
  var ctx;

  // 显示设置存储名称
  var storageKey = $.configs.get('displaySittings');
  // 显示设置功能函数
  var Skintools = {
    $siteSidebar: $('.site-menubar', mainFrame),
    $siteNavbar: $('.site-navbar', mainFrame),
    navbarSkins:
      'bg-blue-600 bg-primary-600 bg-brown-600 bg-cyan-600 bg-green-600 bg-grey-600 bg-indigo-600 bg-orange-600 bg-pink-600 bg-purple-600 bg-red-600 bg-teal-600 bg-yellow-700',
    defaultSettings: {
      sidebar: 'site-menubar-dark',
      navbar: 'bg-primary-600',
      navbarInverse: 'navbar-inverse',
      themeColor: 'primary',
      menuDisplay: 'site-menubar-unfold',
      menuTxtIcon: 'site-menubar-keep',
      tabFlag: 'site-contabs-open'
    },
    initLocalStorage: function() {
      this.settings = $.sessionStorage.get(storageKey);

      if (_.isNull(this.settings)) {
        this.settings = $.extend(true, {}, this.defaultSettings);

        $.sessionStorage.set(storageKey, this.settings);
      }

      if (this.settings && $.isPlainObject(this.settings)) {
        $.each(this.settings, function(n, v) {
          switch (n) {
            case 'boxLayout':
              $('#boxLayout').prop('checked', v !== '');
              break;
            case 'sidebar':
              $('#skintoolsSidebar').selectpicker('val', [v]);
              break;
            case 'navbar':
              $('input[value="' + v + '"]', $('#skintoolsNavbar>ul')).prop('checked', true);
              break;
            case 'navbarInverse':
              $('#navbarDisplay').prop('checked', v !== '');
              break;
            case 'menuDisplay':
              $('input[value="' + v + '"]', '#displayForm').prop('checked', true);
              break;
            case 'menuTxtIcon':
              if ($('#menuFold').is(':checked')) {
                $('#menuFoldSetting').removeClass('hidden ');
                $('input[name="menuTxtIcon"]', '#displayForm')
                  .parent('label')
                  .removeClass('active');
                $('input[value="' + v + '"]', '#displayForm')
                  .prop('checked', true)
                  .parent('label')
                  .addClass('active');
              }
              break;
            case 'themeColor':
              $('input[value="' + v + '"]', '#skintoolsPrimary').prop('checked', true);
              break;
            case 'tabFlag':
              $('input[value="' + v + '"]', '#displayForm').prop('checked', true);
              break;
            default:
              break;
          }
        });
      }
    },

    updateSetting: function(item, value) {
      this.settings[item] = value;

      $.sessionStorage.set(storageKey, this.settings);
    },
    sidebarEvents: function($item) {
      var val = $item.val();

      this.sidebarImprove(val);
      this.updateSetting('sidebar', val);
    },
    navbarEvents: function($item) {
      var val = $item.val();
      var checked = $item.prop('checked');

      this.navbarImprove(val, checked);

      if (val === 'navbar-inverse') {
        this.updateSetting('navbarInverse', checked ? val : '');
      } else {
        this.updateSetting('navbar', val);
      }
    },
    primaryEvents: function($item) {
      var val = $item.val();

      this.primaryImprove(val);

      this.updateSetting('themeColor', val);
    },
    sidebarImprove: function(val) {
      if (val === 'site-menubar-light') {
        this.$siteSidebar.removeClass('site-menubar-dark').addClass(val);
      } else {
        this.$siteSidebar.removeClass('site-menubar-light').addClass(val);
      }
    },
    navbarImprove: function(val, checked) {
      if (val === 'navbar-inverse') {
        if (checked) {
          this.$siteNavbar.addClass(val);
        } else {
          this.$siteNavbar.removeClass(val);
        }
      } else {
        this.$siteNavbar.removeClass(this.navbarSkins).addClass(val);
      }
    },
    primaryImprove: function(val) {
      var $mainLink = $('#admui-siteStyle', mainFrame);
      var $frames = $('#admui-pageContent>iframe', mainFrame);
      var mainHref;
      var mainEtx = $mainLink.prop('href').indexOf('?v=') === -1 ? '' : '.min';

      if (val === 'primary') {
        // 默认皮肤时
        mainHref = theme + '/css/index' + mainEtx + '.css';
      } else {
        // 更换为其他皮肤
        mainHref = theme + '/css/skins/' + val + '/index' + mainEtx + '.css';
      }

      // 加载主框架皮肤文件
      $mainLink.attr('href', mainHref);

      // 遍历当前已创建frame框架为其更换皮肤文件
      $frames.each(function() {
        var thisName = $(this).attr('name');
        var $item = $('#admui-siteStyle', window.top.frames[thisName].document);
        var frameHref;
        var frameEtx;

        // 当前frame框架未加载完成时
        if ($item.length === 0) {
          return;
        }

        frameEtx = $item.prop('href').indexOf('?v=') === -1 ? '' : '.min';

        if (val === 'primary') {
          // 默认皮肤时
          frameHref = theme + '/css/site' + frameEtx + '.css';
        } else {
          // 更换为其他皮肤
          frameHref = theme + '/css/skins/' + val + '/site' + frameEtx + '.css';
        }

        // 加载frame框架皮肤文件
        $item.attr('href', frameHref);
      });
    },
    reset: function() {
      $.sessionStorage.remove(storageKey);
      window.location.reload(true);
    }
  };

  $(function() {
    var $body = $('body', mainFrame);
    // 项目地址
    ctx = $.configs.ctx;
    theme = ctx + '/public/themes/' + $body.data('theme');

    /**
     *  初始化菜单主题选择框
     *  更换菜单主题
     */
    $('#skintoolsSidebar')
      .selectpicker($.concatCpt('selectpicker'))
      .on('change', function() {
        Skintools.sidebarEvents($(this));
      });

    // 更换导航条颜色
    $('#skintoolsNavbar input').on('click', function() {
      Skintools.navbarEvents($(this));
    });

    // 更换主题颜色
    $('#skintoolsPrimary input').on('click', function() {
      Skintools.primaryEvents($(this));
    });

    // 切换系统菜单展开 | 收起状态
    $('[name="menuDisplay"]').on('change', function() {
      var $menuFold = $('#menuFoldSetting');
      var value = $(this).val();

      if (value === 'site-menubar-unfold') {
        $menuFold.attr('hidden');
        $.site.menubar.unfold();
      } else {
        $menuFold.removeAttr('hidden');
        $.site.menubar.fold();
      }
      Skintools.updateSetting('menuDisplay', value);
    });

    // 系统菜单收起时鼠标经过时显示类型[文字 | 图标]
    $('[name="menuTxtIcon"]').on('change', function() {
      var value = $(this).val();

      if (value === 'site-menubar-keep') {
        // 显示文字
        $body.removeClass('site-menubar-fold-alt').addClass('site-menubar-keep');
      } else {
        // 显示图标
        $body.removeClass('site-menubar-keep').addClass('site-menubar-fold-alt');
      }
      Skintools.updateSetting('menuTxtIcon', value);
    });

    // 保存显示设置
    $('[name="save"]').on('click', function(e) {
      var settings = $.sessionStorage.get(storageKey);

      if (_.isNull(settings)) {
        settings = Skintools.defaultSettings;
        // TODO: 不更新存储
        return;
      }

      // TODO: 可以保存数据到后台
      console.warn(settings);
      e.preventDefault();
    });

    // 恢复默认设置
    $('#skintoolsReset').on('click', function() {
      Skintools.reset();
    });

    Skintools.initLocalStorage();
  });
})(window, document, jQuery);
