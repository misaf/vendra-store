<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Resources\Stores\Tables;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Filament\Tables\Columns\StoreDomainColumn;
use Misaf\VendraStore\Filament\Tables\Columns\StoreStatusColumn;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\IsActiveFilter;

/**
 * The store list every panel shares. Panels add their own record actions,
 * record URL, and filters, and scope the query in their resource.
 */
final class StoreTable
{
    /**
     * @param  list<Column>  $identityColumns  panel-specific columns shown right after the name
     */
    public static function configure(Table $table, array $identityColumns = [], bool $filtersSeveralStatuses = false): Table
    {
        return $table
            ->columns([
                RowIndexColumn::make(),

                NameColumn::make()
                    ->searchable()
                    ->sortable(),

                ...$identityColumns,

                StoreDomainColumn::make(),

                TextColumn::make('storefront_status')
                    ->label(__('vendra-store::attributes.storefront_status'))
                    ->badge()
                    ->state(fn (Store $record): ?string => self::deployment($record)?->status->value)
                    ->formatStateUsing(fn (string $state): string => __("vendra-store::attributes.deployment_status_{$state}"))
                    ->placeholder(__('vendra-store::attributes.storefront_not_requested')),

                TextColumn::make('admin_url')
                    ->label(__('vendra-store::attributes.admin_url'))
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->state(fn (Store $record): string => $record->adminUrl())
                    ->url(fn (Store $record): string => $record->adminUrl())
                    ->openUrlInNewTab()
                    ->copyable()
                    ->copyMessage(__('vendra-store::messages.url_copied')),

                TextColumn::make('storefront_url')
                    ->label(__('vendra-store::attributes.storefront_url'))
                    ->icon(Heroicon::OutlinedShoppingBag)
                    ->state(fn (Store $record): ?string => self::deployment($record)?->domain)
                    ->placeholder('—')
                    ->url(fn (Store $record): ?string => self::deployment($record)?->url())
                    ->openUrlInNewTab()
                    ->copyable()
                    ->copyMessage(__('vendra-store::messages.url_copied')),

                StoreStatusColumn::make(),

                CreatedAtColumn::make()
                    ->sortable(),

                UpdatedAtColumn::make(),
            ])
            ->description(__('vendra-store::tables.description.stores'))
            ->emptyStateHeading(__('vendra-store::tables.empty_state.heading.stores'))
            ->emptyStateDescription(__('vendra-store::tables.empty_state.description.stores'))
            ->emptyStateIcon(Heroicon::OutlinedGlobeAlt)
            ->filters(
                [
                    IsActiveFilter::make(),

                    SelectFilter::make('status')
                        ->label(__('vendra-store::attributes.operational_status'))
                        ->multiple($filtersSeveralStatuses)
                        ->options(self::statusOptions())
                        ->query(function (Builder $query, array $data): Builder {
                            $statuses = [];

                            foreach (Arr::wrap(Arr::get($data, 'values', Arr::get($data, 'value'))) as $value) {
                                if (is_string($value) && ($status = StoreStatus::tryFrom($value)) instanceof StoreStatus) {
                                    $statuses[] = $status;
                                }
                            }

                            if ($statuses === []) {
                                return $query;
                            }

                            // Store status is derived, so each one expands to its column conditions.
                            return $query->where(function (Builder $query) use ($statuses): void {
                                foreach ($statuses as $status) {
                                    $query->orWhere(function (Builder $query) use ($status): void {
                                        $query->scopes(['withStatus' => [$status]]);
                                    });
                                }
                            });
                        }),

                    SelectFilter::make('storefront_status')
                        ->label(__('vendra-store::attributes.storefront_status'))
                        ->options(self::deploymentStatusOptions())
                        ->query(function (Builder $query, array $data): Builder {
                            $value = Arr::get($data, 'value');
                            $status = is_string($value) ? StorefrontDeploymentStatus::tryFrom($value) : null;

                            return $status === null
                                ? $query
                                : $query->whereHas(
                                    'storefrontDeployments',
                                    fn (Builder $query): Builder => $query->where('status', $status),
                                );
                        }),
                ],
                layout: FiltersLayout::AboveContentCollapsible,
            )
            ->defaultSort(column: 'id', direction: 'desc');
    }

    /**
     * Reads the eager-loaded relation, so resources must load `storefrontDeployments`.
     */
    private static function deployment(Store $store): ?StorefrontDeployment
    {
        $deployment = $store->storefrontDeployments->first();

        return $deployment instanceof StorefrontDeployment ? $deployment : null;
    }

    /** @return array<string, string> */
    private static function statusOptions(): array
    {
        return collect(StoreStatus::cases())
            ->mapWithKeys(fn (StoreStatus $status): array => [
                $status->value => __("vendra-store::attributes.store_status_{$status->value}"),
            ])
            ->all();
    }

    /** @return array<string, string> */
    private static function deploymentStatusOptions(): array
    {
        return collect(StorefrontDeploymentStatus::cases())
            ->mapWithKeys(fn (StorefrontDeploymentStatus $status): array => [
                $status->value => __("vendra-store::attributes.deployment_status_{$status->value}"),
            ])
            ->all();
    }
}
