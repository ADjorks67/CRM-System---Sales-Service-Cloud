<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreAttachmentRequest extends FormRequest
{
    /** @var list<string> */
    public const EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];

    /** @var list<string> */
    public const MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif',
        'text/plain',
    ];

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'attachable_type' => ['required', 'string', Rule::in(['account', 'contact', 'lead', 'opportunity', 'case'])],
            'attachable_id' => ['required', 'integer'],
            'file' => [
                'required',
                File::types(self::EXTENSIONS)->max(25 * 1024),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $file = $this->file('file');
            if ($file === null) {
                return;
            }

            $mime = $file->getMimeType() ?: $file->getClientMimeType();
            if ($mime && ! in_array($mime, self::MIMES, true)) {
                $validator->errors()->add('file', 'File MIME type is not allowed.');
            }
        });
    }
}
