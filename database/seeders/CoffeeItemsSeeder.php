<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Store;
use App\Models\StoreStock;

class CoffeeItemsSeeder extends Seeder
{
    public function run(): void
    {
        $itemsData = [
            ['code' => 'COF-BR-01', 'name' => 'بن برازيلي سانتوس خام (أخضر)', 'category' => 'بن خام', 'unit' => 'كجم', 'cost_price' => '320.000', 'selling_price' => '380.000', 'min_stock_level' => '50.000'],
            ['code' => 'COF-COL-02', 'name' => 'بن كولومبي سوبريمو خام', 'category' => 'بن خام', 'unit' => 'كجم', 'cost_price' => '450.000', 'selling_price' => '520.000', 'min_stock_level' => '30.000'],
            ['code' => 'COF-IND-03', 'name' => 'بن هندي روبوستا فاخر AB', 'category' => 'بن خام', 'unit' => 'كجم', 'cost_price' => '260.000', 'selling_price' => '310.000', 'min_stock_level' => '100.000'],
            ['code' => 'COF-ETH-04', 'name' => 'بن حبشي يرجاشيف محمص وسط', 'category' => 'بن محمص', 'unit' => 'كجم', 'cost_price' => '510.000', 'selling_price' => '620.000', 'min_stock_level' => '20.000'],
            ['code' => 'BLD-SR-01', 'name' => 'توليفة سرور الممتازة - اسبريسو بلند', 'category' => 'توليفات وخلطات', 'unit' => 'كجم', 'cost_price' => '420.000', 'selling_price' => '540.000', 'min_stock_level' => '40.000'],
            ['code' => 'BLD-TURK-02', 'name' => 'توليفة بن تركي محوج بالحبهان الخاص', 'category' => 'توليفات وخلطات', 'unit' => 'كجم', 'cost_price' => '480.000', 'selling_price' => '600.000', 'min_stock_level' => '25.000'],
        ];

        foreach ($itemsData as $data) {
            $item = Item::firstOrCreate(['code' => $data['code']], $data);
            $item->update(['current_stock' => '450.000']);

            foreach (Store::all() as $store) {
                StoreStock::updateOrCreate(
                    ['store_id' => $store->id, 'item_id' => $item->id],
                    ['quantity' => '150.000', 'custom_selling_price' => null]
                );
            }
        }
    }
}
