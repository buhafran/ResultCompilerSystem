<?php

namespace App\Services;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class SubjectImportService
{
    /** @return array{created:int,updated:int,failed:int,errors:array<int,string>} */
    public function import(School $school, string $absolutePath): array
    {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new RuntimeException('The uploaded CSV file could not be read.');
        }

        $handle = fopen($absolutePath, 'rb');
        if ($handle === false) {
            throw new RuntimeException('The uploaded CSV file could not be opened.');
        }

        try {
            $header = fgetcsv($handle, null, ',', '"', '');
            if (! is_array($header)) {
                throw new RuntimeException('The CSV file is empty.');
            }

            $header = array_map(fn ($value): string => $this->normaliseHeader((string) $value), $header);
            if (in_array('', $header, true) || count(array_unique($header)) !== count($header)) {
                throw new RuntimeException('CSV headers must be unique and cannot be blank.');
            }

            if (! in_array('name', $header, true)) {
                throw new RuntimeException('Missing required column: name.');
            }

            $created = 0;
            $updated = 0;
            $failed = 0;
            $errors = [];
            $rowNumber = 1;
            $maximumRows = 500;

            DB::transaction(function () use ($school, $handle, $header, $maximumRows, &$created, &$updated, &$failed, &$errors, &$rowNumber): void {
                while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                    $rowNumber++;

                    if ($rowNumber > $maximumRows + 1) {
                        throw new RuntimeException("A maximum of {$maximumRows} subject rows can be imported at once.");
                    }

                    if ($this->isBlankRow($row)) {
                        continue;
                    }

                    $row = array_pad($row, count($header), null);
                    $data = array_combine($header, array_slice($row, 0, count($header)));

                    if (! is_array($data)) {
                        $failed++;
                        $errors[] = "Row {$rowNumber}: invalid column structure.";
                        continue;
                    }

                    try {
                        $payload = $this->validateRow($data, $rowNumber);

                        if (($payload['code'] ?? null) !== null) {
                            $existingCode = Subject::query()
                                ->where('school_id', $school->id)
                                ->where('code', $payload['code'])
                                ->where('name', '!=', $payload['name'])
                                ->exists();

                            if ($existingCode) {
                                throw new RuntimeException("Row {$rowNumber}: subject code '{$payload['code']}' is already used by another subject.");
                            }
                        }

                        $subject = Subject::query()
                            ->where('school_id', $school->id)
                            ->where('name', $payload['name'])
                            ->first();

                        if ($subject) {
                            $subject->update($payload);
                            $updated++;
                        } else {
                            Subject::create(['school_id' => $school->id, ...$payload]);
                            $created++;
                        }
                    } catch (RuntimeException $exception) {
                        $failed++;
                        if (count($errors) < 100) {
                            $errors[] = $exception->getMessage();
                        }
                    }
                }
            });

            return compact('created', 'updated', 'failed', 'errors');
        } finally {
            fclose($handle);
        }
    }

    /** @return array{name:string,code:?string,subtitle:?string,is_active:bool} */
    private function validateRow(array $data, int $rowNumber): array
    {
        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '') {
            throw new RuntimeException("Row {$rowNumber}: subject name is required.");
        }

        if (mb_strlen($name) > 255) {
            throw new RuntimeException("Row {$rowNumber}: subject name cannot exceed 255 characters.");
        }

        $code = $this->limitedNullableString($data['code'] ?? null, 40, $rowNumber, 'code');
        $subtitle = $this->limitedNullableString($data['subtitle'] ?? null, 255, $rowNumber, 'subtitle');

        return [
            'name' => $name,
            'code' => $code !== null ? mb_strtoupper($code) : null,
            'subtitle' => $subtitle,
            'is_active' => $this->booleanValue($data['is_active'] ?? true),
        ];
    }

    private function normaliseHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = Str::snake(Str::lower(trim($header)));

        return match ($header) {
            'subject', 'subject_name', 'subjectname' => 'name',
            'subject_code', 'short_code', 'abbreviation' => 'code',
            'translation', 'subject_translation', 'local_name', 'subtitle_translation' => 'subtitle',
            'active', 'status' => 'is_active',
            default => $header,
        };
    }

    /** @param array<int,mixed> $row */
    private function isBlankRow(array $row): bool
    {
        return collect($row)->every(fn ($value): bool => trim((string) $value) === '');
    }

    private function limitedNullableString(mixed $value, int $maximum, int $rowNumber, string $field): ?string
    {
        $value = trim((string) $value);

        if (mb_strlen($value) > $maximum) {
            throw new RuntimeException("Row {$rowNumber}: {$field} cannot exceed {$maximum} characters.");
        }

        return $value !== '' ? $value : null;
    }

    private function booleanValue(mixed $value): bool
    {
        return ! in_array(Str::lower(trim((string) $value)), ['0', 'false', 'no', 'inactive'], true);
    }
}
