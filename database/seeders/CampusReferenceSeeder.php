<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Country;
use App\Models\Venue;
use Illuminate\Database\Seeder;

/**
 * Campus reference seeder — seeds OUT regional branches and campus training venues idempotently.
 *
 * @author Renatus Bernardo
 */
class CampusReferenceSeeder extends Seeder
{
    /**
     * Execute the database seeder.
     */
    public function run(): void
    {
        $tanzaniaId = Country::where('iso', 'TZ')->value('id');

        $branches = [
            ['name' => 'Maarifazone Headquarters', 'branch_code' => 'MZ-HQ', 'address' => 'Dar es Salaam', 'venue' => 'Kinondoni Regional Center'],
            ['name' => 'Mwanza Regional Center', 'branch_code' => 'MZ-MWZ', 'address' => 'Mwanza', 'venue' => 'Mwanza Computer Lab'],
            ['name' => 'Kinondoni Regional Center', 'branch_code' => 'MZ-KIN', 'address' => 'Dar es Salaam', 'venue' => 'Kinondoni Seminar Hall'],
            ['name' => 'Manyara Regional Center', 'branch_code' => 'MZ-MNY', 'address' => 'Manyara', 'venue' => 'Manyara Computer Lab'],
            ['name' => 'Iringa Regional Center', 'branch_code' => 'MZ-IRG', 'address' => 'Iringa', 'venue' => 'Maarifazone Iringa Computer Lab'],
            ['name' => 'Kigoma Regional Center', 'branch_code' => 'MZ-KGM', 'address' => 'Kigoma', 'venue' => 'Maarifazone Kigoma Center Computer Lab'],
            ['name' => 'Rukwa Regional Center', 'branch_code' => 'MZ-RKW', 'address' => 'Rukwa', 'venue' => 'Maarifazone Rukwa Center Computer Lab'],
            ['name' => 'Mara Regional Center', 'branch_code' => 'MZ-MRA', 'address' => 'Mara', 'venue' => 'Maarifazone Mara Center Computer Lab'],
            ['name' => 'Katavi Regional Center', 'branch_code' => 'MZ-KTV', 'address' => 'Mpanda', 'venue' => 'Katavi Computer Lab'],
            ['name' => 'Kagera Regional Center', 'branch_code' => 'MZ-KGR', 'address' => 'Kagera', 'venue' => 'Maarifazone Kagera Center Computer Lab'],
            ['name' => 'Singida Regional Center', 'branch_code' => 'MZ-SGD', 'address' => 'Singida', 'venue' => 'Singida Computer Lab'],
            ['name' => 'Dodoma Regional Center', 'branch_code' => 'MZ-DDM', 'address' => 'Dodoma', 'venue' => 'Maarifazone Dodoma Center Computer Lab'],
            ['name' => 'Kahama Coordination Center', 'branch_code' => 'MZ-KHM', 'address' => 'Kahama', 'venue' => 'Maarifazone Kahama Center Computer Lab'],
            ['name' => 'Mtwara Regional Center', 'branch_code' => 'MZ-MTW', 'address' => 'Mtwara', 'venue' => 'Mtwara Computer Lab'],
            ['name' => 'Lindi Regional Center', 'branch_code' => 'MZ-LND', 'address' => 'Lindi', 'venue' => 'Maarifazone Lindi Center Computer Lab'],
            ['name' => 'Simiyu Regional Center', 'branch_code' => 'MZ-SMY', 'address' => 'Simiyu', 'venue' => 'Maarifazone Simiyu Center Computer Lab'],
            ['name' => 'Mbeya Regional Center', 'branch_code' => 'MZ-MBY', 'address' => 'Mbeya', 'venue' => 'Maarifazone Mbeya Center Computer Lab'],
        ];

        foreach ($branches as $row) {
            $branch = Branch::updateOrCreate(
                ['name' => $row['name']],
                [
                    'branch_code' => $row['branch_code'],
                    'address' => $row['address'],
                    'country_id' => $tanzaniaId,
                    'is_active' => true,
                ]
            );

            Venue::firstOrCreate(
                [
                    'branch_id' => $branch->id,
                    'name' => $row['venue'],
                ],
                [
                    'capacity' => 30,
                    'equipment' => 'Projector, Workstation PCs, High-Speed Wi-Fi',
                    'is_active' => true,
                ]
            );
        }
    }
}
