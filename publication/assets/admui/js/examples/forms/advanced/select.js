/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function(document, window, $) {
  'use strict';

  /* global Bloodhound */

  // Typeahead 输入提示公用数据
  var states = [
    'Andorra , 安道尔',
    'Afghanistan , 阿富汗',
    'Antigua and Barbuda , 安提瓜和巴布达',
    'Anguilla , 安格拉',
    'Albania , 阿尔巴尼亚',
    'Armenia , 亚美尼亚',
    'Netherlands Antilles , 荷兰属地',
    'Angola , 安哥拉',
    'Antarctica , 南极洲',
    'Argentina , 阿根廷',
    'American Samoa , 东萨摩亚',
    'Austria , 奥地利',
    'Australia , 澳大利亚',
    'Aruba , 阿鲁巴',
    'Azerbaijan , 阿塞拜疆',
    'Bosnia Hercegovina , 波黑',
    'Barbados , 巴巴多斯',
    'Bangladesh , 孟加拉国',
    'Belgium , 比利时',
    'Burkina Faso , 布基纳法索',
    'Bulgaria , 保加利亚',
    'Bahrain , 巴林',
    'Burundi , 布隆迪',
    'Benin , 贝宁',
    'Bermuda , 百慕大 ',
    'China, 中国'
  ];
  var examples = {
    example1: function() {
      // Multi-Select（select多选）
      $('.multi-select-methods').multiSelect();
      $('#buttonSelectAll').click(function() {
        $('.multi-select-methods').multiSelect('select_all');
        return false;
      });
      $('#buttonDeselectAll').click(function() {
        $('.multi-select-methods').multiSelect('deselect_all');
        return false;
      });
      $('#buttonSelectSome').click(function() {
        $('.multi-select-methods').multiSelect('select', ['BMW', 'Audi', 'Benz']);
        return false;
      });
      $('#buttonDeselectSome').click(function() {
        $('.multi-select-methods').multiSelect('select', ['BMW', 'Audi', 'Benz']);
        return false;
      });
      $('#buttonRefresh').on('click', function() {
        $('.multi-select-methods').multiSelect('refresh');
        return false;
      });
      $('#buttonAdd').on('click', function() {
        $('.multi-select-methods').multiSelect('addOption', {
          value: 42,
          text: '测试项 42',
          index: 0
        });
        return false;
      });
    },
    example2: function() {
      // Typeahead 输入提示 --- 基本 & 样式
      var substringMatcher = function(strs) {
        return function(q, cb) {
          var matches;
          var substrRegex;

          matches = [];

          substrRegex = new RegExp(q, 'i');

          $.each(strs, function(i, str) {
            if (substrRegex.test(str)) {
              matches.push(str);
            }
          });

          cb(matches);
        };
      };

      $('#exampleTypeaheadBasic, #exampleTypeaheadStyle').typeahead(
        {
          hint: true,
          highlight: true,
          minLength: 1
        },
        {
          name: 'states',
          source: substringMatcher(states)
        }
      );
    },
    example3: function() {
      // Typeahead 输入提示 --- bloodhound
      var state = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: states
      });

      $('#exampleTypeaheadBloodhound').typeahead(
        {
          hint: true,
          highlight: true,
          minLength: 1
        },
        {
          name: 'states',
          source: state
        }
      );
    },
    example4: function() {
      // Typeahead 输入提示 --- 异步加载
      var countries = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.whitespace,
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: '/public/data/examples/forms/countries.json'
      });

      $('#exampleTypeaheadPrefetch').typeahead(null, {
        name: 'countries',
        source: countries
      });
    },
    example5: function() {
      // 级联选择
      $('#cityChina').cxSelect({
        selects: ['province', 'city', 'area'],
        emptyStyle: 'hidden'
      });

      $('#cityChinaVal').cxSelect({
        selects: ['province', 'city', 'area'],
        emptyStyle: 'hidden',
        nodata: 'none'
      });

      $('#globalLocation').cxSelect({
        selects: ['country', 'state', 'city', 'region'],
        emptyStyle: 'hidden',
        nodata: 'none'
      });
      $('#customData').cxSelect({
        selects: ['first', 'second', 'third', 'fourth', 'fifth'],
        emptyStyle: 'hidden',
        required: true,
        jsonValue: 'v',
        data: [
          {
            v: '1',
            n: '第一级',
            s: [
              {
                v: '2',
                n: '第二级',
                s: [
                  {
                    v: '3',
                    n: '第三级',
                    s: [
                      {
                        v: '4',
                        n: '第四级',
                        s: [
                          {
                            v: '5',
                            n: '第五级',
                            s: [{v: '6', n: '第六级'}]
                          }
                        ]
                      }
                    ]
                  }
                ]
              }
            ]
          },
          {
            v: 'test number',
            n: '测试数字',
            s: [
              {
                v: 'text',
                n: '文本类型',
                s: [
                  {v: '4', n: '4'},
                  {v: '5', n: '5'},
                  {v: '6', n: '6'},
                  {v: '7', n: '7'},
                  {v: '8', n: '8'},
                  {v: '9', n: '9'},
                  {v: '10', n: '10'}
                ]
              },
              {
                v: 'number',
                n: '数值类型',
                s: [
                  {v: 11, n: 11},
                  {v: 12, n: 12},
                  {v: 13, n: 13},
                  {v: 14, n: 14},
                  {v: 15, n: 15},
                  {v: 16, n: 16},
                  {v: 17, n: 17}
                ]
              }
            ]
          },
          {
            v: 'test boolean',
            n: '测试 Boolean 类型',
            s: [{v: true, n: true}, {v: false, n: false}]
          },
          {
            v: 'test quotes',
            n: '测试属性不加引号',
            s: [{v: 'quotes', n: '引号'}]
          },
          {
            v: 'test other',
            n: '测试奇怪的值',
            s: [
              {v: '[]', n: '数组（空）'},
              {v: [1, 2, 3], n: '数组（数值）'},
              {v: ['a', 'b', 'c'], n: '数组（文字）'},
              {v: new Date(), n: '日期'},
              {v: new RegExp('\\d+'), n: '正则对象'},
              {v: /\d+/, n: '正则直接量'},
              {v: {}, n: '对象'},
              {v: document.getElementById('custom_data'), n: 'DOM'},
              {v: null, n: 'Null'},
              {n: '未设置 value'}
            ]
          },
          {v: '', n: '无子级'}
        ]
      });
    }
  };

  $(function() {
    // 初始化示例
    $.each(examples, function(i, n) {
      n();
    });
  });
})(document, window, jQuery);
