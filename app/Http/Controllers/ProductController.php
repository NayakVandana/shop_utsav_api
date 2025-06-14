<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $products = Product::with('category')->get();
            return $this->sendJsonResponse(true, 'Products retrieved successfully', $products);
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'mrp' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'status' => 'nullable|string|in:active,inactive',
                'image' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            DB::beginTransaction();

            $product = Product::create($request->all());

            DB::commit();

            return $this->sendJsonResponse(true, 'Product created successfully', $product, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e);
        }
    }

    public function update(Request $request, $uuid)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'mrp' => 'required|numeric|min:0',
                'stock' => 'required|integer|min:0',
                'category_id' => 'required|exists:categories,id',
                'status' => 'nullable|string|in:active,inactive',
                'image' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            DB::beginTransaction();

            $product = Product::where('uuid', $uuid)->firstOrFail();
            $product->update($request->all());

            DB::commit();

            return $this->sendJsonResponse(true, 'Product updated successfully', $product);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e);
        }
    }

    public function destroy($uuid)
    {
        try {
            $product = Product::where('uuid', $uuid)->firstOrFail();
            $product->delete();
            return $this->sendJsonResponse(true, 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }
}