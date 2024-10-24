<?php

namespace Ariaieboy\FilamentJalali;

use Ariaieboy\FilamentJalali\DateConstraint\Operators\IsJalaliAfterOperator;
use Ariaieboy\FilamentJalali\DateConstraint\Operators\IsJalaliBeforeOperator;
use Ariaieboy\FilamentJalali\DateConstraint\Operators\IsJalaliDateOperator;
use Ariaieboy\FilamentJalali\DateConstraint\Operators\IsJalaliYearOperator;
use Ariaieboy\Jalali\Jalali;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\QueryBuilder\Constraints\Operators\IsFilledOperator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentJalaliServiceProvider extends PackageServiceProvider
{
    public function bootingPackage(): void
    {
        FilamentAsset::register([
            AlpineComponent::make('jalali-datetime-picker', __DIR__ . '/../resources/dist/js/jalali-date-time-picker.js'),
        ], package: "ariaieboy/jalali");
        DatePicker::macro('jalali', function (bool $weekdaysShort = false) {
            /** @var DatePicker $this */
            $this->view = "filament-jalali::components.jalali-datetimepicker";
            $this->extraAttributes(['data-weekdays-short' => ($weekdaysShort ? 'short' : 'long')], true);
            $this->firstDayOfWeek(6);
            $this->displayFormat('Y/m/d');

            return $this;
        });
        DateTimePicker::macro('jalali', function (bool $weekdaysShort = false) {
            /** @var DateTimePicker $this */

            $this->view = "filament-jalali::components.jalali-datetimepicker";
            $this->extraAttributes(['data-weekdays-short' => ($weekdaysShort ? 'short' : 'long')], true);
            $this->firstDayOfWeek(6);
            $this->displayFormat('Y/m/d H:i:s');

            return $this;
        });
        TextColumn::macro('jalaliDate', function (?string $format = null, ?string $timezone = null): static {
            $format ??= config('filament-jalali.date_format');

            $this->formatStateUsing(static function (Column $column, $state) use ($format, $timezone): ?string {
                /** @var TextColumn $column */

                if (blank($state)) {
                    return null;
                }

                return Jalali::fromCarbon(Carbon::parse($state)
                    ->setTimezone($timezone ?? $column->getTimezone()))
                    ->format($format);
            });

            return $this;
        });

        TextColumn::macro('jalaliDateTime', function (?string $format = null, ?string $timezone = null): static {
            $format ??= config('filament-jalali.date_time_format');

            return $this->jalaliDate($format, $timezone);
        });
        TextEntry::macro('jalaliDate',function (?string $format = null, ?string $timezone = null): static {
            $format ??= config('filament-jalali.date_format');
            $this->formatStateUsing(static function (TextEntry $column, $state) use ($format, $timezone): ?string {
                /** @var TextColumn $column */

                if (blank($state)) {
                    return null;
                }

                return Jalali::fromCarbon(Carbon::parse($state)
                    ->setTimezone($timezone ?? $column->getTimezone()))
                    ->format($format);
            });
            return $this;
        });
        TextEntry::macro('jalaliDateTooltip',function (?string $format = null, ?string $timezone = null): static {
            $format ??= config('filament-jalali.date_format');
            $this->tooltip(static function (TextEntry $column, $state) use ($format, $timezone): ?string {
                /** @var TextColumn $column */

                if (blank($state)) {
                    return null;
                }

                return Jalali::fromCarbon(Carbon::parse($state)
                    ->setTimezone($timezone ?? $column->getTimezone()))
                    ->format($format);
            });
            return $this;
        });
        TextEntry::macro('jalaliDateTime',function (?string $format = null, ?string $timezone = null): static {
            $format ??= config('filament-jalali.date_time_format');
            return $this->jalaliDate($format, $timezone);
        });
        TextEntry::macro('jalaliDateTimeTooltip',function (?string $format = null, ?string $timezone = null): static {
            $format ??= config('filament-jalali.date_time_format');
            return $this->jalaliDateTooltip($format, $timezone);
        });
        DateConstraint::macro('jalali',function (){
            /**
             * @var DateConstraint $this
             */
            $this->operators([
                IsJalaliAfterOperator::class,
                IsJalaliBeforeOperator::class,
                IsJalaliDateOperator::class,
                IsJalaliYearOperator::class,
                IsFilledOperator::make()
                    ->visible(fn (): bool => $this->isNullable()),
            ]);
            return $this;
        });
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-jalali')
            ->hasTranslations()
            ->hasConfigFile()
            ->hasViews();
    }
}
