//登录
function login(){
    var username = $('#username').val();
    var password = $('#password').val();
    var code = $('#code').val();
    if(username.length != 0 && password.length != 0 && code.length != 0){
        $.ajax({
            url: "/login",
            data: {"username" : username, "password" : password, "code" : code},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    window.location.href = 'main';
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//登出
function logout(){
    $.ajax({
        url: "/logout",
        data: {},
        type: "Post",
        dataType: "json",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function (data) {
            if (data.code === 0) {
                window.location.href = 'login';
            }
        },
        error : function () {

        }
    });
}

//复选
function setChecked(obj) {
    var chbs=document.getElementsByName("chb[]");//获取所有复选框对象

    //JS的if判断中Undefined类型视为false，其他类型视为true；
    //obj.id是定义过的值，类型为非Undefined，所以视为true。
    if(obj.id)
    {
        for(var i=1;i<chbs.length;i++)
        {
            if(obj.checked == true)
            {
                //全选
                chbs[i].checked = true;
            }
            else
            {
                //全不选
                chbs[i].checked = false;
            }
        }
    }
    else
    {
        //先假设子选择全选，那么使全选框选中。
        chbs[0].checked = true;

        //若子选项没有全选，全选框不选中。
        for(var i=1;i<chbs.length;i++)
        {
            if(chbs[i].checked == false)
            {
                chbs[0].checked = false;
            }
        }
    }
}

//切换模块
function changeModel(id){
    if(id.length != 0){
        $.ajax({
            url: "/changeModel",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                console.log(data);
                if (data.code === 0 && data.data.length > 0) {
                    $('#tree').html('');
                    var table = '';
                    table += "<a href='javascript:;' class='ue-clear'><i class='nav-ivon'></i>" +
                        "<span class='nav-text'>" + data.model + "</span></a>";
                    table += "<ul class='subnav'>";
                    for(var p in data.data){
                        if(data.data[p].id > 0) {
                            table += "<li class='subnav-li' href='" + data.url + data.data[p].route + "' " + "data-id='" + data.data[p].id + "'>" +
                                "<a href='javascript:;' class='ue-clear'><i class='subnav-icon'></i>" +
                                "<span class='subnav-text'>" + data.data[p].name + "</span></a></li>";
                        }
                    }
                    table += "</ul>";
                    $('#tree').html(table);
                }
            },
            error : function () {

            }
        });
    }
}

//切换窗口
function changeWindow(n){
    for(var i=1; i<=5; i++){
        if(n == i){
            $('#xtable' + i).css('display', 'block');
            $('#window' + i).css('background', '#ffff00');
        }else{
            $('#xtable' + i).css('display', 'none');
            $('#window' + i).css('background', '#3ca7d9');
        }
    }
}

//禁用滚动条
function unScroll(){
    var top=$(document).scrollTop();
    $(document).on('scroll.unable',function (e){
        $(document).scrollTop(top);
    })
}

//解除禁用滚动条
function removeUnScroll(){
    $(document).unbind("scroll.unable");
}

//查看流程
function viewProcess(id, userid){
    if(id.length != 0 && userid.length != 0){
        $.ajax({
            url: "/viewProcess",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0 && data.data.length > 0) {
                    $('#viewProcess').html('');
                    var table = '';
                    table += "<table class='stable' style='margin-top: 54px;' >" +
                        "<tr class='str'><th class='sth'>编号</th>" +
                        "<th class='sth'>当前节点</th>" +
                        "<th class='sth'>类型</th>" +
                        "<th class='sth'>回退次数</th>" +
                        "<th class='sth'>处理人</th>" +
                        "<th class='sth'>结果</th>" +
                        "<th class='sth'>意见</th>" +
                        "<th class='sth'>开始时间</th>" +
                        "<th class='sth'>结束时间</th>" +
                        "<th class='sth'>操作</th></tr>";
                    for(var p in data.data){
                        if(data.data[p].id > 0) {
                            table += "<tr class='str'><td class='std'>" + (parseInt(p) + parseInt(1)) + "</td>" +
                                "<td class='std'>" + data.currNodeList[data.data[p].curr_node] + "</td>" +
                                "<td class='std'>" + data.typeList[data.data[p].type] + "</td>" +
                                "<td class='std'>" + data.data[p].back_times + "</td>" ;
                            if(data.data[p].dealer === 0){
                                table += "<td class='std'>决策系统</td>"
                            }else{
                                if(data.data[p].auth_users != null) {
                                    table += "<td class='std'>" + data.data[p].auth_users.name + "</td>";
                                }else{
                                    table += "<td class='std'></td>";
                                }
                            }
                            table += "<td class='std'>" + data.resultList[data.data[p].result] + "</td>" +
                                "<td class='std'>" + data.data[p].opinion + "</td>" +
                                "<td class='std'>" + data.data[p].created_at + "</td>" +
                                "<td class='std'>" + data.data[p].updated_at + "</td>" +
                                "<td class='std'><a href='javascript:;' onclick='getDetails(" + userid + ")'>详情</a></td></tr>";
                        }
                    }
                    table += "</table>";
                    $('#viewProcess').html(table);
                    $('#viewProcess').css('display','block');
                }
            },
            error : function () {

            }
        });
    }
}

//获取详情
function getDetails(userid){
    if(userid.length != 0){
        $.ajax({
            url: "/getDetails",
            data: {"userid" : userid},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0 && data.data.id > 0) {
                    $('#xtable1').html('');
                    var table = '';
                    table += "<table class='stable'>" +
                        "<tr class='str'><td class='std bgblue'>姓名:</td><td class='std'>" + data.data.name + "</td>" +
                        "<td class='std bgblue'>身份证:</td><td class='std'>" + data.data.id_number + "</td>" +
                        "<td class='std bgblue'>手机号码:</td><td class='std'>" + data.data.phone + "</td></tr>" +
                        "<tr class='str'><td class='std bgblue'>开户银行:</td><td class='std'></td>" +
                        "<td class='std bgblue'>银行号:</td><td class='std'></td>" +
                        "<td class='std bgblue'>银行预留手机号码:</td><td class='std'></td></tr>" +
                        "<tr class='str'><td class='std bgblue'>婚姻状况:</td><td class='std'>" + data.marriageList[data.data.marriage] + "</td>" +
                        "<td class='std bgblue'>家庭地址:</td><td class='std'>" + data.data.curr_prov +
                        ' ' + data.data.curr_city +
                        ' ' + data.data.curr_area + "</td>" +
                        "<td class='std bgblue'>发薪日:</td><td class='std'>" + data.data.pay_day + "</td></tr>" +
                        "<tr class='str'><td class='std bgblue'>公司名称:</td><td class='std'>" + data.data.merchant_users_ex.company + "</td>" +
                        "<td class='std bgblue'>公司地址:</td><td class='std'>" + data.data.merchant_users_ex.comp_prov +
                        ' ' + data.data.merchant_users_ex.comp_city +
                        ' ' + data.data.merchant_users_ex.comp_area + "</td>" +
                        "<td class='std bgblue'>公司电话:</td><td class='std'>" + data.data.merchant_users_ex.comp_code +
                        '-' + data.data.merchant_users_ex.comp_phone + "</td></tr></table>";
                    table += "<table class='stable' style='margin-top: 5%;'>";
                    table += "<tr class='str'> <th class='sth'>用户等级</th> <th class='sth'>当前授信额度</th> " +
                        "<th class='sth'>可用额度</th> <th class='sth'>累计增长额度</th> <th class='sth'>累计提现金额</th> " +
                        "<th class='sth'>成功提现次数</th> <th class='sth'>停滞天数</th> <th class='sth'>账户状态</th> " +
                        "<th class='sth'>异常次数</th> <th class='sth'>渠道</th> </tr>";
                    table += "<tr class='str'> <td class='std'>" + data.dataPre.account_level + "</td> " +
                        "<td class='std'>" + data.dataPre.credit_limit + "</td> " +
                        "<td class='std'>" + data.dataPre.usable_limit + "</td> " +
                        "<td class='std'></td> " +
                        "<td class='std'>" + data.dataPre.total_loan_amount + "</td> " +
                        "<td class='std'>" + data.dataPre.withdraw_success + "</td> " +
                        "<td class='std'>" + data.dataPre.stagnant_day + "</td> " +
                        "<td class='std'>" + data.accountStatusList[data.dataPre.account_status] + "</td> " +
                        "<td class='std'>" + data.dataPre.abnormal_times + "</td> " +
                        "<td class='std'>";
                    for(var p in data.channels){
                        if(data.channels[p].id == data.dataPre.channel){
                            table += data.channels[p].name;
                        }
                    }
                    table += "</td> </tr>";
                    table += "</table>";
                    $('#xtable1').html(table);
                    $('#xtable2').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>联系人关系</th> <th class='sth'>联系人姓名</th> " +
                        "<th class='sth'>联系电话</th> <th class='sth'>家庭住址</th> </tr>";
                    if(data.emergencyContacts != null){
                        for(var p in data.emergencyContacts) {
                            if(data.emergencyContacts[p].type != null)
                            table += "<tr class='str'> <td class='std'>" + data.emergencyContacts[p].type + "</td> " +
                                "<td class='std'>" + data.emergencyContacts[p].name + "</td> " +
                                "<td class='std'>" + data.emergencyContacts[p].phone + "</td> " +
                                "<td class='std'></td> </tr>";
                        }
                    }
                    table += "</table>";
                    table += "<table class='stable' style='margin-top: 5%;'>";
                    table += "<tr class='str'> <th class='sth'>身份证正面照</th> <th class='sth'>活体正面照</th> </tr>";
                    table += "<tr class='str'> <td class='std'><img class='icImg' src='" + data.imageUri + "'/></td> " +
                        "<td class='std'><img class='icImg' src='" + data.picUri + "'/></td> </tr>";
                    $('#xtable2').html(table);
                    $('#xtable3').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>备注的姓名</th> <th class='sth'>手机号</th> </tr>";
                    if(data.allContacts != null){
                        for(var p in data.allContacts){
                            if(p == 0){
                                for(var k in data.allContacts[p]){
                                    table += "<tr class='str' style='color: #ff0000'> <td class='std'>" + data.allContacts[p][k] + "</td> " +
                                        "<td class='std' style='color: #ff0000'>" + k + "</td> </tr>";
                                }
                            }else{
                                for(var k in data.allContacts[p]){
                                    table += "<tr class='str'> <td class='std'>" + data.allContacts[p][k] + "</td> " +
                                        "<td class='std'>" + k + "</td> </tr>";
                                }
                            }
                        }
                    }
                    table += "</table>";
                    $('#xtable3').html(table);
                    $('#xtable4').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>运营商报告</th> " +
                        "<th class='sth'><a href='javascript:;' onclick='getReportUrl(" + data.data.id + ")'>点击查看</a></th> " +
                        "<th class='sth'>淘宝报告</th> " +
                        "<th class='sth'><a href='javascript:;' onclick='getTbReportUrl(" + data.data.id + ")'>点击查看</a></th> </tr>";
                    table += "</table>";
                    $('#xtable4').html(table);
                    $('#xtable5').html('');
                    var table = '';
                    table += "<table class='stable'>" +
                    "<tr class='str'><td class='std bgblue'>公安校验:</td><td class='std'>未校验</td>" +
                    "<td class='std bgblue'>授信决策分数:</td><td class='std'>" + data.dataPre.score + "</td>" +
                    "<td class='std bgblue'>上次查询时间:</td><td class='std'></td></tr>";
                    table += "</table>";
                    $('#xtable5').html(table);
                    for(var i=1; i<=5; i++){
                        if(1 == i){
                            $('#xtable' + i).css('display', 'block');
                            $('#window' + i).css('background', '#ffff00');
                        }else{
                            $('#xtable' + i).css('display', 'none');
                            $('#window' + i).css('background', '#3ca7d9');
                        }
                    }
                    unScroll();
                    $('#otable').css('display','block');
                    $('#optable').css('display','block');
                }
            },
            error : function () {

            }
        });
    }
}

//获取白骑士运营商报告
function getReportUrl(id){
    if(id.length != 0){
        $.ajax({
            url: "/getReportUrl",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    window.open(data.data);
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//获取白骑士淘宝报告
function getTbReportUrl(id){
    if(id.length != 0){
        $.ajax({
            url: "/getTbReportUrl",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    window.open(data.data);
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//分配人员
function allotMember(){
    var ids = document.getElementsByName('chb[]');
    var ids_arr = [];
    for(p in ids){
        if(ids[p].checked){
            ids_arr.push(ids[p].value);
        }
    }
    if(ids_arr.length != 0){
        $.ajax({
            url: "/allotMember",
            data: {},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0 && data.data.length > 0) {
                    $('#utable2').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>登陆账号</th> <th class='sth'>人员姓名</th> " +
                        "<th class='sth'>操作</th> </tr>";
                    for(var p in data.data) {
                        if (data.data[p].id > 0) {
                            table += "<tr class='str'> <td class='std'>" + data.data[p].account + "</td> " +
                                "<td class='std'>" + data.data[p].name + "</td> " +
                                "<td class='std'><a href='javascript:;' onclick='allot(" + data.data[p].id + ")'>分配</a></td> </tr>";
                        }
                    }
                    table += "</table>";
                    $('#utable2').html(table);
                    unScroll();
                    $('#atable').css('display','block');
                    $('#optable').css('display','block');
                }
            },
            error : function () {

            }
        });
    }
}

//分配
function allot(userid){
    var ids = document.getElementsByName('chb[]');
    var ids_arr = [];
    for(p in ids){
        if(ids[p].checked){
            ids_arr.push(ids[p].value);
        }
    }
    if(ids_arr.length != 0 && userid.length != 0){
        $.ajax({
            url: "/allot",
            data: {"ids" : ids_arr, "userid" : userid},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//打分面板
function scoreDo(id){
    if(id.length != 0){
        $('#orderid').val(id);
        unScroll();
        $('#atable').css('display','block');
        $('#optable').css('display','block');
    }
}

//打分
function score(){
    var id = $('#orderid').val();
    var score = $('#score').val();
    var status = $('#status').val();
    if(id.length != 0 && score.length != 0 && status.length != 0){
        $.ajax({
            url: "/score",
            data: {"id" : id, "score" : score, "status" : status},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//还款计划
function repaymentPlan(id){
    if(id.length != 0){
        $.ajax({
            url: "/repaymentPlan",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable4').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>应还日期</th> <th class='sth'>月供</th> " +
                        "<th class='sth'>本期应还本金</th> <th class='sth'>本期应还利息</th> <th class='sth'>支付日期</th> " +
                        "<th class='sth'>订单状态</th> </tr>";
                    table += "<tr class='str'> <td class='std'>" + data.data.repayment_at + "</td> " +
                        "<td class='std'>" + data.data.month_supply + "</td> " +
                        "<td class='std'>" + data.data.withdraw_amount + "</td> " +
                        "<td class='std'>" + data.data.interest + "</td> " +
                        "<td class='std'>" + data.data.actual_repayment_at + "</td> " +
                        "<td class='std'>" + data.repayStatusList[data.data.repay_status] + "</td> </tr>";
                    table += "</table>";
                    $('#utable4').html(table);
                    unScroll();
                    $('#rtable').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//放款
function grantLoan(){
    var ids = document.getElementsByName('chb[]');
    var ids_arr = [];
    for(p in ids){
        if(ids[p].checked){
            ids_arr.push(ids[p].value);
        }
    }
    if(ids_arr.length != 0){
        $.ajax({
            url: "/grantLoan",
            data: {"ids" : ids_arr},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//线下展期
function underlineExtension(id){
    if(id.length != 0){
        $.ajax({
            url: "/underlineExtension",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//线下还款
function underlineRepay(id){
    if(id.length != 0){
        $.ajax({
            url: "/underlineRepay",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//新增渠道面板
function addChannel(){
    $('#utable5').html('');
    var table = '';
    table += "<div class='div'> <span class='span'>渠道名称:</span><input type='text' id='name'/> " +
        "<span class='span'>渠道专员:</span><input type='text' id='member'/> " +
        "<span class='span'>渠道来源:</span><input type='text' id='from'/> </div> " +
        "<div class='div'> <span class='span'>登陆用户名:</span><input type='text' id='username'/> " +
        "<span class='span'>登陆密码:</span><input type='text' id='password'/> </div> " +
        "<div class='div'> <span class='span'>注册单价:</span><input type='text' id='reg_price'/> " +
        "<span class='span'>注册转化率:</span><input type='text' id='reg_rate'/> </div> " +
        "<div class='div'> <span class='span'>完善资料单价:</span><input type='text' id='data_price'/> " +
        "<span class='span'>完善资料转化率:</span><input type='text' id='data_rate'/> </div> " +
        "<div class='div'> <span class='span'>授信通过单价:</span><input type='text' id='credit_price'/> " +
        "<span class='span'>授信通过转化率:</span><input type='text' id='credit_rate'/> </div> " +
        "<div class='div'> <span class='span'>订单单价:</span><input type='text' id='order_price'/> " +
        "<span class='span'>订单转化率:</span><input type='text' id='order_rate'/> " +
        "<span class='span'>借款转化率:</span><input type='text' id='loan_rate'/> </div> " +
        "<div class='div center'> <button type='button' class='span' onclick='addChannelDo()'>新增</button> " +
        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
    $('#utable5').html(table);
    unScroll();
    $('#rtable').css('display','block');
    $('#optable').css('display','block');
}

//新增渠道
function addChannelDo(){
    var name = $('#name').val();
    var member = $('#member').val();
    var from = $('#from').val();
    var username = $('#username').val();
    var password = $('#password').val();
    var reg_price = $('#reg_price').val();
    var reg_rate = $('#reg_rate').val();
    var data_price = $('#data_price').val();
    var data_rate = $('#data_rate').val();
    var credit_price = $('#credit_price').val();
    var credit_rate = $('#credit_rate').val();
    var order_price = $('#order_price').val();
    var order_rate = $('#order_rate').val();
    var loan_rate = $('#loan_rate').val();
    if(name.length != 0 && member.length != 0 && from.length != 0 && username.length != 0 && password.length != 0 &&
        reg_price.length != 0 && reg_rate.length != 0 && data_price.length != 0 && data_rate.length != 0 &&
        credit_price.length != 0 && credit_rate.length != 0 && order_price.length != 0 && order_rate.length != 0 &&
        loan_rate.length != 0){
        $.ajax({
            url: "/addChannelDo",
            data: {"name" : name, "member" : member, "from" : from, "username" : username, "password" : password,
                "reg_price" : reg_price, "reg_rate" : reg_rate, "data_price" : data_price, "data_rate" : data_rate,
                "credit_price" : credit_price, "credit_rate" : credit_rate, "order_price" : order_price,
                "order_rate" : order_rate, "loan_rate" : loan_rate},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('参数请填写完整');
    }
}

//获取渠道
function modifyChannel(id){
    if(id.length != 0){
        $.ajax({
            url: "/modifyChannel",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable5').html('');
                    var table = '';
                    table += "<div class='div'> <span class='span'>渠道名称:</span><input type='text' id='name' value='" + data.data.name + "'/> " +
                        "<span class='span'>渠道专员:</span><input type='text' id='member' value='" + data.data.member + "'/> " +
                        "<span class='span'>渠道来源:</span><input type='text' id='from' value='" + data.data.from + "'/> </div> " +
                        "<div class='div'> <span class='span'>注册单价:</span><input type='text' id='reg_price' value='" + data.data.reg_price + "'/> " +
                        "<span class='span'>注册转化率:</span><input type='text' id='reg_rate' value='" + data.data.reg_rate + "'/> </div> " +
                        "<div class='div'> <span class='span'>完善资料单价:</span><input type='text' id='data_price' value='" + data.data.data_price + "'/> " +
                        "<span class='span'>完善资料转化率:</span><input type='text' id='data_rate' value='" + data.data.data_rate + "'/> </div> " +
                        "<div class='div'> <span class='span'>授信通过单价:</span><input type='text' id='credit_price' value='" + data.data.credit_price + "'/> " +
                        "<span class='span'>授信通过转化率:</span><input type='text' id='credit_rate' value='" + data.data.credit_rate + "'/> </div> " +
                        "<div class='div'> <span class='span'>订单单价:</span><input type='text' id='order_price' value='" + data.data.order_price + "'/> " +
                        "<span class='span'>订单转化率:</span><input type='text' id='order_rate' value='" + data.data.order_rate + "'/> " +
                        "<span class='span'>借款转化率:</span><input type='text' id='loan_rate' value='" + data.data.loan_rate + "'/> </div> " +
                        "<div class='div center'> <button type='button' class='span' onclick='modifyChannelDo(" + id + ")'>修改</button> " +
                        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
                    $('#utable5').html(table);
                    unScroll();
                    $('#rtable').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//修改渠道
function modifyChannelDo(id){
    if(id.length != 0){
        var name = $('#name').val();
        var member = $('#member').val();
        var from = $('#from').val();
        var reg_price = $('#reg_price').val();
        var reg_rate = $('#reg_rate').val();
        var data_price = $('#data_price').val();
        var data_rate = $('#data_rate').val();
        var credit_price = $('#credit_price').val();
        var credit_rate = $('#credit_rate').val();
        var order_price = $('#order_price').val();
        var order_rate = $('#order_rate').val();
        var loan_rate = $('#loan_rate').val();
        if(name.length != 0 && member.length != 0 && from.length != 0 && reg_price.length != 0 && reg_rate.length != 0 &&
            data_price.length != 0 && data_rate.length != 0 && credit_price.length != 0 && credit_rate.length != 0 &&
            order_price.length != 0 && order_rate.length != 0 && loan_rate.length != 0){
            $.ajax({
                url: "/modifyChannelDo",
                data: {"id" : id, "name" : name, "member" : member, "from" : from, "reg_price" : reg_price,
                    "reg_rate" : reg_rate, "data_price" : data_price, "data_rate" : data_rate,
                    "credit_price" : credit_price, "credit_rate" : credit_rate, "order_price" : order_price,
                    "order_rate" : order_rate, "loan_rate" : loan_rate},
                type: "Post",
                dataType: "json",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (data) {
                    if (data.code === 0) {
                        alert(data.msg);
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                },
                error : function () {

                }
            });
        }else{
            alert('参数请填写完整');
        }
    }
}

//修改密码面板
function modifyPassword(id){
    if(id.length != 0){
        $('#utable6').html('');
        var table = '';
        table += "<div class='div'> <span class='span'>新密码:</span><input type='text' id='npassword'/> </div> " +
            "<div class='div center'> <button type='button' class='span' onclick='modifyPasswordDo(" + id + ")'>修改</button> " +
            "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
        $('#utable6').html(table);
        unScroll();
        $('#atable').css('display','block');
        $('#optable').css('display','block');
    }
}

//修改密码
function modifyPasswordDo(id){
    if(id.length != 0){
        var password = $('#npassword').val();
        if(password.length != 0){
            $.ajax({
                url: "/modifyPasswordDo",
                data: {"id" : id, "password" : password},
                type: "Post",
                dataType: "json",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (data) {
                    if (data.code === 0) {
                        alert(data.msg);
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                },
                error : function () {

                }
            });
        }else{
            alert('请填写新密码');
        }
    }
}

//删除渠道
function deleteChannel(id){
    if(id.length != 0){
        $.ajax({
            url: "/deleteChannel",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//获取路由
function modifyRoute(id){
    if(id.length != 0){
        $.ajax({
            url: "/modifyRoute",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable7').html('');
                    var table = '';
                    table += "<div class='div'> <span class='span'>路由名称:</span><input type='text' id='name' value='" + data.data.name + "'/> </div> " +
                        "<div class='div'> <span class='span'>路由路径:</span><input type='text' id='route' value='" + data.data.route + "'/> </div> " +
                        "<div class='div'> <span class='span'>上级路由:</span> <select id='upid'> <option value='0' ";
                    if(data.data.upid === 0){
                        table += "selected='selected'";
                    }
                    table += ">父路由</option> ";
                    for(var p in data.routes){
                        if(data.routes[p].id > 0) {
                            table += "<option value='" + data.routes[p].id + "' ";
                            if (data.routes[p].id === data.data.upid) {
                                table += "selected='selected'";
                            }
                            table += ">" + data.routes[p].name + "</option>"
                        }
                    }
                    table += "</select> </div> " +
                        "<div class='div center'> <button type='button' class='span' onclick='modifyRouteDo(" + id + ")'>修改</button> " +
                        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
                    $('#utable7').html(table);
                    unScroll();
                    $('#atable').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//修改路由
function modifyRouteDo(id){
    var name = $('#name').val();
    var route = $('#route').val();
    var upid = $('#upid').val();
    if(id.length != 0 && name.length != 0 && route.length != 0 && upid.length != 0){
        $.ajax({
            url: "/modifyRouteDo",
            data: {"id" : id, "name" : name, "route" : route, "upid" : upid},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写完整');
    }
}

//删除路由
function deleteRoute(id){
    if(id.length != 0){
        $.ajax({
            url: "/deleteRoute",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//获取商户
function modifyMerchant(id){
    if(id.length != 0){
        $.ajax({
            url: "/modifyMerchant",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable8').html('');
                    var table = '';
                    table += "<div class='div'> <span class='span'>商户名称:</span><input type='text' id='name' value='" + data.data.name + "'/> </div> " +
                        "<div class='div'> <span class='span'>商户备注:</span><input type='text' id='remark' value='" + data.data.remark + "'/> </div> " +
                        "<div class='div center'> <button type='button' class='span' onclick='modifyMerchantDo(" + id + ")'>修改</button> " +
                        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
                    $('#utable8').html(table);
                    unScroll();
                    $('#atable').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//修改商户
function modifyMerchantDo(id){
    var name = $('#name').val();
    var remark = $('#remark').val();
    if(id.length != 0 && name.length != 0){
        $.ajax({
            url: "/modifyMerchantDo",
            data: {"id" : id, "name" : name, "remark" : remark},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写完整');
    }
}

//关闭商户
function closeMerchant(id){
    if(id.length != 0){
        $.ajax({
            url: "/closeMerchant",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//开启商户
function openMerchant(id){
    if(id.length != 0){
        $.ajax({
            url: "/openMerchant",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//树状列表
function generate(json,par)
{
    for(var attr in json)
    {
        var ele=document.createElement('li');
        if(!json[attr])
            ele.innerHTML=" <input type='checkbox'></input>"+attr;
        else
        {
            if(json[attr] instanceof Object) {
                ele.innerHTML = "<span><span class='switch-open' onclick='toggle(this)'></span><input type='checkbox' onclick='checkChange(this)'/>" + attr + "</span>";
            }else{
                ele.innerHTML = "<span style='margin-left: 10%;'><input type='checkbox' onclick='checkChange(this)' name='routes' value='" + json[attr] + "' />" + attr + "</span>";
            }
            var nextpar=document.createElement('ul');
            ele.appendChild(nextpar);
            generate(json[attr],nextpar);
        }
        par.appendChild(ele);
    }
}

function generate2(json,par,info)
{
    for(var attr in json)
    {
        var ele=document.createElement('li');
        if(!json[attr])
            ele.innerHTML=" <input type='checkbox'></input>"+attr;
        else
        {
            if(json[attr] instanceof Object) {
                ele.innerHTML = "<span><span class='switch-open' onclick='toggle(this)'></span><input type='checkbox' onclick='checkChange(this)'/>" + attr + "</span>";
            }else {
                if ($.inArray(json[attr].toString(), info) >= 0) {
                    ele.innerHTML = "<span style='margin-left: 10%;'><input type='checkbox' onclick='checkChange(this)' name='routes2' value='" + json[attr] + "' checked='checked'/>" + attr + "</span>";
                } else {
                    ele.innerHTML = "<span style='margin-left: 10%;'><input type='checkbox' onclick='checkChange(this)' name='routes2' value='" + json[attr] + "' />" + attr + "</span>";
                }
            }
            var nextpar=document.createElement('ul');
            ele.appendChild(nextpar);
            generate2(json[attr],nextpar,info);
        }
        par.appendChild(ele);
    }
}

//处理展开和收起
function toggle(eve)
{
    var par=eve.parentNode.nextElementSibling;
    if(par.style.display=='none')
    {
        par.style.display='block';
        eve.className='switch-open';

    }
    else
    {
        par.style.display='none';
        eve.className='switch-close';
    }
}

//处理全部勾选和全部不选
function checkChange(eve)
{
    var oul=eve.parentNode.nextElementSibling;
    if(eve.checked)
    {
        for(var i=0;i<oul.querySelectorAll('input').length;i++)
        {
            oul.querySelectorAll('input')[i].checked=true;
        }
    }
    else
    {
        for(var i=0;i<oul.querySelectorAll('input').length;i++)
        {
            oul.querySelectorAll('input')[i].checked=false;
        }
    }
}

//获取路由列表
function addGroup(){
    $.ajax({
        url: "/addGroup",
        data: {},
        type: "Post",
        dataType: "json",
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        success: function (data) {
            if (data.code === 0) {
                /*var json={
                    '0-0':{
                        '0-0-0':1,
                        '0-0-1':2,
                        '0-0-2':3
                    },
                    '0-1':{
                        '0-1-0':4,
                        '0-1-1':5
                    }
                };*/
                var json = data.data;
                $('#name').val('');
                $('#utable9').html('');
                generate(json,document.getElementById('utable9'));
                unScroll();
                $('#gtable1').css('display','block');
                $('#optable').css('display','block');
            }
        },
        error : function () {

        }
    });
}

//新增角色
function addGroupDo(){
    var name = $('#name').val();
    var routes = document.getElementsByName('routes');
    var routes_arr = [];
    for(p in routes){
        if(routes[p].checked){
            routes_arr.push(routes[p].value);
        }
    }
    if(name.length != 0 && routes_arr.length != 0){
        $.ajax({
            url: "/addGroupDo",
            data: {"name" : name, "routes" : routes_arr},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//获取角色
function modifyGroup(id){
    if(id.length != 0){
        $.ajax({
            url: "/modifyGroup",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                //console.log(data);return;
                if (data.code === 0) {
                    $('#id').val(id);
                    $('#name2').val(data.name);
                    var json = data.data;
                    var info = data.route;
                    $('#utable10').html('');
                    generate2(json,document.getElementById('utable10'),info);
                    unScroll();
                    $('#gtable2').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//修改角色
function modifyGroupDo(){
    var id = $('#id').val();
    var name = $('#name2').val();
    var routes = document.getElementsByName('routes2');
    var routes_arr = [];
    for(p in routes){
        if(routes[p].checked){
            routes_arr.push(routes[p].value);
        }
    }
    if(id.length != 0 && name.length != 0 && routes_arr.length != 0){
        $.ajax({
            url: "/modifyGroupDo",
            data: {"id" : id, "name" : name, "routes" : routes_arr},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//删除角色
function deleteGroup(id){
    if(id.length != 0){
        $.ajax({
            url: "/deleteGroup",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//获取用户
function modifyUser(id){
    if(id.length != 0){
        $.ajax({
            url: "/modifyUser",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable11').html('');
                    var table = '';
                    table += "<div class='div'><span class='span'>姓名:</span><input type='text' id='name' value='" + data.data.name + "'/></div> " +
                        "<div class='div'><span class='span'>手机号:</span><input type='text' id='phone' value='" + data.data.phone + "'/></div> " +
                        "<div class='div'><span class='span'>性别:</span><select id='sex'>";
                    for(var p in data.sexList){
                        if(p < data.sexList.length){
                            table += "<option value='" + p + "'";
                            if (p.toString() === data.data.sex.toString()) {
                                table += " selected='selected'";
                            }
                            table += ">" + data.sexList[p] + "</option>";
                        }
                    }
                    table += "</select></div> <div class='div'><span class='span'>在职状态:</span><select id='status'>";
                    for(var p in data.jobStatusList){
                        if(p < data.jobStatusList.length) {
                            table += "<option value='" + p + "'";
                            if (p.toString() === data.data.status.toString()) {
                                table += " selected='selected'";
                            }
                            table += ">" + data.jobStatusList[p] + "</option>";
                        }
                    }
                    table += "</select></div> <div class='div'><span class='span'>角色:</span><select id='gid'>";
                    for(var p in data.groups){
                        if(data.groups[p].id > 0) {
                            table += "<option value='" + data.groups[p].id + "'";
                            if (data.groups[p].id === data.data.gid) {
                                table += " selected='selected'";
                            }
                            table += ">" + data.groups[p].name + "</option>";
                        }
                    }
                    table += "</select></div> <div class='div center'> <button type='button' class='span' onclick='modifyUserDo(" + id + ")'>修改</button> " +
                        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
                    $('#utable11').html(table);
                    unScroll();
                    $('#mutable').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//修改用户
function modifyUserDo(id){
    var name = $('#name').val();
    var phone = $('#phone').val();
    var sex = $('#sex').val();
    var status = $('#status').val();
    var gid = $('#gid').val();
    if(id.length != 0 && name.length != 0 && phone.length != 0 && sex.length != 0 && status.length != 0 && gid.length != 0){
        $.ajax({
            url: "/modifyUserDo",
            data: {"id" : id, "name" : name, "phone" : phone, "sex" : sex, "status" : status, "gid" : gid},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//修改密码面板
function modifyUserPwd(id){
    if(id.length != 0){
        $('#utable12').html('');
        var table = '';
        table += "<div class='div'> <span class='span'>新密码:</span><input type='text' id='npassword'/> </div> " +
            "<div class='div center'> <button type='button' class='span' onclick='modifyUserPwdDo(" + id + ")'>修改</button> " +
            "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
        $('#utable12').html(table);
        unScroll();
        $('#uptable').css('display','block');
        $('#optable').css('display','block');
    }
}

//修改密码
function modifyUserPwdDo(id){
    if(id.length != 0){
        var password = $('#npassword').val();
        if(password.length != 0){
            $.ajax({
                url: "/modifyUserPwdDo",
                data: {"id" : id, "password" : password},
                type: "Post",
                dataType: "json",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (data) {
                    if (data.code === 0) {
                        alert(data.msg);
                        window.location.reload();
                    }else{
                        alert(data.msg);
                    }
                },
                error : function () {

                }
            });
        }else{
            alert('请填写新密码');
        }
    }
}

//删除用户
function deleteUser(id){
    if(id.length != 0){
        $.ajax({
            url: "/deleteUser",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//内派面板
function insideAssign(){
    var ids = document.getElementsByName('chb[]');
    var ids_arr = [];
    for(p in ids){
        if(ids[p].checked){
            ids_arr.push(ids[p].value);
        }
    }
    if(ids_arr.length != 0){
        $.ajax({
            url: "/insideAssign",
            data: {},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0 && data.data.length > 0) {
                    $('#utable2').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>登陆账号</th> <th class='sth'>人员姓名</th> " +
                        "<th class='sth'>操作</th> </tr>";
                    for(var p in data.data) {
                        if (data.data[p].id > 0) {
                            table += "<tr class='str'> <td class='std'>" + data.data[p].account + "</td> " +
                                "<td class='std'>" + data.data[p].name + "</td> " +
                                "<td class='std'><a href='javascript:;' onclick='inside(" + data.data[p].id + ")'>分配</a></td> </tr>";
                        }
                    }
                    table += "</table>";
                    $('#utable2').html(table);
                    unScroll();
                    $('#atable').css('display','block');
                    $('#optable').css('display','block');
                }
            },
            error : function () {

            }
        });
    }
}

//内派
function inside(userid){
    var ids = document.getElementsByName('chb[]');
    var ids_arr = [];
    for(p in ids){
        if(ids[p].checked){
            ids_arr.push(ids[p].value);
        }
    }
    if(ids_arr.length != 0 && userid.length != 0){
        $.ajax({
            url: "/inside",
            data: {"ids" : ids_arr, "userid" : userid},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//豁免面板
function exemption(id){
    if(id.length != 0){
        $.ajax({
            url: "/exemption",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable13').html('');
                    var table = '';
                    table += "<div class='div'> <span class='span'>滞纳金:</span><input type='text' id='late_fee' value='" + data.data + "'/> </div> " +
                        "<div class='div center'> <button type='button' class='span' onclick='exemptionDo(" + id + ")'>修改</button> " +
                        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
                    $('#utable13').html(table);
                    unScroll();
                    $('#htable').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//豁免
function exemptionDo(id){
    var late_fee = $('#late_fee').val();
    if(id.length != 0 && late_fee.length != 0){
        $.ajax({
            url: "/exemptionDo",
            data: {"id" : id, "late_fee" : late_fee},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//催收面板
function collect(id){
    if(id.length != 0){
        $.ajax({
            url: "/collect",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable14').html('');
                    var table = '';
                    table += "<div class='div'> <span class='span'>备注:</span><input type='text' id='remark'/>" +
                        "<button type='button' class='span' onclick='collectDo(" + id + ")'>确定</button> " +
                        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div> ";
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>记录时间</th> <th class='sth'>备注</th> " +
                        "<th class='sth'>处理人</th> </tr>";
                    for(var p in data.data) {
                        if (data.data[p].id > 0) {
                            table += "<tr class='str'> <td class='std'>" + data.data[p].created_at + "</td> " +
                                "<td class='std'>" + data.data[p].remark + "</td> " +
                                "<td class='std'>" + data.data[p].auth_users.name + "</td> </tr>";
                        }
                    }
                    table += "</table>";
                    $('#utable14').html(table);
                    unScroll();
                    $('#rtable').css('display','block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//催收
function collectDo(id){
    var remark = $('#remark').val();
    if(id.length != 0 && remark.length != 0){
        $.ajax({
            url: "/collectDo",
            data: {"id" : id, "remark" : remark},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//获取反馈
function getFeedback(id){
    if(id.length != 0){
        $.ajax({
            url: "/getFeedback",
            data: {"id" : id},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#utable16').html('');
                    var table = '';
                    table += "<div class='div'><span class='span'>意见反馈:</span><br/><textarea style='margin-left: 18%;' id='remark'>" + data.data.remark + "</textarea></div> " +
                        "<div class='div'><span class='span'>意见回复:</span><br/><textarea style='margin-left: 18%;' id='answer'>" + data.data.answer + "</textarea></div> " +
                        "<div class='div center'> <button type='button' class='span' onclick='answerFeedback(" + id + ")'>回复</button> " +
                        "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>"
                    $('#utable16').html(table);
                    $('#mutable').css('display', 'block');
                    $('#optable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//回复反馈
function answerFeedback(id){
    var answer = $('#answer').val();
    if(id.length != 0 && answer.length != 0){
        $.ajax({
            url: "/answerFeedback",
            data: {"id" : id, "answer" : answer},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('请填写参数');
    }
}

//调整额度面板
function adjustLimit(id){
    if (id.length != 0) {
        $('#utable15').html('');
        var table = '';
        table += "<div class='div'> <span class='span'>额度变化量:</span><input type='text' id='amount'/> </div> " +
            "<div class='div center'> <button type='button' class='span' onclick='adjustLimitDo(" + id + ")'>确定</button> " +
            "<button type='button' class='span' onclick='closeDetails()'>取消</button> </div>";
        $('#utable15').html(table);
        unScroll();
        $('#uptable').css('display', 'block');
        $('#optable').css('display', 'block');
    }
}

//调整额度
function adjustLimitDo(id){
    var amount = $('#amount').val();
    if(id.length != 0 && amount.length != 0){
        $.ajax({
            url: "/adjustLimitDo",
            data: {"id" : id, "amount" : amount},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//自动提额
function changeAutoRaise(num){
    if(num.length != 0){
        $.ajax({
            url: "/changeAutoRaise",
            data: {"num" : num},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                    window.location.reload();
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}

//关闭所有面板
function closeDetails()
{
    $('#htable').css('display','none');
    $('#uptable').css('display','none');
    $('#mutable').css('display','none');
    $('#gtable2').css('display','none');
    $('#gtable1').css('display','none');
    $('#rtable').css('display','none');
    $('#atable').css('display','none');
    $('#otable').css('display','none');
    $('#optable').css('display','none');
    removeUnScroll();
}

//智能探针
function getProbe()
{
    var id_no = $('#id_no').val();
    var id_name = $('#id_name').val();
    var phone = $('#phone').val();
    if(id_no.length != 0 && id_name.length != 0 && phone.length != 0){
        $.ajax({
            url: "/getProbe",
            data: {"id_no" : id_no, "id_name" : id_name, "phone" : phone},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#probeTable').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>最大逾期金额</th> <th class='sth'>最长逾期天数</th> " +
                        "<th class='sth'>最近逾期时间</th> <th class='sth'>当前逾期机构数</th> " +
                        "<th class='sth'>当前履约机构数</th> <th class='sth'>异常还款机构数</th> " +
                        "<th class='sth'>睡眠机构数</th> <th class='sth'>操作</th> </tr>";
                    table += "<tr class='str'> <td class='std'>" + data.data['max_overdue_amt'] + "</td> " +
                        "<td class='std'>" + data.data['max_overdue_days'] + "</td> " +
                        "<td class='std'>" + data.data['latest_overdue_time'] + "</td> " +
                        "<td class='std'>" + data.data['currently_overdue'] + "</td> " +
                        "<td class='std'>" + data.data['currently_performance'] + "</td> " +
                        "<td class='std'>" + data.data['acc_exc'] + "</td> " +
                        "<td class='std'>" + data.data['acc_sleep'] + "</td> " +
                        "<td class='std'><a href='javascript:;' onclick='getDetails(" + data.data.userid + ")'>查看</a></td> </tr>";
                    table += "</table>";
                    $('#probeTable').html(table);
                    $('#probeTable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('参数请填写完整');
    }
}

//全景雷达
function getRadar()
{
    var id_no = $('#id_no').val();
    var id_name = $('#id_name').val();
    var phone = $('#phone').val();
    if(id_no.length != 0 && id_name.length != 0 && phone.length != 0){
        $.ajax({
            url: "/getRadar",
            data: {"id_no" : id_no, "id_name" : id_name, "phone" : phone},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    $('#radarTable').html('');
                    var table = '';
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>申请准入分</th> <th class='sth'>申请准入置信度</th> " +
                        "<th class='sth'>查询机构数</th> <th class='sth'>查询消费金融类机构数</th> " +
                        "<th class='sth'>查询网络贷款类机构数</th> <th class='sth'>总查询次数</th> " +
                        "<th class='sth'>最近查询时间</th> <th class='sth'>近1个月总查询笔数</th> " +
                        "<th class='sth'>近3个月总查询笔数</th> <th class='sth'>近6个月总查询笔数</th> " +
                        "<th class='sth'>操作</th> </tr>";
                    table += "<tr class='str'> <td class='std'>" + data.data['apply_score'] + "</td> " +
                        "<td class='std'>" + data.data['apply_credibility'] + "</td> " +
                        "<td class='std'>" + data.data['query_org_count'] + "</td> " +
                        "<td class='std'>" + data.data['query_finance_count'] + "</td> " +
                        "<td class='std'>" + data.data['query_cash_count'] + "</td> " +
                        "<td class='std'>" + data.data['query_sum_count'] + "</td> " +
                        "<td class='std'>" + data.data['latest_query_time'] + "</td> " +
                        "<td class='std'>" + data.data['latest_one_month'] + "</td> " +
                        "<td class='std'>" + data.data['latest_three_month'] + "</td> " +
                        "<td class='std'>" + data.data['latest_six_month'] + "</td> " +
                        "<td class='std'><a href='javascript:;' onclick='getDetails(" + data.data.userid + ")'>查看</a></td> </tr>";
                    table += "</table>";
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>贷款行为分</th> <th class='sth'>贷款行为置信度</th> " +
                        "<th class='sth'>贷款放款总订单数</th> <th class='sth'>贷款已结清订单数</th> " +
                        "<th class='sth'>贷款逾期订单数</th> <th class='sth'>贷款机构数</th> " +
                        "<th class='sth'>消费金融类机构数</th> <th class='sth'>网络贷款类机构数</th> " +
                        "<th class='sth'>近1个月贷款笔数</th> <th class='sth'>近3个月贷款笔数</th> " +
                        "<th class='sth'>近6个月贷款笔数</th> <th class='sth'>历史贷款机构成功扣款笔数</th> " +
                        "<th class='sth'>历史贷款机构失败扣款笔数</th> <th class='sth'>近1个月贷款机构成功扣款笔数</th> " +
                        "<th class='sth'>近1个月贷款机构失败扣款笔数</th> <th class='sth'>信用贷款时长</th> " +
                        "<th class='sth'>最近一次贷款时间	</th> </tr>";
                    table += "<tr class='str'> <td class='std'>" + data.data['loans_score'] + "</td> " +
                        "<td class='std'>" + data.data['loans_credibility'] + "</td> " +
                        "<td class='std'>" + data.data['loans_count'] + "</td> " +
                        "<td class='std'>" + data.data['loans_settle_count'] + "</td> " +
                        "<td class='std'>" + data.data['loans_overdue_count'] + "</td> " +
                        "<td class='std'>" + data.data['loans_org_count'] + "</td> " +
                        "<td class='std'>" + data.data['consfin_org_count'] + "</td> " +
                        "<td class='std'>" + data.data['loans_cash_count'] + "</td> " +
                        "<td class='std'>" + data.data['loans_latest_one_month'] + "</td> " +
                        "<td class='std'>" + data.data['loans_latest_three_month'] + "</td> " +
                        "<td class='std'>" + data.data['loans_latest_six_month'] + "</td> " +
                        "<td class='std'>" + data.data['history_suc_fee'] + "</td> " +
                        "<td class='std'>" + data.data['history_fail_fee'] + "</td> " +
                        "<td class='std'>" + data.data['latest_one_month_suc'] + "</td> " +
                        "<td class='std'>" + data.data['latest_one_month_fail'] + "</td> " +
                        "<td class='std'>" + data.data['loans_long_time'] + "</td> " +
                        "<td class='std'>" + data.data['loans_latest_time'] + "</td> </tr>";
                    table += "</table>";
                    table += "<table class='stable'>";
                    table += "<tr class='str'> <th class='sth'>网贷建议授信额度</th> <th class='sth'>网贷额度置信度</th> " +
                        "<th class='sth'>网络贷款类机构数</th> <th class='sth'>网络贷款类产品数</th> " +
                        "<th class='sth'>网络贷款机构最大授信额度</th> <th class='sth'>网络贷款机构平均授信额度</th> " +
                        "<th class='sth'>消金建议授信额度</th> <th class='sth'>消金额度置信度</th> " +
                        "<th class='sth'>消费金融类机构数</th> <th class='sth'>消费金融类产品数</th> " +
                        "<th class='sth'>消费金融类机构最大授信额度</th> <th class='sth'>消费金融类机构平均授信额度</th> </tr>";
                    table += "<tr class='str'> <td class='std'>" + data.data['loans_credit_limit'] + "</td> " +
                        "<td class='std'>" + data.data['curr_loans_credibility'] + "</td> " +
                        "<td class='std'>" + data.data['curr_loans_org_count'] + "</td> " +
                        "<td class='std'>" + data.data['loans_product_count'] + "</td> " +
                        "<td class='std'>" + data.data['loans_max_limit'] + "</td> " +
                        "<td class='std'>" + data.data['loans_avg_limit'] + "</td> " +
                        "<td class='std'>" + data.data['consfin_credit_limit'] + "</td> " +
                        "<td class='std'>" + data.data['consfin_credibility'] + "</td> " +
                        "<td class='std'>" + data.data['curr_consfin_org_count'] + "</td> " +
                        "<td class='std'>" + data.data['consfin_product_count'] + "</td> " +
                        "<td class='std'>" + data.data['consfin_max_limit'] + "</td> " +
                        "<td class='std'>" + data.data['consfin_avg_limit'] + "</td> </tr>";
                    table += "</table>";
                    //历史
                    table += "<table class='stable' style='width: 50%;margin-top: 5%;'>";
                    table += "<tr class='str'> <th class='sth'>查询时间</th> <th class='sth'>贷款行为分</th> " +
                        "<th class='sth'>贷款放款总订单数</th> <th class='sth'>贷款已结清订单数</th> " +
                        "<th class='sth'>贷款逾期订单数</th> <th class='sth'>最近一次贷款时间</th> </tr>";
                    for(var p in data.oldData){
                        if(data.oldData[p].id > 0) {
                            table += "<tr class='str'> <td class='std'>" + data.oldData[p].created_at + "</td> " +
                                "<td class='std'>" + data.oldData[p].loans_score + "</td> " +
                                "<td class='std'>" + data.oldData[p].loans_count + "</td> " +
                                "<td class='std'>" + data.oldData[p].loans_settle_count + "</td> " +
                                "<td class='std'>" + data.oldData[p].loans_overdue_count + "</td> " +
                                "<td class='std'>" + data.oldData[p].loans_latest_time + "</td> </tr>";
                        }
                    }
                    table += "</table>";
                    $('#radarTable').html(table);
                    $('#radarTable').css('display','block');
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }else{
        alert('参数请填写完整');
    }
}


//给予客户借款
function giveOrder(res){

    if(res[0].length != 0 && confirm('是否确认给予借款?')){
        $.ajax({
            url: "/borrowMoney",
            data: {"phone" : res[0],"mchid":res[1],"withdraw_amount":res[2],"purpose":res[3]},
            type: "Post",
            dataType: "json",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (data) {
                if (data.code === 0) {
                    alert(data.msg);
                }else{
                    alert(data.msg);
                }
            },
            error : function () {

            }
        });
    }
}
