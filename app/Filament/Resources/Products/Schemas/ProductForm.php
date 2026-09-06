<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\ProductFields;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ProductForm
{
    /**
     * [locale => Tab label] — order also drives display order.
     *
     * Indonesian first, the reverse of ArticleForm: these items are sold to a
     * local market, so the Indonesian copy is the one that is always written
     * and the English one is the optional extra.
     */
    protected const LOCALE_TABS = [
        'id' => 'Indonesia (ID)',
        'en' => 'English (EN)',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── Translatable content ─────────────────────────────────
                Section::make('Nama & Deskripsi')
                    ->description('Nama Bahasa Indonesia wajib diisi. Bahasa Inggris opsional — kalau kosong, pengunjung berbahasa Inggris melihat versi Indonesia.')
                    ->columnSpan(2)
                    ->schema([
                        Tabs::make('Bahasa')
                            ->contained(false)
                            ->tabs(
                                collect(self::LOCALE_TABS)->map(
                                    fn (string $label, string $locale) => Tabs\Tab::make($label)
                                        ->schema([
                                            ProductFields::nameField($locale, "Nama ({$label})")
                                                ->live(debounce: 500)
                                                ->afterStateUpdated(
                                                    $locale === 'id'
                                                        ? ProductFields::autoSlugFromName()
                                                        : fn () => null,
                                                )
                                                ->columnSpanFull(),
                                            ProductFields::shortDescriptionField($locale, "Deskripsi Singkat ({$label})")
                                                ->columnSpanFull(),
                                            ProductFields::descriptionField($locale, "Deskripsi Lengkap ({$label})")
                                                ->columnSpanFull(),
                                            ProductFields::metaTitleField($locale, "Meta Title ({$label})")
                                                ->columnSpanFull(),
                                            ProductFields::metaDescriptionField($locale, "Meta Description ({$label})")
                                                ->columnSpanFull(),
                                        ]),
                                )->values()->all(),
                            ),
                    ]),

                // ── Commercial detail ────────────────────────────────────
                Section::make('Jenis & Harga')
                    ->columnSpan(1)
                    ->schema([
                        ProductFields::slugField(),
                        ProductFields::typeField(),
                        ProductFields::priceField(),
                        ProductFields::currencyField(),
                        ProductFields::stockField(),
                    ]),

                // ── Visibility & media ───────────────────────────────────
                Section::make('Tampilan')
                    ->columnSpan(1)
                    ->schema([
                        ProductFields::thumbnailField(),
                        ProductFields::isActiveField(),
                        ProductFields::isFeaturedField(),
                        ProductFields::sortOrderField(),
                    ]),

            ]);
    }
}
