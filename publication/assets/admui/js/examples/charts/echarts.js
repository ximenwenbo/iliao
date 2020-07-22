/**
 * Admui-iframe v2.0.0 (http://www.admui.com/)
 * Copyright 2015-2018 Admui Team
 * Licensed under the Admui License 1.1 (http://www.admui.com/about/license)
 */
(function() {
  'use strict';

  /* globals echarts */

  // 图表示例
  var echartsPage = {
    exampleOne: function() {
      // 全国主要城市空气质量示例
      var myChart = echarts.init(document.getElementById('echartsDemo1'));

      var data = [
        {name: '海门', value: 9},
        {name: '鄂尔多斯', value: 12},
        {name: '招远', value: 12},
        {name: '舟山', value: 12},
        {name: '齐齐哈尔', value: 14},
        {name: '盐城', value: 15},
        {name: '赤峰', value: 16},
        {name: '青岛', value: 18},
        {name: '乳山', value: 18},
        {name: '金昌', value: 19},
        {name: '泉州', value: 21},
        {name: '莱西', value: 21},
        {name: '日照', value: 21},
        {name: '胶南', value: 22},
        {name: '南通', value: 23},
        {name: '拉萨', value: 24},
        {name: '云浮', value: 24},
        {name: '梅州', value: 25},
        {name: '文登', value: 25},
        {name: '上海', value: 25},
        {name: '攀枝花', value: 25},
        {name: '威海', value: 25},
        {name: '承德', value: 25},
        {name: '厦门', value: 26},
        {name: '汕尾', value: 26},
        {name: '潮州', value: 26},
        {name: '丹东', value: 27},
        {name: '太仓', value: 27},
        {name: '曲靖', value: 27},
        {name: '烟台', value: 28},
        {name: '福州', value: 29},
        {name: '瓦房店', value: 30},
        {name: '即墨', value: 30},
        {name: '抚顺', value: 31},
        {name: '玉溪', value: 31},
        {name: '张家口', value: 31},
        {name: '阳泉', value: 31},
        {name: '莱州', value: 32},
        {name: '湖州', value: 32},
        {name: '汕头', value: 32},
        {name: '昆山', value: 33},
        {name: '宁波', value: 33},
        {name: '湛江', value: 33},
        {name: '揭阳', value: 34},
        {name: '荣成', value: 34},
        {name: '连云港', value: 35},
        {name: '葫芦岛', value: 35},
        {name: '常熟', value: 36},
        {name: '东莞', value: 36},
        {name: '河源', value: 36},
        {name: '淮安', value: 36},
        {name: '泰州', value: 36},
        {name: '南宁', value: 37},
        {name: '营口', value: 37},
        {name: '惠州', value: 37},
        {name: '江阴', value: 37},
        {name: '蓬莱', value: 37},
        {name: '韶关', value: 38},
        {name: '嘉峪关', value: 38},
        {name: '广州', value: 38},
        {name: '延安', value: 38},
        {name: '太原', value: 39},
        {name: '清远', value: 39},
        {name: '中山', value: 39},
        {name: '昆明', value: 39},
        {name: '寿光', value: 40},
        {name: '盘锦', value: 40},
        {name: '长治', value: 41},
        {name: '深圳', value: 41},
        {name: '珠海', value: 42},
        {name: '宿迁', value: 43},
        {name: '咸阳', value: 43},
        {name: '铜川', value: 44},
        {name: '平度', value: 44},
        {name: '佛山', value: 44},
        {name: '海口', value: 44},
        {name: '江门', value: 45},
        {name: '章丘', value: 45},
        {name: '肇庆', value: 46},
        {name: '大连', value: 47},
        {name: '临汾', value: 47},
        {name: '吴江', value: 47},
        {name: '石嘴山', value: 49},
        {name: '沈阳', value: 50},
        {name: '苏州', value: 50},
        {name: '茂名', value: 50},
        {name: '嘉兴', value: 51},
        {name: '长春', value: 51},
        {name: '胶州', value: 52},
        {name: '银川', value: 52},
        {name: '张家港', value: 52},
        {name: '三门峡', value: 53},
        {name: '锦州', value: 54},
        {name: '南昌', value: 54},
        {name: '柳州', value: 54},
        {name: '三亚', value: 54},
        {name: '自贡', value: 56},
        {name: '吉林', value: 56},
        {name: '阳江', value: 57},
        {name: '泸州', value: 57},
        {name: '西宁', value: 57},
        {name: '宜宾', value: 58},
        {name: '呼和浩特', value: 58},
        {name: '成都', value: 58},
        {name: '大同', value: 58},
        {name: '镇江', value: 59},
        {name: '桂林', value: 59},
        {name: '张家界', value: 59},
        {name: '宜兴', value: 59},
        {name: '北海', value: 60},
        {name: '西安', value: 61},
        {name: '金坛', value: 62},
        {name: '东营', value: 62},
        {name: '牡丹江', value: 63},
        {name: '遵义', value: 63},
        {name: '绍兴', value: 63},
        {name: '扬州', value: 64},
        {name: '常州', value: 64},
        {name: '潍坊', value: 65},
        {name: '重庆', value: 66},
        {name: '台州', value: 67},
        {name: '南京', value: 67},
        {name: '滨州', value: 70},
        {name: '贵阳', value: 71},
        {name: '无锡', value: 71},
        {name: '本溪', value: 71},
        {name: '克拉玛依', value: 72},
        {name: '渭南', value: 72},
        {name: '马鞍山', value: 72},
        {name: '宝鸡', value: 72},
        {name: '焦作', value: 75},
        {name: '句容', value: 75},
        {name: '北京', value: 79},
        {name: '徐州', value: 79},
        {name: '衡水', value: 80},
        {name: '包头', value: 80},
        {name: '绵阳', value: 80},
        {name: '乌鲁木齐', value: 84},
        {name: '枣庄', value: 84},
        {name: '杭州', value: 84},
        {name: '淄博', value: 85},
        {name: '鞍山', value: 86},
        {name: '溧阳', value: 86},
        {name: '库尔勒', value: 86},
        {name: '安阳', value: 90},
        {name: '开封', value: 90},
        {name: '济南', value: 92},
        {name: '德阳', value: 93},
        {name: '温州', value: 95},
        {name: '九江', value: 96},
        {name: '邯郸', value: 98},
        {name: '临安', value: 99},
        {name: '兰州', value: 99},
        {name: '沧州', value: 100},
        {name: '临沂', value: 103},
        {name: '南充', value: 104},
        {name: '天津', value: 105},
        {name: '富阳', value: 106},
        {name: '泰安', value: 112},
        {name: '诸暨', value: 112},
        {name: '郑州', value: 113},
        {name: '哈尔滨', value: 114},
        {name: '聊城', value: 116},
        {name: '芜湖', value: 117},
        {name: '唐山', value: 119},
        {name: '平顶山', value: 119},
        {name: '邢台', value: 119},
        {name: '德州', value: 120},
        {name: '济宁', value: 120},
        {name: '荆州', value: 127},
        {name: '宜昌', value: 130},
        {name: '义乌', value: 132},
        {name: '丽水', value: 133},
        {name: '洛阳', value: 134},
        {name: '秦皇岛', value: 136},
        {name: '株洲', value: 143},
        {name: '石家庄', value: 147},
        {name: '莱芜', value: 148},
        {name: '常德', value: 152},
        {name: '保定', value: 153},
        {name: '湘潭', value: 154},
        {name: '金华', value: 157},
        {name: '岳阳', value: 169},
        {name: '长沙', value: 175},
        {name: '衢州', value: 177},
        {name: '廊坊', value: 193},
        {name: '菏泽', value: 194},
        {name: '合肥', value: 229},
        {name: '武汉', value: 273},
        {name: '大庆', value: 279}
      ];
      var geoCoordMap = {
        海门: [121.15, 31.89],
        鄂尔多斯: [109.781327, 39.608266],
        招远: [120.38, 37.35],
        舟山: [122.207216, 29.985295],
        齐齐哈尔: [123.97, 47.33],
        盐城: [120.13, 33.38],
        赤峰: [118.87, 42.28],
        青岛: [120.33, 36.07],
        乳山: [121.52, 36.89],
        金昌: [102.188043, 38.520089],
        泉州: [118.58, 24.93],
        莱西: [120.53, 36.86],
        日照: [119.46, 35.42],
        胶南: [119.97, 35.88],
        南通: [121.05, 32.08],
        拉萨: [91.11, 29.97],
        云浮: [112.02, 22.93],
        梅州: [116.1, 24.55],
        文登: [122.05, 37.2],
        上海: [121.48, 31.22],
        攀枝花: [101.718637, 26.582347],
        威海: [122.1, 37.5],
        承德: [117.93, 40.97],
        厦门: [118.1, 24.46],
        汕尾: [115.375279, 22.786211],
        潮州: [116.63, 23.68],
        丹东: [124.37, 40.13],
        太仓: [121.1, 31.45],
        曲靖: [103.79, 25.51],
        烟台: [121.39, 37.52],
        福州: [119.3, 26.08],
        瓦房店: [121.979603, 39.627114],
        即墨: [120.45, 36.38],
        抚顺: [123.97, 41.97],
        玉溪: [102.52, 24.35],
        张家口: [114.87, 40.82],
        阳泉: [113.57, 37.85],
        莱州: [119.942327, 37.177017],
        湖州: [120.1, 30.86],
        汕头: [116.69, 23.39],
        昆山: [120.95, 31.39],
        宁波: [121.56, 29.86],
        湛江: [110.359377, 21.270708],
        揭阳: [116.35, 23.55],
        荣成: [122.41, 37.16],
        连云港: [119.16, 34.59],
        葫芦岛: [120.836932, 40.711052],
        常熟: [120.74, 31.64],
        东莞: [113.75, 23.04],
        河源: [114.68, 23.73],
        淮安: [119.15, 33.5],
        泰州: [119.9, 32.49],
        南宁: [108.33, 22.84],
        营口: [122.18, 40.65],
        惠州: [114.4, 23.09],
        江阴: [120.26, 31.91],
        蓬莱: [120.75, 37.8],
        韶关: [113.62, 24.84],
        嘉峪关: [98.289152, 39.77313],
        广州: [113.23, 23.16],
        延安: [109.47, 36.6],
        太原: [112.53, 37.87],
        清远: [113.01, 23.7],
        中山: [113.38, 22.52],
        昆明: [102.73, 25.04],
        寿光: [118.73, 36.86],
        盘锦: [122.070714, 41.119997],
        长治: [113.08, 36.18],
        深圳: [114.07, 22.62],
        珠海: [113.52, 22.3],
        宿迁: [118.3, 33.96],
        咸阳: [108.72, 34.36],
        铜川: [109.11, 35.09],
        平度: [119.97, 36.77],
        佛山: [113.11, 23.05],
        海口: [110.35, 20.02],
        江门: [113.06, 22.61],
        章丘: [117.53, 36.72],
        肇庆: [112.44, 23.05],
        大连: [121.62, 38.92],
        临汾: [111.5, 36.08],
        吴江: [120.63, 31.16],
        石嘴山: [106.39, 39.04],
        沈阳: [123.38, 41.8],
        苏州: [120.62, 31.32],
        茂名: [110.88, 21.68],
        嘉兴: [120.76, 30.77],
        长春: [125.35, 43.88],
        胶州: [120.03336, 36.264622],
        银川: [106.27, 38.47],
        张家港: [120.555821, 31.875428],
        三门峡: [111.19, 34.76],
        锦州: [121.15, 41.13],
        南昌: [115.89, 28.68],
        柳州: [109.4, 24.33],
        三亚: [109.511909, 18.252847],
        自贡: [104.778442, 29.33903],
        吉林: [126.57, 43.87],
        阳江: [111.95, 21.85],
        泸州: [105.39, 28.91],
        西宁: [101.74, 36.56],
        宜宾: [104.56, 29.77],
        呼和浩特: [111.65, 40.82],
        成都: [104.06, 30.67],
        大同: [113.3, 40.12],
        镇江: [119.44, 32.2],
        桂林: [110.28, 25.29],
        张家界: [110.479191, 29.117096],
        宜兴: [119.82, 31.36],
        北海: [109.12, 21.49],
        西安: [108.95, 34.27],
        金坛: [119.56, 31.74],
        东营: [118.49, 37.46],
        牡丹江: [129.58, 44.6],
        遵义: [106.9, 27.7],
        绍兴: [120.58, 30.01],
        扬州: [119.42, 32.39],
        常州: [119.95, 31.79],
        潍坊: [119.1, 36.62],
        重庆: [106.54, 29.59],
        台州: [121.420757, 28.656386],
        南京: [118.78, 32.04],
        滨州: [118.03, 37.36],
        贵阳: [106.71, 26.57],
        无锡: [120.29, 31.59],
        本溪: [123.73, 41.3],
        克拉玛依: [84.77, 45.59],
        渭南: [109.5, 34.52],
        马鞍山: [118.48, 31.56],
        宝鸡: [107.15, 34.38],
        焦作: [113.21, 35.24],
        句容: [119.16, 31.95],
        北京: [116.46, 39.92],
        徐州: [117.2, 34.26],
        衡水: [115.72, 37.72],
        包头: [110, 40.58],
        绵阳: [104.73, 31.48],
        乌鲁木齐: [87.68, 43.77],
        枣庄: [117.57, 34.86],
        杭州: [120.19, 30.26],
        淄博: [118.05, 36.78],
        鞍山: [122.85, 41.12],
        溧阳: [119.48, 31.43],
        库尔勒: [86.06, 41.68],
        安阳: [114.35, 36.1],
        开封: [114.35, 34.79],
        济南: [117, 36.65],
        德阳: [104.37, 31.13],
        温州: [120.65, 28.01],
        九江: [115.97, 29.71],
        邯郸: [114.47, 36.6],
        临安: [119.72, 30.23],
        兰州: [103.73, 36.03],
        沧州: [116.83, 38.33],
        临沂: [118.35, 35.05],
        南充: [106.110698, 30.837793],
        天津: [117.2, 39.13],
        富阳: [119.95, 30.07],
        泰安: [117.13, 36.18],
        诸暨: [120.23, 29.71],
        郑州: [113.65, 34.76],
        哈尔滨: [126.63, 45.75],
        聊城: [115.97, 36.45],
        芜湖: [118.38, 31.33],
        唐山: [118.02, 39.63],
        平顶山: [113.29, 33.75],
        邢台: [114.48, 37.05],
        德州: [116.29, 37.45],
        济宁: [116.59, 35.38],
        荆州: [112.239741, 30.335165],
        宜昌: [111.3, 30.7],
        义乌: [120.06, 29.32],
        丽水: [119.92, 28.45],
        洛阳: [112.44, 34.7],
        秦皇岛: [119.57, 39.95],
        株洲: [113.16, 27.83],
        石家庄: [114.48, 38.03],
        莱芜: [117.67, 36.19],
        常德: [111.69, 29.05],
        保定: [115.48, 38.85],
        湘潭: [112.91, 27.87],
        金华: [119.64, 29.12],
        岳阳: [113.09, 29.37],
        长沙: [113, 28.21],
        衢州: [118.88, 28.97],
        廊坊: [116.7, 39.53],
        菏泽: [115.480656, 35.23375],
        合肥: [117.27, 31.86],
        武汉: [114.31, 30.52],
        大庆: [125.03, 46.58]
      };

      var convertData = function(opts) {
        var res = [];
        var i = 0;
        var geoCoord;
        for (; i < opts.length; i++) {
          geoCoord = geoCoordMap[opts[i].name];

          if (geoCoord) {
            res.push({
              name: opts[i].name,
              value: geoCoord.concat(opts[i].value)
            });
          }
        }
        return res;
      };

      var option = {
        backgroundColor: '#404a59',
        tooltip: {
          trigger: 'item'
        },
        legend: {
          orient: 'vertical',
          y: 'bottom',
          x: 'right',
          data: ['pm2.5'],
          textStyle: {
            color: '#fff'
          }
        },
        geo: {
          map: 'china',
          label: {
            emphasis: {
              show: false
            }
          },
          roam: true,
          itemStyle: {
            normal: {
              areaColor: '#323c48',
              borderColor: '#111'
            },
            emphasis: {
              areaColor: '#2a333d'
            }
          }
        },
        series: [
          {
            name: 'pm2.5',
            type: 'scatter',
            coordinateSystem: 'geo',
            data: convertData(data),
            symbolSize: function(val) {
              return val[2] / 10;
            },
            label: {
              normal: {
                formatter: '{b}',
                position: 'right',
                show: false
              },
              emphasis: {
                show: true
              }
            },
            itemStyle: {
              normal: {
                color: '#ddb926'
              }
            }
          },
          {
            name: 'Top 5',
            type: 'effectScatter',
            coordinateSystem: 'geo',
            data: convertData(
              data
                .sort(function(a, b) {
                  return b.value - a.value;
                })
                .slice(0, 6)
            ),
            symbolSize: function(val) {
              return val[2] / 10;
            },
            showEffectOn: 'render',
            rippleEffect: {
              brushType: 'stroke'
            },
            hoverAnimation: true,
            label: {
              normal: {
                formatter: '{b}',
                position: 'right',
                show: true
              }
            },
            itemStyle: {
              normal: {
                color: '#f4e925',
                shadowBlur: 10,
                shadowColor: '#333'
              }
            },
            zlevel: 1
          }
        ]
      };

      window.onresize = myChart.resize;
      myChart.setOption(option);
    },
    exampleTwo: function() {
      // 上证指数示例
      var myChart = echarts.init(document.getElementById('echartsDemo2'));
      var data0;

      function splitData(rawData) {
        var categoryData = [];
        var values = [];
        var i = 0;

        for (; i < rawData.length; i++) {
          categoryData.push(rawData[i].splice(0, 1)[0]);
          values.push(rawData[i]);
        }
        return {
          categoryData: categoryData,
          values: values
        };
      }

      // 数据意义：开盘(open)，收盘(close)，最低(lowest)，最高(highest)
      data0 = splitData([
        ['2013/1/24', 2320.26, 2320.26, 2287.3, 2362.94],
        ['2013/1/25', 2300, 2291.3, 2288.26, 2308.38],
        ['2013/1/28', 2295.35, 2346.5, 2295.35, 2346.92],
        ['2013/1/29', 2347.22, 2358.98, 2337.35, 2363.8],
        ['2013/1/30', 2360.75, 2382.48, 2347.89, 2383.76],
        ['2013/1/31', 2383.43, 2385.42, 2371.23, 2391.82],
        ['2013/2/1', 2377.41, 2419.02, 2369.57, 2421.15],
        ['2013/2/4', 2425.92, 2428.15, 2417.58, 2440.38],
        ['2013/2/5', 2411, 2433.13, 2403.3, 2437.42],
        ['2013/2/6', 2432.68, 2434.48, 2427.7, 2441.73],
        ['2013/2/7', 2430.69, 2418.53, 2394.22, 2433.89],
        ['2013/2/8', 2416.62, 2432.4, 2414.4, 2443.03],
        ['2013/2/18', 2441.91, 2421.56, 2415.43, 2444.8],
        ['2013/2/19', 2420.26, 2382.91, 2373.53, 2427.07],
        ['2013/2/20', 2383.49, 2397.18, 2370.61, 2397.94],
        ['2013/2/21', 2378.82, 2325.95, 2309.17, 2378.82],
        ['2013/2/22', 2322.94, 2314.16, 2308.76, 2330.88],
        ['2013/2/25', 2320.62, 2325.82, 2315.01, 2338.78],
        ['2013/2/26', 2313.74, 2293.34, 2289.89, 2340.71],
        ['2013/2/27', 2297.77, 2313.22, 2292.03, 2324.63],
        ['2013/2/28', 2322.32, 2365.59, 2308.92, 2366.16],
        ['2013/3/1', 2364.54, 2359.51, 2330.86, 2369.65],
        ['2013/3/4', 2332.08, 2273.4, 2259.25, 2333.54],
        ['2013/3/5', 2274.81, 2326.31, 2270.1, 2328.14],
        ['2013/3/6', 2333.61, 2347.18, 2321.6, 2351.44],
        ['2013/3/7', 2340.44, 2324.29, 2304.27, 2352.02],
        ['2013/3/8', 2326.42, 2318.61, 2314.59, 2333.67],
        ['2013/3/11', 2314.68, 2310.59, 2296.58, 2320.96],
        ['2013/3/12', 2309.16, 2286.6, 2264.83, 2333.29],
        ['2013/3/13', 2282.17, 2263.97, 2253.25, 2286.33],
        ['2013/3/14', 2255.77, 2270.28, 2253.31, 2276.22],
        ['2013/3/15', 2269.31, 2278.4, 2250, 2312.08],
        ['2013/3/18', 2267.29, 2240.02, 2239.21, 2276.05],
        ['2013/3/19', 2244.26, 2257.43, 2232.02, 2261.31],
        ['2013/3/20', 2257.74, 2317.37, 2257.42, 2317.86],
        ['2013/3/21', 2318.21, 2324.24, 2311.6, 2330.81],
        ['2013/3/22', 2321.4, 2328.28, 2314.97, 2332],
        ['2013/3/25', 2334.74, 2326.72, 2319.91, 2344.89],
        ['2013/3/26', 2318.58, 2297.67, 2281.12, 2319.99],
        ['2013/3/27', 2299.38, 2301.26, 2289, 2323.48],
        ['2013/3/28', 2273.55, 2236.3, 2232.91, 2273.55],
        ['2013/3/29', 2238.49, 2236.62, 2228.81, 2246.87],
        ['2013/4/1', 2229.46, 2234.4, 2227.31, 2243.95],
        ['2013/4/2', 2234.9, 2227.74, 2220.44, 2253.42],
        ['2013/4/3', 2232.69, 2225.29, 2217.25, 2241.34],
        ['2013/4/8', 2196.24, 2211.59, 2180.67, 2212.59],
        ['2013/4/9', 2215.47, 2225.77, 2215.47, 2234.73],
        ['2013/4/10', 2224.93, 2226.13, 2212.56, 2233.04],
        ['2013/4/11', 2236.98, 2219.55, 2217.26, 2242.48],
        ['2013/4/12', 2218.09, 2206.78, 2204.44, 2226.26],
        ['2013/4/15', 2199.91, 2181.94, 2177.39, 2204.99],
        ['2013/4/16', 2169.63, 2194.85, 2165.78, 2196.43],
        ['2013/4/17', 2195.03, 2193.8, 2178.47, 2197.51],
        ['2013/4/18', 2181.82, 2197.6, 2175.44, 2206.03],
        ['2013/4/19', 2201.12, 2244.64, 2200.58, 2250.11],
        ['2013/4/22', 2236.4, 2242.17, 2232.26, 2245.12],
        ['2013/4/23', 2242.62, 2184.54, 2182.81, 2242.62],
        ['2013/4/24', 2187.35, 2218.32, 2184.11, 2226.12],
        ['2013/4/25', 2213.19, 2199.31, 2191.85, 2224.63],
        ['2013/4/26', 2203.89, 2177.91, 2173.86, 2210.58],
        ['2013/5/2', 2170.78, 2174.12, 2161.14, 2179.65],
        ['2013/5/3', 2179.05, 2205.5, 2179.05, 2222.81],
        ['2013/5/6', 2212.5, 2231.17, 2212.5, 2236.07],
        ['2013/5/7', 2227.86, 2235.57, 2219.44, 2240.26],
        ['2013/5/8', 2242.39, 2246.3, 2235.42, 2255.21],
        ['2013/5/9', 2246.96, 2232.97, 2221.38, 2247.86],
        ['2013/5/10', 2228.82, 2246.83, 2225.81, 2247.67],
        ['2013/5/13', 2247.68, 2241.92, 2231.36, 2250.85],
        ['2013/5/14', 2238.9, 2217.01, 2205.87, 2239.93],
        ['2013/5/15', 2217.09, 2224.8, 2213.58, 2225.19],
        ['2013/5/16', 2221.34, 2251.81, 2210.77, 2252.87],
        ['2013/5/17', 2249.81, 2282.87, 2248.41, 2288.09],
        ['2013/5/20', 2286.33, 2299.99, 2281.9, 2309.39],
        ['2013/5/21', 2297.11, 2305.11, 2290.12, 2305.3],
        ['2013/5/22', 2303.75, 2302.4, 2292.43, 2314.18],
        ['2013/5/23', 2293.81, 2275.67, 2274.1, 2304.95],
        ['2013/5/24', 2281.45, 2288.53, 2270.25, 2292.59],
        ['2013/5/27', 2286.66, 2293.08, 2283.94, 2301.7],
        ['2013/5/28', 2293.4, 2321.32, 2281.47, 2322.1],
        ['2013/5/29', 2323.54, 2324.02, 2321.17, 2334.33],
        ['2013/5/30', 2316.25, 2317.75, 2310.49, 2325.72],
        ['2013/5/31', 2320.74, 2300.59, 2299.37, 2325.53],
        ['2013/6/3', 2300.21, 2299.25, 2294.11, 2313.43],
        ['2013/6/4', 2297.1, 2272.42, 2264.76, 2297.1],
        ['2013/6/5', 2270.71, 2270.93, 2260.87, 2276.86],
        ['2013/6/6', 2264.43, 2242.11, 2240.07, 2266.69],
        ['2013/6/7', 2242.26, 2210.9, 2205.07, 2250.63],
        ['2013/6/13', 2190.1, 2148.35, 2126.22, 2190.1]
      ]);

      function calculateMA(dayCount) {
        var result = [];
        var i = 0;
        var len = data0.values.lengt;
        var sum = 0;
        var j = 0;

        for (; i < len; i++) {
          if (i < dayCount) {
            result.push('-');
            break;
          }

          for (; j < dayCount; j++) {
            sum += data0.values[i - j][1];
          }
          result.push(sum / dayCount);
        }
        return result;
      }

      window.onresize = myChart.resize;
      myChart.setOption({
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'line'
          }
        },
        legend: {
          data: ['日K', 'MA5', 'MA10', 'MA20', 'MA30']
        },
        grid: {
          left: '10%',
          right: '10%',
          bottom: '25%'
        },
        xAxis: {
          type: 'category',
          data: data0.categoryData,
          scale: true,
          boundaryGap: false,
          axisLine: {onZero: false},
          splitLine: {show: false},
          splitNumber: 20,
          min: 'dataMin',
          max: 'dataMax'
        },
        yAxis: {
          scale: true,
          splitArea: {
            show: true
          }
        },
        dataZoom: [
          {
            type: 'inside',
            start: 50,
            end: 100
          },
          {
            show: true,
            type: 'slider',
            y: '85%',
            start: 50,
            end: 100
          }
        ],
        series: [
          {
            name: '日K',
            type: 'candlestick',
            data: data0.values,
            markPoint: {
              label: {
                normal: {
                  formatter: function(param) {
                    return param != null ? Math.round(param.value) : '';
                  }
                }
              },
              data: [
                {
                  name: 'XX标点',
                  coord: ['2013/5/31', 2300],
                  value: 2300,
                  itemStyle: {
                    normal: {color: 'rgb(41,60,85)'}
                  }
                },
                {
                  name: 'highest value',
                  type: 'max',
                  valueDim: 'highest'
                },
                {
                  name: 'lowest value',
                  type: 'min',
                  valueDim: 'lowest'
                },
                {
                  name: 'average value on close',
                  type: 'average',
                  valueDim: 'close'
                }
              ],
              tooltip: {
                formatter: function(param) {
                  return param.name + '<br>' + (param.data.coord || '');
                }
              }
            },
            markLine: {
              symbol: ['none', 'none'],
              data: [
                [
                  {
                    name: 'from lowest to highest',
                    type: 'min',
                    valueDim: 'lowest',
                    symbol: 'circle',
                    symbolSize: 10,
                    label: {
                      normal: {show: false},
                      emphasis: {show: false}
                    }
                  },
                  {
                    type: 'max',
                    valueDim: 'highest',
                    symbol: 'circle',
                    symbolSize: 10,
                    label: {
                      normal: {show: false},
                      emphasis: {show: false}
                    }
                  }
                ],
                {
                  name: 'min line on close',
                  type: 'min',
                  valueDim: 'close'
                },
                {
                  name: 'max line on close',
                  type: 'max',
                  valueDim: 'close'
                }
              ]
            }
          },
          {
            name: 'MA5',
            type: 'line',
            data: calculateMA(5),
            smooth: true,
            lineStyle: {
              normal: {opacity: 0.5}
            }
          },
          {
            name: 'MA10',
            type: 'line',
            data: calculateMA(10),
            smooth: true,
            lineStyle: {
              normal: {opacity: 0.5}
            }
          },
          {
            name: 'MA20',
            type: 'line',
            data: calculateMA(20),
            smooth: true,
            lineStyle: {
              normal: {opacity: 0.5}
            }
          },
          {
            name: 'MA30',
            type: 'line',
            data: calculateMA(30),
            smooth: true,
            lineStyle: {
              normal: {opacity: 0.5}
            }
          }
        ]
      });
    },
    exampleThree: function() {
      // 热力图与百度地图扩展示例
      var myChart = echarts.init(document.getElementById('echartsDemo3'));

      $.getJSON('/public/data/examples/echarts/hangzhou-tracks.json', function(data) {
        var points = [].concat.apply(
          [],
          data.map(function(track) {
            return track.map(function(seg) {
              return seg.coord.concat([1]);
            });
          })
        );

        window.onresize = myChart.resize;
        myChart.setOption({
          animation: false,
          bmap: {
            center: [120.13066322374, 30.240018034923],
            zoom: 14,
            roam: true
          },
          visualMap: {
            show: false,
            top: 'top',
            min: 0,
            max: 5,
            seriesIndex: 0,
            calculable: true,
            inRange: {
              color: ['blue', 'blue', 'green', 'yellow', 'red']
            }
          },
          series: [
            {
              type: 'heatmap',
              coordinateSystem: 'bmap',
              data: points,
              pointSize: 5,
              blurSize: 6
            }
          ]
        });
      });
    },
    exampleFour: function() {
      // AQI - 雷达图
      var myChart = echarts.init(document.getElementById('echartsDemo4'));

      var dataBJ = [
        [55, 9, 56, 0.46, 18, 6, 1],
        [25, 11, 21, 0.65, 34, 9, 2],
        [56, 7, 63, 0.3, 14, 5, 3],
        [33, 7, 29, 0.33, 16, 6, 4],
        [42, 24, 44, 0.76, 40, 16, 5],
        [82, 58, 90, 1.77, 68, 33, 6],
        [74, 49, 77, 1.46, 48, 27, 7],
        [78, 55, 80, 1.29, 59, 29, 8],
        [267, 216, 280, 4.8, 108, 64, 9],
        [185, 127, 216, 2.52, 61, 27, 10],
        [39, 19, 38, 0.57, 31, 15, 11],
        [41, 11, 40, 0.43, 21, 7, 12],
        [64, 38, 74, 1.04, 46, 22, 13],
        [108, 79, 120, 1.7, 75, 41, 14],
        [108, 63, 116, 1.48, 44, 26, 15],
        [33, 6, 29, 0.34, 13, 5, 16],
        [94, 66, 110, 1.54, 62, 31, 17],
        [186, 142, 192, 3.88, 93, 79, 18],
        [57, 31, 54, 0.96, 32, 14, 19],
        [22, 8, 17, 0.48, 23, 10, 20],
        [39, 15, 36, 0.61, 29, 13, 21],
        [94, 69, 114, 2.08, 73, 39, 22],
        [99, 73, 110, 2.43, 76, 48, 23],
        [31, 12, 30, 0.5, 32, 16, 24],
        [42, 27, 43, 1, 53, 22, 25],
        [154, 117, 157, 3.05, 92, 58, 26],
        [234, 185, 230, 4.09, 123, 69, 27],
        [160, 120, 186, 2.77, 91, 50, 28],
        [134, 96, 165, 2.76, 83, 41, 29],
        [52, 24, 60, 1.03, 50, 21, 30],
        [46, 5, 49, 0.28, 10, 6, 31]
      ];

      var dataGZ = [
        [26, 37, 27, 1.163, 27, 13, 1],
        [85, 62, 71, 1.195, 60, 8, 2],
        [78, 38, 74, 1.363, 37, 7, 3],
        [21, 21, 36, 0.634, 40, 9, 4],
        [41, 42, 46, 0.915, 81, 13, 5],
        [56, 52, 69, 1.067, 92, 16, 6],
        [64, 30, 28, 0.924, 51, 2, 7],
        [55, 48, 74, 1.236, 75, 26, 8],
        [76, 85, 113, 1.237, 114, 27, 9],
        [91, 81, 104, 1.041, 56, 40, 10],
        [84, 39, 60, 0.964, 25, 11, 11],
        [64, 51, 101, 0.862, 58, 23, 12],
        [70, 69, 120, 1.198, 65, 36, 13],
        [77, 105, 178, 2.549, 64, 16, 14],
        [109, 68, 87, 0.996, 74, 29, 15],
        [73, 68, 97, 0.905, 51, 34, 16],
        [54, 27, 47, 0.592, 53, 12, 17],
        [51, 61, 97, 0.811, 65, 19, 18],
        [91, 71, 121, 1.374, 43, 18, 19],
        [73, 102, 182, 2.787, 44, 19, 20],
        [73, 50, 76, 0.717, 31, 20, 21],
        [84, 94, 140, 2.238, 68, 18, 22],
        [93, 77, 104, 1.165, 53, 7, 23],
        [99, 130, 227, 3.97, 55, 15, 24],
        [146, 84, 139, 1.094, 40, 17, 25],
        [113, 108, 137, 1.481, 48, 15, 26],
        [81, 48, 62, 1.619, 26, 3, 27],
        [56, 48, 68, 1.336, 37, 9, 28],
        [82, 92, 174, 3.29, 0, 13, 29],
        [106, 116, 188, 3.628, 101, 16, 30],
        [118, 50, 0, 1.383, 76, 11, 31]
      ];

      var dataSH = [
        [91, 45, 125, 0.82, 34, 23, 1],
        [65, 27, 78, 0.86, 45, 29, 2],
        [83, 60, 84, 1.09, 73, 27, 3],
        [109, 81, 121, 1.28, 68, 51, 4],
        [106, 77, 114, 1.07, 55, 51, 5],
        [109, 81, 121, 1.28, 68, 51, 6],
        [106, 77, 114, 1.07, 55, 51, 7],
        [89, 65, 78, 0.86, 51, 26, 8],
        [53, 33, 47, 0.64, 50, 17, 9],
        [80, 55, 80, 1.01, 75, 24, 10],
        [117, 81, 124, 1.03, 45, 24, 11],
        [99, 71, 142, 1.1, 62, 42, 12],
        [95, 69, 130, 1.28, 74, 50, 13],
        [116, 87, 131, 1.47, 84, 40, 14],
        [108, 80, 121, 1.3, 85, 37, 15],
        [134, 83, 167, 1.16, 57, 43, 16],
        [79, 43, 107, 1.05, 59, 37, 17],
        [71, 46, 89, 0.86, 64, 25, 18],
        [97, 71, 113, 1.17, 88, 31, 19],
        [84, 57, 91, 0.85, 55, 31, 20],
        [87, 63, 101, 0.9, 56, 41, 21],
        [104, 77, 119, 1.09, 73, 48, 22],
        [87, 62, 100, 1, 72, 28, 23],
        [168, 128, 172, 1.49, 97, 56, 24],
        [65, 45, 51, 0.74, 39, 17, 25],
        [39, 24, 38, 0.61, 47, 17, 26],
        [39, 24, 39, 0.59, 50, 19, 27],
        [93, 68, 96, 1.05, 79, 29, 28],
        [188, 143, 197, 1.66, 99, 51, 29],
        [174, 131, 174, 1.55, 108, 50, 30],
        [187, 143, 201, 1.39, 89, 53, 31]
      ];

      var lineStyle = {
        normal: {
          width: 1,
          opacity: 0.5
        }
      };

      var option = {
        backgroundColor: '#161627',
        legend: {
          bottom: 5,
          data: ['北京', '上海', '广州'],
          itemGap: 20,
          textStyle: {
            color: '#fff',
            fontSize: 14
          },
          selectedMode: 'single'
        },
        // visualMap: {
        //     show: true,
        //     min: 0,
        //     max: 20,
        //     dimension: 6,
        //     inRange: {
        //         colorLightness: [0.5, 0.8]
        //     }
        // },
        radar: {
          indicator: [
            {name: 'AQI', max: 300},
            {name: 'PM2.5', max: 250},
            {name: 'PM10', max: 300},
            {name: 'CO', max: 5},
            {name: 'NO2', max: 200},
            {name: 'SO2', max: 100}
          ],
          shape: 'circle',
          splitNumber: 5,
          name: {
            textStyle: {
              color: 'rgb(238, 197, 102)'
            }
          },
          splitLine: {
            lineStyle: {
              color: [
                'rgba(238, 197, 102, 0.1)',
                'rgba(238, 197, 102, 0.2)',
                'rgba(238, 197, 102, 0.4)',
                'rgba(238, 197, 102, 0.6)',
                'rgba(238, 197, 102, 0.8)',
                'rgba(238, 197, 102, 1)'
              ].reverse()
            }
          },
          splitArea: {
            show: false
          },
          axisLine: {
            lineStyle: {
              color: 'rgba(238, 197, 102, 0.5)'
            }
          }
        },
        series: [
          {
            name: '北京',
            type: 'radar',
            lineStyle: lineStyle,
            data: dataBJ,
            symbol: 'none',
            itemStyle: {
              normal: {
                color: '#F9713C'
              }
            },
            areaStyle: {
              normal: {
                opacity: 0.1
              }
            }
          },
          {
            name: '上海',
            type: 'radar',
            lineStyle: lineStyle,
            data: dataSH,
            symbol: 'none',
            itemStyle: {
              normal: {
                color: '#B3E4A1'
              }
            },
            areaStyle: {
              normal: {
                opacity: 0.05
              }
            }
          },
          {
            name: '广州',
            type: 'radar',
            lineStyle: lineStyle,
            data: dataGZ,
            symbol: 'none',
            itemStyle: {
              normal: {
                color: 'rgb(238, 197, 102)'
              }
            },
            areaStyle: {
              normal: {
                opacity: 0.05
              }
            }
          }
        ]
      };

      window.onresize = myChart.resize;
      myChart.setOption(option);
    }
  };

  $(function() {
    $(function() {
      // 初始化图表示例
      $.each(echartsPage, function(i, n) {
        n();
      });
    });
  });
})();
