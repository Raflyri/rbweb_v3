<?php

namespace App\Filament\Resources\Articles\Tables;

use App\Policies\ArticlePolicy;
use App\Support\ArticleContent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('thumbnail')
                    ->disk('public')
                    ->square()
                    ->defaultImageUrl(fn () => null)
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(60)
                    // An article may only exist in one non-English locale, so
                    // don't rely on the current locale + Spatie's fallback —
                    // show whichever translation actually exists.
                    ->formatStateUsing(fn ($record) => ArticleContent::bestTranslation($record->getTranslations('title')) ?? '(untitled)')
                    ->tooltip(fn ($record) => ArticleContent::bestTranslation($record->getTranslations('title')) ?? '(untitled)'),

                TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Published'      => 'success',
                        'Scheduled'      => 'info',
                        'Pending Review' => 'warning',
                        'Draft'          => 'gray',
                        default          => 'gray',
                    }),

                TextColumn::make('user.name')
                    ->label('Submitted By')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reviewer.name')
                    ->label('Reviewed By')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('reviewed_at')
                    ->label('Reviewed At')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Draft'          => 'Draft',
                        'Pending Review' => 'Pending Review',
                        'Scheduled'      => 'Scheduled',
                        'Published'      => 'Published',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),

                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Article?')
                    ->modalDescription('This will publish the article and notify the author.')
                    ->visible(fn ($record) => $record->status === 'Pending Review'
                        && ArticlePolicy::userCanPublish(auth()->user()))
                    ->action(function ($record) {
                        $record->update([
                            'status'       => 'Published',
                            'reviewer_id'  => auth()->id(),
                            'reviewed_at'  => now(),
                            'published_at' => now(),
                        ]);

                        if ($record->user) {
                            $record->user->notify(
                                new \App\Notifications\ArticleStatusChanged($record, 'approved')
                            );
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Article approved and published.')
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Article?')
                    ->modalDescription('This will revert the article back to Draft and notify the author.')
                    ->visible(fn ($record) => $record->status === 'Pending Review'
                        && ArticlePolicy::userCanPublish(auth()->user()))
                    ->action(function ($record) {
                        $record->update([
                            'status'      => 'Draft',
                            'reviewer_id' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);

                        if ($record->user) {
                            $record->user->notify(
                                new \App\Notifications\ArticleStatusChanged($record, 'rejected')
                            );
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Article rejected and returned to Draft.')
                            ->warning()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
