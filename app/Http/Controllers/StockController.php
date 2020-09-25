<?php

namespace App\Http\Controllers;

class StockController extends Controller
{
    public function index()
    {
        return app('db')
            ->table('stock_entries')
            ->join('books', 'books.isbn', '=', 'stock_entries.isbn')
            ->select('stock_entries.*', 'books.title')
            ->get();
    }
}
