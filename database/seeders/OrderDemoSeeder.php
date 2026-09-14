<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\OrderStatus;
use App\Support\PaymentStatus;
use App\Support\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * SAMPLE TRANSACTION DATA FOR LOCAL DEVELOPMENT / STAGING ONLY.
 *
 * Never wire this into DatabaseSeeder, as deploy.yml runs db:seed on production.
 * Run manually via:
 *
 *     php artisan db:seed --class=OrderDemoSeeder
 *  or
 *     php artisan db:seed --class=EcommerceDemoSeeder
 */
class OrderDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->error('OrderDemoSeeder is sample data and refuses to run in production.');

            return;
        }

        // Ensure sample products exist first
        if (Product::count() === 0) {
            $this->call(ProductDemoSeeder::class);
        }

        $products = Product::all()->keyBy('slug');

        $router     = $products->get('paket-router-wi-fi-6-terpasang');
        $kabelLan   = $products->get('kabel-lan-cat6-terminasi-per-meter');
        $switchHub  = $products->get('switch-hub-gigabit-8-port');
        $ssdNvme    = $products->get('ssd-nvme-m2-1tb-pcie-gen4');
        $webcam     = $products->get('webcam-pro-full-hd-1080p');
        $auditWeb   = $products->get('audit-keamanan-website-1-domain');
        $landing    = $products->get('pembuatan-landing-page-profil-usaha');
        $vpsSetup   = $products->get('paket-setup-vps-cloud-hardening-linux');

        $ordersData = [
            // ── Order 1: QRIS Lunas (Multi-item Barang) ──────────────────────────
            [
                'order_number'    => 'RB-20260914-0001',
                'public_token'    => 'demo-order-qris-settled-001',
                'customer_name'   => 'Budi Santoso',
                'customer_email'  => 'budi.santoso@example.com',
                'customer_phone'  => '+6281234567890',
                'shipping_address'=> 'Jl. Sudirman No. 45, RT 02 / RW 05, Kel. Senayan, Kec. Kebayoran Baru, Jakarta Selatan 12190',
                'preferred_date'  => null,
                'notes'           => 'Mohon dipacking kayu / bubble wrap tebal ya.',
                'status'          => OrderStatus::SELESAI,
                'payment_status'  => PaymentStatus::LUNAS,
                'payment_method'  => 'qris',
                'payment_proof'   => null,
                'payment_note'    => 'Pembayaran QRIS terkonfirmasi otomatis oleh Midtrans Core API.',
                'paid_at'         => Carbon::parse('2026-09-14 09:15:30'),
                'created_at'      => Carbon::parse('2026-09-14 09:10:00'),
                'midtrans_transaction_id' => 'midtrans-demo-qris-001',
                'midtrans_payment_type'   => 'qris',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'settlement',
                    'payment_type'       => 'qris',
                    'transaction_time'   => '2026-09-14 09:10:00',
                    'settlement_time'    => '2026-09-14 09:15:30',
                    'gross_amount'       => '825000.00',
                    'qr_string'          => '00020101021226540014ID.LINKAJA.01189360091100220914020300051440014ID.DANA.WWW0118936008980022091402030005204549953033605802ID5911RBEverything6007Jakarta61051234062070703A0163041D3B',
                ],
                'items'           => [
                    [
                        'product'  => $router,
                        'qty'      => 1,
                        'price'    => 450000,
                    ],
                    [
                        'product'  => $kabelLan,
                        'qty'      => 3,
                        'price'    => 125000,
                    ],
                ],
            ],

            // ── Order 2: BCA VA Menunggu (Multi-item Barang) ─────────────────────
            [
                'order_number'    => 'RB-20260914-0002',
                'public_token'    => 'demo-order-bca-va-pending-002',
                'customer_name'   => 'Dewi Lestari',
                'customer_email'  => 'dewi.lestari@example.com',
                'customer_phone'  => '+6281398765432',
                'shipping_address'=> 'Ruko Dago Boulevard Blok B-12, Jl. Ir. H. Juanda, Bandung, Jawa Barat 40132',
                'preferred_date'  => null,
                'notes'           => 'Kirim saat jam kantor (Senin-Jumat 09.00-17.00).',
                'status'          => OrderStatus::BARU,
                'payment_status'  => PaymentStatus::MENUNGGU,
                'payment_method'  => 'bca_va',
                'payment_proof'   => null,
                'payment_note'    => null,
                'paid_at'         => null,
                'created_at'      => Carbon::parse('2026-09-14 10:20:00'),
                'midtrans_transaction_id' => 'midtrans-demo-bca-002',
                'midtrans_payment_type'   => 'bank_transfer',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'pending',
                    'payment_type'       => 'bank_transfer',
                    'transaction_time'   => '2026-09-14 10:20:00',
                    'expiry_time'        => '2026-09-15 10:20:00',
                    'gross_amount'       => '770000.00',
                    'va_numbers'         => [
                        [
                            'bank'      => 'bca',
                            'va_number' => '7001289123456789',
                        ],
                    ],
                ],
                'items'           => [
                    [
                        'product'  => $switchHub,
                        'qty'      => 1,
                        'price'    => 350000,
                    ],
                    [
                        'product'  => $webcam,
                        'qty'      => 1,
                        'price'    => 420000,
                    ],
                ],
            ],

            // ── Order 3: BNI VA Lunas (Single-item Barang) ────────────────────────
            [
                'order_number'    => 'RB-20260913-0001',
                'public_token'    => 'demo-order-bni-va-settled-003',
                'customer_name'   => 'Ahmad Fauzi',
                'customer_email'  => 'ahmad.fauzi@example.com',
                'customer_phone'  => '+6282112345678',
                'shipping_address'=> 'Jl. Pemuda No. 88, Rawamangun, Jakarta Timur 13220',
                'preferred_date'  => null,
                'notes'           => null,
                'status'          => OrderStatus::DIPROSES,
                'payment_status'  => PaymentStatus::LUNAS,
                'payment_method'  => 'bni_va',
                'payment_proof'   => null,
                'payment_note'    => 'Pembayaran BNI VA telah diterima.',
                'paid_at'         => Carbon::parse('2026-09-13 14:35:10'),
                'created_at'      => Carbon::parse('2026-09-13 14:10:00'),
                'midtrans_transaction_id' => 'midtrans-demo-bni-003',
                'midtrans_payment_type'   => 'bank_transfer',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'settlement',
                    'payment_type'       => 'bank_transfer',
                    'transaction_time'   => '2026-09-13 14:10:00',
                    'settlement_time'    => '2026-09-13 14:35:10',
                    'gross_amount'       => '1250000.00',
                    'va_numbers'         => [
                        [
                            'bank'      => 'bni',
                            'va_number' => '8808123456789012',
                        ],
                    ],
                ],
                'items'           => [
                    [
                        'product'  => $ssdNvme,
                        'qty'      => 1,
                        'price'    => 1250000,
                    ],
                ],
            ],

            // ── Order 4: Transfer Manual Menunggu Verifikasi (Jasa) ───────────────
            [
                'order_number'    => 'RB-20260914-0003',
                'public_token'    => 'demo-order-manual-verify-004',
                'customer_name'   => 'Siti Rahmawati',
                'customer_email'  => 'siti.rahma@example.com',
                'customer_phone'  => '+6285712349999',
                'shipping_address'=> null,
                'preferred_date'  => Carbon::parse('2026-09-18'),
                'notes'           => 'Mohon audit difokuskan pada API endpoint dan proteksi otentikasi login aplikasi kami.',
                'status'          => OrderStatus::BARU,
                'payment_status'  => PaymentStatus::MENUNGGU_VERIFIKASI,
                'payment_method'  => 'manual_transfer',
                'payment_proof'   => 'payment_proofs/sample_transfer_receipt.jpg',
                'payment_note'    => 'Sudah transfer via m-BCA a.n. Siti Rahmawati sebesar Rp 750.000 pada jam 11.15 WIB.',
                'paid_at'         => null,
                'created_at'      => Carbon::parse('2026-09-14 11:00:00'),
                'midtrans_transaction_id' => null,
                'midtrans_payment_type'   => null,
                'midtrans_payment_payload'=> null,
                'items'           => [
                    [
                        'product'  => $auditWeb,
                        'qty'      => 1,
                        'price'    => 750000,
                    ],
                ],
            ],

            // ── Order 5: Mandiri Bill Menunggu (Multi-item Barang) ────────────────
            [
                'order_number'    => 'RB-20260914-0004',
                'public_token'    => 'demo-order-mandiri-bill-pending-005',
                'customer_name'   => 'Hendra Gunawan',
                'customer_email'  => 'hendra.gunawan@example.com',
                'customer_phone'  => '+6287812340011',
                'shipping_address'=> 'Jl. Malioboro No. 12, Sosromenduran, Gedong Tengen, Kota Yogyakarta 55271',
                'preferred_date'  => null,
                'notes'           => 'Tolong dicek kelengkapan adaptor switch-nya.',
                'status'          => OrderStatus::BARU,
                'payment_status'  => PaymentStatus::MENUNGGU,
                'payment_method'  => 'mandiri_bill',
                'payment_proof'   => null,
                'payment_note'    => null,
                'paid_at'         => null,
                'created_at'      => Carbon::parse('2026-09-14 11:10:00'),
                'midtrans_transaction_id' => 'midtrans-demo-echannel-005',
                'midtrans_payment_type'   => 'echannel',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'pending',
                    'payment_type'       => 'echannel',
                    'transaction_time'   => '2026-09-14 11:10:00',
                    'expiry_time'        => '2026-09-15 11:10:00',
                    'gross_amount'       => '600000.00',
                    'biller_code'        => '70012',
                    'bill_key'           => '9912345678',
                ],
                'items'           => [
                    [
                        'product'  => $switchHub,
                        'qty'      => 1,
                        'price'    => 350000,
                    ],
                    [
                        'product'  => $kabelLan,
                        'qty'      => 2,
                        'price'    => 125000,
                    ],
                ],
            ],

            // ── Order 6: Manual Transfer Selesai / Terverifikasi (Jasa) ──────────
            [
                'order_number'    => 'RB-20260911-0001',
                'public_token'    => 'demo-order-manual-settled-006',
                'customer_name'   => 'PT Inovasi Mandiri Kreasi (Bpk. Joko)',
                'customer_email'  => 'procurement@inovasimandiri.co.id',
                'customer_phone'  => '+62811889900',
                'shipping_address'=> null,
                'preferred_date'  => Carbon::parse('2026-09-12'),
                'notes'           => 'Proyek pembuatan landing page profil usaha telah selesai deploy ke domain klien.',
                'status'          => OrderStatus::SELESAI,
                'payment_status'  => PaymentStatus::LUNAS,
                'payment_method'  => 'manual_transfer',
                'payment_proof'   => 'payment_proofs/mandiri_transfer_confirmed.jpg',
                'payment_note'    => 'Transfer Mandiri No Ref 20260911009823 telah diverifikasi tim finance.',
                'paid_at'         => Carbon::parse('2026-09-11 11:20:00'),
                'created_at'      => Carbon::parse('2026-09-11 10:45:00'),
                'midtrans_transaction_id' => null,
                'midtrans_payment_type'   => null,
                'midtrans_payment_payload'=> null,
                'items'           => [
                    [
                        'product'  => $landing,
                        'qty'      => 1,
                        'price'    => 2500000,
                    ],
                ],
            ],

            // ── Order 7: BRI VA Lunas (Kombinasi Barang & Jasa) ─────────────────
            [
                'order_number'    => 'RB-20260912-0001',
                'public_token'    => 'demo-order-briva-settled-007',
                'customer_name'   => 'Rian Pratama',
                'customer_email'  => 'rian.pratama@example.com',
                'customer_phone'  => '+6281987654321',
                'shipping_address'=> 'Jl. Darmo No. 104, Wonokromo, Surabaya, Jawa Timur 60241',
                'preferred_date'  => Carbon::parse('2026-09-15'),
                'notes'           => 'Untuk setup VPS mohon kirim detail akses via email.',
                'status'          => OrderStatus::DIPROSES,
                'payment_status'  => PaymentStatus::LUNAS,
                'payment_method'  => 'bri_va',
                'payment_proof'   => null,
                'payment_note'    => 'Pembayaran BRIVA sukses terverifikasi.',
                'paid_at'         => Carbon::parse('2026-09-12 16:45:00'),
                'created_at'      => Carbon::parse('2026-09-12 16:20:00'),
                'midtrans_transaction_id' => 'midtrans-demo-bri-007',
                'midtrans_payment_type'   => 'bank_transfer',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'settlement',
                    'payment_type'       => 'bank_transfer',
                    'transaction_time'   => '2026-09-12 16:20:00',
                    'settlement_time'    => '2026-09-12 16:45:00',
                    'gross_amount'       => '1100000.00',
                    'va_numbers'         => [
                        [
                            'bank'      => 'bri',
                            'va_number' => '1234509876543210',
                        ],
                    ],
                ],
                'items'           => [
                    [
                        'product'  => $router,
                        'qty'      => 1,
                        'price'    => 450000,
                    ],
                    [
                        'product'  => $vpsSetup,
                        'qty'      => 1,
                        'price'    => 650000,
                    ],
                ],
            ],

            // ── Order 8: QRIS Menunggu (Single-item Barang) ─────────────────────
            [
                'order_number'    => 'RB-20260914-0005',
                'public_token'    => 'demo-order-qris-pending-008',
                'customer_name'   => 'Fitri Handayani',
                'customer_email'  => 'fitri.handayani@example.com',
                'customer_phone'  => '+6285211223344',
                'shipping_address'=> 'Jl. Diponegoro No. 27, Denpasar Barat, Kota Denpasar, Bali 80113',
                'preferred_date'  => null,
                'notes'           => null,
                'status'          => OrderStatus::BARU,
                'payment_status'  => PaymentStatus::MENUNGGU,
                'payment_method'  => 'qris',
                'payment_proof'   => null,
                'payment_note'    => null,
                'paid_at'         => null,
                'created_at'      => Carbon::parse('2026-09-14 11:25:00'),
                'midtrans_transaction_id' => 'midtrans-demo-qris-008',
                'midtrans_payment_type'   => 'qris',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'pending',
                    'payment_type'       => 'qris',
                    'transaction_time'   => '2026-09-14 11:25:00',
                    'expiry_time'        => '2026-09-14 11:40:00',
                    'gross_amount'       => '420000.00',
                    'qr_string'          => '00020101021226540014ID.LINKAJA.01189360091100220914020300051440014ID.DANA.WWW0118936008980022091402030005204549953033605802ID5911RBEverything6007Jakarta61051234062070703A0163041D3B',
                ],
                'items'           => [
                    [
                        'product'  => $webcam,
                        'qty'      => 1,
                        'price'    => 420000,
                    ],
                ],
            ],

            // ── Order 9: Permata VA Dibatalkan / Expired (Single-item) ───────────
            [
                'order_number'    => 'RB-20260910-0001',
                'public_token'    => 'demo-order-permata-cancelled-009',
                'customer_name'   => 'Bayu Wicaksono',
                'customer_email'  => 'bayu.w@example.com',
                'customer_phone'  => '+6281299887766',
                'shipping_address'=> 'Jl. Gajah Mada No. 15, Semarang, Jawa Tengah 50133',
                'preferred_date'  => null,
                'notes'           => null,
                'status'          => OrderStatus::DIBATALKAN,
                'payment_status'  => PaymentStatus::DIBATALKAN,
                'payment_method'  => 'permata_va',
                'payment_proof'   => null,
                'payment_note'    => 'Batas waktu pembayaran telah habis (expired).',
                'paid_at'         => null,
                'created_at'      => Carbon::parse('2026-09-10 08:00:00'),
                'midtrans_transaction_id' => 'midtrans-demo-permata-009',
                'midtrans_payment_type'   => 'bank_transfer',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'expire',
                    'payment_type'       => 'bank_transfer',
                    'transaction_time'   => '2026-09-10 08:00:00',
                    'permata_va_number'  => '8528001234567890',
                    'gross_amount'       => '350000.00',
                ],
                'items'           => [
                    [
                        'product'  => $switchHub,
                        'qty'      => 1,
                        'price'    => 350000,
                    ],
                ],
            ],

            // ── Order 10: CIMB Niaga VA Lunas (Multi-item) ──────────────────────
            [
                'order_number'    => 'RB-20260913-0002',
                'public_token'    => 'demo-order-cimb-va-settled-010',
                'customer_name'   => 'Nadia Putri',
                'customer_email'  => 'nadia.putri@example.com',
                'customer_phone'  => '+6281133445566',
                'shipping_address'=> 'Jl. Raya Pajajaran No. 28, Bogor, Jawa Barat 16143',
                'preferred_date'  => null,
                'notes'           => 'Mohon tes fungsionalitas webcam sebelum dikirim.',
                'status'          => OrderStatus::DIPROSES,
                'payment_status'  => PaymentStatus::LUNAS,
                'payment_method'  => 'cimb_va',
                'payment_proof'   => null,
                'payment_note'    => 'Pembayaran CIMB Niaga VA sukses terkonfirmasi otomatis.',
                'paid_at'         => Carbon::parse('2026-09-13 17:15:00'),
                'created_at'      => Carbon::parse('2026-09-13 16:50:00'),
                'midtrans_transaction_id' => 'midtrans-demo-cimb-010',
                'midtrans_payment_type'   => 'bank_transfer',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'settlement',
                    'payment_type'       => 'bank_transfer',
                    'transaction_time'   => '2026-09-13 16:50:00',
                    'settlement_time'    => '2026-09-13 17:15:00',
                    'gross_amount'       => '770000.00',
                    'va_numbers'         => [
                        [
                            'bank'      => 'cimb',
                            'va_number' => '5919001234567890',
                        ],
                    ],
                ],
                'items'           => [
                    [
                        'product'  => $webcam,
                        'qty'      => 1,
                        'price'    => 420000,
                    ],
                    [
                        'product'  => $switchHub,
                        'qty'      => 1,
                        'price'    => 350000,
                    ],
                ],
            ],

            // ── Order 11: Indomaret Menunggu Pembayaran (Single-item) ───────────
            [
                'order_number'    => 'RB-20260914-0006',
                'public_token'    => 'demo-order-indomaret-pending-011',
                'customer_name'   => 'Rizki Ramadhan',
                'customer_email'  => 'rizki.r@example.com',
                'customer_phone'  => '+6287711223344',
                'shipping_address'=> 'Jl. Veteran No. 5, Malang, Jawa Timur 65145',
                'preferred_date'  => null,
                'notes'           => 'Akan dibayar di Indomaret dekat kampus.',
                'status'          => OrderStatus::BARU,
                'payment_status'  => PaymentStatus::MENUNGGU,
                'payment_method'  => 'indomaret',
                'payment_proof'   => null,
                'payment_note'    => null,
                'paid_at'         => null,
                'created_at'      => Carbon::parse('2026-09-14 11:35:00'),
                'midtrans_transaction_id' => 'midtrans-demo-indomaret-011',
                'midtrans_payment_type'   => 'cstore',
                'midtrans_payment_payload'=> [
                    'transaction_status' => 'pending',
                    'payment_type'       => 'cstore',
                    'transaction_time'   => '2026-09-14 11:35:00',
                    'expiry_time'        => '2026-09-15 11:35:00',
                    'gross_amount'       => '1250000.00',
                    'payment_code'       => '10982345671',
                ],
                'items'           => [
                    [
                        'product'  => $ssdNvme,
                        'qty'      => 1,
                        'price'    => 1250000,
                    ],
                ],
            ],
        ];

        $seededCount = 0;

        foreach ($ordersData as $orderDatum) {
            $itemsData = $orderDatum['items'];
            unset($orderDatum['items']);

            // Calculate subtotal & total
            $subtotal = 0;
            $totalQty = 0;
            $firstProduct = $itemsData[0]['product'] ?? null;

            foreach ($itemsData as $item) {
                $subtotal += ($item['price'] * $item['qty']);
                $totalQty += $item['qty'];
            }

            $orderDatum['subtotal']      = $subtotal;
            $orderDatum['shipping_cost'] = 0;
            $orderDatum['total']         = $subtotal;
            $orderDatum['qty']           = $totalQty;

            if (count($itemsData) === 1 && $firstProduct) {
                $orderDatum['product_id']            = $firstProduct->id;
                $orderDatum['product_name_snapshot'] = $firstProduct->getTranslation('name', 'id');
                $orderDatum['product_type_snapshot'] = $firstProduct->type;
                $orderDatum['price_snapshot']        = $firstProduct->price;
            } else {
                $orderDatum['product_id']            = $firstProduct?->id;
                $orderDatum['product_name_snapshot'] = count($itemsData) . ' Item (' . ($firstProduct?->getTranslation('name', 'id') ?? 'Produk') . ', dll)';
                $orderDatum['product_type_snapshot'] = ProductType::BARANG;
                $orderDatum['price_snapshot']        = null;
            }

            // Create or update order
            $order = Order::updateOrCreate(
                ['order_number' => $orderDatum['order_number']],
                $orderDatum
            );

            // Populate order items
            $order->items()->delete();

            foreach ($itemsData as $item) {
                $productModel = $item['product'];
                if (! $productModel) {
                    continue;
                }

                $order->items()->create([
                    'product_id'            => $productModel->id,
                    'product_name_snapshot' => $productModel->getTranslation('name', 'id'),
                    'product_type_snapshot' => $productModel->type,
                    'price_snapshot'        => $item['price'],
                    'qty'                   => $item['qty'],
                    'subtotal'              => $item['price'] * $item['qty'],
                ]);
            }

            $seededCount++;
        }

        $this->command?->info("Seeded {$seededCount} sample orders with order items (development only).");
    }
}
