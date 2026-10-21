<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Support\ProductFields;
use App\Support\ArticleLocale;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class ProductForm
{
    /**
     * [locale => Tab label] — order also drives display order.
     *
     * Whatever languages the public switcher currently offers, taken from
     * ArticleLocale so there is one list to keep current rather than two.
     * Indonesian leads: these items are sold to a local market, so the
     * Indonesian copy is the one always written and the rest are optional.
     */
    protected static function localeTabs(): array
    {
        return collect(ArticleLocale::enabledLabels())
            ->map(fn (string $label, string $locale) => $label . ' (' . ArticleLocale::badge($locale) . ')')
            ->all();
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([

                // ── Translatable content ─────────────────────────────────
                Section::make('Nama & Deskripsi')
                    ->description('Nama Bahasa Indonesia wajib diisi. Bahasa lain opsional — kalau kosong, pengunjung yang memilih bahasa itu melihat versi Indonesia (lalu Inggris).')
                    ->columnSpan(2)
                    ->schema([
                        Tabs::make('Bahasa')
                            ->contained(false)
                            ->tabs(
                                collect(self::localeTabs())->map(
                                    fn (string $label, string $locale) => Tabs\Tab::make($label)
                                        ->schema([
                                            // Only the Indonesian name drives the slug, so only
                                            // it needs to be live — the other three would
                                            // otherwise fire a round-trip per keystroke for
                                            // nothing.
                                            $locale === 'id'
                                                ? ProductFields::nameField($locale, "Nama ({$label})")
                                                    ->live(debounce: 500)
                                                    ->afterStateUpdated(ProductFields::autoSlugFromName())
                                                    ->columnSpanFull()
                                                : ProductFields::nameField($locale, "Nama ({$label})")
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
