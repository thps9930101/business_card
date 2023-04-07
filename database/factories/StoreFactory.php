<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'=>$this->faker->name,
            'address'=>$this->faker->address,
            'phone'=>$this->faker->phoneNumber,
            'tax_id'=>$this->faker->randomNumber(8),
        ];
    }

    /**
     * after creating the store, create a admin user
     */
    public function configure(){
        return $this->afterCreating(function(Store $store){
            // after creating the store, create a admin user
            User::factory()->create([
                'store_id'=>$store->id,
                'is_store_admin'=>true,
                'email'=>'store@4dbox.com'
            ]);
            //create a customer user of the store
            User::factory()->create([
                'store_id'=>$store->id,
                'is_store_admin'=>false,
            ]);
        });
    }
}
