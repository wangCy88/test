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

    <title>任务调度</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('taskControl')}}" method="post">
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
                            <label>流程名称:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="process">
                                    <option value="">全部</option>
                                    <option value="1" @if($process === '1') selected="selected" @endif>订单审核</option>
                                    <option value="2" @if($process === '2') selected="selected" @endif>客户资料审核</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>申请单号:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="id" value="{{$id}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>流程状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="status">
                                    <option value="">全部</option>
                                    <option value="0" @if($status === '0') selected="selected" @endif>待审核</option>
                                    <option value="1" @if($status === '1') selected="selected" @endif>审核中</option>
                                    <option value="2" @if($status === '2') selected="selected" @endif>审核通过</option>
                                    <option value="3" @if($status === '3') selected="selected" @endif>拒绝</option>
                                    <option value="4" @if($status === '4') selected="selected" @endif>终止</option>
                                    <option value="5" @if($status === '5') selected="selected" @endif>关闭</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>复贷状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="loan">
                                    <option value="">全部</option>
                                    <option value="0" @if($loan === '0') selected="selected" @endif>新增</option>
                                    <option value="1" @if($loan === '1') selected="selected" @endif>复贷</option>
                                </select>
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
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>发起时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="date1" value="{{$date1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="date2" value="{{$date2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-button" style="margin-left: 20px;">
                        <input class="button" type="submit" value="搜索一下" />
                        <input class="button" type="button" value="分配人员" onclick="allotMember()"/>
                    </div>
                </div>
            </form>
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth"><input type="checkbox" name="chb[]" id="all" onclick="setChecked(this)"/></th>
                            <th class="sth">单号</th>
                            <th class="sth">流程名称</th>
                            <th class="sth">申请渠道</th>
                            <th class="sth">申请人</th>
                            <th class="sth">发起时间</th>
                            <th class="sth">流程状态</th>
                            <th class="sth">复贷状态</th>
                            <th class="sth">付款状态</th>
                            <th class="sth">操作</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std"><input type="checkbox" name="chb[]"  onclick="setChecked(this)" value="{{$v->id}}"/></td>
                                <td class="std">{{$v->id}}</td>
                                <td class="std">{{$processList[$v->process]}}</td>
                                <td class="std">@if(!empty($v->merchantChannelConfig)){{$v->merchantChannelConfig->name}}@endif</td>
                                <td class="std">{{$v->merchantUsers->name}}</td>
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std">{{$processStatusList[$v->status]}}</td>
                                <td class="std">{{$loanStatusList[$v->loan]}}</td>
                                <td class="std">{{$v->auth}}</td>
                                <td class="std"><a href="javascript:;" onclick="viewProcess({{$v->id}},{{$v->userid}})">查看流程</a></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['id' => $id, 'process' => $process, 'status' => $status, 'loan' => $loan,
                    'date1' => $date1, 'date2' => $date2, 'name' => $name, 'channel' => $channel])->links() }}
                </div>
            </div>
            <div class="table">
                <div class="grid" id="viewProcess" style="display: none;"></div>
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
            <div id="atable" class="atable">
                <div style="width: 90%;height: 30px;">人员分配</div>
                <div class="utable" id="utable2"></div>
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
