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

    <title>异常账号管理</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('abnormalCustom')}}" method="post">
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
                            <label>注册时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="date1" value="{{$date1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="date2" value="{{$date2}}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>授信时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="credit_at1" value="{{$credit_at1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="credit_at2" value="{{$credit_at2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>用户等级:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="account_level1" value="{{$account_level1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="account_level2" value="{{$account_level2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>授信额度:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="credit_limit1" value="{{$credit_limit1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="credit_limit2" value="{{$credit_limit2}}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>可用额度:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="usable_limit1" value="{{$usable_limit1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="usable_limit2" value="{{$usable_limit2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>累计借款:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="total_loan_amount1" value="{{$total_loan_amount1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="total_loan_amount2" value="{{$total_loan_amount2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>成功提现:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="withdraw_success1" value="{{$withdraw_success1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="withdraw_success2" value="{{$withdraw_success2}}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>停滞天数:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="stagnant_day1" value="{{$stagnant_day1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="stagnant_day2" value="{{$stagnant_day2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>异常次数:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="abnormal_times1" value="{{$abnormal_times1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="abnormal_times2" value="{{$abnormal_times2}}"/>
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
                                <td class="std">@if(!empty($v->merchantUsers))<a href="javascript:;" onclick="getDetails({{$v->merchantUsers->id}})">查看</a>@endif</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['id_number' => $id_number, 'phone' => $phone, 'date1' => $date1, 'date2' => $date2,
                    'name' => $name, 'credit_at1' => $credit_at1, 'credit_at2' => $credit_at2, 'account_level1' => $account_level1,
                    'account_level2' => $account_level2, 'credit_limit1' => $credit_limit1, 'credit_limit2' => $credit_limit2,
                    'usable_limit1' => $usable_limit1, 'usable_limit2' => $usable_limit2, 'total_loan_amount1' => $total_loan_amount1,
                    'total_loan_amount2' => $total_loan_amount2, 'withdraw_success1' => $withdraw_success1, 'withdraw_success2' => $withdraw_success2,
                    'abnormal_times1' => $abnormal_times1, 'abnormal_times2' => $abnormal_times2, 'channel' => $channel])->links() }}
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
