<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Filament\Schemas\Components\Utilities\Get;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Credentials (PII)')
                    ->description('Update your basic information and password.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->rule(Password::default())
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->same('passwordConfirmation'),
                        TextInput::make('passwordConfirmation')
                            ->password()
                            ->required()
                            ->visible(fn (Get $get) => filled($get('password')))
                            ->dehydrated(false),
                    ]),
                Section::make('Public Portfolio Settings')
                    ->description('Customize how you appear globally on the RBeverything ecosystem.')
                    ->schema([
                        TextInput::make('profile.headline')
                            ->label('Professional Headline')
                            ->placeholder('e.g. Senior Laravel Developer')
                            ->maxLength(255),
                        TextInput::make('profile.custom_url_slug')
                            ->label('Custom URL Slug')
                            ->prefix('rbeverything.com/@')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('profile.bio')
                            ->label('Biography')
                            ->placeholder('Tell us about your journey...')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        FileUpload::make('profile.avatar_url')
                            ->label('Avatar Image')
                            ->image()
                            ->directory('avatars'),
                    ])
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = auth()->user();
        $profile = $user->profile;
        
        if ($profile) {
            $data['profile'] = [
                'headline' => $profile->headline,
                'custom_url_slug' => $profile->custom_url_slug,
                'bio' => $profile->bio,
                'avatar_url' => $profile->avatar_url,
            ];
        } else {
            // Provide sensible defaults for the required Custom Slug
            $data['profile'] = [
                'custom_url_slug' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $user->name)) . '-' . rand(100, 999),
            ];
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Intercept profile data array
        $profileData = $data['profile'] ?? [];
        unset($data['profile']);

        // Save User Data (Name, Email, Password) using standard base mechanism
        parent::handleRecordUpdate($record, $data);

        // Update or Create the Profile entity associated with this User
        if ($record->profile) {
            $record->profile->update($profileData);
        } else {
            $record->profile()->create($profileData);
        }

        return $record;
    }
}
