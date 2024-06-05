<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductColorRequest;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsColors extends Controller
{
    public function index(string $id)
    {
        $product = Product::find($id);
        return view('backend.products-colors.index',compact('product'));
    }

    public function create(string $id)
    {
        $colors = Color::all();
        $product = Product::find($id);

        return view('backend.products-colors.create',compact(['product', 'colors']));
    }

    public function store(StoreProductColorRequest $request, string $id)
    {
        $product = Product::find($id);
        $product->colors()->attach($request->validated('color_id'), ['path' => $request->validated('path')]);

        return redirect()->route('products-colors.index', $product->id);
    }

    public function delete(string $id, string $colorId)
    {
        $product = Product::find($id);
        $product->colors()->detach($colorId);

        return redirect()->route('products-colors.index', $product->id);
    }
}
