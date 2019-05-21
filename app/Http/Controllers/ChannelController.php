<?php

namespace App\Http\Controllers;

use App\MerchantChannelConfig;
use App\MerchantChannelMonitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChannelController extends Controller
{
    //渠道配置
    public static function channelConfig(Request $request){
        $mid = session('user_info')->mid;
        //渠道名称
        $name = $request->name;
        //渠道专员
        $member = $request->member;
        //渠道来源
        $from = $request->from;
        $where = [];
        if(!empty($name)){
            $where['name'] = $name;
        }
        if(!empty($member)){
            $where['member'] = $member;
        }
        if(!empty($from)){
            $where['from'] = $from;
        }
        if(!empty($mid)){
            $where['mchid'] = $mid;
        }
        $data = MerchantChannelConfig::where($where);
        $data = $data->orderBy('id', 'desc')->select('id', 'name', 'member', 'from', 'reg_price', 'reg_rate', 'created_at',
            'data_price', 'data_rate', 'credit_price', 'credit_rate', 'order_price', 'order_rate', 'loan_rate', 'code')
            ->paginate(10);
        return view('Channel.channelConfig', compact('data', 'name', 'member', 'from'));
    }

    //新增渠道
    public static function addChannelDo(Request $request){
        $mid = session('user_info')->mid;
        $name = $request->name;
        $member = $request->member;
        $from = $request->from;
        $username = $request->username;
        $password = $request->password;
        $reg_price = $request->reg_price;
        $reg_rate = $request->reg_rate;
        $data_price = $request->data_price;
        $data_rate = $request->data_rate;
        $credit_price = $request->credit_price;
        $credit_rate = $request->credit_rate;
        $order_price = $request->order_price;
        $order_rate = $request->order_rate;
        $loan_rate = $request->loan_rate;
        if($reg_rate > 100 || $data_rate > 100 || $credit_rate > 100 || $order_rate > 100 || $loan_rate > 100){
            return response()->json(['code' => 1, 'msg' => '各项转化率不能大于100']);
        }
        if(empty($username) || empty($password)){
            return response()->json(['code' => 1, 'msg' => '用户名或密码不能为空']);
        }
        $password = md5($password);
        //判断用户名和密码组合是否存在
        $id = MerchantChannelConfig::where(['username' => $username, 'password' => $password])->value('id');
        if($id){
            return response()->json(['code' => 1, 'msg' => '用户名已存在']);
        }
        //生成邀请码
        $code = self::makeCode();
        $insertData = ['mchid' => $mid, 'name' => $name, 'member' => $member, 'from' => $from, 'username' => $username,
            'password' => $password, 'reg_price' => $reg_price, 'reg_rate' => $reg_rate, 'data_price' => $data_price,
            'data_rate' => $data_rate, 'credit_price' => $credit_price, 'credit_rate' => $credit_rate,
            'order_price' => $order_price, 'order_rate' => $order_rate, 'loan_rate' => $loan_rate, 'code' => $code,
            'created_at' => date('Y-m-d H:i:s', time()), 'updated_at' => date('Y-m-d H:i:s', time())];
        $result = MerchantChannelConfig::insert($insertData);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '新增失败']);
        }
        return response()->json(['code' => 0, 'msg' => '新增成功']);
    }

    //生成邀请码
    private static function makeCode() {
        $code = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $rand = $code[rand(0,25)]
            .strtoupper(dechex(date('m')))
            .date('d').substr(time(),-5)
            .substr(microtime(),2,5)
            .sprintf('%02d',rand(0,99));
        for(
            $a = md5( $rand, true ),
            $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
            $d = '',
            $f = 0;
            $f < 8;
            $g = ord( $a[ $f ] ),
            $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],
            $f++
        );
        return  $d;
    }

    //获取渠道信息
    public static function modifyChannel(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantChannelConfig::where(['mchid' => $mid])
            ->select('id', 'name', 'member', 'from', 'reg_price', 'reg_rate', 'data_price', 'data_rate', 'credit_price',
                'credit_rate', 'order_price', 'order_rate', 'loan_rate', 'username')->find($id);
        if(empty($data['id'])){
            return response()->json(['code' => 1, 'msg' => '参数错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //修改渠道
    public static function modifyChannelDo(Request $request){
        $mid = session('user_info')->mid;
        $id= $request->id;
        $name = $request->name;
        $member = $request->member;
        $from = $request->from;
        $reg_price = $request->reg_price;
        $reg_rate = $request->reg_rate;
        $data_price = $request->data_price;
        $data_rate = $request->data_rate;
        $credit_price = $request->credit_price;
        $credit_rate = $request->credit_rate;
        $order_price = $request->order_price;
        $order_rate = $request->order_rate;
        $loan_rate = $request->loan_rate;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        if($reg_rate > 100 || $data_rate > 100 || $credit_rate > 100 || $order_rate > 100 || $loan_rate > 100){
            return response()->json(['code' => 1, 'msg' => '各项转化率不能大于100']);
        }
        $updateData = ['name' => $name, 'member' => $member, 'from' => $from, 'reg_price' => $reg_price,
            'reg_rate' => $reg_rate, 'data_price' => $data_price, 'data_rate' => $data_rate,
            'credit_price' => $credit_price, 'credit_rate' => $credit_rate, 'order_price' => $order_price,
            'order_rate' => $order_rate, 'loan_rate' => $loan_rate, 'updated_at' => date('Y-m-d H:i:s', time())];
        $result = MerchantChannelConfig::where(['id' => $id, 'mchid' => $mid])->update($updateData);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '修改失败']);
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    //修改密码
    public static function modifyPasswordDo(Request $request){
        $mid = session('user_info')->mid;
        $id= $request->id;
        $password = $request->password;
        if(empty($id) || empty($password)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $password = md5($password);
        $updateData = ['password' => $password, 'updated_at' => date('Y-m-d H:i:s', time())];
        $result = MerchantChannelConfig::where(['id' => $id, 'mchid' => $mid])->update($updateData);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '修改失败']);
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    //删除渠道
    public static function deleteChannel(Request $request){
        $mid = session('user_info')->mid;
        $id= $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $result = MerchantChannelConfig::where(['id' => $id, 'mchid' => $mid])->delete();
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '删除失败']);
        }
        return response()->json(['code' => 0, 'msg' => '删除成功']);
    }

    //渠道监控
    public static function channelMonitor(Request $request){
        $mid = session('user_info')->mid;
        //渠道
        $channel = $request->channel;
        //开始时间和结束时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        $where = [];
        if(!empty($channel)){
            $where['channel'] = $channel;
        }
        $where['mchid'] = $mid;
        $data = MerchantChannelMonitor::where($where);
        $total = MerchantChannelMonitor::where($where);
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('curr_date',[$date1,$date2]);
            $total = $total->whereBetween('curr_date',[$date1,$date2]);
        }else{
            $data = $data->whereBetween('curr_date',[date('Y-m-d'),date('Y-m-d')]);
            $total = $total->whereBetween('curr_date',[date('Y-m-d'),date('Y-m-d')]);
        }
        $data = $data->orderBy('id', 'desc')->select('channel', 'curr_date', 'reg', 'complete', 'pass', 'pass_amount', 'order', 'order_amount', 'reg_after')
            ->with(['merchantChannelConfig' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        foreach($data as $k => $v){
            $data[$k]['complete_per'] = empty($v->reg) ? '0.00' : sprintf("%.2f",substr(sprintf("%.3f", $v->complete/$v->reg*100), 0, -2));//转化率
            $data[$k]['pass_per'] = empty($v->reg) ? '0.00' : sprintf("%.2f",substr(sprintf("%.3f", $v->pass/$v->reg*100), 0, -2));//授信通过率
        }
        $total = $total->first([
            DB::raw('sum(reg) as reg'),
            DB::raw('sum(complete) as complete'),
            DB::raw('sum(pass) as pass'),
            DB::raw('sum(pass_amount) as pass_amount'),
            DB::raw('sum(`order`) as `order`'),
            DB::raw('sum(order_amount) as order_amount'),
            DB::raw('sum(reg_after) as reg_after')
        ]);
        $total['pass_per'] = empty($total->reg) ? '0.00' : sprintf("%.2f",substr(sprintf("%.3f", $total->pass/$total->reg*100), 0, -2));//授信通过率
        //获取渠道
        $channels = MerchantChannelConfig::where(['mchid' => $mid])->select('id', 'name')->get();
        return view('Channel.channelMonitor', compact('data', 'total', 'channel', 'date1', 'date2', 'channels'));
    }
}
