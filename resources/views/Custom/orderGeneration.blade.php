<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=emulateIE7" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="referrer" content="no-referrer" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/WdatePicker.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/skin_/table.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/skin_/index.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/jquery.grid.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/mystyle.css')}}" />

    <title>订单生成管理</title>
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
                            <th class="sth">姓名</th>
                            <th class="sth">手机号</th>
                            <th class="sth">注册时间</th>
                            <th class="sth">授信时间</th>
                            <th class="sth">用户等级</th>
                            <th class="sth">当前授信额度</th>
                            <th class="sth">可用额度</th>
                            <th class="sth">累计借款金额</th>
                            <th class="sth">成功提现次数</th>
                            <th class="sth">停滞天数</th>
                            <th class="sth">账户状态</th>
                            <th class="sth">异常次数</th>
                            <th class="sth">渠道来源</th>
                            <th class="sth">操作</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->name}}@endif</td>
                                <td class="std">{{$v->phone}}</td>
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std">{{$v->credit_at}}</td>
                                <td class="std">{{$v->account_level}}</td>
                                <td class="std">{{$v->credit_limit}}</td>
                                <td class="std">{{$v->usable_limit}}</td>
                                <td class="std">{{$v->total_loan_amount}}</td>
                                <td class="std">{{$v->withdraw_success}}</td>
                                <td class="std">{{$v->stagnant_day}}</td>
                                <td class="std">{{$accountStatusList[$v->account_status]}}</td>
                                <td class="std">{{$v->abnormal_times}}</td>
                                <td class="std">@if(!empty($v->merchantChannelConfig)){{$v->merchantChannelConfig->name}}@endif</td>
                                <td class="std">@if(!empty($v->merchantUsers))<a href="javascript:;" onclick="giveOrder([{{$v->phone}},{{$v->mchid}},{{$v->credit_limit}},0])">给予借款</a>@endif</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

            </div>
            <div id="optable" class="optable" onclick="closeDetails()"></div>
            <div id="otable" class="otable">
                <div style="width: 90%;height: 30px;">详情</div>
                <div style="width: 100%;height: 30px;">
                    <ul>
                        <li><a href="javascript:void(0);" onclick="changeWindow(1)" id="window1" style="background: #ffff00;">订单与资料信息</a></li>
                        <li><a href="javascript:void(0);" onclick="changeWindow(2)" id="window2">联系人与影像资料</a></li>
                        <li><a href="javascript:void(0);" onclick="changeWindow(3)" id="window3">通讯录</a></li>
                        <li><a href="javascript:void(0);" onclick="changeWindow(4)" id="window4">运营商报告</a></li>
                        <li><a href="javascript:void(0);" onclick="changeWindow(5)" id="window5">其他数据结果查询</a></li>
                    </ul>
                </div>
                <div class="utable" id="xtable1"></div>
                <div class="utable display-none" id="xtable2"></div>
                <div class="utable display-none" id="xtable3"></div>
                <div class="utable display-none" id="xtable4"></div>
                <div class="utable display-none" id="xtable5"></div>
            </div>
            <div id="uptable" class="atable">
                <div style="width: 90%;height: 30px;">调整额度</div>
                <div class="utable" id="utable15"></div>
            </div>
        </div>
    </div>
</div>
</body>
<script type="text/javascript" src="{{asset('js/jquery.js')}}"></script>
<script type="text/javascript" src="{{asset('js/global.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.select.js')}}"></script>
<script type="text/javascript" src="{{asset('js/core.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.pagination.js')}}"></script>
<script type="text/javascript" src="{{asset('js/jquery.grid.js')}}"></script>
<script type="text/javascript" src="{{asset('js/WdatePicker.js')}}"></script>
<script type="text/javascript" src="{{asset('js/myjs.js')}}"></script>
</html>
