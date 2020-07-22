/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* eslint no-param-reassign: ["error", { "props": false}] */

  var taskboard = {
    stageTpl: function(title) {
      return (
        '<li class="taskboard-stage">' +
        '<header class="taskboard-stage-header">' +
        '<div class="taskboard-stage-actions float-right">' +
        '<div class="dropdown">' +
        '<a data-toggle="dropdown" href="javascript:;" aria-expanded="false"><i class="icon wb-chevron-down" aria-hidden="true"></i></a>' +
        '<div class="dropdown-menu bullet" role="menu">' +
        '<a class="dropdown-item taskboard-stage-rename" href="javascript:;" role="menuitem"><i class="icon wb-pencil" aria-hidden="true"></i>重命名</a>' +
        '<a class="dropdown-item taskboard-stage-delete" href="javascript:;" role="menuitem"><i class="icon wb-trash" aria-hidden="true"></i>删除</a>' +
        '<div class="taskboard-stage-rename-wrap">' +
        '<div class="form-group">' +
        '<input class="form-control taskboard-stage-rename-input" type="text" value="' +
        title +
        '" name="name">' +
        '</div>' +
        '<button class="btn btn-primary btn-block taskboard-stage-rename-save" type="button">保存</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '<h5 class="taskboard-stage-title">' +
        title +
        '</h5>' +
        '</header>' +
        '<div class="taskboard-stage-content">' +
        '<ul class="list-group taskboard-list">' +
        '</ul>' +
        '<div class="action-wrap">' +
        '<a class="add-item-toggle" href="javascript:;"><i class="icon wb-plus" aria-hidden="true"></i>添加任务</a>' +
        '<div class="add-item-wrap">' +
        '<form class="add-item" role="form" method="post" action="#">' +
        '<div class="form-group">' +
        '<label class="control-label mb-15" for="name">任务名称：</label>' +
        '<input class="form-control" type="text" placeholder="任务名称" name="name">' +
        '</div>' +
        '<div class="form-group text-right">' +
        '<a class="btn btn-sm btn-white add-item-cancel">取消</a>' +
        '<button type="button" class="btn btn-primary add-item-add">添加</button>' +
        '</div>' +
        '</form>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</li>'
      );
    },

    taskTpl: function(data) {
      return (
        '<li class="list-group-item priority-' +
        data.priority +
        '" data-taskboard="slidePanel" data-url="/public/data/examples/pages/taskboard-panel.tpl">' +
        '<div class="checkbox-custom checkbox-primary">' +
        '<input type="checkbox" ' +
        (data.complete ? 'checked="checked"' : '') +
        ' name="checkbox">' +
        '<label class="task-title">' +
        data.title +
        '</label>' +
        '</div>' +
        '<div class="task-badges"></div>' +
        '<ul class="task-members">' +
        '<li><img class="avatar avatar-sm" src="/public/images/portraits/5.jpg"></li>' +
        '</div>' +
        '</li>'
      );
    },

    badgesTpl: function(type, content) {
      var html = '';
      switch (type) {
        case 'duedate':
          html =
            '<span class="task-badge task-badge-subtask icon wb-calendar">' + content + '</span>';
          break;
        case 'subtasks':
          html =
            '<span class="task-badge task-badge-subtask icon wb-list-bulleted">' +
            content +
            '</span>';
          break;
        case 'attachments':
          html =
            '<span class="task-badge task-badge-attachments icon wb-paperclip">' +
            content +
            '</span>';
          break;
        case 'comments':
          html = '<span class="task-badge task-badge-comments icon wb-chat">' + content + '</span>';
          break;
        default:
          break;
      }
      return html;
    },

    membersTpl: function(src) {
      return '<li><img class="avatar avatar-sm" src="' + src + '"></li>';
    },

    subtaskTpl: function(data) {
      return (
        '<li class="list-group-item subtask">' +
        '<div class="checkbox-custom checkbox-primary">' +
        '<input type="checkbox" ' +
        (data.complete ? 'checked="checked"' : '') +
        ' name="checkbox">' +
        '<label class="title">' +
        data.title +
        '</label>' +
        '</div>' +
        '<div class="subtask-editor">' +
        '<form>' +
        '<div class="form-group">' +
        '<input class="form-control subtask-title" type="text" name="title">' +
        '</div>' +
        '<div class="form-group">' +
        '<button class="btn btn-primary subtask-editor-save" type="button">保存</button>' +
        '<a class="btn btn-sm btn-white subtask-editor-delete" href="javascript:;">删除</a>' +
        '</div>' +
        '</form>' +
        '</div>' +
        '</li>'
      );
    },

    attachmentTpl: function(data) {
      return (
        '<li class="list-group-item">' +
        '<div class="meida">' +
        '<div class="media-left">' +
        '<div class="attachments-image">' +
        '<img src="' +
        data.src +
        '">' +
        '</div>' +
        '</div>' +
        '<div class="media-body">' +
        '<p><span class="name">' +
        data.title +
        '</span><span</p>' +
        '<p>' +
        '<span class="size">' +
        data.size +
        '</span>' +
        '<span class="attachments-actions">' +
        '<button class="btn btn-icon btn-pure" type="button">' +
        '<i class="icon wb-download" aria-hidden="true"></i>' +
        '</button>' +
        '<button class="btn btn-icon btn-pure" type="button">' +
        '<i class="icon wb-trash" aria-hidden="true"></i>' +
        '</button>' +
        '</span>' +
        '</p>' +
        '</div>' +
        '</div>' +
        '</li>'
      );
    },

    commentTpl: function(src, user, time, content) {
      return (
        '<div class="comment media">' +
        '<div class="pr-20">' +
        '<a class="avatar avatar-lg" href="javascript:;">' +
        '<img src="' +
        src +
        '" alt="...">' +
        '</a>' +
        '</div>' +
        '<div class="media-body">' +
        '<div class="comment-body">' +
        '<a class="comment-author" href="javascript:;">' +
        user +
        '</a>' +
        '<div class="comment-meta">' +
        '<span class="date">' +
        time +
        '</span>' +
        '</div>' +
        '<div class="comment-content"><p>' +
        content +
        '</p></div>' +
        '</div>' +
        '</div>' +
        '</div>'
      );
    },

    dataTpl: function() {
      return {
        status: false,
        title: '',
        description: '',
        priority: 'normal',
        duedate: '',
        members: [],
        subtasks: [],
        attachments: [],
        comments: []
      };
    },
    init: function() {
      var self = this;
      $.getJSON('/public/data/examples/pages/taskboard.json', function(data) {
        var $wrap = $('#taskboard-stages');
        self.buildStage($wrap, data);
        self.initSortable();
      });
    },
    buildStage: function($wrap, data) {
      var self = this;

      if (data.length === 0) {
        return;
      }

      $.each(data, function(n, info) {
        var $stage = $(self.stageTpl(info.title, info.type));
        self.buildTask($stage, info.tasks);
        $wrap.append($stage);
      });
    },
    buildTask: function($wrap, data, once) {
      var self = this;
      var $container = $('.taskboard-list', $wrap);
      var $task;

      if (data.length === 0) {
        return;
      }

      if (once) {
        $task = $(self.taskTpl(data));

        self.buildBadges($task, data);
        $task.data('taskInfo', data);
        $wrap.append($task);
      } else {
        $.each(data, function(n, info) {
          var $task2 = $(self.taskTpl(info));

          self.buildBadges($task2, info);
          self.buildMembers($task2, info.members);
          $task2.data('taskInfo', info);
          $container.append($task2);
        });
      }
    },
    buildBadges: function($wrap, data) {
      var self = this;
      var html = '';
      var duedate = data.duedate;
      var subtasks = data.subtasks;
      var attachments = data.attachments;
      var comments = data.comments;
      var num;

      if (duedate.length > 0) {
        html += self.badgesTpl('duedate', duedate.split(/\//, 2).join('/'));
      }

      if (subtasks.length > 0) {
        num = 0;
        $.each(subtasks, function(n, i) {
          if (i.complete) {
            num += 1;
          }
        });

        html += self.badgesTpl('subtasks', num + '/' + subtasks.length);
      }

      if (attachments.length > 0) {
        html += self.badgesTpl('attachments', attachments.length);
      }

      if (comments.length > 0) {
        html += self.badgesTpl('comments', comments.length);
      }

      $wrap.find('.task-badges').html(html);
    },
    buildMembers: function($wrap, data) {
      var self = this;
      var html = '';
      if (data.length === 0) {
        return;
      }
      $.each(data, function(i, n) {
        html += self.membersTpl(n.img);
      });
      $wrap.find('.task-members').html(html);
    },
    initSortable: function() {
      // Sortable
      $('.taskboard-stages').sortable({
        handle: '.taskboard-stage-header'
      });
      $('.taskboard-stage .list-group').sortable({
        connectWith: '.taskboard-stage .list-group'
      });
    },
    handleAddStage: function() {
      // Stage
      var self = this;

      $(document).on('click', '.site-floataction', function() {
        var $model = $('#addStageFrom');

        $('input', $model).val('');
        $('option:first', $('select', $model)).prop('selected', 'selected');
      });

      $(document).on('click', '#taskboard-stage-creat', function() {
        var $model = $('#addStageFrom');
        var $name = $('[name="name"]', $model);

        $('.taskboard-stages').append(self.stageTpl($name.val()));
        self.initSortable();
      });
    },
    handleDeleteStage: function() {
      $(document).on('click', '.taskboard-stage-delete', function() {
        var $this = $(this);

        window.top.layer.alert('您确定要删除吗？', {icon: 4}, function(index) {
          $this.closest('.taskboard-stage').remove();
          window.top.layer.close(index);
        });
      });
    },
    getStage: function($task) {
      return $task.closest('.taskboard-stage');
    },
    initStageDropdown: function() {
      // Stage Dropdown
      $(document).on('click', '.taskboard-stage-actions .dropdown-toggle', function() {
        $(this)
          .next('.dropdown-menu')
          .removeClass('is-edit');

        // judge dropdown side
      });
    },
    handleStageRename: function() {
      $(document).on('click', '.taskboard-stage-rename', function(e) {
        var $header = $(this).closest('.taskboard-stage-header');
        var $menu = $(this).closest('.dropdown-menu');
        var $input = $('.taskboard-stage-rename-input', $menu);
        var $title = $('.taskboard-stage-title', $header);

        $menu.toggleClass('is-edit');
        $input
          .val('')
          .focus()
          .val($title.html());
        e.stopPropagation();
      });

      $(document).on('click', '.taskboard-stage-rename-save', function() {
        var $header = $(this).closest('.taskboard-stage-header');
        var $input = $('.taskboard-stage-rename-input', $header);
        var $title = $('.taskboard-stage-title', $header);
        var value = $input.val();

        if (value.length === 0) {
          return;
        }

        $title.html(value);
      });
    },
    handleAddTask: function() {
      // Task
      var self = this;

      $(document).on('click', '.add-item-toggle, .add-item-add, .add-item-cancel', function() {
        var $this = $(this);
        var $wrap = $this.closest('.action-wrap');
        var $input = $('[name="name"]', $wrap);

        $wrap.toggleClass('action-open');
        if ($this.hasClass('add-item-toggle')) {
          $input.val('');
        }

        if ($this.hasClass('add-item-toggle')) {
          $(document).on('click.add-item', function(e) {
            var $target = $(e.target);
            if ($target.closest('.add-item-wrap').length === 0) {
              $wrap.removeClass('action-open');
              $(document).off('click.add-item');
            }
          });
        } else {
          $(document).off('click.add-item');
        }
      });

      $(document).on('click', '.add-item-add', function() {
        var $this = $(this);
        var $wrap = $this.closest('.action-wrap');
        var $input = $('[name="name"]', $wrap);
        var $list = $('.taskboard-list', $this.closest('.taskboard-stage-content'));
        var data = self.dataTpl();

        if ($input.val().length === 0) {
          return;
        }

        data.title = $input.val();
        self.buildTask($list, data, true);
      });
    },
    handleDeleteTask: function() {
      $(document).on('click', '.taskboard-task-delete', function() {
        var $this = $(this);

        window.top.layer.alert('确定要删除此任务吗？', function(index) {
          $this
            .closest('.slidePanel')
            .data('slidePanel')
            .target.remove();
          $('.slidePanel-close').trigger('click');
          window.top.layer.close(index);
        });
      });
    },
    handleTaskInput: function() {
      var self = this;
      $(document).on('click', '.taskboard-list .checkbox-custom input', function(e) {
        var $this = $(this);
        var $target = $this.closest('.list-group-item');

        self.dataChange($target, 'complete', $this.prop('checked'));
        e.stopPropagation();
      });
    },
    handlSlidePanelPlugin: function() {
      // Init SlidePanel
      var self = this;

      if (typeof $.slidePanel === 'undefined') {
        return;
      }

      $(document).on('click', '[data-taskboard="slidePanel"]', function(e) {
        var $target = $(e.target).closest('.list-group-item');
        $.slidePanel.show(
          {
            url: $(this).data('url'),
            target: $target
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
            afterLoad: function(object) {
              var that = this;
              var $etarget = $(object.target);
              var info = $etarget.data('taskInfo');

              this.$panel
                .find('.' + this.options.classes.base + '-scrollable')
                .mCustomScrollbar($.concatCpt('mCustomScrollbar'));

              this.$panel.find('#task-description').markdown({
                language: 'zh'
              });
              if (info.duedate.length > 0) {
                this.$panel.find('#taskDatepicker').data('date', info.duedate);
              }
              this.$panel
                .find('#taskDatepicker')
                .datepicker(
                  $.concatCpt('datepicker', {
                    autoclose: false,
                    todayHighlight: true,
                    language: 'zh-CN'
                  })
                )
                .on('changeDate', function() {
                  $('#taskDatepickerInput').val(
                    that.$panel.find('#taskDatepicker').datepicker('getFormattedDate')
                  );
                });

              this.$panel.data('slidePanel', object);

              $(document).off('click.slidePanelDatepicker');
              $(document).on('click.slidePanelDatepicker', 'span, td, th', function() {
                e.stopPropagation();
              });
            },
            afterShow: function() {
              var that = this;
              $(document).on('click.slidePanelShow', function() {
                if (
                  $(e.target).closest('.slidePanel').length === 0 &&
                  $(e.target).closest('body').length === 1
                ) {
                  that.hide();
                }
              });
            },
            afterHide: function() {
              $(document).off('click.slidePanelShow');
              $(document).off('click.slidePanelDatepicker');
            },
            contentFilter: function(data, object) {
              var $etarget = $(object.target);
              var info = $etarget.data('taskInfo');
              var $panel = $(data);
              var $checked;

              $('.stage-name', $panel).html(
                $('.taskboard-stage-title', self.getStage($etarget)).html()
              );

              $('.task-title', $panel).html(info.title);

              switch (info.priority) {
                case 'high':
                  $checked = $('#priorityHigh', $panel);
                  break;
                case 'urgent':
                  $checked = $('#priorityUrgent', $panel);
                  break;
                default:
                  $checked = $('#priorityNormal', $panel);
                  break;
              }
              $checked.prop('checked', true);

              self.handleSelective($('[data-plugin="jquery-selective"]', $panel), info.members);

              if (info.description.length === 0) {
                $('.description', $panel).addClass('is-empty');
              } else {
                $('.description-content', $panel).html(info.description);
              }

              if (info.subtasks.length !== 0) {
                $.each(info.subtasks, function(n, subtask) {
                  var $subtask = $(self.subtaskTpl(subtask));
                  $('.subtasks-list', $panel).append($subtask);
                });
                $('.subtasks', $panel).toggleClass('is-show');
              }

              if (info.attachments.length !== 0) {
                $.each(info.attachments, function(n, attachment) {
                  var $attachment = $(self.attachmentTpl(attachment));
                  $('.attachments-list', $panel).append($attachment);
                });
                $('.attachments', $panel).toggleClass('is-show');
              }

              if (info.comments.length !== 0) {
                $.each(info.comments, function(n, comment) {
                  var $comment = $(
                    self.commentTpl(comment.src, comment.user, comment.time, comment.content)
                  );
                  $('.comments-history', $panel).append($comment);
                });
              }

              return $panel;
            }
          })
        );

        e.stopPropagation();
      });

      $(document).on('click', '#fileuploadToggle', function() {
        $('#fileupload').trigger('click');
      });
    },
    handleSelective: function($target, selected) {
      // SlidePanel Section Handle
      var self = this;
      var getSelected = function() {
        var that = this;
        var arr = [];

        $.each(this._options.getOptions(this), function(n, option) {
          $.each(that.options.local, function(i, user) {
            if (user.id === $(option).val()) {
              arr.push(user);
            }
          });
        });
        return arr;
      };
      var members = [
        {
          id: 'uid_1',
          name: '梅小燕',
          img: '/public/images/portraits/1.jpg'
        },
        {
          id: 'uid_2',
          name: '赵桦',
          img: '/public/images/portraits/2.jpg'
        },
        {
          id: 'uid_3',
          name: '唐雪琴',
          img: '/public/images/portraits/3.jpg'
        },
        {
          id: 'uid_4',
          name: '曹洁群',
          img: '/public/images/portraits/4.jpg'
        },
        {
          id: 'uid_5',
          name: '陈媚婉',
          img: '/public/images/portraits/5.jpg'
        },
        {
          id: 'uid_6',
          name: '嵇慧莉',
          img: '/public/images/portraits/6.jpg'
        }
      ];

      $target.selective({
        namespace: 'addMember',
        local: members,
        selected: selected,
        buildFromHtml: false,
        tpl: {
          optionValue: function(data) {
            return data.id;
          },
          frame: function() {
            return (
              '<div class="' +
              this.namespace +
              '">' +
              this.options.tpl.items.call(this) +
              '<div class="' +
              this.namespace +
              '-trigger">' +
              this.options.tpl.triggerButton.call(this) +
              '<div class="' +
              this.namespace +
              '-trigger-dropdown">' +
              this.options.tpl.list.call(this) +
              '</div>' +
              '</div>' +
              '</div>'
            );
          },
          triggerButton: function() {
            return (
              '<div class="' + this.namespace + '-trigger-button"><i class="wb-plus"></i></div>'
            );
          },
          listItem: function(data) {
            return (
              '<li class="' +
              this.namespace +
              '-list-item"><img class="avatar" src="' +
              data.img +
              '">' +
              data.name +
              '</li>'
            );
          },
          item: function(data) {
            return (
              '<li class="' +
              this.namespace +
              '-item"><img class="avatar" src="' +
              data.img +
              '">' +
              this.options.tpl.itemRemove.call(this) +
              '</li>'
            );
          },
          itemRemove: function() {
            return (
              '<span class="' + this.namespace + '-remove"><i class="wb-minus-circle"></i></span>'
            );
          },
          option: function(data) {
            return (
              '<option value="' +
              this.options.tpl.optionValue.call(this, data) +
              '">' +
              data.name +
              '</option>'
            );
          }
        },
        onAfterItemAdd: function() {
          var $etarget = this.$el.closest('.slidePanel').data('slidePanel').target;
          var arr = getSelected.call(this);
          self.dataChange($etarget, 'members', arr);
        },
        onAfterItemRemove: function() {
          var $etarget = this.$el.closest('.slidePanel').data('slidePanel').target;
          var arr = getSelected.call(this);
          self.dataChange($etarget, 'members', arr);
        }
      });
    },
    handlePriority: function() {
      var self = this;
      $(document).on('click', '[name="priorities"]', function() {
        var $this = $(this);
        var $target = $this.closest('.slidePanel').data('slidePanel').target;

        self.dataChange($target, 'priority', $this.data('priority'));
      });
    },
    handleEditor: function() {
      var self = this;
      $(document).on(
        'click',
        '.slidePanel .task-title, .taskboard-task-edit, .description-toggle',
        function() {
          var $this = $(this);
          var $target = $this.closest('.slidePanel').data('slidePanel').target;
          var data = $target.data('taskInfo');

          $('#task-title').val(data.title);
          $('#task-description').val(data.description);
          $this
            .closest('.slidePanel')
            .find('.task-main')
            .addClass('is-edit');
        }
      );

      $(document).on('click', '.task-main-editor-save', function() {
        var $item = $(this);
        var $target = $item.closest('.slidePanel').data('slidePanel').target;

        self.dataChange($target, 'title', $('#task-title').val());
        self.dataChange($target, 'description', $('#task-description').val());

        $item
          .closest('.slidePanel')
          .find('.task-main')
          .removeClass('is-edit');
        if ($('#task-description').val().length === 0) {
          $('.description').addClass('is-empty');
        } else {
          $('.description').removeClass('is-empty');
        }
      });

      $(document).on('click', '.task-main-editor-cancel', function() {
        $(this)
          .closest('.slidePanel')
          .find('.task-main')
          .removeClass('is-edit');
      });
    },
    handleSubtasks: function() {
      var self = this;
      $(document).on('click', '.subtask-toggle', function() {
        var length = $('.subtask').length;
        var $input = $('.subtasks-add .subtask-title');
        var $subtasks = $('.subtasks');

        $input.val('');
        if (length === 0) {
          $subtasks.addClass('is-show');
        }
        $subtasks.addClass('is-edit');

        $input.focus();

        $(document).on('click.subtask-add', function(e) {
          var $target = $(e.target);
          if ($target.closest($('.subtasks-add')).length === 0) {
            $subtasks.removeClass('is-edit');
            $(document).off('click.subtask-add');
          }
        });
      });

      $(document).on('click', '.subtask-add-save', function() {
        var length = $('.subtask').length;
        var $subtasks = $('.subtasks');
        var $input = $('.subtasks-add .subtask-title');
        var value = $input.val();
        var $target = $(this)
          .closest('.slidePanel')
          .data('slidePanel').target;
        var data;
        var $subtask;

        if (value.length === 0) {
          if (length === 0) {
            $subtasks.removeClass('is-show');
          }
        } else {
          data = {
            title: value,
            complete: false
          };
          $subtask = $(self.subtaskTpl(data));

          $('.subtasks-list').append($subtask);
          self.dataChange($target, 'subtasks', data, length);
        }
        $input.val('').focus();
      });

      $(document).on('click', '.subtask-add-cancel', function() {
        $('.subtasks').removeClass('is-edit');
        $(document).off('click.subtask-add');
      });

      $(document).on('click', '.subtask input', function() {
        var $this = $(this);
        var $target = $this.closest('.slidePanel').data('slidePanel').target;
        var $subtask = $this.closest('.subtask');
        var index = $subtask.index();

        self.dataChange($target, 'subtasks', $this.prop('checked'), index, 'complete');
      });

      $(document).on('click', '.subtask .title', function() {
        var $this = $(this);
        var $target = $this.closest('.slidePanel').data('slidePanel').target;
        var data = $target.data('taskInfo');
        var $subtask = $this.closest('.subtask');
        var index = $subtask.index();
        var $input = $('.subtask-title', $subtask);

        $subtask.addClass('is-edit');
        $input
          .val('')
          .focus()
          .val(data.subtasks[index].title);

        $(document).on('click.subtask', function(e) {
          var $etarget = $(e.target);

          if ($etarget.closest($subtask).length === 0) {
            $subtask.removeClass('is-edit');
            $(document).off('click.subtask');
          }
        });
      });

      $(document).on('click', '.subtask-editor-save', function() {
        var $item = $(this);
        var $etarget = $item.closest('.slidePanel').data('slidePanel').target;
        var $subtask = $item.closest('.subtask');
        var index = $subtask.index();

        self.dataChange($etarget, 'subtasks', $('.subtask-title', $subtask).val(), index, 'title');
        $subtask.removeClass('is-edit');
        $(document).off('click.subtask');
      });

      $(document).on('click', '.subtask-editor-delete', function() {
        var $this = $(this);

        window.top.layer.alert('确定要删除子任务吗？', function(_index) {
          var $target = $this.closest('.slidePanel').data('slidePanel').target;
          var $subtask = $this.closest('.subtask');
          var index = $subtask.index();

          self.dataDelete($target, 'subtasks', index);
          $subtask.remove();
          $(document).off('click.subtask');
          if ($('.subtask').length === 0) {
            $('.subtasks').removeClass('is-show');
          }
          window.top.layer.close(_index);
        });
      });
    },
    handleDatepicker: function() {
      var self = this;
      $(document).on('click', '.due-date-save', function() {
        var $item = $(this);
        var $target = $item.closest('.slidePanel').data('slidePanel').target;
        var value = $('#taskDatepickerInput').val();

        if (value.length > 0) {
          self.dataChange($target, 'duedate', value);
        }
      });
      $(document).on('click', '.due-date-delete', function() {
        var $item = $(this);
        var $target = $item.closest('.slidePanel').data('slidePanel').target;
        var data = $target.data('taskInfo');

        if (data.duedate.length === 0) {
          return;
        }
        self.dataDelete($target, 'duedate');
        $('#taskDatepicker').datepicker('clearDates');
      });
    },
    dataDelete: function($target, name, index) {
      // Data
      if (index) {
        $target.data('taskInfo')[name].splice(index, 1);
        this.dataDeleteResponse($target, name, index);
      } else {
        $target.data('taskInfo')[name] = '';
        this.dataChangeResponse($target, name);
      }
    },
    dataDeleteResponse: function($target, name) {
      switch (name) {
        case 'duedate':
          this.buildBadges($target, $target.data('taskInfo'));
          break;
        case 'subtasks':
          this.buildBadges($target, $target.data('taskInfo'));
          break;
        default:
          break;
      }
    },
    dataChange: function($target, name, content, index, subname) {
      if (content.length === 0) {
        return;
      }
      if (index !== undefined) {
        if (subname) {
          $target.data('taskInfo')[name][index][subname] = content;
        } else {
          $target.data('taskInfo')[name][index] = content;
        }
      } else {
        $target.data('taskInfo')[name] = content;
      }
      this.dataChangeResponse($target, name, content, index, subname);
    },
    dataChangeResponse: function($target, name, content, index, subname) {
      switch (name) {
        case 'title':
          $('.task-title', $target).html(content);
          $('.slidePanel .task-title').html(content);
          break;
        case 'description':
          $('.slidePanel .description-content').html(content);
          break;
        case 'priority':
          $target
            .removeClass('priority-normal priority-high priority-urgent')
            .addClass('priority-' + content);
          break;
        case 'duedate':
          this.buildBadges($target, $target.data('taskInfo'));
          break;
        case 'members':
          this.buildMembers($target, $target.data('taskInfo').members);
          break;
        case 'subtasks':
          if (subname === 'title') {
            $('.title', $('.subtasks-list .subtask')[index]).html(content);
          } else {
            this.buildBadges($target, $target.data('taskInfo'));
          }
          break;
        case 'attachments':
          break;
        case 'comments':
          break;
        default:
          break;
      }
    },
    run: function() {
      this.init();

      this.handleAddStage();
      this.handleDeleteStage();

      this.handleAddTask();
      this.handleDeleteTask();

      this.handleTaskInput();

      this.initStageDropdown();
      this.handleStageRename();

      this.handleDatepicker();
      this.handlSlidePanelPlugin();

      this.handleEditor();
      this.handleSubtasks();
      this.handlePriority();

      $.leavePage = function() {
        $.slidePanel = null;
      };
    }
  };

  taskboard.run();
})(document, window, jQuery);
