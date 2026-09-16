<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Tables\Columns;

use Filament\Tables\Columns\TextColumn;
use Misaf\VendraStore\Models\Store;

final class StoreStatusColumn extends TextColumn
{
    public static function getDefaultName(): string
    {
        return 'status';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(__('vendra-store::attributes.operational_status'))
            ->badge()
            ->state(fn (Store $record): string => $record->status()->value)
            ->formatStateUsing(fn (string $state): string => __("vendra-store::attributes.store_status_{$state}"));
    }
}
