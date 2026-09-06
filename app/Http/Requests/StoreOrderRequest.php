<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Support\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validation for the public order form.
 *
 * A FormRequest rather than inline rules because the shape depends on what is
 * being ordered: goods need somewhere to ship to, services do not.
 */
class StoreOrderRequest extends FormRequest
{
    /** The form is public — anyone may submit it. */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name'    => ['required', 'string', 'max:120'],
            'customer_email'   => ['required', 'email:rfc', 'max:190'],
            // Loose on format, strict on length: Indonesian numbers get written
            // as 0812…, +62 812…, and (0812) 3456-7890, and rejecting any of
            // those spellings costs a real order to satisfy a regex.
            'customer_phone'   => ['required', 'string', 'min:8', 'max:40'],
            'qty'              => ['required', 'integer', 'min:1', 'max:999'],
            'shipping_address' => [$this->orderedProduct()?->isBarang() ? 'required' : 'nullable', 'string', 'max:1000'],
            'preferred_date'   => ['nullable', 'date', 'after_or_equal:today'],
            'notes'            => ['nullable', 'string', 'max:2000'],

            // Honeypot. Real people never see this field, so anything in it is
            // a bot filling every input on the page.
            'website'          => ['prohibited'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name'    => 'nama',
            'customer_email'   => 'email',
            'customer_phone'   => 'nomor WhatsApp',
            'qty'              => 'jumlah',
            'shipping_address' => 'alamat pengiriman',
            'preferred_date'   => 'tanggal yang diinginkan',
            'notes'            => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address.required' => 'Alamat pengiriman wajib diisi untuk produk berupa barang.',
            'preferred_date.after_or_equal' => 'Tanggal yang diinginkan tidak boleh di masa lalu.',
            'website.prohibited'        => 'Pengiriman formulir ditolak.',
        ];
    }

    /**
     * Stock is checked here rather than in the controller so a sold-out item
     * reports itself as a form error next to the quantity field instead of
     * bouncing the buyer to a generic error page.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $product = $this->orderedProduct();

            if (! $product || ! $product->tracksStock()) {
                return;
            }

            if ((int) $this->input('qty') > (int) $product->stock) {
                $validator->errors()->add(
                    'qty',
                    $product->stock > 0
                        ? "Stok tersisa hanya {$product->stock}."
                        : 'Stok produk ini sedang habis.',
                );
            }
        });
    }

    protected function orderedProduct(): ?Product
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product : null;
    }
}
