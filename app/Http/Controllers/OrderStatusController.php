<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index()
    {

        $status = OrderStatus::all();

        return response()->json(["data" => $status]);
    }


    public function store(Request $request)
    {

        foreach ($request->status as $itme) {
            OrderStatus::create([
                "name" => $itme['name'],
                "id" => $itme['id']
            ]);
        }

        return response()->json(['message' => "success"]);
    }
}
