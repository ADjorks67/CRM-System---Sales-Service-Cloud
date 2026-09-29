<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApiTokenRequest;
use App\Models\ApiToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiTokenController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ApiToken::class);

        $tokens = ApiToken::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->get();

        return view('api-tokens.index', [
            'tokens' => $tokens,
            'plainToken' => session('api_token_plain'),
        ]);
    }

    public function store(StoreApiTokenRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $expiresAt = null;
        if (! empty($validated['expires_at'])) {
            $expiresAt = $validated['expires_at'];
        }

        $issued = ApiToken::issue($request->user(), $validated['name'], $expiresAt);

        return redirect()
            ->route('api-tokens.index')
            ->with('success', 'API token created. Copy it now — it will not be shown again.')
            ->with('api_token_plain', $issued['plain']);
    }

    public function destroy(ApiToken $apiToken): RedirectResponse
    {
        $this->authorize('delete', $apiToken);
        $apiToken->delete();

        return redirect()
            ->route('api-tokens.index')
            ->with('success', 'API token revoked.');
    }
}
