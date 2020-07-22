/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function($) {
  'use strict';

  $(function() {
    // 定义一个数组存放Deferred对象
    var defereds = [];

    // imgs循环所有图片
    $('.page-container')
      .find('img, video, audio')
      .each(function() {
        // 新建一个deferred对象
        var dfd = $.Deferred();

        // 图片加载完成之后，改变deferred对象的执行状态
        $(this).on('load', function() {
          dfd.resolve();
        });

        // push到数组中
        defereds.push(dfd);
      });

    // defereds中数据完成加载时初始化masonry插件
    $.when(defereds).done(function() {
      $('#masonry').masonry($.extend($.concatCpt('masonry')));
    });
  });
})(jQuery);
