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

    <title>到期管理</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('expireControl')}}" method="post">
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
                            <label>贷款状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="loan_status">
                                    <option value="">全部</option>
                                    <option value="0" @if($loan_status === '0') selected="selected" @endif>待审核</option>
                                    <option value="1" @if($loan_status === '1') selected="selected" @endif>审核通过</option>
                                    <option value="2" @if($loan_status === '2') selected="selected" @endif>拒绝</option>
                                    <option value="3" @if($loan_status === '3') selected="selected" @endif>逾期</option>
                                    <option value="4" @if($loan_status === '4') selected="selected" @endif>结清</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>展期状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="extension_status">
                                    <option value="">全部</option>
                                    <option value="0" @if($extension_status === '0') selected="selected" @endif>未申请</option>
                                    <option value="1" @if($extension_status === '1') selected="selected" @endif>申请成功</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>申请金额:</label>
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
                </div>
                <div class="search-box ue-clear">
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
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>实收金额:</label>
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
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>提现时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="date1" value="{{$date1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="date2" value="{{$date2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>展期天数:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="extension1" value="{{$extension1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="extension2" value="{{$extension2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>展期费用:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="extension_amount1" value="{{$extension_amount1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="extension_amount2" value="{{$extension_amount2}}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>当前利息:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="interest1" value="{{$interest1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="interest2" value="{{$interest2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>豁免金额:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="exemption_amount1" value="{{$exemption_amount1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="exemption_amount2" value="{{$exemption_amount2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>滞纳金:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="late_fee1" value="{{$late_fee1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="late_fee2" value="{{$late_fee2}}"/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search-box ue-clear">
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>还款总额:</label>
                            <div class="kv-item-content">
                                <input type="text" style="width: 100px;height: 28px;" name="total_fee1" value="{{$total_fee1}}"/>
                                <span>~</span>
                                <input type="text" style="width: 100px;height: 28px;" name="total_fee2" value="{{$total_fee2}}"/>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>还款日期:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="actual_repayment_at1" value="{{$actual_repayment_at1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="actual_repayment_at2" value="{{$actual_repayment_at2}}"/>
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
                            <th class="sth">订单号</th>
                            <th class="sth">借款人</th>
                            <th class="sth">身份证号</th>
                            <th class="sth">手机号</th>
                            <th class="sth">用户申请金额(元)</th>
                            <th class="sth">实收金额(元)</th>
                            <th class="sth">期限(天)</th>
                            <th class="sth">逾期(天)</th>
                            <th class="sth">当前本金(元)</th>
                            <th class="sth">展期次数</th>
                            <th class="sth">展期费用(元)</th>
                            <th class="sth">当前利息(元)</th>
                            <th class="sth">还款豁免金额(元)</th>
                            <th class="sth">滞纳金(元)</th>
                            <th class="sth">还款总额(元)</th>
                            <th class="sth">放款日</th>
                            <th class="sth">应还款日</th>
                            <th class="sth">贷款状态</th>
                            <th class="sth">展期状态</th>
                            <th class="sth">逾期阶段</th>
                            <th class="sth">还款日期</th>
                            <th class="sth">放款渠道</th>
                            <th class="sth">还款状态</th>
                            <th class="sth">操作</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">{{$v->id}}</td>
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->name}}@endif</td>
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->id_number}}@endif</td>
                                <td class="std">{{$v->phone}}</td>
                                <td class="std">{{$v->withdraw_amount}}</td>
                                <td class="std">{{$v->net_receipts}}</td>
                                <td class="std">{{$v->deadline}}</td>
                                <td class="std">{{$v->overdue}}</td>
                                <td class="std">{{$v->withdraw_amount}}</td>
                                <td class="std">{{$v->extension}}</td>
                                <td class="std">{{$v->extension_amount}}</td>
                                <td class="std">{{$v->interest}}</td>
                                <td class="std">{{$v->exemption_amount}}</td>
                                <td class="std">{{$v->late_fee}}</td>
                                <td class="std">{{$v->total_fee}}</td>
                                <td class="std">{{$v->loan_at}}</td>
                                <td class="std">{{$v->repayment_at}}</td>
                                <td class="std">{{$loanStatusList[$v->loan_status]}}</td>
                                <td class="std">{{$extensionStatusList[$v->extension_status]}}</td>
                                <td class="std">{{$overdueStatusList[$v->overdue_status]}}</td>
                                <td class="std">{{$v->actual_repayment_at}}</td>
                                <td class="std">@if(!empty($v->channel) || $v->channel === '0'){{$channelList[$v->channel]}}@endif</td>
                                <td class="std">{{$repayStatusList[$v->repay_status]}}</td>
                                <td class="std"><a href="javascript:;" onclick="underlineExtension({{$v->id}})">线下展期</a><br/><a href="javascript:;" onclick="underlineRepay({{$v->id}})">线下还款</a></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['name' => $name, 'id_number' => $id_number, 'phone' => $phone, 'loan_status' => $loan_status,
                    'date1' => $date1, 'date2' => $date2, 'withdraw_amount1' => $withdraw_amount1,
                    'withdraw_amount2' => $withdraw_amount2, 'deadline1' => $deadline1, 'deadline2' => $deadline2,
                    'service_charge1' => $service_charge1, 'service_charge2' => $service_charge2, 'net_receipts1' => $net_receipts1,
                    'net_receipts2' => $net_receipts2, 'loan_at1' => $loan_at1, 'loan_at2' => $loan_at2,
                    'extension1' => $extension1, 'extension2' => $extension2, 'extension_amount1' => $extension_amount1,
                    'extension_amount2' => $extension_amount2, 'interest1' => $interest1, 'interest2' => $interest2,
                    'exemption_amount1' => $exemption_amount1, 'exemption_amount2' => $exemption_amount2, 'late_fee1' => $late_fee1,
                    'late_fee2' => $late_fee2, 'total_fee1' => $total_fee1, 'total_fee2' => $total_fee2, 'extension_status' => $extension_status,
                    'actual_repayment_at1' => $actual_repayment_at1, 'actual_repayment_at2' => $actual_repayment_at2])->links() }}
                </div>
            </div>
            <div class="table">
                <div class="grid">
                    <table class="stable">
                        <tr class="str">
                            <th class="sth">今日应还款总数</th>
                            <th class="sth">今日已还款总数</th>
                            <th class="sth">今日已还款比例</th>
                            <th class="sth">今日未还款总数</th>
                            <th class="sth">今日未还款比例</th>
                        </tr>
                        <tr class="str">
                            <td class="std">{{$total_repayment}}</td>
                            <td class="std">{{$repayment_done}}</td>
                            <td class="std">{{$repayment_done_per}}%</td>
                            <td class="std">{{$repayment_do}}</td>
                            <td class="std">{{$repayment_do_per}}%</td>
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
