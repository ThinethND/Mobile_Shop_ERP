<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceOrderStatusMail;
use App\Models\ColorOption;
use App\Models\CosmeticProduct;
use App\Models\CosmeticSizeVolume;
use App\Models\FashionProduct;
use App\Models\HomeNeedProduct;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MotorcycleProduct;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShoeProduct;
use App\Models\StorageOption;
use App\Models\WarrantyOption;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    protected const ORDER_STATUSES = [
        'reserved',
        'confirmed',
        'dispatched',
        'delivered',
        'cancelled',
    ];

    protected const PAYMENT_TYPES = [
        'unpaid',
        'mixed',
        'cash',
        'card',
        'advance',
    ];

    protected const PRODUCT_TYPES = [
        'electronics',
        'tech',
        'motorcycle',
        'cosmetics',
        'fashion',
        'home-needs',
    ];

    public function index()
    {
        return Inertia::render('Invoice/index', [
            'message' => 'Orders',
        ]);
    }

    public function create()
    {
        return Inertia::render('Invoice/partials/CreateUpdate', [
            'mode' => 'create',
            'invoice' => null,
            'catalogProducts' => $this->catalogProductsPayload(),
            'techProducts' => [],
            'shoeProducts' => [],
            'shop' => $this->shopInfo(),
            'nextInvoiceNo' => $this->nextInvoiceNumber(),
        ]);
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load('items');

        return Inertia::render('Invoice/partials/CreateUpdate', [
            'mode' => 'edit',
            'invoice' => [
                'id' => $invoice->id,
                'invoice_no' => $invoice->invoice_no,
                'invoice_date' => optional($invoice->invoice_date)->format('Y-m-d'),
                'customer_name' => $invoice->customer_name,
                'customer_contact_number' => $invoice->customer_contact_number,
                'customer_address' => $invoice->customer_address,
                'customer_email' => $invoice->customer_email,
                'sales_person' => $invoice->sales_person,
                'ship_date' => optional($invoice->ship_date)->format('Y-m-d'),
                'ship_via' => $invoice->ship_via,
                'delivery_enabled' => (bool) $invoice->delivery_enabled,
                'delivery_method' => $invoice->delivery_method,
                'delivery_payment_status' => $invoice->delivery_payment_status,
                'tracking_id' => $invoice->tracking_id,
                'delivery_agent' => $invoice->delivery_agent,
                'delivery_amount' => (float) ($invoice->delivery_amount ?? 0),
                'payment_type' => $invoice->payment_type,
                'cash_paid' => (float) $invoice->cash_paid,
                'card_paid' => (float) $invoice->card_paid,
                'advance_amount' => (float) $invoice->advance_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'subtotal' => (float) $invoice->subtotal,
                'total_discount' => (float) $invoice->total_discount,
                'tax_amount' => (float) $invoice->tax_amount,
                'grand_total' => (float) $invoice->grand_total,
                'balance_due' => (float) $invoice->balance_due,
                'notes' => $invoice->notes,
                'terms' => $invoice->terms,
                'status' => $invoice->status,
                'order_status' => $invoice->order_status ?? 'reserved',
                'pdf_path' => $invoice->pdf_path,
                'pdf_url' => $invoice->pdf_url,
                'items' => $invoice->items->map(fn(InvoiceItem $item) => [
                    'id' => $item->id,
                    'item_no' => $item->item_no,
                    'product_type' => $item->product_type,
                    'product_id' => $item->product_id,
                    'model_name' => $item->model_name,
                    'storage' => $item->storage,
                    'color' => $item->color,
                    'size' => $item->size,
                    'imei_serial' => $item->imei_serial,
                    'warranty' => $item->warranty,
                    'is_preorder' => (bool) $item->is_preorder,
                    'description' => $item->description,
                    'qty' => (int) $item->qty,
                    'regular_price' => (float) $item->regular_price,
                    'discount_type' => $item->discount_type,
                    'discount_value' => $item->discount_value !== null ? (float) $item->discount_value : null,
                    'discount_percent_display' => $item->discount_percent_display !== null ? (float) $item->discount_percent_display : null,
                    'discounted_unit_price' => (float) $item->discounted_unit_price,
                    'line_total' => (float) $item->line_total,
                ])->values(),
            ],
            'catalogProducts' => $this->catalogProductsPayload(),
            'techProducts' => [],
            'shoeProducts' => [],
            'shop' => $this->shopInfo(),
            'nextInvoiceNo' => $invoice->invoice_no,
        ]);
    }

    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $searchValue = trim((string) $request->input('q', $request->input('search.value', '')));
        $orderStatusFilter = trim((string) $request->input('order_status_filter', ''));
        $paymentTypeFilter = trim((string) $request->input('payment_type_filter', ''));

        $baseQuery = Invoice::query();

        $recordsTotal = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function ($q) use ($searchValue) {
                $q->where('invoice_no', 'like', "%{$searchValue}%")
                    ->orWhere('customer_name', 'like', "%{$searchValue}%")
                    ->orWhere('customer_contact_number', 'like', "%{$searchValue}%");
            });
        }

        if (in_array($orderStatusFilter, self::ORDER_STATUSES, true)) {
            $baseQuery->where('order_status', $orderStatusFilter);
        }

        if (in_array($paymentTypeFilter, self::PAYMENT_TYPES, true)) {
            $baseQuery->where('payment_type', $paymentTypeFilter);
        }

        $recordsFiltered = (clone $baseQuery)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $columns = [
            0 => 'id',
            1 => 'invoice_no',
            2 => 'invoice_date',
            3 => 'customer_name',
            4 => 'customer_contact_number',
            5 => 'sales_person',
            6 => 'order_status',
            7 => 'status',
            8 => 'payment_type',
            9 => 'grand_total',
            10 => 'paid_amount',
            11 => 'balance_due',
        ];

        $orderBy = $columns[$orderColIndex] ?? 'id';

        $rows = $baseQuery
            ->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $rows->map(function (Invoice $invoice) {
            return [
                'id' => $invoice->id,
                'invoice_no' => e($invoice->invoice_no),
                'invoice_date' => optional($invoice->invoice_date)->format('Y-m-d'),
                'customer_name' => e($invoice->customer_name),
                'customer_contact_number' => e($invoice->customer_contact_number),
                'sales_person' => e($invoice->sales_person ?? '-'),
                'order_status_dropdown' => $this->renderOrderStatusDropdown($invoice),
                'status_badge' => $this->invoiceStatusBadge($invoice->status),
                'payment_type_badge' => $this->paymentTypeBadge($invoice->payment_type),
                'grand_total' => number_format((float) $invoice->grand_total, 2),
                'paid_amount' => number_format((float) $invoice->paid_amount, 2),
                'balance_due' => number_format((float) $invoice->balance_due, 2),
                'actions_dropdown' => $this->renderActionsDropdown($invoice),
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);
        $submitAction = $request->input('submit_action', 'draft');

        $result = DB::transaction(function () use ($validated, $submitAction) {
            $itemsPayload = $this->normalizeItems($validated['items'] ?? []);

            $cashPaid = round((float) ($validated['cash_paid'] ?? 0), 2);
            $cardPaid = round((float) ($validated['card_paid'] ?? 0), 2);
            $advanceAmount = round((float) ($validated['advance_amount'] ?? 0), 2);
            $taxAmount = round((float) ($validated['tax_amount'] ?? 0), 2);

            $deliveryEnabled = (bool) ($validated['delivery_enabled'] ?? false);
            $deliveryAmount = $deliveryEnabled
                ? round((float) ($validated['delivery_amount'] ?? 0), 2)
                : 0;

            $totals = $this->calculateTotals(
                $itemsPayload,
                $cashPaid,
                $cardPaid,
                $advanceAmount,
                $taxAmount,
                $deliveryAmount
            );

            $invoiceNo = $this->nextInvoiceNumberForUpdate();
            $orderStatus = $validated['order_status'] ?? 'reserved';

            $payload = [
                'invoice_no' => $invoiceNo,
                'invoice_date' => $validated['invoice_date'],
                'customer_name' => $validated['customer_name'],
                'customer_contact_number' => $validated['customer_contact_number'],
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'sales_person' => $validated['sales_person'] ?? null,
                'ship_date' => $validated['ship_date'] ?? null,
                'ship_via' => $validated['ship_via'] ?? null,
                'delivery_enabled' => $deliveryEnabled,
                'delivery_method' => $deliveryEnabled ? ($validated['delivery_method'] ?? null) : null,
                'delivery_payment_status' => $deliveryEnabled ? ($validated['delivery_payment_status'] ?? null) : null,
                'tracking_id' => $deliveryEnabled ? ($validated['tracking_id'] ?? null) : null,
                'delivery_agent' => $deliveryEnabled ? ($validated['delivery_agent'] ?? null) : null,
                'delivery_amount' => $deliveryEnabled ? $totals['delivery_amount'] : null,
                'payment_type' => $this->detectPaymentType($cashPaid, $cardPaid, $advanceAmount),
                'cash_paid' => $totals['cash_paid'],
                'card_paid' => $totals['card_paid'],
                'advance_amount' => $totals['advance_amount'],
                'paid_amount' => $totals['paid_amount'],
                'subtotal' => $totals['subtotal'],
                'total_discount' => $totals['total_discount'],
                'tax_amount' => $totals['tax_amount'],
                'grand_total' => $totals['grand_total'],
                'balance_due' => $totals['balance_due'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'status' => $submitAction === 'finalize' ? 'finalized' : ($validated['status'] ?? 'draft'),
                'order_status' => $orderStatus,
            ];

            $payload = $this->applyOrderStatusMutations($payload, $orderStatus);
            $this->guardOrderStatusRequirements($payload);

            $invoice = Invoice::create($payload);

            foreach ($itemsPayload as $index => $item) {
                $invoice->items()->create([
                    ...$item,
                    'item_no' => $index + 1,
                ]);
            }

            $needsPdf = $submitAction === 'finalize' || $this->shouldSendOrderStatusEmail(null, $invoice->order_status);

            if ($needsPdf) {
                $path = $this->regeneratePdf($invoice->fresh('items'));
                $invoice->update(['pdf_path' => $path]);
            }

            $this->syncLinkedOrder($invoice);

            return [
                'invoice' => $invoice->fresh('items'),
                'should_send_status_mail' => $this->shouldSendOrderStatusEmail(null, $invoice->order_status),
            ];
        });

        if ($result['should_send_status_mail']) {
            $this->sendOrderStatusEmail($result['invoice']);
        }

        return redirect()
            ->route('invoices.edit', $result['invoice'])
            ->with('success', $submitAction === 'finalize'
                ? 'Invoice created and PDF generated.'
                : 'Invoice saved as draft.');
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $this->validateInvoice($request, $invoice->id);
        $submitAction = $request->input('submit_action', 'draft');

        $result = DB::transaction(function () use ($validated, $invoice, $submitAction) {
            $itemsPayload = $this->normalizeItems($validated['items'] ?? []);
            $previousOrderStatus = $invoice->order_status ?? 'reserved';

            $cashPaid = round((float) ($validated['cash_paid'] ?? 0), 2);
            $cardPaid = round((float) ($validated['card_paid'] ?? 0), 2);
            $advanceAmount = round((float) ($validated['advance_amount'] ?? 0), 2);
            $taxAmount = round((float) ($validated['tax_amount'] ?? 0), 2);

            $deliveryEnabled = (bool) ($validated['delivery_enabled'] ?? false);
            $deliveryAmount = $deliveryEnabled
                ? round((float) ($validated['delivery_amount'] ?? 0), 2)
                : 0;

            $totals = $this->calculateTotals(
                $itemsPayload,
                $cashPaid,
                $cardPaid,
                $advanceAmount,
                $taxAmount,
                $deliveryAmount
            );

            $orderStatus = $validated['order_status'] ?? $previousOrderStatus;

            $payload = [
                'invoice_date' => $validated['invoice_date'],
                'customer_name' => $validated['customer_name'],
                'customer_contact_number' => $validated['customer_contact_number'],
                'customer_address' => $validated['customer_address'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'sales_person' => $validated['sales_person'] ?? null,
                'ship_date' => $validated['ship_date'] ?? null,
                'ship_via' => $validated['ship_via'] ?? null,
                'delivery_enabled' => $deliveryEnabled,
                'delivery_method' => $deliveryEnabled ? ($validated['delivery_method'] ?? null) : null,
                'delivery_payment_status' => $deliveryEnabled ? ($validated['delivery_payment_status'] ?? null) : null,
                'tracking_id' => $deliveryEnabled ? ($validated['tracking_id'] ?? null) : null,
                'delivery_agent' => $deliveryEnabled ? ($validated['delivery_agent'] ?? null) : null,
                'delivery_amount' => $deliveryEnabled ? $totals['delivery_amount'] : null,
                'payment_type' => $this->detectPaymentType($cashPaid, $cardPaid, $advanceAmount),
                'cash_paid' => $totals['cash_paid'],
                'card_paid' => $totals['card_paid'],
                'advance_amount' => $totals['advance_amount'],
                'paid_amount' => $totals['paid_amount'],
                'subtotal' => $totals['subtotal'],
                'total_discount' => $totals['total_discount'],
                'tax_amount' => $totals['tax_amount'],
                'grand_total' => $totals['grand_total'],
                'balance_due' => $totals['balance_due'],
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'status' => $submitAction === 'finalize'
                    ? 'finalized'
                    : ($validated['status'] ?? $invoice->status ?? 'draft'),
                'order_status' => $orderStatus,
            ];

            $payload = $this->applyOrderStatusMutations($payload, $orderStatus);
            $this->guardOrderStatusRequirements($payload);

            $invoice->update($payload);
            $invoice->items()->delete();

            foreach ($itemsPayload as $index => $item) {
                $invoice->items()->create([
                    ...$item,
                    'item_no' => $index + 1,
                ]);
            }

            $needsPdf = $submitAction === 'finalize'
                || $this->shouldSendOrderStatusEmail($previousOrderStatus, $orderStatus)
                || in_array($orderStatus, ['dispatched', 'delivered', 'cancelled'], true);

            if ($needsPdf) {
                $path = $this->regeneratePdf($invoice->fresh('items'));
                $invoice->update(['pdf_path' => $path]);
            }

            $this->syncLinkedOrder($invoice);

            return [
                'invoice' => $invoice->fresh('items'),
                'previous_order_status' => $previousOrderStatus,
                'should_send_status_mail' => $this->shouldSendOrderStatusEmail($previousOrderStatus, $orderStatus),
            ];
        });

        if ($result['should_send_status_mail']) {
            $this->sendOrderStatusEmail($result['invoice']);
        }

        return redirect()
            ->route('invoices.edit', $result['invoice'])
            ->with('success', $submitAction === 'finalize'
                ? 'Invoice updated and PDF regenerated.'
                : 'Invoice updated.');
    }

    public function updateorderstatus(Request $request)
    {

        Log::info($request->all());

        $invoice = Invoice::findOrFail($request->invoice_id);

        //  Log::info($invoice);
        $validated = $request->validate([
            'order_status' => ['required', Rule::in(self::ORDER_STATUSES)],
        ]);

        $result = DB::transaction(function () use ($invoice, $validated) {
            $previousOrderStatus = $invoice->order_status ?? 'reserved';
            $newOrderStatus = $validated['order_status'];

            $payload = [
                'status' => $invoice->status,
                'order_status' => $newOrderStatus,
                'delivery_enabled' => (bool) $invoice->delivery_enabled,
                'delivery_payment_status' => $invoice->delivery_payment_status,
                'tracking_id' => $invoice->tracking_id,
                'delivery_agent' => $invoice->delivery_agent,
                'grand_total' => (float) $invoice->grand_total,
                'payment_type' => $invoice->payment_type,
                'cash_paid' => (float) $invoice->cash_paid,
                'card_paid' => (float) $invoice->card_paid,
                'advance_amount' => (float) $invoice->advance_amount,
                'paid_amount' => (float) $invoice->paid_amount,
                'balance_due' => (float) $invoice->balance_due,
            ];

            $payload = $this->applyOrderStatusMutations($payload, $newOrderStatus);
            $this->guardOrderStatusRequirements($payload);

            $invoice->update([
                'status' => $payload['status'],
                'order_status' => $payload['order_status'],
                'delivery_payment_status' => $payload['delivery_payment_status'] ?? $invoice->delivery_payment_status,
                'payment_type' => $payload['payment_type'],
                'cash_paid' => $payload['cash_paid'],
                'card_paid' => $payload['card_paid'],
                'advance_amount' => $payload['advance_amount'],
                'paid_amount' => $payload['paid_amount'],
                'balance_due' => $payload['balance_due'],
            ]);

            try {
                $path = $this->regeneratePdf($invoice->fresh('items'));
                $invoice->update(['pdf_path' => $path]);
            } catch (\Throwable $throwable) {
                report($throwable);
            }

            $this->syncLinkedOrder($invoice);

            return [
                'invoice' => $invoice->fresh('items'),
                'should_send_status_mail' => $this->shouldSendOrderStatusEmail($previousOrderStatus, $newOrderStatus),
            ];
        });

        if ($result['should_send_status_mail']) {
            $this->sendOrderStatusEmail($result['invoice']);
        }

        return response()->json([
            'message' => 'Order status updated successfully.',
            'invoice_id' => $result['invoice']->id,
            'order_status' => $result['invoice']->order_status,
            'invoice_status' => $result['invoice']->status,
            'payment_type' => $result['invoice']->payment_type,
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
            Storage::disk('public')->delete($invoice->pdf_path);
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('items');

        $path = $this->regeneratePdf($invoice);
        $invoice->update(['pdf_path' => $path]);

        return response()
            ->file(storage_path('app/public/' . $path))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function download(Invoice $invoice)
    {
        $invoice->load('items');

        $path = $this->regeneratePdf($invoice);
        $invoice->update(['pdf_path' => $path]);

        return response()->download(
            storage_path('app/public/' . $path),
            $invoice->invoice_no . '.pdf'
        );
    }

    protected function validateInvoice(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'invoice_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_contact_number' => ['required', 'string', 'max:255'],
            'customer_address' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'sales_person' => ['nullable', 'string', 'max:255'],
            'ship_date' => ['nullable', 'date'],
            'ship_via' => ['nullable', 'string', 'max:255'],

            'delivery_enabled' => ['nullable', 'boolean'],
            'delivery_method' => ['nullable', 'in:cash_on_delivery,paid_delivery,pickme_flash,uber_flash'],
            'delivery_payment_status' => ['nullable', 'in:paid,non_paid'],
            'tracking_id' => ['nullable', 'string', 'max:255'],
            'delivery_agent' => ['nullable', 'in:domex,pickme'],
            'delivery_amount' => ['nullable', 'numeric', 'min:0'],

            'cash_paid' => ['nullable', 'numeric', 'min:0'],
            'card_paid' => ['nullable', 'numeric', 'min:0'],
            'advance_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'terms' => ['nullable', 'string'],
            'status' => ['nullable', 'in:draft,finalized,cancelled'],
            'order_status' => ['required', Rule::in(self::ORDER_STATUSES)],
            'submit_action' => ['nullable', 'in:draft,finalize'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_type' => ['required', Rule::in(self::PRODUCT_TYPES)],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.model_name' => ['nullable', 'string', 'max:255'],
            'items.*.storage' => ['nullable', 'string', 'max:255'],
            'items.*.color' => ['nullable', 'string', 'max:255'],
            'items.*.size' => ['nullable', 'string', 'max:255'],
            'items.*.imei_serial' => ['nullable', 'string', 'max:255'],
            'items.*.warranty' => ['nullable', 'string', 'max:255'],
            'items.*.is_preorder' => ['nullable', 'boolean'],
            'items.*.description' => ['required', 'string'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.regular_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_type' => ['nullable', 'in:percentage,fixed'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_percent_display' => ['nullable', 'numeric', 'min:0'],
            'items.*.discounted_unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.line_total' => ['required', 'numeric', 'min:0'],
        ]);
    }

    protected function normalizeProductType(?string $value): string
    {
        $type = str_replace('_', '-', mb_strtolower(trim((string) $value)));

        return match ($type) {
            'tech', 'electronic', 'electronics' => 'electronics',
            'cosmetic', 'cosmetics' => 'cosmetics',
            'motorcycle', 'motorcycles', 'motorcycle-products' => 'motorcycle',
            'fashion', 'fashion-accessories' => 'fashion',
            'home-need', 'home-needs' => 'home-needs',
            default => 'electronics',
        };
    }

    protected function normalizeItems(array $items): array
    {
        return collect($items)
            ->values()
            ->map(function ($item) {
                return [
                    'product_type' => $this->normalizeProductType($item['product_type'] ?? null),
                    'product_id' => !empty($item['product_id']) ? (int) $item['product_id'] : null,
                    'model_name' => $item['model_name'] ?? null,
                    'storage' => $item['storage'] ?? null,
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'imei_serial' => $item['imei_serial'] ?? null,
                    'warranty' => $item['warranty'] ?? null,
                    'is_preorder' => (bool) ($item['is_preorder'] ?? false),
                    'description' => $item['description'],
                    'qty' => max(1, (int) ($item['qty'] ?? 1)),
                    'regular_price' => round((float) ($item['regular_price'] ?? 0), 2),
                    'discount_type' => $item['discount_type'] ?? null,
                    'discount_value' => isset($item['discount_value']) ? round((float) $item['discount_value'], 2) : null,
                    'discount_percent_display' => isset($item['discount_percent_display']) ? round((float) $item['discount_percent_display'], 2) : null,
                    'discounted_unit_price' => round((float) ($item['discounted_unit_price'] ?? 0), 2),
                    'line_total' => round((float) ($item['line_total'] ?? 0), 2),
                ];
            })
            ->all();
    }

    protected function calculateTotals(
        array $items,
        float $cashPaid = 0,
        float $cardPaid = 0,
        float $advanceAmount = 0,
        float $taxAmount = 0,
        float $deliveryAmount = 0
    ): array {
        $subtotal = 0;
        $grandTotalBeforeTax = 0;

        foreach ($items as $item) {
            $regular = (float) $item['regular_price'];
            $qty = max(1, (int) $item['qty']);
            $lineTotal = (float) $item['line_total'];

            $subtotal += ($regular * $qty);
            $grandTotalBeforeTax += $lineTotal;
        }

        $deliveryAmount = round(max(0, $deliveryAmount), 2);

        $subtotal = round($subtotal + $deliveryAmount, 2);
        $grandTotalBeforeTax = round($grandTotalBeforeTax + $deliveryAmount, 2);
        $taxAmount = round($taxAmount, 2);
        $cashPaid = round(max(0, $cashPaid), 2);
        $cardPaid = round(max(0, $cardPaid), 2);
        $advanceAmount = round(max(0, $advanceAmount), 2);

        $grandTotal = round($grandTotalBeforeTax + $taxAmount, 2);
        $totalDiscount = round($subtotal - $grandTotalBeforeTax, 2);
        $paidAmount = round($cashPaid + $cardPaid + $advanceAmount, 2);
        $balanceDue = round(max(0, $grandTotal - $paidAmount), 2);

        return [
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'cash_paid' => $cashPaid,
            'card_paid' => $cardPaid,
            'advance_amount' => $advanceAmount,
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'delivery_amount' => $deliveryAmount,
        ];
    }

    protected function detectPaymentType(float $cashPaid, float $cardPaid, float $advanceAmount): string
    {
        $hasCash = $cashPaid > 0;
        $hasCard = $cardPaid > 0;
        $hasAdvance = $advanceAmount > 0;

        $count = collect([$hasCash, $hasCard, $hasAdvance])->filter()->count();

        if ($count === 0) {
            return 'unpaid';
        }

        if ($count > 1) {
            return 'mixed';
        }

        if ($hasCash) {
            return 'cash';
        }

        if ($hasCard) {
            return 'card';
        }

        return 'advance';
    }

    protected function nextInvoiceNumber(): string
    {
        $nextSequence = $this->maxInvoiceSequence() + 1;

        return $this->formatInvoiceNumber($nextSequence);
    }

    protected function nextInvoiceNumberForUpdate(): string
    {
        Invoice::query()
            ->select('id')
            ->lockForUpdate()
            ->latest('id')
            ->first();

        Order::query()
            ->select('id')
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $nextSequence = $this->maxInvoiceSequence() + 1;

        return $this->formatInvoiceNumber($nextSequence);
    }

    protected function maxInvoiceSequence(): int
    {
        $invoiceMax = (int) (Invoice::query()
            ->where('invoice_no', 'like', 'INV-%')
            ->selectRaw('MAX(SUBSTR(invoice_no, 5) + 0) as max_seq')
            ->value('max_seq') ?? 0);

        $orderMax = (int) (Order::query()
            ->where('order_number', 'like', 'INV-%')
            ->selectRaw('MAX(SUBSTR(order_number, 5) + 0) as max_seq')
            ->value('max_seq') ?? 0);

        return max($invoiceMax, $orderMax);
    }

    protected function formatInvoiceNumber(int $sequence): string
    {
        $sequence = max(1, $sequence);

        return 'INV-' . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    protected function generateAndStorePdf(Invoice $invoice): string
    {
        $invoice->loadMissing('items');

        $shop = $this->shopInfo();

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'shop' => $shop,
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'dpi' => 120,
            ]);

        $path = 'invoices/' . $invoice->invoice_no . '-' . now()->format('YmdHis') . '.pdf';

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    protected function shopInfo(): array
    {
        $logoPath = public_path('images/dezestoreblack.png');
        $logoBase64 = null;

        if (is_file($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        return [
            'name' => 'DezeStore',
            'address_lines' => [
                'No.14/S Waragashinna,',
                'Akurana 20850',
            ],
            'phone' => '077 203 0597',
            'website' => 'www.dezestore.com',
            'logo_url' => asset('images/dezestoreblack.png'),
            'logo_path' => $logoPath,
            'logo_base64' => $logoBase64,
        ];
    }

    protected function catalogProductsPayload()
    {
        $storageMap = StorageOption::query()->get()->keyBy('id');
        $colorMap = ColorOption::query()->get()->keyBy('id');
        $warrantyMap = WarrantyOption::query()->get()->keyBy('id');
        $cosmeticSizeMap = CosmeticSizeVolume::query()->get()->keyBy('id');

        $electronics = Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->where('status', 'active')
            ->orderBy('model')
            ->get()
            ->map(function (Product $product) use ($storageMap, $colorMap, $warrantyMap) {
                $storageOptionIds = collect($product->storage_option_ids ?? [])
                    ->map(fn($id) => (int) $id)
                    ->values();

                $colorIds = collect($product->color_ids ?? [])
                    ->map(fn($id) => (int) $id)
                    ->values();

                return [
                    'id' => $product->id,
                    'product_type' => 'electronics',
                    'label' => $product->model,
                    'name' => $product->model,
                    'sku' => $product->sku,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'price' => (float) $product->price_lkr,
                    'warranty' => $product->warranty_period
                        ?: ($product->warranty_option_id ? optional($warrantyMap->get($product->warranty_option_id))->name : null),
                    'storages' => $storageOptionIds->map(function ($id) use ($storageMap) {
                        $option = $storageMap->get($id);

                        return $option
                            ? trim($option->value . ' ' . $option->unit)
                            : null;
                    })->filter()->values(),
                    'colors' => $colorIds->map(function ($id) use ($colorMap) {
                        return optional($colorMap->get($id))->name;
                    })->filter()->values(),
                    'sizes' => [],
                ];
            });

        $motorcycles = MotorcycleProduct::query()
            ->with(['category:id,name', 'helmetBrand:id,name', 'warrantyOption:id,name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (MotorcycleProduct $product) => [
                'id' => $product->id,
                'product_type' => 'motorcycle',
                'label' => $product->name,
                'name' => $product->name,
                'sku' => $product->sku,
                'brand' => $product->helmetBrand?->name,
                'category' => $product->category?->name,
                'price' => (float) ($product->sale_price ?: $product->regular_price ?: 0),
                'warranty' => $product->warranty_period ?: $product->warranty ?: $product->warrantyOption?->name,
                'storages' => [],
                'colors' => [],
                'sizes' => [],
            ]);

        $cosmetics = CosmeticProduct::query()
            ->with(['brand:id,name', 'category:id,name', 'productType:id,name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function (CosmeticProduct $product) use ($cosmeticSizeMap) {
                return [
                    'id' => $product->id,
                    'product_type' => 'cosmetics',
                    'label' => $product->name,
                    'name' => $product->name,
                    'sku' => $product->batch_number,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name ?: $product->productType?->name,
                    'price' => $this->discountedCatalogPrice((float) ($product->price ?: 0), $product->discount_type, $product->discount_value),
                    'warranty' => null,
                    'storages' => [],
                    'colors' => [],
                    'sizes' => collect($product->size_volume_ids ?? [])
                        ->map(fn ($id) => optional($cosmeticSizeMap->get((int) $id))->display)
                        ->filter()
                        ->values(),
                ];
            });

        $fashion = FashionProduct::query()
            ->with(['brand:id,name', 'category:id,name', 'productType:id,name', 'warrantyOption:id,name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (FashionProduct $product) => [
                'id' => $product->id,
                'product_type' => 'fashion',
                'label' => $product->name,
                'name' => $product->name,
                'sku' => $product->sku,
                'brand' => $product->brand?->name ?: $product->brand_name,
                'category' => $product->category?->name ?: $product->productType?->name,
                'price' => (float) ($product->sale_price ?: $product->price ?: 0),
                'warranty' => $product->warranty_period ?: $product->warrantyOption?->name,
                'storages' => [],
                'colors' => collect([$product->color])->filter()->values(),
                'sizes' => collect([$product->size_label])->filter()->values(),
            ]);

        $homeNeeds = HomeNeedProduct::query()
            ->with(['brand:id,name', 'category:id,name', 'warrantyOption:id,name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (HomeNeedProduct $product) => [
                'id' => $product->id,
                'product_type' => 'home-needs',
                'label' => $product->name,
                'name' => $product->name,
                'sku' => $product->sku,
                'brand' => $product->brand?->name ?: $product->brand_name,
                'category' => $product->category?->name,
                'price' => (float) ($product->sale_price ?: $product->price ?: 0),
                'warranty' => $product->warranty_period ?: $product->warrantyOption?->name,
                'storages' => collect([$product->unit_label])->filter()->values(),
                'colors' => collect([$product->color])->filter()->values(),
                'sizes' => [],
            ]);

        return $electronics
            ->concat($motorcycles)
            ->concat($cosmetics)
            ->concat($fashion)
            ->concat($homeNeeds)
            ->sortBy(fn (array $product) => $product['label'])
            ->values();
    }

    protected function discountedCatalogPrice(float $price, ?string $discountType, $discountValue): float
    {
        $discountValue = is_numeric($discountValue) ? (float) $discountValue : 0.0;

        if ($discountValue <= 0) {
            return round(max(0, $price), 2);
        }

        return match ($discountType) {
            'percent', 'percentage' => round(max(0, $price - (($price * $discountValue) / 100)), 2),
            'price', 'fixed' => round(max(0, $price - $discountValue), 2),
            default => round(max(0, $price), 2),
        };
    }
    protected function techProductsPayload()
    {
        $storageMap = StorageOption::query()->get()->keyBy('id');
        $colorMap = ColorOption::query()->get()->keyBy('id');
        $warrantyMap = WarrantyOption::query()->get()->keyBy('id');

        return Product::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->orderBy('model')
            ->get()
            ->map(function (Product $product) use ($storageMap, $colorMap, $warrantyMap) {
                $storageOptionIds = collect($product->storage_option_ids ?? [])
                    ->map(fn($id) => (int) $id)
                    ->values();

                $colorIds = collect($product->color_ids ?? [])
                    ->map(fn($id) => (int) $id)
                    ->values();

                return [
                    'id' => $product->id,
                    'label' => $product->model,
                    'model' => $product->model,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'price' => (float) $product->price_lkr,
                    'warranty' => $product->warranty_period
                        ?: ($product->warranty_option_id ? optional($warrantyMap->get($product->warranty_option_id))->name : null),
                    'storages' => $storageOptionIds->map(function ($id) use ($storageMap) {
                        $option = $storageMap->get($id);

                        return $option
                            ? trim($option->value . ' ' . $option->unit)
                            : null;
                    })->filter()->values(),
                    'colors' => $colorIds->map(function ($id) use ($colorMap) {
                        return optional($colorMap->get($id))->name;
                    })->filter()->values(),
                ];
            })
            ->values();
    }

    protected function shoeProductsPayload()
    {
        return ShoeProduct::query()
            ->with(['brand:id,name', 'category:id,name'])
            ->orderBy('name')
            ->get()
            ->map(function (ShoeProduct $product) {
                $sizes = collect($product->sizes_by_type ?? [])
                    ->flatMap(function ($entry) {
                        return collect($entry['sizes'] ?? []);
                    })
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'id' => $product->id,
                    'label' => $product->name,
                    'name' => $product->name,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'price' => (float) ($product->sale_price ?: $product->regular_price ?: 0),
                    'regular_price' => (float) ($product->regular_price ?: 0),
                    'sizes' => $sizes,
                ];
            })
            ->values();
    }

    protected function guardOrderStatusRequirements(array $payload): void
    {
        if (($payload['order_status'] ?? 'reserved') === 'dispatched') {
            if (blank($payload['tracking_id'] ?? null)) {
                throw ValidationException::withMessages([
                    'tracking_id' => 'Tracking ID is required before setting the order as dispatched.',
                ]);
            }

            if (blank($payload['delivery_agent'] ?? null)) {
                throw ValidationException::withMessages([
                    'delivery_agent' => 'Delivery agent is required before setting the order as dispatched.',
                ]);
            }
        }
    }

    protected function applyOrderStatusMutations(array $payload, string $orderStatus): array
    {
        $payload['order_status'] = $orderStatus;

        if ($orderStatus === 'delivered') {
            $grandTotal = round((float) ($payload['grand_total'] ?? 0), 2);

            $payload['status'] = 'finalized';
            $payload['payment_type'] = 'cash';
            $payload['cash_paid'] = $grandTotal;
            $payload['card_paid'] = 0;
            $payload['advance_amount'] = 0;
            $payload['paid_amount'] = $grandTotal;
            $payload['balance_due'] = 0;

            if (($payload['delivery_enabled'] ?? false) === true) {
                $payload['delivery_payment_status'] = 'paid';
            }
        }

        if ($orderStatus === 'cancelled') {
            $payload['status'] = 'cancelled';
        }

        return $payload;
    }

    protected function shouldSendOrderStatusEmail(?string $previousStatus, string $newStatus): bool
    {
        if ($previousStatus === $newStatus) {
            return false;
        }

        return in_array($newStatus, ['confirmed', 'dispatched', 'delivered', 'cancelled'], true);
    }

    protected function sendOrderStatusEmail(Invoice $invoice): void
    {
        if (blank($invoice->customer_email)) {
            return;
        }

        try {
            $mail = new InvoiceOrderStatusMail($invoice, $invoice->order_status);

            if ($invoice->pdf_path && Storage::disk('public')->exists($invoice->pdf_path)) {
                $mail->attach(Storage::disk('public')->path($invoice->pdf_path), [
                    'as' => $invoice->invoice_no . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }

            Mail::mailer($this->resolveOrderStatusMailer())->to($invoice->customer_email)->send($mail);
        } catch (\Throwable $throwable) {
            report($throwable);
        }
    }

    protected function resolveOrderStatusMailer(): string
    {
        $default = (string) config('mail.default', 'log');

        if (!in_array($default, ['log', 'array'], true)) {
            return $default;
        }

        $smtpHost = config('mail.mailers.smtp.host');
        $smtpUser = config('mail.mailers.smtp.username');

        if (!empty($smtpHost) && !empty($smtpUser)) {
            Log::info('Order status mailer fallback to smtp because default mailer is non-delivery.', [
                'default_mailer' => $default,
            ]);

            return 'smtp';
        }

        Log::warning('Order status emails will not be delivered because the mailer is not configured.', [
            'default_mailer' => $default,
        ]);

        return $default;
    }

    protected function regeneratePdf(Invoice $invoice): string
    {
        $previousPath = $invoice->pdf_path;
        $newPath = $this->generateAndStorePdf($invoice);

        if (
            $previousPath
            && $previousPath !== $newPath
            && Storage::disk('public')->exists($previousPath)
        ) {
            Storage::disk('public')->delete($previousPath);
        }

        return $newPath;
    }

    protected function syncLinkedOrder(Invoice $invoice): void
    {
        Order::query()
            ->where('order_number', $invoice->invoice_no)
            ->update([
                'status' => $invoice->order_status,
            ]);
    }

    protected function invoiceStatusBadge(?string $status): string
    {
        return match ($status) {
            'finalized' => '<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Finalized</span>',
            'cancelled' => '<span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Cancelled</span>',
            default => '<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Draft</span>',
        };
    }

    protected function paymentTypeBadge(?string $paymentType): string
    {
        $label = ucfirst(str_replace('_', ' ', (string) $paymentType));

        return match ($paymentType) {
            'cash' => '<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">' . e($label) . '</span>',
            'card' => '<span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">' . e($label) . '</span>',
            'mixed' => '<span class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700">' . e($label) . '</span>',
            'advance' => '<span class="inline-flex items-center rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700">' . e($label) . '</span>',
            default => '<span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">Unpaid</span>',
        };
    }

    protected function renderOrderStatusDropdown(Invoice $invoice): string
    {
        $buttonClasses = $this->orderStatusButtonClasses($invoice->order_status ?? 'reserved');
        $label = ucfirst(str_replace('_', ' ', $invoice->order_status ?? 'reserved'));

        $items = collect(self::ORDER_STATUSES)
            ->map(function (string $status) use ($invoice) {
                $active = ($invoice->order_status ?? 'reserved') === $status;
                $icon = $active ? '<span class="text-emerald-600">●</span>' : '<span class="text-slate-300">●</span>';

                return '
                    <button
                        type="button"
                        data-action="change-order-status"
                        data-id="' . $invoice->id . '"
                        data-status="' . e($status) . '"
                        class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50"
                    >
                        <span>' . e(ucfirst($status)) . '</span>
                        ' . $icon . '
                    </button>
                ';
            })
            ->implode('');

        return '
            <div class="table-dropdown relative inline-block text-left">
                <button
                    type="button"
                    data-action="toggle-dropdown"
                    class="inline-flex min-w-[148px] items-center justify-between gap-2 rounded-full border px-3 py-2 text-xs font-semibold shadow-sm transition hover:-translate-y-[1px] ' . $buttonClasses . '"
                >
                    <span>' . e($label) . '</span>
                    <svg class="h-4 w-4 text-current" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.173l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                </button>
                <div class="table-dropdown-menu invisible absolute right-0 z-20 mt-2 w-56 origin-top-right rounded-2xl border border-slate-200 bg-white p-2 opacity-0 shadow-xl transition-all duration-200 ease-out">
                    ' . $items . '
                </div>
            </div>
        ';
    }

    protected function renderActionsDropdown(Invoice $invoice): string
    {
        return '
            <div class="table-dropdown relative inline-block text-left">
                <button
                    type="button"
                    data-action="toggle-dropdown"
                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:-translate-y-[1px] hover:bg-slate-50"
                >
                    <span>Actions</span>
                    <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.173l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                </button>
                <div class="table-dropdown-menu invisible absolute right-0 z-20 mt-2 w-48 origin-top-right rounded-2xl border border-slate-200 bg-white p-2 opacity-0 shadow-xl transition-all duration-200 ease-out">
                    <button type="button" data-action="view" data-id="' . $invoice->id . '" class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">View PDF</button>
                    <button type="button" data-action="download" data-id="' . $invoice->id . '" class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">Download PDF</button>
                    <button type="button" data-action="edit" data-id="' . $invoice->id . '" data-name="' . e($invoice->invoice_no) . '" class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">Edit Order</button>
                    <button type="button" data-action="delete" data-id="' . $invoice->id . '" data-name="' . e($invoice->invoice_no) . '" class="flex w-full rounded-xl px-3 py-2 text-left text-sm text-rose-600 transition hover:bg-rose-50">Delete</button>
                </div>
            </div>
        ';
    }

    protected function orderStatusButtonClasses(string $status): string
    {
        return match ($status) {
            'confirmed' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'dispatched' => 'border-blue-200 bg-blue-50 text-blue-700',
            'delivered' => 'border-violet-200 bg-violet-50 text-violet-700',
            'cancelled' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-amber-200 bg-amber-50 text-amber-700',
        };
    }
}
