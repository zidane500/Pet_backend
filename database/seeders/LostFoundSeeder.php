<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LostFound;
use App\Models\User;

class LostFoundSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) return;

        $reports = [
            [
                'type'               => 'lost',
                'animal_name'        => 'Rex',
                'species'            => 'Chien',
                'breed'              => 'Labrador',
                'color'              => 'Noir',
                'description'        => 'Perdu le 15 juin près du parc El Mourouj. Porte un collier rouge avec médaille. Très gentil.',
                'last_seen_location' => 'Parc El Mourouj, Tunis',
                'latitude'           => 36.7819,
                'longitude'          => 10.1658,
                'date_lost_found'    => '2026-06-15',
                'photos'             => ['https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400'],
                'contact_phone'      => '+216 22 100 200',
            ],
            [
                'type'               => 'found',
                'animal_name'        => null,
                'species'            => 'Chat',
                'breed'              => 'European',
                'color'              => 'Orange tigré',
                'description'        => 'Trouvé près de la gare de Sfax, semble domestique, très affectueux. Pas de puce détectée.',
                'last_seen_location' => 'Gare de Sfax, Centre-ville',
                'latitude'           => 34.7400,
                'longitude'          => 10.7601,
                'date_lost_found'    => '2026-06-16',
                'photos'             => ['https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400'],
                'contact_phone'      => '+216 25 300 400',
            ],
            [
                'type'               => 'lost',
                'animal_name'        => 'Luna',
                'species'            => 'Chat',
                'breed'              => 'Siamois',
                'color'              => 'Crème et brun',
                'description'        => 'Chatte Siamois perdue à Ariana depuis 2 jours. Yeux bleus, très craintive. Récompense.',
                'last_seen_location' => 'Cité Ennasr, Ariana',
                'latitude'           => 36.8800,
                'longitude'          => 10.1900,
                'date_lost_found'    => '2026-06-14',
                'photos'             => ['https://images.unsplash.com/photo-1596854407944-bf87f6fdd49e?w=400'],
                'contact_phone'      => '+216 28 500 600',
            ],
            [
                'type'               => 'found',
                'animal_name'        => null,
                'species'            => 'Chien',
                'breed'              => 'Bâtard moyen',
                'color'              => 'Beige et blanc',
                'description'        => 'Chien trouvé errant à Monastir, propre, semble habitué aux humains. Porte un vieux collier.',
                'last_seen_location' => 'Corniche de Monastir',
                'latitude'           => 35.7775,
                'longitude'          => 10.8262,
                'date_lost_found'    => '2026-06-17',
                'photos'             => ['https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=400'],
                'contact_phone'      => '+216 29 700 800',
            ],
        ];

        $userIds = $users->pluck('id')->toArray();

        foreach ($reports as $i => $r) {
            LostFound::create([
                ...$r,
                'user_id'     => $userIds[$i % count($userIds)],
                'is_resolved' => false,
            ]);
        }

        $this->command->info('✅ ' . count($reports) . ' signalements perdus/trouvés créés');
    }
}