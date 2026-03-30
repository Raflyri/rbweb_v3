<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Models\UserChangeRequest;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Override the save behaviour.
     *
     * Super Admin  → saves directly to the users table.
     * Admin        → intercepts the save, creates a pending change request instead,
     *                and navigates back to the list with a notification.
     */
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        /** @var \App\Models\User $actor */
        $actor = Auth::user();

        // Super Admin: standard Filament save
        if ($actor->hasRole('super_admin')) {
            parent::save($shouldRedirect, $shouldSendSavedNotification);
            return;
        }

        // Admin: validate form data first (Filament's built-in validation)
        $data = $this->form->getState();

        // Check for an already-pending update request for this user
        $alreadyPending = UserChangeRequest::where('target_user_id', $this->record->id)
            ->where('action', 'update')
            ->where('status', 'pending')
            ->exists();

        if ($alreadyPending) {
            Notification::make()
                ->title('Request already pending')
                ->body('An update request for this user is already awaiting Super Admin approval.')
                ->warning()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        // Build the payload — only include fields that have actually changed
        /** @var User $target */
        $target  = $this->record;
        $payload = [];

        if (isset($data['name']) && $data['name'] !== $target->name) {
            $payload['name'] = $data['name'];
        }
        if (isset($data['email']) && $data['email'] !== $target->email) {
            $payload['email'] = $data['email'];
        }
        if (! empty($data['password'])) {
            // Password is already hashed by dehydrateStateUsing in the form
            $payload['password'] = $data['password'];
        }
        if (isset($data['roles'])) {
            // $data['roles'] is an array of role IDs from the Select component
            $payload['roles'] = $data['roles'];
        }

        if (empty($payload)) {
            Notification::make()
                ->title('No changes detected')
                ->body('Nothing was changed, so no request was submitted.')
                ->info()
                ->send();

            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }

        UserChangeRequest::create([
            'requested_by'   => $actor->id,
            'target_user_id' => $target->id,
            'action'         => 'update',
            'payload'        => $payload,
            'status'         => 'pending',
        ]);

        Notification::make()
            ->title('Update request submitted')
            ->body('Your changes have been sent to the Super Admin for approval.')
            ->success()
            ->send();

        $this->redirect($this->getResource()::getUrl('index'));
    }
}
