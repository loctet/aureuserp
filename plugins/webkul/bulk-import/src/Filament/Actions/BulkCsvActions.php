<?php

namespace Webkul\BulkImport\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Throwable;

class BulkCsvActions
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string, string>  $columnTypes
     * @param  callable(array<string, mixed>): array<string, mixed>|null  $mutateRow
     */
    public static function makeImportAction(
        string $modelClass,
        array $columnTypes,
        ?callable $mutateRow = null
    ): Action {
        return Action::make('importBulkCsv')
            ->label('Import in Bulk')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('primary')
            ->form([
                FileUpload::make('csv_file')
                    ->label('CSV File')
                    ->required()
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                    ->disk('local')
                    ->directory('bulk-imports'),
            ])
            ->action(function (array $data) use ($modelClass, $columnTypes, $mutateRow): void {
                $path = Storage::disk('local')->path((string) $data['csv_file']);
                $parsed = self::parseCsvRows($path);

                if ($parsed['headers'] === []) {
                    Notification::make()
                        ->danger()
                        ->title('Import failed')
                        ->body('The CSV file is empty or has no valid header row.')
                        ->send();

                    return;
                }

                $requiredHeaders = array_keys($columnTypes);
                $missingHeaders = array_values(array_diff($requiredHeaders, $parsed['headers']));

                if ($missingHeaders !== []) {
                    Notification::make()
                        ->danger()
                        ->title('Import failed')
                        ->body('Missing required columns: '.implode(', ', $missingHeaders))
                        ->send();

                    return;
                }

                $created = 0;
                $errors = [];

                foreach ($parsed['rows'] as $rowIndex => $row) {
                    try {
                        $payload = [];

                        foreach ($columnTypes as $column => $type) {
                            $payload[$column] = self::castValue($row[$column] ?? null, $type);
                        }

                        if ($mutateRow) {
                            $payload = $mutateRow($payload);
                        }

                        $modelClass::create($payload);
                        $created++;
                    } catch (Throwable $e) {
                        $errors[] = 'Row '.($rowIndex + 2).': '.$e->getMessage();
                    }
                }

                if ($errors === []) {
                    Notification::make()
                        ->success()
                        ->title('Import completed')
                        ->body("Successfully imported {$created} rows.")
                        ->send();

                    return;
                }

                Notification::make()
                    ->warning()
                    ->title('Import completed with errors')
                    ->body("Imported {$created} rows. Failed ".count($errors).' rows.')
                    ->send();
            });
    }

    /**
     * @param  list<string>  $headers
     * @param  array<string, scalar|null>  $sampleRow
     */
    public static function makeTemplateAction(
        string $fileName,
        array $headers,
        array $sampleRow = []
    ): Action {
        return Action::make('downloadBulkCsvTemplate')
            ->label('Download Template CSV')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->action(function () use ($fileName, $headers, $sampleRow) {
                return Response::streamDownload(function () use ($headers, $sampleRow): void {
                    $handle = fopen('php://output', 'w');

                    fputcsv($handle, $headers);

                    if ($sampleRow !== []) {
                        $row = [];

                        foreach ($headers as $header) {
                            $row[] = $sampleRow[$header] ?? '';
                        }

                        fputcsv($handle, $row);
                    }

                    fclose($handle);
                }, $fileName, [
                    'Content-Type' => 'text/csv',
                ]);
            });
    }

    /**
     * @return array{headers: list<string>, rows: list<array<string, string|null>>}
     */
    protected static function parseCsvRows(string $path): array
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = [];
        $rows = [];

        while (($line = fgetcsv($handle)) !== false) {
            $line = array_map(
                fn ($value) => is_string($value) ? trim($value) : $value,
                $line
            );

            if ($headers === []) {
                $headers = array_values(array_filter($line, fn ($header) => filled($header)));

                continue;
            }

            if (collect($line)->filter(fn ($value) => filled($value))->isEmpty()) {
                continue;
            }

            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = $line[$index] ?? null;
            }

            $rows[] = $row;
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows'    => $rows,
        ];
    }

    protected static function castValue(mixed $value, string $type): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            default => $value,
        };
    }
}
