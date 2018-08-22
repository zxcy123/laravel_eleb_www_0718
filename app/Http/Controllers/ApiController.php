<?php

namespace App\Http\Controllers;

use App\Model\Address;
use App\Model\Member;
use App\Model\Menu;
use App\Model\MenuCategory;
use App\Model\Order;
use App\Model\OrderGoods;
use App\Model\Shop;
use App\Model\ShoppingCart;
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
        for($i=0;$i<count($menucategory);++$i) {
            //根据菜品分类查询该分类下的菜品,并循环插入goods_list里
            $menu = DB::table('menus')->where('category_id', $menucategory[$i]->id)->get();
            for ($j = 0; $j < count($menu); ++$j) {
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
//        return $menucategory;
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



        return substr(substr(json_encode($business),1),0,-1);
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

    //添加收货地址
    public function addaddress(Request $request)
    {
        $validator=Validator::make($request->all(),[
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
        if ($validator->fails()){
            return [
                "status"=> "false",
                "message"=> $validator->errors()->first()
            ];
        }
        Address::create([
            "user_id"=>Auth::user()->id,
            "name"=>$request->name,
            "tel"=>$request->tel,
            "provence"=>$request->provence,
            "city"=>$request->city,
            "area"=>$request->area,
            "detail_address"=>$request->detail_address,
            'is_default'=>0
        ]);
        return json_encode([
        "status"=>"true",
        "message"=>"添加成功"
        ]);
    }

    //显示修改页面
    public function address(Request $request)
    {
        return json_encode(Address::find($request->id));
    }

    //修改收货地址
    public function editaddress(Request $request)
    {
        $validator=Validator::make($request->all(),[
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
        if ($validator->fails()){
            return [
                "status"=> "false",
                "message"=> $validator->errors()->first()
            ];
        }
        $status=Address::where('id',$request->id)->update([
            "user_id"=>Auth::user()->id,
            "name"=>$request->name,
            "tel"=>$request->tel,
            "provence"=>$request->provence,
            "city"=>$request->city,
            "area"=>$request->area,
            "detail_address"=>$request->detail_address,
            'is_default'=>0
        ]);
        if ($status){
            return json_encode([
                "status"=>"true",
                "message"=>"修改成功"
            ]);
        }
        return json_encode([
            "status"=>"false",
            "message"=>"修改失败"
        ]);
    }
    
    //获取购物车数据接口
    public function cart()
    {
        static $totalCost=0;
        $goods_lists=ShoppingCart::select('goods_id','amount')->where('user_id',Auth::user()->id)->get();
        foreach ($goods_lists as $goods_list){
            $goods=Menu::select('goods_name','goods_img','goods_price')->where('id',$goods_list->goods_id)->get();
            $goods_list->goods_name=$goods[0]->goods_name;
            $goods_list->goods_img=$goods[0]->goods_img;
            $goods_list->goods_price=$goods[0]->goods_price;
            $totalCost+=(int)$goods[0]->goods_price*(int)$goods_list->amount;
        }
        $carts=["goods_list"=>$goods_lists,"totalCost"=>$totalCost];
        return json_encode($carts);
    }

    //保存购物车接口
    public function addcart(Request $request)
    {
        DB::table('shopping_carts')->where("user_id",Auth::user()->id)->delete();
        $validator=Validator::make($request->all(),[
            "goodsList"=>"required",
            "goodsCount"=>"required"
        ],[
            'goodsList.required'=>"商品列表不能为空",
            'goodsCount.required'=>"商品数量不能为空"
        ]);
        if ($validator->fails()){
            return [
                "status"=> "false",
                "message"=> $validator->errors()->first()
            ];
        }
        for ($i=0;$i<count($request->goodsList);++$i){
            ShoppingCart::create([
            "user_id"=>Auth::user()->id,
            "goods_id"=>$request->goodsList[$i],
            "amount"=>$request->goodsCount[$i],
            ]);
        }
        return json_encode([
            "status"=>"true",
            "message"=>"添加成功"
        ]);

    }

    // 添加订单接口
    public function addorder(Request $request)
    {
        $validator=Validator::make($request->all(),[
            "address_id"=>"required"
        ],[
            'address_id.required'=>"地址不能为空不能为空"
        ]);
        if ($validator->fails()){
            return [
                "status"=> "false",
                "message"=> $validator->errors()->first()
            ];
        }
        /**
         * address_id: 地址id
         */
        //根据地址id查询地址详情
        $address = Address::find($request->address_id);
        //根据用户id查询购物车表中的goods_id,从而查询店铺id
        $shoppingcart=ShoppingCart::select('goods_id')->where('user_id',Auth::user()->id);
//        dd($carts);
        $goods=Menu::select('shop_id')->where('id',$shoppingcart->first()->goods_id);
        $carts=ShoppingCart::all()->where('user_id',Auth::user()->id);
        $total=0;
        $ordergoodsstatus=true;
        foreach ($shoppingcart as $cart){
            $goods=Menu::select('goods_price')->where('id',$cart->goods_id);
            $total+=$shoppingcart->amount*$goods->goods_price;
        }
        //开启事务
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => Auth::user()->id,
                'shop_id' =>$goods->first()->shop_id,
                'sn'=>date("YmdHis").rand(1000,9999),
                'province' =>$address->provence,
                'city' =>$address->city,
                'area' =>$address->area,
                'detail_address' =>$address->detail_address,
                'tel' =>$address->tel,
                'name' =>$address->name,
                'total' =>$total,
                'status' => 0,
                'out_trade_no' => rand(1000,9999)
            ]);
            foreach ($carts as $cartgoods){
                $goods=Menu::find($cartgoods->goods_id);
//                dd();
                $ordergoods = OrderGoods::create([
                    'order_id' => $order->id,
                    'goods_id' => $cartgoods->goods_id,
                    'amount' => $cartgoods->amount,
                    'goods_name'=>$goods->goods_name,
                    'goods_img' => $goods->goods_img,
                    'goods_price' => $goods->goods_price
                ]);
                if($ordergoods==false){
                    $ordergoodsstatus=false;
                }
            }
            if ($order && $ordergoodsstatus) {
                $tel = $address->tel;
                // *** 需用户填写部分 ***

                // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
                $accessKeyId = "LTAIQRpGUr0gCV2H";
                $accessKeySecret = "0yf6cU7AaN2FEipU5LEX1Ldec0bncl";

                // fixme 必填: 短信接收号码
                $params["PhoneNumbers"] = $tel;

                // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
                $params["SignName"] = "李俊杰";

                // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
                $params["TemplateCode"] = "SMS_141450003";

                // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
                $params['TemplateParam'] = Array(
                    "code" => 'Eleb',
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
                DB::commit();
                return [
                    "status" => "true",
                    "message" => "通知发送成功",
                    "order_id" => $order->id
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                "status" => "false",
                "message" => "添加失败"
            ];
        }
    }
    
    //获取订单接口
    public function order(Request $request)
    {
        $order=Order::select('shop_id','status',"detail_address")->where('user_id',Auth::user()->id)->first();
        $shop=Shop::select('shop_name','shop_img')->where('id',$order->shop_id)->first();
        $goods_list=DB::table('order_goods')->select("created_at","goods_id","goods_name","amount","goods_img","goods_price")->where('order_id',$request->id)->get();
        $status=$order->status==0?"代付款":"已付款";
        $order_price=0;
        foreach ($goods_list as $goods){
            $order_price+=$goods->goods_price;
        }

        return [
            "id"=>$request->id,
            "order_code"=>"0000001",
            "order_birth_time"=>$goods_list[0]->created_at,
            "order_status"=>$status,
            "shop_id"=>$order->shop_id,
            "shop_name"=>$shop->shop_name,
            "shop_img"=>$shop->shop_img,
            "shop_list"=>$goods_list,
            "order_price"=>$order_price,
            "order_address"=>$order->detail_address

        ];
    }

    // 获得订单列表接口
    public function orderlist()
    {
        $orders=Order::select('id','shop_id','status',"detail_address","created_at")->where('user_id',Auth::user()->id)->get();
        static $order_price=0;
        $orderlist=[];
        foreach ($orders as $key=>$order){
            $status=$order->status==0?"代付款":"已付款";
            $shop=Shop::select('shop_name','shop_img')->where('id',$order->shop_id)->first();
            $goods_list=DB::table('order_goods')->select("goods_id","goods_name","amount","goods_img","goods_price")->where('order_id',$order->id)->get();

            foreach ($goods_list as $goods){
                $order_price+=$goods->goods_price;
            }
            $orderlist[$key]=[
                "id"=>$order->id,
                "order_code"=>"000000".$order->id,
                "order_birth_time"=>$order->created_at->toArray()['formatted'],
                "order_status"=>$status,
                "shop_id"=>$order->shop_id,
                "shop_name"=>$shop->shop_name,
                "shop_img"=>$shop->shop_img,
                "goods_list"=>$goods_list,
                "order_price"=>$order_price,
                "order_address"=>$order->detail_address
            ];
        }
        return $orderlist;



        /*
        $orderlist=[];
        foreach ($goods_lists as $key=>$goods_list){
//            foreach ($goods_list as $goods){
//                $order_price+=$goods->goods_price;
//            }
            $orderlist[$key]=$goods_list;
        }
        $status=$order->status==0?"代付款":"已付款";
        $order_price=0;
        $list=[
            "id"=>$order->id,
            "order_code"=>"0000001",
            "order_birth_time"=>"2017-02-17 18:36",
            "order_status"=>$status,
            "shop_id"=>$order->shop_id,
            "shop_name"=>$shop->shop_name,
            "shop_img"=>$shop->shop_img,
            "shop_list"=>$goods_list,
            "order_price"=>$order_price,
            "order_address"=>$order->detail_address
        ];
        return json_encode($list);*/
    }

    //修改密码
    public function changepassword()
    {

    }
}
