<?php

namespace App\Http\Controllers\Web;

use App\Enums\PublicationStatus;
use App\Http\Controllers\Controller;
use App\Models\ResultPublication;
use App\Models\ResultSummary;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ResultViewController extends Controller
{
    public function show(ResultSummary $summary): View
    {
        $summary->loadMissing(['publication.school', 'publication.template']);
        abort_unless($summary->released_at && $summary->publication->status === PublicationStatus::Released, 404);

        return view('result.show', [
            'summary' => $summary,
            'snapshot' => $summary->snapshot,
            'template' => $summary->publication->template,
        ]);
    }

    public function pdf(ResultSummary $summary): Response
    {
        $summary->loadMissing(['publication.school', 'publication.template']);
        abort_unless($summary->released_at && $summary->publication->status === PublicationStatus::Released, 404);

        return Pdf::loadView('result.pdf.report', [
            'summary' => $summary,
            'snapshot' => $summary->snapshot,
            'template' => $summary->publication->template,
        ])->setPaper('a4')->download('result-'.$summary->public_token.'.pdf');
    }

    public function preview(ResultPublication $publication): View
    {
        $this->authorizePublication($publication);
        $publication->loadMissing(['school', 'template']);
        $summary = $publication->summaries()->firstOrFail();

        return view('result.show', [
            'summary' => $summary,
            'snapshot' => $summary->snapshot,
            'template' => $publication->template,
            'preview' => true,
        ]);
    }

    public function broadsheetPdf(ResultPublication $publication): Response
    {
        $this->authorizePublication($publication);
        $publication->loadMissing(['school', 'term.academicSession', 'schoolClass', 'summaries.student']);
        $summaries = $publication->summaries
            ->sortBy(fn (ResultSummary $summary): array => [$summary->class_position ?? PHP_INT_MAX, data_get($summary->snapshot, 'student.name', '')])
            ->values();

        return Pdf::loadView('result.pdf.broadsheet', [
            'publication' => $publication,
            'summaries' => $summaries,
        ])->setPaper('a3', 'landscape')->download($this->publicationFileName($publication, 'broadsheet').'.pdf');
    }

    public function classReportCardsPdf(ResultPublication $publication): Response
    {
        $this->authorizePublication($publication);
        $publication->loadMissing(['school', 'template', 'term.academicSession', 'schoolClass', 'summaries.student']);
        $summaries = $publication->summaries
            ->sortBy(fn (ResultSummary $summary): string => (string) data_get($summary->snapshot, 'student.name', ''))
            ->values();

        return Pdf::loadView('result.pdf.class-report-cards', [
            'publication' => $publication,
            'summaries' => $summaries,
            'template' => $publication->template,
        ])->setPaper('a4')->download($this->publicationFileName($publication, 'report-cards').'.pdf');
    }

    private function authorizePublication(ResultPublication $publication): void
    {
        $publication->loadMissing('school');
        abort_unless(auth()->check() && auth()->user()->isSchoolManager($publication->school), 403);
    }

    private function publicationFileName(ResultPublication $publication, string $type): string
    {
        $class = str($publication->schoolClass->name)->slug('-');
        $term = str($publication->term->name)->slug('-');
        $session = str($publication->term->academicSession->name)->slug('-');

        return "{$publication->school->slug}-{$class}-{$session}-{$term}-v{$publication->version}-{$type}";
    }
}
