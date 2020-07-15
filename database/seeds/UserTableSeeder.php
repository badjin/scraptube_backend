<?php

use App\Category;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        DB::table('role_user')->truncate();
        Schema::enableForeignKeyConstraints();

        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $memberRole = Role::where('name', 'member')->first();

        $user1 = User::create([
            'name' => 'BadJin',
            'avatar_id' => 1,
            'email' => 'admin@scraptube.net',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678')
        ]);
        $user1->roles()->attach($adminRole);
        Category::create([
            'user_id' => $user1->id,
            'name'=> 'Interest'
        ]);
        Category::create([
            'user_id' => $user1->id,
            'name'=> 'Study'
        ]);
        Category::create([
            'user_id' => $user1->id,
            'name'=> 'Work'
        ]);

        $user3 = User::create([
            'name' => 'Guest',
            'avatar_id' => 3,
            'email' => 'guest@scraptube.net',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678')
        ]);
        $user3->roles()->attach($memberRole);
        Category::create([
            'user_id' => $user3->id,
            'name'=> 'Interest'
        ]);
        Category::create([
            'user_id' => $user3->id,
            'name'=> 'Study'
        ]);
        Category::create([
            'user_id' => $user3->id,
            'name'=> 'Work'
        ]);
    }
}
