<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Services\DataExportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportController extends Controller
{
    public function __construct(
        private readonly DataExportService $exports,
    ) {}

    public function index(Request $request): View
    {
        $objects = collect($this->exports->objects())
            ->filter(fn (string $label, string $key) => $this->canExport($request, $key))
            ->all();

        abort_unless($objects !== [], 403);

        return view('exports.index', [
            'objects' => $objects,
        ]);
    }

    public function download(Request $request, string $object): StreamedResponse
    {
        abort_unless(isset($this->exports->objects()[$object]), 404);
        abort_unless($this->canExport($request, $object), 403);

        return $this->exports->downloadCsv($object, $request->user());
    }

    private function canExport(Request $request, string $object): bool
    {
        $model = match ($object) {
            'accounts' => Account::class,
            'contacts' => Contact::class,
            'leads' => Lead::class,
            'opportunities' => Opportunity::class,
            default => null,
        };

        return $model !== null && $request->user()->can('viewAny', $model);
    }
}
