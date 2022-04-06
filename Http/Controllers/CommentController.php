<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CommentController extends Controller
{

    public function add_comment(Request $request)
    {

        $data = array();
        $product_id = Session::get('product_id_session');
        if (isset($product_id)) {
            $data['product_id'] = $product_id;
        } else {
            $data['product_id'] = 24;
        }

        $data['product_id'] = $product_id;
        $data['star_number'] = $request->star_number;
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['rating'] = $request->rating;
        DB::table('feedback')->insert($data);
        Session::put('message', 'Đã thêm một bình luận.');
        Session::put('product_id_session', null);
        return back();
    }
}
