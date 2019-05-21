<?php
return[
    //性别列表
    'sexList' => ['男', '女'],
    //在职状态
    'jobStatusList' => ['在职', '离职'],
    //商户状态
    'merchantStatusList' => ['在用', '关闭'],
    //流程名称
    'processList' => ['全部', '订单审核', '客户资料审核'],
    //流程状态
    'processStatusList' => ['待审核', '审核中', '审核通过', '拒绝', '终止', '关闭'],
    //复贷状态
    'loanStatusList' => ['新增', '复贷'],
    //当前节点
    'currNodeList' => ['业务发起', '客户资料复核'],
    //审核类型
    'typeList' => ['正常', '异常'],
    //处理结果
    'resultList' => ['同意', '不同意'],
    //充值状态
    'rechargeStatusList' => ['成功', '失败'],
    //充值方式
    'rechargeModeList' => ['管理员充值'],
    //资料状态
    'dataStatusList' => ['注册完成', '基础资料完成', '联系人完成', 'OCR完成', '运营商完成', '银行卡绑定完成'],
    //银行卡状态
    'bankStatusList' => ['未认证', '已认证'],
    //账户状态
    'accountStatusList' => ['无', '审核中', '审核通过', '审核失败'],
    //订单状态
    'orderStatusList' => ['待审核', '等待放款', '放款成功', '放款失败', '取消放款'],
    //贷款状态
    'loanStatusList2' => ['待审核', '审核通过', '拒绝', '逾期', '结清'],
    //放款渠道
    'channelList' => ['合利宝', '支付宝'],
    //还款状态
    'repayStatusList' => ['未还款', '已还款'],
    //展期状态
    'extensionStatusList' => ['未申请', '展期成功'],
    //逾期阶段
    'overdueStatusList' => ['', 'M0', 'M1', 'M2'],
    //省城列表
    'provList' => ['上海省', '北京省', '天津省'],
    //城市列表
    'cityList' => ['上海市', '上海市2', '上海市3'],
    //区域列表
    'areaList' => ['长宁区', '普陀区', '闵行区', '长宁区', '普陀区', '闵行区'],
    //婚姻状况
    'marriageList' => ['离异', '已婚', '未婚', '已婚无子女', '已婚有子女'],
    //收入列表
    'incomeList' => ['工资不打卡', '2000以下', '2000~3000', '3000~5000', '5000~8000', '8000~10000', '10000以上'],
    //借款用途
    'purposeList' => ['租房', '生病', '旅游', '结婚', '其他'],
    //房产类型
    'property' => ['商品住宅', '商铺', '办公楼/厂房', '自建房'],
    //风险城市
    'riskList' => ['新疆', '西藏', '香港', '澳门', '台湾', '东莞', '泉州', '漳州', '邵阳', '莆田', '衡阳', '南充', '运城',
        '温州', '宁波', '宁德', '遵义'],
    //运营商类型
    'mnoTypeList' => ['移动', '联通', '中国移动', '中国联通'],
    //反馈类型
    'feedbackTypeList' => ['类型1', '类型2', '类型3', '类型4', '类型5', '类型6', '类型7', '类型8'],
    //回复状态
    'answerStatusList' => ['未回复', '已回复'],
    //发送授权成功短信url
    'messageExamineUrl' => 'https://tg.liangziloan.com/submail/message/message_examine.php',
];