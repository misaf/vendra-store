<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Tables\Columns;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Misaf\VendraStore\Models\Store;

final class StoreDomainColumn extends TextColumn
{
    public static function getDefaultName(): string
    {
        return 'domain';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-store::attributes.domain'))
            ->icon(Heroicon::GlobeAlt)
            ->state(fn (Store $record): ?string => $record->domains->first()?->name)
            ->placeholder('—');
    }
}
