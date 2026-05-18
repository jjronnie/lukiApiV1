<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderBookingMode;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('public_id')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Select::make('provider_profile_id')
                    ->relationship('providerProfile', 'id'),
                Select::make('service_id')
                    ->relationship('service', 'name'),
                Select::make('service_tier_id')
                    ->relationship('serviceTier', 'name'),
                Select::make('transport_zone_id')
                    ->relationship('transportZone', 'name'),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default('created')
                    ->required(),
                Select::make('booking_mode')
                    ->options(OrderBookingMode::class)
                    ->default('normal')
                    ->required(),
                TextInput::make('pair_provider_number')
                    ->numeric(),
                TextInput::make('service_name_snapshot'),
                TextInput::make('service_tier_name_snapshot'),
                TextInput::make('transport_zone_name_snapshot'),
                Toggle::make('is_scheduled')
                    ->required(),
                DateTimePicker::make('scheduled_at'),
                DateTimePicker::make('offering_started_at'),
                DateTimePicker::make('accepted_at'),
                DateTimePicker::make('on_the_way_at'),
                DateTimePicker::make('arrived_at'),
                DateTimePicker::make('in_service_at'),
                DateTimePicker::make('completed_at'),
                DateTimePicker::make('cancelled_at'),
                DateTimePicker::make('expired_at'),
                TextInput::make('cancelled_by_user_id')
                    ->numeric(),
                TextInput::make('cancellation_reason'),
                TextInput::make('cancellation_fee_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('address_text')
                    ->required(),
                TextInput::make('location_lat')
                    ->required()
                    ->numeric(),
                TextInput::make('location_lng')
                    ->required()
                    ->numeric(),
                TextInput::make('provider_last_location_lat')
                    ->numeric(),
                TextInput::make('provider_last_location_lng')
                    ->numeric(),
                DateTimePicker::make('provider_last_location_at'),
                TextInput::make('provider_eta_minutes')
                    ->numeric(),
                TextInput::make('provider_distance_meters')
                    ->numeric(),
                TextInput::make('place_id'),
                Select::make('payment_method')
                    ->options(PaymentMethod::class)
                    ->required(),
                Select::make('payment_status')
                    ->options(PaymentStatus::class)
                    ->default('unpaid')
                    ->required(),
                DateTimePicker::make('paid_at'),
                TextInput::make('subtotal_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('transport_fee_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('distance_fee_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('overtime_fee_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('peak_fee_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('price_breakdown')
                    ->required(),
                TextInput::make('promo_code'),
                TextInput::make('provider_rating')
                    ->numeric(),
                Textarea::make('provider_review')
                    ->columnSpanFull(),
                DateTimePicker::make('rated_at'),
            ]);
    }
}
