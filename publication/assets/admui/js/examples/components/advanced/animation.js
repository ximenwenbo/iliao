/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  $(function() {
    // NProgress - 根据不同的配置显示不同的进度条
    $(document).on('click', '.btn', function(e) {
      var $target = $(e.target);
      var id = $target.attr('id');

      window.top.NProgress.configure($.concatCpt('nprogress'));

      switch (id) {
        case 'exampleNProgressStart':
          window.top.NProgress.start();
          break;
        case 'exampleNProgressSet':
          window.top.NProgress.set(0.5);
          break;
        case 'exampleNProgressInc':
          window.top.NProgress.inc();
          break;
        case 'exampleNProgressDone':
          window.top.NProgress.done(true);
          break;

        case 'exampleNProgressDefault':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressHeader':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-header" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressBottom':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-bottom" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;

        case 'exampleNProgressPrimary':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-primary" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressSuccess':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-success" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressInfo':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-info" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressWarning':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-warning" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressDanger':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-danger" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressDark':
          NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-dark" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        case 'exampleNProgressLight':
          window.top.NProgress.done(true);
          window.top.NProgress.configure(
            $.concatCpt('nprogress', {
              template:
                '<div class="bar nprogress-bar-light" role="bar"></div><div class="spinner" role="spinner"><div class="spinner-icon"></div></div>'
            })
          );
          window.top.NProgress.start();
          break;
        default:
          break;
      }
    });
  });
})(document, window, jQuery);
