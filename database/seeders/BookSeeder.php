<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    public function run()
    {
        $books = Book::whereNull('sort_number')->get();
        foreach ($books as $key => $book) {
            DB::table('book')->where('id', $book->id)->update(['sort_number' => microtime(true)*10000]);
            if ($key > 0) {
                sleep(3);
            }
        }
    }
}
