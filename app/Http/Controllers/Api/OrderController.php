<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    /**
     * Crée une commande à partir du panier envoyé par le front.
     * Toute la logique sensible (stock, prix, total) est recalculée et
     * vérifiée ICI côté serveur — on ne fait JAMAIS confiance aux prix
     * ou totaux envoyés par le client.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // ← Fusionne les lignes qui pointent vers le même produit, pour
        // empêcher de contourner la vérification de stock en envoyant
        // le même produit plusieurs fois dans le panier.
        $quantitiesByProduct = [];
        foreach ($data['items'] as $item) {
            $productId = (int) $item['product_id'];
            $quantitiesByProduct[$productId] =
                ($quantitiesByProduct[$productId] ?? 0) + (int) $item['quantity'];
        }

        $order = DB::transaction(function () use ($quantitiesByProduct, $data, $request) {
            $orderItemsData = [];
            $total = 0;

            foreach ($quantitiesByProduct as $productId => $quantity) {
                // ← lockForUpdate : verrouille la ligne produit le temps
                // de la transaction, pour empêcher que deux commandes
                // simultanées ne vendent plus de stock qu'il n'y en a
                // réellement (condition de course classique en e-commerce).
                $product = Product::where('id', $productId)->lockForUpdate()->first();

                if (!$product) {
                    throw ValidationException::withMessages([
                        'items' => "Un produit du panier n'existe plus.",
                    ]);
                }

                if (!$product->hasStock($quantity)) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuffisant pour \"{$product->name}\" (disponible : {$product->stock_quantity}).",
                    ]);
                }

                $subtotal = $product->price * $quantity;
                $total += $subtotal;

                $orderItemsData[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'unit_price'   => $product->price,
                    'quantity'     => $quantity,
                    'subtotal'     => $subtotal,
                ];

                $product->decrement('stock_quantity', $quantity);
            }

            $order = Order::create([
                'user_id'          => $request->user()->id,
                'status'           => 'pending',
                'total_amount'     => $total,
                'shipping_name'    => $data['shipping_name'],
                'shipping_phone'   => $data['shipping_phone'],
                'shipping_address' => $data['shipping_address'],
                'shipping_city'    => $data['shipping_city'],
                'notes'            => $data['notes'] ?? null,
            ]);

            $order->items()->createMany($orderItemsData);

            return $order;
        });

        // ← En dehors de la transaction : si l'envoi de la notification
        // échoue pour une raison quelconque, ça ne doit pas annuler la
        // commande qui vient d'être validée et déduite du stock.
        User::where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new NewOrderNotification($order)),
        );

        return response()->json($order->load('items'), 201);
    }

    /**
     * Historique des commandes du client connecté.
     */
    public function myOrders(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::with('items.product:id,name,photos')->findOrFail($id);

        // ← Un client ne peut voir que SES commandes ; l'admin peut tout voir.
        if ($order->user_id !== $request->user()->id && $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé.'], 403);
        }

        return response()->json($order);
    }
}