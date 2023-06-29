<?php

namespace App\Http\Controllers;

use App\Models\Carts;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::with(['galleries'])->latest()->get();
        return view('pages.frontend.index', compact('products'));
    }

    /**
     * Display a listing of the resource.
     */
    public function details(Request $request, $slug)
    {
        $products = Products::with(['galleries'])->where('slug', $slug)->firstOrFail();
        $recommendations = Products::with(['galleries'])->inRandomOrder()->limit(5)->get();
        return view('pages.frontend.details', compact('products', 'recommendations'));
    }

    public function cartDelete(Request $request, $id)
    {
        $item = Carts::findOrFail($id);
        $item->delete();

        return redirect('/cart');
    }

    public function cartAdd(Request $request, $id)
    {
        Carts::create([
            'user_id' => Auth::user()->id,
            'product_id' => $id,
        ]);

        return redirect('/cart');
    }

    public function cart(Request $request)
    {
        $carts = Carts::with(['product.galleries'])->where('user_id', Auth::user()->id)->get();
        return view('pages.frontend.cart', compact('carts'));
    }

    public function success(Request $request)
    {
        return view('pages.frontend.success');
    }
}
