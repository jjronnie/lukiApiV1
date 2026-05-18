<?php

namespace App\Filament\Resources\ProviderProfiles\Schemas;

use App\Enums\ProviderVerificationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProviderProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('public_id')
                    ->required(),
                TextInput::make('provider_number')
                    ->numeric(),
                TextInput::make('provider_type')
                    ->required()
                    ->default('individual'),
                TextInput::make('display_name')
                    ->required(),
                TextInput::make('legal_name'),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('address_text'),
                TextInput::make('business_name'),
                TextInput::make('business_address'),
                DateTimePicker::make('onboarding_completed_at'),
                TextInput::make('avatar_path'),
                DateTimePicker::make('avatar_locked_at'),
                Select::make('verification_status')
                    ->options(ProviderVerificationStatus::class)
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('verified_at'),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                TextInput::make('rating_avg')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('rating_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('completed_orders_count')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('cancelled_orders_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
