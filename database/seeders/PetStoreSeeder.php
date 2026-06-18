<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PetStore;

class PetStoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            [
                'store_name'    => 'Animalerie Hamza',
                'description'   => 'La plus grande animalerie de Sousse — nourriture, accessoires, aquariums, animaux.',
                'phone'         => '+216 73 234 567',
                'email'         => 'hamza@petshop.tn',
                'address'       => 'Centre Commercial Sousse Mall, Sousse',
                'city'          => 'Sousse',
                'region'        => 'Sousse',
                'latitude'      => 35.8300,
                'longitude'     => 10.6350,
                'services'      => ['Nourriture', 'Accessoires', 'Aquariums', 'Toilettage', 'Conseil'],
                'opening_hours' => ['Lun-Dim' => '9h-21h'],
                'rating'        => 4.6,
                'reviews_count' => 312,
                'is_verified'   => true,
            ],
            [
                'store_name'    => 'Zoo Express Tunis',
                'description'   => 'Votre animalerie de confiance depuis 15 ans à Tunis. Spécialistes reptiles et oiseaux.',
                'phone'         => '+216 71 456 789',
                'email'         => 'zooexpress@petshop.tn',
                'address'       => 'Avenue Mohamed V, La Goulette',
                'city'          => 'Tunis',
                'region'        => 'Tunis',
                'latitude'      => 36.8190,
                'longitude'     => 10.3047,
                'services'      => ['Nourriture', 'Reptiles', 'Oiseaux', 'Aquariums', 'Toilettage'],
                'opening_hours' => ['Lun-Sam' => '8h30-20h', 'Dim' => '10h-18h'],
                'rating'        => 4.4,
                'reviews_count' => 189,
                'is_verified'   => true,
            ],
            [
                'store_name'    => 'PetWorld Sfax',
                'description'   => 'Tout pour vos animaux : nutrition premium, accessoires, vêtements et toilettage.',
                'phone'         => '+216 74 678 901',
                'email'         => 'petworld@petshop.tn',
                'address'       => 'Rue Habib Maazoun, Sfax',
                'city'          => 'Sfax',
                'region'        => 'Sfax',
                'latitude'      => 34.7375,
                'longitude'     => 10.7570,
                'services'      => ['Nourriture premium', 'Accessoires', 'Vêtements', 'Toilettage'],
                'opening_hours' => ['Lun-Sam' => '9h-20h'],
                'rating'        => 4.2,
                'reviews_count' => 98,
                'is_verified'   => true,
            ],
            [
                'store_name'    => 'Happy Pets Ariana',
                'description'   => 'Boutique familiale spécialisée chiens et chats. Livraison à domicile disponible.',
                'phone'         => '+216 71 789 012',
                'email'         => 'happypets@petshop.tn',
                'address'       => 'Rue Ibn Khaldoun, Ariana Ville',
                'city'          => 'Ariana',
                'region'        => 'Ariana',
                'latitude'      => 36.8667,
                'longitude'     => 10.1933,
                'services'      => ['Nourriture', 'Accessoires', 'Livraison', 'Abonnement mensuel'],
                'opening_hours' => ['Lun-Dim' => '9h-21h'],
                'rating'        => 4.7,
                'reviews_count' => 234,
                'is_verified'   => true,
            ],
        ];

        foreach ($stores as $s) {
            PetStore::create($s);
        }

        $this->command->info('✅ ' . count($stores) . ' pet stores créés');
    }
}