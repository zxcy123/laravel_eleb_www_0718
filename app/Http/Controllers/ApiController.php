<?php

namespace App\Http\Controllers;

use App\Model\Address;
use App\Model\Member;
use App\Model\Menu;
use App\Model\MenuCategory;
use App\Model\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    //获取商家列表
    public function businesslist()
    {
        $businesslist=Shop::all()->makeHidden(['shop_category_id','created_at','updated_at']);
        for($i=0;$i<count($businesslist);++$i){
            $businesslist[$i]['distance']=800;
            $businesslist[$i]['estimate_time']=30;
        }
        return $businesslist;
    }
    
    //获取指定商家
    public function business(Request $request)
    {
        //根据商户id查询该商户的菜品分类
        $business=DB::table('shops')->where('id',$request->id)->get();
        $menucategory=DB::table('menu_categories')->where('shop_id',$request->id)->get();
//        dump($menucategory);
        for($i=0;$i<count($menucategory);++$i) {
            //根据菜品分类查询该分类下的菜品,并循环插入goods_list里
            $menu = DB::table('menus')->where('category_id', $menucategory[$i]->id)->get();
            for ($j = 0; $j < count($menucategory); ++$j) {
                $menucategory[$i]->goods_list[$j]=[
                    "goods_id"=> $menu[$j]->id,
                    "goods_name"=> $menu[$j]->goods_name,
                    "rating"=> $menu[$j]->rating,
                    "shop_id"=> $menu[$j]->shop_id,
                    "category_id"=> $menu[$j]->category_id,
                    "goods_price"=> $menu[$j]->goods_price,
                    "description"=> $menu[$j]->description,
                    "month_sales"=> $menu[$j]->month_sales,
                    "rating_count"=> $menu[$j]->rating_count,
                    "tips"=> $menu[$j]->tips,
                    "satisfy_count"=> $menu[$j]->satisfy_count,
                    "satisfy_rate"=> $menu[$j]->satisfy_rate,
                    "goods_img"=> $menu[$j]->goods_img,
                    "status"=> $menu[$j]->status,
                    "created_at"=> $menu[$j]->created_at,
                    "updated_at"=> $menu[$j]->updated_at
                ];
            }
        }
        //将假数据和菜品分类及其下属菜品一起加入$business变量
        for($i=0;$i<count($business);++$i){
            $business[$i]->distance=8000;
            $business[$i]->estimate_time=30;
            $business[$i]->service_code= 4.6;
            $business[$i]->foods_code= 4.4;
            $business[$i]->high_or_low= true;
            $business[$i]->h_l_percent= 30;
            $business[$i]->send_cost= 5;
            $business[$i]->evaluate=[
                [
                    "user_id"=> 12344,
                    "username"=> "w******k",
                    "user_img"=> "http=>//www.homework.com/images/slider-pic4.jpeg",
                    "time"=> "2017-2-22",
                    "evaluate_code"=> 1,
                    "send_time"=> 30,
                    "evaluate_details"=> "不怎么好吃"
                ],[
                    "user_id"=> 12344,
                    "username"=> "w******k",
                    "user_img"=> "http=>//www.homework.com/images/slider-pic4.jpeg",
                    "time"=> "2017-2-22",
                    "evaluate_code"=> 4.5,
                    "send_time"=> 30,
                    "evaluate_details"=> "很好吃"
                ],
                [
                    "user_id"=> 12344,
                    "username"=> "w******k",
                    "user_img"=> "http=>//www.homework.com/images/slider-pic4.jpeg",
                    "time"=> "2017-2-22",
                    "evaluate_code"=> 5,
                    "send_time"=> 30,
                    "evaluate_details"=> "很好吃"
                ],[
                    "user_id"=> 12344,
                    "username"=> "w******k",
                    "user_img"=> "http=>//www.homework.com/images/slider-pic4.jpeg",
                    "time"=> "2017-2-22",
                    "evaluate_code"=> 4.7,
                    "send_time"=> 30,
                    "evaluate_details"=> "很好吃"
                ],[
                    "user_id"=> 12344,
                    "username"=> "w******k",
                    "user_img"=> "http=>//www.homework.com/images/slider-pic4.jpeg",
                    "time"=> "2017-2-22",
                    "evaluate_code"=> 5,
                    "send_time"=> 30,
                    "evaluate_details"=> "很好吃"
                ]
            ];
            $business[$i]->commodity=$menucategory;
        }

//        return(substr(json_encode($business),1));
        return substr(substr(json_encode($business),1),0,-1);
//        return substr(substr(json_encode($business),0,1),-1,1);
//        return substr(substr(json_encode($business),0,1),-1,1);
    }
    
    //获取注册
    public function regist(Request $request,Member $regist)
    {
        $validator = Validator::make($request->all(),[
            'username'=>"required|unique:members",
            'tel'=>"required|unique:members",
            'sms'=>"required",
            'password'=>"required",
        ],[
            'username.required'=>"用户名不能为空",
            'tel.required'=>"手机号不能为空",
            'sms.required'=>"验证码不能为空",
            'password.required'=>"密码不能为空",
        ]);
        if($validator->fails()){
            return [
                "status"=> "false",
                "message"=> $validator->errors()->first()
            ];
        }
        $redis=new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $sms=$redis->get('code_'.$request->tel);
//        dump($request->sms);
//        dd($sms);
        if ($sms!==$request->sms){
//            return '{"status": "false","message": "注册失败"}';
            return json_encode([
                "status"=>"false",
                "message"=>"注册失败"
            ]);
        }

        $regist->create([
            'username'=>$request->username,
            'tel'=>$request->tel,
            'password'=>bcrypt($request->password),
        ]);
        return json_encode([
            "status"=>"true",
            "message"=>"注册成功"
        ]);
    }
    
    // 登录验证接口
    public function logincheck(Request $request)
    {
        $this->validate($request,[
            "name"=>"required",
            "password"=>"required",
        ]);
        if(Auth::attempt(['username'=>$request->name,'password'=>$request->password])){
            return json_encode([
                "status"=>"true",
                "message"=>"登录成功",
                "user_id"=>Auth::user()->id,
                "username"=>Auth::user()->username
            ]);

        }

        return json_encode([
            "status"=>"false",
            "message"=>"登录失败",
        ]);
    }
    
    //验证码接口
    public function sms(Request $request)
    {

        $tel = $request->tel;
        $params = [];
        $alisms = random_int(100000, 999999);
        // *** 需用户填写部分 ***

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = "LTAIQRpGUr0gCV2H";
        $accessKeySecret = "0yf6cU7AaN2FEipU5LEX1Ldec0bncl";

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $tel;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = "李俊杰";

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = "SMS_140510058";

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = Array(
            "code" => $alisms,
//        "product" => "阿里通信"
        );

        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new \App\SignatureHelper();

        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        // fixme 选填: 启用https
        // ,true
        );
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->set('code_' . $tel, $alisms);
        $redis->expire($tel, 120);
        return [
            "status" => "true",
            "message"=>"获取短信验证码成功"
        ];
    }
    
    //获取收货地址接口
    public function addresslist()
    {
        return json_encode(Address::all()->where('user_id',Auth::user()->id));
    }
    public function addaddress(Request $request)
    {
        Validator::make($request->all(),[
            "name"=>"required",
            "tel"=>"required",
            "provence"=>"required",
            "city"=>"required",
            "area"=>"required",
            "detail_address"=>"required"
        ],[
            'name.required'=>"收货人不能为空",
            'tel.required'=>"联系方式不能为空",
            'provence.required'=>"省不能为空",
            'city.required'=>"市不能为空",
            'area.required'=>"区不能为空",
            'detail_address.required'=>"详细地址不能为空"
        ]);

//        return json_encode(Address::all()->where('user_id',Auth::user()->id));
        return json_encode([
        "status"=>"true",
        "message"=>"添加成功"
        ]);
    }
    
    //修改密码
    public function changepassword()
    {

    }
}
