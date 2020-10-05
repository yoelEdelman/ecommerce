<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Country;
use App\Models\Product;
use App\Models\State;
use App\Services\Shipping;
use Darryldecode\Cart\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param Shipping $ship
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, Shipping $ship)
    {
        $addresses = $request->user()->addresses()->get();

        if ($addresses->isEmpty()) {
            // Là il faudra renvoyer l'utilisateur sur son compte quand on l'aura créé
        }

        $country_id = $addresses->first()->country_id;

        $shipping = $ship->compute($country_id);

        $content = \Cart::getContent();

        $total = \Cart::getTotal();

        $tax = Country::findOrFail($country_id)->tax;

        return view('command.index', compact('addresses', 'shipping', 'content', 'total', 'tax'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Shipping $ship
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(Request $request, Shipping $ship)
    {
        // Vérification du stock
        $items = \Cart::getContent();
        foreach($items as $row) {
            $product = Product::findOrFail($row->id);
            if($product->quantity < $row->quantity) {
                $request->session()->flash('message', 'Nous sommes désolés mais le produit "' . $row->name . '" ne dispose pas d\'un stock suffisant pour satisfaire votre demande. Il ne nous reste plus que ' . $product->quantity . ' exemplaires disponibles.');
                return back();
            }
        }
        // Client
        $user = $request->user();
        // Facturation
        $address_facturation = Address::with('country')->findOrFail($request->facturation);
        // Livraison
        $address_livraison = $request->different ? Address::with('country')->findOrFail($request->livraison) : $address_facturation;
        $shipping = $request->expedition === 'colissimo' ? $ship->compute($address_livraison->country->id) : 0;
        // TVA
        $tvaBase = Country::whereName('France')->first()->tax;
        $tax = $request->expedition === 'colissimo' ? $address_livraison->country->tax : $tvaBase;
        // Enregistrement commande
        $order = $user->orders()->create([
            'reference' => strtoupper(Str::random(8)),
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $tax > 0 ? \Cart::getTotal() : \Cart::getTotal() / (1 + $tvaBase),
            'payment' => $request->payment,
            'pick' => $request->expedition === 'retrait',
            'state_id' => State::whereSlug($request->payment)->first()->id,
        ]);
        // Enregistrement adresse de facturation
        $order->adresses()->create($address_facturation->toArray());
        // Enregistrement éventuel adresse de livraison
        if($request->different) {
            $address_livraison->facturation = false;
            $order->adresses()->create($address_livraison->toArray());
        }
        // Enregistrement des produits
        foreach($items as $row) {
            $order->products()->create(
                [
                    'name' => $row->name,
                    'total_price_gross' => ($tax > 0 ? $row->price : $row->price / (1 + $tvaBase)) * $row->quantity,
                    'quantity' => $row->quantity,
                ]
            );
            // Mise à jour du stock
            $product = Product::findOrFail($row->id);
            $product->quantity -= $row->quantity;
            $product->save();
            // Alerte stock
            if ($product->quantity <= $product->quantity_alert) {
                // Notifications à prévoir pour les administrateurs
            }
        }
        // On vide le panier
        \Cart::clear();
        \Cart::session($request->user())->clear();
        // Notifications à prévoir pour les administrateurs et l'utilisateur
        return redirect(route('commandes.confirmation', $order->id));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
