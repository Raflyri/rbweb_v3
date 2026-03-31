<?php

namespace App\Filament\ClientArea\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;

/**
 * Custom registration page for the Client Area panel.
 *
 * Role assignment is handled cleanly by the AssignClientRoleOnRegister listener
 * (registered in AppServiceProvider), which fires on the Registered event that
 * Filament dispatches inside the parent handleRegistration() call.
 * No override of handleRegistration() is needed here.
 *
 * This class exists as a hook point for future customization of the
 * registration form fields (e.g., adding a "company name" field).
 */
class Register extends BaseRegister
{
    //
}
