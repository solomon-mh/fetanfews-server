<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pharmacy>
 */
class PharmacyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => rand(1, 3),
            'name' => $this->faker->company . ' Pharmacy',
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'website' => $this->faker->optional()->url,
            'operating_hours' => '8AM - 10PM',
            'latitude' => $this->faker->latitude,
            'longitude' => $this->faker->longitude,
            'image' => $this->faker->imageUrl(640, 480, 'pharmacy', true),
            'license_image' => $this->faker->imageUrl(640, 480, 'license', true),
            'license_number' => $this->faker->bothify('PHARMA-###??'),
            'status' => $this->faker->randomElement(['Pending', 'Approved', 'Rejected']),
            'is_verified' => $this->faker->boolean,
            'delivery_available' => $this->faker->boolean,
        ];
    }
}
