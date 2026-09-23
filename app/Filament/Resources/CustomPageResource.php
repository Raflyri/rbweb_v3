<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomPageResource\Pages;
use App\Models\CustomPage;
use App\Policies\CustomPagePolicy;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomPageResource extends Resource
{
    protected static ?string $model = CustomPage::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Halaman Kustom';

    protected static ?string $modelLabel = 'Halaman';

    protected static ?string $pluralModelLabel = 'Halaman Kustom';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canViewAny(): bool
    {
        return CustomPagePolicy::userIsManager(auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Utama')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Judul Halaman')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Validasi Dokumen Kerjasama')
                        ->columnSpan(1),

                    TextInput::make('path')
                        ->label('URL / Path')
                        ->required()
                        ->unique(CustomPage::class, 'path', ignoreRecord: true)
                        ->prefix(url('/') . '/')
                        ->placeholder('contoh: signed/rb atau info/kontrak-2026')
                        ->helperText('Hanya gunakan huruf kecil, angka, garis miring (/), atau strip (-).')
                        ->columnSpan(1),

                    Select::make('template')
                        ->label('Format Tampilan')
                        ->options([
                            'verification' => 'Verifikasi / Validasi Dokumen (Tampilan Kartu Tengah)',
                            'standard'     => 'Standar Dokumen (Halaman Penuh / Teks Lengkap)',
                        ])
                        ->default('verification')
                        ->required()
                        ->columnSpan(1),

                    Toggle::make('is_active')
                        ->label('Status Tayang (Aktif)')
                        ->helperText('Jika dinonaktifkan, URL ini akan otomatis mengembalikan error 404 (Tidak Ditemukan).')
                        ->default(true)
                        ->columnSpan(1),
                ]),

            Section::make('Isi Konten Body')
                ->schema([
                    RichEditor::make('content')
                        ->label('Konten Teks')
                        ->required()
                        ->toolbarButtons([
                            'h2', 'h3', 'bold', 'italic', 'underline', 'strike',
                            'bulletList', 'orderedList', 'blockquote', 'link', 'undo', 'redo',
                        ])
                        ->columnSpanFull(),
                ]),

            Section::make('Pengaturan SEO & Mesin Pencari (Opsional)')
                ->collapsible()
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta Title')
                        ->placeholder('Biarkan kosong untuk memakai judul halaman')
                        ->columnSpan(1),

                    Select::make('meta_robots')
                        ->label('Indexing Robot')
                        ->options([
                            'noindex, nofollow' => 'noindex, nofollow (Privat - jangan muncul di Google)',
                            'index, follow'     => 'index, follow (Publik - boleh terindeks mesin pencari)',
                        ])
                        ->default('noindex, nofollow')
                        ->required()
                        ->columnSpan(1),

                    Textarea::make('meta_description')
                        ->label('Meta Description')
                        ->rows(2)
                        ->placeholder('Deskripsi ringkas untuk preview mesin pencari atau share link')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('path')
                    ->label('URL Path')
                    ->formatStateUsing(fn (string $state): string => '/' . $state)
                    ->copyable()
                    ->copyMessage('URL berhasil disalin!')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('template')
                    ->label('Template')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'verification' => 'Verifikasi',
                        'standard'     => 'Standar',
                        default        => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'verification' => 'info',
                        'standard'     => 'gray',
                        default        => 'gray',
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                SelectFilter::make('template')
                    ->label('Tipe Template')
                    ->options([
                        'verification' => 'Verifikasi',
                        'standard'     => 'Standar',
                    ]),
            ])
            ->recordActions([
                Action::make('visit')
                    ->label('Buka')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (CustomPage $record): string => url($record->path))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomPages::route('/'),
            'create' => Pages\CreateCustomPage::route('/create'),
            'edit'   => Pages\EditCustomPage::route('/{record}/edit'),
        ];
    }
}
