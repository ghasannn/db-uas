<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Organization;
use App\Models\Category;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmikomEventHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /** Test Google SSO Mock login */
    public function test_sso_google_mock_login_creates_or_authenticates_buyer()
    {
        $response = $this->get('/auth/google/callback?mock=1&email=test_sso@amikom.ac.id&name=SSO+User');

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'test_sso@amikom.ac.id',
            'provider' => 'google',
            'role' => 'buyer',
        ]);
        $this->assertAuthenticated();
    }

    /** Test Organizer Registration creates pending organization */
    public function test_organizer_registration_creates_pending_organization()
    {
        $response = $this->post('/organizer/register', [
            'name' => 'John Organizer',
            'email' => 'new_org@amikom.ac.id',
            'password' => 'password123',
            'organization_name' => 'Kepanitiaan Amikom Tech',
            'description' => 'Penyelenggara Tech Conference',
        ]);

        $response->assertRedirect('/organizer/dashboard');
        $this->assertDatabaseHas('organizations', [
            'name' => 'Kepanitiaan Amikom Tech',
            'status' => 'pending',
        ]);
    }

    /** Test Superadmin Approval of Organizer */
    public function test_superadmin_can_approve_pending_organizer()
    {
        $superadmin = User::where('role', 'superadmin')->first();
        $pendingOrg = Organization::where('status', 'pending')->first();

        $response = $this->actingAs($superadmin, 'admin')->post('/admin/organizers/' . $pendingOrg->id . '/approve');

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('organizations', [
            'id' => $pendingOrg->id,
            'status' => 'approved',
        ]);
    }

    /** Test Tenant Isolation: Organizer A cannot edit Organizer B's event */
    public function test_tenant_isolation_prevents_organizer_from_editing_other_tenant_event()
    {
        $org1Owner = User::where('email', 'bem@amikom.ac.id')->first();
        $org2Owner = User::where('email', 'himika@amikom.ac.id')->first();

        $org1Event = Event::where('organization_id', $org1Owner->organization_id)->first();

        // Org 2 owner tries to edit Org 1's event
        $response = $this->actingAs($org2Owner, 'admin')->get('/admin/events/' . $org1Event->id . '/edit');

        $response->assertStatus(403);
    }

    /** Test Free Event Checkout Bypass (No Midtrans call, instant free_claimed status) */
    public function test_free_event_checkout_bypasses_midtrans()
    {
        $freeEvent = Event::where('is_free', true)->first();

        $createResponse = $this->get('/checkout/' . $freeEvent->id);
        $createResponse->assertStatus(200);
        $createResponse->assertSee('Gratis (Rp 0)');
        $createResponse->assertSee('Klaim Tiket Gratis');

        $response = $this->post('/checkout/' . $freeEvent->id, [
            'customer_name' => 'Budi Pembeli',
            'customer_email' => 'budi@gmail.com',
            'customer_phone' => '081234567890',
        ]);

        $this->assertDatabaseHas('transactions', [
            'event_id' => $freeEvent->id,
            'customer_email' => 'budi@gmail.com',
            'total_price' => 0,
            'status' => 'free_claimed',
        ]);

        $lastTrx = Transaction::latest('id')->first();
        $response->assertRedirect('/checkout/success/' . $lastTrx->order_id);
    }

    /** Test Paid Event Checkout Reserves Ticket stock with pessimistic lock */
    public function test_paid_event_checkout_creates_reserved_transaction()
    {
        $paidEvent = Event::where('price', '>', 0)->first();
        $initialReserved = $paidEvent->reserved_count;

        $response = $this->post('/checkout/' . $paidEvent->id, [
            'customer_name' => 'Agus Pembeli',
            'customer_email' => 'agus@gmail.com',
            'customer_phone' => '081234567891',
        ]);

        $paidEvent->refresh();
        $this->assertEquals($initialReserved + 1, $paidEvent->reserved_count);

        $this->assertDatabaseHas('transactions', [
            'event_id' => $paidEvent->id,
            'customer_email' => 'agus@gmail.com',
            'status' => 'reserved',
        ]);
    }

    /** Test Rating & Review constraints */
    public function test_review_can_only_be_submitted_for_completed_event_by_paid_buyer()
    {
        $buyer = User::where('email', 'buyer@amikom.ac.id')->first();
        $futureEvent = Event::where('date', '>', now())->first();

        // 1. Trying to review a future event should fail
        $response = $this->actingAs($buyer)->post('/events/' . $futureEvent->id . '/reviews', [
            'rating' => 5,
            'comment' => 'Event bagus sekali!',
        ]);

        $response->assertSessionHas('error');

        // 2. Reviewing past event with paid transaction succeeds
        $pastEvent = Event::where('date', '<', now())->first();
        // Remove existing review if seeded
        Review::where('user_id', $buyer->id)->where('event_id', $pastEvent->id)->delete();

        $response2 = $this->actingAs($buyer)->post('/events/' . $pastEvent->id . '/reviews', [
            'rating' => 5,
            'comment' => 'Event luar biasa dan sukses!',
        ]);

        $response2->assertSessionHas('success');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $buyer->id,
            'event_id' => $pastEvent->id,
            'rating' => 5,
        ]);
    }

    /** Test Superadmin event creation is attributed to Admin Amikom, not BEM */
    public function test_superadmin_creates_event_attributed_to_admin_amikom_not_bem()
    {
        $superadmin = User::where('role', 'superadmin')->first();
        $category = Category::first();

        $response = $this->actingAs($superadmin, 'admin')->post('/admin/events', [
            'category_id' => $category->id,
            'title' => 'Event Resmi Superadmin',
            'description' => 'Event buatan superadmin',
            'date' => '2026-12-01 10:00:00',
            'location' => 'Amikom Main Hall',
            'price' => 10000,
            'stock' => 50,
        ]);

        $response->assertRedirect('/admin/events');

        $createdEvent = Event::where('title', 'Event Resmi Superadmin')->first();
        $this->assertNotNull($createdEvent);

        $adminOrg = Organization::where('slug', 'admin-amikom')->first();
        $this->assertNotNull($adminOrg);
        $this->assertEquals($adminOrg->id, $createdEvent->organization_id);
    }

    /** Test Organizer Directory index page displays list of organizers */
    public function test_organizer_directory_page_is_accessible()
    {
        $response = $this->get('/organizers');

        $response->assertStatus(200);
        $response->assertSee('Daftar Penyelenggara Event Resmi');
        $response->assertSee('BEM Amikom Yogyakarta');
    }

    /** Test Reserving ticket decrements stock immediately and releasing returns stock +1 */
    public function test_reserving_ticket_decrements_stock_and_releasing_returns_stock()
    {
        $paidEvent = Event::where('price', '>', 0)->first();
        $initialStock = $paidEvent->stock;

        // Reserve ticket
        $response = $this->post('/checkout/' . $paidEvent->id, [
            'customer_name' => 'Testing Stock',
            'customer_email' => 'testing_stock@gmail.com',
            'customer_phone' => '081234567899',
        ]);

        $paidEvent->refresh();
        $this->assertEquals($initialStock - 1, $paidEvent->stock);

        // Expire transaction & verify stock returns +1
        $lastTrx = Transaction::where('customer_email', 'testing_stock@gmail.com')->first();
        $lastTrx->expires_at = now()->subMinute();
        $lastTrx->save();

        $lastTrx->syncMidtransStatus();

        $paidEvent->refresh();
        $this->assertEquals($initialStock, $paidEvent->stock);
        $this->assertEquals('expired', $lastTrx->fresh()->status);
    }

    /** Test User can cancel reserved order and restore stock */
    public function test_user_can_cancel_reserved_order_and_restore_stock()
    {
        $paidEvent = Event::where('price', '>', 0)->first();
        $initialStock = $paidEvent->stock;

        // Reserve ticket
        $this->post('/checkout/' . $paidEvent->id, [
            'customer_name' => 'Cancel Test User',
            'customer_email' => 'cancel_user@gmail.com',
            'customer_phone' => '081234567888',
        ]);

        $paidEvent->refresh();
        $this->assertEquals($initialStock - 1, $paidEvent->stock);

        $trx = Transaction::where('customer_email', 'cancel_user@gmail.com')->first();
        $this->assertNotNull($trx);
        $this->assertEquals('reserved', $trx->status);

        // Cancel order via POST endpoint
        $cancelResponse = $this->post('/checkout/cancel/' . $trx->order_id);

        $cancelResponse->assertRedirect('/events/' . $paidEvent->id);
        $cancelResponse->assertSessionHas('success');

        $paidEvent->refresh();
        $trx->refresh();

        $this->assertEquals('cancelled', $trx->status);
        $this->assertEquals($initialStock, $paidEvent->stock);
    }
}
