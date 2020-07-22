/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global toastr */

  $(function() {
    // 示例 1
    $.contextMenu({
      selector: '#exampleContext1',
      items: {
        edit: {
          name: '复制',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-copy';
          }
        },
        cut: {
          name: '剪贴',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-scissor';
          }
        },
        copy: {
          name: '粘贴',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-clipboard';
          }
        },
        sep1: '---------',
        share: {
          name: '分享',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-share';
          }
        }
      }
    });

    // 示例 2
    $.contextMenu({
      selector: '#exampleContext2 > span',
      items: {
        edit: {
          name: '复制',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-copy';
          }
        },
        cut: {
          name: '剪贴',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-scissor';
          }
        },
        copy: {
          name: '粘贴',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-clipboard';
          }
        },
        sep1: '---------',
        share: {
          name: '分享',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-share';
          }
        }
      }
    });

    // 示例 3
    $.contextMenu({
      selector: '#exampleContext3',
      items: {
        edit: {
          name: '复制',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-copy';
          }
        },
        cut: {
          name: '剪贴',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-scissor';
          }
        },
        copy: {
          name: '粘贴',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-clipboard';
          }
        },
        sep1: '---------',
        share: {
          name: '分享',
          icon: function() {
            return 'context-menu-icon context-menu-extend-icon icon wb-share';
          }
        }
      },
      callback: function(itemKey, opt) {
        toastr.info(opt.items[itemKey].name);
      },
      events: {
        show: function() {
          toastr.info('菜单已显示');
        },
        hide: function() {
          toastr.info('菜单已隐藏');
        }
      }
    });
  });
})(document, window, jQuery);
