<?php

namespace App\Http\Controllers;

use App\MerchantFeedback;
use App\MerchantMessageRecord;
use App\MerchantNotice;
use Illuminate\Http\Request;

class OperateController extends Controller
{
    //意见反馈统计
    public static function feedback(Request $request){
        $mid = session('user_info')->mid;
        //客户名称
        $name = $request->name;
        //手机号
        $phone = $request->phone;
        //反馈类型
        $type = $request->type;
        //反馈内容
        $remark = $request->remark;
        //反馈时间
        $date1 = $request->date1;
        $date2 = $request->date2;
        $where = [];
        $where['mchid'] = $mid;
        if(!empty($name)){
            $where['name'] = $name;
        }
        if(!empty($phone)){
            $where['phone'] = $phone;
        }
        if(!empty($type) || $type === '0'){
            $where['type'] = $type;
        }
        $data = MerchantFeedback::where($where);
        if(!empty($date1) && !empty($date2)){
            $data = $data->whereBetween('created_at',[$date1,$date2]);
        }
        if(!empty($remark)){
            $data = $data->where('remark', 'like', '%' . $remark . '%');
        }
        $data = $data->orderBy('id', 'desc')->paginate(10);
        $typeList = config('config.feedbackTypeList');
        $answerStatusList = config('config.answerStatusList');
        return view('Operate.feedback', compact('data', 'name', 'phone', 'type', 'remark', 'date1', 'date2', 'typeList',
            'answerStatusList'));
    }

    //获取反馈
    public static function getFeedbackSystem(Request $request){
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = MerchantFeedback::select('remark', 'answer')->find($id);
        if(!$data){
            return response()->json(['code' => 2, 'msg' => '参数错误']);
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //回复反馈
    public static function answerFeedback(Request $request){
        $id = $request->id;
        $answer = $request->answer;
        if(empty($id) || empty($answer)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        if(strlen($answer) >= 255){
            return response()->json(['code' => 2, 'msg' => '回复过长']);
        }
        $result = MerchantFeedback::where(['id' => $id])->update(['answer' => $answer, 'status' => 1]);
        if(!$result){
            return response()->json(['code' => 3, 'msg' => '回复失败']);
        }
        return response()->json(['code' => 0, 'msg' => '回复成功']);
    }

}
