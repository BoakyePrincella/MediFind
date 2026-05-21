<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //checks if admin alreay exists
        $email = 'admin@example.com';
        if (User::where('email', $email)->exists()){
            $this->command->info('Admin user already exists');
            return;
        }

        //create admin user
        $user = User::create([
            'fullname' => 'Admin User',
            'email' => $email,
            'phone' => '1234567890',
            'password' => Hash::make('password'), //default password
            'role' => 'admin',
        ]);

        $this->command->info('Admin user created with email: ' . $email . ' and password: password');
    }
}
