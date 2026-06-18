<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ahmed Ben Ali',     'email' => 'ahmed@test.tn',   'role' => 'owner',   'city' => 'Tunis'],
            ['name' => 'Fatma Trabelsi',    'email' => 'fatma@test.tn',   'role' => 'owner',   'city' => 'Sfax'],
            ['name' => 'Dr. Karim Mansour', 'email' => 'vet@test.tn',     'role' => 'vet',     'city' => 'Tunis'],
            ['name' => 'Animalerie Hamza',  'email' => 'shop@test.tn',    'role' => 'shop',    'city' => 'Sousse'],
            ['name' => 'Refuge Les Pattes', 'email' => 'shelter@test.tn', 'role' => 'shelter', 'city' => 'Tunis'],
            ['name' => 'Sonia Chaabane',    'email' => 'sonia@test.tn',   'role' => 'owner',   'city' => 'Monastir'],
            ['name' => 'Mohamed Gharbi',    'email' => 'med@test.tn',     'role' => 'breeder', 'city' => 'Bizerte'],
            ['name' => 'Leila Bouzid',      'email' => 'leila@test.tn',   'role' => 'owner',   'city' => 'Nabeul'],
        ];

        foreach ($users as $u) {
            User::create([
                'name'        => $u['name'],
                'email'       => $u['email'],
                'password'    => Hash::make('password123'),
                'role'        => $u['role'],
                'city'        => $u['city'],
                'is_verified' => true,
                'is_active'   => true,
                'plan'        => 'free',
                'phone'       => '+216 ' . rand(20, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
            ]);
        }

        $this->command->info('✅ ' . count($users) . ' utilisateurs créés');
    }
}