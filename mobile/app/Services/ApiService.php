<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class ApiService
{
    /**
     * Get Base API URL (from session, cache, or env)
     */
    public static function getBaseUrl(): string
    {
        return session('erp_api_url') 
            ?: Cache::get('erp_api_url') 
            ?: env('ERP_API_URL', 'http://127.0.0.1:8000/api/v1');
    }

    /**
     * Set Base API URL
     */
    public static function setBaseUrl(string $url): void
    {
        $cleanUrl = rtrim(trim($url), '/');
        if (!str_ends_with($cleanUrl, '/api/v1')) {
            if (str_ends_with($cleanUrl, '/api')) {
                $cleanUrl .= '/v1';
            } else {
                $cleanUrl .= '/api/v1';
            }
        }

        session(['erp_api_url' => $cleanUrl]);
        Cache::forever('erp_api_url', $cleanUrl);
    }

    /**
     * Get Stored Auth Token
     */
    public static function getToken(): ?string
    {
        return session('api_token') ?: request()->cookie('api_token');
    }

    /**
     * Set Auth Token & User Session
     */
    public static function setToken(string $token, array $userData = [], ?array $storeData = []): void
    {
        session([
            'api_token' => $token,
            'api_user'  => $userData,
            'api_store' => $storeData,
        ]);
        cookie()->queue('api_token', $token, 60 * 24 * 30);
    }

    /**
     * Clear Auth Session
     */
    public static function clearAuth(): void
    {
        session()->forget(['api_token', 'api_user', 'api_store', 'accessible_stores']);
        cookie()->queue(cookie()->forget('api_token'));
    }

    /**
     * Get Authenticated User from Session
     */
    public static function getUser(): ?array
    {
        return session('api_user');
    }

    /**
     * Get Active Store from Session
     */
    public static function getStore(): ?array
    {
        return session('api_store');
    }

    /**
     * Set Active Store
     */
    public static function setStore(array $storeData): void
    {
        session(['api_store' => $storeData]);
    }

    /**
     * Check if client is logged in
     */
    public static function isAuthenticated(): bool
    {
        return !empty(self::getToken());
    }

    /**
     * Build HTTP Request client with headers & token
     */
    protected static function client()
    {
        $token = self::getToken();
        $store = self::getStore();
        $storeId = $store['id'] ?? session('current_store_id') ?? 1;

        $headers = [
            'Accept'     => 'application/json',
            'X-Store-Id' => (string)$storeId,
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return Http::baseUrl(self::getBaseUrl())
            ->timeout(10)
            ->withHeaders($headers);
    }

    // ==========================================
    // 0. Dashboard Consolidated Fast Summary
    // ==========================================

    public static function getDashboardSummary(): array
    {
        try {
            $res = self::client()->get('/dashboard/summary');
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'          => false,
            'customers_count'  => 0,
            'suppliers_count'  => 0,
            'total_receivable' => '0.000',
            'total_payable'    => '0.000',
            'today_metrics'    => [],
            'current_shift'    => null,
            'has_active_shift' => false,
            'low_stock_count'  => 0,
            'recent_invoices'  => [],
            'recent_logs'      => [],
        ];
    }

    // ==========================================
    // 1. Authentication Endpoints
    // ==========================================

    public static function login(string $login, string $password): array
    {
        try {
            $response = self::client()->post('/auth/login', [
                'login'    => $login,
                'password' => $password,
            ]);

            if ($response->successful() && $response->json('success')) {
                $data = $response->json();
                self::setToken($data['token'], $data['user'] ?? [], $data['store'] ?? []);
                return [
                    'success' => true,
                    'user'    => $data['user'],
                    'store'   => $data['store'],
                    'token'   => $data['token'],
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'فشل تسجيل الدخول. يرجى التحقق من البيانات.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'تعذر الاتصال بسيرفر الـ ERP: ' . $e->getMessage(),
            ];
        }
    }

    public static function logout(): void
    {
        try {
            self::client()->post('/auth/logout');
        } catch (\Exception $e) {
            // ignore network error on logout
        }
        self::clearAuth();
    }

    // ==========================================
    // 2. Stores & Branches Endpoints
    // ==========================================

    public static function getStores(): array
    {
        try {
            $res = self::client()->get('/stores');
            if ($res->successful() && $res->json('success')) {
                $data = $res->json();
                if (!empty($data['active_store'])) {
                    self::setStore($data['active_store']);
                }
                session(['accessible_stores' => $data['stores'] ?? []]);
                return $data;
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'      => false,
            'active_store' => self::getStore(),
            'stores'       => session('accessible_stores', []),
        ];
    }

    public static function switchStore(int $storeId): array
    {
        try {
            $res = self::client()->post('/stores/switch', ['store_id' => $storeId]);
            if ($res->successful() && $res->json('success')) {
                $data = $res->json();
                if (!empty($data['active_store'])) {
                    self::setStore($data['active_store']);
                }
                return $data;
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل التبديل إلى هذا الفرع',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'تعذر الاتصال بالسيرفر: ' . $e->getMessage(),
            ];
        }
    }

    // ==========================================
    // 3. Customers Endpoints
    // ==========================================

    public static function getCustomers(string $search = '', string $status = 'all', int $page = 1): array
    {
        try {
            $res = self::client()->get('/customers', [
                'search' => $search,
                'status' => $status,
                'page'   => $page,
            ]);

            if ($res->status() === 401) {
                self::clearAuth();
                return ['success' => false, 'unauthorized' => true, 'data' => [], 'summary' => []];
            }

            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'data' => [], 'summary' => []];
    }

    public static function createCustomer(array $data): array
    {
        try {
            $res = self::client()->post('/customers', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل إضافة العميل',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function updateCustomer(int $id, array $data): array
    {
        try {
            $res = self::client()->put("/customers/{$id}", $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تعديل بيانات العميل',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function deleteCustomer(int $id): array
    {
        try {
            $res = self::client()->delete("/customers/{$id}");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل حذف العميل',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getCustomerStatement(int $id, ?string $fromDate = null, ?string $toDate = null): array
    {
        try {
            $res = self::client()->get("/customers/{$id}/statement", [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ]);

            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'customer' => [], 'ledger' => [], 'summary' => []];
    }

    // ==========================================
    // 4. Suppliers Endpoints
    // ==========================================

    public static function getSuppliers(string $search = '', string $status = 'all', int $page = 1): array
    {
        try {
            $res = self::client()->get('/suppliers', [
                'search' => $search,
                'status' => $status,
                'page'   => $page,
            ]);

            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'data' => [], 'summary' => []];
    }

    public static function createSupplier(array $data): array
    {
        try {
            $res = self::client()->post('/suppliers', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل إضافة المورد',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function updateSupplier(int $id, array $data): array
    {
        try {
            $res = self::client()->put("/suppliers/{$id}", $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تعديل بيانات المورد',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function deleteSupplier(int $id): array
    {
        try {
            $res = self::client()->delete("/suppliers/{$id}");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل حذف المورد',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getSupplierStatement(int $id, ?string $fromDate = null, ?string $toDate = null): array
    {
        try {
            $res = self::client()->get("/suppliers/{$id}/statement", [
                'from_date' => $fromDate,
                'to_date'   => $toDate,
            ]);

            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'supplier' => [], 'ledger' => [], 'summary' => []];
    }

    // ==========================================
    // 5. Items & Inventory Endpoints
    // ==========================================

    public static function getItems(string $search = '', string $category = 'all'): array
    {
        try {
            $res = self::client()->get('/items', [
                'search'   => $search,
                'category' => $category,
            ]);

            if ($res->successful() && $res->json('success')) {
                $json = $res->json();
                return [
                    'success'    => true,
                    'items'      => $json['data'] ?? [],
                    'data'       => $json['data'] ?? [],
                    'categories' => $json['categories'] ?? [],
                    'total'      => $json['total'] ?? 0,
                ];
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'items' => [], 'data' => [], 'categories' => []];
    }

    public static function getLowStockItems(): array
    {
        try {
            $res = self::client()->get('/items/low-stock');
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'count' => 0, 'low_items' => []];
    }

    public static function getActivityLogs(array $filters = []): array
    {
        try {
            $res = self::client()->get('/activity-logs', $filters);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'     => false,
            'logs'        => [],
            'total_count' => 0,
            'pagination'  => ['current_page' => 1, 'last_page' => 1, 'total' => 0]
        ];
    }

    // ==========================================
    // 6. POS & Sales Invoices Endpoints
    // ==========================================

    public static function getInvoices(string $search = '', string $status = 'all', ?string $fromDate = null, ?string $toDate = null, ?int $customerId = null, int $page = 1): array
    {
        try {
            $res = self::client()->get('/invoices', [
                'search'      => $search,
                'status'      => $status,
                'from_date'   => $fromDate,
                'to_date'     => $toDate,
                'customer_id' => $customerId,
                'page'        => $page,
            ]);

            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'data' => [], 'summary' => []];
    }

    public static function getInvoice(int $id): array
    {
        try {
            $res = self::client()->get("/invoices/{$id}");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'data' => []];
    }

    public static function createInvoice(array $data): array
    {
        try {
            $res = self::client()->post('/invoices', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل حفظ الفاتورة',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function cancelInvoice(int $id, string $reason = 'إلغاء من تطبيق الموبايل'): array
    {
        try {
            $res = self::client()->post("/invoices/{$id}/cancel", ['reason' => $reason]);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل إلغاء الفاتورة',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==========================================
    // 7. Payments & Vouchers (Receipts / Disbursements)
    // ==========================================

    public static function getPayments(string $type = 'all', ?string $fromDate = null, ?string $toDate = null, int $page = 1): array
    {
        try {
            $res = self::client()->get('/payments', [
                'type'      => $type,
                'from_date' => $fromDate,
                'to_date'   => $toDate,
                'page'      => $page,
            ]);

            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return ['success' => false, 'data' => [], 'summary' => []];
    }

    public static function createCustomerReceipt(array $data): array
    {
        try {
            $res = self::client()->post('/payments/customer-receipt', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تسجيل سند القبض',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function createSupplierVoucher(array $data): array
    {
        try {
            $res = self::client()->post('/payments/supplier-voucher', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تسجيل سند الصرف',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==========================================
    // 8. Treasury & Financial Summary Endpoints
    // ==========================================

    public static function getTreasurySummary(): array
    {
        try {
            $res = self::client()->get('/treasury/summary');
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success' => false,
            'today'   => [
                'sales_total'        => '0.000',
                'cash_collected'     => '0.000',
                'customer_receipts'  => '0.000',
                'total_inflow'       => '0.000',
                'supplier_paid'      => '0.000',
                'expenses_total'     => '0.000',
                'total_outflow'      => '0.000',
                'net_cash'           => '0.000',
            ],
            'balances' => [
                'total_receivable' => '0.000',
                'total_payable'    => '0.000',
            ],
        ];
    }

    // ==========================================
    // 9. In-App Auto Update & Version Checker
    // ==========================================

    public static function checkAppUpdate(): array
    {
        $currentVersion = env('APP_VERSION', '1.0.0');
        $versionCode = (int)env('APP_VERSION_CODE', 1);

        try {
            $res = Http::timeout(2)->get(self::getBaseUrl() . '/app/version', [
                'current_version' => $currentVersion,
                'version_code'    => $versionCode,
            ]);

            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // Ignore network timeouts gracefully
        }

        return [
            'success'             => true,
            'has_update'          => false,
            'force_update'        => false,
            'current_app_version' => $currentVersion,
        ];
    }

    // ==========================================
    // 10. Cashier Shifts & Z-Report Endpoints
    // ==========================================

    public static function getCurrentShift(): array
    {
        try {
            $res = self::client()->get('/shifts/current');
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'      => false,
            'has_active'   => false,
            'active_shift' => null,
            'metrics'      => null,
        ];
    }

    public static function openShift(array $data): array
    {
        try {
            $res = self::client()->post('/shifts/open', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل فتح الوردية',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function closeShift(array $data): array
    {
        try {
            $res = self::client()->post('/shifts/close', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تقفيل الوردية',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function getZReport(int $id): array
    {
        try {
            $res = self::client()->get("/shifts/{$id}/z-report");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success' => false,
            'report'  => null,
        ];
    }

    // ==========================================
    // 11. Expenses & Petty Cash Endpoints
    // ==========================================

    public static function getExpenses(array $filters = []): array
    {
        try {
            $res = self::client()->get('/expenses', $filters);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'          => false,
            'expenses'         => [],
            'total_amount'     => '0.000',
            'total_count'      => 0,
            'quick_categories' => [
                'شنط وأكياس',
                'أكواب ورقية وبلاستيكية',
                'لاصق وشرائط تغليف',
                'بوفيه وضيافة',
                'صيانة مطاحن ومعدات',
                'إيجار وكهرباء ومرافق',
                'نثريات ومصاريف تشغيل',
            ],
            'pagination'       => ['current_page' => 1, 'last_page' => 1, 'total' => 0]
        ];
    }

    public static function createExpense(array $data): array
    {
        try {
            $res = self::client()->post('/expenses', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تسجيل المصروف',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function updateExpense(int $id, array $data): array
    {
        try {
            $res = self::client()->put("/expenses/{$id}", $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تعديل المصروف',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function deleteExpense(int $id): array
    {
        try {
            $res = self::client()->delete("/expenses/{$id}");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل حذف المصروف',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==========================================
    // 12. Purchases & Coffee Inbound Endpoints
    // ==========================================

    public static function getPurchases(array $filters = []): array
    {
        try {
            $res = self::client()->get('/purchases', $filters);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'         => false,
            'purchases'       => [],
            'total_purchases' => '0.000',
            'total_remaining' => '0.000',
            'total_count'     => 0,
            'pagination'      => ['current_page' => 1, 'last_page' => 1, 'total' => 0]
        ];
    }

    public static function getPurchase(int $id): array
    {
        try {
            $res = self::client()->get("/purchases/{$id}");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'  => false,
            'purchase' => null,
        ];
    }

    public static function createPurchase(array $data): array
    {
        try {
            $res = self::client()->post('/purchases', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تسجيل فاتورة المشتريات',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function cancelPurchase(int $id, string $reason = ''): array
    {
        try {
            $res = self::client()->post("/purchases/{$id}/cancel", ['reason' => $reason]);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل إلغاء فاتورة المشتريات',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==========================================
    // 13. Profit & Loss Reports & Analytics
    // ==========================================

    public static function getReportSummary(array $filters = []): array
    {
        try {
            $res = self::client()->get('/reports/summary', $filters);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success' => false,
            'period'  => ['preset' => 'this_month'],
            'metrics' => [
                'total_sales'       => '0.000',
                'total_cogs'        => '0.000',
                'gross_profit'      => '0.000',
                'total_expenses'    => '0.000',
                'expenses_count'    => 0,
                'net_profit'        => '0.000',
                'profit_margin_pct' => '0.00',
                'total_paid'        => '0.000',
                'total_remaining'   => '0.000',
                'invoice_count'     => 0,
                'average_ticket'    => '0.00',
            ]
        ];
    }

    public static function getReportTopItems(array $filters = []): array
    {
        try {
            $res = self::client()->get('/reports/top-items', $filters);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'   => false,
            'top_items' => [],
        ];
    }

    public static function getItemCard(int $id): array
    {
        try {
            $res = self::client()->get("/reports/items/{$id}/card");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'   => false,
            'item'      => null,
            'movements' => [],
        ];
    }

    // ==========================================
    // 14. Returns (Sales & Purchase Returns)
    // ==========================================

    public static function getReturns(array $filters = []): array
    {
        try {
            $res = self::client()->get('/returns', $filters);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'       => false,
            'returns'       => [],
            'total_returns' => '0.000',
            'total_count'   => 0,
            'pagination'    => ['current_page' => 1, 'last_page' => 1, 'total' => 0]
        ];
    }

    public static function createSalesReturn(array $data): array
    {
        try {
            $res = self::client()->post('/returns/sales', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تسجيل مرتجع المبيعات',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function createPurchaseReturn(array $data): array
    {
        try {
            $res = self::client()->post('/returns/purchases', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل تسجيل مرتجع المشتريات',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function cancelReturn(int $id, string $reason = ''): array
    {
        try {
            $res = self::client()->post("/returns/{$id}/cancel", ['reason' => $reason]);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل إلغاء المرتجع',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==========================================
    // 15. Stock Transfers between stores
    // ==========================================

    public static function getTransfers(array $filters = []): array
    {
        try {
            $res = self::client()->get('/transfers', $filters);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'     => false,
            'transfers'   => [],
            'total_count' => 0,
            'pagination'  => ['current_page' => 1, 'last_page' => 1, 'total' => 0]
        ];
    }

    public static function getTransfer(int $id): array
    {
        try {
            $res = self::client()->get("/transfers/{$id}");
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'  => false,
            'transfer' => null,
        ];
    }

    public static function createTransfer(array $data): array
    {
        try {
            $res = self::client()->post('/transfers', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل التحويل المخزني',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function cancelTransfer(int $id, string $reason = ''): array
    {
        try {
            $res = self::client()->post("/transfers/{$id}/cancel", ['reason' => $reason]);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل إلغاء التحويل المخزني',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ==========================================
    // 16. Admin System Settings
    // ==========================================

    public static function getSettings(): array
    {
        try {
            $res = self::client()->get('/settings');
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
        } catch (\Exception $e) {
            // ignore
        }

        return [
            'success'     => false,
            'settings'    => [],
            'stores'      => [],
            'users_count' => 0,
        ];
    }

    public static function updateSettings(array $data): array
    {
        try {
            $res = self::client()->post('/settings', $data);
            if ($res->successful() && $res->json('success')) {
                return $res->json();
            }
            return [
                'success' => false,
                'message' => $res->json('message') ?? 'فشل حفظ الإعدادات',
                'errors'  => $res->json('errors') ?? [],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}






