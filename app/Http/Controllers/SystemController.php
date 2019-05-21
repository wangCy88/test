<?php

namespace App\Http\Controllers;

use App\AuthGroups;
use App\AuthMerchants;
use App\AuthRoutes;
use App\AuthUsers;
use App\MerchantConsumeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SystemController extends Controller
{
    //角色管理
    public static function groupControl(){
        $mid = session('user_info')->mid;
        $data = AuthGroups::where(['mid' => $mid])->select('id', 'name', 'created_at')->paginate(10);
        return view('System.groupControl', compact('data'));
    }

    //获取路由列表
    public static function addGroup(){
        $routes = AuthRoutes::select('id', 'name', 'upid')->get();
        $data = [];
        $info = [];
        foreach($routes as $k => $v){
            if($v->upid === 0){
                $data[$v->name] = [];
                $info[$v->id] = $v->name;
            }
        }
        foreach($routes as $k => $v){
            if($v->upid !== 0) {
                foreach ($info as $kk => $vv) {
                    if ($v->upid === $kk) {
                        $data[$vv][$v->name] = $v->id;
                        break;
                    }
                }
            }
        }
        return response()->json(['code' => 0, 'data' => $data]);
    }

    //新增角色
    public static function addGroupDo(Request $request){
        $mid = session('user_info')->mid;
        $name = $request->name;
        $routes = $request->routes;
        if(empty($name) || empty($routes)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $upid = AuthRoutes::whereIn('id', $routes)->select('upid')->distinct()->get();
        foreach($upid as $v){
            $routes[] = "$v->upid";
        }
        sort($routes);
        $routes = implode(',', $routes);
        $insertData = ['mid' => $mid, 'name' => $name, 'routes' => $routes, 'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')];
        $result = AuthGroups::insert($insertData);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '新增失败']);
        }
        return response()->json(['code' => 0, 'msg' => '新增成功']);
    }

    //获取角色
    public static function modifyGroup(Request $request){
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        //获取当前角色路由
        $self = AuthGroups::select('name', 'routes')->find($id);
        $name = $self->name;
        $route = explode(',', $self->routes);
        //获取路由列表
        $routes = AuthRoutes::select('id', 'name', 'upid')->get();
        $data = [];
        $info = [];
        foreach($routes as $k => $v){
            if($v->upid === 0){
                $data[$v->name] = [];
                $info[$v->id] = $v->name;
            }
        }
        foreach($routes as $k => $v){
            if($v->upid !== 0) {
                foreach ($info as $kk => $vv) {
                    if ($v->upid === $kk) {
                        $data[$vv][$v->name] = $v->id;
                        break;
                    }
                }
            }
        }
        return response()->json(['code' => 0, 'data' => $data, 'route' => $route, 'name' => $name]);
    }

    //修改角色
    public static function modifyGroupDo(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        $name = $request->name;
        $routes = $request->routes;
        if(empty($id) || empty($name) || empty($routes)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $upid = AuthRoutes::whereIn('id', $routes)->select('upid')->distinct()->get();
        foreach($upid as $v){
            $routes[] = "$v->upid";
        }
        sort($routes);
        $routes = implode(',', $routes);
        $updateData = ['name' => $name, 'routes' => $routes, 'updated_at' => date('Y-m-d H:i:s')];
        $result = AuthGroups::where(['id' => $id, 'mid' => $mid])->update($updateData);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '修改失败']);
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    //删除角色
    public static function deleteGroup(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $result = AuthGroups::where(['id' => $id, 'mid' => $mid])->delete();
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '删除失败']);
        }
        return response()->json(['code' => 0, 'msg' => '删除成功']);
    }

    //用户管理
    public static function userControl(){
        $mid = session('user_info')->mid;
        $data = AuthUsers::where(['mid' => $mid])
            ->select('id', 'account', 'name', 'phone', 'sex', 'status', 'gid', 'mid', 'created_at')
            ->with(['authMerchants' => function($query){$query->select('id', 'name');}])
            ->with(['authGroups' => function($query){$query->select('id', 'name');}])
            ->paginate(10);
        $groups = AuthGroups::where(['mid' => $mid])->select('id', 'name')->get();
        $sexList = config('config.sexList');
        $jobStatusList = config('config.jobStatusList');
        return view('System.userControl', compact('data', 'groups', 'sexList', 'jobStatusList'));
    }

    //新增用户
    public static function addUser(Request $request){
        $mid = session('user_info')->mid;
        $name = $request->name;
        $account = $request->account;
        $password = $request->password;
        $phone = $request->phone;
        $sex = empty($request->sex) ? 0 : $request->sex;
        $status = empty($request->status) ? 0 : $request->status;
        $gid = $request->gid;
        if(!empty($name) && !empty($account) && !empty($password) && !empty($gid) && !empty($phone)){
            $password = md5($password);
            $insertData = ['name' => $name, 'account' => $account, 'password' => $password, 'phone' => $phone,
                'sex' => $sex, 'status' => $status, 'gid' => $gid, 'mid' => $mid, 'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')];
            AuthUsers::insert($insertData);
        }
        return redirect('userControl');
    }

    //获取用户
    public static function modifyUser(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $data = AuthUsers::where(['mid' => $mid])->select('name', 'phone', 'sex', 'status', 'gid')->find($id);
        $groups = AuthGroups::where(['mid' => $mid])->select('id', 'name')->get();
        $sexList = config('config.sexList');
        $jobStatusList = config('config.jobStatusList');
        return response()->json(['code' => 0, 'data' => $data, 'groups' => $groups, 'sexList' => $sexList,
            'jobStatusList' => $jobStatusList]);
    }

    //修改用户
    public static function modifyUserDo(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        $name = $request->name;
        $phone = $request->phone;
        $sex = empty($request->sex) ? 0 : $request->sex;
        $status = empty($request->status) ? 0 : $request->status;
        $gid = $request->gid;
        if(empty($id) || empty($name) || empty($phone) || empty($gid)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $updateData = ['name' => $name, 'phone' => $phone, 'sex' => $sex, 'status' => $status, 'gid' => $gid,
            'updated_at' => date('Y-m-d H:i:s')];
        $result = AuthUsers::where(['id' => $id, 'mid' => $mid])->update($updateData);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '修改失败']);
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    //修改密码
    public static function modifyUserPwdDo(Request $request){
        $mid = session('user_info')->mid;
        $id= $request->id;
        $password = $request->password;
        if(empty($id) || empty($password)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $password = md5($password);
        $updateData = ['password' => $password, 'updated_at' => date('Y-m-d H:i:s', time())];
        $result = AuthUsers::where(['id' => $id, 'mid' => $mid])->update($updateData);
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '修改失败']);
        }
        return response()->json(['code' => 0, 'msg' => '修改成功']);
    }

    //删除用户
    public static function deleteUser(Request $request){
        $mid = session('user_info')->mid;
        $id = $request->id;
        if(empty($id)){
            return response()->json(['code' => 1, 'msg' => '缺少参数']);
        }
        $result = AuthUsers::where(['id' => $id, 'mid' => $mid])->delete();
        if(!$result){
            return response()->json(['code' => 1, 'msg' => '删除失败']);
        }
        return response()->json(['code' => 0, 'msg' => '删除成功']);
    }

    //系统扣款
    public static function systemDeduction($phone, $mid, $amount, $type){
        $accountBalance = Redis::hget('merchantAccountBalance', $mid);
        $accountBalance -= $amount;
        /*if($accountBalance < 0){
            return false;
        }*/
        if(Redis::hset('merchantAccountBalance', $mid, $accountBalance) !== false){
            $insertData = ['mid' => $mid, 'account' => $phone, 'amount' => $amount, 'type' => $type,
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')];
            $result = MerchantConsumeRecord::insert($insertData);
            if(!$result){
                \Log::LogWirte('记录存入失败:' . json_encode($insertData), 'systemDeduction');
            }
            return true;
        }else{
            \Log::LogWirte('扣款失败:'. $phone . '|' . $mid . '|' . $amount . '|' . $type, 'systemDeduction');
            return false;
        }
    }

    //外部系统扣款
    public static function outsideSystemDeduction(Request $request){
        \Log::LogWirte('request:' . json_encode($request->toArray()), 'outsideSystemDeduction');
        $phone = $request->phone;
        $mid = $request->mchid;
        $amount = $request->amount;
        $type = $request->type;
        if(!empty($phone) && !empty($mid) && !empty($amount) && !empty($type)){
            self::systemDeduction($phone, $mid, $amount, $type);
        }
    }
}
