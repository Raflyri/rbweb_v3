<?php

namespace App\Filament\Support;

use App\Support\ProductType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Single source of truth for every Product form field: labels, limits, and the
 * (?) hint tooltips explaining what each field does and whether it is required.
 *
 * Same idea as ArticleFields — one place to change a limit or a helper text —
 * with two deliberate differences from the article editor:
 *
 *  1. Indonesian leads the locale tabs. Products are sold locally; the
 *     Indonesian copy is the one that always gets written.
 *  2. The slug is a plain string field, not a per-locale JSON blob. One
 *     product, one URL (see the comment on Product::getSlugOptions).
 */
class ProductFields
{
    protected const HINT_ICON = 'heroicon-o-question-mark-circle';

    /**
     * A trimmed version of the article toolbar: everything a product
     * description realistically needs, and nothing the 'product' purifier
     * profile would strip back out again.
     */
    public const RICH_EDITOR_TOOLBAR = [
        'h2', 'h3', 'bold', 'italic', 'underline', 'strike',
        'bulletList', 'orderedList', 'blockquote',
        'link', 'attachFiles', 'table', 'redo', 'undo',
    ];

    public static function nameField(string $locale, string $label): TextInput
    {
        return TextInput::make("name.{$locale}")
            ->label($label)
            ->maxLength(255)
            ->required($locale === 'id')
            ->hintIcon(
                self::HINT_ICON,
                $locale === 'id'
                    ? 'Wajib — nama produk/layanan dalam Bahasa Indonesia. Dipakai untuk membuat slug URL.'
                    : 'Opsional — nama untuk bahasa ini. Kalau kosong, pengunjung yang memilih bahasa ini akan melihat nama Bahasa Indonesia (lalu Inggris).',
            );
    }

    public static function shortDescriptionField(string $locale, string $label): Textarea
    {
        return Textarea::make("short_description.{$locale}")
            ->label($label)
            ->rows(3)
            ->maxLength(300)
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — ringkasan 1–2 kalimat yang tampil di kartu daftar produk. Kalau kosong, kartu hanya menampilkan nama dan harga.',
            );
    }

    public static function descriptionField(string $locale, string $label): RichEditor
    {
        return RichEditor::make("description.{$locale}")
            ->label($label)
            ->toolbarButtons(self::RICH_EDITOR_TOOLBAR)
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory('product-attachments')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional per bahasa — penjelasan lengkap yang tampil di halaman detail produk. Isinya dibersihkan otomatis dari HTML berbahaya saat disimpan.',
            );
    }

    public static function metaTitleField(string $locale, string $label): TextInput
    {
        return TextInput::make("meta_title.{$locale}")
            ->label($label)
            ->maxLength(70)
            ->live(debounce: 500)
            ->helperText(fn ($state) => mb_strlen(is_string($state) ? $state : '') . ' / 60 disarankan')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — judul khusus untuk hasil pencarian Google & saat dibagikan ke media sosial. Kosongkan untuk otomatis memakai nama produk.',
            );
    }

    public static function metaDescriptionField(string $locale, string $label): Textarea
    {
        return Textarea::make("meta_description.{$locale}")
            ->label($label)
            ->rows(3)
            ->maxLength(320)
            ->live(debounce: 500)
            ->helperText(fn ($state) => mb_strlen(is_string($state) ? $state : '') . ' / 160 disarankan (maks. 320)')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — ringkasan untuk hasil pencarian Google. Kosongkan untuk dibuat otomatis dari deskripsi singkat.',
            );
    }

    public static function slugField(): TextInput
    {
        return TextInput::make('slug')
            ->label('Slug')
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true)
            ->helperText('Bagian akhir URL: rbeverything.com/produk-layanan/slug-ini')
            ->hintIcon(
                self::HINT_ICON,
                'Wajib — terisi otomatis dari nama Bahasa Indonesia saat produk dibuat. Setelah produk tayang, mengubah slug akan memutus semua tautan lama ke produk ini.',
            );
    }

    public static function typeField(): Select
    {
        return Select::make('type')
            ->label('Jenis')
            ->options(ProductType::options())
            ->default(ProductType::DEFAULT)
            ->required()
            ->native(false)
            ->live()
            ->hintIcon(
                self::HINT_ICON,
                'Wajib — "Barang" untuk produk fisik yang perlu dikirim (pembeli mengisi alamat saat memesan), "Jasa" untuk layanan yang tidak perlu pengiriman.',
            );
    }

    public static function priceField(): TextInput
    {
        return TextInput::make('price')
            ->label('Harga')
            ->numeric()
            ->minValue(0)
            ->maxValue(9999999999)
            ->prefix('Rp')
            ->helperText("Kosongkan untuk menampilkan tombol 'Hubungi Kami' alih-alih harga.")
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — isi angka saja tanpa titik/koma (contoh: 250000). Produk tanpa harga tidak bisa dipesan online; pengunjung diarahkan menghubungi kamu langsung.',
            );
    }

    public static function stockField(): TextInput
    {
        return TextInput::make('stock')
            ->label('Stok')
            ->numeric()
            ->minValue(0)
            ->maxValue(4294967295)
            // Stock only means anything for physical goods; a service has no
            // shelf to run out of.
            ->visible(fn (Get $get): bool => $get('type') === ProductType::BARANG)
            ->helperText('Kosongkan kalau stok tidak dilacak.')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — hanya untuk Barang. Kosong berarti stok tidak dilacak sama sekali. Isi 0 berarti dilacak dan sedang habis.',
            );
    }

    public static function currencyField(): TextInput
    {
        return TextInput::make('currency')
            ->label('Mata Uang')
            ->default('IDR')
            ->maxLength(3)
            ->required()
            ->hintIcon(
                self::HINT_ICON,
                'Wajib — kode mata uang 3 huruf. Biarkan IDR kecuali produk ini memang dijual dalam mata uang lain.',
            );
    }

    public static function sortOrderField(): TextInput
    {
        return TextInput::make('sort_order')
            ->label('Urutan')
            ->numeric()
            ->default(0)
            ->minValue(0)
            ->maxValue(65535)
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — angka kecil tampil lebih dulu di halaman publik. Produk "Unggulan" tetap naik ke atas apa pun urutannya.',
            );
    }

    public static function isActiveField(): Toggle
    {
        return Toggle::make('is_active')
            ->label('Tayangkan')
            ->default(true)
            ->hintIcon(
                self::HINT_ICON,
                'Kalau dimatikan, produk hilang dari halaman publik dan URL-nya jadi 404 untuk pengunjung biasa (admin yang sedang login tetap bisa melihat untuk pratinjau).',
            );
    }

    public static function isFeaturedField(): Toggle
    {
        return Toggle::make('is_featured')
            ->label('Unggulan')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — produk unggulan tampil paling atas di daftar /produk-layanan.',
            );
    }

    public static function thumbnailField(): FileUpload
    {
        return FileUpload::make('thumbnail')
            ->label('Gambar Utama')
            ->image()
            ->disk('public')
            ->directory('product-thumbnails')
            ->imageEditor()
            ->imageEditorAspectRatioOptions(['1:1' => '1:1 (Persegi)', '4:3' => '4:3 (Landscape)'])
            ->maxSize(2048)
            ->hintIcon(
                self::HINT_ICON,
                'Opsional tapi sangat disarankan — kartu produk tanpa gambar tampil dengan placeholder. Rasio 1:1 atau 4:3, maksimal 2 MB.',
            );
    }

    /**
     * Fill the slug from the Indonesian name while it is still blank.
     *
     * Only acts on an empty slug, so it never fights an admin who has already
     * customised one — and Product::getSlugOptions() does not regenerate on
     * update, so an existing product's URL stays put when its name changes.
     */
    public static function autoSlugFromName(): \Closure
    {
        return function (Set $set, Get $get, ?string $state) {
            if (blank($get('slug'))) {
                $set('slug', Str::slug($state ?? ''));
            }
        };
    }
}
