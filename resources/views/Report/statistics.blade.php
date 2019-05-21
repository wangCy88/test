<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=emulateIE7" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/WdatePicker.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/skin_/table.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/skin_/index.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/jquery.grid.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/mystyle.css')}}" />


    <title>首页统计</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">成功放款总额度</th>
                            <th class="sth">盈利额</th>
                            <th class="sth">逾期总额</th>
                            <th class="sth">待还款金额</th>
                        </tr>
                        <tr class="str">
                            <td class="std">{{$total_withdraw_amount}}</td>
                            <td class="std">{{$total_profit_amount}}</td>
                            <td class="std">{{$total_overdue_amount}}</td>
                            <td class="std">{{$total_repay_amount}}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div style="margin-top: 2%;">
                <div id="sub1" style="width:100%; height:450px;"></div>
            </div>
        </div>
    </div>
</div>
</body>

<script type="text/javascript" src="{{asset('js/global.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.select.js')}}"></script>
<script type="text/javascript" src="{{asset('js/core.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.pagination.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.grid.js')}}"></script>
<script type="text/javascript" src="{{asset('js/WdatePicker.js')}}"></script>
<script type="text/javascript" src="{{asset('js/myjs.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.js')}}"></script>
<script type="text/javascript" src="{{asset('js/echarts.js')}}"></script>
<script>
    window.onload = getWeekRegister();

    function getWeekRegister(){
        $.ajax({
            url: "/getWeekRegister",
            data: {},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if(data.code === 0) {
                    var xData = data.data.day;
                    var yData = data.data.reg;
                    var subChart = echarts.init(document.getElementById('sub1'));
                    var colors = ['#33FF66', '#2BD5D5'];
                    option = {
                        color: colors,
                        title: {
                            text: '周新增用户'
                        },
                        legend: {
                            data:['注册数'],
                            textStyle:{
                                color:"#33FF66"
                            }
                        },
                        tooltip: {
                            trigger: 'axis'
                        },
                        grid: {
                            left: '3%',
                            right: '4%',
                            bottom: '3%',
                            containLabel: true
                        },
                        xAxis: {
                            type: 'category',
                            boundaryGap: false,
                            name: '日期',
                            data: xData,
                            axisLabel: {
                                show: true,
                                textStyle: {
                                    color: '#000000'
                                }
                            },
                            axisLine:{
                                lineStyle:{
                                    color:'#000000'
                                }
                            }
                        },
                        yAxis: {
                            splitLine:{
                                show: false
                            },
                            name: '人数',
                            type: 'value',
                            axisLabel: {
                                show: true,
                                textStyle: {
                                    color: '#000000'
                                }
                            },
                            axisLine:{
                                lineStyle:{
                                    color:'#000000'
                                }
                            }
                        },
                        series: [
                            {
                                name: '注册数',
                                type: 'line',
                                stack: '总量',
                                data: yData,
                                markPoint: {
                                    data: [
                                        {type: 'max', name: '最高注册数'}
                                    ]
                                },
                                markLine: {
                                    data: [
                                        {type: 'max', name: '最高值'},
                                        {type: 'average', name: '平均值'}
                                    ]
                                }
                            }
                        ]
                    };
                    subChart.setOption(option);
                }
            },
            error : function () {

            }
        });
    }
</script>
</html>
