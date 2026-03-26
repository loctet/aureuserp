<?php

namespace Webkul\BulkImport\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                $failedRows = [];

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
                        $rowNumber = $rowIndex + 2;
                        $message = $e->getMessage();

                        $errors[] = "Row {$rowNumber}: {$message}";
                        $failedRows[] = [
                            'row_number' => $rowNumber,
                            'error'      => $message,
                            'row'        => $row,
                        ];
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

                $failedFilePath = self::writeFailedRowsCsv(
                    headers: $parsed['headers'],
                    failedRows: $failedRows,
                );

                Notification::make()
                    ->warning()
                    ->title('Import completed with errors')
                    ->body(
                        "Imported {$created} rows. Failed ".count($errors).' rows. '
                        .'First errors: '.implode(' | ', array_slice($errors, 0, 3))
                    )
                    ->actions([
                        NotificationAction::make('download_failed_rows')
                            ->label('Download failed_rows.csv')
                            ->url(Storage::disk('public')->url($failedFilePath), shouldOpenInNewTab: true),
                    ])
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

    /**
     * @param  list<string>  $headers
     * @param  list<array{row_number:int,error:string,row:array<string,mixed>}>  $failedRows
     */
    protected static function writeFailedRowsCsv(array $headers, array $failedRows): string
    {
        $directory = 'bulk-import-failures';
        $fileName = 'failed_rows_'.now()->format('Ymd_His').'_'.Str::random(6).'.csv';
        $path = $directory.'/'.$fileName;

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, array_merge(['row_number', 'error'], $headers));

        foreach ($failedRows as $failedRow) {
            $csvRow = [$failedRow['row_number'], $failedRow['error']];

            foreach ($headers as $header) {
                $csvRow[] = $failedRow['row'][$header] ?? '';
            }

            fputcsv($handle, $csvRow);
        }

        rewind($handle);
        $contents = stream_get_contents($handle) ?: '';
        fclose($handle);

        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
