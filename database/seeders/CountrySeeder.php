<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        $path = public_path('/seeders/countries.csv');
        if (!file_exists($path)) {
            $this->command->error("countries.csv not found at {$path}");
            return;
        }

        $rows   = [];
        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->command->error("Unable to open {$path}");
            return;
        }

        // read header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->command->error("Empty CSV: {$path}");
            return;
        }

        // normalize header keys
        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);

            // guard rows
            if (empty($row['id']) || empty($row['name']) || !isset($row['code'])) {
                continue;
            }

            $rows[] = [
                'id'         => (int) $row['id'],
                'name'       => trim($row['name']),
                'code'       => trim($row['code']),      
                'status'     => (int) ($row['status'] ?? 1),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        fclose($handle);

       
        DB::table('countries')->upsert(
            $rows,
            ['id'],                
            ['name','code','status','updated_at']
        );

        $this->command->info('Seeded countries: '.count($rows));
    }
}
