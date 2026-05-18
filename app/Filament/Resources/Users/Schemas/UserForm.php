<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('public_id')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('first_name'),
                TextInput::make('last_name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at'),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('phone_country_code')
                    ->tel(),
                TextInput::make('phone_local_number')
                    ->tel(),
                DateTimePicker::make('phone_verified_at'),
                TextInput::make('referral_code'),
                DateTimePicker::make('last_seen_at'),
                DateTimePicker::make('profile_completed_at'),
                Toggle::make('is_blocked')
                    ->required(),
                TextInput::make('google_id'),
                TextInput::make('signup_method')
                    ->required()
                    ->default('email'),
            ]);
    }
}
