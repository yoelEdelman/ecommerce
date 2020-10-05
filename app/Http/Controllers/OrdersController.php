<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    /**
     * Show order confirmation.
     *
     * @param Request $request
     * @param integer $id
     * @return \Illuminate\Http\Response
     */
    public function confirmation(Request $request, $id)
    {
        $order = Order::with('products', 'adresses', 'state')->findOrFail($id);
        if(in_array($order->state->slug, ['cheque', 'mandat', 'virement', 'carte', 'erreur'])) {
            $this->authorize('manage', $order);

            $data = $this->data($request, $order);
            return view('command.confirmation', $data);
        }
    }
    /**
     * Get order data
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return array
     */
    protected function data($request, $order)
    {
        $shop = Shop::firstOrFail();
        $data = compact('order', 'shop');
        if ($order->state->slug === 'carte' || $order->state->slug === 'erreur') {
            // Là on s'occupera de Stripe
        }

        return $data;
    }
}
