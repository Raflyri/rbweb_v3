<?php

namespace App\Filament\ClientArea\Resources\Posts;

use App\Filament\ClientArea\Resources\Posts\Pages\CreatePost;
use App\Filament\ClientArea\Resources\Posts\Pages\EditPost;
use App\Filament\ClientArea\Resources\Posts\Pages\ListPosts;
use App\Filament\ClientArea\Resources\Posts\Pages\ViewPost;
use App\Filament\ClientArea\Resources\Posts\Schemas\PostForm;
use App\Filament\ClientArea\Resources\Posts\Schemas\PostInfolist;
use App\Filament\ClientArea\Resources\Posts\Tables\PostsTable;
use App\Models\Post;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'My Articles';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PostInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'view'   => ViewPost::route('/{record}'),
            'edit'   => EditPost::route('/{record}/edit'),
        ];
    }

    /**
     * ✅ ISOLASI DATA: Hanya tampilkan post milik user yang sedang login.
     * Ini mencegah user A mengakses data user B meski tahu ID-nya.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }
}
