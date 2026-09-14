@extends('layouts.public')

@php
    use App\Support\OrderStatus;
    use App\Support\PaymentStatus;
    use App\Support\ProductType;

    $waText = __('receipt_ui.wa_inquiry_message', [
        'number' => $order->order_number,
        'product' => $order->product_name_snapshot,
    ]);
    $waMessage = rawurlencode($waText);
@endphp

@section('meta_title', __('receipt_ui.meta_title', ['number' => $order->order_number]))
{{-- Somebody else's order details must never end up in a search index. --}}
@section('meta_robots', 'noindex, nofollow')

@section('content')

    <section aria-label="{{ __('receipt_ui.section_label') }}">
        <div class="rb-section" style="padding-bottom:5rem;max-width:52rem;">

            <div class="receipt-hero">
                <div class="receipt-check" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5"/>
                    </svg>
                </div>
                <h1 class="receipt-title">{{ __('receipt_ui.received_title') }}</h1>
                <p class="receipt-lead">
                    {{ $order->needsShipping() ? __('receipt_ui.received_lead_goods') : __('receipt_ui.received_lead_services') }}
                </p>

                <div class="receipt-number">
                    <span>{{ __('receipt_ui.order_number') }}</span>
                    <strong>{{ $order->order_number }}</strong>
                </div>
            </div>

            <div class="receipt-card">

                <div class="receipt-statuses">
                    <div class="receipt-status">
                        <span class="receipt-status__label">{{ __('receipt_ui.order_status') }}</span>
                        <span class="receipt-badge receipt-badge--{{ $order->status }}">
                            {{ OrderStatus::label($order->status) }}
                        </span>
                    </div>
                    <div class="receipt-status">
                        <span class="receipt-status__label">{{ __('receipt_ui.payment_status') }}</span>
                        <span class="receipt-badge receipt-badge--payment">
                            {{ PaymentStatus::label($order->payment_status) }}
                        </span>
                    </div>
                </div>

                @php
                    $orderItems = $order->allItems();
                @endphp

                @if($orderItems->count() > 1)
                    <div class="receipt-items-wrap">
                        <h3 class="receipt-block__title">Rincian Produk Dipesan</h3>
                        <div class="receipt-items-table">
                            @foreach($orderItems as $item)
                                <div class="receipt-item-row">
                                    <div class="receipt-item-info">
                                        <strong>{{ $item->product_name_snapshot }}</strong>
                                        <span class="receipt-type">{{ ProductType::label($item->product_type_snapshot) }}</span>
                                    </div>
                                    <div class="receipt-item-qty">&times; {{ $item->qty }}</div>
                                    <div class="receipt-item-subtotal">{{ $item->formattedSubtotal() }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <dl class="receipt-lines">
                    @if($orderItems->count() <= 1)
                        <div class="receipt-line">
                            <dt>{{ __('receipt_ui.product') }}</dt>
                            <dd>
                                {{ $order->product_name_snapshot }}
                                <span class="receipt-type">{{ ProductType::label($order->product_type_snapshot) }}</span>
                            </dd>
                        </div>
                        <div class="receipt-line">
                            <dt>{{ __('receipt_ui.qty') }}</dt>
                            <dd>{{ $order->qty }}</dd>
                        </div>
                    @endif
                    <div class="receipt-line">
                        <dt>{{ __('receipt_ui.subtotal') }}</dt>
                        <dd>{{ $order->formattedSubtotal() }}</dd>
                    </div>
                    @if($order->needsShipping())
                        <div class="receipt-line">
                            <dt>{{ __('receipt_ui.shipping_fee') }}</dt>
                            <dd>{{ $order->formattedShipping() ?? __('receipt_ui.not_calculated') }}</dd>
                        </div>
                    @endif
                    <div class="receipt-line receipt-line--total">
                        <dt>{{ $order->needsShipping() && $order->shipping_cost === null ? __('receipt_ui.total_provisional') : __('receipt_ui.total') }}</dt>
                        <dd>{{ $order->formattedTotal() }}</dd>
                    </div>
                </dl>

                <div class="receipt-block">
                    <h2 class="receipt-block__title">{{ __('receipt_ui.customer_title') }}</h2>
                    <dl class="receipt-lines receipt-lines--plain">
                        <div class="receipt-line"><dt>{{ __('receipt_ui.name') }}</dt><dd>{{ $order->customer_name }}</dd></div>
                        <div class="receipt-line"><dt>{{ __('receipt_ui.email') }}</dt><dd>{{ $order->customer_email }}</dd></div>
                        <div class="receipt-line"><dt>{{ __('receipt_ui.phone') }}</dt><dd>{{ $order->customer_phone }}</dd></div>
                        @if($order->shipping_address)
                            <div class="receipt-line"><dt>{{ __('receipt_ui.address') }}</dt><dd>{{ $order->shipping_address }}</dd></div>
                        @endif
                        @if($order->preferred_date)
                            <div class="receipt-line"><dt>{{ __('receipt_ui.date') }}</dt><dd>{{ $order->preferred_date->format('d/m/Y') }}</dd></div>
                        @endif
                        @if($order->notes)
                            <div class="receipt-line"><dt>{{ __('receipt_ui.notes') }}</dt><dd>{{ $order->notes }}</dd></div>
                        @endif
                    </dl>
                </div>

                <div class="receipt-payment">
                    <h2 class="receipt-block__title">{{ __('receipt_ui.payment_heading', ['name' => $payment['name']]) }}</h2>

                    @if(session('payment_success'))
                        <div class="receipt-alert receipt-alert--ok" role="status">{{ session('payment_success') }}</div>
                    @endif
                    @if(session('payment_error'))
                        <div class="receipt-alert receipt-alert--warn" role="alert">{{ session('payment_error') }}</div>
                    @endif
                    @if($order->payment_note && ! $order->isPaid())
                        {{-- An admin sent the last receipt back; the reason is the
                             most useful thing on this page right now. --}}
                        <div class="receipt-alert receipt-alert--warn" role="alert">
                            <strong>{{ __('receipt_ui.proof_rejected_title') }}</strong><br>
                            {{ $order->payment_note }}
                        </div>
                    @endif

                    @if($order->isPaid())
                        <div class="receipt-paid-box">
                            <div class="receipt-paid-check" aria-hidden="true">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5"/>
                                </svg>
                            </div>
                            <h3 class="receipt-paid-title">Pembayaran Berhasil & Terverifikasi!</h3>
                            <p class="receipt-paid-desc">
                                @php
                                    $paidDateStr = $order->paid_at ? __('receipt_ui.paid_on', ['date' => $order->paid_at->format('d/m/Y H:i')]) : '';
                                @endphp
                                {!! __('receipt_ui.paid_message', ['date' => $paidDateStr]) !!}
                            </p>
                            <p style="font-size:0.875rem; color:var(--color-muted); margin:0;">
                                Tim kami segera memproses pesanan Anda. Pemberitahuan akan dikirimkan ke <strong>{{ $order->customer_email }}</strong>.
                            </p>
                        </div>
                    @elseif($order->isCancelled())
                        <p>{{ __('receipt_ui.cancelled_message') }}</p>
                    @elseif($payment['type'] === 'unavailable' || $payment['type'] === 'channel_selection')
                        <p style="margin-bottom:1rem;">
                            Silakan pilih metode pembayaran untuk menyelesaikan pesanan sebesar <strong>{{ $payment['formatted_amount'] ?? $order->formattedTotal() }}</strong>:
                        </p>

                        <div class="coreapi-channels">
                            @foreach($paymentMethods as $channelId => $channel)
                                <form method="POST" action="{{ route('order.midtrans.charge', $order->public_token) }}" class="coreapi-channel-form">
                                    @csrf
                                    <input type="hidden" name="channel" value="{{ $channelId }}">
                                    <button type="submit" class="coreapi-channel-card">
                                        <div class="coreapi-channel-header">
                                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                                @if(($channel['category'] ?? '') === 'qris')
                                                    <span>📱</span>
                                                @elseif(in_array(($channel['category'] ?? ''), ['va', 'bill'], true))
                                                    <span>🏦</span>
                                                @else
                                                    <span>💳</span>
                                                @endif
                                                <span class="coreapi-channel-name">{{ $channel['name'] }}</span>
                                            </div>
                                            @if(!empty($channel['badge']))
                                                <span class="coreapi-badge">{{ $channel['badge'] }}</span>
                                            @endif
                                        </div>
                                        <div class="coreapi-channel-desc">{{ $channel['description'] }}</div>
                                        <div class="coreapi-channel-action">
                                            <span>Pilih metode ini &rarr;</span>
                                        </div>
                                    </button>
                                </form>
                            @endforeach
                        </div>

                    @elseif($payment['type'] === 'core_api')
                        @php
                            $channel = $payment['channel'];
                            $payload = $payment['payload'];
                        @endphp

                        <div class="coreapi-active-payment">
                            <div class="coreapi-active-header">
                                <div>
                                    <span class="coreapi-method-subtitle">Metode Pembayaran</span>
                                    <h3 class="coreapi-method-title">{{ $payment['channel_info']['name'] ?? ucfirst($channel) }}</h3>
                                </div>
                                <form method="POST" action="{{ route('order.midtrans.reset', $order->public_token) }}">
                                    @csrf
                                    <button type="submit" class="coreapi-btn-change" title="Ganti Metode Pembayaran">
                                        Ganti Metode
                                    </button>
                                </form>
                            </div>

                            @if($channel === 'qris')
                                <div class="coreapi-qris-container">
                                    @if(!empty($payload['qr_url']))
                                        <div class="coreapi-qr-wrapper">
                                            <img src="{{ $payload['qr_url'] }}" alt="QRIS Code" class="coreapi-qr-image">
                                        </div>
                                        <div class="coreapi-qr-actions">
                                            <a href="{{ $payload['qr_url'] }}" download="QRIS-{{ $order->order_number }}.png" target="_blank" class="coreapi-btn-small">
                                                ⬇️ Simpan / Buka QR Code
                                            </a>
                                        </div>
                                    @endif

                                    <div class="pay-account" style="margin-top:1.25rem;">
                                        <div class="pay-account__row pay-account__row--amount">
                                            <span>Total yang Harus Dibayar</span>
                                            <strong>{{ $payment['formatted_amount'] }}</strong>
                                        </div>
                                        @if(!empty($payload['expiry_time']))
                                            <div class="pay-account__row">
                                                <span>Batas Waktu Bayar</span>
                                                <strong style="color:#FBBF24;">{{ \Carbon\Carbon::parse($payload['expiry_time'])->format('d/m/Y H:i') }} WIB</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                            @elseif(in_array($channel, ['bca_va', 'bni_va', 'bri_va', 'permata_va'], true))
                                <div class="pay-account">
                                    <div class="pay-account__row">
                                        <span>Bank</span>
                                        <strong>{{ strtoupper($payload['bank'] ?? str_replace('_va', '', $channel)) }}</strong>
                                    </div>
                                    <div class="pay-account__row">
                                        <span>Nomor Virtual Account</span>
                                        <div style="display:flex; align-items:center; gap:0.5rem;">
                                            <strong class="pay-account__number">{{ $payload['va_number'] ?? '-' }}</strong>
                                            <button type="button" class="coreapi-copy-btn" onclick="copyToClipboard('{{ $payload['va_number'] ?? '' }}', this)">Salin</button>
                                        </div>
                                    </div>
                                    <div class="pay-account__row pay-account__row--amount">
                                        <span>Total Transfer</span>
                                        <div style="display:flex; align-items:center; gap:0.5rem;">
                                            <strong>{{ $payment['formatted_amount'] }}</strong>
                                            <button type="button" class="coreapi-copy-btn" onclick="copyToClipboard('{{ (int) round($payment['amount']) }}', this)">Salin</button>
                                        </div>
                                    </div>
                                    @if(!empty($payload['expiry_time']))
                                        <div class="pay-account__row">
                                            <span>Batas Pembayaran</span>
                                            <strong style="color:#FBBF24;">{{ \Carbon\Carbon::parse($payload['expiry_time'])->format('d/m/Y H:i') }} WIB</strong>
                                        </div>
                                    @endif
                                </div>

                            @elseif($channel === 'mandiri_bill')
                                <div class="pay-account">
                                    <div class="pay-account__row">
                                        <span>Kode Perusahaan (Biller)</span>
                                        <div style="display:flex; align-items:center; gap:0.5rem;">
                                            <strong class="pay-account__number">{{ $payload['biller_code'] ?? '70012' }}</strong>
                                            <button type="button" class="coreapi-copy-btn" onclick="copyToClipboard('{{ $payload['biller_code'] ?? '70012' }}', this)">Salin</button>
                                        </div>
                                    </div>
                                    <div class="pay-account__row">
                                        <span>Nomor Tagihan (Bill Key)</span>
                                        <div style="display:flex; align-items:center; gap:0.5rem;">
                                            <strong class="pay-account__number">{{ $payload['bill_key'] ?? '-' }}</strong>
                                            <button type="button" class="coreapi-copy-btn" onclick="copyToClipboard('{{ $payload['bill_key'] ?? '' }}', this)">Salin</button>
                                        </div>
                                    </div>
                                    <div class="pay-account__row pay-account__row--amount">
                                        <span>Total Tagihan</span>
                                        <div style="display:flex; align-items:center; gap:0.5rem;">
                                            <strong>{{ $payment['formatted_amount'] }}</strong>
                                            <button type="button" class="coreapi-copy-btn" onclick="copyToClipboard('{{ (int) round($payment['amount']) }}', this)">Salin</button>
                                        </div>
                                    </div>
                                    @if(!empty($payload['expiry_time']))
                                        <div class="pay-account__row">
                                            <span>Batas Pembayaran</span>
                                            <strong style="color:#FBBF24;">{{ \Carbon\Carbon::parse($payload['expiry_time'])->format('d/m/Y H:i') }} WIB</strong>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <div class="coreapi-instructions">
                                <h4 class="coreapi-instructions-title">Petunjuk Pembayaran:</h4>
                                <ol class="pay-steps">
                                    @foreach($payment['instructions'] as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            </div>

                            <div class="receipt-actions" style="margin-top:1.25rem; margin-bottom:0.75rem;">
                                <a href="{{ route('order.status', $order->public_token) }}" class="rb-btn-primary receipt-btn" id="btn-check-status">
                                    🔄 Cek Status Pembayaran
                                </a>
                            </div>

                            <p class="pay-uploaded" id="status-poll-note">
                                Status pembayaran Anda akan dicek otomatis. Halaman ini akan memuat ulang secara otomatis begitu pembayaran lunas.
                            </p>
                        </div>

                    @elseif($payment['type'] === 'redirect')
                        <p>
                            {!! __('receipt_ui.redirect_lead', ['amount' => $payment['formatted_amount'], 'name' => $payment['name']]) !!}
                        </p>

                        <div class="receipt-actions" style="margin-bottom:1.25rem;">
                            <a href="{{ $payment['url'] }}" class="rb-btn-primary receipt-btn" rel="noopener">
                                {{ __('receipt_ui.pay_now') }}
                            </a>
                        </div>

                        <p class="pay-uploaded">
                            {{ __('receipt_ui.auto_update_note') }}
                        </p>
                    @elseif($payment['type'] === 'manual_transfer' && $payment['configured'])

                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                            <span style="font-size:0.85rem; color:var(--color-muted);">Metode Pembayaran: <strong>Transfer Bank Manual</strong></span>
                            <form method="POST" action="{{ route('order.midtrans.reset', $order->public_token) }}">
                                @csrf
                                <button type="submit" class="coreapi-btn-change" title="Ganti Metode Pembayaran">
                                    Ganti Metode
                                </button>
                            </form>
                        </div>

                        <div class="pay-account">
                            <div class="pay-account__row">
                                <span>{{ __('receipt_ui.bank_name') }}</span>
                                <strong>{{ $payment['account']['bank_name'] }}</strong>
                            </div>
                            <div class="pay-account__row">
                                <span>{{ __('receipt_ui.account_number') }}</span>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <strong class="pay-account__number">{{ $payment['account']['account_number'] }}</strong>
                                    <button type="button" class="coreapi-copy-btn" onclick="copyToClipboard('{{ $payment['account']['account_number'] }}', this)">Salin</button>
                                </div>
                            </div>
                            <div class="pay-account__row">
                                <span>{{ __('receipt_ui.account_holder') }}</span>
                                <strong>{{ $payment['account']['account_holder'] }}</strong>
                            </div>
                            <div class="pay-account__row pay-account__row--amount">
                                <span>{{ __('receipt_ui.transfer_amount') }}</span>
                                <div style="display:flex; align-items:center; gap:0.5rem;">
                                    <strong>{{ $payment['formatted_amount'] }}</strong>
                                    <button type="button" class="coreapi-copy-btn" onclick="copyToClipboard('{{ (int) round($payment['amount']) }}', this)">Salin</button>
                                </div>
                            </div>
                        </div>

                        <ol class="pay-steps">
                            @foreach($payment['instructions'] as $step)
                                <li>{{ $step }}</li>
                            @endforeach
                        </ol>

                        @if($order->needsShipping() && $order->shipping_cost === null)
                            <p class="pay-warning">
                                {{ __('receipt_ui.shipping_cost_warning') }}
                            </p>
                        @endif

                        {{-- ── Upload bukti transfer ───────────────────── --}}
                        <form method="POST" action="{{ route('order.proof.upload', $order->public_token) }}"
                              enctype="multipart/form-data" class="pay-upload">
                            @csrf

                            @error('proof')
                                <div class="receipt-alert receipt-alert--warn" role="alert">{{ $message }}</div>
                            @enderror

                            <label for="proof">{{ __('receipt_ui.upload_proof_label') }}</label>
                            <input type="file" id="proof" name="proof" required
                                   accept=".jpg,.jpeg,.png,.webp,.pdf">
                            <small>{{ __('receipt_ui.file_hint') }}</small>

                            <button type="submit" class="rb-btn-primary receipt-btn">
                                @if($order->payment_proof) {{ __('receipt_ui.replace_proof_button') }} @else {{ __('receipt_ui.upload_button') }} @endif
                            </button>
                        </form>

                        @if($order->payment_proof)
                            <p class="pay-uploaded">
                                {{ __('receipt_ui.proof_submitted_notice', ['email' => $order->customer_email]) }}
                            </p>
                        @endif

                    @else
                        {{-- No manual account configured or fallback: offer other channels --}}
                        <p style="margin-bottom:1rem;">
                            Silakan pilih metode pembayaran yang tersedia di bawah ini untuk menyelesaikan pesanan sebesar <strong>{{ $payment['formatted_amount'] ?? $order->formattedTotal() }}</strong>:
                        </p>

                        <div class="coreapi-channels">
                            @foreach($paymentMethods as $channelId => $channel)
                                <form method="POST" action="{{ route('order.midtrans.charge', $order->public_token) }}" class="coreapi-channel-form">
                                    @csrf
                                    <input type="hidden" name="channel" value="{{ $channelId }}">
                                    <button type="submit" class="coreapi-channel-card">
                                        <div class="coreapi-channel-header">
                                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                                @if(($channel['category'] ?? '') === 'qris')
                                                    <span>📱</span>
                                                @elseif(in_array(($channel['category'] ?? ''), ['va', 'bill'], true))
                                                    <span>🏦</span>
                                                @else
                                                    <span>💳</span>
                                                @endif
                                                <span class="coreapi-channel-name">{{ $channel['name'] }}</span>
                                            </div>
                                            @if(!empty($channel['badge']))
                                                <span class="coreapi-badge">{{ $channel['badge'] }}</span>
                                            @endif
                                        </div>
                                        <div class="coreapi-channel-desc">{{ $channel['description'] }}</div>
                                        <div class="coreapi-channel-action">
                                            <span>Pilih metode ini &rarr;</span>
                                        </div>
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    @endif

                    <div class="receipt-actions">
                        @if($contact['whatsapp'])
                            <a href="{{ $contact['whatsapp'] }}?text={{ $waMessage }}"
                               class="rb-btn-primary receipt-btn" target="_blank" rel="noopener">
                                {{ __('receipt_ui.ask_order_wa') }}
                            </a>
                        @endif
                        <a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode(__('receipt_ui.email_subject', ['number' => $order->order_number])) }}"
                           class="rb-btn-ghost receipt-btn">
                            {{ __('receipt_ui.ask_order_email') }}
                        </a>
                    </div>
                </div>
            </div>

            <p class="receipt-footnote">
                {{ __('receipt_ui.footnote') }}
            </p>

            <a href="{{ route('products.index') }}" class="receipt-back">&larr; {{ __('receipt_ui.back_to_catalog') }}</a>
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

.receipt-items-wrap {
    margin-bottom: 1.25rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--color-border);
}
.receipt-items-table {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.receipt-item-row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 1rem;
    font-size: 0.875rem;
}
.receipt-item-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex: 1;
}
.receipt-item-info strong { color: #F5F5F5; }
.receipt-item-qty { color: var(--color-muted); font-size: 0.85rem; }
.receipt-item-subtotal { font-weight: 700; color: #F5F5F5; }

.receipt-paid-box {
    text-align: center;
    padding: 2rem 1.5rem;
    border-radius: 1rem;
    background: rgba(52, 211, 153, 0.06);
    border: 1px solid rgba(52, 211, 153, 0.3);
    margin-bottom: 1.5rem;
}
.receipt-paid-check {
    width: 3.5rem; height: 3.5rem; margin: 0 auto 1rem;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    background: rgba(52, 211, 153, 0.15);
    border: 1px solid rgba(52, 211, 153, 0.4);
}
.receipt-paid-title {
    font-size: 1.35rem; font-weight: 900; color: #34D399; margin: 0 0 0.5rem;
}
.receipt-paid-desc {
    font-size: 0.95rem; color: #F5F5F5; line-height: 1.6; margin: 0 0 0.75rem !important;
}

/* ── Payment ────────────────────────────────────────── */
.receipt-alert {
    padding: 0.85rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.85rem;
    line-height: 1.6;
    margin-bottom: 1.25rem;
}
.receipt-alert--ok {
    border: 1px solid rgba(52,211,153,0.28);
    background: rgba(52,211,153,0.07);
    color: #6EE7B7;
}
.receipt-alert--warn {
    border: 1px solid rgba(251,191,36,0.28);
    background: rgba(251,191,36,0.07);
    color: #FBBF24;
}

.pay-account {
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
    padding: 1.25rem;
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    background: rgba(255,255,255,0.03);
    margin-bottom: 1.25rem;
}
.pay-account__row {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    gap: 1rem;
    font-size: 0.85rem;
    color: var(--color-muted);
}
.pay-account__row strong { color: #F5F5F5; font-size: 0.95rem; text-align: right; }
.pay-account__number { font-family: var(--font-mono, monospace); letter-spacing: 0.05em; font-size: 1.05rem !important; }
.pay-account__row--amount {
    padding-top: 0.7rem;
    border-top: 1px solid var(--color-border);
}
.pay-account__row--amount strong { font-size: 1.25rem !important; font-weight: 900; }

.pay-steps {
    margin: 0 0 1.25rem 1.1rem;
    padding: 0;
    font-size: 0.875rem;
    color: var(--color-muted);
    line-height: 1.75;
}
.pay-steps li { margin-bottom: 0.35rem; }

.pay-warning {
    font-size: 0.82rem !important;
    color: #FBBF24 !important;
    margin-bottom: 1.25rem !important;
}

.pay-upload {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1.25rem;
    border: 1px dashed var(--color-border);
    border-radius: 1rem;
    margin-bottom: 1.25rem;
}
.pay-upload label { font-size: 0.82rem; font-weight: 700; color: var(--color-text); }
.pay-upload input[type="file"] {
    font-size: 0.82rem;
    color: var(--color-muted);
    padding: 0.5rem 0;
}
.pay-upload small { font-size: 0.75rem; color: var(--color-muted); }
.pay-upload button { margin-top: 0.5rem; width: fit-content; cursor: pointer; }

.pay-uploaded {
    font-size: 0.82rem !important;
    color: var(--color-muted) !important;
    margin-bottom: 1.25rem !important;
}

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

/* ── Midtrans Core API Custom Styles ── */
.coreapi-channels {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 0.85rem;
    margin-bottom: 1.5rem;
}
.coreapi-channel-form { margin: 0; }
.coreapi-channel-card {
    width: 100%;
    text-align: left;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid var(--color-border);
    border-radius: 0.85rem;
    padding: 1rem 1.15rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}
.coreapi-channel-card:hover {
    border-color: var(--rb-red);
    background: rgba(220, 38, 38, 0.05);
    transform: translateY(-2px);
}
.coreapi-channel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
}
.coreapi-channel-name {
    font-weight: 700;
    color: #F5F5F5;
    font-size: 0.95rem;
}
.coreapi-badge {
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: rgba(52, 211, 153, 0.12);
    border: 1px solid rgba(52, 211, 153, 0.3);
    color: #34D399;
}
.coreapi-channel-desc {
    font-size: 0.78rem;
    color: var(--color-muted);
    line-height: 1.4;
}
.coreapi-channel-action {
    margin-top: 0.3rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--rb-red);
}

.coreapi-active-payment {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--color-border);
    border-radius: 1rem;
    padding: 1.25rem;
    margin-bottom: 1.5rem;
}
.coreapi-active-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--color-border);
    padding-bottom: 0.85rem;
    margin-bottom: 1.25rem;
}
.coreapi-method-subtitle {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-muted);
}
.coreapi-method-title {
    font-size: 1.15rem;
    color: #F5F5F5;
    margin: 0.15rem 0 0;
    font-weight: 800;
}
.coreapi-btn-change {
    background: none;
    border: 1px solid var(--color-border);
    color: var(--color-muted);
    font-size: 0.75rem;
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}
.coreapi-btn-change:hover {
    color: #F5F5F5;
    border-color: var(--rb-red);
}

.coreapi-qris-container {
    text-align: center;
}
.coreapi-qr-wrapper {
    background: #FFFFFF;
    padding: 1rem;
    border-radius: 0.85rem;
    display: inline-block;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}
.coreapi-qr-image {
    width: 220px;
    height: 220px;
    display: block;
}
.coreapi-qr-actions {
    margin-top: 0.75rem;
}
.coreapi-btn-small {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    font-weight: 600;
    color: #38BDF8;
    text-decoration: none;
}
.coreapi-btn-small:hover { text-decoration: underline; }

.coreapi-copy-btn {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid var(--color-border);
    color: #F5F5F5;
    font-size: 0.75rem;
    padding: 0.25rem 0.6rem;
    border-radius: 0.4rem;
    cursor: pointer;
    transition: all 0.2s;
}
.coreapi-copy-btn:hover {
    background: var(--rb-red);
    border-color: var(--rb-red);
    color: #FFFFFF;
}

.coreapi-instructions {
    margin-top: 1.25rem;
    border-top: 1px solid var(--color-border);
    padding-top: 1rem;
}
.coreapi-instructions-title {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #F5F5F5;
    margin: 0 0 0.75rem;
}
</style>

<script>
function copyToClipboard(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(function() {
        const original = btn.innerText;
        btn.innerText = 'Tersalin!';
        btn.style.background = '#34D399';
        btn.style.color = '#000000';
        setTimeout(function() {
            btn.innerText = original;
            btn.style.background = '';
            btn.style.color = '';
        }, 2000);
    });
}

// Live polling for payment confirmation (every 7 seconds)
@if(! $order->isPaid() && ! $order->isCancelled() && ($payment['type'] ?? '') === 'core_api')
(function() {
    const statusUrl = "{{ route('order.status', $order->public_token) }}";
    let pollInterval = setInterval(function() {
        fetch(statusUrl, {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data && data.paid === true) {
                clearInterval(pollInterval);
                const note = document.getElementById('status-poll-note');
                if (note) {
                    note.innerText = '✅ Pembayaran terverifikasi! Memuat ulang halaman...';
                    note.style.color = '#34D399';
                }
                setTimeout(function() {
                    window.location.reload();
                }, 1200);
            }
        })
        .catch(function(err) {
            console.log('Status polling paused:', err);
        });
    }, 7000);
})();
@endif
</script>
@endsection
