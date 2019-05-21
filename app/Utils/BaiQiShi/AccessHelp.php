<?php

class AccessHelp
{
    /**
     * 登录
     */
    public static function loginMno($reqId, $partnerId, $certNo, $name, $mobile, $pwd, $smsCode) {
        $loginUrl = "https://credit.baiqishi.com/clweb/api/mno/login";
        $data = array('reqId'=>$reqId, 'partnerId'=>$partnerId, 'certNo'=>$certNo, 'name'=>$name, 'mobile'=>$mobile, 'pwd'=>$pwd, 'smsCode'=>$smsCode);

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $encode = mb_detect_encoding($data, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        $req = mb_convert_encoding($data, 'UTF-8', $encode);

        return self::doPost($loginUrl, $req);
    }

    /**
     * 二次授权
     * @param $reqId
     * @param $smsCode
     * @return mixed
     */
    public static function verifySmsCodeMno($reqId, $smsCode) {
        $smsCodeUrl = "https://credit.baiqishi.com/clweb/api/mno/verifyauthsms";
        $data = array('reqId'=>$reqId, 'smsCode'=>$smsCode);
        $data = json_encode($data);

        $encode = mb_detect_encoding($data, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        $req = mb_convert_encoding($data, 'UTF-8', $encode);

        return self::doPost($smsCodeUrl, $req);
    }

    /**
     * 重发登录动码
     * @param $reqId
     * @return mixed
     */
    public static function sendLoginSmsMno($reqId) {
        $sendLoginSmsUrl = "https://credit.baiqishi.com/clweb/api/mno/sendloginsms";
        $data = array('reqId'=> $reqId);
        $data = json_encode($data);

        $encode = mb_detect_encoding($data, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        $req = mb_convert_encoding($data, 'UTF-8', $encode);

        //error_log($req);
        return self::doPost($sendLoginSmsUrl, $req);

    }

    /**
     * 重发二次鉴权动码
     * @param $reqId
     * @return mixed
     */
    public static function sendAuthSmsMno($reqId) {
        $sendAuthSmsUrl = "https://credit.baiqishi.com/clweb/api/mno/sendauthsms";
        $data = array('reqId'=> $reqId);
        $data = json_encode($data);

        $encode = mb_detect_encoding($data, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        $req = mb_convert_encoding($data, 'UTF-8', $encode);
        return self::doPost($sendAuthSmsUrl, $req);
    }

    /**
     * 淘宝登录
     */
    public static function loginTb($reqId, $partnerId, $certNo, $name, $mobile, $pwd, $smsCode, $userName) {
        $loginUrl = "https://credit.baiqishi.com/clweb/api/tb/login";
        $data = array('reqId'=>$reqId, 'partnerId'=>$partnerId, 'certNo'=>$certNo, 'name'=>$name, 'mobile'=>$mobile, 'pwd'=>$pwd, 'smsCode'=>$smsCode, 'userName'=>$userName, 'type' => '1');

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $encode = mb_detect_encoding($data, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        $req = mb_convert_encoding($data, 'UTF-8', $encode);

        return self::doPost($loginUrl, $req);
    }

    /**
     * 重发淘宝登录动码
     * @param $reqId
     * @return mixed
     */
    public static function sendLoginSmsTb($reqId) {
        $sendLoginSmsUrl = "https://credit.baiqishi.com/clweb/api/tb/sendloginsms";
        $data = array('reqId'=> $reqId, 'type' => '1');
        $data = json_encode($data);

        $encode = mb_detect_encoding($data, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
        $req = mb_convert_encoding($data, 'UTF-8', $encode);

        //error_log($req);
        return self::doPost($sendLoginSmsUrl, $req);

    }

    public static function doPost($url, $data) {
        $ch = curl_init();
        //error_log($url.$data);

        $timeout = 1500;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $rs = curl_exec($ch);
        curl_close($ch);

        $rs = json_decode($rs, true);
        return $rs;
    }

    public static function Post($PostArry,$request_url){
        $postData = $PostArry;
        $postDataString = json_encode($postData);//格式化参数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $request_url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postDataString); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 60); // 设置超时限制防止死循环返回
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {

            $tmpInfo = curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }
}