<?php

namespace App\Http\Controllers;

use App\Contracts\AttachmentScanner;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /** @var array<string, class-string<Model>> */
    private const ATTACHABLES = [
        'account' => Account::class,
        'contact' => Contact::class,
        'lead' => Lead::class,
        'opportunity' => Opportunity::class,
        'case' => CrmCase::class,
    ];

    public function __construct(private readonly AttachmentScanner $scanner) {}

    public function store(StoreAttachmentRequest $request): RedirectResponse
    {
        $type = $request->string('attachable_type')->toString();
        $id = (int) $request->integer('attachable_id');
        $attachable = $this->resolveAttachable($type, $id);

        $this->authorize('create', [Attachment::class, $attachable]);

        $file = $request->file('file');
        $disk = 'attachments';
        $path = $type.'/'.$attachable->getKey().'/'.Str::uuid().'.'.$file->getClientOriginalExtension();

        Storage::disk($disk)->putFileAs(
            dirname($path),
            $file,
            basename($path),
        );

        $absolute = Storage::disk($disk)->path($path);
        $scan = $this->scanner->scan($absolute, $file->getClientOriginalName());

        if (($scan['status'] ?? '') === Attachment::SCAN_REJECTED) {
            Storage::disk($disk)->delete($path);

            return back()->withErrors(['file' => $scan['message'] ?? 'File rejected by virus scan.']);
        }

        Attachment::query()->create([
            'attachable_type' => $type,
            'attachable_id' => $attachable->getKey(),
            'original_name' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
            'size_bytes' => $file->getSize() ?: 0,
            'uploaded_by' => $request->user()->id,
            'scan_status' => Attachment::SCAN_CLEAN,
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $this->authorize('download', $attachment);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function preview(Attachment $attachment): StreamedResponse|Response
    {
        $this->authorize('download', $attachment);

        if (! $attachment->isImage()) {
            return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
        }

        return response(
            Storage::disk($attachment->disk)->get($attachment->path),
            200,
            [
                'Content-Type' => $attachment->mime_type,
                'Content-Disposition' => 'inline; filename="'.$attachment->original_name.'"',
            ],
        );
    }

    public function destroy(Request $request, Attachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'Attachment deleted.');
    }

    private function resolveAttachable(string $type, int $id): Model
    {
        abort_unless(isset(self::ATTACHABLES[$type]), 404);

        return self::ATTACHABLES[$type]::query()->findOrFail($id);
    }
}
