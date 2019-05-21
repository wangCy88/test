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

    <title>放款管理</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('withdrawControl')}}" method="post">
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
                            <label>身份证:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="id_number" value="{{$id_number}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>银行卡:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="bank_card" value="{{$bank_card}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>放款状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="order_status">
                                    <option value="">全部</option>
                                    <option value="0" @if($order_status === '0') selected="selected" @endif>待审核</option>
                                    <option value="1" @if($order_status === '1') selected="selected" @endif>等待放款</option>
                                    <option value="2" @if($order_status === '2') selected="selected" @endif>放款成功</option>
                                    <option value="3" @if($order_status === '3') selected="selected" @endif>放款失败</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>申请日期:</label>
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
                            <label>借款金额:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="withdraw_amount1" value="{{$withdraw_amount1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="withdraw_amount2" value="{{$withdraw_amount2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>贷款期限:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="deadline1" value="{{$deadline1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="deadline2" value="{{$deadline2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>服务费:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="service_charge1" value="{{$service_charge1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="service_charge2" value="{{$service_charge2}}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>放款金额:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="net_receipts1" value="{{$net_receipts1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="net_receipts2" value="{{$net_receipts2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>放款时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="loan_at1" value="{{$loan_at1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="loan_at2" value="{{$loan_at2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-button" style="margin-left: 20px;">
                        <input class="button" type="submit" value="搜索一下" />
                        <input class="button" type="button" value="放款" onclick="grantLoan()"/>
                    </div>
                </div>
            </form>
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth"><input type="checkbox" name="chb[]" id="all" onclick="setChecked(this)"/></th>
                            <th class="sth">提现申请日期</th>
                            <th class="sth">客户名称</th>
                            <th class="sth">银行卡号</th>
                            <th class="sth">身份证号</th>
                            <th class="sth">用户借款金额(元)</th>
                            <th class="sth">贷款期限(天)</th>
                            <th class="sth">服务费(元)</th>
                            <th class="sth">放款金额</th>
                            <th class="sth">放款时间</th>
                            <th class="sth">放款状态</th>
                            <th class="sth">放款渠道</th>
                            <th class="sth">操作</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std"><input type="checkbox" name="chb[]"  onclick="setChecked(this)" value="{{$v->id}}"/></td>
                                <td class="std">{{$v->created_at}}</td>
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->name}}@endif</td>
                                <td class="std">{{$v->bank_card}}</td>
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->id_number}}@endif</td>
                                <td class="std">{{$v->withdraw_amount}}</td>
                                <td class="std">{{$v->deadline}}</td>
                                <td class="std">{{$v->service_charge}}</td>
                                <td class="std">{{$v->net_receipts}}</td>
                                <td class="std">{{$v->loan_at}}</td>
                                <td class="std">{{$orderStatusList[$v->order_status]}}</td>
                                <td class="std">@if(!empty($v->channel) || $v->channel === '0'){{$channelList[$v->channel]}}@endif</td>
                                <td class="std"><a href="javascript:;" onclick="repaymentPlan({{$v->id}})">还款计划</a></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['withdraw_amount1' => $withdraw_amount1, 'withdraw_amount2' => $withdraw_amount2,
                    'deadline1' => $deadline1, 'deadline2' => $deadline2, 'service_charge1' => $service_charge1, 'service_charge2' => $service_charge2,
                    'net_receipts1' => $net_receipts1, 'net_receipts2' => $net_receipts2, 'loan_at1' => $loan_at1, 'loan_at2' => $loan_at2,
                    'name' => $name, 'bank_card' => $bank_card, 'id_number' => $id_number, 'order_status' => $order_status,
                    'date1' => $date1, 'date2' => $date2])->links() }}
                </div>
            </div>
            <div class="table" style="width: 50%;">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">累计用户借款金额</th>
                            <th class="sth">累计优惠金额</th>
                            <th class="sth">累计扣除服务费</th>
                            <th class="sth">累计放款金额</th>
                        </tr>
                        <tr class="str">
                            <td class="std">{{$total_withdraw_amount}}</td>
                            <td class="std">{{$total_discount}}</td>
                            <td class="std">{{$total_service_charge}}</td>
                            <td class="std">{{$total_net_receipts}}</td>
                        </tr>
                    </table>
                </div>
            </div>
            <div id="optable" class="optable" onclick="closeDetails()"></div>
            <div id="rtable" class="rtable">
                <div style="width: 90%;height: 30px;">还款计划</div>
                <div class="utable" id="utable4"></div>
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
