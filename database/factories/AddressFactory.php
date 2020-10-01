<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $professional = $this->faker->boolean();
        if(!$professional || ($professional && $this->faker->boolean())) {
            $name = $this->faker->lastName;
            $firstName = $this->faker->firstName;
        } else {
            $name = null;
            $firstName = null;
        }
        return [
            'professional' => $professional,
            'civility' => $this->faker->boolean() ? 'Mme': 'M.',
            'name' => $name,
            'firstname' => $firstName,
            'company' => $professional ? $this->faker->company : null,
            'address' => $this->faker->streetAddress,
            'addressbis' => $this->faker->boolean() ? $this->faker->secondaryAddress : null,
            'bp' => $this->faker->boolean() ? $this->faker->numberBetween(100, 900) : null,
            'postal' => $this->faker->numberBetween(10000, 90000),
            'city' => $this->faker->city,
            'country_id' => mt_rand(1, 4),
            'phone' => $this->faker->numberBetween(1000000000, 9000000000),
        ];
    }
}
