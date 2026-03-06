<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Product;
use App\Models\Set;
use App\Models\StorageLocation;
use Illuminate\Database\Seeder;

class BaselineCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $game = Game::query()->firstOrCreate(
            ['slug' => 'pokemon-tcg'],
            [
                'name' => 'Pokémon TCG',
                'publisher' => 'The Pokémon Company',
            ],
        );

        $set = Set::query()->firstOrCreate(
            ['game_id' => $game->id, 'code' => 'BASE'],
            [
                'name' => 'Base Set',
                'release_date' => '1999-01-09',
            ],
        );

        Product::query()->firstOrCreate(
            ['game_id' => $game->id, 'name' => 'Base Set Booster Pack'],
            [
                'set_id' => $set->id,
                'product_type' => 'sealed',
                'is_sealed' => true,
                'sku' => 'BASE-BOOSTER',
            ],
        );

        StorageLocation::query()->firstOrCreate(
            ['name' => 'Main Shelf'],
            [
                'type' => 'shelf',
                'code' => 'MAIN-SHELF',
                'description' => 'Default storage location',
            ],
        );
    }
}
