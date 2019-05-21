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

    <title>充值记录</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <div class="search-box ue-clear">
                <div class="search-area">
                    <div class="kv-item ue-clear">
                        <div class="kv-item-content">
                            充值金额:{{$sum}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">充值时间</th>
                            <th class="sth">流水号</th>
                            <th class="sth">充值账号</th>
                            <th class="sth">充值金额</th>
                            <th class="sth">充值状态</th>
                            <th class="sth">支付方式</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std">{{$v->id}}</td>
                                <td class="std">{{$v->account}}</td>
                                <td class="std">{{$v->amount}}</td>
                                <td class="std">{{$rechargeStatusList[$v->status]}}</td>
                                <td class="std">{{$rechargeModeList[$v->mode]}}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="grid">
                {{ $data->links() }}
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
