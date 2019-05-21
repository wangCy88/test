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

    <title>渠道配置</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('channelConfig')}}" method="post">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>渠道名称:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="name" value="{{$name}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>渠道专员:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="member" value="{{$member}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>渠道来源:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="from" value="{{$from}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-button" style="margin-left: 20px;">
                        <input class="button" type="submit" value="搜索一下" />
                    </div>
                    <div class="search-button" style="margin-left: 20px;">
                        <input class="button" type="button" value="新增渠道" onclick="addChannel()"/>
                    </div>
                </div>
            </form>
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">邀请码</th>
                            <th class="sth">渠道名称</th>
                            <th class="sth">渠道专员</th>
                            <th class="sth">渠道来源</th>
                            <th class="sth">注册单价</th>
                            <th class="sth">注册转化率</th>
                            <th class="sth">完善资料单价</th>
                            <th class="sth">完善资料转化率</th>
                            <th class="sth">授信通过单价</th>
                            <th class="sth">授信通过转化率</th>
                            <th class="sth">订单单价</th>
                            <th class="sth">订单转化率</th>
                            <th class="sth">借款转化率</th>
                            <th class="sth">创建时间</th>
                            <th class="sth">操作</th>
                        </tr>
                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">{{$v->code}}</td>
                                <td class="std">{{$v->name}}</td>
                                <td class="std">{{$v->member}}</td>
                                <td class="std">{{$v->from}}</td>
                                <td class="std">{{$v->reg_price}}</td>
                                <td class="std">{{$v->reg_rate}}%</td>
                                <td class="std">{{$v->data_price}}</td>
                                <td class="std">{{$v->data_rate}}%</td>
                                <td class="std">{{$v->credit_price}}</td>
                                <td class="std">{{$v->credit_rate}}%</td>
                                <td class="std">{{$v->order_price}}</td>
                                <td class="std">{{$v->order_rate}}%</td>
                                <td class="std">{{$v->loan_rate}}%</td>
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std"><a href="javascript:;" onclick="modifyChannel({{$v->id}})">修改渠道</a>&nbsp;&nbsp;
                                    <a href="javascript:;" onclick="modifyPassword({{$v->id}})">修改密码</a>&nbsp;&nbsp;
                                    <a href="javascript:;" onclick="deleteChannel({{$v->id}})">删除渠道</a>&nbsp;&nbsp;</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['name' => $name, 'member' => $member, 'from' => $from])->links() }}
                </div>
            </div>
            <div id="optable" class="optable" onclick="closeDetails()"></div>
            <div id="rtable" class="rtable">
                <div style="width: 90%;height: 30px;">配置渠道</div>
                <div class="utable" id="utable5"></div>
            </div>
            <div id="atable" class="atable">
                <div style="width: 90%;height: 30px;">修改密码</div>
                <div class="utable" id="utable6"></div>
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
