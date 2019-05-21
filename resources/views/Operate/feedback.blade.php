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

    <title>意见反馈统计</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('feedback')}}" method="post">
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
                            <label>反馈内容:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="remark" value="{{$remark}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>反馈类型:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="type">
                                    <option value="">全部</option>
                                    @foreach($typeList as $k => $v)
                                        <option value="{{$k}}" @if("$k" === $type) selected @endif>{{$v}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>反馈时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="date1" value="{{$date1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="date2" value="{{$date2}}"/>
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
                            <th class="sth">编号</th>
                            <th class="sth">客户名称</th>
                            <th class="sth">手机号</th>
                            <th class="sth">反馈时间</th>
                            <th class="sth">反馈类型</th>
                            <th class="sth">反馈内容</th>
                            <th class="sth">回复状态</th>
                            <th class="sth">操作</th>
                        </tr>

                        @foreach($data as $k => $v)
                            <tr class="str">
                                <td class="std">{{$k+1}}</td>
                                <td class="std">{{$v->name}}</td>
                                <td class="std">{{$v->phone}}</td>
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std">{{$typeList[$v->type]}}</td>
                                <td class="std">{{$v->remark}}</td>
                                <td class="std">{{$answerStatusList[$v->status]}}</td>
                                <td class="std"><a href="javascript:;" onclick="getFeedback({{$v->id}})">回复</a></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['name' => $name, 'phone' => $phone, 'type' => $type, 'remark' => $remark,
                    'date1' => $date1, 'date2' => $date2])->links() }}
                </div>
            </div>
            <div id="optable" class="optable" onclick="closeDetails()"></div>
            <div id="mutable" class="atable">
                <div style="width: 90%;height: 30px;">意见反馈</div>
                <div class="utable" id="utable16"></div>
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
