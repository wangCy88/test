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

    <title>我的催收</title>
</head>

<body>
<div id="container" class="position">
    <div id="hd"></div>
    <div id="bd">
        <div id="main">
            <form action="{{url('selfCollect')}}" method="post">
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
                            <label>还款状态:</label>
                            <div class="kv-item-content">
                                <select style="width: 120px;" name="repay_status">
                                    <option value="">全部</option>
                                    <option value="0" @if($repay_status === '0') selected="selected" @endif>待审核</option>
                                    <option value="1" @if($repay_status === '1') selected="selected" @endif>审核通过</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="search-area">
                        <div class="kv-item ue-clear">
                            <label>应还款日:</label>
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
                </div>
                <div class="search-box ue-clear">
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
                            <label>指派时间:</label>
                            <div class="kv-item-content">
                                <input type="date" style="width: 120px;height: 25px;" name="deal_at1" value="{{$deal_at1}}"/>
                                <span>~</span>
                                <input type="date" style="width: 120px;height: 25px;" name="deal_at2" value="{{$deal_at2}}"/>
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
                            <th class="sth">借款人</th>
                            <th class="sth">身份证号</th>
                            <th class="sth">手机号</th>
                            <th class="sth">用户申请金额(元)</th>
                            <th class="sth">期限(天)</th>
                            <th class="sth">逾期(天)</th>
                            <th class="sth">当前利息(元)</th>
                            <th class="sth">滞纳金(元)</th>
                            <th class="sth">还款总额(元)</th>
                            <th class="sth">放款日</th>
                            <th class="sth">应还款日</th>
                            <th class="sth">逾期阶段</th>
                            <th class="sth">任务分配日</th>
                            <th class="sth">催收在案时间</th>
                            <th class="sth">还款状态</th>
                            <th class="sth">操作</th>
                        </tr>

                        @foreach($data as $v)
                            <tr class="str">
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->name}}@endif</td>
                                <td class="std">@if(!empty($v->merchantUsers)){{$v->merchantUsers->id_number}}@endif</td>
                                <td class="std">{{$v->phone}}</td>
                                <td class="std">{{$v->withdraw_amount}}</td>
                                <td class="std">{{$v->deadline}}</td>
                                <td class="std">{{$v->overdue}}</td>
                                <td class="std">{{$v->interest}}</td>
                                <td class="std">{{$v->late_fee}}</td>
                                <td class="std">{{$v->total_fee}}</td>
                                <td class="std">{{$v->loan_at}}</td>
                                <td class="std">{{$v->repayment_at}}</td>
                                <td class="std">{{$overdueStatusList[$v->overdue_status]}}</td>
                                <td class="std">{{$v->deal_at}}</td>
                                <td class="std">{{$v->continued}}</td>
                                <td class="std">{{$repayStatusList[$v->repay_status]}}</td>
                                <td class="std"><a href="javascript:;" onclick="collect({{$v->id}})">催收</a></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div class="grid">
                    {{ $data->appends(['id_number' => $id_number, 'phone' => $phone, 'repay_status' => $repay_status, 'date1' => $date1,
                    'date2' => $date2, 'name' => $name, 'withdraw_amount1' => $withdraw_amount1, 'withdraw_amount2' => $withdraw_amount2,
                    'deadline1' => $deadline1, 'deadline2' => $deadline2, 'interest1' => $interest1, 'interest2' => $interest2,
                    'late_fee1' => $late_fee1, 'late_fee2' => $late_fee2, 'total_fee1' => $total_fee1, 'total_fee2' => $total_fee2,
                    'loan_at1' => $loan_at1, 'loan_at2' => $loan_at2, 'deal_at1' => $deal_at1, 'deal_at2' => $deal_at2])->links() }}
                </div>
                <div id="optable" class="optable" onclick="closeDetails()"></div>
                <div id="rtable" class="rtable">
                    <div style="width: 90%;height: 30px;">催收</div>
                    <div class="utable" id="utable14"></div>
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
