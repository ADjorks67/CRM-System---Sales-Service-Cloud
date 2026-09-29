<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function show(): View
    {
        return view('api.docs');
    }

    public function openapi(): Response
    {
        $path = base_path('docs/openapi.yaml');
        abort_unless(File::exists($path), 404);

        return response(File::get($path), 200, [
            'Content-Type' => 'application/yaml; charset=UTF-8',
        ]);
    }
}
