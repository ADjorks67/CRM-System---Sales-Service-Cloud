<?php

namespace App\Http\Controllers;

use App\Http\Requests\GdprSubjectRequest;
use App\Services\PersonalDataAnonymizeService;
use App\Services\PersonalDataExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GdprController extends Controller
{
    public function __construct(
        private readonly PersonalDataExportService $exportService,
        private readonly PersonalDataAnonymizeService $anonymizeService,
    ) {}

    public function index(): View
    {
        $this->authorize('manageGdpr');

        return view('gdpr.index');
    }

    public function export(GdprSubjectRequest $request): StreamedResponse
    {
        $payload = $this->exportService->export(
            $request->string('subject_type')->toString(),
            $request->integer('subject_id'),
            $request->user(),
        );

        $filename = sprintf(
            'gdpr-export-%s-%d-%s.json',
            $payload['subject_type'],
            $payload['subject_id'],
            now()->format('YmdHis'),
        );

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function anonymize(GdprSubjectRequest $request): RedirectResponse
    {
        try {
            $this->anonymizeService->anonymize(
                $request->string('subject_type')->toString(),
                $request->integer('subject_id'),
                $request->user(),
            );
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('gdpr.index')
                ->withErrors(['subject_id' => $exception->getMessage()]);
        }

        return redirect()
            ->route('gdpr.index')
            ->with('status', 'Personal data anonymized. Related history rows were retained.');
    }
}
