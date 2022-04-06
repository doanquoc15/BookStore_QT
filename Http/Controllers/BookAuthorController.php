<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
session_start();

class BookAuthorController extends Controller
{
    public function AuthLogin(){
        $admin_id = Session::get('admin_id');
        if($admin_id){
            return Redirect::to('dashboard');
        }else{
            return Redirect::to('admin')->send();
        }
    }

    public function add_book_author(){
        $this->AuthLogin();
        return view('admin.bookAuthor.add_book_author');
    }

    public function all_book_author(){
        $this->AuthLogin();
        $all_book_author = DB::table('book_author')->paginate(4);
        $manager_book_author  = view('admin.bookAuthor.all_book_author')->with('all_book_author',$all_book_author);
        return view('admin_layout')->with('admin.bookAuthor.all_book_author', $manager_book_author);
    }

    public function search_bookAuthor(Request $request){
        $book_author = $request->search_bookAuthor;
        if(!empty($book_author)){
            $all_book_author = DB::table('book_author')->Where('bookAuthor_name','LIKE', "%{$book_author}%")->paginate(4);
            $manager_book_author  = view('admin.bookAuthor.all_book_author')->with('all_book_author',$all_book_author);
            return view('admin_layout')->with('admin.bookAuthor.all_book_author', $manager_book_author);
        }

        $all_book_author = DB::table('book_author')->paginate(4);
        $manager_book_author  = view('admin.bookAuthor.all_book_author')->with('all_book_author',$all_book_author);
        return view('admin_layout')->with('admin.bookAuthor.all_book_author', $manager_book_author);
    }

    public function save_book_author(Request $request){
        $this->AuthLogin();
        $data = array();
        $data['bookAuthor_name'] = $request->bookAuthor_name;
        $data['bookAuthor_slug'] = $request->bookAuthor_slug;
        $data['bookAuthor_desc'] = $request->bookAuthor_desc;
        $data['bookAuthor_status'] = $request->bookAuthor_status;

        DB::table('book_author')->insert($data);
        Session::put('message','Đã thêm một tác giả.');
        return Redirect::to('all-book-author-product');
    }

    public function unactive_book_author($bookAuthor_id){
        $this->AuthLogin();
        DB::table('book_author')->where('bookAuthor_id',$bookAuthor_id)->update(['bookAuthor_status'=>0]);
        Session::put('message','Đã ẩn tác giả này.');
        return Redirect::to('all-book-author-product');

    }
    public function active_book_author($bookAuthor_id){
        $this->AuthLogin();
        DB::table('book_author')->where('bookAuthor_id',$bookAuthor_id)->update(['bookAuthor_status'=>1]);
        Session::put('message','Đã hiển thị tác giả này.');
        return Redirect::to('all-book-author-product');
    }

    public function edit_book_author($bookAuthor_id){
        $this->AuthLogin();
        $edit_bookAuthor = DB::table('book_author')->where('bookAuthor_id',$bookAuthor_id)->get();

        $manager_bookAuthor  = view('admin.bookAuthor.edit_book_author')->with('edit_book_author',$edit_bookAuthor);

        return view('admin_layout')->with('admin.bookAuthor.edit_book_author', $manager_bookAuthor);
    }

    public function update_book_author(Request $request,$bookAuthor_id){
        $this->AuthLogin();
        $data = array();
        $data['bookAuthor_name'] = $request->bookAuthor_name;
        $data['bookAuthor_desc'] = $request->bookAuthor_desc;

        DB::table('book_author')->where('bookAuthor_id',$bookAuthor_id)->update($data);
        Session::put('message','Đã cập nhật một tác giả.');
        return Redirect::to('all-book-author-product');
    }

    public function delete_book_author($bookAuthor_id){
        $this->AuthLogin();
        DB::table('book_author')->where('bookAuthor_id',$bookAuthor_id)->delete();
        Session::put('message','Đã xóa một tác giả.');
        return Redirect::to('all-book-author-product');
    }


    //Client

    public function show_brand_home(Request $request, $bookAuthor_slug){
        // $cate_product = DB::table('category')->where('category_status','1')->orderby('category_id','desc')->get();
        // $bookAuthor_product = DB::table('book_author')->where('bookAuthor_status','1')->orderby('bookAuthor_id','desc')->get();
        // $bookAuthor_by_id = DB::table('product')->join('book_author','product.bookAuthor_id ','=','book_author.bookAuthor_id ')
        // ->where('book_author.bookAuthor_slug',$bookAuthor_slug)->get();
        // $bookAuthor_name = DB::table('book_author')->where('book_author.bookAuthor_slug',$bookAuthor_slug)->limit(1)->get();
        // return view('page.bookAuthor.show_brand')->with('category',$cate_product)->with('author',$bookAuthor_product)
        // ->with('bookAuthor_id ',$bookAuthor_by_id)->with('bookAuthor_name',$bookAuthor_name);

        $cate_product = DB::table('category')->where('category_status','1')->orderby('category_id','desc')->get();
         $brand_product = DB::table('book_author')->where('bookAuthor_status','1')->orderby('bookAuthor_id','desc')->get();
         $brand_by_id = DB::table('product')->join('book_author','product.bookAuthor_id','=','book_author.bookAuthor_id')->where('book_author.bookAuthor_slug',$bookAuthor_slug)->get();
         $brand_name = DB::table('book_author')->where('book_author.bookAuthor_slug',$bookAuthor_slug)->limit(1)->get();
         return view('page.bookAuthor.show_brand')->with('category',$cate_product)->with('author',$brand_product)->with('brand_by_id',$brand_by_id)->with('brand_name',$brand_name);
        }

}
