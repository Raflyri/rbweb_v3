<?php

namespace App\Filament\ClientArea\Resources\Profiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('custom_url_slug'),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('headline'),
                TextInput::make('avatar_url')
                    ->url(),
                Textarea::make('theme_preferences')
                    ->columnSpanFull(),
            ]);
    }
}
