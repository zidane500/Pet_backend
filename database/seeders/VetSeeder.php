<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vet;

class VetSeeder extends Seeder
{
    public function run(): void
    {
        $vets = [
            [
                'clinic_name'   => 'Clinique Vétérinaire El Menzah',
                'doctor_name'   => 'Dr. Karim Mansour',
                'speciality'    => 'Médecine générale & chirurgie',
                'phone'         => '+216 71 234 567',
                'email'         => 'elmenzah@vet.tn',
                'address'       => 'Avenue de la Liberté, El Menzah 6',
                'city'          => 'Tunis',
                'region'        => 'Ariana',
                'latitude'      => 36.8527,
                'longitude'     => 10.1940,
                'services'      => ['Consultation', 'Chirurgie', 'Vaccination', 'Radiographie', 'Urgences 24h'],
                'opening_hours' => ['Lun-Ven' => '8h-19h', 'Sam' => '9h-16h', 'Dim' => 'Fermé'],
                'rating'        => 4.8,
                'reviews_count' => 127,
                'is_verified'   => true,
            ],
            [
                'clinic_name'   => 'Cabinet Vétérinaire Sfax Centre',
                'doctor_name'   => 'Dr. Nadia Khelifi',
                'speciality'    => 'Dermatologie & animaux exotiques',
                'phone'         => '+216 74 456 789',
                'email'         => 'sfaxvet@vet.tn',
                'address'       => 'Rue Hédi Chaker, Centre-ville',
                'city'          => 'Sfax',
                'region'        => 'Sfax',
                'latitude'      => 34.7406,
                'longitude'     => 10.7603,
                'services'      => ['Consultation', 'Dermatologie', 'NAC', 'Vaccination', 'Analyses'],
                'opening_hours' => ['Lun-Sam' => '9h-18h', 'Dim' => '10h-13h'],
                'rating'        => 4.6,
                'reviews_count' => 89,
                'is_verified'   => true,
            ],
            [
                'clinic_name'   => 'VetCare Sousse',
                'doctor_name'   => 'Dr. Tarek Bouazizi',
                'speciality'    => 'Orthopédie & reproduction',
                'phone'         => '+216 73 789 012',
                'email'         => 'vetcare.sousse@vet.tn',
                'address'       => 'Boulevard 14 Janvier, Sousse',
                'city'          => 'Sousse',
                'region'        => 'Sousse',
                'latitude'      => 35.8245,
                'longitude'     => 10.6346,
                'services'      => ['Consultation', 'Chirurgie', 'Orthopédie', 'Reproduction', 'Urgences'],
                'opening_hours' => ['Lun-Ven' => '8h30-20h', 'Sam-Dim' => '9h-15h'],
                'rating'        => 4.7,
                'reviews_count' => 203,
                'is_verified'   => true,
            ],
            [
                'clinic_name'   => 'Clinique des Animaux Ariana',
                'doctor_name'   => 'Dr. Imen Saidi',
                'speciality'    => 'Ophtalmologie & comportement',
                'phone'         => '+216 71 890 123',
                'email'         => 'ariana.vet@vet.tn',
                'address'       => 'Avenue Mongi Slim, Ariana',
                'city'          => 'Ariana',
                'region'        => 'Ariana',
                'latitude'      => 36.8625,
                'longitude'     => 10.1956,
                'services'      => ['Consultation', 'Ophtalmologie', 'Comportement', 'Vaccination', 'Pension'],
                'opening_hours' => ['Lun-Sam' => '8h-20h', 'Dim' => 'Urgences seulement'],
                'rating'        => 4.5,
                'reviews_count' => 156,
                'is_verified'   => true,
            ],
            [
                'clinic_name'   => 'Cabinet Ben Amor Bizerte',
                'doctor_name'   => 'Dr. Sami Ben Amor',
                'speciality'    => 'Médecine interne',
                'phone'         => '+216 72 345 678',
                'email'         => 'benamor@vet.tn',
                'address'       => 'Rue de la République, Bizerte',
                'city'          => 'Bizerte',
                'region'        => 'Bizerte',
                'latitude'      => 37.2744,
                'longitude'     => 9.8739,
                'services'      => ['Consultation', 'Médecine interne', 'Vaccination', 'Stérilisation'],
                'opening_hours' => ['Lun-Ven' => '9h-18h', 'Sam' => '9h-13h'],
                'rating'        => 4.3,
                'reviews_count' => 67,
                'is_verified'   => false,
            ],
            [
                'clinic_name'   => 'PetMed Nabeul',
                'doctor_name'   => 'Dr. Rania Ferchichi',
                'speciality'    => 'Médecine générale',
                'phone'         => '+216 72 567 890',
                'email'         => 'petmed.nabeul@vet.tn',
                'address'       => 'Avenue Habib Bourguiba, Nabeul',
                'city'          => 'Nabeul',
                'region'        => 'Nabeul',
                'latitude'      => 36.4561,
                'longitude'     => 10.7376,
                'services'      => ['Consultation', 'Vaccination', 'Stérilisation', 'Urgences'],
                'opening_hours' => ['Lun-Sam' => '8h-19h'],
                'rating'        => 4.4,
                'reviews_count' => 45,
                'is_verified'   => true,
            ],
        ];

        foreach ($vets as $v) {
            Vet::create($v);
        }

        $this->command->info('✅ ' . count($vets) . ' vétérinaires créés');
    }
}