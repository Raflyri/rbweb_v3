<?php

namespace App\Filament\Pages;

use App\Settings\MailSettings;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Admin UI for editing the email verification template.
 *
 * Accessible by: super_admin and admin.
 * Settings are stored in the `settings` table (group = 'mail') via spatie/laravel-settings.
 *
 * Supported placeholders in subject and body:
 *   {name}  → recipient's display name
 *   {app}   → config('app.name')
 */
class ManageMailSettings extends SettingsPage
{
    protected static string $settings = MailSettings::class;

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-envelope-open';
    protected static ?string $navigationLabel = 'Email Templates';
    protected static string|\UnitEnum|null $navigationGroup  = 'System';
    protected static ?int    $navigationSort  = 98;

    // ── Authorization ─────────────────────────────────────────────────────────
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testEmail')
                ->label('Test Kirim Email')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->modalHeading('Kirim Email Uji Coba')
                ->modalDescription('Kirimkan email uji coba untuk memverifikasi apakah konfigurasi SMTP / server email sudah dapat mengirimkan pesan dengan sukses.')
                ->modalSubmitActionLabel('Kirim Sekarang')
                ->schema([
                    TextInput::make('recipient_email')
                        ->label('Alamat Email Tujuan')
                        ->email()
                        ->required()
                        ->default(fn () => Auth::user()?->email)
                        ->placeholder('nama@domain.com')
                        ->helperText('Secara default menggunakan email akun Anda saat ini.'),
                ])
                ->action(function (array $data) {
                    $recipient = trim($data['recipient_email']);
                    $appName = config('app.name', 'RB Everything');
                    $subject = "Uji Coba Pengiriman Email — {$appName}";
                    $body = "Halo!\n\nIni adalah pesan email uji coba dari sistem {$appName}.\n\nJika Anda menerima pesan ini, artinya konfigurasi SMTP server email Anda sudah terpasang dengan benar dan siap mengirimkan notifikasi kepada pengguna dan pelanggan.\n\nWaktu pengiriman: " . now()->format('d/m/Y H:i:s') . "\n\nSalam hangat,\nTim {$appName}";

                    try {
                        Mail::raw($body, function ($message) use ($recipient, $subject) {
                            $message->to($recipient)
                                ->subject($subject);
                        });

                        Notification::make()
                            ->title('Email Berhasil Dikirim!')
                            ->body("Email uji coba telah berhasil dikirim ke {$recipient}. Silakan cek kotak masuk (inbox/spam) Anda.")
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Test email sending failed', [
                            'recipient' => $recipient,
                            'error'     => $e->getMessage(),
                        ]);

                        \App\Models\EmailLog::create([
                            'to_email'      => $recipient,
                            'subject'       => $subject,
                            'status'        => 'failed',
                            'error_message' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Gagal Mengirim Email')
                            ->body('Terjadi kesalahan saat menghubungi server email: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('viewLogs')
                ->label('Lihat Riwayat Email')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(fn (): string => \App\Filament\Resources\EmailLogResource::getUrl('index')),
        ];
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([

            // ── Verification Email ─────────────────────────────────────────
            Section::make('📧  Verification Email Template')
                ->description('Customise the email sent to new users when they need to verify their address. The link expires in 5 minutes.')
                ->icon('heroicon-o-envelope')
                ->schema([

                    Placeholder::make('placeholder_hint')
                        ->label('Available Placeholders')
                        ->content('Use {name} for the recipient\'s name and {app} for the site name.')
                        ->columnSpanFull(),

                    TextInput::make('verification_subject')
                        ->label('Subject Line')
                        ->placeholder('Verifikasi Alamat Email Anda — {app}')
                        ->helperText('Shown in the email inbox. Supports {name} and {app}.')
                        ->required()
                        ->maxLength(150)
                        ->columnSpanFull(),

                    Textarea::make('verification_body')
                        ->label('Email Body')
                        ->placeholder("Halo {name},\n\nTerima kasih telah mendaftar di {app}...")
                        ->helperText(
                            'Plain text only (no HTML). Use a blank line to start a new paragraph. ' .
                            'Supports {name} and {app}. The verification button is appended automatically.'
                        )
                        ->required()
                        ->rows(8)
                        ->columnSpanFull(),

                    TextInput::make('verification_action_label')
                        ->label('Button Label')
                        ->placeholder('Verifikasi Email Sekarang')
                        ->helperText('The text on the call-to-action button inside the email.')
                        ->required()
                        ->maxLength(80)
                        ->columnSpanFull(),

                ]),

        ]);
    }
}
