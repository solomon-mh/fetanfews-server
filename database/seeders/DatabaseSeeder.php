<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Medication;
use App\Models\Pharmacy;
use Faker\Factory as Faker;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PharmacySeeder::class);
        $this->call(MedicationSeeder::class);
        $this->call(CategorySeeder::class);

        $this->seedMedicationPharmacyPivot();

    }
    private function seedMedicationPharmacyPivot(): void
    {
        $medications = Medication::all();
        $pharmacies = Pharmacy::all();
       $faker = Faker::create();

        foreach ($medications as $medication) {
            // Pick 1 to 3 random pharmacies for this medication
            $selectedPharmacies = $pharmacies->random(rand(1, 3));

            foreach ($selectedPharmacies as $pharmacy) {
                $medication->pharmacies()->attach($pharmacy->id, [
                    'price' => rand(5, 20),
                    'stock_quantity' => rand(0, 100),
                    'stock_status' =>(bool)(rand(0,1)),
                    'quantity_available' => rand(0, 20),
                    'manufacturer'=>$faker->company
                ]);
            }
        }
    }
}
