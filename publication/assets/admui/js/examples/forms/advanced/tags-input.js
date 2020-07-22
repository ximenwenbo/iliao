/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function() {
  /* global Bloodhound, toastr */

  // Tokenfield 中使用的 Typeahead 参数
  var engine = new Bloodhound({
    local: [
      {
        value: 'red-红色'
      },
      {
        value: 'blue-蓝色'
      },
      {
        value: 'green-绿色'
      },
      {
        value: 'yellow-黄色'
      },
      {
        value: 'violet-紫罗兰'
      },
      {
        value: 'brown-棕色'
      },
      {
        value: 'purple-紫色'
      },
      {
        value: 'black-黑色'
      },
      {
        value: 'white-白色'
      }
    ],
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
    queryTokenizer: Bloodhound.tokenizers.whitespace
  });

  $(function() {
    var $input = $('#inputTagsObject');
    var $inputTab = $('#inputTagsCategorizing');

    // Tokenfield 初始化
    $('#inputTokenfieldTypeahead').tokenfield({
      typeahead: [
        null,
        {
          name: 'engine',
          displayKey: 'value',
          source: engine.ttAdapter()
        }
      ]
    });

    // Tokenfield 事件
    $('#inputTokenfieldEvents')
      .on('tokenfield:createtoken', function(event) {
        var opts = event;
        var data = event.attrs.value.split('|');

        opts.attrs.value = data[1] || data[0];
        opts.attrs.label = data[1] ? data[0] + ' (' + data[1] + ')' : data[0];
      })
      .on('tokenfield:createdtoken', function(e) {
        // 邮件验证
        var re = /\S+@\S+\.\S+/;
        var valid = re.test(e.attrs.value);
        if (!valid) {
          $(e.relatedTarget).addClass('invalid');
        }
      })
      .on('tokenfield:edittoken', function(e) {
        var label;
        var options = e;
        if (options.attrs.label !== options.attrs.value) {
          label = options.attrs.label.split(' (');
          options.attrs.value = label[0] + '|' + options.attrs.value;
        }
      })
      .on('tokenfield:removedtoken', function(e) {
        var values;
        if (e.attrs.length > 1) {
          values = $.map(e.attrs, function(attrs) {
            return attrs.value;
          });
          toastr.info(e.attrs.length + '已删除：' + values.join(', '));
        } else {
          toastr.info('已删除：' + e.attrs.value);
        }
      })
      .tokenfield();

    // 标签使用对象
    $input.tagsinput(
      $.concatCpt('tagsinput', {
        itemValue: 'value',
        itemText: 'text'
      })
    );

    $input.tagsinput('add', {
      value: 1,
      text: '北京',
      continent: '北京'
    });

    $input.tagsinput('add', {
      value: 2,
      text: '广州',
      continent: '广东'
    });

    $input.tagsinput('add', {
      value: 3,
      text: '韶关',
      continent: '广东'
    });

    $input.tagsinput('add', {
      value: 4,
      text: '深圳',
      continent: '广东'
    });
    $input.tagsinput('add', {
      value: 5,
      text: '珠海',
      continent: '广东'
    });

    // 标签分类
    $inputTab.tagsinput(
      $.concatCpt('tagsinput', {
        tagClass: function(item) {
          switch (item.continent) {
            case '北京':
              return 'badge badge-primary';
            case '广东':
              return 'badge badge-danger';
            case '浙江':
              return 'badge badge-success';
            case '新疆':
              return 'badge badge-default';
            case '江苏':
              return 'badge badge-warning';
            default:
              return undefined;
          }
        },
        itemValue: 'value',
        itemText: 'text'
      })
    );

    $inputTab.tagsinput('add', {
      value: 1,
      text: '北京',
      continent: '北京'
    });

    $inputTab.tagsinput('add', {
      value: 2,
      text: '广州',
      continent: '广东'
    });

    $inputTab.tagsinput('add', {
      value: 3,
      text: '韶关',
      continent: '浙江'
    });

    $inputTab.tagsinput('add', {
      value: 4,
      text: '乌鲁木齐',
      continent: '新疆'
    });

    $inputTab.tagsinput('add', {
      value: 5,
      text: '南京',
      continent: '江苏'
    });
  });
})();
