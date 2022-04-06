<?php

namespace App\Http\Controllers;

use App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

session_start();

class ShipperController extends Controller
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

    public function all_shipping(Request $request)
    {
        $all_shipper = DB::table('shipping')->paginate(4);
        $manager_shipper = view('admin.shipping.all_shipping')->with('all_shipping', $all_shipper);
        return view('admin_layout')->with('admin.shipping.all_shipping', $manager_shipper);
    }

    public function search_customer(Request $request)
    {

    }

    public function block_shipping($shipping_id)
    {
        $this->AuthLogin();
        DB::table('shipping')->where('shipping_id', $shipping_id)->update(['shipping_method' => 0]);
        Session::put('message', 'Đã khóa tài khoản này.');
        return Redirect::to('all-shipping');

    }

    public function active_shipping($shipping_id)
    {
        $this->AuthLogin();
        DB::table('shipping')->where('shipping_id', $shipping_id)->update(['shipping_method' => 1]);
        Session::put('message', 'Đã kích hoạt tài khoản này.');
        return Redirect::to('all-shipping');
    }

    public function edit_shipping($shipping_id)
    {
        $this->AuthLogin();
        $edit_shipping = DB::table('shipping')->where('shipping_id', $shipping_id)->get();

        $manager_shipping = view('admin.shipping.edit_shipping')->with('edit_shipping', $edit_shipping);

        return view('admin_layout')->with('admin.shipping.edit_shipping', $manager_shipping);
    }

    public function update_shipping(Request $request, $shipping_id)
    {
        $this->AuthLogin();
        $data = array();
        $data['shipping_name'] = $request->shipping_name;
        $data['shipping_address'] = $request->shipping_address;
        $data['shipping_phone'] = $request->shipping_phone;
        $data['shipping_email'] = $request->shipping_email;
        $data['shipping_method'] = $request->shipping_status;

        DB::table('shipping')->where('shipping_id', $shipping_id)->update($data);
        Session::put('message', 'Đã cập nhật một tài khoản người dùng.');
        return Redirect::to('all-shipping');
    }

    public function add_shipping(){
        $this->AuthLogin();
        return view('admin.shipping.add_shipping');

    }
    public function save_shipping(Request $request){
        $this->AuthLogin();
        $data = array();
        $data['shipping_name'] = $request->shipping_name;
        $data['shipping_address'] = $request->shipping_address;
        $data['shipping_phone'] = $request->shipping_phone;
        $data['shipping_email'] = $request->shipping_email;
        $data['shipping_method'] = $request->shipping_stutus;

        DB::table('shipping')->insert($data);
        Session::put('message','Đã thêm một người giao hàng.');
        return Redirect::to('all-shipping');
    }

    public function delete_shipping($shipping_id){
        $this->AuthLogin();
        DB::table('shipping')->where('shipping_id',$shipping_id)->delete();
        Session::put('message','Đã xóa một người giao hàng.');
        return Redirect::to('all-shipping');
    }


}
