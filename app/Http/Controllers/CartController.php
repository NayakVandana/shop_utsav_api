<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $carts = Cart::where('user_id', auth()->id())->with('product')->get();
            return $this->sendJsonResponse(true, 'Cart retrieved successfully', $carts);
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            $product = Product::findOrFail($request->product_id);
            if ($product->stock < $request->quantity) {
                return $this->sendJsonResponse(false, 'Insufficient stock', [], 422);
            }

            DB::beginTransaction();

            $cart = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);

            DB::commit();

            return $this->sendJsonResponse(true, 'Added to cart', $cart, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e);
        }
    }

    public function update(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            $cart = Cart::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();
            $product = Product::findOrFail($cart->product_id);
            if ($product->stock < $request->quantity) {
                return $this->sendJsonResponse(false, 'Insufficient stock', [], 422);
            }

            DB::beginTransaction();

            $cart->update(['quantity' => $request->quantity]);

            DB::commit();

            return $this->sendJsonResponse(true, 'Cart updated successfully', $cart);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e);
        }
    }

    public function destroy($uuid)
    {
        try {
            $cart = Cart::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();
            $cart->delete();
            return $this->sendJsonResponse(true, 'Item removed from cart');
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }
}