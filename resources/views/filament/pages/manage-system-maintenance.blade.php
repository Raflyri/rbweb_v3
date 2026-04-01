<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Database Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon
                        icon="heroicon-o-circle-stack"
                        class="h-5 w-5 text-danger-500"
                    />
                    <span>Database Management</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Run critical database commands securely on the production server.
            </x-slot>

            <div class="flex flex-col sm:flex-row gap-4 mt-6">
                {{ $this->runMigrationsAction }}
                {{ $this->runDatabaseSeederAction }}
            </div>
            
            <div class="mt-4 text-sm text-gray-500">
                <strong>Important:</strong> Only click Migrations after updating the codebase. Seeders must be idempotent to prevent duplicates.
            </div>
        </x-filament::section>

        <!-- System Caches & Storage Section -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-filament::icon
                        icon="heroicon-o-cpu-chip"
                        class="h-5 w-5 text-info-500"
                    />
                    <span>System Caches & Storage</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Refresh your application's caches configuration and storage symlinks.
            </x-slot>

            <div class="flex flex-col sm:flex-row gap-4 mt-6">
                {{ $this->clearAllCachesAction }}
                {{ $this->storageLinkAction }}
            </div>
            
            <div class="mt-4 text-sm text-gray-500">
                <strong>Tip:</strong> If changes are not appearing, clear your optimization caches or re-link storage.
            </div>
        </x-filament::section>
        
    </div>
</x-filament-panels::page>
