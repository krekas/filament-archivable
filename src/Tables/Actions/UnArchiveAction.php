<?php

namespace Okeonline\FilamentArchivable\Tables\Actions;

use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;

class UnArchiveAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'unarchive';
    }

    protected function setUp(): void
    {
        parent::setUp();

        // $this->label(__('filament-actions::restore.single.label'));
        $this->label('Herstellen');

        $this->modalHeading(fn (): string => __('filament-actions::restore.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitActionLabel(__('filament-actions::restore.single.modal.actions.restore.label'));

        $this->successNotificationTitle(__('filament-actions::restore.single.notifications.restored.title'));

        $this->color('gray');

        $this->icon(FilamentIcon::resolve('actions::restore-action') ?? 'heroicon-m-arrow-uturn-left');

        $this->requiresConfirmation();

        $this->modalIcon(FilamentIcon::resolve('actions::restore-action.modal') ?? 'heroicon-o-arrow-uturn-left');

        $this->action(function (Model $record): void {
            if (! method_exists($record, 'unArchive')) {
                // @codeCoverageIgnoreStart
                $this->failure();

                return;
                // @codeCoverageIgnoreEnd
            }

            $result = $this->process(static fn () => $record->unArchive());

            if (! $result) {
                // @codeCoverageIgnoreStart
                $this->failure();

                return;
                // @codeCoverageIgnoreEnd
            }

            $this->success();
        });

        $this->visible(static function (Model $record): bool {
            if (! method_exists($record, 'isArchived')) {
                return false;
            }

            return $record->isArchived();
        });
    }
}
