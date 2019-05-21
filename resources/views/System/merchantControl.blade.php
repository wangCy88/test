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

    <title>商户管理</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('addMerchant')}}" method="post">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>商户名称:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="name" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>商户备注:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="remark" value=""/>
                            </div>
                        </div>
                    </div>
                    <div class="search-button" style="margin-left: 20px;">
                        <input class="button" type="submit" value="新增商户" />
                    </div>
                </div>
            </form>
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">编号</th>
                            <th class="sth">商户名称</th>
                            <th class="sth">商户备注</th>
                            <th class="sth">创建时间</th>
                            <th class="sth">状态</th>
                            <th class="sth">操作</th>
                        </tr>
                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">{{$v->id}}</td>
                                <td class="std">{{$v->name}}</td>
                                <td class="std">{{$v->remark}}</td>
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std">{{$merchantStatusList[$v->status]}}</td>
                                <td class="std"><a href="javascript:;" onclick="modifyMerchant({{$v->id}})">修改</a>&nbsp;&nbsp;
                                    @if($v->status === 0)<a href="javascript:;" onclick="closeMerchant({{$v->id}})">关闭</a>
                                    @else<a href="javascript:;" onclick="openMerchant({{$v->id}})">开启</a>
                                    @endif</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->links() }}
                </div>
            </div>
            <div id="optable" class="optable" onclick="closeDetails()"></div>
            <div id="atable" class="atable">
                <div style="width: 90%;height: 30px;">修改商户</div>
                <div class="utable" id="utable8"></div>
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
