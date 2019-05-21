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

    <title>客户查询</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('customSelect')}}" method="post">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>客户名称:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="name" value="{{$name}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>手机号:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="phone" value="{{$phone}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>身份证:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="id_number" value="{{$id_number}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>资料状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="data_status">
                                    <option value="">全部</option>
                                    <option value="0" @if($data_status === '0') selected="selected" @endif>注册完成</option>
                                    <option value="1" @if($data_status === '1') selected="selected" @endif>基础资料完成</option>
                                    <option value="2" @if($data_status === '2') selected="selected" @endif>联系人完成</option>
                                    <option value="3" @if($data_status === '3') selected="selected" @endif>OCR完成</option>
                                    <option value="4" @if($data_status === '4') selected="selected" @endif>运营商完成</option>
                                    <option value="5" @if($data_status === '5') selected="selected" @endif>银行卡绑定完成</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label style="width: 100px;">银行卡认证状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="bank_status">
                                    <option value="">全部</option>
                                    <option value="0" @if($bank_status === '0') selected="selected" @endif>未认证</option>
                                    <option value="1" @if($bank_status === '1') selected="selected" @endif>已认证</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>账户状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="account_status">
                                    <option value="">全部</option>
                                    <option value="0" @if($account_status === '0') selected="selected" @endif>无</option>
                                    <option value="1" @if($account_status === '1') selected="selected" @endif>审核中</option>
                                    <option value="2" @if($account_status === '2') selected="selected" @endif>审核通过</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>注册时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="date1" value="{{$date1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="date2" value="{{$date2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>渠道:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="channel">
                                    <option value="">请选择</option>
                                    @foreach($channels as $v)
                                        <option value="{{$v->id}}" @if($channel == $v->id) selected="selected" @endif>{{$v->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-button" style="margin-left: 20px;">
                        <input class="button" type="submit" value="搜索一下" />
                    </div>
                </div>
            </form>
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">客户名称</th>
                            <th class="sth">身份证号</th>
                            <th class="sth">手机号</th>
                            <th class="sth">注册时间</th>
                            <th class="sth">资料状态</th>
                            <th class="sth">银行卡认证状态</th>
                            <th class="sth">账户状态</th>
                            <th class="sth">渠道来源</th>
                            <th class="sth">操作</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->name}}@endif</td>
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->id_number}}@endif</td>
                                <td class="std">{{$v->phone}}</td>
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std">{{$dataStatusList[$v->data_status]}}</td>
                                <td class="std">{{$bankStatusList[$v->bank_status]}}</td>
                                <td class="std">{{$accountStatusList[$v->account_status]}}</td>
                                <td class="std">@if(!empty($v->merchantChannelConfig)){{$v->merchantChannelConfig->name}}@endif</td>
                                <td class="std">@if(!empty($v->merchantUsers))<a href="javascript:;" onclick="getDetails({{$v->merchantUsers->id}})">查看</a>@endif</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['id_number' => $id_number, 'phone' => $phone, 'data_status' => $data_status, 'bank_status' => $bank_status,
                    'account_status' => $account_status, 'date1' => $date1, 'date2' => $date2, 'name' => $name,
                    'channel' => $channel])->links() }}
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
