<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;
use Livewire\Component as Livewire;
use Misaf\VendraStore\Models\StoreDomain;

/**
 * The first domain of a new store. Its leading label seeds the storefront slug
 * and English name from `StorefrontConfigurationFields` while those are blank.
 */
final class StoreDomainInput extends TextInput
{
    public static function getDefaultName(): string
    {
        return 'domain';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->afterStateUpdated(function (?string $state, Get $get, Set $set, Livewire $livewire): void {
                $livewire->validateOnly('data.domain');

                if (blank($state)) {
                    return;
                }

                $domainLabel = Str::before(StoreDomain::normalizeDomain($state), '.');

                if (blank($get('storefront_slug'))) {
                    $set('storefront_slug', Str::slug($domainLabel));
                }

                if (blank($get('storefront_name_en'))) {
                    $set('storefront_name_en', Str::headline($domainLabel));
                }
            })
            ->helperText(__('vendra-store::attributes.domain_helper_text'))
            ->label(__('vendra-store::attributes.domain'))
            ->placeholder('flowers.example')
            ->extraAttributes(['dir' => 'ltr'])
            ->live(onBlur: true)
            ->maxLength(255)
            ->required()
            ->rules(StoreDomain::activeDomainRules())
            ->dehydrateStateUsing(fn (?string $state): ?string => $state === null
                ? null
                : StoreDomain::normalizeDomain($state))
            ->visibleOn('create');
    }
}
