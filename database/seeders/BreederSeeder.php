<?php

namespace Database\Seeders;

use App\Models\Breeder;
use App\Models\User;
use Illuminate\Database\Seeder;

class BreederSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'breeder')->first()
             ?? User::factory()->create([
                'name'     => 'Breeder Admin',
                'email'    => 'breeder@animali.tn',
                'role'     => 'breeder',
                'password' => bcrypt('password'),
                'is_active'=> true,
             ]);

        $breeders = [
            [
                'name'              => 'Élevage Atlas Tunis',
                'tagline'           => 'Des chiens de race pure depuis 2005',
                'address'           => 'Cité El Ghazala, Ariana',
                'city'              => 'Ariana',
                'phone'             => '+216 71 234 567',
                'email'             => 'contact@elevage-atlas.tn',
                'website'           => 'elevage-atlas.tn',
                'verified'          => true,
                'is_certified'      => true,
                'speciality'        => 'Berger Allemand, Labrador, Husky',
                'years_experience'  => 19,
                'animals_sold_total'=> 450,
                'rating'            => 4.9,
                'reviews_count'     => 112,
                'description'       => 'Élevage professionnel certifié spécialisé dans les races de grande taille. Tous nos animaux sont vaccinés, vermifugés et accompagnés de leur carnet de santé.',
                'logo'              => 'https://images.unsplash.com/photo-1551717743-49959800b1f6?w=200&h=200&fit=crop',
                'cover_image'       => 'https://images.unsplash.com/photo-1558929996-da64ba858215?w=800&h=300&fit=crop',
                'is_active'         => true,
            ],
            [
                'name'              => 'Chatterie Les Perles Sfax',
                'tagline'           => 'Des chats de race élevés avec amour',
                'address'           => 'Route de Mahdia, Sfax',
                'city'              => 'Sfax',
                'phone'             => '+216 74 567 890',
                'email'             => 'contact@chatterie-perles.tn',
                'website'           => null,
                'verified'          => true,
                'is_certified'      => false,
                'speciality'        => 'Persan, Maine Coon, Siamois',
                'years_experience'  => 8,
                'animals_sold_total'=> 180,
                'rating'            => 4.7,
                'reviews_count'     => 63,
                'description'       => 'Chatterie familiale spécialisée dans les races de chats de luxe. Chaque chaton part avec son carnet de santé, ses vaccins et une garantie sanitaire.',
                'logo'              => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=200&h=200&fit=crop',
                'cover_image'       => 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?w=800&h=300&fit=crop',
                'is_active'         => true,
            ],
            [
                'name'              => 'Élevage Medina Sousse',
                'tagline'           => 'Chiens de compagnie et de garde',
                'address'           => 'Zone Industrielle, Sousse',
                'city'              => 'Sousse',
                'phone'             => '+216 73 456 123',
                'email'             => 'elevage.medina@gmail.com',
                'website'           => null,
                'verified'          => false,
                'is_certified'      => false,
                'speciality'        => 'Rottweiler, Dobermann, Golden Retriever',
                'years_experience'  => 5,
                'animals_sold_total'=> 95,
                'rating'            => 4.4,
                'reviews_count'     => 31,
                'description'       => 'Éleveur passionné proposant des chiens de compagnie et de garde bien socialisés, élevés en famille.',
                'logo'              => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?w=200&h=200&fit=crop',
                'cover_image'       => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=800&h=300&fit=crop',
                'is_active'         => true,
            ],
        ];

        foreach ($breeders as $data) {
            Breeder::firstOrCreate(
                ['email' => $data['email'] ?? $data['name']],
                array_merge($data, ['user_id' => $user->id])
            );
        }
    }
}