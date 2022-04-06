<?php

namespace App\Http\Controllers;


use App\Http\Requests;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
session_start();


class AdminController extends Controller
{
    public function AuthLogin(){
        $admin_id = Session::get('admin_id');
        if($admin_id){
            return Redirect::to('dashboard');
        }else{
            return Redirect::to('admin')->send();
        }
    }

    public function index(){
        return view('admin_login');
    }

    public function show_dashboard(){
        $this->AuthLogin();
        return view('admin.dashboard');
    }

    public function dashboard(Request $request){

        $admin_email = $request->admin_userName;
        $admin_password = md5($request->admin_password);

        $result = DB::table('admin')->where('admin_email',$admin_email)->where ('admin_password',$admin_password)->first();

        if($result){
            Session::put('admin_name',$result->admin_name);
            Session::put('admin_id',$result->admin_id);

            return Redirect::to('/dashboard');
        }else{
            Session::put('message','UserName hoặc Mật khẩu sai!');
            return Redirect::to('/admin'); // trả về trang admin
        }

        /*$data = $request->all();
        $admin_email = $data['admin_email'];
        $admin_password = md5($data['admin_password']);
        $loginCustomer  =Login::where('admin_email',$admin_email)-where('admin_password',$admin_password)->first();
        $login_count = $loginCustomer->count();

        if($login_count){
            Session::put('admin_name', $loginCustomer->admin_name);
            Session::put('admin_id', $loginCustomer->admin_id);
            return Redirect::to('/dashboard');
        } else{
            Session::put('message', 'Tài khoản hoặc mật khẩu sai! Vui lòng nhập lại.');
            return Redirect::to('/admin');
        }*/
    }

    public function logout(){
        Session::put('admin.name', null);
        Session::put('admin_id', null);
        return Redirect::to('/admin'); // trả về trang admin
    }
}
