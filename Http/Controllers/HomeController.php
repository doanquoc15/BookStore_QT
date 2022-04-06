<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

session_start();

class HomeController extends Controller
{
    public function index()
    {
        $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
        $book_author = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
        $all_product = DB::table('product')->where('product_status', '1')->orderby('product_id', 'desc')->limit(15)->get();
        return view('page.home')->with('category', $cate_product)->with('author', $book_author)->with('all_product', $all_product);
    }

    public function search(Request $request)
    {
        $keywords = $request->keywords_submit;
        $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
        $brand_product = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
        $search_product = DB::table('product')->where('product_name', 'like', '%' . $keywords . '%')->get();
        return view('page.product.search')->with('category', $cate_product)->with('author', $brand_product)->with('search_product', $search_product);
    }


     // lay thong tin tai khoan
     public function get_customer()
     {
         $customer_id = Session::get('customer_id');
         $customer = DB::table('customer')->where('customer_id', $customer_id)->first();
 
         $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
         $brand_product = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
 
         return view('page.user.update_user')->with('category', $cate_product)->with('brand', $brand_product)->with(compact('customer'));
 
     }
 
     // cau 26 cap nhat user
     public function update_user(Request $request)
     {
         $customer_id = Session::get('customer_id');
         $data = array();
         $data['customer_name'] = $request->customer_name;
         $data['customer_email'] = $request->customer_email;
         $data['customer_phone'] = $request->customer_phone;
         $data['customer_id'] = $customer_id;
         DB::table('customer')->where('customer_id', $customer_id)->update($data);
         Session::put('message', 'Cập nhật sản phẩm thành công');
         return Redirect::to('cap-nhat-user');
     }
     public function show_update_pass(Request $request)
     {
 
         $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
         $brand_product = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
 
         return view('page.user.update_password')->with('category', $cate_product)->with('author', $brand_product);
     }
     public function update_pass_saver(Request $request)
     {
 
         $old_password = md5($request->old_password);
         $result = DB::table('customer')->where('customer_password', $old_password)->first();
 
         if ($result) {
             $data = array();
             $customer_id = Session::get('customer_id');
             $data['customer_password'] = md5($request->new_password);
             DB::table('customer')->where('customer_id', $customer_id)->update($data);
             return Redirect::to('cap-nhat-user');
 
         } else {
             Session::put('message', 'đổi mât khẩu không thành công');
             return Redirect::to('cap-nhat-pass');
         }
 
     }
 
     public function show_pass()
     {
 
         $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
         $brand_product = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
 
         return view('page.checkout.quen_mat_khau')->with('category', $cate_product)->with('author', $brand_product);
     }
 
}
