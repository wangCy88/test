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

    <title>渠道监控</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('channelMonitor')}}" method="post">
                <input type="hidden" name="_token" value="{{csrf_token()}}">
                <div class="search-box ue-clear">
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
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>日期:</label>
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
                            <th class="sth">渠道</th>
                            <th class="sth">日期</th>
                            <th class="sth">注册量</th>
                            <th class="sth">转化后注册量</th>
                            <th class="sth">完善资料量</th>
                            <th class="sth">转化率</th>
                            <th class="sth">授信通过量</th>
                            <th class="sth">授信通过金额</th>
                            <th class="sth">授信通过率</th>
                            <th class="sth">订单量</th>
                            <th class="sth">订单借款金额</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">@if(!empty($v->merchantChannelConfig)){{$v->merchantChannelConfig->name}}@endif</td>
                                <td class="std">{{$v->curr_date}}</td>
                                <td class="std">{{$v->reg}}</td>
                                <td class="std">{{$v->reg_after}}</td>
                                <td class="std">{{$v->complete}}</td>
                                <td class="std">{{$v->complete_per}}%</td>
                                <td class="std">{{$v->pass}}</td>
                                <td class="std">{{$v->pass_amount}}</td>
                                <td class="std">{{$v->pass_per}}%</td>
                                <td class="std">{{$v->order}}</td>
                                <td class="std">{{$v->order_amount}}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['channel' => $channel, 'date1' => $date1, 'date2' => $date2])->links() }}
                </div>
            </div>
            <div class="table" style="width: 50%;">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">注册量</th>
                            <th class="sth">转化后注册量</th>
                            <th class="sth">完善资料量</th>
                            <th class="sth">授信通过量</th>
                            <th class="sth">授信通过金额</th>
                            <th class="sth">授信通过率</th>
                            <th class="sth">订单量</th>
                            <th class="sth">订单借款金额</th>
                        </tr>
                        <tr class="str">
                            <td class="std">{{$total->reg}}</td>
                            <td class="std">{{$total->reg_after}}</td>
                            <td class="std">{{$total->complete}}</td>
                            <td class="std">{{$total->pass}}</td>
                            <td class="std">{{$total->pass_amount}}</td>
                            <td class="std">{{$total->pass_per}}%</td>
                            <td class="std">{{$total->order}}</td>
                            <td class="std">{{$total->order_amount}}</td>
                        </tr>
                    </table>
                </div>
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
