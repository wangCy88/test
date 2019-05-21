<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::any('test','ClientController@test');

//前端用
Route::any('createImgCode','ClientController@createImgCode');//图片验证码
Route::any('register','ClientController@register');//用户注册
Route::any('repeatRegister','ClientController@repeatRegister');//判断是否重复注册
Route::any('checkImgCode','ClientController@checkImgCode');//判断图片验证码是否正确
Route::any('clientLogin','ClientController@clientLogin');//用户登录
Route::any('clientCodeLogin','ClientController@clientCodeLogin');//用户验证码登录
Route::any('getHomeData','ClientController@getHomeData');//获取首页数据
Route::any('carouselData','ClientController@carouselData');//走马灯
Route::any('getUserStatus','ClientController@getUserStatus');//获取用户状态
Route::any('baseAuth','ClientController@baseAuth');//基础认证第一部分
Route::any('baseAuthEx','ClientController@baseAuthEx');//基础认证第二部分
Route::any('emergencyContacts','ClientController@emergencyContacts');//紧急联系人
Route::any('getUserInfo','ClientController@getUserInfo');//获取用户基础数据
Route::any('getEmergencyContacts','ClientController@getEmergencyContacts');//获取紧急联系人
Route::any('bankCardBindCode','ClientController@bankCardBindCode');//银行卡绑定验证码
Route::any('bankCardBind','ClientController@bankCardBind');//银行卡绑定
Route::any('getBankCard','ClientController@getBankCard');//获取银行卡列表
Route::any('computeLoanBudget','ClientController@computeLoanBudget');//计算借款预算
Route::any('borrowMoney','ClientController@borrowMoney');//借款
Route::any('getRepayDetail','ClientController@getRepayDetail');//获取还款明细
Route::any('repayment','ClientController@repayment');//还款
Route::any('getWithdrawRecord','ClientController@getWithdrawRecord');//获取借款记录
Route::any('submitFeedback','ClientController@submitFeedback');//提交意见反馈
Route::any('getFeedback','ClientController@getFeedback');//获取意见反馈
Route::any('getFeedbackDetail','ClientController@getFeedbackDetail');//获取意见反馈详情
Route::any('changePassword','ClientController@changePassword');//修改密码
Route::any('retrievePassword','ClientController@retrievePassword');//找回密码
Route::any('checkEquipmentLogin','ClientController@checkEquipmentLogin');//判断设备登录
Route::any('getRepayInfo','ClientController@getRepayInfo');//获取还款信息
Route::any('ocrAuthorizeNotify','ClientController@ocrAuthorizeNotify');//OCR鉴权通知
Route::any('changeMasterCard','ClientController@changeMasterCard');//更改银行卡主卡
Route::any('tbSuccessNotify','ClientController@tbSuccessNotify');//淘宝成功通知
Route::any('getShareUrl','ClientController@getShareUrl');//获取分享链接
Route::any('exchangeMchCode','ClientController@exchangeMchCode');//转换商户码
Route::any('getExtensionAmount','ClientController@getExtensionAmount');//获取展期费用
Route::any('userExtension','ClientController@userExtension');//用户展期
Route::any('getFriendShareUrl','ClientController@getFriendShareUrl');//获取好友分享链接
Route::any('existsInterestFreeCoupon','ClientController@existsInterestFreeCoupon');//判断是否存在免息券
Route::any('authorizationPay','ClientController@authorizationPay');//授权支付
Route::any('getCreditAndRecommend','ClientController@getCreditAndRecommend');//获取信用审查推荐
Route::any('getRecommendList','ClientController@getRecommendList');//获取精品推荐列表
Route::any('getOneRecommend','ClientController@getOneRecommend');//获取单个精品推荐
Route::any('checkRefundQualification','ClientController@checkRefundQualification');//判断退款资格
Route::any('submitRefundApplication','ClientController@submitRefundApplication');//提交退款申请
Route::any('recommendStatistics','ClientController@recommendStatistics');//精品推荐统计



Route::any('bqsMnoVerify','ValidateController@bqsMnoVerify');//白骑士运营商认证填写基础信息
Route::any('bqsMnoNextVerify','ValidateController@bqsMnoNextVerify');//白骑士运营商二次鉴权
Route::any('bqsMnoLoginCodeResend','ValidateController@bqsMnoLoginCodeResend');//白骑士重发登陆验证码
Route::any('bqsMnoAuthCodeResend','ValidateController@bqsMnoAuthCodeResend');//白骑士重发二次鉴权验证码
Route::any('tbAuthorizeNotify','ValidateController@tbAuthorizeNotify');//淘宝授权通知

//Route::any('bqsTbVerify','ValidateController@bqsTbVerify');//白骑士淘宝登录
//Route::any('bqsTbLoginCodeResend','ValidateController@bqsTbLoginCodeResend');//白骑士淘宝重发登陆验证码
//Route::any('getMnoReportData','ValidateController@getMnoReportData');//白骑士获取运营商报告数据
//Route::any('getReportView','ValidateController@getMnoReportView');//白骑士获取运营商报告页面
//Route::any('getOriginalData','ValidateController@getOriginalData');//白骑士获取原始数据
//Route::any('getReportView','ValidateController@getReportView');//白骑士获取报告页面


Route::any('transferNotify','PaymentController@transferNotify');//合利宝代付回调
Route::any('confirmPay','PaymentController@confirmPay');//合利宝异步通知接口
Route::any('confirmPayExtension','PaymentController@confirmPayExtension');//合利宝异步通知展期接口
Route::any('confirmPayCredit','PaymentController@confirmPayCredit');//合利宝异步通知信用接口
Route::any('aliPayNotify','PaymentController@aliPayNotify');//支付宝回调
Route::any('aliPayNotifyRepay','PaymentController@aliPayNotifyRepay');//支付宝还款回调
Route::any('aliPayNotifyExtension','PaymentController@aliPayNotifyExtension');//支付宝展期回调
Route::any('aliPayTradeRefund','PaymentController@aliPayTradeRefund');//支付宝退款


Route::any('unbindCard','PaymentController@unbindCard');//合利宝银行卡解绑
/*Route::any('transfer','PaymentController@transfer');//合利宝代付
Route::any('transferQuery','PaymentController@transferQuery');//合利宝代付结果查询
Route::any('bindCardCode','PaymentController@bindCardCode');//合利宝鉴权绑卡短信
Route::any('bindCard','PaymentController@bindCard');//合利宝鉴权绑卡
Route::any('bindPayCard','PaymentController@bindPayCard');//合利宝绑卡支付短信
Route::any('bindPay','PaymentController@bindPay');//合利宝绑卡支付
Route::any('unbindCard','PaymentController@unbindCard');//合利宝银行卡解绑
Route::any('cardBindList','PaymentController@cardBindList');//合利宝用户绑定银行卡信息查询*/


Route::any('outsideSystemDeduction','SystemController@outsideSystemDeduction');//外部系统扣款


//后端用
Route::any('login','IndexController@login');//登陆
Route::any('logout','IndexController@logout');//登出
Route::any('createImgLoginCode/{rand}','IndexController@createImgLoginCode');//图片验证码
Route::group(['middleware' => ['login']],function() {
    Route::any('main', 'IndexController@main');//页面顶部显示
    Route::any('changeModel', 'IndexController@changeModel');//切换模块
    /*Route::any('nav', 'IndexController@nav');//页面底部显示*/
    Route::any('index', 'IndexController@index');//首页

    Route::any('examineList', 'WorkController@examineList');//审核清单
    Route::any('viewProcess', 'WorkController@viewProcess');//查看流程
    Route::any('getDetails', 'WorkController@getDetails');//获取详情
    Route::any('getReportUrl', 'WorkController@getReportUrl');//获取白骑士运营商报告
    Route::any('getTbReportUrl', 'WorkController@getTbReportUrl');//获取白骑士淘宝报告
    Route::any('taskControl', 'WorkController@taskControl');//任务调度
    Route::any('allotMember', 'WorkController@allotMember');//分配人员
    Route::any('allot', 'WorkController@allot');//分配
    Route::any('taskDo', 'WorkController@taskDo');//待办任务
    Route::any('taskDone', 'WorkController@taskDone');//已办任务
    Route::any('score', 'WorkController@score');//打分
    Route::any('selfCollect', 'WorkController@selfCollect');//我的催收
    Route::any('collect', 'WorkController@collect');//催收面板
    Route::any('collectDo', 'WorkController@collectDo');//催收

    Route::any('accountRecharge', 'RechargeController@accountRecharge');//账户充值
    Route::any('rechargeRecord', 'RechargeController@rechargeRecord');//充值记录
    Route::any('consumeRecord', 'RechargeController@consumeRecord');//消费记录

    Route::any('customSelect', 'CustomController@customSelect');//客户查询
    Route::any('normalCustom', 'CustomController@normalCustom');//正常账号管理
    Route::any('adjustLimitDo', 'CustomController@adjustLimitDo');//调整额度
    Route::any('changeAutoRaise', 'CustomController@changeAutoRaise');//自动提额
    Route::any('abnormalCustom', 'CustomController@abnormalCustom');//异常账号管理
    Route::any('orderGeneration', 'CustomController@orderGeneration');//订单生成管理

    Route::any('withdrawApply', 'FinanceController@withdrawApply');//提现申请列表
    Route::any('withdrawControl', 'FinanceController@withdrawControl');//放款管理
    Route::any('repaymentPlan', 'FinanceController@repaymentPlan');//还款计划
    Route::any('grantLoan', 'FinanceController@grantLoan');//放款
    Route::any('repaymentControl', 'FinanceController@repaymentControl');//还款管理
    Route::any('underlineExtension', 'FinanceController@underlineExtension');//线下展期
    Route::any('underlineRepay', 'FinanceController@underlineRepay');//线下还款
    Route::any('expireControl', 'FinanceController@expireControl');//到期管理

    Route::any('collectControl', 'CollectController@collectControl');//催收管理
    Route::any('insideAssign', 'CollectController@insideAssign');//内派面板
    Route::any('inside', 'CollectController@inside');//内派
    Route::any('exemption', 'CollectController@exemption');//豁免面板
    Route::any('exemptionDo', 'CollectController@exemptionDo');//豁免

    Route::any('statistics', 'ReportController@statistics');//首页统计
    Route::any('getWeekRegister', 'ReportController@getWeekRegister');//获取周新增用户
    Route::any('dataMonitor', 'ReportController@dataMonitor');//数据监控
    Route::any('getDayLoan', 'ReportController@getDayLoan');//获取每日放款

    Route::any('channelConfig', 'ChannelController@channelConfig');//渠道配置
    Route::any('addChannelDo', 'ChannelController@addChannelDo');//新增渠道
    Route::any('modifyChannel', 'ChannelController@modifyChannel');//获取渠道信息
    Route::any('modifyChannelDo', 'ChannelController@modifyChannelDo');//修改渠道
    Route::any('modifyPasswordDo', 'ChannelController@modifyPasswordDo');//修改密码
    Route::any('deleteChannel', 'ChannelController@deleteChannel');//删除渠道
    Route::any('channelMonitor', 'ChannelController@channelMonitor');//渠道监控


    Route::any('groupControl', 'SystemController@groupControl');//角色管理
    Route::any('addGroup', 'SystemController@addGroup');//获取路由列表
    Route::any('addGroupDo', 'SystemController@addGroupDo');//新增角色
    Route::any('modifyGroup', 'SystemController@modifyGroup');//获取角色
    Route::any('modifyGroupDo', 'SystemController@modifyGroupDo');//修改角色
    Route::any('deleteGroup', 'SystemController@deleteGroup');//删除角色
    Route::any('userControl', 'SystemController@userControl');//用户管理
    Route::any('addUser', 'SystemController@addUser');//新增用户
    Route::any('modifyUser', 'SystemController@modifyUser');//获取用户
    Route::any('modifyUserDo', 'SystemController@modifyUserDo');//修改用户
    Route::any('modifyUserPwdDo', 'SystemController@modifyUserPwdDo');//修改密码
    Route::any('deleteUser', 'SystemController@deleteUser');//删除用户

    /*Route::any('intelligentProbe','ValidateController@intelligentProbe');//智能探针页面
    Route::any('getProbe','ValidateController@getProbe');//智能探针
    Route::any('intelligentRadar','ValidateController@intelligentRadar');//全景雷达页面
    Route::any('getRadar','ValidateController@getRadar');//全景雷达*/

    Route::any('feedback', 'OperateController@feedback');//意见反馈统计
    Route::any('getFeedbackSystem', 'OperateController@getFeedbackSystem');//获取反馈
    Route::any('answerFeedback', 'OperateController@answerFeedback');//回复反馈
});