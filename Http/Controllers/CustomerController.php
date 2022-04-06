<?php

namespace App\Http\Controllers;

use App\Http\Requests;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

session_start();

class CustomerController extends Controller
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

    public function all_customer(Request $request)
    {
        $this->AuthLogin();
        $customer = $request->customer_search;
        if (!empty($customer)) {
            $all_customer = DB::table('customer')->Where('customer_id', 'LIKE', "%{$customer}%")->paginate(4);
            $manager_customer = view('admin.customer.all_customer')->with('all_customer', $all_customer);
            return view('admin_layout')->with('admin.customer.all_customer', $manager_customer);
        }

        $all_customer = DB::table('customer')->paginate(4);
        $manager_customer = view('admin.customer.all_customer')->with('all_customer', $all_customer);
        return view('admin_layout')->with('admin.customer.all_customer', $manager_customer);
    }

    public function search_customer(Request $request)
    {
        $output = '';
        $all_customer = DB::table('customer')
            ->Where('customer_name', 'LIKE', '%' .$request->keyword. '%')->get();
        $i = 1;
        foreach ($all_customer as $customer) {
            $output = '
                <tr>
                <td>'."1".'</td>
                    <td>'.$customer->customer_name.'</td>
                        <td>'.$customer->customer_email .'</td>
                        <td>'.$customer->customer_password .'</td>
                        <td>'.$customer->customer_status .'</td>
                    </tr>';
        }
        return response()->json($output);
    }

    public function block_user($customer_id)
    {
        $this->AuthLogin();
        DB::table('customer')->where('customer_id', $customer_id)->update(['customer_status' => 0]);
        Session::put('message', 'Đã khóa tài khoản này.');
        return Redirect::to('all-customer');

    }

    public function active_user($customer_id)
    {
        $this->AuthLogin();
        DB::table('customer')->where('customer_id', $customer_id)->update(['customer_status' => 1]);
        Session::put('message', 'Đã kích hoạt tài khoản này.');
        return Redirect::to('all-customer');
    }

    public function edit_customer($customer_id)
    {
        $this->AuthLogin();
        $edit_customer = DB::table('customer')->where('customer_id', $customer_id)->get();

        $manager_customer = view('admin.customer.edit_customer')->with('edit_customer', $edit_customer);

        return view('admin_layout')->with('admin.customer.edit_customer', $manager_customer);
    }

    public function update_customer(Request $request, $customer_id)
    {
        $this->AuthLogin();
        $data = array();
        $data['customer_name'] = $request->customer_name;
        $data['customer_email'] = $request->customer_email;
        $data['customer_password'] = $request->customer_password;
        $data['customer_phone'] = $request->customer_phone;
        $data['customer_address'] = $request->customer_address;

        DB::table('customer')->where('customer_id', $customer_id)->update($data);
        Session::put('message', 'Đã cập nhật một tài khoản người dùng.');
        return Redirect::to('all-customer');
    }
}
