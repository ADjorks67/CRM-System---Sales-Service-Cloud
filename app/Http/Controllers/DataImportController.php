<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportDataRequest;
use App\Http\Requests\PreviewImportRequest;
use App\Services\DataImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataImportController extends Controller
{
    public function __construct(
        private readonly DataImportService $imports,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            collect($this->imports->objects())->contains(
                fn (array $meta) => $request->user()->can('create', $meta['model'])
            ),
            403,
        );

        return view('imports.index', [
            'objects' => collect($this->imports->objects())
                ->filter(fn (array $meta) => $request->user()->can('create', $meta['model']))
                ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label']])
                ->all(),
        ]);
    }

    public function preview(PreviewImportRequest $request): View
    {
        $object = $request->validated('object');
        $file = $request->file('file');
        $dir = 'imports/'.$request->user()->id;
        $filename = Str::uuid()->toString().'.csv';
        $path = $file->storeAs($dir, $filename);

        $absolute = Storage::path($path);
        $headers = $this->imports->readHeaders($absolute);
        $mapping = $this->imports->suggestMapping($object, $headers);

        session([
            'import.path' => $path,
            'import.object' => $object,
            'import.update_or_insert' => $request->boolean('update_or_insert', true),
        ]);

        return view('imports.preview', [
            'object' => $object,
            'objectLabel' => $this->imports->objects()[$object]['label'],
            'fields' => $this->imports->objects()[$object]['fields'],
            'headers' => $headers,
            'mapping' => $mapping,
            'updateOrInsert' => $request->boolean('update_or_insert', true),
        ]);
    }

    public function store(ImportDataRequest $request): RedirectResponse
    {
        $path = session('import.path');
        $object = $request->validated('object');

        abort_unless(is_string($path) && Storage::exists($path), 422, 'Upload expired. Please upload the CSV again.');

        $result = $this->imports->import(
            $object,
            Storage::path($path),
            $request->validated('mapping'),
            $request->user(),
            $request->boolean('update_or_insert', true),
        );

        Storage::delete($path);
        session()->forget(['import.path', 'import.object', 'import.update_or_insert']);
        session(['import.errors' => $result['errors']]);

        $summary = sprintf(
            'Import finished: %d created, %d updated, %d skipped, %d errors.',
            $result['created'],
            $result['updated'],
            $result['skipped'],
            count($result['errors']),
        );

        return redirect()
            ->route('imports.index')
            ->with('success', $summary)
            ->with('import_error_count', count($result['errors']));
    }

    public function downloadErrors(Request $request): StreamedResponse
    {
        $errors = session('import.errors', []);
        abort_unless(is_array($errors) && $errors !== [], 404);

        return response()->streamDownload(function () use ($errors): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fputcsv($out, ['row', 'message']);
            foreach ($errors as $error) {
                fputcsv($out, [$error['row'] ?? '', $error['message'] ?? '']);
            }
            fclose($out);
        }, 'import-errors-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
