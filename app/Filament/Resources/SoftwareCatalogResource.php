<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SoftwareCatalogResource\Pages;
use App\Models\SoftwareCatalog;
use App\Models\SoftwareCategory;
use App\Policies\ProductPolicy;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class SoftwareCatalogResource extends Resource
{
    protected static ?string $model = SoftwareCatalog::class;

    protected static ?string $slug = 'software-portal';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'Software Catalog';

    protected static ?string $modelLabel = 'Software Catalog';

    protected static ?string $pluralModelLabel = 'Software Catalog';

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 16;

    protected static ?string $recordTitleAttribute = 'title';

    public static function canViewAny(): bool
    {
        return ProductPolicy::userIsManager(auth()->user());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Utama Software')
                ->description('Data dasar perangkat lunak yang akan ditampilkan di katalog.')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Nama / Judul Software')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Sistem POS & ERP Toko Bangunan')
                        ->live(debounce: 500)
                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                            if (blank($get('slug'))) {
                                $set('slug', Str::slug($state ?? ''));
                            }
                        }),

                    TextInput::make('slug')
                        ->label('Slug URL')
                        ->required()
                        ->maxLength(255)
                        ->unique(SoftwareCatalog::class, 'slug', ignoreRecord: true),

                    Select::make('software_category_id')
                        ->label('Kategori Software (Dinamis)')
                        ->relationship('softwareCategory', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            TextInput::make('name')
                                ->label('Nama Kategori Baru')
                                ->required()
                                ->maxLength(255)
                                ->live(debounce: 500)
                                ->afterStateUpdated(function (Set $set, ?string $state) {
                                    $set('slug', Str::slug($state ?? ''));
                                }),
                            TextInput::make('slug')
                                ->label('Slug Kategori')
                                ->required()
                                ->maxLength(255)
                                ->unique(SoftwareCategory::class, 'slug'),
                            Textarea::make('description')
                                ->label('Deskripsi Kategori')
                                ->rows(2),
                        ])
                        ->createOptionAction(fn ($action) => $action->modalHeading('Tambah Kategori Software Baru'))
                        ->placeholder('Pilih atau buat kategori software baru...')
                        ->native(false),

                    TextInput::make('version')
                        ->label('Versi Rilis')
                        ->required()
                        ->maxLength(50)
                        ->placeholder('Contoh: 1.0, 2.0, atau 2.4-LTS')
                        ->default('1.0'),

                    DatePicker::make('release_date')
                        ->label('Tanggal Rilis Resmi')
                        ->native(false)
                        ->displayFormat('d F Y')
                        ->placeholder('Pilih tanggal rilis software...'),

                    TextInput::make('badge_text')
                        ->label('Teks Badge / Label Sorotan')
                        ->maxLength(100)
                        ->placeholder('Contoh: v2.0 Tested, Ready to Deploy, Anti-Cheating'),

                    Textarea::make('short_description')
                        ->label('Deskripsi Singkat (Ringkasan Kartu)')
                        ->rows(3)
                        ->required()
                        ->placeholder('Deskripsi singkat 1-2 kalimat untuk kartu preview katalog.')
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi Lengkap & Problem Solving')
                        ->rows(6)
                        ->placeholder('Penjelasan lengkap mengenai fitur, arsitektur, dan manfaat software...')
                        ->columnSpanFull(),
                ]),

            Section::make('Spesifikasi & Versi')
                ->columns(2)
                ->schema([
                    TagsInput::make('tech_stack')
                        ->label('Tech Stack (Tekan Enter)')
                        ->placeholder('Tambah teknologi (e.g. Next.js, Laravel, MySQL)')
                        ->suggestions([
                            'Next.js', 'React', 'TypeScript', 'Tailwind CSS', 'Prisma ORM',
                            'Laravel', 'Inertia.js', 'Vue.js', 'PHP', 'MySQL', 'PostgreSQL',
                            'Redis', 'Cloudflare Tunnel', 'Docker', 'Proxmox', 'KaTeX',
                        ]),

                    TagsInput::make('features')
                        ->label('Fitur-fitur Utama (Tekan Enter)')
                        ->placeholder('Tambah poin fitur unggulan...'),

                    Textarea::make('changelog')
                        ->label('Catatan Rilis / Changelog Versi Ini')
                        ->rows(4)
                        ->placeholder("Catatan perubahan versi (e.g. • Peningkatan performa render\n• Dukungan offline-first)")
                        ->columnSpanFull(),
                ]),

            Section::make('Akses Live Demo & Kredensial')
                ->description('Konfigurasi tautan live demo untuk dicoba langsung oleh pengunjung.')
                ->columns(2)
                ->schema([
                    TextInput::make('demo_url')
                        ->label('URL Live Demo')
                        ->url()
                        ->maxLength(500)
                        ->placeholder('https://digitalization-exam.rbeverything.com/')
                        ->columnSpanFull(),

                    Select::make('demo_type')
                        ->label('Tipe Demo')
                        ->options([
                            'direct'      => 'Direct Link (Langsung buka URL)',
                            'one-click'   => 'One-Click Auto-Login',
                            'credentials' => 'Kredensial Manual Terlampir',
                        ])
                        ->default('direct')
                        ->required()
                        ->native(false),

                    Textarea::make('demo_credentials')
                        ->label('Catatan Kredensial Demo')
                        ->rows(2)
                        ->placeholder('Contoh: Role Siswa: siswa@sekolah.com / Pass: password'),
                ]),

            Section::make('Aset Gambar & Publikasi')
                ->columns(2)
                ->schema([
                    FileUpload::make('thumbnail')
                        ->label('Thumbnail / Screenshot Utama')
                        ->image()
                        ->disk('public')
                        ->directory('software-thumbnails')
                        ->maxSize(3072)
                        ->columnSpanFull(),

                    Select::make('status')
                        ->label('Status Publikasi')
                        ->options([
                            SoftwareCatalog::STATUS_TAYANGKAN => 'Tayangkan',
                            SoftwareCatalog::STATUS_DRAFT     => 'Draft',
                        ])
                        ->default(SoftwareCatalog::STATUS_DRAFT)
                        ->required()
                        ->native(false),

                    TextInput::make('sort_order')
                        ->label('Urutan Tampil')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_featured')
                        ->label('Tampilkan sebagai Software Unggulan (Featured)')
                        ->helperText('Jika aktif, kartu software akan ditonjolkan dengan border emas/ruby khusus.')
                        ->default(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->square(),

                TextColumn::make('title')
                    ->label('Nama Software')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('softwareCategory.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('version')
                    ->label('Versi')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('release_date')
                    ->label('Tgl Rilis')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('demo_url')
                    ->label('Demo URL')
                    ->limit(25)
                    ->copyable()
                    ->placeholder('Belum ada URL'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => $state === SoftwareCatalog::STATUS_TAYANGKAN ? 'success' : 'gray'),

                TextColumn::make('sort_order')
                    ->label('Urutan')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('software_category_id')
                    ->label('Kategori')
                    ->relationship('softwareCategory', 'name'),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        SoftwareCatalog::STATUS_TAYANGKAN => 'Tayangkan',
                        SoftwareCatalog::STATUS_DRAFT     => 'Draft',
                    ]),
            ])
            ->recordActions([
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
            'index'  => Pages\ListSoftwareCatalogs::route('/'),
            'create' => Pages\CreateSoftwareCatalog::route('/create'),
            'edit'   => Pages\EditSoftwareCatalog::route('/{record}/edit'),
        ];
    }
}
