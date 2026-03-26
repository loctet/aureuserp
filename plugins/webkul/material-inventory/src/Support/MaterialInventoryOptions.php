<?php

namespace Webkul\MaterialInventory\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Webkul\MaterialInventory\Settings\MaterialInventorySettings;

final class MaterialInventoryOptions
{
    /** @var array<string, mixed>|null */
    private static ?array $settingsCache = null;

    /**
     * @return array<int, string>
     */
    public static function categories(): array
    {
        $categories = array_values(array_filter(array_map(
            fn ($value) => trim((string) $value),
            (array) self::value('categories', [])
        )));

        return $categories !== [] ? $categories : [
            'N-Notebook',
            'O-Asset Office',
            'L-Software License',
            'S-Hardware Instrument',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        $statuses = array_values(array_filter(array_map(
            fn ($value) => Str::snake(trim((string) $value)),
            (array) self::value('statuses', [])
        )));

        return $statuses !== [] ? $statuses : ['new', 'used', 'broken', 'in_use', 'under_repair'];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryOptions(): array
    {
        $values = self::categories();

        return collect($values)->mapWithKeys(fn (string $value) => [$value => $value])->all();
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        $values = self::statuses();

        return collect($values)->mapWithKeys(fn (string $value) => [$value => self::humanizeStatus($value)])->all();
    }

    public static function defaultStatus(): string
    {
        $value = Str::snake((string) self::value('default_status', 'new'));

        return in_array($value, self::statuses(), true) ? $value : self::statuses()[0];
    }

    public static function inUseStatus(): string
    {
        $value = Str::snake((string) self::value('status_in_use', 'in_use'));

        return in_array($value, self::statuses(), true) ? $value : self::defaultStatus();
    }

    public static function underRepairStatus(): string
    {
        $value = Str::snake((string) self::value('status_under_repair', 'under_repair'));

        return in_array($value, self::statuses(), true) ? $value : self::defaultStatus();
    }

    /**
     * @return array<string, string>
     */
    public static function returnStatusOptions(): array
    {
        $blocked = [self::inUseStatus(), self::underRepairStatus()];

        return collect(self::statusOptions())
            ->reject(fn (string $label, string $value) => in_array($value, $blocked, true))
            ->all();
    }

    public static function humanizeStatus(string $value): string
    {
        return Str::of($value)->replace('_', ' ')->headline()->toString();
    }

    public static function enforceProjectBudget(): bool
    {
        return (bool) self::value('enforce_project_budget', true);
    }

    private static function value(string $property, mixed $default = null): mixed
    {
        $cache = self::settingsFromDatabase();

        if (array_key_exists($property, $cache)) {
            return $cache[$property];
        }

        return $default;
    }

    /**
     * Read settings payload once per request without instantiating Spatie settings.
     *
     * @return array<string, mixed>
     */
    private static function settingsFromDatabase(): array
    {
        if (self::$settingsCache !== null) {
            return self::$settingsCache;
        }

        try {
            if (! Schema::hasTable('settings')) {
                return self::$settingsCache = [];
            }

            $rows = DB::table('settings')
                ->where('group', MaterialInventorySettings::group())
                ->pluck('payload', 'name');

            $values = [];

            foreach ($rows as $name => $payload) {
                $decoded = json_decode((string) $payload, true);
                $values[$name] = json_last_error() === JSON_ERROR_NONE ? $decoded : $payload;
            }

            return self::$settingsCache = $values;
        } catch (\Throwable) {
            return self::$settingsCache = [];
        }
    }
}
