<?php

namespace App\Http\Controllers;

use Log;

class CartController extends Controller
{
    public function index()
    {
        $userId = 1; // TODO
        return app('db')
            ->table('cart_entries')
            ->join('stock_entries', 'stock_entries.id', '=', 'cart_entries.stock_entry_id')
            ->join('books', 'books.isbn', '=', 'stock_entries.isbn')
            ->where('user_id', $userId)
            ->select(
                'cart_entries.*',
                'books.title',
                'stock_entries.unit_price',
                'stock_entries.currency',
            )
            ->get();
    }

    public function add($stockEntryId)
    {
        $userId = 1; // TODO

        \App\Order::stopAll($userId);

        app('db')->transaction(function () use ($stockEntryId, $userId) {
            $stockEntry = app('db')
                ->table('stock_entries')
                ->where('id', $stockEntryId)
                ->select('quantity')
                ->lockForUpdate()
                ->first();

            $existingCartEntry = app('db')
                ->table('cart_entries')
                ->where('user_id', $userId)
                ->where('stock_entry_id', $stockEntryId)
                ->lockForUpdate()
                ->first();

            if ($existingCartEntry) {
                if ($stockEntry->quantity > $existingCartEntry->quantity) {
                    app('db')
                        ->table('cart_entries')
                        ->where('id', $existingCartEntry->id)
                        ->increment('quantity', 1);
                }
            } else {
                if ($stockEntry->quantity >= 1) {
                    app('db')
                        ->table('cart_entries')
                        ->insert([
                            'user_id' => $userId,
                            'stock_entry_id' => $stockEntryId,
                            'quantity' => 1,
                        ]);
                }
            }
        });

        return [ 'status' => 'OK' ];
    }

    public function remove($cartEntryId)
    {
        $userId = 1; // TODO

        \App\Order::stopAll($userId);

        app('db')
            ->table('cart_entries')
            ->where('id', $cartEntryId)
            ->where('user_id', $userId)
            ->decrement('quantity', 1);

        app('db')
            ->table('cart_entries')
            ->where('id', $cartEntryId)
            ->where('user_id', $userId)
            ->where('quantity', '<=', 0)
            ->delete();

        return [ 'status' => 'OK' ];
    }
}
