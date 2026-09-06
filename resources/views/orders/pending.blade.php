@extends('layouts.public')

@php
    use App\Support\OrderStatus;
    use App\Support\PaymentStatus;
    use App\Support\ProductType;

    $waMessage = rawurlencode(
        "Halo RBeverything, saya mau menanyakan pesanan {$order->order_number} ({$order->product_name_snapshot})."
    );
@endphp

@section('meta_title', 'Pesanan ' . $order->order_number)
{{-- Somebody else's order details must never end up in a search index. --}}
@section('meta_robots', 'noindex, nofollow')

@section('content')

    <section aria-label="Ringkasan pesanan">
        <div class="rb-section" style="padding-bottom:5rem;max-width:52rem;">

            <div class="receipt-hero">
                <div class="receipt-check" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </div>
                <h1 class="receipt-title">Pesanan kamu sudah kami terima</h1>
                <p class="receipt-lead">
                    Simpan halaman ini. Kami menghubungi kamu lewat WhatsApp atau email untuk konfirmasi
                    {{ $order->needsShipping() ? 'ketersediaan dan ongkos kirim' : 'jadwal pengerjaan' }},
                    lalu memberi instruksi pembayaran.
                </p>

                <div class="receipt-number">
                    <span>Nomor pesanan</span>
                    <strong>{{ $order->order_number }}</strong>
                </div>
            </div>

            <div class="receipt-card">

                <div class="receipt-statuses">
                    <div class="receipt-status">
                        <span class="receipt-status__label">Status pesanan</span>
                        <span class="receipt-badge receipt-badge--{{ $order->status }}">
                            {{ OrderStatus::label($order->status) }}
                        </span>
                    </div>
                    <div class="receipt-status">
                        <span class="receipt-status__label">Status pembayaran</span>
                        <span class="receipt-badge receipt-badge--payment">
                            {{ PaymentStatus::label($order->payment_status) }}
                        </span>
                    </div>
                </div>

                <dl class="receipt-lines">
                    <div class="receipt-line">
                        <dt>Produk</dt>
                        <dd>
                            {{ $order->product_name_snapshot }}
                            <span class="receipt-type">{{ ProductType::label($order->product_type_snapshot) }}</span>
                        </dd>
                    </div>
                    <div class="receipt-line">
                        <dt>Jumlah</dt>
                        <dd>{{ $order->qty }}</dd>
                    </div>
                    <div class="receipt-line">
                        <dt>Subtotal</dt>
                        <dd>{{ $order->formattedSubtotal() }}</dd>
                    </div>
                    @if($order->needsShipping())
                        <div class="receipt-line">
                            <dt>Ongkos kirim</dt>
                            <dd>{{ $order->formattedShipping() ?? 'Belum dihitung' }}</dd>
                        </div>
                    @endif
                    <div class="receipt-line receipt-line--total">
                        <dt>Total{{ $order->needsShipping() && $order->shipping_cost === null ? ' sementara' : '' }}</dt>
                        <dd>{{ $order->formattedTotal() }}</dd>
                    </div>
                </dl>

                <div class="receipt-block">
                    <h2 class="receipt-block__title">Data pemesan</h2>
                    <dl class="receipt-lines receipt-lines--plain">
                        <div class="receipt-line"><dt>Nama</dt><dd>{{ $order->customer_name }}</dd></div>
                        <div class="receipt-line"><dt>Email</dt><dd>{{ $order->customer_email }}</dd></div>
                        <div class="receipt-line"><dt>WhatsApp/HP</dt><dd>{{ $order->customer_phone }}</dd></div>
                        @if($order->shipping_address)
                            <div class="receipt-line"><dt>Alamat</dt><dd>{{ $order->shipping_address }}</dd></div>
                        @endif
                        @if($order->preferred_date)
                            <div class="receipt-line"><dt>Tanggal diinginkan</dt><dd>{{ $order->preferred_date->format('d/m/Y') }}</dd></div>
                        @endif
                        @if($order->notes)
                            <div class="receipt-line"><dt>Catatan</dt><dd>{{ $order->notes }}</dd></div>
                        @endif
                    </dl>
                </div>

                {{-- Phase 4 replaces this block with real payment instructions
                     (bank transfer details + proof upload). Until then, saying
                     plainly what happens next beats a payment button that does
                     nothing. --}}
                <div class="receipt-payment">
                    <h2 class="receipt-block__title">Pembayaran</h2>
                    <p>
                        Pembayaran online belum aktif. Kami akan mengirimkan instruksi pembayaran
                        (transfer bank) setelah pesanan ini dikonfirmasi. Sebutkan nomor
                        <strong>{{ $order->order_number }}</strong> saat menghubungi kami.
                    </p>

                    <div class="receipt-actions">
                        @if($contact['whatsapp'])
                            <a href="{{ $contact['whatsapp'] }}?text={{ $waMessage }}"
                               class="rb-btn-primary receipt-btn" target="_blank" rel="noopener">
                                Hubungi via WhatsApp
                            </a>
                        @endif
                        <a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Pesanan ' . $order->order_number) }}"
                           class="rb-btn-ghost receipt-btn">
                            Kirim Email
                        </a>
                    </div>
                </div>
            </div>

            <p class="receipt-footnote">
                Tautan halaman ini bersifat pribadi — hanya orang yang memilikinya bisa melihat detail pesanan.
                Jangan dibagikan ke orang lain.
            </p>

            <a href="{{ route('products.index') }}" class="receipt-back">&larr; Kembali ke katalog</a>
        </div>
    </section>

@endsection

@section('styles')
<style>
.receipt-hero { text-align: center; margin-bottom: 2.5rem; }

.receipt-check {
    width: 3.25rem; height: 3.25rem; margin: 0 auto 1.25rem;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    color: #34D399;
    background: rgba(52,211,153,0.08);
    border: 1px solid rgba(52,211,153,0.25);
}

.receipt-title {
    font-size: clamp(1.6rem, 3.5vw, 2.25rem);
    font-weight: 900; letter-spacing: -0.025em; color: #F5F5F5;
    margin: 0 0 0.75rem; line-height: 1.2;
}
.receipt-lead {
    font-size: 0.95rem; color: var(--color-muted); line-height: 1.75;
    max-width: 34rem; margin: 0 auto 1.75rem;
}

.receipt-number {
    display: inline-flex; flex-direction: column; gap: 0.2rem; align-items: center;
    padding: 0.75rem 1.5rem;
    border: 1px dashed var(--rb-red-border);
    border-radius: 0.85rem;
    background: rgba(185,28,28,0.05);
}
.receipt-number span { font-size: 0.7rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--color-muted); }
.receipt-number strong { font-family: var(--font-mono, monospace); font-size: 1.15rem; color: #F5F5F5; letter-spacing: 0.02em; }

.receipt-card {
    border: 1px solid var(--color-border);
    border-radius: 1.25rem;
    background: rgba(255,255,255,0.025);
    padding: 1.75rem;
    display: flex; flex-direction: column; gap: 1.75rem;
}

.receipt-statuses { display: flex; flex-wrap: wrap; gap: 2rem; }
.receipt-status { display: flex; flex-direction: column; gap: 0.4rem; }
.receipt-status__label { font-size: 0.7rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--color-muted); }

.receipt-badge {
    font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;
    padding: 0.25rem 0.7rem; border-radius: 999px; width: fit-content;
    border: 1px solid rgba(251,191,36,0.25); background: rgba(251,191,36,0.08); color: #FBBF24;
}
.receipt-badge--diproses  { border-color: rgba(56,189,248,0.25); background: rgba(56,189,248,0.08); color: #38BDF8; }
.receipt-badge--selesai   { border-color: rgba(52,211,153,0.25); background: rgba(52,211,153,0.08); color: #34D399; }
.receipt-badge--dibatalkan{ border-color: rgba(220,38,38,0.3);  background: rgba(220,38,38,0.08);  color: #FCA5A5; }

.receipt-lines { display: flex; flex-direction: column; gap: 0.65rem; margin: 0; }
.receipt-line {
    display: flex; justify-content: space-between; align-items: baseline; gap: 1.5rem;
    font-size: 0.9rem;
}
.receipt-line dt { color: var(--color-muted); flex-shrink: 0; }
.receipt-line dd { margin: 0; color: var(--color-text); text-align: right; }
.receipt-line--total {
    padding-top: 0.75rem; border-top: 1px solid var(--color-border);
    font-size: 1rem;
}
.receipt-line--total dd { font-weight: 800; color: #F5F5F5; font-size: 1.15rem; }

.receipt-type {
    font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.06em;
    color: var(--color-muted); margin-left: 0.5rem;
}

.receipt-block__title {
    font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--rb-red); font-weight: 700; margin: 0 0 0.9rem;
}
.receipt-block { border-top: 1px solid var(--color-border); padding-top: 1.5rem; }

.receipt-payment { border-top: 1px solid var(--color-border); padding-top: 1.5rem; }
.receipt-payment p { font-size: 0.9rem; color: var(--color-muted); line-height: 1.75; margin: 0 0 1.25rem; }
.receipt-payment strong { color: var(--color-text); }

.receipt-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.receipt-btn {
    display: inline-flex; align-items: center; gap: 0.5rem;
    padding: 0.7rem 1.4rem; border-radius: 999px;
    font-size: 0.85rem; font-weight: 700; text-decoration: none;
}

.receipt-footnote {
    font-size: 0.78rem; color: var(--color-muted); line-height: 1.6;
    text-align: center; margin: 1.5rem 0 0;
}

.receipt-back {
    display: block; text-align: center; margin-top: 1.5rem;
    font-size: 0.85rem; color: var(--color-muted); text-decoration: none;
}
.receipt-back:hover { color: var(--rb-red); }

@media (max-width: 560px) {
    .receipt-line { flex-direction: column; gap: 0.15rem; }
    .receipt-line dd { text-align: left; }
}
</style>
@endsection
