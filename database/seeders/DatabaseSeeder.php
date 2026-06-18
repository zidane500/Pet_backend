<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            VetSeeder::class,
            PetStoreSeeder::class,
            ListingSeeder::class,
            LostFoundSeeder::class,
        ]);

        $this->command->info('🎉 Base de données remplie avec succès !');
    }
}