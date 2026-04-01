<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ManageSystemMaintenance extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-server';
    protected static string | \UnitEnum | null $navigationGroup = 'System';
    protected static ?string $title = 'System Maintenance';
    protected static ?string $slug = 'system-maintenance';
    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.manage-system-maintenance';

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        return $user && $user->hasRole('super_admin');
    }

    public function runMigrationsAction(): Action
    {
        return Action::make('runMigrations')
            ->label('Run Migrations')
            ->icon('heroicon-o-circle-stack')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Run Database Migrations')
            ->modalDescription('Are you absolutely sure? This will execute database schema migrations on the production server. This action CANNOT be undone.')
            ->modalSubmitActionLabel('Yes, run migrations')
            ->action(function () {
                try {
                    Artisan::call('migrate', ['--force' => true]);
                    $output = Artisan::output();
                    
                    Log::info('System Maintenance: Migrations executed via Admin Panel.', ['user' => \Illuminate\Support\Facades\Auth::id()]);
                    
                    Notification::make()
                        ->title('Migrations Completed Successfully')
                        ->body(nl2br(htmlspecialchars($output)))
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Log::error('System Maintenance Migration Error: ' . $e->getMessage());
                    Notification::make()
                        ->title('Migration Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function runDatabaseSeederAction(): Action
    {
        return Action::make('runDatabaseSeeder')
            ->label('Run Database Seeder')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Run Database Seeder')
            ->modalDescription('Are you sure? This will run the database seeders. Make sure all your seeders are fully idempotent (using firstOrCreate) to avoid duplicate data on production.')
            ->modalSubmitActionLabel('Yes, plant the seeds')
            ->action(function () {
                try {
                    Artisan::call('db:seed', ['--force' => true]);
                    $output = Artisan::output();
                    
                    Log::info('System Maintenance: Seeder executed via Admin Panel.', ['user' => \Illuminate\Support\Facades\Auth::id()]);
                    
                    Notification::make()
                        ->title('Database Seeded Successfully')
                        ->body(nl2br(htmlspecialchars($output)))
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Log::error('System Maintenance Seeder Error: ' . $e->getMessage());
                    Notification::make()
                        ->title('Seeder Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function clearAllCachesAction(): Action
    {
        return Action::make('clearAllCaches')
            ->label('Clear Optimization Caches')
            ->icon('heroicon-o-sparkles')
            ->color('gray')
            ->requiresConfirmation()
            ->modalHeading('Clear All Caches')
            ->modalDescription('This will run optimize:clear to reset view, config, route, and application caches. Safe to run anytime.')
            ->modalSubmitActionLabel('Yes, clear caches')
            ->action(function () {
                try {
                    Artisan::call('optimize:clear');
                    $output = Artisan::output();
                    
                    Log::info('System Maintenance: Cache cleared via Admin Panel.', ['user' => \Illuminate\Support\Facades\Auth::id()]);
                    
                    Notification::make()
                        ->title('Caches Cleared Successfully')
                        ->body(nl2br(htmlspecialchars($output)))
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Action Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    public function storageLinkAction(): Action
    {
        return Action::make('storageLink')
            ->label('Link Storage')
            ->icon('heroicon-o-folder-open')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Create Storage Symlink')
            ->modalDescription('This will run storage:link which makes public files accessible. Crucial for image uploads and media management.')
            ->modalSubmitActionLabel('Yes, link storage')
            ->action(function () {
                try {
                    Artisan::call('storage:link');
                    $output = Artisan::output();
                    
                    Notification::make()
                        ->title('Storage Linked Successfully')
                        ->body(nl2br(htmlspecialchars($output)))
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Action Failed')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
