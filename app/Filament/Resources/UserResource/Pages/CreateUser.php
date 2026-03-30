<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * After the record is created, auto-verify the email.
     * Users created in the admin panel are trusted —
     * they should be able to log in immediately without email verification.
     */
    protected function afterCreate(): void
    {
        $this->record->update([
            'email_verified_at' => now(),
        ]);
    }
}
