<?php

use Okeonline\FilamentArchivable\FilamentArchivable;
use Okeonline\FilamentArchivable\FilamentArchivablePlugin;
use Okeonline\FilamentArchivable\Tables\Actions\ArchiveAction;
use Okeonline\FilamentArchivable\Tables\Actions\UnArchiveAction;
use Okeonline\FilamentArchivable\Tables\Filters\ArchivedFilter;
use Okeonline\FilamentArchivable\Tests\TestModels\ModelWithArchivableTrait;
use Okeonline\FilamentArchivable\Tests\TestModels\ModelWithoutArchivableTrait;
use Okeonline\FilamentArchivable\Tests\TestResources\ModelWithArchivableTraitAndCustomClassesResource;
use Okeonline\FilamentArchivable\Tests\TestResources\ModelWithArchivableTraitAndFalseCustomClassesResource;
use Okeonline\FilamentArchivable\Tests\TestResources\ModelWithArchivableTraitResource;
use Okeonline\FilamentArchivable\Tests\TestResources\ModelWithoutArchivableTraitResource;

use function Pest\Livewire\livewire;

it('can test', function () {
    expect(true)->toBeTrue();
});

it('is a valid plugin', function () {
    $plugin = new FilamentArchivablePlugin();

    expect($plugin->getId())
        ->toBe('archivable');

    expect(FilamentArchivablePlugin::make())
        ->toBeInstanceOf(FilamentArchivablePlugin::class);

});

it('registers archivedRecordClasses macro to Table', function () {
    // cant find a good way to test macros
})->todo();

it('shows items when Archivable-trait is used, unfiltered, so only the unarchived items', function () {
    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);
    $modelWithoutArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => null]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithoutArchivedAt)
        ->assertCanNotSeeTableRecords($modelWithArchivedAt)
        ->assertCountTableRecords(1);
});

it('shows all models when Archivable-trait is not used', function () {
    $modelWithArchivedAt = ModelWithoutArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);
    $modelWithoutArchivedAt = ModelWithoutArchivableTrait::factory()->count(1)->create(['archived_at' => null]);
    $both = $modelWithArchivedAt->merge($modelWithoutArchivedAt);

    livewire(ModelWithoutArchivableTraitResource\Pages\ListPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($both)
        ->assertCountTableRecords(2);
});

it('does not show (un)ArchivedActions when Archivable-trait is not used', function () {
    $modelWithArchivedAt = ModelWithoutArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);
    $modelWithoutArchivedAt = ModelWithoutArchivableTrait::factory()->count(1)->create(['archived_at' => null]);
    $both = $modelWithArchivedAt->merge($modelWithoutArchivedAt);

    livewire(ModelWithoutArchivableTraitResource\Pages\ListPage::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($both)
        ->assertCountTableRecords(2)
        ->assertTableActionDoesNotExist(UnArchiveAction::class, record: $modelWithArchivedAt->nth(1))
        ->assertTableActionDoesNotExist(ArchiveAction::class, record: $modelWithArchivedAt->nth(2));
});

// filters
it('filters rows based on ArchiveFilter = blank', function () {

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);
    $modelWithoutArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => null]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, null)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithoutArchivedAt)
        ->assertCanNotSeeTableRecords($modelWithArchivedAt)
        ->assertCountTableRecords(1);

});

it('filters rows based on ArchiveFilter = true', function () {

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);
    $modelWithoutArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => null]);
    $both = $modelWithArchivedAt->merge($modelWithoutArchivedAt);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($both)
        ->assertCountTableRecords(2);

});

it('filters rows based on ArchiveFilter = false', function () {

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);
    $modelWithoutArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => null]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, false)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithArchivedAt)
        ->assertCanNotSeeTableRecords($modelWithoutArchivedAt)
        ->assertCountTableRecords(1);

});

// actions
it('shows row-action archive, only on unarchived rows', function () {

    $modelWithoutArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => null]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithoutArchivedAt)
        ->assertCountTableRecords(1)
        ->assertTableActionExists(ArchiveAction::class, record: $modelWithoutArchivedAt->first())
        ->assertTableActionDoesNotExist(ArchiveAction::class, record: $modelWithoutArchivedAt->first());

});

it('shows row-action unarchive, only on archived rows', function () {

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithArchivedAt)
        ->assertCountTableRecords(1)
        ->assertTableActionExists(UnArchiveAction::class, record: $modelWithArchivedAt->first())
        ->assertTableActionDoesNotExist(ArchiveAction::class, record: $modelWithArchivedAt->first());

});

it('archives the model if ArchiveAction is called', function () {

    $modelWithoutArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => null]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithoutArchivedAt)
        ->assertCountTableRecords(1)
        ->assertTableActionExists(ArchiveAction::class, record: $modelWithoutArchivedAt->first())

        ->callTableAction(ArchiveAction::class, $modelWithoutArchivedAt->first())
        ->assertHasNoTableActionErrors();

    expect($modelWithoutArchivedAt->first()->refresh()->archived_at)
        ->not->toBe(null);
});

it('unarchives the model if UnarchiveAction is called', function () {

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithArchivedAt)
        ->assertCountTableRecords(1)
        ->assertTableActionExists(UnArchiveAction::class, record: $modelWithArchivedAt->first())
        ->assertTableActionDoesNotExist(ArchiveAction::class, record: $modelWithArchivedAt->first())

        ->callTableAction(UnArchiveAction::class, $modelWithArchivedAt->first())
        ->assertHasNoTableActionErrors();

    expect($modelWithArchivedAt->first()->refresh()->archived_at)
        ->toBe(null);
});

it('can set default archived-table-row classes', function () {
    $plugin = new FilamentArchivablePlugin();

    expect(FilamentArchivable::$archivedRecordClasses)
        ->toBeNull();

    $plugin->archivedTableRowClasses(['opacity-25']);

    expect(FilamentArchivable::$archivedRecordClasses)
        ->not->toBeNull()
        ->toBe(['opacity-25']);

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);

    livewire(ModelWithArchivableTraitResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithArchivedAt)
        ->assertCountTableRecords(1)
        ->assertTableActionExists(UnArchiveAction::class, record: $modelWithArchivedAt->first())
        ->assertTableActionDoesNotExist(ArchiveAction::class, record: $modelWithArchivedAt->first())
        ->assertSee('opacity-25');
});

it('can set the recordClasses specific for archived records in the table', function () {

    FilamentArchivable::$archivedRecordClasses = null;

    expect(FilamentArchivable::$archivedRecordClasses)
        ->toBeNull();

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);

    // bg-color-300 is set in @see ModelWithArchivableTraitAndCustomClassesResource
    livewire(ModelWithArchivableTraitAndCustomClassesResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithArchivedAt)
        ->assertCountTableRecords(1)
        ->assertSee('bg-red-300');

});

it('can ignore default archived-table-row classes when specificly defined on the resource-table', function () {

    $plugin = new FilamentArchivablePlugin();

    expect(FilamentArchivable::$archivedRecordClasses)
        ->toBeNull();

    $plugin->archivedTableRowClasses(['opacity-25']);

    expect(FilamentArchivable::$archivedRecordClasses)
        ->not->toBeNull()
        ->toBe(['opacity-25']);

    $modelWithArchivedAt = ModelWithArchivableTrait::factory()->count(1)->create(['archived_at' => now()]);

    // archivedRecordClasses is set to false in @see ModelWithArchivableTraitAndCustomClassesResource
    livewire(ModelWithArchivableTraitAndFalseCustomClassesResource\Pages\ListPage::class)
        ->filterTable(ArchivedFilter::class, true)
        ->assertSuccessful()
        ->assertCanSeeTableRecords($modelWithArchivedAt)
        ->assertCountTableRecords(1)
        ->assertTableActionExists(UnArchiveAction::class, record: $modelWithArchivedAt->first())
        ->assertTableActionDoesNotExist(ArchiveAction::class, record: $modelWithArchivedAt->first())
        ->assertDontSee('opacity-25')
        ->assertDontSee('bg-red-300');

});
