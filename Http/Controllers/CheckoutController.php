<?php

namespace App\Http\Controllers;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use App\Http\Requests;

/*use Gloudemans\Shoppingcart\Cart;*/


use App\social;

//sử dụng model Social
use mysql_xdevapi\Table;
use Socialite;

//sử dụng Socialite
use App\loginCustomer;

//sử dụng model Login


session_start();

class CheckoutController extends Controller
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

    public function manage_order()
    {
        $this->AuthLogin();
        $all_order = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.customer_id')
            ->select('orders.*', 'customer.customer_name')
            ->orderby('orders.order_id', 'desc')->paginate(4);
        $manager_order = view('admin.order.manage_order')->with('all_order', $all_order);
        return view('admin_layout')->with('admin.order.manage_order', $manager_order);
    }

    public function details_order($order_id)
    {
        $this->AuthLogin();
        $order_details = DB::table('order_details')->where('order_id', $order_id)->get();
        $order = DB::table('orders')->where('order_id', $order_id)->get();

        foreach ($order as $key => $order) {
            $customer_id = $order->customer_id;
            $shipping_id = $order->shipping_id;
        }

        $customer = DB::table('customer')->where('customer_id', $customer_id)->first();
        $shipping_order_details = DB::table('shipping')->where('shipping_id', $shipping_id)->first();

        return view('admin.order.details_order')
            ->with(compact('order_details', 'customer', 'order', 'shipping_order_details'));
    }

    public function edit_order($order_id)
    {
        $edit_order = DB::table('orders')->where('order_id', $order_id)->get();

        $edit_shipper_order = DB::table('shipping')->get();

        $manager_order_edit = view('admin.order.edit_order')->with('edit_order', $edit_order)->with('shipper_order', $edit_shipper_order);

        return view('admin_layout')->with('admin.order.edit_order', $manager_order_edit);
    }

    public function update_order(Request $request, $order_id)
    {
        $data = array();
        $data['order_status'] = $request->select_order_status;
        $data['shipping_id'] = $request->select_shipping_order;

        DB::table('orders')->where('order_id', $order_id)->update($data);
        Session::put('message', 'Đã cập nhật một đơn hàng.');
        return Redirect::to('/manage-order');

    }

    public function delete_order($order_id)
    {
        DB::table('orders')->where('order_id', $order_id)->delete();
        Session::put('message', 'Đã xóa một đơn hàng.');
        return Redirect::to('/manage-order');
    }

    //Client
    public function login_checkout(Request $request)
    {
        $meta_desc = "Đăng nhập thanh toán";
        $meta_keywords = "Đăng nhập thanh toán";
        $meta_title = "Đăng nhập thanh toán";
        $url_canonical = $request->url();

        $cate_product = DB::table('category')->where('category_status', '0')->orderby('category_id', 'desc')->get();
        $brand_product = DB::table('book_author')->where('bookAuthor_status', '0')->orderby('bookAuthor_id', 'desc')->get();
        return view('page.checkout.login_checkout')->with('category', $cate_product)->with('author', $brand_product)->with('meta_desc', $meta_desc)
            ->with('meta_keywords', $meta_keywords)->with('meta_title', $meta_title)->with('url_canonical', $url_canonical);
    }

    public function add_customer(Request $request)
    {
        $data = array();
        $data['customer_name'] = $request->customer_name;
        $data['customer_phone'] = $request->customer_phone;
        $data['customer_email'] = $request->customer_email;
        $data['customer_password'] = md5($request->customer_password);
        $data['customer_status'] = 1;
        $customer_id = DB::table('customer')->insertGetId($data);
        Session::put('customer_id', $customer_id);
        Session::put('customer_name', $request->customer_name);
        Session::put('message', 'Bạn đã đăng ký thành công! Mời đăng nhập.');
        return Redirect::to('/loginCustomer-checkout');
    }

    public function checkout(Request $request)
    {
        $meta_desc = "Đăng nhập thanh toán";
        $meta_keywords = "Đăng nhập thanh toán";
        $meta_title = "Đăng nhập thanh toán";

        $url_canonical = $request->url();
        $cate_product = DB::table('category')->where('category_status', '0')->orderby('category_id', 'desc')->get();
        $book_author = DB::table('book_author')->where('bookAuthor_status', '0')->orderby('bookAuthor_id', 'desc')->get();
        // $city = City::orderby('matp', 'ASC')->get();
        return view('page.checkout.show_checkout')->with('category', $cate_product)->with('author', $book_author)->with('meta_desc', $meta_desc)
            ->with('meta_keywords', $meta_keywords)->with('meta_title', $meta_title)->with('url_canonical', $url_canonical); //->with('city', $city);
    }

    // call api facebook
    public function login_facebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function callback_facebook()
    {
        $provider = Socialite::driver('facebook')->user();
        $account = Social::where('provider', 'facebook')->where('provider_user_id', $provider->getId())->first();
        if ($account) {
            //loginCustomer in vao trang client
            $account_name = Login::where('customer_id', $account->user)->first();
            Session::put('admin_login', $account_name->admin_name);
            Session::put('customer_id', $account_name->admin_id);
            return redirect('/')->with('message', 'Đăng nhập client thành công');
        } else {
            $social = new Social([
                'provider_user_id' => $provider->getId(),
                'provider' => 'facebook'
            ]);

            $orang = Login::where('customer_email', $provider->getEmail())->first();

            if (!$orang) {
                $orang = Login::create([
                    'customer_name' => $provider->getName(),
                    'customer_email' => $provider->getEmail(),
                    'customer_password' => '',
                    'customer_phone' => '',
                    'customer_status' => 1
                ]);
            }
            $social->login()->associate($orang);
            $social->save();

            $account_name = Login::where('customer_id', $account->user)->first();

            Session::put('admin_login', $account_name->customer_name);
            Session::put('customer_id', $account_name->customer_id);

            return redirect('/')->with('message', 'Đăng nhập Client thành công');
        }
    }


    public function login_customer(Request $request)
    {
        $email = $request->email_account;
        $password = md5($request->password_account);
        $result = DB::table('customer')->where('customer_email', $email)->where('customer_password', $password)->first();

        if (DB::table('customer')->where('customer_email', $email)->where('customer_password', $password)->where('customer_status', 0)->first()) {
            Session::put('message', 'Tài khoản bị khóa.');
            return redirect('/loginCustomer-checkout');
        } else if ($result) {
            Session::put('customer_id', $result->customer_id);
            Session::put('customer_name', $result->customer_name);
            return Redirect::to('/');
        } else {
            Session::put('message', 'Email hoặc mật khẩu của bạn không đúng.');
            return Redirect::to('/loginCustomer-checkout');
        }

        /*$data = $request->all();
        $customer_email = $data['customer_email'];
        $customer_password = md5($data['customer_password']);
        $login  =Login::where('customer_email',$customer_email)-where('$customer_password',$customer_password)->first();
        $login_count = $login->count();

        if($login_count){
            Session::put('customer_name', $login->customer_name);
            Session::put('customer_id', $login->customer_id);
            return Redirect::to('/');
        } else{
            Session::put('message', 'Tài khoản hoặc mật khẩu sai! Vui lòng nhập lại.');
            return Redirect::to('/loginCustomer-checkout');
        }*/
    }

    public function logout_checkout()
    {
        Session::flush();
        return Redirect::to('/loginCustomer-checkout');
    }

    public function save_checkout_customer(Request $request)
    {
        $data = array();
        $data['shipping_name'] = $request->shipping_name;
        $data['shipping_phone'] = $request->shipping_phone;
        $data['shipping_email'] = $request->shipping_email;
        $data['shipping_notes'] = $request->shipping_notes;
        $data['shipping_address'] = $request->shipping_address;
        $data['shipping_method'] = $request->shipping_method;
        $shipping_id = DB::table('shipping')->insertGetId($data);

        Session::put('shipping_id', $shipping_id);

        return Redirect::to('/payment');
    }

    public function payment(Request $request)
    {
        $meta_desc = "Đăng nhập thanh toán";
        $meta_keywords = "Đăng nhập thanh toán";
        $meta_title = "Đăng nhập thanh toán";
        $url_canonical = $request->url();

        $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
        $book_author = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
        return view('page.checkout.payment')->with('category', $cate_product)->with('author', $book_author)
            ->with('meta_desc', $meta_desc)->with('meta_keywords', $meta_keywords)->with('meta_title', $meta_title)->with('url_canonical', $url_canonical);
    }

    public function order_place(Request $request)
    {
        //insert order
        $order_data = array();
        $order_data['customer_id'] = Session::get('customer_id');
        $order_data['shipping_id'] = "42";
        $order_data['payment_id'] = $request->payment_options;
        $order_data['shipping_method'] = $request->shipping_method;
        $order_data['order_note'] = $request->order_note;
        //
        $sumPrice = $request->sumPrice;
        if (isset($sumPrice)) {
            $order_data['order_total'] = $sumPrice;
        } else {
            $order_data['order_total'] = "10";
        }
        //
        $order_data['order_status'] = 1;
        $order_data['created_at'] = now();
        $order_id = DB::table('orders')->insertGetId($order_data);

        $content = Cart::content();

        $check = 0;
        foreach ($content as $v_content) {
            $resultProduct = DB::table('product')->where('product_id',$v_content->id)->value('product_quantity');
            if($v_content->qty > $resultProduct){
                $check +=1;
            }
        }
        if ($check == 0){
            //insert order_details
            foreach ($content as $v_content) {
                $order_d_data['order_id'] = $order_id;
                $order_d_data['product_id'] = $v_content->id;
                $order_d_data['product_name'] = $v_content->name;
                $order_d_data['product_price'] = $v_content->price;
                $order_d_data['product_sales_quantity'] = $v_content->qty;
                DB::table('order_details')->insert($order_d_data);
            }

            // cập nhật số lượng sản phẩm
            foreach ($content as $v_content) {
                $result = DB::table('product')->where('product_id', $v_content->id)->value('product_quantity');
                $quantity = $result - $v_content->qty;
                $product_data = array();
                $product_data['product_quantity'] = $quantity;

                DB::table('product')->where('product_id', $v_content->id)->update($product_data);
            }

            Cart::destroy(Cart::content());
            return Redirect::to('/');
        }
        else{
            Session::put('message', 'Xin Lỗi! Không đủ sản phẩm trong kho, Vui lòng chọn lại.');
            return Redirect::to('/show-cart');
        }

    }

    public function user_setting(Request $request)
    {
        $customer_id = Session::get('customer_id');
        $meta_desc = "Đăng nhập thanh toán";
        $meta_keywords = "Đăng nhập thanh toán";
        $meta_title = "Đăng nhập thanh toán";
        $url_canonical = $request->url();
        //--seo
        $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
        $brand_product = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
        $manager_order = DB::table('orders')
            ->join('customer', 'orders.customer_id', '=', 'customer.customer_id')
            ->select('orders.*', 'customer.customer_name')->where('orders.customer_id', $customer_id)
            ->orderby('orders.order_id', 'desc')->get();

        return view('page.user.user_setting')->with('all_order', $manager_order)->with('category', $cate_product)->with('brand', $brand_product)
            ->with('meta_desc', $meta_desc)->with('meta_keywords', $meta_keywords)->with('meta_title', $meta_title)->with('url_canonical', $url_canonical);
    }

    public function view_order_user(Request $request, $order_id)
    {
        $customer_id = Session::get('customer_id');
        $meta_desc = "Đăng nhập thanh toán";
        $meta_keywords = "Đăng nhập thanh toán";
        $meta_title = "Đăng nhập thanh toán";
        $url_canonical = $request->url();
        //--seo
        $cate_product = DB::table('category')->where('category_status', '1')->orderby('category_id', 'desc')->get();
        $brand_product = DB::table('book_author')->where('bookAuthor_status', '1')->orderby('bookAuthor_id', 'desc')->get();
        $order_details = DB::table('order_details')->where('order_id', $order_id)->get();
        $order = DB::table('orders')->where('order_id', $order_id)->get();
        foreach ($order as $key => $ord) {
            $customer_id = $ord->customer_id;
            $shipping_id = $ord->shipping_id;
            $order_status = $ord->order_status;
        }
        $customer = DB::table('customer')->where('customer_id', $customer_id)->first();
        $shipping = DB::table('shipping')->where('shipping_id', $shipping_id)->first();
        //     $order_details_product = OrderDetails::with('product')->where('order_id',
        //    $order_id)->get();

        return view('page.user.order_detail_user')->with(compact('order_details', 'customer', 'shipping', 'order'))
            ->with('category', $cate_product)->with('author', $brand_product)->with('meta_desc', $meta_desc)->with('meta_keywords', $meta_keywords)
            ->with('meta_title', $meta_title)->with('url_canonical', $url_canonical)->with('order_status', $order_status);
    }

    public function change_register()
    {
        return view("page.checkout.register_checkout");
    }

    public function login()
    {
        return view("page.checkout.login_checkout");
    }
}
