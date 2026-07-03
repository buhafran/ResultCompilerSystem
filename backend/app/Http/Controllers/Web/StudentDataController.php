<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\School;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDataController extends Controller
{
    public function template(School $school): StreamedResponse
    {
        $this->authorizeSchool($school);

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, ['admission_number', 'first_name', 'middle_name', 'last_name', 'gender', 'date_of_birth', 'class_name', 'portal_pin', 'is_active']);
            fputcsv($output, ['STU-001', 'Amina', '', 'Bello', 'female', '2012-04-17', 'JSS 1 A', '1234', 'yes']);
            fclose($output);
        }, 'student-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function export(School $school): StreamedResponse
    {
        $this->authorizeSchool($school);
        $students = $school->students()->with('schoolClass:id,name')->orderBy('school_class_id')->orderBy('last_name')->orderBy('first_name')->lazy(500);

        return response()->streamDownload(function () use ($students): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Admission Number', 'First Name', 'Middle Name', 'Last Name', 'Gender', 'Date of Birth', 'Class', 'Active']);
            foreach ($students as $student) {
                fputcsv($output, [
                    $this->csvCell($student->admission_number),
                    $this->csvCell($student->first_name),
                    $this->csvCell($student->middle_name),
                    $this->csvCell($student->last_name),
                    $this->csvCell($student->gender),
                    $student->date_of_birth?->format('Y-m-d'),
                    $this->csvCell($student->schoolClass?->name),
                    $student->is_active ? 'Yes' : 'No',
                ]);
            }
            fclose($output);
        }, $school->slug.'-students-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function csvCell(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'{$value}" : $value;
    }

    private function authorizeSchool(School $school): void
    {
        abort_unless(auth()->check() && auth()->user()->isSchoolManager($school), 403);
    }
}
