<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $categories = Category::all();
            return $this->sendJsonResponse(true, 'Categories retrieved successfully', $categories);
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
                'status' => 'nullable|string|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            DB::beginTransaction();

            $category = Category::create($request->all());

            DB::commit();

            return $this->sendJsonResponse(true, 'Category created successfully', $category, 201);
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
                'status' => 'nullable|string|in:active,inactive',
            ]);

            if ($validator->fails()) {
                return $this->sendJsonResponse(false, $validator->errors()->first(), [], 422);
            }

            DB::beginTransaction();

            $category = Category::where('uuid', $uuid)->firstOrFail();
            $category->update($request->all());

            DB::commit();

            return $this->sendJsonResponse(true, 'Category updated successfully', $category);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e);
        }
    }

    public function destroy($uuid)
    {
        try {
            $category = Category::where('uuid', $uuid)->firstOrFail();
            $category->delete();
            return $this->sendJsonResponse(true, 'Category deleted successfully');
        } catch (\Exception $e) {
            return $this->sendError($e);
        }
    }
}