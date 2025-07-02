<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Pharmacy;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Medication>
 */
class MedicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'name' => $this->faker->word(),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'stock_status' => $this->faker->boolean(),
            'description' => $this->faker->paragraph(),
            'pharmacy_id' => Pharmacy::factory(), // ✅ creates related pharmacy
            'category_id' => Category::factory(), // ✅ creates related category
            'dosage_form' => $this->faker->randomElement(['tablet','capsule','syrup','injection']),
            'dosage_strength' => $this->faker->randomElement(['250mg', '500mg', '1000mg']),
            'manufacturer' => $this->faker->company(),
            'expiry_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'prescription_required' => $this->faker->boolean(),
            'side_effects' => $this->faker->sentence(),
            'usage_instructions' => $this->faker->sentence(),
            'quantity_available' => $this->faker->numberBetween(0, 500),
            'image' => $this->faker->imageUrl(), // or default placeholder
        ];
    }
}
