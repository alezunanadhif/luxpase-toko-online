<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\CheckoutRequest;
use App\Models\Carts;
use App\Models\Products;
use App\Models\Transactions;
use App\Models\TransactionsItems;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

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

    public function checkout(CheckoutRequest $request)
    {
        $data = $request->all();

        // Get carts data
        $carts = Carts::with(['product'])->where('user_id', Auth::user()->id)->get();

        // Add to transaction data
        $data['user_id'] = Auth::user()->id;
        $data['total_price'] = $carts->sum('prducts.price');

        // Create transaction
        $transaction = Transactions::create($data);

        // Create transaction item
        foreach ($carts as $cart) {
            $items[] = TransactionsItems::create([
                'transaction_id' => $transaction->id,
                'user_id' => $cart->id,
                'product_id' => $cart->product_id,
            ]);
        }

        // Delete cart after transaction
        Carts::where('user_id', Auth::user()->id)->delete();

        // Konfigurasi
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Setup variable midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => 'LUX-' . $transaction->id,
                'gross_amount' => (int) $transaction->total_price
            ],
            'customer_details' => [
                'first_name' => $transaction->name,
                'email' => $transaction->email
            ],
            'enabled_payments' => ['gopay', 'bank_transfer'],
            'vtweb' => []
        ];

        // Payment process
        try {
            // Get Snap Payment Page URL
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            $transaction->payment_url = $paymentUrl;
            $transaction->save();

            // Redirect to Snap Payment Page
            return redirect($paymentUrl);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function success(Request $request)
    {
        return view('pages.frontend.success');
    }
}
