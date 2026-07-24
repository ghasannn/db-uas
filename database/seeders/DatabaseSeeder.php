<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Event;
use App\Models\Organization;
use App\Models\Transaction;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Superadmin Account & Organization
        $superadmin = User::create([
            'name' => 'Superadmin Amikom',
            'email' => 'admin@amikom.ac.id',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        $adminOrg = Organization::create([
            'name' => 'Admin Amikom',
            'slug' => 'admin-amikom',
            'owner_user_id' => $superadmin->id,
            'description' => 'Penyelenggara Event Resmi Admin Amikom',
            'status' => 'approved',
        ]);

        $superadmin->update(['organization_id' => $adminOrg->id]);

        // 2. Sample Organizations & Owners
        $organizerUser1 = User::create([
            'name' => 'BEM Amikom',
            'email' => 'bem@amikom.ac.id',
            'password' => bcrypt('password'),
            'role' => 'organizer_owner',
        ]);

        $org1 = Organization::create([
            'name' => 'BEM Amikom Yogyakarta',
            'slug' => 'bem-amikom',
            'owner_user_id' => $organizerUser1->id,
            'description' => 'Badan Eksekutif Mahasiswa Universitas Amikom Yogyakarta',
            'status' => 'approved',
        ]);

        $organizerUser1->update(['organization_id' => $org1->id]);

        $organizerUser2 = User::create([
            'name' => 'HIMIKA Amikom',
            'email' => 'himika@amikom.ac.id',
            'password' => bcrypt('password'),
            'role' => 'organizer_owner',
        ]);

        $org2 = Organization::create([
            'name' => 'HIMIKA Amikom',
            'slug' => 'himika-amikom',
            'owner_user_id' => $organizerUser2->id,
            'description' => 'Himpunan Mahasiswa Informatika Amikom',
            'status' => 'pending', // Pending approval
        ]);

        $organizerUser2->update(['organization_id' => $org2->id]);

        // 3. Buyer User
        $buyer = User::create([
            'name' => 'Nur Amin',
            'email' => 'buyer@amikom.ac.id',
            'password' => bcrypt('password'),
            'role' => 'buyer',
        ]);

        // 4. Categories
        $catIT = Category::create([
            'name' => 'Seminar IT',
            'slug' => 'seminar-it',
        ]);

        $catEnt = Category::create([
            'name' => 'Entertainment',
            'slug' => 'entertainment',
        ]);

        // 5. Events
        $eventPaid = Event::create([
            'organization_id' => $org1->id,
            'category_id' => $catEnt->id,
            'title' => 'Jazz Night 2026',
            'description' => 'Nikmati malam yang indah dengan alunan musik jazz yang merdu di Kampus Amikom.',
            'date' => '2026-10-10 19:00:00',
            'location' => 'Amikom Baru',
            'price' => 50000,
            'stock' => 100,
            'quota' => 100,
            'reserved_count' => 0,
            'sold_count' => 0,
            'status' => 'published',
            'is_free' => false,
            'poster_path' => 'posters/event-1.png',
        ]);

        $eventFree = Event::create([
            'organization_id' => $org1->id,
            'category_id' => $catIT->id,
            'title' => 'Hackathon Gratis - Unleash Your Code',
            'description' => 'Ayo asah skill coding kamu dan ciptakan solusi inovatif! Gratis pendaftaran.',
            'date' => '2026-09-05 10:00:00',
            'location' => 'Inkubator Amikom',
            'price' => 0,
            'stock' => 50,
            'quota' => 50,
            'reserved_count' => 0,
            'sold_count' => 0,
            'status' => 'published',
            'is_free' => true,
            'poster_path' => 'posters/event-2.png',
        ]);

        $eventPast = Event::create([
            'organization_id' => $org1->id,
            'category_id' => $catIT->id,
            'title' => 'AI & FUTURE TECH SUMMIT 2025 (Selesai)',
            'description' => 'Konferensi AI terbesar di Amikom yang telah sukses diselenggarakan.',
            'date' => '2026-01-15 13:00:00', // Past event
            'location' => 'Cinema Unit 6',
            'price' => 25000,
            'stock' => 100,
            'quota' => 100,
            'reserved_count' => 0,
            'sold_count' => 1,
            'status' => 'ended',
            'is_free' => false,
            'poster_path' => 'posters/event-3.png',
        ]);

        // 6. Completed Transaction for Past Event (to test Rating & Review)
        $pastOrder = Transaction::create([
            'organization_id' => $org1->id,
            'user_id' => $buyer->id,
            'event_id' => $eventPast->id,
            'order_id' => 'TRX-PAST-001',
            'customer_name' => $buyer->name,
            'customer_email' => $buyer->email,
            'customer_phone' => '081234567890',
            'total_price' => 25000,
            'status' => 'paid',
        ]);

        // Sample initial review
        Review::create([
            'user_id' => $buyer->id,
            'event_id' => $eventPast->id,
            'organization_id' => $org1->id,
            'rating' => 5,
            'comment' => 'Acara luar biasa! Pembicara sangat kompeten dan ilmu yang didapat sangat berguna.',
        ]);
    }
}
