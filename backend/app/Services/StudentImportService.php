<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

final class StudentImportService
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

            $required = ['admission_number', 'first_name', 'last_name', 'class_name'];
            $missing = array_values(array_diff($required, $header));
            if ($missing !== []) {
                throw new RuntimeException('Missing required columns: '.implode(', ', $missing).'.');
            }

            $classes = SchoolClass::query()
                ->where('school_id', $school->id)
                ->get(['id', 'name'])
                ->keyBy(fn (SchoolClass $class): string => Str::lower(trim($class->name)));

            $created = 0;
            $updated = 0;
            $failed = 0;
            $errors = [];
            $rowNumber = 1;
            $maximumRows = 10000;

            DB::transaction(function () use ($school, $handle, $header, $classes, $maximumRows, &$created, &$updated, &$failed, &$errors, &$rowNumber): void {
                while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
                    $rowNumber++;
                    if ($rowNumber > $maximumRows + 1) {
                        throw new RuntimeException("A maximum of {$maximumRows} student rows can be imported at once.");
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
                        $payload = $this->validateRow($data, $classes, $rowNumber);
                        $pin = $payload['portal_pin'] ?? null;
                        unset($payload['portal_pin']);

                        $student = Student::withTrashed()
                            ->where('school_id', $school->id)
                            ->where('admission_number', $payload['admission_number'])
                            ->first();

                        if ($student) {
                            $student->restore();
                            $student->fill($payload);
                            if ($pin !== null) {
                                $student->portal_pin_hash = Hash::make($pin);
                            }
                            $student->save();
                            $updated++;
                        } else {
                            if ($pin !== null) {
                                $payload['portal_pin_hash'] = Hash::make($pin);
                            }
                            Student::create(['school_id' => $school->id, ...$payload]);
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

    /** @param array<string,mixed> $data @param \Illuminate\Support\Collection<string,SchoolClass> $classes */
    private function validateRow(array $data, $classes, int $rowNumber): array
    {
        $admission = trim((string) ($data['admission_number'] ?? ''));
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $className = trim((string) ($data['class_name'] ?? ''));

        if ($admission === '' || $firstName === '' || $lastName === '' || $className === '') {
            throw new RuntimeException("Row {$rowNumber}: admission_number, first_name, last_name and class_name are required.");
        }
        foreach (['admission_number' => $admission, 'first_name' => $firstName, 'last_name' => $lastName, 'class_name' => $className] as $field => $value) {
            if (mb_strlen($value) > 255) {
                throw new RuntimeException("Row {$rowNumber}: {$field} cannot exceed 255 characters.");
            }
        }

        $class = $classes->get(Str::lower($className));
        if (! $class) {
            throw new RuntimeException("Row {$rowNumber}: class '{$className}' does not exist in this school.");
        }

        $gender = Str::lower(trim((string) ($data['gender'] ?? '')));
        if ($gender !== '' && ! in_array($gender, ['male', 'female'], true)) {
            throw new RuntimeException("Row {$rowNumber}: gender must be male or female.");
        }

        $dateOfBirth = null;
        if (filled($data['date_of_birth'] ?? null)) {
            $rawDate = trim((string) $data['date_of_birth']);
            try {
                $parsedDate = CarbonImmutable::createFromFormat('!Y-m-d', $rawDate);
                if (! $parsedDate || $parsedDate->format('Y-m-d') !== $rawDate) {
                    throw new \RuntimeException();
                }
                $dateOfBirth = $parsedDate->format('Y-m-d');
            } catch (\Throwable) {
                throw new RuntimeException("Row {$rowNumber}: date_of_birth is invalid; use YYYY-MM-DD.");
            }
        }

        $pin = trim((string) ($data['portal_pin'] ?? ''));
        if ($pin !== '' && (mb_strlen($pin) < 4 || mb_strlen($pin) > 20)) {
            throw new RuntimeException("Row {$rowNumber}: portal_pin must contain 4 to 20 characters.");
        }

        return [
            'school_class_id' => $class->id,
            'admission_number' => $admission,
            'first_name' => $firstName,
            'middle_name' => $this->limitedNullableString($data['middle_name'] ?? null, 255, $rowNumber, 'middle_name'),
            'last_name' => $lastName,
            'gender' => $gender !== '' ? $gender : null,
            'date_of_birth' => $dateOfBirth,
            'is_active' => $this->booleanValue($data['is_active'] ?? true),
            'portal_pin' => $pin !== '' ? $pin : null,
        ];
    }

    private function normaliseHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = Str::snake(Str::lower(trim($header)));

        return match ($header) {
            'admission_no', 'admission', 'student_id' => 'admission_number',
            'firstname', 'first' => 'first_name',
            'middlename', 'middle' => 'middle_name',
            'lastname', 'surname' => 'last_name',
            'class', 'school_class' => 'class_name',
            'dob', 'birth_date' => 'date_of_birth',
            'pin', 'portal_password' => 'portal_pin',
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
