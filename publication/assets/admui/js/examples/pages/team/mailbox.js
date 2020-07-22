/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global toastr */

  window.App.extend({
    handleAction: function() {
      // 初始化新建邮件按钮
      var actionBtn = $('.site-action')
        .actionBtn()
        .data('actionBtn');
      var $selectable = $('[data-selectable]');
      var $addMailForm = $('#addMailForm');

      // 点击新建邮件按钮
      $(document).on('click', '.site-action-toggle', '.site-action', function(e) {
        console.log($selectable.asSelectable('getSelected').length);
        if ($selectable.asSelectable('getSelected').length === 0 && actionBtn.showed) {
          $addMailForm.modal('show');
        }

        if (!actionBtn.showed) {
          $addMailForm.modal('hide');
        }
        e.stopPropagation();
      });

      // 删除邮件
      $(document).on('click', '[data-action="trash"]', '.site-action', function() {
        toastr.info('删除所选邮件');
      });

      // 移动邮件
      $(document).on('click', '[data-action="inbox"]', '.site-action', function() {
        toastr.info('移动所选邮件到指定文件夹');
      });

      // 新建按钮展开 | 折叠
      $(document).on('asSelectable::change', '[data-selectable]', function(e, api, checked) {
        if (checked) {
          actionBtn.show();
          $addMailForm.modal('show');
        } else {
          actionBtn.hide();
        }
      });
    },

    handleListItem: function() {
      // 新建标签
      $(document).on('click', '#addLabelToggle', function(e) {
        $('#addLabelForm').modal('show');
        e.stopPropagation();
      });

      // 删除标签
      $(document).on('click', '[data-tag=list-delete]', function(e) {
        window.top.layer.alert('您确定要删除这个标签吗？', {icon: 4}, function(index) {
          $(e.target)
            .closest('.list-group-item')
            .remove();
          window.top.layer.close(index);
        });
      });
    },
    itemTpl: function(data) {
      // 按序列渲染邮件
      return (
        '<tr id="' +
        data.id +
        '" data-mailbox="slidePanel" ' +
        (data.unread === 'true' ? 'class="unread"' : '') +
        '>' +
        '<td class="cell-60">' +
        '<span class="checkbox-custom checkbox-primary checkbox-lg">' +
        '<input type="checkbox" class="mailbox-checkbox selectable-item" id="mail_' +
        data.id +
        '"/>' +
        '<label for="mail_' +
        data.id +
        '"></label>' +
        '</span>' +
        '</td>' +
        '<td class="cell-30 responsive-hide">' +
        '<span class="checkbox-important checkbox-default">' +
        '<input type="checkbox" class="mailbox-checkbox mailbox-important" ' +
        (data.starred === 'true' ? 'checked="checked"' : '') +
        ' id="mail_' +
        data.id +
        '_important"/>' +
        '<label for="mail_' +
        data.id +
        '_important"></label>' +
        '</span>' +
        '</td>' +
        '<td class="cell-60 responsive-hide">' +
        '<a class="avatar" href="javascript:;"><img class="img-responsive" src="' +
        data.avatar +
        '" alt="..."></a>' +
        '</td>' +
        '<td>' +
        '<div class="content">' +
        '<div class="title">' +
        data.name +
        '</div>' +
        '<div class="abstract">' +
        data.title +
        '</div>' +
        '</div>' +
        '</td>' +
        '<td class="cell-30 responsive-hide">' +
        (data.attachments.length > 0
          ? '<i class="icon wb-paperclip" aria-hidden="true"></i>'
          : '') +
        '</td>' +
        '<td class="cell-130">' +
        '<div class="time">' +
        data.time +
        '</div>' +
        (data.group.length > 0
          ? '<div class="identity"><i class="wb-medium-point ' +
            data.color +
            '" aria-hidden="true"></i>' +
            data.group +
            '</div>'
          : '') +
        '</td>' +
        '</tr>'
      );
    },
    attachmentsTpl: function(data) {
      // 定义附件列表tpl
      var self = this;
      var html = '';

      html +=
        '<div class="mail-attachments">' +
        '<p><i Class="icon wb-paperclip"></i>Attachments | <a href="javascript:;">Download All</a></p>' +
        '<ul class="list-group">';

      $.each(data, function(n, item) {
        html += self.attachmentTpl(item);
      });

      html += '</ul></div>';

      return html;
    },
    attachmentTpl: function(data) {
      // 定义附件中详细列表tpl
      return (
        '<li class="list-group-item">' +
        '<span class="name">' +
        data.name +
        '</span><span class="size">' +
        data.size +
        '</span>' +
        '<button type="button" class="btn btn-icon btn-pure btn-default"><i class="icon wb-download" aria-hidden="true"></i></button>' +
        '</li>'
      );
    },
    messagesTpl: function(data) {
      // 初始化邮件消息tpl
      var self = this;
      var html = '';

      $.each(data.messages, function(n, item) {
        html +=
          '<section class="slidePanel-inner-section">' +
          '<div class="mail-header">' +
          '<div class="mail-header-main">' +
          '<a class="avatar" href="javascript:;"><img src="' +
          data.avatar +
          '" alt="..."></a>' +
          '<div><span class="name">' +
          data.name +
          '</span></div>' +
          '<div>' +
          '<a href="javascript:;" class="mailbox-panel-email">' +
          data.email +
          '</a>' +
          ' 发送给 <a href="javascript:;" class="mr-10">我</a>' +
          '<span class="identity"><i class="wb-medium-point red-600" aria-hidden="true"></i>' +
          data.group +
          '</span>' +
          '</div>' +
          '</div>' +
          '<div class="mail-header-right">' +
          '<span class="time">' +
          item.time +
          '</span>' +
          '<div class="btn-group actions" role="group">' +
          '<button type="button" class="btn btn-icon btn-pure btn-default"><i class="icon wb-star" aria-hidden="true"></i></button>' +
          '<button type="button" class="btn btn-icon btn-pure btn-default"><i class="icon wb-reply" aria-hidden="true"></i></button>' +
          '</div>' +
          '</div>' +
          '</div>' +
          '<div class="mail-content">' +
          item.content +
          '</div>';

        if (n === 0) {
          if (item.attachments && item.attachments.length > 0) {
            html += self.attachmentsTpl(item.attachments);
          }
        }

        html += '</section>';
      });

      return html;
    },

    initMail: function() {
      // 初始化已有邮件列表
      var self = this;

      $.getJSON('../../../../public/data/examples/pages/mailbox.json', function(data) {
        var $wrap = $('#mailboxTable');

        self.buildMail($wrap, data);
        self.initMailData(data);
        self.handlSlidePanelPlugin();
      });
    },

    initMailData: function(data) {
      this.mailboxData = data;
    },

    buildMail: function($wrap, data) {
      // 新建邮件
      var self = this;
      var $tbody = $('<tbody></tbody>');

      $.each(data, function(i, item) {
        self.buildItem($tbody, item);
      });

      $wrap.empty().append($tbody);
    },

    buildItem: function($wrap, data) {
      // 新建邮件tpl
      $wrap.append($(this.itemTpl(data)).data('mailInfo', data));
    },

    handlSlidePanelPlugin: function() {
      var self = this;

      if (typeof $.slidePanel === 'undefined') {
        return;
      }

      // 显示邮件面板详情
      $(document).on('click', '[data-mailbox="slidePanel"]', function(e) {
        $.slidePanel.show(
          {
            url: '../../../../public/data/examples/pages/mailbox-panel.tpl',
            target: $(this)
          },
          $.concatCpt('slidePanel', {
            template: function(options) {
              return (
                '<div class="' +
                options.classes.base +
                ' ' +
                options.classes.base +
                '-' +
                options.direction +
                '">' +
                '<div class="' +
                options.classes.base +
                '-scrollable"><div>' +
                '<div class="' +
                options.classes.content +
                '"></div>' +
                '</div></div>' +
                '<div class="' +
                options.classes.base +
                '-handler"></div>' +
                '</div>'
              );
            },
            afterLoad: function() {
              this.$panel.find('.' + this.options.classes.base + '-scrollable').mCustomScrollbar();
            },
            contentFilter: function(data, object) {
              var $target = $(object.target);
              var info = $target.data('mailInfo');
              var $panel = $(data);

              $('.mailbox-panel-title', $panel).html(info.title);

              $('.slidePanel-messages', $panel).html(self.messagesTpl(info));

              return $panel;
            }
          })
        );

        e.stopPropagation();
      });
    },
    run: function() {
      this.handleAction();
      this.handleListItem();

      this.initMail();

      // 初始化modal
      $('#addlabelForm').modal({show: false});
      $('#addMailForm').modal({
        show: false
      });

      // 阻止元素默认操作
      $(document).on('click', '.checkbox-important', function(e) {
        e.stopPropagation();
      });

      // 初始化markdown编辑器
      $('.markdown-edit').markdown({
        language: 'zh',
        iconlibrary: 'fa'
      });
    }
  });

  $(function() {
    window.App.run();
  });
})(document, window, jQuery);
