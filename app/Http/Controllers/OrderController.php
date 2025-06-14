<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $orders = Order::where('user_id', auth()->id())->with('product')->get();
            return $this->sendJsonResponse(true, 'Orders retrieved successfully', $orders);
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'cart_ids' => 'required|array',
                'cart_ids.*' => 'exists:carts,uuid',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            DB::beginTransaction();

            $carts = Cart::whereIn('uuid', $request->cart_ids)
                ->where('user_id', auth()->id())
                ->with('product')
                ->get();

            foreach ($carts as $cart) {
                $product = $cart->product;
                if ($product->stock < $cart->quantity) {
                    DB::rollBack();
                    return $this->sendJsonResponse(false, "Insufficient stock for product: {$product->name}", [], 422);
                }

                Order::create([
                    'user_id' => auth()->id(),
                    'product_id' => $cart->product_id,
                    'quantity' => $cart->quantity,
                    'total_price' => $cart->quantity * $product->price,
                    'status' => 'pending',
                ]);

                $product->decrement('stock', $cart->quantity);
                $cart->delete();
            }

            DB::commit();

            return $this->sendJsonResponse(true, 'Order placed successfully', []);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e);
        }
    }

    public function updateStatus(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:pending,confirmed,shipped,delivered,cancelled',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            $order = Order::where('uuid', $uuid)->firstOrFail();
            if (auth()->user()->role !== 'admin') {
                return $this->sendJsonResponse(false, 'Unauthorized', [], 401);
            }

            DB::beginTransaction();

            $order->update(['status' => $request->status]);

            DB::commit();

            return $this->sendJsonResponse(true, 'Order status updated', $order);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e);
        }
    }
}