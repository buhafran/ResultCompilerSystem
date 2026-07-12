<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\School;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolSetupDataController extends Controller
{
    public function classTemplate(School $school): StreamedResponse
    {
        $this->authorizeSchool($school);

        $rows = [
            ['Nursery 1', 'Nursery', '', 'yes'],
            ['Nursery 2', 'Nursery', '', 'yes'],
            ['Nursery 3', 'Nursery', '', 'yes'],
            ['KG 1', 'Kindergarten', '', 'yes'],
            ['KG 2', 'Kindergarten', '', 'yes'],
            ['Primary 1', 'Primary', '', 'yes'],
            ['Primary 2', 'Primary', '', 'yes'],
            ['Primary 3', 'Primary', '', 'yes'],
            ['Primary 4', 'Primary', '', 'yes'],
            ['Primary 5', 'Primary', '', 'yes'],
            ['Primary 6', 'Primary', '', 'yes'],
            ['JSS 1', 'Junior Secondary', '', 'yes'],
            ['JSS 2', 'Junior Secondary', '', 'yes'],
            ['JSS 3', 'Junior Secondary', '', 'yes'],
            ['SS 1', 'Senior Secondary', '', 'yes'],
            ['SS 2', 'Senior Secondary', '', 'yes'],
            ['SS 3', 'Senior Secondary', '', 'yes'],
        ];

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['name', 'level', 'arm', 'is_active']);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, 'class-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function subjectTemplate(School $school): StreamedResponse
    {
        $this->authorizeSchool($school);

        $rows = [
            ['English Language', 'ENG', '', 'yes'],
            ['Mathematics', 'MTH', '', 'yes'],
            ['Basic Science', 'BSC', '', 'yes'],
            ['Basic Technology', 'BTE', '', 'yes'],
            ['Computer Studies', 'CMP', '', 'yes'],
            ['Civic Education', 'CIV', '', 'yes'],
            ['Social Studies', 'SOS', '', 'yes'],
            ['Islamic Studies', 'IRS', '', 'yes'],
            ['Christian Religious Studies', 'CRS', '', 'yes'],
            ['Hausa Language', 'HAU', '', 'yes'],
            ['Yoruba Language', 'YOR', '', 'yes'],
            ['Igbo Language', 'IGB', '', 'yes'],
            ['Agricultural Science', 'AGR', '', 'yes'],
            ['Physical and Health Education', 'PHE', '', 'yes'],
            ['Business Studies', 'BST', '', 'yes'],
            ['Home Economics', 'HEC', '', 'yes'],
            ['Creative Arts', 'ART', '', 'yes'],
            ['French Language', 'FRE', '', 'yes'],
            ['Physics', 'PHY', '', 'yes'],
            ['Chemistry', 'CHE', '', 'yes'],
            ['Biology', 'BIO', '', 'yes'],
            ['Economics', 'ECO', '', 'yes'],
            ['Geography', 'GEO', '', 'yes'],
            ['Government', 'GOV', '', 'yes'],
            ['Literature in English', 'LIT', '', 'yes'],
            ['Commerce', 'COM', '', 'yes'],
            ['Accounting', 'ACC', '', 'yes'],
            ['Further Mathematics', 'FMT', '', 'yes'],
        ];

        return response()->streamDownload(function () use ($rows): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['name', 'code', 'subtitle', 'is_active']);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }

            fclose($output);
        }, 'subject-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function authorizeSchool(School $school): void
    {
        abort_unless(auth()->check() && auth()->user()->isSchoolManager($school), 403);
    }
}
