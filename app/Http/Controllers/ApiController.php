<?php

namespace App\Http\Controllers;

use App\Model\Menu;
use App\Model\MenuCategory;
use App\Model\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    //获取商家列表
    public function businesslist()
    {
        $businesslist=Shop::all();
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
    public function regist()
    {

    }
}
