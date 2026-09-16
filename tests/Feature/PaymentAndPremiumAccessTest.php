<?php

namespace Tests\Feature;

use App\Models\Cv;
use App\Models\CvPersonalInfo;
use App\Models\CvTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\TemplateCategory;
use App\Models\User;
use App\Models\UserEntitlement;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentAndPremiumAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected CvTemplate $freeTemplate;
    protected CvTemplate $premiumTemplate;
    protected Product $product;
    protected TemplateCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'user']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $this->category = TemplateCategory::create([
            'name' => 'Executive',
            'slug' => 'executive',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->freeTemplate = CvTemplate::create([
            'category_id' => $this->category->id,
            'name' => 'Classic Standard',
            'slug' => 'classic-standard',
            'key' => 'classic-executive',
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->premiumTemplate = CvTemplate::create([
            'category_id' => $this->category->id,
            'name' => 'Executive Gold Pro',
            'slug' => 'executive-gold-pro',
            'key' => 'modern-clean',
            'is_premium' => true,
            'price' => 12.99,
            'currency' => 'USD',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Also create a linked Product record for the premium template
        $this->product = Product::create([
            'cv_template_id' => $this->premiumTemplate->id,
            'name' => 'Executive Gold Pro Template',
            'slug' => 'executive-gold-pro-template',
            'description' => 'Lifetime license for Executive Gold Pro template',
            'price' => 12.99,
            'currency' => 'USD',
            'is_active' => true,
        ]);
    }

    public function test_free_template_pdf_download_allowed_without_payment(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->freeTemplate->id,
            'title' => 'My Free Resume',
            'template_key' => $this->freeTemplate->key,
            'status' => 'draft',
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv->id,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($this->user)->get(route('cvs.pdf', $cv));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_premium_template_pdf_download_redirects_to_checkout_when_unauthorized(): void
    {
        $cv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->premiumTemplate->id,
            'title' => 'My Premium Resume',
            'template_key' => $this->premiumTemplate->key,
            'status' => 'draft',
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv->id,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($this->user)->get(route('cvs.pdf', $cv));

        $response->assertRedirect(route('checkout.template', [
            'template' => $this->premiumTemplate->id,
            'return_url' => route('cvs.pdf', $cv),
        ]));

        $response->assertSessionHas('warning');
    }

    public function test_admin_can_download_premium_template_pdf_without_payment(): void
    {
        $cv = Cv::create([
            'user_id' => $this->admin->id,
            'template_id' => $this->premiumTemplate->id,
            'title' => 'Admin Premium Resume',
            'template_key' => $this->premiumTemplate->key,
            'status' => 'draft',
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv->id,
            'full_name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $response = $this->actingAs($this->admin)->get(route('cvs.pdf', $cv));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_checkout_screen_displays_authoritative_price(): void
    {
        $response = $this->actingAs($this->user)->get(route('checkout.template', $this->premiumTemplate));

        $response->assertStatus(200);
        $response->assertSee('Executive Gold Pro');
        $response->assertSee('$12.99');
        $response->assertSee('Unlock Premium Design');
    }

    public function test_checkout_process_with_mock_gateway_fulfills_order_and_entitlement(): void
    {
        $response = $this->actingAs($this->user)->post(route('checkout.process'), [
            'template_id' => $this->premiumTemplate->id,
            'payment_provider' => 'mock',
        ]);

        $order = Order::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(12.99, (float) $order->total_amount);
        $this->assertEquals('USD', $order->currency);
        $this->assertEquals('mock', $order->payment_provider);

        // Follow redirect to callback to complete simulated payment
        $callbackResponse = $this->actingAs($this->user)->get(route('checkout.callback', [
            'order' => $order->id,
            'mock_action' => 'success',
        ]));

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertTrue($this->user->fresh()->hasAccessToTemplate($this->premiumTemplate));

        // Check redirect to success page
        $callbackResponse->assertRedirect(route('checkout.success', $order));
    }

    public function test_checkout_process_with_mock_gateway_cancellation_marks_order_cancelled(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-CANCEL-111',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_provider' => 'mock',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'item_name' => "Premium Template: {$this->premiumTemplate->name}",
            'price' => 12.99,
            'quantity' => 1,
            'subtotal' => 12.99,
        ]);

        $response = $this->actingAs($this->user)->get(route('checkout.callback', [
            'order' => $order->id,
            'mock_action' => 'cancel',
        ]));

        $this->assertEquals('cancelled', $order->fresh()->status);
        $response->assertRedirect(route('checkout.failed', ['order' => $order->id, 'reason' => 'Sandbox checkout was cancelled by user.']));
    }

    public function test_unlocked_premium_template_allows_pdf_download(): void
    {
        // Grant entitlement to user
        UserEntitlement::create([
            'user_id' => $this->user->id,
            'cv_template_id' => $this->premiumTemplate->id,
            'entitlement_type' => 'template_unlock',
            'status' => 'active',
        ]);

        $cv = Cv::create([
            'user_id' => $this->user->id,
            'template_id' => $this->premiumTemplate->id,
            'title' => 'My Unlocked Premium Resume',
            'template_key' => $this->premiumTemplate->key,
            'status' => 'draft',
        ]);

        CvPersonalInfo::create([
            'cv_id' => $cv->id,
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response = $this->actingAs($this->user)->get(route('cvs.pdf', $cv));
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_purchased_template_cannot_be_re_purchased_redirects_gracefully(): void
    {
        // Grant entitlement
        UserEntitlement::create([
            'user_id' => $this->user->id,
            'cv_template_id' => $this->premiumTemplate->id,
            'entitlement_type' => 'template_unlock',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('checkout.template', $this->premiumTemplate));

        $response->assertRedirect(route('billing.index'));
        $response->assertSessionHas('info');
    }

    public function test_payment_fulfillment_is_idempotent(): void
    {
        $paymentService = app(PaymentService::class);

        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-TEST-12345',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_provider' => 'mock',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'item_name' => "Premium Template: {$this->premiumTemplate->name}",
            'price' => 12.99,
            'quantity' => 1,
            'subtotal' => 12.99,
        ]);

        // First fulfillment
        $result1 = $paymentService->verifyAndFulfill($order, ['mock_action' => 'success']);
        $this->assertTrue($result1->isSuccessful());
        $this->assertEquals(1, UserEntitlement::where('user_id', $this->user->id)->where('cv_template_id', $this->premiumTemplate->id)->count());

        // Duplicate/Repeated fulfillment attempt (e.g. repeated webhook)
        $result2 = $paymentService->verifyAndFulfill($order, ['mock_action' => 'success']);
        $this->assertTrue($result2->isSuccessful());
        $this->assertEquals(1, UserEntitlement::where('user_id', $this->user->id)->where('cv_template_id', $this->premiumTemplate->id)->count());
    }

    public function test_webhook_fulfillment_grants_entitlement_safely(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-WEBHOOK-999',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_provider' => 'mock',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'item_name' => "Premium Template: {$this->premiumTemplate->name}",
            'price' => 12.99,
            'quantity' => 1,
            'subtotal' => 12.99,
        ]);

        $response = $this->postJson(route('checkout.webhook', ['gateway' => 'mock']), [
            'order_id' => $order->id,
            'transaction_id' => 'TXN-MOCK-999',
            'status' => 'success',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertTrue($this->user->fresh()->hasAccessToTemplate($this->premiumTemplate));
    }

    public function test_user_billing_history_displays_orders_and_entitlements(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-BILL-1001',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_provider' => 'mock',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'item_name' => "Premium Template: {$this->premiumTemplate->name}",
            'price' => 12.99,
            'quantity' => 1,
            'subtotal' => 12.99,
        ]);

        UserEntitlement::create([
            'user_id' => $this->user->id,
            'cv_template_id' => $this->premiumTemplate->id,
            'entitlement_type' => 'template_unlock',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)->get(route('billing.index'));

        $response->assertStatus(200);
        $response->assertSee('ORD-BILL-1001');
        $response->assertSee('Executive Gold Pro');
        $response->assertSee('$12.99');
        $response->assertSee('Paid');
    }

    public function test_user_can_view_single_order_receipt(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-RECEIPT-2002',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_provider' => 'mock',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'item_name' => "Premium Template: {$this->premiumTemplate->name}",
            'price' => 12.99,
            'quantity' => 1,
            'subtotal' => 12.99,
        ]);

        $response = $this->actingAs($this->user)->get(route('billing.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('ORD-RECEIPT-2002');
        $response->assertSee('Executive Gold Pro');
        $response->assertSee('$12.99');
    }

    public function test_user_cannot_view_another_users_receipt(): void
    {
        $otherUser = User::factory()->create();

        $order = Order::create([
            'user_id' => $otherUser->id,
            'order_number' => 'ORD-PRIVATE-3003',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_provider' => 'mock',
        ]);

        $response = $this->actingAs($this->user)->get(route('billing.orders.show', $order));
        $response->assertStatus(403);
    }

    public function test_admin_orders_index_lists_all_transactions(): void
    {
        Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-ADMIN-4004',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_provider' => 'mock',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.index'));

        $response->assertStatus(200);
        $response->assertSee('ORD-ADMIN-4004');
        $response->assertSee('Total Revenue');
    }

    public function test_admin_order_show_inspects_transaction_payload(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'ORD-AUDIT-5005',
            'total_amount' => 12.99,
            'currency' => 'USD',
            'status' => 'paid',
            'payment_provider' => 'mock',
            'payment_details' => ['test_ip' => '127.0.0.1', 'auth_code' => 'AUTH9988'],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.orders.show', $order));

        $response->assertStatus(200);
        $response->assertSee('ORD-AUDIT-5005');
        $response->assertSee('AUTH9988');
    }

    public function test_non_admin_cannot_access_admin_orders(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.orders.index'));
        $response->assertStatus(403);
    }

    public function test_guest_redirected_to_login_on_checkout_and_billing(): void
    {
        $responseCheckout = $this->get(route('checkout.template', $this->premiumTemplate));
        $responseCheckout->assertRedirect(route('login'));

        $responseBilling = $this->get(route('billing.index'));
        $responseBilling->assertRedirect(route('login'));
    }
}
