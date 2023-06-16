<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('pages.frontend.index');
    }

    /**
     * Display a listing of the resource.
     */
    public function details()
    {
        return view('pages.frontend.details');
    }

    public function cart(Request $request)
    {
        return view('pages.frontend.cart');
    }

    public function success(Request $request)
    {
        return view('pages.frontend.success');
    }
}
