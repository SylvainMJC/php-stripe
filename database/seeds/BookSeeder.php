<?php

use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         app('db')
            ->table('books')
            ->insert([
                'isbn' => '9780134456478',
                'title' => 'Practical Object-oriented Design: An Agile Primer Using Ruby (2nd Edition)',
            ]);

        app('db')
            ->table('books')
            ->insert([
                'isbn' => '1617296856',
                'title' => 'Object Design Style Guide',
            ]);
    }
}
