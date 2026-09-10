<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for the buyer's transfer receipt.
 *
 * The endpoint is reached with the order's secret token rather than a login,
 * so authorisation is the URL itself; what matters here is that the file is
 * something a person can actually look at and is not large enough to fill the
 * disk.
 */
class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proof' => [
                'required',
                'file',
                // Photos of a transfer slip, screenshots, or a PDF receipt from
                // a banking app — nothing executable.
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:4096',
            ],
        ];
    }

    public function attributes(): array
    {
        return ['proof' => 'bukti transfer'];
    }

    public function messages(): array
    {
        return [
            'proof.required' => 'Pilih dulu berkas bukti transfer yang mau diunggah.',
            'proof.mimes'    => 'Bukti transfer harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'proof.max'      => 'Ukuran berkas maksimal 4 MB.',
        ];
    }
}
