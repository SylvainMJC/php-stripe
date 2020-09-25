<?php

namespace App;

class Order
{




    public static function retrieveById($orderId)
    {
        return app('db')
            ->table('orders')
            ->find($orderId);

        
    }

    public static function retrieveAll($userId)
    {
        $rows = app('db')
            ->table('orders')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $rows;
    }

    public static function initiate($userId)
    {
        // Get the current cart entries for this user
        $cartEntries = app('db')
            ->table('cart_entries')
            ->where('user_id', $userId)
            ->select('stock_entry_id', 'quantity')
            ->get();

        // Lock the appropriate stock entries
        $cartEntryIds = $cartEntries->map(function($cartEntry) {
            return $cartEntry->stock_entry_id;
        });

        $stockEntries = app('db')
            ->table('stock_entries')
            ->whereIn('id', $cartEntryIds)
            ->lockForUpdate()
            ->get();

        $lines = $cartEntries->map(function ($cartEntry) use ($stockEntries) {
            $stockEntry = $stockEntries->first(function($stockEntry) use ($cartEntry) {
                return $stockEntry->id == $cartEntry->stock_entry_id;
            });

            return [
                'isbn' => $stockEntry->isbn,
                'quantity' => $cartEntry->quantity,
                'unit_price' => $stockEntry->unit_price,
                'currency' => $stockEntry->currency,
                'stock_entry_id' => $stockEntry->id,
            ];
        });

        $totalPrice = $lines->sum(function ($line) {
            return $line['quantity'] * $line['unit_price'];
        });

        $orderId = app('db')
            ->table('orders')
            ->insertGetId([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'state' => 'initiated',
            ]);

        $lines->each(function ($line) use ($orderId) {
            $line['order_id'] = $orderId;
            app('db')
                ->table('order_lines')
                ->insert($line);
            app('db')
                ->table('stock_entries')
                ->where('id', $line['stock_entry_id'])
                ->decrement('quantity', $line['quantity']);
        });

        return $orderId;
    }

    public static function stopAll($userId)
    {
        return app('db')->transaction(function () use ($userId) {
            // Prevent anything else to interact with user's orders
            app('db')
                ->table('orders')
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->get();

            $orders = app('db')
                ->table('orders')
                ->where('user_id', $userId)
                ->where('state', 'initiated')
                ->get();

            foreach ($orders as $order) {
                app('db')
                    ->table('orders')
                    ->where('id', $order->id)
                    ->update(['state' => 'stopped']);

                // Restock the orderLines
                $orderLines = Order::orderLines($order->id);
                foreach ($orderLines as $orderLine) {
                    app('db')
                        ->table('stock_entries')
                        ->where('id', $orderLine->stock_entry_id)
                        ->increment('quantity', $orderLine->quantity);
                }
            }

            return $orders;
        });
    }

    public static function orderLines($orderId)
    {
        return app('db')
            ->table('order_lines')
            ->join('books', 'books.isbn', '=', 'order_lines.isbn')
            ->where('order_id', $orderId)
            ->select('order_lines.*', 'books.title')
            ->get();
    }

    public static function updateState($orderId, $state){


        return app('db')
            ->table('orders')
            ->where('id', $orderId)
            ->update(['state' => $state]);

    }
}
