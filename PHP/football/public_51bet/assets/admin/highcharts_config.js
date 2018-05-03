$(function(){
    Highcharts.setOptions({
        lang :{
            resetZoom : '重置大小',
            resetZoomTitle : '重置大小',
            //numericSymbols : [' 千',' 百万',' 千万',' 亿',' 千亿',' 兆']
            numericSymbols : null
        }
    });
});
var ChartsOption = {
    chart: {
        zoomType: 'x',
        spacingRight: 20
    },
    credits : {enabled : false},
    title: {
        text: ''
    },
    subtitle: {
    },
    xAxis: {
        categories:[],
        //type: 'date',
        //maxZoom: 14 * 24 * 3600000, // fourteen days
        title: {
            text: null
        }
    },
    yAxis: {
        title: {
            text: ''
        },
        labels: {
            formatter : function(){
                return formatMoney(this.value);
            },
            //format: '{value} 枚',
            style: {color: '#4572A7'}
        }
    },
    tooltip: {
        shared: true
    },
    legend: {
        enabled: false
    },
    plotOptions: {
        area: {
            turboThreshold : 0, //不限制点数
            fillColor: {
                linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                stops: [
                    [0, Highcharts.getOptions().colors[0]],
                    [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                ]
            },
            lineWidth: 1,
            marker: {
                enabled: false
            },
            shadow: false,
            states: {
                hover: {
                    lineWidth: 1
                }
            },
            threshold: null
        }

    },

    series: [{
        type: 'area',
        //pointInterval: 24 * 3600 * 1000,
        //pointStart: Date.UTC(2006, 0, 01),
        data: []
    }]
}

var formatMoney = function(value){
    var length = Math.abs(value).toString().length;
    var str = '';
    if(length <= 5){return value;}
    if(length > 5 && length < 9){
        str = parseInt(parseInt(value) / 10000) + ' 万';
    }else if(length >= 9){
        str = parseInt(parseInt(value) / 100000000) + ' 亿';
    }
    return str;
}