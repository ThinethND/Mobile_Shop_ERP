<?php

namespace Tests\Feature;

use App\Mail\InvoiceOrderStatusMail;
use App\Mail\NewOrderAdminNotificationMail;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CheckoutOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_places_order_and_sends_customer_and_admin_emails(): void
    {
        Mail::fake();
        Storage::fake('public');

        config([
            'mail.from.address' => 'info@dezestore.com',
            'mail.from.name' => 'DezeStore',
            'mail.order_notification.address' => 'info@dezestore.com',
        ]);

        $response = $this->postJson(route('frontend.checkout.store'), [
            'full_name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '771234567',
            'address_line_1' => '123 Main Street',
            'address_line_2' => 'Unit 4',
            'city' => 'Colombo',
            'postal_code' => '00100',
            'delivery_note' => 'Call before delivery.',
            'shipping_method' => [
                'code' => 'cash_on_delivery',
                'name' => 'Cash on delivery',
                'fee' => 450,
            ],
            'items' => [
                [
                    'key' => 'electronics-10-variant-1',
                    'id' => 10,
                    'productType' => 'electronics',
                    'name' => 'DezeStore Test Phone',
                    'sku' => 'DZ-TEST-01',
                    'price' => 100000,
                    'quantity' => 1,
                    'image' => '/images/dezestoreblack.png',
                    'colorName' => 'Black',
                    'storageLabel' => '256 GB',
                    'variantLabel' => 'Black / 256 GB',
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Order placed successfully.',
            ]);

        $order = Order::with('items')->firstOrFail();
        $invoice = Invoice::with('items')->firstOrFail();

        $this->assertSame($order->order_number, $invoice->invoice_no);
        $this->assertSame('confirmed', $order->status);
        $this->assertSame('confirmed', $invoice->order_status);
        $this->assertSame('customer@example.com', $invoice->customer_email);
        $this->assertCount(1, $order->items);
        $this->assertCount(1, $invoice->items);
        $this->assertNotNull($order->email_sent_at);
        $this->assertNotNull($order->admin_email_sent_at);

        Mail::assertSent(InvoiceOrderStatusMail::class, function (InvoiceOrderStatusMail $mail) use ($invoice) {
            return $mail->hasTo('customer@example.com')
                && $mail->invoice->is($invoice)
                && $mail->orderStatus === 'confirmed';
        });

        Mail::assertSent(NewOrderAdminNotificationMail::class, function (NewOrderAdminNotificationMail $mail) use ($order) {
            return $mail->hasTo('info@dezestore.com')
                && $mail->order->is($order);
        });
    }

    public function test_order_status_update_sends_customer_status_email_and_syncs_linked_order(): void
    {
        Mail::fake();
        Storage::fake('public');

        config([
            'mail.from.address' => 'info@dezestore.com',
            'mail.from.name' => 'DezeStore',
        ]);

        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $invoice = Invoice::create([
            'invoice_no' => 'INV-999',
            'invoice_date' => now()->toDateString(),
            'customer_name' => 'Test Customer',
            'customer_contact_number' => '0771234567',
            'customer_address' => '123 Main Street, Colombo',
            'customer_email' => 'customer@example.com',
            'sales_person' => 'Website',
            'ship_via' => 'Cash on delivery',
            'delivery_enabled' => true,
            'delivery_method' => 'cash_on_delivery',
            'delivery_payment_status' => 'non_paid',
            'tracking_id' => 'TRK-123',
            'delivery_agent' => 'Courier',
            'delivery_amount' => 450,
            'payment_type' => 'unpaid',
            'cash_paid' => 0,
            'card_paid' => 0,
            'advance_amount' => 0,
            'paid_amount' => 0,
            'subtotal' => 100000,
            'total_discount' => 0,
            'tax_amount' => 0,
            'grand_total' => 100450,
            'balance_due' => 100450,
            'status' => 'draft',
            'order_status' => 'confirmed',
        ]);

        $invoice->items()->create([
            'item_no' => 1,
            'product_type' => 'electronics',
            'product_id' => 10,
            'model_name' => 'DezeStore Test Phone',
            'description' => 'DezeStore Test Phone / Black / 256 GB',
            'qty' => 1,
            'regular_price' => 100000,
            'discounted_unit_price' => 100000,
            'line_total' => 100000,
        ]);

        $order = Order::create([
            'order_number' => $invoice->invoice_no,
            'full_name' => 'Test Customer',
            'email' => 'customer@example.com',
            'phone' => '0771234567',
            'address_line_1' => '123 Main Street',
            'city' => 'Colombo',
            'shipping_method_code' => 'cash_on_delivery',
            'shipping_method_name' => 'Cash on delivery',
            'shipping_fee' => 450,
            'subtotal' => 100000,
            'grand_total' => 100450,
            'currency' => 'LKR',
            'status' => 'confirmed',
        ]);

        $response = $this
            ->actingAs($user)
            ->postJson(route('invoices.order-status'), [
                'invoice_id' => $invoice->id,
                'order_status' => 'dispatched',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Order status updated successfully.',
                'order_status' => 'dispatched',
            ]);

        $invoice->refresh();
        $order->refresh();

        $this->assertSame('dispatched', $invoice->order_status);
        $this->assertSame('dispatched', $order->status);

        Mail::assertSent(InvoiceOrderStatusMail::class, function (InvoiceOrderStatusMail $mail) use ($invoice) {
            return $mail->hasTo('customer@example.com')
                && $mail->invoice->is($invoice)
                && $mail->orderStatus === 'dispatched';
        });
    }
}
