<?php

namespace App\Filament\Resources\ProviderProfiles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProviderProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.name')
                    ->label('User'),
                TextEntry::make('public_id'),
                TextEntry::make('provider_number')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('provider_type'),
                TextEntry::make('display_name'),
                TextEntry::make('legal_name')
                    ->placeholder('-'),
                TextEntry::make('bio')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('phone')
                    ->placeholder('-'),
                TextEntry::make('address_text')
                    ->placeholder('-'),
                TextEntry::make('business_name')
                    ->placeholder('-'),
                TextEntry::make('business_address')
                    ->placeholder('-'),
                TextEntry::make('onboarding_completed_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('avatar_path')
                    ->placeholder('-'),
                TextEntry::make('avatar_locked_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('verification_status')
                    ->badge(),
                TextEntry::make('verified_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('rejection_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('rating_avg')
                    ->numeric(),
                TextEntry::make('rating_count')
                    ->numeric(),
                TextEntry::make('completed_orders_count')
                    ->numeric(),
                TextEntry::make('cancelled_orders_count')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
