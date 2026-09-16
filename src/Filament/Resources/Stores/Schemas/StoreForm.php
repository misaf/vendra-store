<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Misaf\VendraStore\Filament\Schemas\StorefrontConfigurationFields;

/**
 * Edit exposes only descriptive fields; everything a store is created with
 * arrives through `$creationFields` because each panel decides the reseller
 * and administrator inputs itself.
 */
final class StoreForm
{
    /**
     * @param  list<Field>  $creationFields
     */
    public static function configure(Schema $schema, array $creationFields, bool $storefrontIsOptional): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('vendra-store::attributes.name'))
                    ->required()
                    ->maxLength(255)
                    ->visibleOn('edit'),

                Textarea::make('description')
                    ->label(__('vendra-store::attributes.description'))
                    ->rows(4)
                    ->maxLength(2000)
                    ->visibleOn('edit')
                    ->columnSpanFull(),

                ...$creationFields,

                ...StorefrontConfigurationFields::make(optional: $storefrontIsOptional),
            ])
            ->columns(2);
    }
}
