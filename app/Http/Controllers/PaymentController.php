<?php

namespace App\Http\Controllers;

use App\MerchantAlipayRepay;
use App\MerchantAuthorizationPay;
use App\MerchantHelibaoBindcard;
use App\MerchantHelibaoBindpay;
use App\MerchantHelibaoTransfer;
use App\MerchantUsersPre;
use App\MerchantWithdrawApply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PaymentController extends Controller
{
    //合利宝代付
    public static function transfer($amount, $name, $bank_card, $bankid, $id){
        //$amount = 1;
        \Log::LogWirte("=====start=====", 'transfer');
        $P1_bizType =  "Transfer";
        $P2_orderId = date('ymdhis',time()) . $id;
        $P3_customerNumber =  config('helibao.customerNumber');
        $P4_amount =  $amount;
        $P5_bankCode =  $bankid;
        $P6_bankAccountNo = $bank_card;
        $P7_bankAccountName = $name;
        $P8_biz = 'B2C';
        $P9_bankUnionCode = '';
        $P10_feeType = 'PAYER';
        $P11_urgency = 'true';
        $P12_summary = '';
        $notifyUrl = config('helibao.notifyTransferUrl');
        $privatekey = file_get_contents(config('helibao.privateKey'));

        if($P5_bankCode<>"" && $P4_amount<>"" && $P6_bankAccountNo<>"" && $P7_bankAccountName<>"") {
            $source = "&" . $P1_bizType . "&" . $P2_orderId . "&" . $P3_customerNumber . "&" . $P4_amount . "&" .
                $P5_bankCode . "&" . $P6_bankAccountNo . "&" . $P7_bankAccountName . "&" . $P8_biz . "&" .
                $P9_bankUnionCode . "&" . $P10_feeType . "&" . $P11_urgency . "&" . $P12_summary;
            \Log::LogWirte("source:" . $source, 'transfer');

            $rsa = new \CryptRSA();
            $rsa->setHash('md5');
            $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
            $rsa->loadKey($privatekey);
            $sign = base64_encode($rsa->sign($source));
            \Log::LogWirte("sign:" . $sign, 'transfer');

            $Client = new \HttpClient("127.0.0.1");
            $url = config('helibao.transferUrl');//请求的页面地址  request url
            //post的参数
            $params = array('P1_bizType' => $P1_bizType, 'P2_orderId' => $P2_orderId,
                'P3_customerNumber' => $P3_customerNumber, 'P4_amount' => $P4_amount, 'P5_bankCode' => $P5_bankCode,
                'P6_bankAccountNo' => $P6_bankAccountNo, 'P7_bankAccountName' => $P7_bankAccountName,
                'P8_biz' => $P8_biz, 'P9_bankUnionCode' => $P9_bankUnionCode, 'P10_feeType' => $P10_feeType,
                'P11_urgency' => $P11_urgency, 'P12_summary' => $P12_summary, 'notifyUrl' => $notifyUrl, 'sign' => $sign);
            \Log::LogWirte("params:" . json_encode($params), 'transfer');

            $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
            \Log::LogWirte("return:" . $pageContents, 'transfer');
            \Log::LogWirte("=====end=====", 'transfer');

            $pageContents = json_decode($pageContents, true);
            if($pageContents['rt2_retCode'] !== '0000'){
                return false;
            }
            //存储合利宝代付信息
            $result = self::saveTransferData($id, $pageContents['rt5_orderId'], $amount, $pageContents['rt6_serialNumber']);
            if(!$result){
                \Log::LogWirte("代付信息创建失败:" . $id . '|' . $amount, 'transfer');
            }
            return $pageContents['rt5_orderId'];
        }
    }

    //存储合利宝代付信息
    private static function saveTransferData($waid, $orderid, $amount, $serial_number){
        $cond = ['orderid' => $orderid];
        $data = ['waid' => $waid, 'amount' => $amount, 'serial_number' => $serial_number];
        $result = MerchantHelibaoTransfer::firstOrCreate($cond, $data);
        if(!$result->wasRecentlyCreated){
            return false;
        }
        return true;
    }

    //合利宝代付回调
    public static function transferNotify(){
        \Log::LogWirte("=====start=====", 'transferNotify');
        \Log::LogWirte("request:" . json_encode($_REQUEST), 'transferNotify');
        $pageContents = $_REQUEST;
        if($pageContents['rt2_retCode'] === '0000' && $pageContents['rt7_orderStatus'] === 'SUCCESS'){
            echo 'success';
            //调用订单查询
            if(self::transferQuery($pageContents['rt5_orderId'])){
                //更新合利宝代付信息
                if(!self::updateTransferData($pageContents)){
                    \Log::LogWirte("合利宝代付更新出错", 'transferNotify');
                }
                //更新放款状态
                if(!self::updateWithdrawStatus($pageContents['rt5_orderId'])){
                    \Log::LogWirte("放款状态更新出错", 'transferNotify');
                }
                //减少可用额度
                if(!self::decreaseUsableLimit($pageContents['rt5_orderId'])){
                    \Log::LogWirte("可用额度更新出错", 'transferNotify');
                }
            }
        }
    }

    //减少可用额度
    private static function decreaseUsableLimit($orderid){
        $data = MerchantHelibaoTransfer::where(['orderid' => $orderid])
            ->with(['merchantWithdrawApply' => function($query){$query->select('id', 'mchid', 'phone', 'withdraw_amount');}])
            ->select('waid')->first();
        $result = MerchantUsersPre::where(['phone' => $data->merchantWithdrawApply->phone, 'mchid' => $data->merchantWithdrawApply->mchid])
            ->decrement('usable_limit', $data->merchantWithdrawApply->withdraw_amount);
        return $result;
    }


    //更新合利宝代付信息
    private static function updateTransferData($pageContents){
        $where = ['orderid' => $pageContents['rt5_orderId']];
        $updateData = ['order_status' => $pageContents['rt7_orderStatus'], 'notify_type' => $pageContents['rt8_notifyType'],
            'reason' => $pageContents['rt9_reason'], 'created_date' => $pageContents['rt10_createDate'],
            'complete_date' => $pageContents['rt11_completeDate'], 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantHelibaoTransfer::where($where)->update($updateData);
        return $result;
    }

    //更新放款状态
    private static function updateWithdrawStatus($orderid){
        $waid = MerchantHelibaoTransfer::where(['orderid' => $orderid])->value('waid');
        if(!$waid){
            return false;
        }
        $deadline = Redis::get('deadline');
        $deadline = empty($deadline) ? 8 : $deadline;
        $deadline -= 1;
        $updataData = ['order_status' => 2, 'updated_at' => date('Y-m-d H:i:s'), 'loan_at' => date('Y-m-d H:i:s'),
            'repayment_at' => date('Y-m-d', strtotime("+$deadline days")) . ' 23:59:59'];
        $result = MerchantWithdrawApply::where(['id' => $waid])->update($updataData);
        return $result;
    }

    //合利宝代付结果查询
    public static function transferQuery($orderid)
    {
        \Log::LogWirte("=====start=====", 'transferQuery');
        $P1_bizType = "TransferQuery";
        $P3_customerNumber = config('helibao.customerNumber');
        $P2_orderId = $orderid;
        $privatekey = file_get_contents(config('helibao.privateKey'));

        if ($P2_orderId <> "") {
            $source = "&" . $P1_bizType . "&" . $P2_orderId . "&" . $P3_customerNumber;
            \Log::LogWirte("source:" . $source, 'transferQuery');

            $rsa = new \CryptRSA();
            $rsa->setHash('md5');
            $rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
            $rsa->loadKey($privatekey);
            $sign = base64_encode($rsa->sign($source));
            \Log::LogWirte("sign:" . $sign, 'transferQuery');

            $Client = new \HttpClient("127.0.0.1");
            $url = config('helibao.transferUrl');
            //post的参数
            $params = array('P1_bizType' => $P1_bizType, 'P3_customerNumber' => $P3_customerNumber, 'P2_orderId' => $P2_orderId, 'sign' => $sign);
            \Log::LogWirte("params:" . json_encode($params), 'transferQuery');

            $pageContents = $Client->quickPost($url, $params);
            \Log::LogWirte("return:" . $pageContents, 'transferQuery');
            \Log::LogWirte("=====end=====", 'transferQuery');

            $pageContents = json_decode($pageContents, true);
            if($pageContents['rt2_retCode'] === '0000' && $pageContents['rt7_orderStatus'] === 'SUCCESS'){
                return true;
            }
            return false;
        }
    }

    //合利宝鉴权绑卡短信
    public static function bindCardCode($request, $id){
        \Log::LogWirte("原始数据:" . $id . '|' . json_encode($request->toArray()), 'bindCardCode');
        \Log::LogWirte("=====start=====", 'bindCardCode');
        $phone =  $request->phone;
        if($phone <> "") {//判断非空
            $time = date('Ymdhis',time());
            $keyStr = genKey(16);
            $aes = new \CryptAES($keyStr);
            //获取form提交参数
            $P1_bizType = 'AgreementPayBindCardValidateCode';
            $P2_customerNumber = config('helibao.customerNumber');
            $P3_userId = $id;
            $P4_orderId = "p_".$time;
            $P5_timestamp = $time;
            $P6_cardNo = $aes->encrypt($request->bank_card);
            $P7_phone = $aes->encrypt($phone);
            $P8_idCardNo = $aes->encrypt($request->id_number);
            $P9_idCardType = 'IDCARD';
            $P10_payerName = $request->name;

            //构造支付签名串
            $signFormString = "&$P1_bizType&$P2_customerNumber&$P3_userId&$P4_orderId&$P5_timestamp&$P6_cardNo&$P7_phone&$P8_idCardNo&$P9_idCardType&$P10_payerName";
            \Log::LogWirte("签名串:" . $signFormString, 'bindCardCode');

            $rsa = new \Rsa();
            $sign = $rsa->genSign($signFormString, config('helibao.rsaPem'));
            \Log::LogWirte("sign:" . $sign, 'bindCardCode');
            $encryptionKey = $rsa->rsaEnc($keyStr, config('helibao.enPem'));

            $Client = new \HttpClient("127.0.0.1");
            $url = config('helibao.quickUrl');//请求下单地址  request url

            //post的参数
            $params = array('P1_bizType' => $P1_bizType, 'P2_customerNumber' => $P2_customerNumber,
                'P3_userId' => $P3_userId, 'P4_orderId' => $P4_orderId, 'P5_timestamp' => $P5_timestamp,
                'P6_cardNo' => $P6_cardNo, 'P7_phone' => $P7_phone, 'P8_idCardNo' => $P8_idCardNo,
                'P9_idCardType' => $P9_idCardType, 'P10_payerName' => $P10_payerName, 'signatureType' => 'MD5WITHRSA',
                'sign' => $sign, 'encryptionKey' => $encryptionKey);

            $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
            \Log::LogWirte("return:" . $pageContents, 'bindCardCode');
            \Log::LogWirte("=====end=====", 'bindCardCode');

            $pageContents = json_decode($pageContents, true);
            if($pageContents['rt2_retCode'] !== '0000'){
                return false;
            }
            //存储订单号
            $result = Redis::set('bindCardCode_' . $id, $P4_orderId);
            Redis::expire('bindCardCode_' . $id, 3600);
            if(!$result){
                \Log::LogWirte("redis存储失败:" . $id . '|' . $P4_orderId, 'bindCardCode');
            }
            return true;
        }
    }

    //合利宝鉴权绑卡
    public static function bindCard($request, $id){
        \Log::LogWirte("原始数据:" . $id . '|' . json_encode($request->toArray()), 'bindCard');
        \Log::LogWirte("=====start=====", 'bindCard');
        $phone =  $request->phone;
        if($phone <> "") {//判断非空
            $orderId = Redis::get('bindCardCode_' . $id);
            if(!$orderId){
                return false;
            }
            $keyStr = genKey(16);
            $aes = new \CryptAES($keyStr);
            //获取form提交参数
            $P1_bizType = 'QuickPayBindCard';
            $P2_customerNumber = config('helibao.customerNumber');
            $P3_userId = $id;
            $P4_orderId = $orderId;
            $P5_timestamp = date('Ymdhis',time());
            $P6_payerName = $request->name;
            $P7_idCardType = 'IDCARD';
            $P8_idCardNo = $aes->encrypt($request->id_number);
            $P9_cardNo = $aes->encrypt($request->bank_card);
            $P10_year = $aes->encrypt('');
            $P11_month = $aes->encrypt('');
            $P12_cvv2 = $aes->encrypt('');
            $P13_phone = $aes->encrypt($phone);
            $P14_validateCode = $aes->encrypt($request->third_code);
            $P15_isEncrypt = 'TRUE';

            //构造支付签名串
            $signFormString = "&$P1_bizType&$P2_customerNumber&$P3_userId&$P4_orderId&$P5_timestamp&$P6_payerName&$P7_idCardType&$P8_idCardNo&$P9_cardNo&$P10_year&$P11_month&$P12_cvv2&$P13_phone&$P14_validateCode";
            \Log::LogWirte("签名串:" . $signFormString, 'bindCard');

            $rsa = new \Rsa();
            $sign = $rsa->genSign($signFormString, config('helibao.rsaPem'));
            \Log::LogWirte("sign:" . $sign, 'bindCard');
            $encryptionKey = $rsa->rsaEnc($keyStr, config('helibao.enPem'));

            $Client = new \HttpClient("127.0.0.1");
            $url = config('helibao.quickUrl');//请求下单地址  request url

            //post的参数
            $params = array('P1_bizType' => $P1_bizType, 'P2_customerNumber' => $P2_customerNumber,
                'P3_userId' => $P3_userId, 'P4_orderId' => $P4_orderId, 'P5_timestamp' => $P5_timestamp,
                'P6_payerName' => $P6_payerName, 'P7_idCardType' => $P7_idCardType, 'P8_idCardNo' => $P8_idCardNo,
                'P9_cardNo' => $P9_cardNo, 'P10_year' => $P10_year, 'P11_month' => $P11_month, 'P12_cvv2' => $P12_cvv2,
                'P13_phone' => $P13_phone, 'P14_validateCode' => $P14_validateCode, 'P15_isEncrypt' => $P15_isEncrypt,
                'signatureType' => 'MD5WITHRSA', 'sign' => $sign, 'encryptionKey' => $encryptionKey);

            $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
            \Log::LogWirte("return:" . $pageContents, 'bindCard');
            \Log::LogWirte("=====end=====", 'bindCard');

            $pageContents = json_decode($pageContents, true);
            if($pageContents['rt2_retCode'] !== '0000'){
                return false;
            }
            $result = self::saveBindCardResult($pageContents, $request->bank_card, $phone);
            if(!$result){
                \Log::LogWirte("数据库录入失败", 'bindCard');
            }
            return true;
        }
    }

    //存储合利宝绑卡结果
    private static function saveBindCardResult($request, $bank_card, $phone){
        if($request['rt7_bindStatus'] == 'SUCCESS' && !MerchantHelibaoBindcard::where(['userid' => $request['rt5_userId'], 'master' => 1])->select('id')->first()){
            $master = 1;
        }else{
            $master = 0;
        }
        $insertData = ['userid' => $request['rt5_userId'], 'orderid' => $request['rt6_orderId'], 'bank_card' => $bank_card,
            'bankid' => $request['rt8_bankId'], 'bind_status' => $request['rt7_bindStatus'], 'bindid' => $request['rt10_bindId'],
            'serial_number' => $request['rt11_serialNumber'], 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            'phone' => $phone, 'status' => '0', 'master' => $master];
        $result = MerchantHelibaoBindcard::insert($insertData);
        return $result;
    }

    //合利宝绑卡支付短信
    /*public static function bindPayCard(){
        \Log::LogWirte("=====start=====", 'bindPayCard');
        $phone =  '';
        if($phone <> "") {//判断非空
            $keyStr = genKey(16);
            $aes = new \CryptAES($keyStr);
            //获取form提交参数
            $P1_bizType = 'QuickPayBindPayValidateCode';
            $P2_customerNumber = config('helibao.customerNumber');
            $P3_bindId = '5fa1c48c74364854864f6bfd';
            $P4_userId = '1';
            $P5_orderId = "p_".date('Ymdhis',time());
            $P6_timestamp = date('Ymdhis',time());
            $P7_currency = 'CNY';
            $P8_orderAmount = '0.01';
            $P9_phone = $aes->encrypt($phone);

            //构造支付签名串
            $signFormString = "&$P1_bizType&$P2_customerNumber&$P3_bindId&$P4_userId&$P5_orderId&$P6_timestamp&$P7_currency&$P8_orderAmount&$P9_phone";
            \Log::LogWirte("签名串:" . $signFormString, 'bindPayCard');

            $rsa = new \Rsa();
            $sign = $rsa->genSign($signFormString, config('helibao.rsaPem'));
            \Log::LogWirte("sign:" . $sign, 'bindPayCard');
            $encryptionKey = $rsa->rsaEnc($keyStr, config('helibao.enPem'));

            $Client = new \HttpClient("127.0.0.1");
            $url = config('helibao.quickUrl');//请求下单地址  request url

            //post的参数
            $params = array('P1_bizType' => $P1_bizType, 'P2_customerNumber' => $P2_customerNumber,
                'P3_bindId' => $P3_bindId, 'P4_userId' => $P4_userId, 'P5_orderId' => $P5_orderId,
                'P6_timestamp' => $P6_timestamp, 'P7_currency' => $P7_currency, 'P8_orderAmount' => $P8_orderAmount,
                'P9_phone' => $P9_phone, 'signatureType' => 'MD5WITHRSA', 'sign' => $sign,
                'encryptionKey' => $encryptionKey);

            $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
            \Log::LogWirte("return:" . $pageContents, 'bindPayCard');
            \Log::LogWirte("=====end=====", 'bindPayCard');
        }
    }*/

    //合利宝绑卡支付
    public static function bindPay($userid, $bindId, $amount, $ip, $terminalId, $id, $type){
        \Log::LogWirte("=====start=====", 'bindPay');
        if(!empty($userid) && !empty($bindId) && !empty($amount) && !empty($ip) && !empty($terminalId)) {//判断非空
            //$amount = 1;
            $keyStr = genKey(16);
            $aes = new \CryptAES($keyStr);
            $time = date('Ymdhis',time());
            //获取form提交参数
            $P1_bizType = 'QuickPayBindPay';
            $P2_customerNumber = config('helibao.customerNumber');
            $P3_bindId = $bindId;
            $P4_userId = $userid;
            $P5_orderId = "p_".$time;
            $P6_timestamp = $time;
            $P7_currency = 'CNY';
            $P8_orderAmount = $amount;
            $P9_goodsName = '还款';
            $P10_goodsDesc = '还款';
            $P11_terminalType = 'IMEI';
            $P12_terminalId = $terminalId;//终端唯一标识，如手机序列号
            $P13_orderIp = $ip;//用户支付时使用的网络终端IP
            $P14_period = '1';
            $P15_periodUnit = 'hour';
            //回调
            if($type == 0){
                $P16_serverCallbackUrl = config('helibao.serverCallbackUrl');
            }elseif($type == 1){
                $P16_serverCallbackUrl = config('helibao.serverCallbackExtensionUrl');
            }elseif($type == 2){
                $P16_serverCallbackUrl = config('helibao.serverCallbackCreditUrl');
            }else{
                return false;
            }


            //构造支付签名串
            $signFormString = "&$P1_bizType&$P2_customerNumber&$P3_bindId&$P4_userId&$P5_orderId&$P6_timestamp&$P7_currency&$P8_orderAmount&$P9_goodsName&$P10_goodsDesc&$P11_terminalType&$P12_terminalId&$P13_orderIp&$P14_period&$P15_periodUnit&$P16_serverCallbackUrl";
            \Log::LogWirte("签名串:" . $signFormString, 'bindPay');

            $rsa = new \Rsa();
            $sign = $rsa->genSign($signFormString, config('helibao.rsaPem'));
            \Log::LogWirte("sign:" . $sign, 'bindPay');
            $encryptionKey = $rsa->rsaEnc($keyStr, config('helibao.enPem'));

            $Client = new \HttpClient("127.0.0.1");
            $url = config('helibao.quickUrl');//请求下单地址  request url

            //post的参数
            $params = array('P1_bizType' => $P1_bizType, 'P2_customerNumber' => $P2_customerNumber,
                'P3_bindId' => $P3_bindId, 'P4_userId' => $P4_userId, 'P5_orderId' => $P5_orderId,
                'P6_timestamp' => $P6_timestamp, 'P7_currency' => $P7_currency, 'P8_orderAmount' => $P8_orderAmount,
                'P9_goodsName' => $P9_goodsName, 'P10_goodsDesc' => $P10_goodsDesc, 'P11_terminalType' => $P11_terminalType,
                'P12_terminalId' => $P12_terminalId, 'P13_orderIp' => $P13_orderIp, 'P14_period' => $P14_period,
                'P15_periodUnit' => $P15_periodUnit, 'P16_serverCallbackUrl' => $P16_serverCallbackUrl,
                'signatureType' => 'MD5WITHRSA', 'sign' => $sign, 'encryptionKey' => $encryptionKey);

            $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
            \Log::LogWirte("return:" . $pageContents, 'bindPay');
            \Log::LogWirte("=====end=====", 'bindPay');

            $pageContents = json_decode($pageContents, true);

            if($pageContents['rt2_retCode'] !== '0000'){
                return false;
            }
            $result = self::saveBindPayResult($pageContents, $id, $type);
            if(!$result){
                \Log::LogWirte("数据库录入失败", 'bindPay');
            }
            return true;
        }
    }

    //存储合利宝绑卡支付结果
    private static function saveBindPayResult($request, $id, $type){
        $insertData = ['userid' => $request['rt14_userId'], 'orderid' => $request['rt5_orderId'], 'complete_date' => $request['rt7_completeDate'],
            'order_amount' => $request['rt8_orderAmount'], 'order_status' => $request['rt9_orderStatus'], 'bindid' => $request['rt10_bindId'],
            'serial_number' => $request['rt6_serialNumber'], 'bankid' => $request['rt11_bankId'], 'card_type' => $request['rt12_onlineCardType'],
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'waid' => $id, 'type' => $type];
        $result = MerchantHelibaoBindpay::insert($insertData);
        return $result;
    }

    //合利宝异步通知接口
    public static function confirmPay(){
        \Log::LogWirte("=====start=====", 'confirmPay');
        \Log::LogWirte("request:" . json_encode($_REQUEST), 'confirmPay');
        $pageContents = $_REQUEST;
        if($pageContents['rt2_retCode'] === '0000' && $pageContents['rt9_orderStatus'] === 'SUCCESS'){
            //调用订单查询
            $return = self::payQuery($pageContents['rt5_orderId']);
            if($return){
                $data = self::selectOrderAmount($pageContents['rt5_orderId'], 0);
                if(!$data){
                    \Log::LogWirte("查询不到订单号" . $pageContents['rt5_orderId'], 'confirmPay');
                    exit;
                }
                if($data->type){
                    \Log::LogWirte("订单类型错误" . $pageContents['rt5_orderId'], 'confirmPay');
                    exit;
                }
                if(!$data['merchantWithdrawApply']){
                    \Log::LogWirte("查询不到借款记录" . $pageContents['rt5_orderId'], 'confirmPay');
                    exit;
                }
                if($data->merchantWithdrawApply->repay_status){
                    \Log::LogWirte("重复还款" . $pageContents['rt5_orderId'], 'confirmPay');
                    exit;
                }
                $amount = $data['order_amount'];
                if($amount != $pageContents['rt8_orderAmount']){
                //if(1 != $pageContents['rt8_orderAmount']){
                    \Log::LogWirte("订单金额|实际金额,不符" . $pageContents['rt8_orderAmount'] . '|' . $amount, 'confirmPay');
                    exit;
                }
                //增加可用额度
                if(!self::increaseUsableLimit($data['waid'])){
                    \Log::LogWirte("增加可用额度出错", 'confirmPay');
                }
                //自动提额降费
                if(Redis::get('autoRaiseAndReduceSwitch')) {
                    \Log::LogWirte("自动提额降费开始", 'confirmPay');
                    if (!self::autoRaiseAndReduce($data['waid'])) {
                        \Log::LogWirte("自动提额降费出错", 'confirmPay');
                    }
                    \Log::LogWirte("自动提额降费结束", 'confirmPay');
                }
                //更新还款状态
                if(!self::updateRepayStatus($data['waid'])){
                    \Log::LogWirte("还款状态更新出错", 'confirmPay');
                }
                echo 'success';
            }
        }
    }

    //合利宝异步通知展期接口
    public static function confirmPayExtension(){
        \Log::LogWirte("=====start=====", 'confirmPayExtension');
        \Log::LogWirte("request:" . json_encode($_REQUEST), 'confirmPayExtension');
        $pageContents = $_REQUEST;
        if($pageContents['rt2_retCode'] === '0000' && $pageContents['rt9_orderStatus'] === 'SUCCESS'){
            //调用订单查询
            $return = self::payQuery($pageContents['rt5_orderId']);
            if($return){
                $data = self::selectOrderAmount($pageContents['rt5_orderId'], 0);
                if(!$data){
                    \Log::LogWirte("查询不到订单号" . $pageContents['rt5_orderId'], 'confirmPayExtension');
                    exit;
                }
                \Log::LogWirte("data:" .json_encode($data), 'confirmPayExtension');
                if(!$data->type){
                    \Log::LogWirte("订单类型错误" . $pageContents['rt5_orderId'], 'confirmPayExtension');
                    exit;
                }
                if($data->used){
                    \Log::LogWirte("订单已使用" . $pageContents['rt5_orderId'], 'confirmPayExtension');
                    exit;
                }
                if(!$data['merchantWithdrawApply']){
                    \Log::LogWirte("查询不到借款记录" . $pageContents['rt5_orderId'], 'confirmPayExtension');
                    exit;
                }
                $amount = $data['order_amount'];
                if($amount != $pageContents['rt8_orderAmount']){
                //if(1 != $pageContents['rt8_orderAmount']){
                    \Log::LogWirte("订单金额|实际金额,不符" . $pageContents['rt8_orderAmount'] . '|' . $amount, 'confirmPayExtension');
                    exit;
                }
                \Log::LogWirte("更新展期状态开始", 'confirmPayExtension');
                //更新展期状态
                if(!self::updateExtensionStatus($data['waid'], $amount)){
                    \Log::LogWirte("更新展期状态出错", 'confirmPayExtension');
                }
                \Log::LogWirte("更新展期状态结束", 'confirmPayExtension');
                //更新订单状态
                if(!self::updateOrderUsed($data->id)){
                    \Log::LogWirte("更新订单状态出错", 'confirmPayExtension');
                }
                echo 'success';
            }
        }
    }

    //合利宝异步通知信用接口
    public static function confirmPayCredit(){
        \Log::LogWirte("=====start=====", 'confirmPayCredit');
        \Log::LogWirte("request:" . json_encode($_REQUEST), 'confirmPayCredit');
        $pageContents = $_REQUEST;
        if($pageContents['rt2_retCode'] === '0000' && $pageContents['rt9_orderStatus'] === 'SUCCESS'){
            //调用订单查询
            $return = self::payQuery($pageContents['rt5_orderId']);
            if($return){
                $data = self::selectOrderAmount($pageContents['rt5_orderId'], 1);
                if(!$data){
                    \Log::LogWirte("查询不到订单号" . $pageContents['rt5_orderId'], 'confirmPayCredit');
                    exit;
                }
                \Log::LogWirte("data:" .json_encode($data), 'confirmPayCredit');
                if(!$data->type){
                    \Log::LogWirte("订单类型错误" . $pageContents['rt5_orderId'], 'confirmPayCredit');
                    exit;
                }
                if($data->used){
                    \Log::LogWirte("订单已使用" . $pageContents['rt5_orderId'], 'confirmPayCredit');
                    exit;
                }
                if(!$data['merchantAuthorizationPay']){
                    \Log::LogWirte("查询不到借款记录" . $pageContents['rt5_orderId'], 'confirmPayCredit');
                    exit;
                }
                $amount = $data['order_amount'];
                if($amount != $pageContents['rt8_orderAmount']){
                    //if(1 != $pageContents['rt8_orderAmount']){
                    \Log::LogWirte("订单金额|实际金额,不符" . $pageContents['rt8_orderAmount'] . '|' . $amount, 'confirmPayCredit');
                    exit;
                }
                \Log::LogWirte("更新信用状态开始", 'confirmPayCredit');
                //更新展期状态
                if(!self::updateCreditStatus($data['waid'], $pageContents['rt5_orderId'], $pageContents['rt9_orderStatus'])){
                    \Log::LogWirte("更新信用状态出错", 'confirmPayCredit');
                }
                \Log::LogWirte("更新信用状态结束", 'confirmPayCredit');
                //更新订单状态
                if(!self::updateOrderUsed($data->id)){
                    \Log::LogWirte("更新订单状态出错", 'confirmPayCredit');
                }
                echo 'success';
            }
        }
    }

    /*public static function test(){
        $string = '{"rt10_bindId":"df3251607816402ca7a1032e8ac7adb1","sign":"NW0Xg9heDA5bqYQ6xSfe4vynh6XA4HGNikUa1+iucDI0icDijLCzudi8Uk8h4D1yo+8hxxnjzVKbbWgcqBgmdPonXyJL9bgNvmg1u0uv4PkPoe0buovSnzrVljeu8qOnIXdWeEMWhCsTvWtKR1sgpF7QsuPV0gRxPQ7XO7dRnTr4h87EBV0PaNKJqoKL61vc3giODGBJ0blHhjKn967Vbrowb+3OQ4ygIqiQB1xCVnX5cMtBru2P1VtQP8SJTV1euph+2\/JBvK2YCQ3nlRC6tFUKY7itNWyHaKHBciz61FQSbbKqh58DgiJEDUo4FDi0ds3TfBvDfZLJ5CjK\/dFFmw==","rt1_bizType":"QuickPayConfirmPay","rt9_orderStatus":"SUCCESS","rt6_serialNumber":"QUICKPAY190416093744LZCZ","rt14_userId":"23","rt2_retCode":"0000","rt12_onlineCardType":"DEBIT","rt11_bankId":"CCB","rt13_cardAfterFour":"7220","rt5_orderId":"p_20190416093744","rt4_customerNumber":"C1800372744","rt8_orderAmount":"1.00","rt3_retMsg":"\u6210\u529f","rt7_completeDate":"2019-04-16 09:37:46"}';
        $string = json_decode($string, 1);
        $url = 'https://cv.liangziloan.com/confirmPayExtension';
        \AccessHelp::doPost($url, $string);
    }*/

    //查询订单金额
    private static function selectOrderAmount($orderid, $type){
        if($type){
            $data = MerchantHelibaoBindpay::where(['orderid' => $orderid])->select('id', 'userid', 'order_amount', 'waid', 'type', 'used')
                ->with(['merchantAuthorizationPay' => function($query){$query->select('id', 'status');}])
                ->first();
        }else{
            $data = MerchantHelibaoBindpay::where(['orderid' => $orderid])->select('id', 'userid', 'order_amount', 'waid', 'type', 'used')
                ->with(['merchantWithdrawApply' => function($query){$query->select('id', 'repay_status');}])
                ->first();
        }
        return $data;
    }

    //增加可用额度
    private static function increaseUsableLimit($waid){
        $where = ['id' => $waid, 'repay_status' => 0];
        $data = MerchantWithdrawApply::where($where)->select('withdraw_amount', 'phone', 'mchid')->orderBy('id', 'desc')->first();
        if(!$data){
            return false;
        }
        $result = MerchantUsersPre::where(['phone' => $data->phone, 'mchid' => $data->mchid])->increment('usable_limit', $data->withdraw_amount);
        return $result;
    }

    //自动提额降费
    private static function autoRaiseAndReduce($waid){
        $where = ['id' => $waid, 'repay_status' => 0];
        $data = MerchantWithdrawApply::where($where)->select('repayment_at', 'actual_repayment_at', 'phone', 'mchid')->orderBy('id', 'desc')->first();
        if(!$data){
            return false;
        }
        if($data->repayment_at < $data->actual_repayment_at){
            $limit_amount = '-200';
            $service_fee = '0.1';
        }else{
            $limit_amount = '100';
            $service_fee = '-0.1';
        }
        $info = MerchantUsersPre::where(['phone' => $data->phone, 'mchid' => $data->mchid])->select('credit_limit', 'usable_limit')->first();
        $credit_limit = $info->credit_limit + $limit_amount;
        $usable_limit = $info->usable_limit + $limit_amount;
        if($credit_limit > 2500){
            $credit_limit = 2500;
        }elseif($credit_limit < 500){
            $credit_limit = 500;
        }
        if($usable_limit > 2500){
            $usable_limit = 2500;
        }elseif($usable_limit < 500){
            $usable_limit = 500;
        }
        $updateData = ['credit_limit' => $credit_limit, 'usable_limit' => $usable_limit, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantUsersPre::where(['phone' => $data->phone, 'mchid' => $data->mchid])->update($updateData);
        $str = $data->phone . '_' . $data->mchid;
        $service_fee = Redis::hget('autoRaiseAndReduce', $str) + $service_fee;
        Redis::hset('autoRaiseAndReduce', $str, $service_fee);
        return true;
    }

    //更新还款状态
    private static function updateRepayStatus($waid){
        $where = ['id' => $waid, 'repay_status' => 0];
        $updateData = ['updated_at' => date('Y-m-d H:i:s'), 'loan_status' => 4, 'repay_status' => 1];
        $result = MerchantWithdrawApply::where($where)->update($updateData);
        return $result;
    }

    //更新展期状态
    private static function updateExtensionStatus($waid, $amount){
        $where = ['id' => $waid, 'repay_status' => 0];
        $data = MerchantWithdrawApply::where($where)->select('extension', 'extension_amount', 'repayment_at', 'phone', 'mchid')->first();
        if(!$data){
            return false;
        }
        $extension = $data->extension + 1;
        $extension_amount = empty($data->extension_amount) ? 0 : $data->extension_amount;
        $extension_amount = $extension_amount + $amount;
        $extension_day = Redis::get('extension');
        $extension_day = empty($extension_day) ? 8 : $extension_day;
        $repayment_at = date('Y-m-d H:i:s', strtotime("+$extension_day days", strtotime($data->repayment_at)));
        $updateData = ['updated_at' => date('Y-m-d H:i:s'), 'extension' => $extension, 'extension_amount' => $extension_amount,
            'repayment_at' => $repayment_at, 'extension_status' => 1];
        $result = MerchantWithdrawApply::where($where)->update($updateData);
        //更新展期统计
        MerchantUsersPre::where(['phone' => $data->phone, 'mchid' => $data->mchid])->increment('extension_times');
        return $result;
    }

    //更新信用状态
    private static function updateCreditStatus($waid, $trade_no, $trade_status){
        $where = ['id' => $waid];
        $updateData = ['trade_no' => $trade_no, 'trade_status' => $trade_status, 'status' => 1, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantAuthorizationPay::where($where)->update($updateData);
        return $result;
    }

    //更新订单状态
    private static function updateOrderUsed($id){
        $result = MerchantHelibaoBindpay::where(['id' => $id, 'used' => 0])->update(['used' => 1]);
        return $result;
    }

    //合利宝订单查询
    private static function payQuery($P2_orderId){
        \Log::LogWirte("=====start=====", 'payQuery');
        if(!empty($P2_orderId)) {//判断非空
            //获取form提交参数
            $P1_bizType = 'QuickPayQuery';
            $P3_customerNumber = config('helibao.customerNumber');

            //构造支付签名串
            $signFormString = "&$P1_bizType&$P2_orderId&$P3_customerNumber";
            \Log::LogWirte("签名串:" . $signFormString, 'payQuery');

            $rsa = new \Rsa();
            $sign = $rsa->genSign($signFormString, config('helibao.rsaPem'));
            \Log::LogWirte("sign:" . $sign, 'payQuery');

            $Client = new \HttpClient("127.0.0.1");
            $url = config('helibao.quickUrl');//请求下单地址  request url

            //post的参数
            $params = array('P1_bizType' => $P1_bizType, 'P2_orderId' => $P2_orderId,
                'P3_customerNumber' => $P3_customerNumber, 'signatureType' => 'MD5WITHRSA', 'sign' => $sign);

            $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
            \Log::LogWirte("return:" . $pageContents, 'payQuery');
            \Log::LogWirte("=====end=====", 'payQuery');
            $pageContents = json_decode($pageContents, true);
            if($pageContents['rt2_retCode'] === '0000' && $pageContents['rt9_orderStatus'] === 'SUCCESS'){
                return true;
            }else{
                return false;
            }
        }
    }

    //合利宝银行卡解绑
    public static function unbindCard(){
        \Log::LogWirte("=====start=====", 'unbindCard');
        //获取form提交参数
        $P1_bizType = 'BankCardUnbind';
        $P2_customerNumber = config('helibao.customerNumber');
        $P3_userId = '23';
        $P4_bindId = 'df3251607816402ca7a1032e8ac7adb1';
        $P5_orderId = "p_".date('Ymdhis',time());
        $P6_timestamp = date('Ymdhis',time());

        //构造支付签名串
        $signFormString = "&$P1_bizType&$P2_customerNumber&$P3_userId&$P4_bindId&$P5_orderId&$P6_timestamp";
        \Log::LogWirte("签名串:" . $signFormString, 'unbindCard');

        $rsa = new \Rsa();
        $sign = $rsa->genSign($signFormString, config('helibao.rsaPem'));
        \Log::LogWirte("sign:" . $sign, 'unbindCard');

        $Client = new \HttpClient("127.0.0.1");
        $url = config('helibao.quickUrl');//请求下单地址  request url

        //post的参数
        $params = array('P1_bizType' => $P1_bizType, 'P2_customerNumber' => $P2_customerNumber, 'P3_userId' => $P3_userId,
            'P4_bindId' => $P4_bindId, 'P5_orderId' => $P5_orderId, 'P6_timestamp' => $P6_timestamp,
            'signatureType' => 'MD5WITHRSA', 'sign' => $sign);

        $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
        \Log::LogWirte("return:" . $pageContents, 'unbindCard');
        \Log::LogWirte("=====end=====", 'unbindCard');
    }

    /*//合利宝用户绑定银行卡信息查询
    public static function cardBindList(){
        \Log::LogWirte("=====start=====", 'cardBindList');
        //获取form提交参数
        $P1_bizType = 'BankCardbindList';
        $P2_customerNumber = config('helibao.customerNumber');
        $P3_userId = '1';
        $P4_bindId = '5fa1c48c74364854864f6bfd';
        $P5_timestamp = date('Ymdhis',time());

        //构造支付签名串
        $signFormString = "&$P1_bizType&$P2_customerNumber&$P3_userId&$P4_bindId&$P5_timestamp";
        \Log::LogWirte("签名串:" . $signFormString, 'cardBindList');

        $rsa = new \Rsa();
        $sign = $rsa->genSign($signFormString, config('helibao.rsaPem'));
        \Log::LogWirte("sign:" . $sign, 'cardBindList');

        $Client = new \HttpClient("127.0.0.1");
        $url = config('helibao.quickUrl');//请求下单地址  request url

        //post的参数
        $params = array('P1_bizType' => $P1_bizType, 'P2_customerNumber' => $P2_customerNumber, 'P3_userId' => $P3_userId,
            'P4_bindId' => $P4_bindId, 'P5_timestamp' => $P5_timestamp, 'signatureType' => 'MD5WITHRSA', 'sign' => $sign);

        $pageContents = $Client->quickPost($url, $params);  //发送请求 send request
        \Log::LogWirte("return:" . $pageContents, 'cardBindList');
        \Log::LogWirte("=====end=====", 'cardBindList');
    }*/

    //支付宝下单
    public static function aliPayApplyOrder($orderid, $amount, $type){
        \Log::LogWirte('原始数据:' . $orderid . '|' . $amount, 'aliPayApplyOrder');
        //$amount = "0.01";
        if($type == 1){
            $subject = '线上支付';
            $notifyUrl = config('alipay.notifyUrlRepay');
        }elseif($type == 2){
            $subject = '展期费支付';
            $notifyUrl = config('alipay.notifyUrlExtension');
        }elseif($type == 3){
            $subject = '审查费支付';
            $notifyUrl = config('alipay.notifyUrl');
        }else{
            return false;
        }
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = config('alipay.appid');
        $aop->rsaPrivateKey = config('alipay.privateKey');
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = config('alipay.publicKey');
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        /*$bizcontent = "{\"body\":\"我是测试数据\","
            . "\"subject\": \"App支付测试\","
            . "\"out_trade_no\": \"20190321test01\","
            . "\"timeout_express\": \"30m\","
            . "\"total_amount\": \"0.01\","
            . "\"product_code\":\"QUICK_MSECURITY_PAY\""
            . "}";*/
        $bizcontent = [
            "body" => "App支付",
            "subject" => $subject,
            "out_trade_no" => $orderid,
            "timeout_express" => "30m",
            "total_amount" => $amount,
            "product_code" => "QUICK_MSECURITY_PAY",
        ];
        $bizcontent = json_encode($bizcontent);
        $request->setNotifyUrl($notifyUrl);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        \Log::LogWirte('返回数据:' . $response, 'aliPayApplyOrder');
        return $response;
        //return response()->json(['code' => 0, 'data' => $response]);
        //$result = $aop->pageExecute($request);
        //var_dump($result);
        //$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        //$resultCode = $result->$responseNode->code;
        /*if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }*/
        //\Log::LogWirte('返回数据:' . json_encode($response), 'aliPayApplyOrder');
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        //echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        /*$result = file_get_contents('https://openapi.alipay.com/gateway.do?' . htmlspecialchars($response));
        echo $result;
        \Log::LogWirte('返回数据:' . $result, 'aliPayApplyOrder');*/
    }

    //支付宝回调
    public static function aliPayNotify(){
        \Log::LogWirte('原始数据:' . json_encode($_POST), 'aliPayNotify');
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = config('alipay.publicKey');
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if(!$flag){
            \Log::LogWirte('验证失败', 'aliPayNotify');
            exit;
        }
        if($_POST['trade_status'] != 'TRADE_SUCCESS'){
            \Log::LogWirte('订单状态错误', 'aliPayNotify');
            exit;
        }
        //获取订单金额
        $amount = self::getAliPayOrderAmount($_POST['out_trade_no']);
        if(!$amount || $amount != $_POST['total_amount']){
            \Log::LogWirte('订单金额错误', 'aliPayNotify');
            exit;
        }
        //支付宝订单查询
        if(!self::aliPayTradeQuery($_POST['out_trade_no'], $_POST['trade_no'], $amount)){
            \Log::LogWirte('订单查询错误', 'aliPayNotify');
            exit;
        }
        //存储支付宝支付结果
        if(!self::saveAliPayTradeResult($_POST['out_trade_no'], $_POST['trade_no'], $_POST['trade_status'])){
            \Log::LogWirte('存储数据失败', 'aliPayNotify');
            exit;
        }
        \Log::LogWirte('订单完成', 'aliPayNotify');
        echo 'success';
    }

    //支付宝还款回调
    public static function aliPayNotifyRepay(){
        \Log::LogWirte('原始数据:' . json_encode($_POST), 'aliPayNotifyRepay');
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = config('alipay.publicKey');
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if(!$flag){
            \Log::LogWirte('验证失败', 'aliPayNotifyRepay');
            exit;
        }
        if($_POST['trade_status'] != 'TRADE_SUCCESS'){
            \Log::LogWirte('订单状态错误', 'aliPayNotifyRepay');
            exit;
        }
        //获取订单金额
        $data = self::getAliPayRepayAmount($_POST['out_trade_no']);
        if(!$data || $data['amount'] != $_POST['total_amount']){
            \Log::LogWirte('订单金额错误', 'aliPayNotifyRepay');
            exit;
        }
        //支付宝订单查询
        if(!self::aliPayTradeQuery($_POST['out_trade_no'], $_POST['trade_no'], $data['amount'])){
            \Log::LogWirte('订单查询错误', 'aliPayNotifyRepay');
            exit;
        }
        //存储支付宝支付结果
        if(!self::saveAliPayRepayResult($_POST['out_trade_no'], $_POST['trade_no'], $_POST['trade_status'])){
            \Log::LogWirte('存储数据失败', 'aliPayNotifyRepay');
            exit;
        }
        //增加可用额度
        if(!self::increaseUsableLimit($data['waid'])){
            \Log::LogWirte("增加可用额度出错", 'aliPayNotifyRepay');
        }
        //自动提额降费
        if(Redis::get('autoRaiseAndReduceSwitch')) {
            \Log::LogWirte("自动提额降费开始", 'aliPayNotifyRepay');
            if (!self::autoRaiseAndReduce($data['waid'])) {
                \Log::LogWirte("自动提额降费出错", 'aliPayNotifyRepay');
            }
            \Log::LogWirte("自动提额降费结束", 'aliPayNotifyRepay');
        }
        //更新还款状态
        if(!self::updateRepayStatus($data['waid'])){
            \Log::LogWirte("还款状态更新出错", 'aliPayNotifyRepay');
        }
        \Log::LogWirte('订单完成', 'aliPayNotifyRepay');
        echo 'success';
    }

    //支付宝展期回调
    public static function aliPayNotifyExtension(){
        \Log::LogWirte('原始数据:' . json_encode($_POST), 'aliPayNotifyExtension');
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = config('alipay.publicKey');
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        if(!$flag){
            \Log::LogWirte('验证失败', 'aliPayNotifyExtension');
            exit;
        }
        if($_POST['trade_status'] != 'TRADE_SUCCESS'){
            \Log::LogWirte('订单状态错误', 'aliPayNotifyExtension');
            exit;
        }
        //获取订单金额
        $data = self::getAliPayRepayAmount($_POST['out_trade_no']);
        if(!$data || $data['amount'] != $_POST['total_amount']){
            \Log::LogWirte('订单金额错误', 'aliPayNotifyExtension');
            exit;
        }
        //支付宝订单查询
        if(!self::aliPayTradeQuery($_POST['out_trade_no'], $_POST['trade_no'], $data['amount'])){
            \Log::LogWirte('订单查询错误', 'aliPayNotifyExtension');
            exit;
        }
        //存储支付宝支付结果
        if(!self::saveAliPayRepayResult($_POST['out_trade_no'], $_POST['trade_no'], $_POST['trade_status'])){
            \Log::LogWirte('存储数据失败', 'aliPayNotifyExtension');
            exit;
        }
        //更新展期状态
        if(!self::updateExtensionStatus($data['waid'], $data['amount'])){
            \Log::LogWirte("更新展期状态出错", 'aliPayNotifyExtension');
        }
        \Log::LogWirte("更新展期状态结束", 'aliPayNotifyExtension');
        \Log::LogWirte('订单完成', 'aliPayNotifyExtension');
        echo 'success';
    }

    //获取订单金额
    private static function getAliPayOrderAmount($orderid){
        $amount = MerchantAuthorizationPay::where(['orderid' => $orderid])->value('order_amount');
        return $amount;
    }

    //获取支付宝订单金额
    private static function getAliPayRepayAmount($orderid){
        $data = MerchantAlipayRepay::where(['orderid' => $orderid])->value('order_amount', 'waid');
        return $data;
    }

    //支付宝订单查询
    private static function aliPayTradeQuery($out_trade_no, $trade_no, $amount){
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = config('alipay.appid');
        $aop->rsaPrivateKey = config('alipay.privateKey');
        $aop->alipayrsaPublicKey = config('alipay.publicKey');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = "UTF-8";//'GBK';
        $aop->format='json';
        $request = new \AlipayTradeQueryRequest ();
        $bizcontent = [
            "out_trade_no" => $out_trade_no,
            "trade_no" => $trade_no
        ];
        $bizcontent = json_encode($bizcontent);
        $request->setBizContent($bizcontent);
        /*$request->setBizContent("{" .
            "\"out_trade_no\":\"20150320010101001\"," .
            "\"trade_no\":\"2014112611001004680 073956707\"," .
            "\"org_pid\":\"2088101117952222\"" .
            "  }");*/
        $result = $aop->execute ( $request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        $total_amount = $result->$responseNode->total_amount;
        $trade_status = $result->$responseNode->trade_status;
        if(!empty($resultCode) && $resultCode == 10000 && $total_amount == $amount && $trade_status == 'TRADE_SUCCESS'){
            return true;
        } else {
            return false;
        }
    }

    //存储支付宝支付结果
    private static function saveAliPayTradeResult($orderid, $trade_no, $trade_status){
        $where = ['orderid' => $orderid];
        $updateData = ['trade_no' => $trade_no, 'trade_status' => $trade_status, 'status' => 1, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantAuthorizationPay::where($where)->update($updateData);
        return $result;
    }

    //存储支付宝还款支付结果
    private static function saveAliPayRepayResult($orderid, $trade_no, $trade_status){
        $where = ['orderid' => $orderid];
        $updateData = ['trade_no' => $trade_no, 'trade_status' => $trade_status, 'status' => 1, 'updated_at' => date('Y-m-d H:i:s')];
        $result = MerchantAlipayRepay::where($where)->update($updateData);
        return $result;
    }

    //支付宝退款
    public static function aliPayTradeRefund(){
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $aop->appId = config('alipay.appid');
        $aop->rsaPrivateKey = config('alipay.privateKey');
        $aop->alipayrsaPublicKey = config('alipay.publicKey');
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = "UTF-8";//'GBK';
        $aop->format='json';
        $request = new \AlipayTradeRefundRequest ();
        $bizcontent = [
            "out_trade_no" => "jl20190422064049",
            "trade_no" => "2019042222001457511030448175",
            "refund_amount" => "0.01"
        ];
        $bizcontent = json_encode($bizcontent);
        $request->setBizContent($bizcontent);
        $result = $aop->execute ( $request);
        var_dump($result);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            echo "成功";
        } else {
            echo "失败";
        }
    }
}
