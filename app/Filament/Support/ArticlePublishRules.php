<?php

namespace App\Filament\Support;

use App\Models\Article;
use App\Policies\ArticlePolicy;
use App\Support\ArticleContent;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Publish guardrails shared by the admin ArticleForm and the Client Area
 * ClientArticleResource, so an article cannot go live half-finished from
 * either panel.
 *
 * Hard blockers (thin content, missing thumbnail) fail validation.
 * Soft warnings (missing excerpt / meta description) only notify — those are
 * back-filled by App\Observers\ArticleObserver on save.
 */
class ArticlePublishRules
{
    /** Statuses that make an article publicly reachable. */
    public const GUARDED_STATUSES = ArticlePolicy::PUBLIC_STATUSES;

    /**
     * Status options the current user is allowed to pick.
     *
     * A client only ever sees Draft and Pending Review. The record's existing
     * status is always included so that opening an already-published article
     * does not blank the field and silently downgrade it on save.
     *
     * @return array<string, string>
     */
    public static function statusOptions(?Article $record = null): array
    {
        $all = [
            'Draft'          => 'Draft',
            'Pending Review' => 'Pending Review',
            'Scheduled'      => 'Scheduled',
            'Published'      => 'Published',
        ];

        if (ArticlePolicy::userCanPublish(Auth::user())) {
            return $all;
        }

        $options = [
            'Draft'          => 'Draft',
            'Pending Review' => 'Pending Review',
        ];

        if ($record?->status !== null && ! isset($options[$record->status])) {
            $options[$record->status] = $record->status . ' (dikunci — hanya admin yang bisa mengubah)';
        }

        return $options;
    }

    /**
     * Validation rules for the `status` Select.
     *
     * @return array<int, Closure>
     */
    public static function rules(): array
    {
        return [
            fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                if (! in_array($value, static::GUARDED_STATUSES, true)) {
                    return;
                }

                if (! ArticlePolicy::userCanPublish(Auth::user())) {
                    $fail('Anda tidak berwenang menerbitkan artikel. Pilih "Pending Review" agar ditinjau admin terlebih dahulu.');

                    return;
                }

                foreach (ArticleContent::publishBlockers(static::stateFrom($get)) as $message) {
                    $fail($message);
                }
            },
        ];
    }

    /**
     * Give immediate feedback the moment the author picks a live status,
     * instead of making them hit Save to discover the article is not ready.
     */
    public static function notifyOnStatusChange(Get $get, mixed $state): void
    {
        if (! in_array($state, static::GUARDED_STATUSES, true)) {
            return;
        }

        if (! ArticlePolicy::userCanPublish(Auth::user())) {
            Notification::make()
                ->title('Tidak berwenang menerbitkan')
                ->body('Artikel harus melewati review admin. Pilih "Pending Review".')
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        $formState = static::stateFrom($get);

        if ($blockers = ArticleContent::publishBlockers($formState)) {
            Notification::make()
                ->title('Belum bisa dipublikasikan')
                ->body(implode(' ', $blockers))
                ->danger()
                ->persistent()
                ->send();

            return;
        }

        if ($warnings = ArticleContent::publishWarnings($formState)) {
            Notification::make()
                ->title('Siap terbit, dengan catatan')
                ->body(implode(' ', $warnings))
                ->warning()
                ->send();
        }
    }

    /** Inline hint under the status field describing what still blocks publishing. */
    public static function helperText(Get $get): string
    {
        $formState = static::stateFrom($get);

        if ($blockers = ArticleContent::publishBlockers($formState)) {
            return 'Belum siap terbit — ' . implode(' ', $blockers);
        }

        if ($warnings = ArticleContent::publishWarnings($formState)) {
            return implode(' ', $warnings);
        }

        return 'Artikel memenuhi syarat untuk dipublikasikan.';
    }

    /**
     * Pull the fields the guardrails care about out of the live form state.
     *
     * @return array<string, mixed>
     */
    protected static function stateFrom(Get $get): array
    {
        return [
            'title'            => $get('title'),
            'content'          => $get('content'),
            'thumbnail'        => $get('thumbnail'),
            'excerpt'          => $get('excerpt'),
            'meta_description' => $get('meta_description'),
        ];
    }
}
