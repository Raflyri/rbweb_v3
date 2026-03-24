<?php

namespace App\Filament\ClientArea\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                // ✅ user_id diisi otomatis dari session, tidak tampil di form
                Hidden::make('user_id')
                    ->default(fn () => Auth::id()),

                TextInput::make('title')
                    ->label('Judul Artikel')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, callable $set) {
                        if ($operation === 'create') {
                            $set('slug', Str::slug($state));
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('slug')
                    ->label('Slug URL')
                    ->required()
                    ->unique(Post::class, 'slug', ignoreRecord: true)
                    ->maxLength(255)
                    ->helperText('Diisi otomatis dari judul, bisa diubah manual.'),

                // ✅ Status: client hanya bisa memilih Draft atau Pending Review
                Select::make('status')
                    ->label('Status')
                    ->options([
                        Post::STATUS_DRAFT   => 'Draft',
                        Post::STATUS_PENDING => 'Pending Review',
                    ])
                    ->default(Post::STATUS_DRAFT) // ✅ Default: selalu Draft
                    ->required()
                    ->native(false),

                // ✅ RichEditor dengan dukungan upload gambar/file
                RichEditor::make('content')
                    ->label('Konten Artikel')
                    ->toolbarButtons([
                        'attachFiles',
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'underline',
                        'undo',
                    ])
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('posts/attachments')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
