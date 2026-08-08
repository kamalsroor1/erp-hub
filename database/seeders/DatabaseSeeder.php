<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Supplier;
use App\Services\StockService;
use App\Services\InvoiceService;

class DatabaseSeeder extends Seeder
{
    public function run(StockService $stockService, InvoiceService $invoiceService): void
    {
        // 1. Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@sroor.com'],
            [
                'name' => 'المدير العام',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );

        // 2. Specialized Customers (Cafes, Supermarkets, Retail walk-ins)
        $c1 = Customer::firstOrCreate(['name' => 'كافيه وكوفي شوب البستان'], [
            'phone' => '01012345678',
            'address' => 'القاهرة - التجمع الخامس',
            'current_balance' => '0.000',
            'is_active' => true,
        ]);

        $c2 = Customer::firstOrCreate(['name' => 'سوبر ماركت النور والبركة'], [
            'phone' => '01198765432',
            'address' => 'الجيزة - المهندسين',
            'current_balance' => '0.000',
            'is_active' => true,
        ]);

        $c3 = Customer::firstOrCreate(['name' => 'عميل نقدي مطحنة وبن (POS)'], [
            'phone' => '01200000000',
            'address' => 'مبيعات مباشرة بالمحل',
            'current_balance' => '0.000',
            'is_active' => true,
        ]);

        // 3. Specialized Coffee & Tea Importers / Suppliers
        $s1 = Supplier::firstOrCreate(['name' => 'شركة الأهرام لاستيراد البن الأخضر والشاي'], [
            'company_name' => 'مجموعة الأهرام للبن',
            'phone' => '01099887766',
            'address' => 'الإسكندرية - ميناء الدخيلة',
            'current_balance' => '0.000',
            'is_active' => true,
        ]);

        $s2 = Supplier::firstOrCreate(['name' => 'مؤسسة النيل لتوزيع الشاي والنسكافيه'], [
            'company_name' => 'النيل للتجارة والتوزيع',
            'phone' => '01555443322',
            'address' => 'القاهرة - العبور',
            'current_balance' => '0.000',
            'is_active' => true,
        ]);

        // 4. Rich Coffee, Tea, Herbs & Nescafé Catalog (By Gram, Kg, and Piece)
        $itemsData = [
            // --- بن وتوليفات ومطاحن (بالكيلو والجرام) ---
            [
                'code' => 'COF-001',
                'name' => 'بن برازيلي سانتوس فاخر (محمص حبوب/مطحون)',
                'category' => 'بن وتوليفات',
                'unit' => 'كجم',
                'current_stock' => '150.000', // 150 كجم
                'cost_price' => '420.000', // تكلفة الكيلو
                'weighted_avg_cost' => '420.000',
                'selling_price' => '540.000', // سعر بيع الكيلو (الـ 250جم بـ 135 ج.م)
                'min_stock_level' => '20.000',
                'notes' => 'يباع بالكيلو ومضاعفات الجرام (125جم، 250جم، 500جم)',
            ],
            [
                'code' => 'COF-002',
                'name' => 'بن كولومبي سوبريمو أرابيكا 100%',
                'category' => 'بن وتوليفات',
                'unit' => 'كجم',
                'current_stock' => '80.000',
                'cost_price' => '580.000',
                'weighted_avg_cost' => '580.000',
                'selling_price' => '720.000',
                'min_stock_level' => '15.000',
                'notes' => 'أرابيكا نقية نكهة فاكهية وقوام غني',
            ],
            [
                'code' => 'COF-003',
                'name' => 'بن حبشي يرجاشيفي مميز (إثيوبي)',
                'category' => 'بن وتوليفات',
                'unit' => 'كجم',
                'current_stock' => '60.000',
                'cost_price' => '510.000',
                'weighted_avg_cost' => '510.000',
                'selling_price' => '660.000',
                'min_stock_level' => '10.000',
                'notes' => 'بن حبشي عالي الإيحاءات العطرية',
            ],
            [
                'code' => 'COF-004',
                'name' => 'توليفة بن سرور محوج خصوصي (حبهان ومستكة وقرنفل)',
                'category' => 'بن وتوليفات',
                'unit' => 'كجم',
                'current_stock' => '100.000',
                'cost_price' => '480.000',
                'weighted_avg_cost' => '480.000',
                'selling_price' => '620.000',
                'min_stock_level' => '25.000',
                'notes' => 'التوليفة الأكثر مبيعاً بالمحل بالميزان الدقيق',
            ],
            [
                'code' => 'COF-005',
                'name' => 'بن يمني مطري أصلي درجة أولى',
                'category' => 'بن وتوليفات',
                'unit' => 'كجم',
                'current_stock' => '25.000',
                'cost_price' => '850.000',
                'weighted_avg_cost' => '850.000',
                'selling_price' => '1100.000',
                'min_stock_level' => '5.000',
                'notes' => 'أفخر أنواع البن العربي الأصيل',
            ],

            // --- شاي وأعشاب (فرط بالكيلو والجرام + علب) ---
            [
                'code' => 'TEA-001',
                'name' => 'شاي سيلاني فاخر فرط أسود خرز O.P (بالوزن)',
                'category' => 'شاي وأعشاب',
                'unit' => 'كجم',
                'current_stock' => '120.000',
                'cost_price' => '260.000',
                'weighted_avg_cost' => '260.000',
                'selling_price' => '360.000',
                'min_stock_level' => '20.000',
                'notes' => 'شاي فرط عالي الجودة بالميزان',
            ],
            [
                'code' => 'TEA-002',
                'name' => 'شاي أخضر صيني إبر فرط نقي (بالجرام والكيلو)',
                'category' => 'شاي وأعشاب',
                'unit' => 'كجم',
                'current_stock' => '45.000',
                'cost_price' => '310.000',
                'weighted_avg_cost' => '310.000',
                'selling_price' => '440.000',
                'min_stock_level' => '10.000',
            ],
            [
                'code' => 'TEA-003',
                'name' => 'كرتونة شاي العروسة (50 باكت 250جم)',
                'category' => 'شاي وأعشاب',
                'unit' => 'كرتونة',
                'current_stock' => '35.000',
                'cost_price' => '2100.000',
                'weighted_avg_cost' => '2100.000',
                'selling_price' => '2350.000',
                'min_stock_level' => '5.000',
            ],
            [
                'code' => 'TEA-004',
                'name' => 'شاي ليبتون العلامة الصفراء 100 فتلة (علبة)',
                'category' => 'شاي وأعشاب',
                'unit' => 'علبة',
                'current_stock' => '80.000',
                'cost_price' => '92.000',
                'weighted_avg_cost' => '92.000',
                'selling_price' => '115.000',
                'min_stock_level' => '15.000',
            ],

            // --- نسكافيه ومشروبات سريعة التحضير ---
            [
                'code' => 'NES-001',
                'name' => 'نسكافيه جولد برطمان زجاج 200 جم أصلي',
                'category' => 'نسكافيه ومشروبات سريعة',
                'unit' => 'برطمان',
                'current_stock' => '50.000',
                'cost_price' => '280.000',
                'weighted_avg_cost' => '280.000',
                'selling_price' => '340.000',
                'min_stock_level' => '10.000',
            ],
            [
                'code' => 'NES-002',
                'name' => 'نسكافيه كلاسيك أحمر برطمان 200 جم',
                'category' => 'نسكافيه ومشروبات سريعة',
                'unit' => 'برطمان',
                'current_stock' => '60.000',
                'cost_price' => '195.000',
                'weighted_avg_cost' => '195.000',
                'selling_price' => '240.000',
                'min_stock_level' => '12.000',
            ],
            [
                'code' => 'NES-003',
                'name' => 'كوفي مكس 3 في 1 (علبة 24 ظرف)',
                'category' => 'نسكافيه ومشروبات سريعة',
                'unit' => 'علبة',
                'current_stock' => '70.000',
                'cost_price' => '110.000',
                'weighted_avg_cost' => '110.000',
                'selling_price' => '135.000',
                'min_stock_level' => '15.000',
            ],
            [
                'code' => 'NES-004',
                'name' => 'كاكاو خام إسباني فاخر (بالوزن كجم/جرام)',
                'category' => 'نسكافيه ومشروبات سريعة',
                'unit' => 'كجم',
                'current_stock' => '40.000',
                'cost_price' => '320.000',
                'weighted_avg_cost' => '320.000',
                'selling_price' => '420.000',
                'min_stock_level' => '8.000',
            ],

            // --- تحبيشات وإضافات البن والمطحنة (بالجرام) ---
            [
                'code' => 'SPICE-001',
                'name' => 'حبهان (هيل) أخضر هندي جامبو درجة أولى (بالجرام)',
                'category' => 'تحبيشات وإضافات',
                'unit' => 'كجم',
                'current_stock' => '15.000', // 15 كجم (15000 جم)
                'cost_price' => '1600.000', // 1600 ج.م للكيلو (1.6 ج.م للجرام)
                'weighted_avg_cost' => '1600.000',
                'selling_price' => '2100.000', // 2.10 ج.م للجرام
                'min_stock_level' => '2.000',
                'notes' => 'لتحويج البن بالمطحنة بالجرامات الدقيقة',
            ],
            [
                'code' => 'SPICE-002',
                'name' => 'مستكة يوناني حر طبيعي فصوص (بالجرام)',
                'category' => 'تحبيشات وإضافات',
                'unit' => 'كجم',
                'current_stock' => '8.000',
                'cost_price' => '4500.000',
                'weighted_avg_cost' => '4500.000',
                'selling_price' => '5800.000',
                'min_stock_level' => '1.000',
                'notes' => 'مستكة أصلية بالجرام',
            ],
        ];

        foreach ($itemsData as $data) {
            $item = Item::firstOrCreate(['code' => $data['code']], [
                'name'              => $data['name'],
                'category'          => $data['category'],
                'unit'              => $data['unit'],
                'current_stock'     => '0.000',
                'cost_price'        => $data['cost_price'],
                'weighted_avg_cost' => $data['weighted_avg_cost'],
                'selling_price'     => $data['selling_price'],
                'min_stock_level'   => $data['min_stock_level'],
                'notes'             => $data['notes'] ?? null,
                'is_active'         => true,
            ]);

            if (bccomp($item->current_stock, '0.000', 3) === 0) {
                $stockService->depositStock(
                    item: $item,
                    quantity: $data['current_stock'],
                    costPrice: $data['cost_price'],
                    depositType: 'opening_balance',
                    reason: 'رصيد افتتاحي لمخزن ومطحنة البن والشاي'
                );
            }
        }

        // 5. Sample Retail & Cafe Invoices with Fractional Quantities (250g, 500g, 125g)
        $cof1 = Item::where('code', 'COF-001')->first(); // بن برازيلي
        $cof4 = Item::where('code', 'COF-004')->first(); // بن محوج
        $tea1 = Item::where('code', 'TEA-001')->first(); // شاي سيلاني

        if ($cof1 && $cof4 && $tea1) {
            // Invoice 1: Cafe buying 2.500 kg Brazilian + 1.250 kg Special blend
            $invoiceService->confirmInvoice([
                'customer_id'    => $c1->id,
                'invoice_date'   => now()->toDateString(),
                'payment_type'   => 'cash',
                'discount_type'  => 'fixed',
                'discount_value' => '25.000',
                'paid_amount'    => '2100.000',
                'notes'          => 'طلبية كافيه: 2.5 كجم بن برازيلي + 1.25 كجم محوج',
                'items'          => [
                    [
                        'item_id'         => $cof1->id,
                        'quantity'        => '2.500', // 2.5 كجم
                        'unit_price'      => $cof1->selling_price,
                        'discount_amount' => '0.000',
                    ],
                    [
                        'item_id'         => $cof4->id,
                        'quantity'        => '1.250', // 1 وربع كجم (1250 جم)
                        'unit_price'      => $cof4->selling_price,
                        'discount_amount' => '0.000',
                    ],
                ],
            ]);

            // Invoice 2: Walk-in retail customer: ربع كيلو بن محوج (0.250 كجم) + ثمن كيلو شاي (0.125 كجم)
            $invoiceService->confirmInvoice([
                'customer_id'    => $c3->id,
                'invoice_date'   => now()->toDateString(),
                'payment_type'   => 'cash',
                'discount_type'  => 'fixed',
                'discount_value' => '0.000',
                'paid_amount'    => '200.000',
                'notes'          => 'بيع قطاعي: ربع بن محوج (250جم) + ثمن شاي سيلاني (125جم)',
                'items'          => [
                    [
                        'item_id'         => $cof4->id,
                        'quantity'        => '0.250', // ربع كيلو
                        'unit_price'      => $cof4->selling_price,
                        'discount_amount' => '0.000',
                    ],
                    [
                        'item_id'         => $tea1->id,
                        'quantity'        => '0.125', // ثمن كيلو (125 جم)
                        'unit_price'      => $tea1->selling_price,
                        'discount_amount' => '0.000',
                    ],
                ],
            ]);
        }
    }
}
