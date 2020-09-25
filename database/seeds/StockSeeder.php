<?php

use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    public function run()
    {
        app('db')
            ->table('stock_entries')
            ->insert([
                'isbn' => '9780134456478',
                'quantity' => 5,
                'unit_price' => 3099,
                'currency' => 'EUR',
            ]);

        app('db')
            ->table('stock_entries')
            ->insert([
                'isbn' => '9780134456478',
                'quantity' => 1,
                'unit_price' => 1510,
                'currency' => 'EUR',
            ]);

        app('db')
            ->table('stock_entries')
            ->insert([
                'isbn' => '1617296856',
                'quantity' => 3,
                'unit_price' => 3549,
                'currency' => 'EUR',
            ]);
    }
}
