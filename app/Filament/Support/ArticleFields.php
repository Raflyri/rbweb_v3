<?php

namespace App\Filament\Support;

use App\Support\ArticleContent;
use App\Support\ArticleLocale;
use App\Support\ArticleType;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * Single source of truth for every Article form field: labels, limits,
 * WYSIWYG toolbar, and the (?) hint tooltips explaining what each field does
 * and whether it's required. Both the Admin ArticleForm and the Client Area
 * ClientArticleResource build their fields from here, so the two panels stay
 * behaviourally identical (same limits, same validation, same guidance) even
 * though each renders its own distinct layout around them.
 */
class ArticleFields
{
    protected const HINT_ICON = 'heroicon-o-question-mark-circle';

    /**
     * A full WYSIWYG toolbar — headings, formatting, lists, media, tables,
     * undo/redo — used identically in both panels so "what the editor can
     * do" never differs by which door the author walked through.
     */
    public const RICH_EDITOR_TOOLBAR = [
        'h2', 'h3', 'bold', 'italic', 'underline', 'strike',
        'bulletList', 'orderedList', 'blockquote', 'codeBlock',
        'link', 'attachFiles', 'table', 'redo', 'undo',
    ];

    public static function titleField(string $locale, string $label): TextInput
    {
        return TextInput::make("title.{$locale}")
            ->label($label)
            ->maxLength(255)
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — judul artikel untuk bahasa ini. Tidak wajib diisi di setiap bahasa; cukup satu bahasa manapun agar artikel bisa dipublikasikan.',
            );
    }

    public static function contentField(string $locale, string $label): RichEditor
    {
        return RichEditor::make("content.{$locale}")
            ->label($label)
            ->toolbarButtons(self::RICH_EDITOR_TOOLBAR)
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsDirectory('article-attachments')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional per bahasa — isi lengkap artikel (rich text). Minimal satu bahasa harus berisi ±50 kata sebelum artikel bisa berstatus Published.',
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
                'Opsional — judul khusus untuk hasil pencarian Google & saat dibagikan ke media sosial. Kosongkan untuk otomatis memakai judul artikel. Disarankan maks. 60 karakter.',
            );
    }

    public static function metaDescriptionField(string $locale, string $label): Textarea
    {
        return Textarea::make("meta_description.{$locale}")
            ->label($label)
            ->rows(3)
            // A soft ceiling, not the SEO-ideal number: 160 is the length
            // recommendation shown to the author, but blocking submission
            // at exactly 160 is what produced the "Jumlah karakter" error —
            // real writing in Indonesian/Malay routinely needs a bit more
            // room to say the same thing English says in 160 characters.
            ->maxLength(320)
            ->live(debounce: 500)
            ->helperText(fn ($state) => mb_strlen(is_string($state) ? $state : '') . ' / 160 disarankan (maks. 320)')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — ringkasan untuk hasil pencarian Google & pratinjau media sosial. Kosongkan untuk dibuat otomatis dari isi artikel. Disarankan 120–160 karakter.',
            );
    }

    public static function typeField(): Select
    {
        return Select::make('type')
            ->label('Tipe Konten')
            ->options(ArticleType::options())
            ->default(ArticleType::DEFAULT)
            ->required()
            ->native(false)
            ->hintIcon(
                self::HINT_ICON,
                'Wajib — pemisah kategori konten: Blog (tulisan santai/personal), Article (panduan/teknis), atau News (berita/pengumuman). Menentukan badge yang tampil di /blog.',
            );
    }

    public static function slugField(): TextInput
    {
        return TextInput::make('slug')
            ->label('Slug')
            ->required()
            ->unique(ignoreRecord: true)
            // 'slug' is stored as one JSON value per locale (Spatie
            // Translatable), but every locale shares the identical value
            // (see Article::boot()). This is a single flat TextInput, not
            // slug.{locale} — without formatStateUsing, Filament hands it
            // the raw per-locale array when editing an existing record, and
            // it renders as "[object Object]" instead of the slug text.
            // dehydrateStateUsing rebuilds that array on save so every
            // locale keeps resolving to the same slug.
            ->formatStateUsing(fn (mixed $state) => is_array($state)
                ? (ArticleContent::bestTranslation($state) ?? '')
                : $state)
            ->dehydrateStateUsing(fn (?string $state) => array_fill_keys(ArticleLocale::SUPPORTED, $state))
            ->hintIcon(
                self::HINT_ICON,
                'Wajib — bagian akhir URL artikel (contoh: rbeverything.com/blog/slug-ini). Terisi otomatis dari judul bahasa manapun yang pertama kamu tulis; boleh diedit manual.',
            );
    }

    public static function publishedAtField(): DateTimePicker
    {
        return DateTimePicker::make('published_at')
            ->label('Tanggal Publikasi')
            ->native(false)
            ->displayFormat('d/m/Y H:i')
            ->hintIcon(
                self::HINT_ICON,
                'Opsional — tanggal & waktu yang ditampilkan ke publik. Boleh diatur mundur (backdate) untuk artikel lama, atau ke masa depan untuk dijadwalkan otomatis. Kosongkan untuk memakai waktu saat disimpan.',
            );
    }

    public static function thumbnailField(): FileUpload
    {
        return FileUpload::make('thumbnail')
            ->label('Thumbnail')
            ->image()
            ->disk('public')
            // One directory for both panels — previously Admin saved to
            // article-thumbnails/ and Client Area to articles/thumbnails/,
            // splitting the same kind of file across two folders depending
            // on who uploaded it.
            ->directory('article-thumbnails')
            ->imageEditor()
            ->imageEditorAspectRatioOptions(['16:9' => '16:9 (Landscape)'])
            ->maxSize(2048)
            ->hintIcon(
                self::HINT_ICON,
                'Wajib sebelum artikel bisa Published (boleh kosong sementara masih Draft). Rasio 16:9 disarankan, maksimal 2 MB.',
            );
    }

    /**
     * Fill the slug from whichever locale's title the author writes first.
     * Only acts while the slug is still blank, so it never fights the
     * author once they have typed or customised it themselves.
     */
    public static function autoSlugFromTitle(): \Closure
    {
        return function (Set $set, Get $get, ?string $state) {
            if (blank($get('slug'))) {
                $set('slug', Str::slug($state ?? ''));
            }
        };
    }
}
