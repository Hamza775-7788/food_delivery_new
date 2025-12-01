<?php

namespace App\Http\Controllers;

use App\Services\OrderStatusService;
use App\Http\Resources\OrderResources;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\OrderView;
use App\Models\OrderDetails;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    private $statusService;

    public function __construct(OrderStatusService $statusService)
    {
        $this->statusService = $statusService;
    }
    public function showByID(int $id)
    {
        $order = Order::where('id', $id)
            ->with(['orderDetils', 'orderStatus', 'orderDetils.Product', 'user'])
            ->orderBy('id', 'desc')
            ->get();

        if (!$order) return response()->json(["message" => "Order not found"], 404);
        return response()->json($order);
    }

    public function show(Request $request)
    {

        $data = Order::where('user_id', Auth::user()->id)
            ->with(['orderDetils', 'orderStatus', 'orderDetils.Product', 'user'])
            ->orderBy('id', 'desc')
            ->get();
        return response()->json(['data' => $data]);
    }
    public function showWithStats(Request $request, $id)
    {

        $data = Order::where("user_id", Auth::user()->id)->where("orderStatus_id", $id)->with(['orderDetils', 'orderStatus', 'orderDetils.Product', "user"])->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data]);
    }

    public function index()
    {
        $data = Order::with(['orderDetils', 'orderStatus', 'orderDetils.Product', "user"])->orderBy('id', 'desc')->get();
        return response()->json(['data' => $data]);
    }

    public function addOrder(Request $request)
    {
        $request->validate([
            "address" => 'required',
            'totle' => 'required',
            'orderStatus_id' => "required",
            'pieces_number' => "required",
            'order_detils' => "required",
        ]);

        $order = Order::create([
            "address" => $request->address,
            "totle" => $request->totle,
            "orderStatus_id" => $request->orderStatus_id,
            "pieces_number" => $request->pieces_number,
            "user_id" => Auth::user()->id,
        ]);

        foreach ($request->order_detils as $item) {
            OrderDetails::create([
                "order_id" => $order->id,
                "product_id" => $item['product_id'],
                "uint_price" => $item['uint_price'],
                "quntity" => $item['quntity'],
                "totle" => $item['totle'],
            ]);
        }

        return response()->json(['order_id' => $order->id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "address" => 'required',
            'totle' => 'required',
            'orderStatus_id' => "required",
            'pieces_number' => "required",
            'order_detils' => "required",
        ]);

        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        $order->update([
            "address" => $request->address,
            "totle" => $request->totle,
            "orderStatus_id" => $request->orderStatus_id,
            "pieces_number" => $request->pieces_number,
            "user_id" => Auth::user()->id,
        ]);

        OrderDetails::where("order_id", $order->id)->delete();

        foreach ($request->order_detils as $item) {
            OrderDetails::create([
                "order_id" => $order->id,
                "product_id" => $item['product_id'],
                "uint_price" => $item['uint_price'],
                "quntity" => $item['quntity'],
                "totle" => $item['totle'],
            ]);
        }

        return response()->json(["order_id" => $order->id]);
    }

    private function updateStatus(Order $order, int $newStatusId, bool $isAdmin = false)
    {
        if (!$this->statusService->canChangeStatus($order->orderStatus_id, $newStatusId, $isAdmin)) {
            return response()->json([
                "message" => "Status change not allowed"
            ], 400);
        }

        $order->orderStatus_id = $newStatusId;
        $order->save();

        return response()->json([
            "message" => "Order status updated successfully",
            "order" => $order
        ]);
    }

    // ====== دوال المستخدم ======
    public function cancelOrder(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        if ($order->user_id != $request->user()->id) {
            return response()->json(["message" => "Forbidden"], 403);
        }

        return $this->updateStatus($order, OrderStatusService::STATUS['cancelled'], false);
    }

    // ====== دوال الإدارة ======
    public function acceptOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        return $this->updateStatus($order, OrderStatusService::STATUS['accepted'], true);
    }

    public function rejectOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        return $this->updateStatus($order, OrderStatusService::STATUS['rejected'], true);
    }

    public function shipOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        return $this->updateStatus($order, OrderStatusService::STATUS['shipped'], true);
    }

    public function deliverOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        return $this->updateStatus($order, OrderStatusService::STATUS['delivered'], true);
    }

    public function returnOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        return $this->updateStatus($order, OrderStatusService::STATUS['returned'], true);
    }

    public function deleteOrder($id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(["message" => "Order not found"], 404);

        return $this->updateStatus($order, OrderStatusService::STATUS['deleted'], true);
    }
}
