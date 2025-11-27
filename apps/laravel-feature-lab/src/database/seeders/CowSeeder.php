<?php

namespace Database\Seeders;

use App\Models\Cow;
use Illuminate\Database\Seeder;

class CowSeeder extends Seeder
{
    public function run(): void
    {
        Cow::factory()->count(25)->create();
    }
}
