<?php

use App\Category;
use App\User;
use Illuminate\Database\Seeder;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        foreach ($users as $user) {

            Category::create([
                'user_id' => $user->id,
                'name'=> 'Interest'
            ]);
            Category::create([
                'user_id' => $user->id,
                'name'=> 'Study'
            ]);
            Category::create([
                'user_id' => $user->id,
                'name'=> 'Work'
            ]);
        }
    }
}
