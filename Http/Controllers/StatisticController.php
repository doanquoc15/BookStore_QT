<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use DB;
use Session;
use App\Http\Requests;

use Excel;
use App\orders;

session_start();

class StatisticController extends Controller
{
    public function AuthLogin(){
        $admin_id = Session::get('admin_id');
        if($admin_id){
            return Redirect::to('dashboard');
        }else{
            return Redirect::to('admin')->send();
        }
    }

    public function show_statistic_day(){
        return view('admin.statistic.day_order');
    }

    public function show_statistic_time(){
        return view('admin.statistic.time_order');
    }

    public function show_order_day(Request $request){
        $this->AuthLogin();

        $day   = str_pad($request->day, 2, '0', STR_PAD_LEFT);
        $month = str_pad($request->month, 2, '0', STR_PAD_LEFT);
        $year  = $request->year;

        if($day && $month && $year)
        {
            $all_order = DB::table('orders')->where(DB::raw("DATE_FORMAT(created_at, '%d-%m-%Y')"),"$day-$month-$year")->get();
            Session::put('message_statistic','Số liệu thống kê ngày: '. $day.'/'. $month. '/'.$year);
            return view('admin.statistic.day_order')->with('all_order',$all_order);

        }
        return view('admin.statistic.day_order');
    }

    public function show_order_time(Request $request){
        $this->AuthLogin();

        $fromDate = $request->date_start;
        $toDate   = $request->date_end;

        $all_order = DB::table('orders')
            ->whereBetween('created_at', [$fromDate, $toDate])->get();
        Session::put('message_statistic','Số liệu thống kê trong thời gian: '. $fromDate.' Đến '. $toDate);
        return view('admin.statistic.time_order')->with('all_order',$all_order);
    }

    public function export_day_statistic(){
        return Excel::download(new ExcelExports , 'day_statistic.xlsx');
    }

    public function export_time_statistic(){
        return Excel::download(new ExcelExports , 'time_statistic.xlsx');
    }

    public function statistic_chart(){
        $num1 = 0;
        $num2 = 0;
        $num3 = 0;

        $resultNotApproved  = DB::table('orders')
            ->Where('orders.order_status', 1)
            ->get();

        if (isset($resultNotApproved)){
            foreach ($resultNotApproved as $result){
                if(isset($result)){ $num1 ++;}
            }
            Session::put('resultNotApproved',$num1);
        }

        $resultApproved = DB::table('orders')
            ->Where('orders.order_status', 2)
            ->get();

        if (isset($resultApproved)){
            foreach ($resultApproved as $result){
                if(isset($result)){ $num2 ++;}
            }
            Session::put('resultApproved',$num2);
        }

        $resultCancelOrder = DB::table('orders')
            ->Where('orders.order_status', 3)
            ->get();

        if (isset($resultCancelOrder)){
            foreach ($resultCancelOrder as $result){
                if(isset($result)){ $num3 ++;}
            }
            Session::put('resultCancelOrder',$num3);
        }

        return view('admin.statistic.chart')
            ->with('resultNotApproved',$resultNotApproved)
            ->with('resultApproved',$resultApproved)
            ->with('resultCancelOrder',$resultCancelOrder);
    }

}
