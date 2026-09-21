<?php

namespace Tests\Feature;

use App\Enums\SalesOrderStatus;
use App\Models\SalesCustomer;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private SalesCustomer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = SalesCustomer::create(['name' => 'ลูกค้าทดสอบ', 'phone' => '0812345678']);
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-09-20',
            'note' => 'ทดสอบ',
            'items' => [
                ['product_id' => null, 'product_name' => 'สินค้า A', 'unit_price' => 100.50, 'quantity' => 2],
                ['product_id' => null, 'product_name' => 'สินค้า B', 'unit_price' => 50, 'quantity' => 1],
            ],
        ], $override);
    }

    private function makeOrder(): SalesOrder
    {
        return SalesOrder::place([
            'customer_id' => $this->customer->id,
            'order_date' => '2026-09-20',
            'user_id' => $this->user->id,
        ], $this->payload()['items']);
    }

    public function test_guest_cannot_access_api(): void
    {
        $this->getJson('/api/sales/orders')->assertUnauthorized();
    }

    public function test_can_create_order_with_items_total_and_pending_status(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/sales/orders', $this->payload());

        $response->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total', 251)
            ->assertJsonCount(2, 'data.items')
            ->assertJsonCount(1, 'data.histories');

        $this->assertDatabaseCount('sales_orders', 1);
        $this->assertDatabaseCount('sales_order_items', 2);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-0001$/', $response->json('data.order_number'));
    }

    public function test_create_order_validates_input(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/sales/orders', ['items' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['customer_id', 'order_date', 'items']);
    }

    public function test_can_list_and_filter_orders(): void
    {
        $shipped = $this->makeOrder();
        $shipped->moveTo(SalesOrderStatus::Shipped);
        $this->makeOrder();

        $this->actingAs($this->user)->getJson('/api/sales/orders')
            ->assertOk()->assertJsonCount(2, 'data');

        $this->actingAs($this->user)->getJson('/api/sales/orders?status=shipped')
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', 'shipped');

        $this->actingAs($this->user)->getJson('/api/sales/orders?search=ลูกค้าทดสอบ')
            ->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_can_update_pending_order(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->user)->putJson("/api/sales/orders/{$order->id}", $this->payload([
            'items' => [['product_name' => 'สินค้า C', 'unit_price' => 10, 'quantity' => 3]],
        ]))->assertOk()
            ->assertJsonPath('data.total', 30)
            ->assertJsonCount(1, 'data.items');

        $this->assertDatabaseCount('sales_order_items', 1);
    }

    public function test_cannot_update_shipped_order(): void
    {
        $order = $this->makeOrder();
        $order->moveTo(SalesOrderStatus::Shipped);

        $this->actingAs($this->user)->putJson("/api/sales/orders/{$order->id}", $this->payload())
            ->assertUnprocessable();
    }

    public function test_status_moves_forward_only(): void
    {
        $order = $this->makeOrder();

        // ข้ามขั้นไม่ได้
        $this->actingAs($this->user)->patchJson("/api/sales/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertUnprocessable();

        $this->actingAs($this->user)->patchJson("/api/sales/orders/{$order->id}/status", ['status' => 'shipped'])
            ->assertOk()->assertJsonPath('data.status', 'shipped');

        $this->actingAs($this->user)->patchJson("/api/sales/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertOk()->assertJsonPath('data.status', 'delivered')
            ->assertJsonCount(3, 'data.histories');

        // ย้อนกลับไม่ได้
        $this->actingAs($this->user)->patchJson("/api/sales/orders/{$order->id}/status", ['status' => 'pending'])
            ->assertUnprocessable();

        $this->assertNotNull($order->fresh()->shipped_at);
        $this->assertNotNull($order->fresh()->delivered_at);
    }

    public function test_can_delete_order(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->user)->deleteJson("/api/sales/orders/{$order->id}")->assertOk();

        $this->assertDatabaseMissing('sales_orders', ['id' => $order->id]);
        $this->assertDatabaseCount('sales_order_items', 0);
    }

    public function test_summary_counts_by_status(): void
    {
        $this->makeOrder();
        $delivered = $this->makeOrder();
        $delivered->moveTo(SalesOrderStatus::Shipped);
        $delivered->moveTo(SalesOrderStatus::Delivered);

        $this->actingAs($this->user)->getJson('/api/sales/summary')
            ->assertOk()
            ->assertJson(['pending' => 1, 'shipped' => 0, 'delivered' => 1, 'total' => 2, 'revenue' => 251]);
    }

    public function test_receipt_pdf_is_generated(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->user)->get("/sales/orders/{$order->id}/receipt");

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}