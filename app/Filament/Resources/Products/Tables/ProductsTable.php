<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Support\ProductType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Gambar')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn () => null)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->limit(50)
                    // Show whichever translation actually exists rather than
                    // trusting the panel's locale — a product may only have
                    // Indonesian copy, and an empty cell looks like a bug.
                    ->formatStateUsing(fn (Product $record) => $record->translate('name', 'id') ?: '(tanpa nama)')
                    ->tooltip(fn (Product $record) => $record->translate('name', 'id') ?: null),

                TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => ProductType::label($state))
                    ->color(fn (?string $state) => ProductType::color($state)),

                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn (Product $record) => $record->formattedPrice() ?? 'Hubungi Kami')
                    ->color(fn (Product $record) => $record->hasPrice() ? null : 'gray')
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->placeholder('tidak dilacak')
                    ->badge()
                    ->color(fn (?int $state) => $state === 0 ? 'danger' : 'gray')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Tayang')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Unggulan')
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Jenis')
                    ->options(ProductType::options()),

                TernaryFilter::make('is_active')
                    ->label('Status tayang')
                    ->placeholder('Semua')
                    ->trueLabel('Tayang')
                    ->falseLabel('Disembunyikan'),

                TernaryFilter::make('is_featured')
                    ->label('Unggulan')
                    ->placeholder('Semua')
                    ->trueLabel('Unggulan saja')
                    ->falseLabel('Bukan unggulan'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
}
