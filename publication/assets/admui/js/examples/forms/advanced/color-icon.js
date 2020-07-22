/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  /* global toastr */

  $(function() {
    var options = {
      fullClassFormatter: function(value) {
        return 'icon ' + value;
      },
      templates: {
        buttons:
          '<button class="iconpicker-btn iconpicker-btn-cancel btn btn-default btn-sm">取消</button>' +
          ' <button class="iconpicker-btn iconpicker-btn-accept btn btn-primary btn-sm">确认</button>',
        search:
          '<input type="search" class="form-control iconpicker-search" placeholder="查找图标">'
      }
    };

    // 颜色选择器
    $('#colorpickerEvent')
      .colorpicker({
        format: 'hex'
      })
      .on('colorpickerChange', function(e) {
        toastr.info('颜色值已改变');
        e.stopPropagation();
      });

    // 图标选择器
    $('#definedIcon1').iconpicker(
      $.extend(
        {
          title: '自定义配置',
          icons: [
            {
              title: 'icon fa-github',
              searchTerms: ['repository', 'code']
            },
            {
              title: 'icon fa-heart',
              searchTerms: ['love']
            },
            {
              title: 'icon fa-html5',
              searchTerms: ['web']
            },
            {
              title: 'icon fa-css3',
              searchTerms: ['style']
            }
          ],
          selectedCustomClass: 'label label-success',
          mustAccept: true,
          placement: 'bottomRight',
          showFooter: true
        },
        options
      )
    );

    // 使用 glypghicons
    $('#definedIcon2').iconpicker(
      $.extend(
        {
          title: '使用 glypghicons',
          icons: $.merge(
            [
              {
                title: 'icon glyphicon-home',
                searchTerms: ['home']
              },
              {
                title: 'icon glyphicon-repeat',
                searchTerms: ['repeat']
              },
              {
                title: 'icon glyphicon-search',
                searchTerms: ['search']
              },
              {
                title: 'icon glyphicon-arrow-left',
                searchTerms: ['left']
              },
              {
                title: 'icon glyphicon-arrow-right',
                searchTerms: ['right']
              },
              {
                title: 'icon glyphicon-star',
                searchTerms: ['star']
              }
            ],
            $.iconpicker.defaultOptions.icons
          ),
          fullClassFormatter: function(val) {
            if (val.match(/^fa-/)) {
              return 'icon ' + val;
            }
            return 'glyphicon ' + val;
          }
        },
        options
      )
    );
  });
})(document, window, jQuery);
