<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
// use DB;
// use Session;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
session_start();

class CategoryController extends Controller
{
    public function AuthLogin(){
        $admin_id = Session::get('admin_id');
        if($admin_id){
            return Redirect::to('dashboard');
        }else{
            return Redirect::to('admin')->send();
        }
    }

    public function add_category(){
        $this->AuthLogin();
        return view('admin.category.add_category');
    }

    public function all_category(){
        $this->AuthLogin();
        $all_category = DB::table('category')->paginate(4);
        $manager_category  = view('admin.category.all_category')->with('all_category',$all_category);
        return view('admin_layout')->with('admin.category.all_category', $manager_category);
    }

    public function search_category(Request $request){
        $category = $request->search_category;
        if(!empty($category)){
            $all_category = DB::table('category')->Where('category_name','LIKE', "%{$category}%")->paginate(4);
            $manager_category  = view('admin.category.all_category')->with('all_category',$all_category);
            return view('admin_layout')->with('admin.category.all_category', $manager_category);
        }

        $all_category = DB::table('category')->paginate(4);
        $manager_category  = view('admin.category.all_category')->with('all_category',$all_category);
        return view('admin_layout')->with('admin.category.all_category', $manager_category);
    }

    public function save_category(Request $request){
        $this->AuthLogin();
        $data = array();
        $data['category_name'] = $request->category_name;
        $data['category_desc'] = $request->category_desc;
        $data['category_slug'] = $request->category_slug;
        $data['category_status'] = $request->category_status;

        DB::table('category')->insert($data);
        Session::put('message','Đã thêm một danh mục.');
        return Redirect::to('all-category-product');
    }

    public function unactive_category($category_id){
        $this->AuthLogin();
        DB::table('category')->where('category_id',$category_id)->update(['category_status'=>0]);
        Session::put('message','Đã ẩn danh mục sản phẩm này.');
        return Redirect::to('all-category-product');

    }
    public function active_category($category_id){
        $this->AuthLogin();
        DB::table('category')->where('category_id',$category_id)->update(['category_status'=>1]);
        Session::put('message','Đã hiển thị danh mục sản phẩm này.');
        return Redirect::to('all-category-product');
    }

    public function edit_category($category_id){
        $this->AuthLogin();
        $edit_category = DB::table('category')->where('category_id',$category_id)->get();

        $manager_category  = view('admin.category.edit_category')->with('edit_category',$edit_category);

        return view('admin_layout')->with('admin.category.edit_category', $manager_category);
    }

    public function update_category(Request $request,$category_id){
        $this->AuthLogin();
        $data = array();
        $data['category_name'] = $request->category_name;
        $data['category_desc'] = $request->category_desc;
        DB::table('category')->where('category_id',$category_id)->update($data);
        Session::put('message','Đã cập nhật một danh mục.');
        return Redirect::to('all-category-product');
    }

    public function delete_category($category_id){
        $this->AuthLogin();
        DB::table('category')->where('category_id',$category_id)->delete();
        Session::put('message','Đã xóa một danh mục.');
        return Redirect::to('all-category-product');
    }



       //BE client
       public function show_category_home(Request $request ,$slug_category_product){
        $cate_product = DB::table('category')->where('category_status','1')->orderby('category_id','desc')->get();
        $book_product = DB::table('book_author')->where('bookAuthor_status','1')->orderby('bookAuthor_id','desc')->get();
        $category_by_id = DB::table('product')->join('category','product.category_id','=','category.category_id')
        ->where('category.category_slug',$slug_category_product)->get();

        foreach($category_by_id as $key => $val){
        //seo
        $meta_desc = $val->category_desc;
        $meta_keywords = $val->meta_keywords;
        $meta_title = $val->category_name;
        $url_canonical = $request->url();
        //--seo
        }

        $category_name = DB::table('category')->where('category.category_slug',$slug_category_product)->limit(1)->get();
        return view('page.category.show_category')->with('category',$cate_product)->with('author',$book_product)
        ->with('category_by_id',$category_by_id)->with('category_name',$category_name)->with('meta_desc',$meta_desc)
        ->with('meta_keywords',$meta_keywords)->with('meta_title',$meta_title)->with('url_canonical',$url_canonical);
        }
}
