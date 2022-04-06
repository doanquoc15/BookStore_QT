<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use League\Flysystem\File;
use App\feedback;
session_start();

class ProductController extends Controller
{
    public function AuthLogin()
    {
        $admin_id = Session::get('admin_id');
        if ($admin_id) {
            return Redirect::to('dashboard');
        } else {
            return Redirect::to('admin')->send();
        }
    }

    public function add_product()
    {
        $this->AuthLogin();
        $category_product = DB::table('category')->ORDERBY('category_id', 'desc')->get();
        $bookAuthor_product = DB::table('book_author')->ORDERBY('bookAuthor_id', 'desc')->get();

        return view('admin.product.add_product')->with('category_product', $category_product)->with('bookAuthor_product', $bookAuthor_product);
    }

    public function all_product()
    {
        $this->AuthLogin();
        $all_product = DB::table('product')->join('category', 'category.category_id', '=', 'product.category_id')
            ->join('book_author', 'book_author.bookAuthor_id', '=', 'product.bookAuthor_id')
            ->orderby('product.product_id', 'desc')->paginate(4);
        $manager_product = view('admin.product.all_product')->with('all_product', $all_product);
        return view('admin_layout')->with('admin.product.all_product', $manager_product);
    }

    public function search_product(Request $request)
    {
        $product = $request->search_product;
        if (!empty($product)) {
            $all_product = DB::table('product')
                ->join('category', 'category.category_id', '=', 'product.category_id')
                ->join('book_author', 'book_author.bookAuthor_id', '=', 'product.bookAuthor_id')
                ->orderby('product.product_id', 'desc')
                ->Where('product_name', 'LIKE', "%{$product}%")
                ->paginate(4);
            $manager_product = view('admin.product.all_product')->with('all_product', $all_product);
            return view('admin_layout')->with('admin.product.all_product', $manager_product);
        }

        $all_product = DB::table('product')->join('category', 'category.category_id', '=', 'product.category_id')
            ->join('book_author', 'book_author.bookAuthor_id', '=', 'product.bookAuthor_id')
            ->orderby('product.product_id', 'desc')->paginate(4);
        $manager_product = view('admin.product.all_product')->with('all_product', $all_product);
        return view('admin_layout')->with('admin.product.all_product', $manager_product);
    }

    public function save_product(Request $request)
    {
        $this->AuthLogin();
        $data = array();
        $data['category_id'] = $request->product_category;
        $data['bookAuthor_id'] = $request->product_bookAuthor;
        $data['product_name'] = $request->product_name;
        $data['product_desc'] = $request->product_desc;
        $data['product_quantity'] = $request->product_quantity;
        $data['product_price'] = $request->product_price;
        $data['product_status'] = $request->product_status;
        $data['product_slug'] = $request->product_slug;

        $get_image = $request->file('product_image');

        if ($get_image) {
            // lấy tên hình ảnh
            $get_name_image = $get_image->getClientOriginalName();
            // lấy tên ko lấy đuôi trước dấu chấm
            $name_image = current(explode('.', $get_name_image));
            // random 1 số và nối theo thứ tự: tên - so random - cham duoi - tránh trùng tên
            $new_image = $name_image . rand(0, 500) . '.' . $get_image->getClientOriginalExtension();
            // lưu ảnh vào thư mục
            $get_image->move('public/uploads/product', $new_image);

            // lưu đường dẫn vào db
            $data['product_image'] = $new_image;

            DB::table('product')->insert($data);
            Session::put('message', 'Đã thêm một sản phẩm.');
            return Redirect::to('all-product');
        }
        DB::table('product')->insert($data);
        Session::put('message', 'Đã thêm một sản phẩm.');
        return Redirect::to('all-product');
    }

    public function unactive_product($product_id)
    {
        $this->AuthLogin();
        DB::table('product')->where('product_id', $product_id)->update(['product_status' => 0]);
        Session::put('message', 'Đã ẩn sản phẩm này.');
        return Redirect::to('all-product');

    }
    public function active_product($product_id)
    {
        $this->AuthLogin();
        DB::table('product')->where('product_id', $product_id)->update(['product_status' => 1]);
        Session::put('message', 'Đã hiển thị sản phẩm này.');
        return Redirect::to('all-product');
    }

    public function edit_product($product_id)
    {
        $this->AuthLogin();
        $category_product = DB::table('category')->ORDERBY('category_id', 'desc')->get();
        $bookAuthor_product = DB::table('book_author')->ORDERBY('bookAuthor_id', 'desc')->get();

        $edit_product = DB::table('product')->where('product_id', $product_id)->get();
        $manager_product = view('admin.product.edit_product')->with('edit_product', $edit_product)
            ->with('category_product', $category_product)
            ->with('bookAuthor_product', $bookAuthor_product);

        return view('admin_layout')->with('admin.product.edit_product', $manager_product);
    }

    public function update_product(Request $request, $product_id)
    {
        $this->AuthLogin();
        $data = array();
        $data['category_id'] = $request->product_category;
        $data['bookAuthor_id'] = $request->product_bookAuthor;
        $data['product_name'] = $request->product_name;
        $data['product_desc'] = $request->product_desc;
        $data['product_quantity'] = $request->product_quantity;
        $data['product_price'] = $request->product_price;
        $oldImage = $request->oldImage;
        $get_image = $request->file('product_image');

        if ($get_image) {
            $get_name_image = $get_image->getClientOriginalName();
            if ($get_name_image == $oldImage) {
                File::delete('public/uploads/product/' . $oldImage);
            }
            $name_image = current(explode('.', $get_name_image));
            $new_image = $name_image . rand(0, 500) . '.' . $get_image->getClientOriginalExtension();
            $get_image->move('public/uploads/product', $new_image);
            $data['product_image'] = $new_image;

            DB::table('product')->where('product_id', $product_id)->update($data);
            Session::put('message', 'Đã cập nhật một sản phẩm.');
            return Redirect::to('all-product');
        }

        DB::table('product')->where('product_id', $product_id)->update($data);
        Session::put('message', 'Đã cập nhật một sản phẩm.');
        return Redirect::to('all-product');
    }

    public function delete_product($product_id)
    {
        $this->AuthLogin();
        DB::table('product')->where('product_id', $product_id)->delete();
        Session::put('message', 'Đã xóa một sản phẩm.');
        return Redirect::to('all-product');
    }

    //Client
      //Client
      public function details_product($product_slug , Request $request){
        $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
        $bookAuthor_product = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
        $details_product = DB::table('product') ->join('category', 'category.category_id', '=', 'product.category_id')
         ->join('book_author', 'book_author.bookAuthor_id', '=', 'product.bookAuthor_id')
         ->where('product.product_slug', $product_slug)->get();
        foreach ($details_product as $key => $value) {
            $product_id = $value->product_id;
            $category_id = $value->category_id;
            $meta_desc = $value->product_desc;
            $meta_keywords = $value->product_slug;
            $meta_title = $value->product_name;
            $url_canonical = $request->url();
            //--seo
        }
        $star_number = feedback::where('product_id',$product_id)->avg('star_number');
        $star_number = round($star_number);
        $list_image = DB::table('product_image')->where('product_id',$product_id)->get();
        Session::put('product_id_session',$product_id);
        $all_comment = feedback::where('product_id',$product_id)->get();
        $related_product = DB::table('product')->join('category','category.category_id','=','product.category_id')
         ->join('book_author','book_author.bookAuthor_id','=','product.bookAuthor_id')
         ->where('category.category_id',$category_id)->whereNotIn('product.product_slug',[$product_slug])->get();
         return view('page.product.show_details')->with('category',$cate_product)->with('author',$bookAuthor_product)->with('product_details',$details_product)->with('relate',$related_product)
         ->with('meta_desc',$meta_desc)->with('meta_keywords',$meta_keywords)->with('meta_title',$meta_title)
         ->with('url_canonical',$url_canonical)->with('star_number',$star_number)->with('all_comment', $all_comment)->with('list_image',$list_image);
    }
}
