<?php

namespace Okeonline\FilamentArchivable;

use Filament\Tables\Table;
use LaravelArchivable\Scopes\ArchivableScope;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Okeonline\FilamentArchivable\Commands\FilamentArchivableCommand;

class FilamentArchivableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-archivable')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations();
    }
}
