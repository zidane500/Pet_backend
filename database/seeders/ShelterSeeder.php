<?php

namespace Database\Seeders;

use App\Models\Shelter;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShelterSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'shelter')->first()
             ?? User::factory()->create([
                'name'     => 'Refuge Admin',
                'email'    => 'shelter@animali.tn',
                'role'     => 'shelter',
                'password' => bcrypt('password'),
                'is_active'=> true,
             ]);

        $shelters = [
            [
                'name'                 => 'Refuge Espoir Tunis',
                'tagline'              => 'Chaque animal mérite une famille',
                'address'              => 'Route de La Marsa, Tunis',
                'city'                 => 'Tunis',
                'phone'                => '+216 71 456 789',
                'email'                => 'contact@refuge-espoir.tn',
                'website'              => 'refuge-espoir.tn',
                'verified'             => true,
                'is_nonprofit'         => true,
                'capacity'             => 80,
                'current_animals'      => 63,
                'volunteers_count'     => 24,
                'animals_helped_total' => 1240,
                'rating'               => 4.8,
                'reviews_count'        => 89,
                'description'          => 'Le Refuge Espoir est le plus grand centre de sauvetage animal de Tunis. Nous accueillons chiens, chats et autres animaux abandonnés depuis 2010.',
                'logo'                 => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&h=200&fit=crop',
                'cover_image'          => 'https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=800&h=300&fit=crop',
                'is_active'            => true,
            ],
            [
                'name'                 => 'SOS Animaux Sfax',
                'tagline'              => 'Sauvons ensemble nos compagnons',
                'address'              => 'Rue Habib Bourguiba, Sfax',
                'city'                 => 'Sfax',
                'phone'                => '+216 74 321 654',
                'email'                => 'contact@sos-animaux-sfax.tn',
                'website'              => 'sos-animaux-sfax.tn',
                'verified'             => true,
                'is_nonprofit'         => true,
                'capacity'             => 50,
                'current_animals'      => 38,
                'volunteers_count'     => 15,
                'animals_helped_total' => 780,
                'rating'               => 4.6,
                'reviews_count'        => 54,
                'description'          => 'Association à but non lucratif dédiée au sauvetage et à l\'adoption des animaux errants dans la région de Sfax.',
                'logo'                 => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=200&h=200&fit=crop',
                'cover_image'          => 'https://images.unsplash.com/photo-1601758124510-52d02ddb7cbd?w=800&h=300&fit=crop',
                'is_active'            => true,
            ],
            [
                'name'                 => 'Patte Douce Sousse',
                'tagline'              => 'Une patte tendue vers l\'avenir',
                'address'              => 'Avenue Léopold Senghor, Sousse',
                'city'                 => 'Sousse',
                'phone'                => '+216 73 789 123',
                'email'                => 'info@patte-douce.tn',
                'website'              => null,
                'verified'             => false,
                'is_nonprofit'         => true,
                'capacity'             => 30,
                'current_animals'      => 22,
                'volunteers_count'     => 8,
                'animals_helped_total' => 320,
                'rating'               => 4.3,
                'reviews_count'        => 27,
                'description'          => 'Petite association familiale qui prend soin des animaux abandonnés de Sousse et de ses environs.',
                'logo'                 => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=200&h=200&fit=crop',
                'cover_image'          => 'https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=800&h=300&fit=crop',
                'is_active'            => true,
            ],
        ];

        foreach ($shelters as $data) {
            Shelter::firstOrCreate(
                ['email' => $data['email'] ?? $data['name']],
                array_merge($data, ['user_id' => $user->id])
            );
        }
    }
}