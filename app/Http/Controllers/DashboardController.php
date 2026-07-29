<?php

namespace App\Http\Controllers;

use App\Models\CosmeticProduct;
use App\Models\FashionProduct;
use App\Models\HomeNeedProduct;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MotorcycleProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShoeProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();

        $totalInvoiceRevenue = (float) (clone $this->billableInvoices())
            ->sum('grand_total');

        $totalInvoiceOrders = (clone $this->billableInvoices())
            ->count();

        $websiteOrdersTotal = Order::query()->count();

        $inventoryDepartments = $this->inventoryDepartments();
        $lowStockItems = $this->lowStockItems();
        $totalProducts = collect($inventoryDepartments)->sum('total');
        $activeProducts = collect($inventoryDepartments)->sum('active');
        $lowStockCount = collect($inventoryDepartments)->sum('low_stock');

        return Inertia::render('Dashboard/Index', [
            'message' => 'Welcome to the Dashboard ',
            'dashboard' => [
                'generated_at' => $now->format('M d, Y h:i A'),
                'kpis' => [
                    [
                        'key' => 'revenue',
                        'label' => 'Invoice Revenue',
                        'value' => round($totalInvoiceRevenue, 2),
                        'format' => 'currency',
                        'caption' => 'from saved invoices',
                    ],
                    [
                        'key' => 'orders',
                        'label' => 'Invoice Orders',
                        'value' => $totalInvoiceOrders,
                        'format' => 'number',
                        'caption' => 'non-cancelled invoices',
                    ],
                    [
                        'key' => 'website_orders',
                        'label' => 'Website Orders',
                        'value' => $websiteOrdersTotal,
                        'format' => 'number',
                        'caption' => 'checkout records',
                    ],
                    [
                        'key' => 'catalog',
                        'label' => 'Active Catalog',
                        'value' => $activeProducts,
                        'format' => 'number',
                        'caption' => $totalProducts . ' total, ' . $lowStockCount . ' low stock',
                    ],
                ],
                'summary' => [
                    'website_orders_total' => $websiteOrdersTotal,
                    'low_stock_count' => $lowStockCount,
                    'total_products' => $totalProducts,
                    'active_products' => $activeProducts,
                ],
                'charts' => [
                    'daily_revenue' => $this->dailyRevenueSeries($now),
                    'monthly_orders' => $this->monthlyOrdersSeries($now),
                    'department_mix' => $this->departmentMix($monthStart),
                ],
                'order_pipeline' => $this->orderPipeline(),
                'inventory_departments' => $inventoryDepartments,
                'recent_orders' => $this->recentOrders(),
                'low_stock_items' => $lowStockItems,
            ],
        ]);
    }

    private function billableInvoices(): Builder
    {
        return $this->applyBillableInvoiceScope(Invoice::query());
    }

    private function applyBillableInvoiceScope(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('status')
                ->orWhere('status', '!=', 'cancelled');
        });
    }

    private function dailyRevenueSeries(Carbon $now): array
    {
        $start = $now->copy()->subDays(13)->startOfDay();
        $rows = (clone $this->billableInvoices())
            ->whereBetween('created_at', [$start, $now])
            ->get(['created_at', 'grand_total'])
            ->groupBy(fn (Invoice $invoice) => $invoice->created_at?->format('Y-m-d'));

        $labels = [];
        $data = [];

        for ($date = $start->copy(); $date->lte($now); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('M j');
            $data[] = round((float) ($rows->get($key, collect())->sum(fn (Invoice $invoice) => (float) $invoice->grand_total)), 2);
        }

        return compact('labels', 'data');
    }

    private function monthlyOrdersSeries(Carbon $now): array
    {
        $start = $now->copy()->subMonthsNoOverflow(5)->startOfMonth();
        $rows = (clone $this->billableInvoices())
            ->whereBetween('created_at', [$start, $now])
            ->get(['created_at'])
            ->groupBy(fn (Invoice $invoice) => $invoice->created_at?->format('Y-m'));

        $labels = [];
        $data = [];

        for ($date = $start->copy(); $date->lte($now); $date->addMonthNoOverflow()) {
            $key = $date->format('Y-m');
            $labels[] = $date->format('M');
            $data[] = $rows->get($key, collect())->count();
        }

        return compact('labels', 'data');
    }

    private function departmentMix(Carbon $monthStart): array
    {
        $items = InvoiceItem::query()
            ->whereHas('invoice', function (Builder $query) use ($monthStart) {
                $this->applyBillableInvoiceScope($query)->where('created_at', '>=', $monthStart);
            })
            ->get(['product_type', 'qty', 'line_total']);

        $groups = $items->groupBy(fn (InvoiceItem $item) => $this->normalizeProductType($item->product_type));

        return collect($this->departmentLabels())
            ->map(function (string $label, string $key) use ($groups) {
                $rows = $groups->get($key, collect());

                return [
                    'key' => $key,
                    'label' => $label,
                    'units' => (int) $rows->sum(fn (InvoiceItem $item) => (int) $item->qty),
                    'revenue' => round((float) $rows->sum(fn (InvoiceItem $item) => (float) $item->line_total), 2),
                ];
            })
            ->filter(fn (array $row) => $row['units'] > 0 || $row['revenue'] > 0)
            ->values()
            ->all();
    }

    private function orderPipeline(): array
    {
        $counts = Invoice::query()
            ->selectRaw('order_status, COUNT(*) as total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status');

        return collect([
            'reserved' => 'Reserved',
            'confirmed' => 'Confirmed',
            'dispatched' => 'Dispatched',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ])->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'count' => (int) ($counts[$key] ?? 0),
        ])->values()->all();
    }

    private function recentOrders(): array
    {
        return Invoice::query()
            ->withCount('items')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn (Invoice $invoice) => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'customer' => $invoice->customer_name ?: 'Walk-in customer',
                'amount' => (float) $invoice->grand_total,
                'status' => $invoice->order_status ?: $invoice->status,
                'payment_type' => $invoice->payment_type ?: 'unpaid',
                'items_count' => (int) ($invoice->items_count ?? 0),
                'date' => $invoice->created_at?->format('M d, Y'),
                'href' => route('invoices.edit', $invoice->id),
            ])
            ->values()
            ->all();
    }

    private function inventoryDepartments(): array
    {
        return [
            [
                'key' => 'electronics',
                'label' => 'Electronics',
                'total' => Product::query()->count(),
                'active' => Product::query()->where('status', 'active')->count(),
                'low_stock' => $this->electronicsLowStockQuery()->count(),
                'href' => route('products.index'),
            ],
            [
                'key' => 'cosmetics',
                'label' => 'Beauty Glow',
                'total' => CosmeticProduct::query()->count(),
                'active' => CosmeticProduct::query()->where('status', 'active')->count(),
                'low_stock' => $this->cosmeticsLowStockQuery()->count(),
                'href' => route('admin.cosmetics.products.index'),
            ],
            [
                'key' => 'motorcycle',
                'label' => 'Motorcycle',
                'total' => MotorcycleProduct::query()->count(),
                'active' => MotorcycleProduct::query()->where('status', 'active')->count(),
                'low_stock' => $this->moduleLowStockQuery(MotorcycleProduct::query())->count(),
                'href' => route('admin.motorcycles.products.index'),
            ],
            [
                'key' => 'fashion',
                'label' => 'Fashion',
                'total' => FashionProduct::query()->count(),
                'active' => FashionProduct::query()->where('status', 'active')->count(),
                'low_stock' => $this->moduleLowStockQuery(FashionProduct::query())->count(),
                'href' => route('admin.fashion.products.index'),
            ],
            [
                'key' => 'home-needs',
                'label' => 'Home Needs',
                'total' => HomeNeedProduct::query()->count(),
                'active' => HomeNeedProduct::query()->where('status', 'active')->count(),
                'low_stock' => $this->moduleLowStockQuery(HomeNeedProduct::query())->count(),
                'href' => route('admin.home-needs.products.index'),
            ],
            [
                'key' => 'shoes',
                'label' => 'Shoes',
                'total' => ShoeProduct::query()->count(),
                'active' => ShoeProduct::query()->where('status', 'published')->count(),
                'low_stock' => $this->shoeLowStockQuery()->count(),
                'href' => route('admin.shoes.products.index'),
            ],
        ];
    }

    private function lowStockItems(): array
    {
        return collect()
            ->merge($this->mapLowStockItems($this->electronicsLowStockQuery(), 'electronics', 'Electronics', 'model', 'stock_count', route('products.index')))
            ->merge($this->mapLowStockItems($this->cosmeticsLowStockQuery(), 'cosmetics', 'Beauty Glow', 'name', 'stock', route('admin.cosmetics.products.index')))
            ->merge($this->mapLowStockItems($this->moduleLowStockQuery(MotorcycleProduct::query()), 'motorcycle', 'Motorcycle', 'name', 'stock_quantity', route('admin.motorcycles.products.index')))
            ->merge($this->mapLowStockItems($this->moduleLowStockQuery(FashionProduct::query()), 'fashion', 'Fashion', 'name', 'stock_quantity', route('admin.fashion.products.index')))
            ->merge($this->mapLowStockItems($this->moduleLowStockQuery(HomeNeedProduct::query()), 'home-needs', 'Home Needs', 'name', 'stock_quantity', route('admin.home-needs.products.index')))
            ->merge($this->mapLowStockItems($this->shoeLowStockQuery(), 'shoes', 'Shoes', 'name', 'stock_quantity', route('admin.shoes.products.index')))
            ->sortBy('stock')
            ->take(8)
            ->values()
            ->all();
    }

    private function mapLowStockItems(Builder $query, string $key, string $label, string $nameColumn, string $stockColumn, string $href): Collection
    {
        return $query
            ->latest('updated_at')
            ->take(4)
            ->get()
            ->map(fn ($product) => [
                'id' => $product->id,
                'key' => $key,
                'department' => $label,
                'name' => $product->{$nameColumn} ?: 'Product',
                'stock' => (int) ($product->{$stockColumn} ?? 0),
                'href' => $href,
            ]);
    }

    private function electronicsLowStockQuery(): Builder
    {
        return Product::query()
            ->where('status', 'active')
            ->where(function (Builder $query) {
                $query->where('in_stock', false)
                    ->orWhereRaw('stock_count <= COALESCE(low_stock_alert_quantity, 5)');
            });
    }

    private function cosmeticsLowStockQuery(): Builder
    {
        return CosmeticProduct::query()
            ->where('status', 'active')
            ->whereNotNull('stock')
            ->where('stock', '<=', 5);
    }

    private function moduleLowStockQuery(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function (Builder $query) {
                $query->where('stock_status', 'out_of_stock')
                    ->orWhereRaw('stock_quantity <= COALESCE(low_stock_alert_quantity, 5)');
            });
    }

    private function shoeLowStockQuery(): Builder
    {
        return ShoeProduct::query()
            ->where('status', 'published')
            ->where(function (Builder $query) {
                $query->where('stock_status', 'out_of_stock')
                    ->orWhereRaw('stock_quantity <= COALESCE(low_stock_alert_quantity, 5)');
            });
    }

    private function normalizeProductType(?string $value): string
    {
        $type = str_replace('_', '-', mb_strtolower(trim((string) $value)));

        return match ($type) {
            'tech', 'electronic', 'electronics' => 'electronics',
            'cosmetic', 'cosmetics' => 'cosmetics',
            'motorcycle', 'motorcycles', 'motorcycle-products' => 'motorcycle',
            'fashion', 'fashion-accessories' => 'fashion',
            'home-need', 'home-needs' => 'home-needs',
            'shoe', 'shoes' => 'shoes',
            default => $type ?: 'electronics',
        };
    }

    private function departmentLabels(): array
    {
        return [
            'electronics' => 'Electronics',
            'cosmetics' => 'Beauty Glow',
            'motorcycle' => 'Motorcycle',
            'fashion' => 'Fashion',
            'home-needs' => 'Home Needs',
            'shoes' => 'Shoes',
        ];
    }
}
