<?php

namespace App\Rules;

use App\Services\RoleLimitService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RoleWithinLimit implements ValidationRule
{
    /**
     * Validasi bahwa role yang dipilih belum melampaui batas maksimum.
     * Gunakan rule ini pada form Filament saat admin menetapkan role ke user.
     *
     * Contoh penggunaan di form:
     *   Select::make('roles')
     *       ->rules([new RoleWithinLimit(ignoreUserId: $this->record?->id)])
     */
    public function __construct(
        protected ?int $ignoreUserId = null
    ) {}

    /**
     * @param  \Closure(string): never  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // $value bisa berupa string (nama role tunggal) atau array
        $roles = is_array($value) ? $value : [$value];

        foreach ($roles as $roleName) {
            $limit = RoleLimitService::$limits[$roleName] ?? null;

            if ($limit === null) {
                continue; // Tidak ada batas untuk role ini
            }

            $role = \Spatie\Permission\Models\Role::findByName($roleName, 'web');

            $currentCount = $role->users()
                ->when($this->ignoreUserId, fn ($q) => $q->where('id', '!=', $this->ignoreUserId))
                ->count();

            if ($currentCount >= $limit) {
                $fail(RoleLimitService::limitErrorMessage($roleName));
            }
        }
    }
}
